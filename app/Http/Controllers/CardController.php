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
        $maxOrder = Card::where('column_id', $column->id)->max('order');
        $column->cards()->create([
            'title' => $request->title,
            'user_id' => Auth::id(),
            'order' => $maxOrder + 1,
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

    public function reorder(Request $request)
    {
        $request->validate([
            'cards' => 'required|array',
            'cards.*.id' => 'required|exists:cards,id',
            'cards.*.order' => 'required|integer',
            'cards.*.column_id' => 'required|exists:columns,id',
        ]);

        foreach ($request->cards as $cardData) {
            Card::where('id', $cardData['id'])
                ->update([
                    'order' => $cardData['order'],
                    'column_id' => $cardData['column_id'],
                ]);
        }

        return back();
    }
}
