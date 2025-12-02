<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a paginated list of users.
     */
    public function index(Request $request)
    {
        $query = User::with('branch:id,name')->orderBy('branch_id','asc');

        // optional search by name or username
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $role = $request->input('role');
            if ($role === 'admin') {
                $query->whereNull('branch_id');
            } elseif ($role === 'branch') {
                $query->whereNotNull('branch_id');
            }
        }

        $users = $query->paginate(10);
        return response()->json($users);
    }

    // public function indexId($id)
    // {
    //     $user = User::findOrFail($id);
    //     return response()->json($user);
    // }

    // Get all admins (users with null branch_id)
    // public function indexAdmins(Request $request)
    // {
    //     $query = User::with('branch:id,name')->whereNull('branch_id');

    //     if ($search = $request->input('search')) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //             ->orWhere('username', 'like', "%{$search}%");
    //         });
    //     }

    //     $admins = $query->paginate(10);
    //     return response()->json($admins);
    // }

    // Get users of a specific branch
    public function indexByBranch(Request $request, $branch_id)
    {
        $query = User::with('branch:id,name')->where('branch_id', $branch_id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10);
        return response()->json($users);
    }

    // Get branch users
    public function indexBranch(Request $request)
    {
        $query = User::with('branch:id,name')->whereNotNull('branch_id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10);
        return response()->json($users);
    }

    /**
     * Store a newly created user (either admin or branch employee).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully.',
            'data' => $user
        ], 201);
    }

    /**
     * Display a specific user.
     */
    public function show($id)
    {
        $user = User::with('branch:id,name')->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Remove branch_id if it exists in request
        if ($request->has('branch_id')) {
            unset($validated['branch_id']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $user
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deletion if it's the last admin
        if ($user->branch_id === null) {
            $adminCount = User::whereNull('branch_id')->count();
            if ($adminCount === 1) {
                return response()->json([
                    'message' => 'Cannot delete the last admin user.'
                ], 422);
            }
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }
}
