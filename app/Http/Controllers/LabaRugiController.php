<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\ProductHistory;
// use App\Models\ExpenseHistory;
// use App\Models\BranchProduct;
// use Illuminate\Support\Facades\DB;

// class LabaRugiController extends Controller
// {
//     public function index(Request $request)
//     {
//         $branchId = $request->input('branch_id');
//         $start = $request->input('start_date');
//         $end = $request->input('end_date');

//         if (!$branchId || !$start || !$end) {
//             return response()->json(['message' => 'branch_id, start_date, and end_date are required'], 422);
//         }

//         /* ------------------------------
//          *  FETCH PRODUCT HISTORIES
//          * ------------------------------ */
//         $products = ProductHistory::with('product')
//             ->where('branch_id', $branchId)
//             ->whereBetween('date', [$start, $end])
//             ->get();

//         // Load branch prices
//         $branchPrices = BranchProduct::where('branch_id', $branchId)
//             ->pluck('branch_price', 'product_id');

//         // Group by product
//         $groupedProducts = $products->groupBy('product_id')->map(function ($rows, $productId) use ($branchPrices) {

//             $branchPrice = $branchPrices[$productId] ?? 0;

//             $totalQty = $rows->sum('quantity');
//             $totalIncome = $totalQty * $branchPrice;

//             $pagi = $rows->where('shift', 'pagi')->sum('quantity');
//             $siang = $rows->where('shift', 'siang')->sum('quantity');

//             $pesanan = $rows->where('transaction_type', 'pesanan')->sum('quantity');
//             $retail = $rows->where('transaction_type', 'retail')->sum('quantity');

//             return [
//                 'product_id' => $productId,
//                 'product_name' => $rows->first()->product->name ?? '-',
//                 'qty' => $totalQty,
//                 'branch_price' => $branchPrice,
//                 'income' => $totalIncome,

//                 'shift_pagi_qty' => $pagi,
//                 'shift_siang_qty' => $siang,

//                 'pesanan_qty' => $pesanan,
//                 'retail_qty' => $retail,
//             ];
//         });

//         $totalProductIncome = $groupedProducts->sum('income');

//         // Add ratio among products & shift/transaction percentages
//         $groupedProducts = $groupedProducts->map(function ($row) use ($totalProductIncome) {

//             $totalQty = $row['qty'];

//             return array_merge($row, [
//                 'ratio_among_products' =>
//                     $totalProductIncome > 0 ? ($row['income'] / $totalProductIncome) * 100 : 0,

//                 'shift_pagi_percent' =>
//                     $totalQty > 0 ? ($row['shift_pagi_qty'] / $totalQty) * 100 : 0,

//                 'shift_siang_percent' =>
//                     $totalQty > 0 ? ($row['shift_siang_qty'] / $totalQty) * 100 : 0,

//                 'pesanan_percent' =>
//                     $totalQty > 0 ? ($row['pesanan_qty'] / $totalQty) * 100 : 0,

//                 'retail_percent' =>
//                     $totalQty > 0 ? ($row['retail_qty'] / $totalQty) * 100 : 0,
//             ]);
//         });


//         /* ------------------------------
//          *  FETCH EXPENSE HISTORIES
//          * ------------------------------ */
//         $expenses = ExpenseHistory::with('expense')
//             ->where('branch_id', $branchId)
//             ->whereBetween('date', [$start, $end])
//             ->get();

//         $groupedExpenses = $expenses->groupBy('expense_id')->map(function ($rows, $expenseId) {

//             $total = $rows->sum('nominal');

//             $pagi = $rows->where('shift', 'pagi')->sum('nominal');
//             $siang = $rows->where('shift', 'siang')->sum('nominal');

//             return [
//                 'expense_id' => $expenseId,
//                 'expense_name' => $rows->first()->expense->name ?? '-',
//                 'total' => $total,
//                 'shift_pagi_total' => $pagi,
//                 'shift_siang_total' => $siang,
//             ];
//         });

//         $totalExpenseOutcome = $groupedExpenses->sum('total');

//         // Add ratios
//         $groupedExpenses = $groupedExpenses->map(function ($row) use ($totalExpenseOutcome) {

//             $sum = $row['total'];

//             return array_merge($row, [
//                 'ratio_among_expenses' =>
//                     $totalExpenseOutcome > 0 ? ($sum / $totalExpenseOutcome) * 100 : 0,

//                 'shift_pagi_percent' =>
//                     $sum > 0 ? ($row['shift_pagi_total'] / $sum) * 100 : 0,

//                 'shift_siang_percent' =>
//                     $sum > 0 ? ($row['shift_siang_total'] / $sum) * 100 : 0,
//             ]);
//         });


//         /* ------------------------------
//          * FINAL TOTAL
//          * ------------------------------ */
//         $net = $totalProductIncome - $totalExpenseOutcome;

//         return response()->json([
//             'products' => $groupedProducts->values(),
//             'expenses' => $groupedExpenses->values(),
//             'total_income' => $totalProductIncome,
//             'total_outcome' => $totalExpenseOutcome,
//             'net' => $net
//         ]);
//     }
// }

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\ProductHistory;
use App\Models\ExpenseHistory;
use App\Models\BranchProduct;

class LabaRugiController extends Controller
{
    public function index(Request $request, $branchId)
    {
        // $branchId = $request->branch_id;
        $start = $request->start_date;
        $end = $request->end_date;

        // Validate
        if (!$start || !$end) {
            return response()->json([
                'message' => 'start_date and end_date are required.'
            ], 422);
        }

        /* ----------------------- PRODUCTS ----------------------- */

        $productHistories = ProductHistory::with('product')
            ->where('branch_id', $branchId)
            ->whereBetween('date', [$start, $end])
            ->get();

        // Load branch prices (important!)
        $branchPrices = BranchProduct::where('branch_id', $branchId)
            ->pluck('branch_price', 'product_id');

        // Group products
        $groupedProducts = $productHistories->groupBy('product_id')->map(function ($items) use ($branchPrices) {

            $product = $items->first()->product;
            $branchPrice = $branchPrices[$product->id] ?? $product->price;

            $totalQty = $items->sum('quantity');
            $income = $items->sum(function ($i) use ($branchPrice) {

                // apply percent discount first
                if (!is_null($i->discount_percent)) {
                    $disc = ($branchPrice * ($i->discount_percent / 100));
                    $finalUnit = $branchPrice - $disc;
                    return $finalUnit * $i->quantity;
                }

                // else apply price discount
                if (!is_null($i->discount_price)) {
                    $finalUnit = $branchPrice - $i->discount_price;
                    return $finalUnit * $i->quantity;
                }

                // otherwise no discount
                return $branchPrice * $i->quantity;
            });

            // Percent split: shift
            $pagi = $items->where('shift', 'pagi')->sum('quantity');
            $siang = $items->where('shift', 'siang')->sum('quantity');
            $totalShift = max($pagi + $siang, 1);

            // Percent split: transaction type
            $retail = $items->where('transaction_type', 'retail')->sum('quantity');
            $pesanan = $items->where('transaction_type', 'pesanan')->sum('quantity');
            $totalType = max($retail + $pesanan, 1);

            return [
                'product_id' => $product->id,
                'name'       => $product->name,
                'quantity'   => $totalQty,
                'branch_price' => $branchPrice,
                'income'     => $income,

                'shift_percent' => [
                    'pagi' => round(($pagi / $totalShift) * 100, 2),
                    'siang' => round(($siang / $totalShift) * 100, 2),
                ],

                'transaction_percent' => [
                    'retail' => round(($retail / $totalType) * 100, 2),
                    'pesanan' => round(($pesanan / $totalType) * 100, 2),
                ]
            ];
        })->values();


        $totalIncome = $groupedProducts->sum('income');


        /* ----------------------- EXPENSES ----------------------- */

        $expenseHistories = ExpenseHistory::with('expense')
            ->where('branch_id', $branchId)
            ->whereBetween('date', [$start, $end])
            ->get();

        $groupedExpenses = $expenseHistories->groupBy('expense_id')->map(function ($items) {

            $expense = $items->first()->expense;
            $nominal = $items->sum('nominal');

            $pagi = $items->where('shift', 'pagi')->sum('nominal');
            $siang = $items->where('shift', 'siang')->sum('nominal');
            $totalShift = max($pagi + $siang, 1);

            return [
                'expense_id' => $expense->id,
                'name'       => $expense->name,
                'nominal'    => $nominal,
                'shift_percent' => [
                    'pagi' => round(($pagi / $totalShift) * 100, 2),
                    'siang' => round(($siang / $totalShift) * 100, 2),
                ]
            ];
        })->values();

        $totalOutcome = $groupedExpenses->sum('nominal');


        /* ------------------ OVERALL PERCENTAGES ------------------ */

        // Shift totals for products
        $pagiProd = $productHistories->where('shift', 'pagi')->sum('quantity');
        $siangProd = $productHistories->where('shift', 'siang')->sum('quantity');
        $totalProdShift = max($pagiProd + $siangProd, 1);

        // Transaction type totals for products
        $retailTotal = $productHistories->where('transaction_type', 'retail')->sum('quantity');
        $pesananTotal = $productHistories->where('transaction_type', 'pesanan')->sum('quantity');
        $totalTypes = max($retailTotal + $pesananTotal, 1);

        // Shift totals for expenses
        $pagiExp = $expenseHistories->where('shift', 'pagi')->sum('nominal');
        $siangExp = $expenseHistories->where('shift', 'siang')->sum('nominal');
        $totalExpShift = max($pagiExp + $siangExp, 1);


        return response()->json([
            'branch_name' => Branch::find($branchId)->name ?? '-',
            'products' => $groupedProducts,
            'expenses' => $groupedExpenses,

            'total_income'  => $totalIncome,
            'total_outcome' => $totalOutcome,
            'total'         => $totalIncome - $totalOutcome,

            'overall_products_shift' => [
                'pagi' => round(($pagiProd / $totalProdShift) * 100, 2),
                'siang' => round(($siangProd / $totalProdShift) * 100, 2),
            ],

            'overall_products_transaction_type' => [
                'retail' => round(($retailTotal / $totalTypes) * 100, 2),
                'pesanan' => round(($pesananTotal / $totalTypes) * 100, 2),
            ],

            'overall_expenses_shift' => [
                'pagi' => round(($pagiExp / $totalExpShift) * 100, 2),
                'siang' => round(($siangExp / $totalExpShift) * 100, 2),
            ]
        ]);
    }
}
