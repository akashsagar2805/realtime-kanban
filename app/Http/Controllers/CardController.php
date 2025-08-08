<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'column_id' => 'required|exists:columns,id',
        ]);

        $column = Column::findOrFail($request->column_id);

        $card = $column->cards()->create([
            'title' => $request->title,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with(['success' => 'Card created successfully!']);
    }

    public function update(Request $request, Card $card)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $card->update([
            'title' => $request->title,
        ]);

        return redirect()->back()->with(['success' => 'Card updated successfully!']);
    }

    public function destroy(Card $card)
    {
        $card->delete();

        return redirect()->back()->with(['success' => 'Card deleted successfully!']);
    }
}
