<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $boards = $request->user()
            ->boards()
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

        $board->load('columns.cards');

        return Inertia::render('Boards/Show', [
            'board' => $board
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

}
