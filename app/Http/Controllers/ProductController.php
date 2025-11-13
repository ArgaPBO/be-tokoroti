<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Product::query();

        // optional search by product name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->paginate(10);
        return response()->json($products);
    }

    public function indexId($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0'
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0'
        ]);

        $product = Product::findOrFail($id);
        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product
        ], 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ]);
    }
}
