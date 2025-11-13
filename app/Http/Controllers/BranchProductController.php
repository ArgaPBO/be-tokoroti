<?php

namespace App\Http\Controllers;

use App\Models\BranchProduct;
use Illuminate\Http\Request;

class BranchProductController extends Controller
{
    //
    public function index(Request $request, $branchId)
    {
        $query = BranchProduct::with('product')->where('branch_id', $branchId);

        // optional search by product name (name lives on the related Product)
        if ($search = $request->input('search')) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(10);
        return response()->json($products);
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
