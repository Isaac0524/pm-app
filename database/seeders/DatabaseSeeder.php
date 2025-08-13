<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Manager principal
        User::create([
            'name' => 'Manager Principal',
            'email' => 'manager@pm-app.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);

        // Membres de l'équipe
        $members = [
            ['name' => 'Alice Dupont', 'email' => 'alice@pm-app.com'],
            ['name' => 'Bob Martin', 'email' => 'bob@pm-app.com'],
            ['name' => 'Caroline Leroy', 'email' => 'caroline@pm-app.com'],
            ['name' => 'David Bernard', 'email' => 'david@pm-app.com'],
        ];

        foreach ($members as $member) {
            User::create([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => Hash::make('password123'),
                'role' => 'member',
                'email_verified_at' => now(),
            ]);
        }
    }
}
