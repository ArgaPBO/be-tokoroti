<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Create a branch
        $branch = Branch::create([
            'name' => 'Main Branch',
        ]);

        // Create admin (no branch)
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'branch_id' => null,
        ]);

        // Create employee (belongs to branch)
        User::create([
            'name' => 'Employee User',
            'username' => 'employee',
            'password' => Hash::make('employee123'),
            'branch_id' => $branch->id,
        ]);
    }
}
