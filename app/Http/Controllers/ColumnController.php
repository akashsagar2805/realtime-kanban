<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $request, Board $board)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $board->columns()->create([
            'name' => $request->name,
        ]);

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

        return redirect()->back()->with('success', 'Column updated successfully.');
    }

    public function destroy(Board $board, Column $column)
    {
        $column->delete();

        return redirect()->back()->with('success', 'Column deleted successfully.');
    }
}