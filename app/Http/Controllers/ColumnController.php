<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    # Store a new column
    public function store(Request $request, Board $board)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxOrder = Column::where('board_id', $board->id)->max('order');
        $column = $board->columns()->create([
            'name' => $request->name,
            'order' => $maxOrder + 1
        ]);

        broadcast(new \App\Events\ColumnCreated($column))->toOthers();

        return redirect()->back()->with('success', 'Column created successfully.');
    }

    public function update(Request $request, Board $board, Column $column)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $column->update([
            'name' => $request->name,
        ]);

        broadcast(new \App\Events\ColumnUpdated($column))->toOthers();

        return redirect()->back()->with('success', 'Column updated successfully.');
    }

    public function destroy(Board $board, Column $column)
    {
        broadcast(new \App\Events\ColumnDeleted($column))->toOthers();

        $column->delete();

        return redirect()->back()->with('success', 'Column deleted successfully.');
    }

    public function reorder(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:columns,id',
        ]);

        foreach ($validated['order'] as $index => $columnId) {
            Column::where('id', $columnId)
                ->where('board_id', $board->id)
                ->update(['order' => $index + 1]);
        }

        broadcast(new \App\Events\ColumnReordered($validated['order'], $board->id))->toOthers();
        // return something clean for Inertia
        return redirect()->back()->with('success', 'Column updated successfully.');
    }
}
