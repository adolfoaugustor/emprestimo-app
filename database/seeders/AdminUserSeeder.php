<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Adolfo Augusto',
                'email' => 'adolfoaugustor@gmail.com',
                'password' => Hash::make('augustod2'),
                'user_type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'name' => 'Everton Rodrigues',
                'email' => 'admin@admin.com.br',
                'password' => Hash::make('@2024Everton'),
                'user_type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        User::insert($users);
    }
}
