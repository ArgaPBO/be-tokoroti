<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $expenses = $query->paginate(10);
        return response()->json($expenses);
    }

    public function indexId($id)
    {
        $expense = Expense::findOrFail($id);
        return response()->json($expense);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $expense = Expense::create($validated);

        return response()->json([
            'message' => 'Expense created successfully.',
            'data'    => $expense
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update($validated);

        return response()->json([
            'message' => 'Expense updated successfully.',
            'data'    => $expense
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully.'
        ]);
    }
}
