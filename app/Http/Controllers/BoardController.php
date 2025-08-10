<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\User;
use App\Mail\BoardInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $boards = $request->user()
            ->boards()
            ->withPivot('role')
            ->latest()
            ->get();

        return Inertia::render('Boards/Index', [
            'boards' => $boards
        ]);
    }

    public function create()
    {
        return Inertia::render('Boards/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $board = Board::create([
            'name' => $data['name'],
            'created_by' => $request->user()->id,
        ]);

        // Attach creator as admin
        $board->users()->attach($request->user()->id, ['role' => 'admin']);

        return redirect()->route('boards.index')->with('success', 'Board created successfully.');
    }

    public function destroy(Board $board)
    {
        $this->authorize('delete', $board);
        $board->delete();

        return redirect()->route('boards.index')->with('success', 'Board deleted successfully.');
    }

    public function show(Board $board)
    {
        $this->authorize('view', $board);

        $board->load([
            'columns' => function ($query) {
                $query->orderBy('order');
            },
            'columns.cards' => function ($query) {
                $query->orderBy('order');
            },
            'users'
        ]);

        $user = Auth::user();
        $role = $board->users->find($user->id)->pivot->role;

        return Inertia::render('Boards/Show', [
            'board' => $board,
            'role' => $role,
            'members' => $board->users,
        ]);
    }

    public function edit(Board $board)
    {
        $this->authorize('update', $board);

        return Inertia::render('Boards/Edit', [
            'board' => $board,
        ]);
    }

    public function update(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $board->update($data);

        return redirect()->route('boards.index')->with('success', 'Board updated successfully.');
    }

    public function invite(Request $request, Board $board)
    {
        $this->authorize('invite', $board);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,editor,viewer'],
        ]);

        // Check if the user is already a member of the board
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser && $board->users->contains($existingUser->id)) {
            return back()->withErrors(['email' => 'This user is already a member of this board.']);
        }

        // Check if an invitation already exists for this email and board
        $existingInvitation = BoardInvitation::where('board_id', $board->id)
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->first();

        if ($existingInvitation) {
            return back()->withErrors(['email' => 'An invitation has already been sent to this email for this board.']);
        }

        $token = Str::random(60);

        $invitation = BoardInvitation::create([
            'board_id' => $board->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($validated['email'])->send(new BoardInvitationMail($invitation));

        return back()->with('success', 'Invitation sent successfully.');
    }

    public function acceptInvitation(Request $request, string $token)
    {
        $invitation = BoardInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            abort(404, 'Invitation not found or expired.');
        }

        if (!Auth::check()) {
            session()->put('url.intended', route('invitations.accept', ['token' => $token]));
            return redirect()->route('login')->with('info', 'Please log in or register to accept the invitation.');
        }

        $user = Auth::user();

        // Check if the logged-in user's email matches the invited email
        if ($user->email !== $invitation->email) {
            return redirect()->route('dashboard')->with('error', 'This invitation is not for your account. Please log in with the correct email or register.');
        }

        // Check if the user is already a member of the board
        if ($invitation->board->users->contains($user->id)) {
            $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);
            return redirect()->route('boards.show', $invitation->board)->with('info', 'You are already a member of this board.');
        }

        // Attach the user to the board with the specified role
        $invitation->board->users()->attach($user->id, ['role' => $invitation->role]);

        // Update invitation status
        $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);

        return redirect()->route('boards.show', $invitation->board)->with('success', 'Invitation accepted! You are now a member of the board.');
    }

    public function updateMemberRole(Request $request, Board $board, User $user)
    {
        $this->authorize('updateRole', $board);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,editor,viewer'],
        ]);

        $board->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'User role updated successfully.');
    }

    public function removeMember(Request $request, Board $board, User $user)
    {
        $this->authorize('removeMember', [$board, $user]);

        // You can't remove the board creator
        if ($user->id === $board->created_by) {
            return back()->withErrors(['error' => 'You cannot remove the board creator.']);
        }

        $board->users()->detach($user->id);

        return back()->with('success', 'User removed from the board successfully.');
    }
}
