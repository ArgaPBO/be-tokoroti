<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductHistory;
use App\Models\Product;
use App\Models\BranchProduct;
use App\Models\Expense;
use App\Models\ExpenseHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HistoryController extends Controller
{
    // Product transaction history for branch-scoped endpoints
    public function productIndex(Request $request, $branchId)
    {
        $query = ProductHistory::with('product')->where('branch_id', $branchId);

        if ($productName = $request->input('product_name')) {
            $query->whereHas('product', function ($q) use ($productName) {
                $q->where('name', 'like', "%{$productName}%");
            });
        }

        if ($type = $request->input('transaction_type')) {
            $query->where('transaction_type', $type);
        }

        if ($shift = $request->input('shift')) {
            $query->where('shift', $shift);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('date', '<=', $to);
        }

        $histories = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json($histories);
    }

    public function productStore(Request $request, $branchId)
    {
        // Accept either a root array of items or an 'items' key with an array.
        $payload = $request->all();

        if (isset($payload[0]) && is_array($payload[0])) {
            $items = $payload;
        } elseif ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
        } else {
            // single object -> wrap into array for uniform processing
            $items = [$payload];
        }

        // basic check: items must be a non-empty array
        if (! is_array($items) || count($items) === 0) {
            return response()->json(['errors' => ['items' => ['No items provided']]], 422);
        }

        $created = [];
        $errors = [];

        // rules for a single item (we validate per-item so we can collect per-item errors)
        $itemRules = [
            'date' => 'required|date',
            'product_name' => 'required|string',
            'quantity' => 'required|integer',
            'discount_percent' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric',
            'transaction_type' => 'required|string|in:pesanan,retail',
            'shift' => 'required|string|in:pagi,siang',
        ];

        foreach ($items as $index => $item) {
            $validator = Validator::make($item, $itemRules);
            if ($validator->fails()) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // normalize product name
            $productName = trim($item['product_name']);

            // case-insensitive product lookup
            $product = Product::whereRaw('LOWER(name) = ?', [strtolower($productName)])->first();
            if (! $product) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'errors' => ["Product not found: {$item['product_name']}"],
                ];
                continue;
            }

            $branchProduct = BranchProduct::where('branch_id', $branchId)
                ->where('product_id', $product->id)
                ->first();

            if (! $branchProduct) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'errors' => ["Branch price not found for product: {$item['product_name']}"],
                ];
                continue;
            }

            $product_price = $branchProduct->branch_price;

            $discount_percent = isset($item['discount_percent']) && $item['discount_percent'] !== null && $item['discount_percent'] !== ''
                ? $item['discount_percent']
                : null;

            $discount_price = null;
            $discount_price = isset($item['discount_price']) && $item['discount_price'] !== null && $item['discount_price'] !== ''
                ? $item['discount_price']
                : null;
            

            $data = [
                'date' => $item['date'],
                'branch_id' => $branchId,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'product_price' => $product_price,
                'discount_percent' => $discount_percent,
                'discount_price' => $discount_price,
                'transaction_type' => $item['transaction_type'],
                'shift' => $item['shift'],
            ];

            try {
                DB::transaction(function () use ($data, &$created) {
                    $created[] = ProductHistory::create($data);
                });
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        $summary = [
            'inserted' => count($created),
            'errors_count' => count($errors),
            'errors' => $errors,
            'data' => $created,
        ];

        $status = count($created) > 0 ? 201 : 422;

        return response()->json($summary, $status);
    }

    public function productDestroy($branchId, $id)
    {
        $history = ProductHistory::where('id', $id)->where('branch_id', $branchId)->firstOrFail();

        $history->delete();

        return response()->json(['message' => 'Product history deleted']);
    }

    // Expense transaction history for branch-scoped endpoints
    public function expenseIndex(Request $request, $branchId)
    {
        $query = ExpenseHistory::with('expense')->where('branch_id', $branchId);

        if ($expenseName = $request->input('expense_name')) {
            $query->whereHas('expense', function ($q) use ($expenseName) {
                $q->where('name', 'like', "%{$expenseName}%");
            });
        }

        if ($shift = $request->input('shift')) {
            $query->where('shift', $shift);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('date', '<=', $to);
        }

        $histories = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json($histories);
    }

    public function expenseStore(Request $request, $branchId)
    {
        // Accept either a root array of items or an 'items' key with an array.
        $payload = $request->all();

        if (isset($payload[0]) && is_array($payload[0])) {
            $items = $payload;
        } elseif ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
        } else {
            $items = [$payload];
        }

        if (! is_array($items) || count($items) === 0) {
            return response()->json(['errors' => ['items' => ['No items provided']]], 422);
        }

        $created = [];
        $errors = [];

        $itemRules = [
            'date' => 'required|date',
            'expense_name' => 'required|string',
            'nominal' => 'required|numeric',
            'description' => 'nullable|string',
            'shift' => 'required|string|in:pagi,siang',
        ];

        foreach ($items as $index => $item) {
            $validator = Validator::make($item, $itemRules);
            if ($validator->fails()) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            $expenseName = trim($item['expense_name']);
            $expense = Expense::whereRaw('LOWER(name) = ?', [strtolower($expenseName)])->first();
            if (! $expense) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'errors' => ["Expense not found: {$item['expense_name']}"],
                ];
                continue;
            }

            $data = [
                'date' => $item['date'],
                'branch_id' => $branchId,
                'expense_id' => $expense->id,
                'nominal' => $item['nominal'],
                'description' => $item['description'] ?? null,
                'shift' => $item['shift'],
            ];

            try {
                DB::transaction(function () use ($data, &$created) {
                    $created[] = ExpenseHistory::create($data);
                });
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        $summary = [
            'inserted' => count($created),
            'errors_count' => count($errors),
            'errors' => $errors,
            'data' => $created,
        ];

        $status = count($created) > 0 ? 201 : 422;

        return response()->json($summary, $status);
    }

    public function expenseDestroy($branchId, $id)
    {
        $history = ExpenseHistory::where('id', $id)->where('branch_id', $branchId)->firstOrFail();

        $history->delete();

        return response()->json(['message' => 'Expense history deleted']);
    }
}
