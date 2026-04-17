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




// AUTHENTICATION
Route::post('/login', [ApiAuthController::class, 'login'])
    ->middleware('guest')
    ->name('login');

Route::post('/logout', [ApiAuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('logout');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// ADMIN/BRANCH
Route::middleware('auth:sanctum')->group(function () {
    // Get product
    Route::get('/products', [ProductController::class, 'index']); //Get (paginated)
    Route::get('/products/{id}', [ProductController::class, 'indexId']); //Get id
    // Get expense
    Route::get('/expenses', [ExpenseController::class, 'index']); //Get (paginated)
    Route::get('/expenses/{id}', [ExpenseController::class, 'indexId']); //Get id
});

// ADMIN
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Get login data
    Route::get('/admin', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => $user
        ]);
    });

    // Dashboard 
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

    // Branches
    Route::get('/branches', [BranchController::class, 'index']); //Get (paginated)
    Route::get('/branchesall', [BranchController::class, 'indexAll']); //Get all (not paginated)
    Route::get('/branches/{id}', [BranchController::class, 'indexId']); //Get id
    Route::post('/branches', [BranchController::class, 'store']); //Create
    Route::put('/branches/{id}', [BranchController::class, 'update']);//Update
    Route::delete('/branches/{id}', [BranchController::class, 'destroy']);//Delete

    // Users
    Route::get('/users/branch/{branch_id}', [UserController::class, 'indexByBranch']);//Get by branch
    Route::get('/users/branch', [UserController::class, 'indexBranch']);//Get branch users (without admin users)
    Route::get('/users', [UserController::class, 'index']);//Get all (can be filtered by role (lol idk why i needed the above one))
    Route::get('/users/{id}', [UserController::class, 'show']);//Get by id
    // Route::get('/users/admin', [UserController::class, 'indexAdmins']);
    Route::post('/users', [UserController::class, 'store']);//Create
    Route::put('/users/{id}', [UserController::class, 'update']);//Update
    Route::delete('/users/{id}', [UserController::class, 'destroy']);//Delete

    // Products (get is above)
    Route::post('/products', [ProductController::class, 'store']); //Create
    Route::put('/products/{id}', [ProductController::class, 'update']); //Update
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); //Delete
    
    // Expenses
    Route::post('/expenses', [ExpenseController::class, 'store']); //Create
    Route::put('/expenses/{id}', [ExpenseController::class, 'update']); //Update
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']); //Delete

    // Branch products
    Route::get('/branches/{branchId}/products', [BranchProductController::class, 'index']);//Get (paginated)
    Route::get('/branches/{branchId}/products/{productId}', [BranchProductController::class, 'indexId']); //Get by product id
    Route::post('/branches/{branchId}/products', [BranchProductController::class, 'store']);//Create
    Route::put('/branches/{branchId}/products/{productId}', [BranchProductController::class, 'update']);//Update
    Route::delete('/branches/{branchId}/products/{productId}', [BranchProductController::class, 'destroy']);//Delete

    // branch expenses
    Route::get('/branches/{branchId}/expenses', [ExpenseController::class, 'indexBranch']);

    // laba rugi by branch
    Route::get('/branches/{branchId}/labarugi', [LabaRugiController::class, 'index']);
});

// BRANCH
Route::middleware(['auth:sanctum', 'role:branch'])->group(function () {
    // login data and branch data
    Route::get('/branch', function (Request $request) {
        $user = $request->user();
        $branch = \App\Models\Branch::find($user->branch_id);

        return response()->json([
            'user' => $user,
            'branch' => $branch
        ]);
    });

    // Branch dashboard
    Route::get('/branch/dashboard', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return response()->json([
        'branch_product_count' => \App\Models\BranchProduct::where('branch_id', $branchId)->count(),

        'branch_product_history_count' => \App\Models\ProductHistory::where('branch_id', $branchId)//why product history not branch producct
            ->whereYear('date', now()->format('Y'))
            ->whereMonth('date', now()->format('m'))
            ->sum('quantity'),

        'branch_expense_history_count' => \App\Models\ExpenseHistory::where('branch_id', $branchId)
            ->whereYear('date', now()->format('Y'))
            ->whereMonth('date', now()->format('m'))
            ->sum('nominal')
    ]);
    });

    // laba rugi by branch
    Route::get('/branch/labarugi', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(LabaRugiController::class)->index($request, $branchId);
    });

    // Branch products
    Route::get('branch/products', function (Request $request) { //Get branch products of branch
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->index($request, $branchId);
    });
    Route::get('branch/products/{productId}', function (Request $request, $productId) { //Get by product id
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->indexId($branchId, $productId);
    });
    Route::post('branch/products', function (Request $request) { //Create
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->store($request, $branchId);
    });
    Route::put('branch/products/{productId}', function (Request $request, $productId) { //Update
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->update($request, $branchId, $productId);
    });
    Route::delete('branch/products/{productId}', function (Request $request, $productId) { //Delete
        $branchId = $request->user()->branch_id;
        return app(BranchProductController::class)->destroy($branchId, $productId);
    });

    // Branch expenses
    Route::get('branch/expenses', function (Request $request) {
        $branchId = $request->user()->branch_id;
        return app(ExpenseController::class)->indexBranch($request, $branchId);
    });

    //product transaction history
    Route::get('branch/histories/products', function (Request $request) { //Get branch's product history
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->productIndex($request, $branchId);
    });
    Route::post('branch/histories/products', function (Request $request) { //Create
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->productStore($request, $branchId);
    });
    Route::delete('branch/histories/products/{id}', function (Request $request, $id) { //Delete
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->productDestroy($branchId, $id);
    });

    //expense transaction history
    Route::get('branch/histories/expenses', function (Request $request) { //Get branch's expense history
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->expenseIndex($request, $branchId);
    });
    Route::post('branch/histories/expenses', function (Request $request) { //Create
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->expenseStore($request, $branchId);
    });
    Route::delete('branch/histories/expenses/{id}', function (Request $request, $id) { //Delete
        $branchId = $request->user()->branch_id;
        return app(HistoryController::class)->expenseDestroy($branchId, $id);
    });

});

