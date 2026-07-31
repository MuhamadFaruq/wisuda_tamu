<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ush.ac.id'],
            ['name' => 'Admin Registrasi', 'username' => 'admin', 'password' => 'Admin123!']
        );
    }
}
