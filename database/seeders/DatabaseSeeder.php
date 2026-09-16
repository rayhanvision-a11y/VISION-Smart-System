<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@isp.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'NOC Engineer',
            'email' => 'noc@isp.com',
            'password' => Hash::make('password'),
            'role' => 'noc',
        ]);

        User::create([
            'name' => 'Reseller One',
            'email' => 'reseller@isp.com',
            'password' => Hash::make('password'),
            'role' => 'reseller',
        ]);
    }
}
