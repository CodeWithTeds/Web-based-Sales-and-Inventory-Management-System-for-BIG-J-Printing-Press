<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]);
            
            echo "Admin user created successfully!\n";
            echo "Username: admin\n";
            echo "Password: admin\n";
        } else {
            echo "Admin user already exists.\n";
        }
    }
}