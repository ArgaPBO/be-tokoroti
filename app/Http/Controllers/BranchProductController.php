<?php

namespace App\Http\Controllers;

use App\Models\BranchProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchProductController extends Controller
{
    //
    public function index(Request $request, $branchId)
{
    $start = $request->input('start_date', now()->startOfMonth()->toDateString());
    $end   = $request->input('end_date', now()->endOfMonth()->toDateString());
    $search = $request->input('search');

    // SUBQUERY: summarize histories
    $historyAgg = DB::table('product_histories')
        ->select(
            'product_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM( (product_price 
                        - CASE 
                            WHEN discount_percent IS NOT NULL THEN product_price * (discount_percent / 100)
                            WHEN discount_price IS NOT NULL THEN discount_price
                            ELSE 0 
                        END
                      ) * quantity ) as total_revenue'),

            DB::raw("AVG(CASE WHEN transaction_type='retail' THEN 1 ELSE 0 END)*100 as retail_percent"),
            DB::raw("AVG(CASE WHEN transaction_type='pesanan' THEN 1 ELSE 0 END)*100 as pesanan_percent"),
            DB::raw("AVG(CASE WHEN shift='pagi' THEN 1 ELSE 0 END)*100 as pagi_percent"),
            DB::raw("AVG(CASE WHEN shift='siang' THEN 1 ELSE 0 END)*100 as siang_percent")
        )
        ->where('branch_id', $branchId)
        ->whereBetween('date', [$start, $end])
        ->groupBy('product_id');

    // BASE QUERY
    $query = BranchProduct::with('product')
        ->leftJoinSub($historyAgg, 'agg', function($join){
            $join->on('branch_products.product_id', '=', 'agg.product_id');
        })
        ->where('branch_products.branch_id', $branchId)
        ->select(
            'branch_products.*',
            DB::raw('COALESCE(agg.total_quantity,0) as total_quantity'),
            DB::raw('COALESCE(agg.total_revenue,0) as total_revenue'),
            DB::raw('COALESCE(agg.retail_percent,0) as retail_percent'),
            DB::raw('COALESCE(agg.pesanan_percent,0) as pesanan_percent'),
            DB::raw('COALESCE(agg.pagi_percent,0) as pagi_percent'),
            DB::raw('COALESCE(agg.siang_percent,0) as siang_percent')
        )
        ->orderByDesc('total_quantity');

    if ($search) {
        $query->whereHas('product', fn($q)=> $q->where('name', 'like', "%{$search}%"));
    }

    return $query->paginate(10);
}




    // get a single branch-product by branch_id and product_id
    public function indexId($branchId, $productId)
    {
        $branchProduct = BranchProduct::with('product')
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->firstOrFail();

        return response()->json($branchProduct);
    }

    // create a branch-product for a given branch (use firstOrCreate because branch_id+product_id is unique)
    public function store(Request $request, $branchId)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            // BranchProduct only has branch_price (no stock)
            'branch_price' => 'nullable|numeric',
        ]);

        $attributes = [
            'branch_id' => $branchId,
            'product_id' => $validated['product_id'],
        ];

        $values = [
            'branch_price' => $validated['branch_price'] ?? null,
        ];

        $branchProduct = BranchProduct::firstOrCreate($attributes, $values);

        // if it already existed but a branch_price was provided and differs, update it
        if (! $branchProduct->wasRecentlyCreated && array_key_exists('branch_price', $validated)) {
            if ($branchProduct->branch_price !== $validated['branch_price']) {
                $branchProduct->update(['branch_price' => $validated['branch_price']]);
                $message = 'Branch product existed; branch_price updated.';
            } else {
                $message = 'Branch product already existed.';
            }
            $status = 200;
        } else {
            $message = 'Branch product created successfully.';
            $status = 201;
        }

        return response()->json([
            'message' => $message,
            'data' => $branchProduct->load('product')
        ], $status);
    }

    // update a branch-product identified by branch_id and product_id
    public function update(Request $request, $branchId, $productId)
    {
        $validated = $request->validate([
            // only branch_price is relevant here
            'branch_price' => 'nullable|numeric',
        ]);

        $branchProduct = BranchProduct::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->firstOrFail();

        $branchProduct->update($validated);

        return response()->json([
            'message' => 'Branch product updated successfully.',
            'data' => $branchProduct->load('product')
        ], 200);
    }

    // delete a branch-product by branch_id and product_id
    public function destroy($branchId, $productId)
    {
        $branchProduct = BranchProduct::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->firstOrFail();

        $branchProduct->delete();

        return response()->json([
            'message' => 'Branch product deleted successfully.'
        ]);
    }
}
