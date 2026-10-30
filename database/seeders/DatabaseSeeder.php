<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'Administrator',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ],
        );
    }
}
