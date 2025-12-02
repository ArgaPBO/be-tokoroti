<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing foreign keys and recreate them with onDelete('cascade')
        
        // branch_products table
        if (DB::getDriverName() === 'mysql') {
            Schema::table('branch_products', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('branch_products', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            });
        }

        // users table
        if (DB::getDriverName() === 'mysql') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            });
        }

        // product_histories table
        if (DB::getDriverName() === 'mysql') {
            Schema::table('product_histories', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('product_histories', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            });
        }

        // expense_histories table
        if (DB::getDriverName() === 'mysql') {
            Schema::table('expense_histories', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('expense_histories', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to non-cascading foreign keys (optional, for rollback)
        if (DB::getDriverName() === 'mysql') {
            Schema::table('branch_products', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('branch_products', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches');
            });

            Schema::table('product_histories', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('product_histories', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches');
            });

            Schema::table('expense_histories', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
            Schema::table('expense_histories', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches');
            });
        }
    }
};
