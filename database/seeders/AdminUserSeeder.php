<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 5; $i++) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin' . $i . '@gmail.com',
                'password' => '123456',
                'role' => 'admin',
            ]);
        }
    }
}
