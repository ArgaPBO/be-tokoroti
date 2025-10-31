<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return response()->json(['message' => 'Welcome Admin!']);
    });
    //branch crud
    Route::get('/branches', [BranchController::class, 'index']);
    Route::get('/branches/{id}', [BranchController::class, 'indexId']);
    Route::post('/branches', [BranchController::class, 'store']);
    Route::put('/branches/{id}', [BranchController::class, 'update']);

    //users crud
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'indexId']);
    Route::get('/users/admin', [UserController::class, 'indexAdmins']);
    Route::get('/users/branch/{branch_id}', [UserController::class, 'indexByBranch']);
    Route::get('/users/branch', [UserController::class, 'indexBranch']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    //product crud
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'indexId']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);


    // Route::post('/branches', [BranchController::class, 'store']);
});

// Example: only Employees
Route::middleware(['auth:sanctum', 'role:branch'])->group(function () {
    Route::get('/branch/dashboard', function () {
        return response()->json(['message' => 'Welcome Employee!']);
    });

    // Route::get('/orders', [OrderController::class, 'index']);
});

// Accessible by any authenticated user
// Route::middleware(['auth:sanctum'])->get('/profile', function (Request $request) {
//     return $request->user();
// });
//example
// Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
//     return $request->user();
// });
