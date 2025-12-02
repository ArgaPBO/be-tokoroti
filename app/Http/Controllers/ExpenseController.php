<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function indexBranch(Request $request, $branchId)
{
    $start = $request->input('start_date', now()->startOfMonth()->toDateString());
    $end   = $request->input('end_date', now()->endOfMonth()->toDateString());
    $search = $request->input('search');

    $query = ExpenseHistory::with('expense')
        ->select(
            'expense_id',
            DB::raw('SUM(nominal) as total_nominal'),
            DB::raw("AVG(CASE WHEN shift = 'pagi' THEN 1 ELSE 0 END) * 100 as pagi_percent"),
            DB::raw("AVG(CASE WHEN shift = 'siang' THEN 1 ELSE 0 END) * 100 as siang_percent")
        )
        ->where('branch_id', $branchId)
        ->whereBetween('date', [$start, $end])
        ->groupBy('expense_id')
        ->orderByDesc('total_nominal');

    if ($search) {
        $query->whereHas('expense', fn($q)=> 
            $q->where('name', 'like', "%{$search}%")
        );
    }

    return $query->paginate(10);
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
