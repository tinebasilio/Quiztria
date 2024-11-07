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
        User::create([
            'name' => 'Admin',
            'email' => 'quiztria@gmail.com',
            'password' => Hash::make('QuiztriaAdmin'), // Secure this in production
            'is_admin' => 1, // Assuming 1 is for admin based on your setup
        ]);
    }
}

