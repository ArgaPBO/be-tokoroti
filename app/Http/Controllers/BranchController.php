<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Branch::query();

        // optional search by branch name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $branches = $query->paginate(10);
        return response()->json($branches);
    }
    public function indexAll(Request $request)
    {
        $query = Branch::query();

        // optional search by branch name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $branches = $query->get();
        return response()->json($branches);
    }
    public function indexId($id)
    {
        $branch = Branch::findOrFail($id);
        return response()->json($branch);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $branch = Branch::create($validated);
        return response()->json([
            'message' => 'Branch created successfully.',
            'data' => $branch
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $branch = Branch::findOrFail($id);
        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully.',
            'data' => $branch
        ], 200);
    }

}
