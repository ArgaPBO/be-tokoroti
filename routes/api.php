<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchProductController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LabaRugiController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function Symfony\Component\Clock\now;

Route::post('/login', [ApiAuthController::class, 'login'])
    ->middleware('guest')
    ->name('login');

Route::post('/logout', [ApiAuthController::class, 'logout'])
->middleware('auth:sanctum')
->name('logout');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Example: only Admins
//buat dashboard admin mengambil data jumlah branch, jumlah user, jumlah product dll.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'indexId']);
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::get('/expenses/{id}', [ExpenseController::class, 'indexId']);
});
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => $user
        ]);
    });
    Route::get('/admin/dashboard', function () {
        
        return response()->json([
        'products_count' => \App\Models\Product::count(),

        'branches_count' => \App\Models\Branch::count(),

        'branch_product_history_quantity_sum' => \App\Models\ProductHistory::whereYear('date', now()->format('Y'))
            ->whereMonth('date', now()->format('m'))
            ->sum('quantity'),

        'branch_expense_history_nominal_sum' => \App\Models\ExpenseHistory::whereYear('date', now()->format('Y'))
            ->whereMonth('date', now()->format('m'))
            ->sum('nominal')
    ]);
    });
    //branch crud
    Route::get('/branches', [BranchController::class, 'index']);
    Route::get('/branchesall', [BranchController::class, 'indexAll']);
    Route::get('/branches/{id}', [BranchController::class, 'indexId']);
    Route::post('/branches', [BranchController::class, 'store']);
    Route::put('/branches/{id}', [BranchController::class, 'update']);
    Route::delete('/branches/{id}', [BranchController::class, 'destroy']);

    //users crud
    Route::get('/users/branch/{branch_id}', [UserController::class, 'indexByBranch']);
    Route::get('/users/branch', [UserController::class, 'indexBranch']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    // Route::get('/users/admin', [UserController::class, 'indexAdmins']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    //product crud
    // Route::get('/products', [ProductController::class, 'index']);
    // Route::get('/products/{id}', [ProductController::class, 'indexId']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    //expense crud
    // Route::get('/expenses', [ExpenseController::class, 'index']);
    // Route::get('/expenses/{id}', [ExpenseController::class, 'indexId']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);

    Route::get('/branches/{branchId}/products', [BranchProductController::class, 'index']);
    Route::get('/branches/{branchId}/products/{productId}', [BranchProductController::class, 'indexId']);
    Route::post('/branches/{branchId}/products', [BranchProductController::class, 'store']);
    Route::put('/branches/{branchId}/products/{productId}', [BranchProductController::class, 'update']);
    Route::delete('/branches/{branchId}/products/{productId}', [BranchProductController::class, 'destroy']);

    Route::get('/branches/{branchId}/expenses', [ExpenseController::class, 'indexBranch']);

    // Route::post('/branches', [BranchController::class, 'store']);
    Route::get('/branches/{branchId}/labarugi', [LabaRugiController::class, 'index']);
});

// Example: only Employees
Route::middleware(['auth:sanctum', 'role:branch'])->group(function () {
    Route::get('/branch', function (Request $request) {
        $user = $request->user();
        $branch = \App\Models\Branch::find($user->branch_id);

        return response()->json([
            'user' => $user,
            'branch' => $branch
        ]);
    });

    Route::get('/branch/dashboard', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return response()->json([
        'branch_product_count' => \App\Models\BranchProduct::where('branch_id', $branchId)->count(),

        'branch_product_history_count' => \App\Models\ProductHistory::where('branch_id', $branchId)
            ->whereYear('date', now()->format('Y'))
            ->whereMonth('date', now()->format('m'))
            ->sum('quantity'),

        'branch_expense_history_count' => \App\Models\ExpenseHistory::where('branch_id', $branchId)
            ->whereYear('date', now()->format('Y'))
            ->whereMonth('date', now()->format('m'))
            ->sum('nominal')
    ]);
    });

    Route::get('/branch/labarugi', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(LabaRugiController::class)->index($request, $branchId);
    });

    // Route::get('/orders', [OrderController::class, 'index']);
// });


//buat rute berdasarkan id branch yang login untuk branch.
//buat rute branch yang mengambil data branch seperti nama etc. dan ambil data untuk dashboard.

// Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    
//     // Route::get('/products/{id}', [ProductController::class, 'indexId']);
//     // Route::post('/products', [ProductController::class, 'store']);
//     // Route::put('/products/{id}', [ProductController::class, 'update']);
//     // Route::delete('/products/{id}', [ProductController::class, 'destroy']);
// });

// Route::middleware(['auth:sanctum', 'role:branch'])->group(function () {
    // For branch users, derive the branch id from the authenticated user's branch
    // and call the controller methods so clients only need to specify product ids.
    Route::get('branch/products', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->index($request, $branchId);
    });
    
    Route::get('branch/expenses', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(ExpenseController::class)->indexBranch($request, $branchId);
    });

    Route::get('branch/products/{productId}', function (Request $request, $productId) {
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->indexId($branchId, $productId);
    });

    Route::post('branch/products', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->store($request, $branchId);
    });

    Route::put('branch/products/{productId}', function (Request $request, $productId) {
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->update($request, $branchId, $productId);
    });

    Route::delete('branch/products/{productId}', function (Request $request, $productId) {
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->destroy($branchId, $productId);
    });

    //product transaction history
    Route::get('branch/histories/products', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->productIndex($request, $branchId);
    });
    Route::post('branch/histories/products', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->productStore($request, $branchId);
    });
    Route::delete('branch/histories/products/{id}', function (Request $request, $id) {
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->productDestroy($branchId, $id);
    });

    //expense transaction history
    Route::get('branch/histories/expenses', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->expenseIndex($request, $branchId);
    });
    Route::post('branch/histories/expenses', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->expenseStore($request, $branchId);
    });
    Route::delete('branch/histories/expenses/{id}', function (Request $request, $id) {
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->expenseDestroy($branchId, $id);
    });

});

// Accessible by any authenticated user
// Route::middleware(['auth:sanctum'])->get('/profile', function (Request $request) {
//     return $request->user();
// });
//example
// Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
//     return $request->user();
// });
