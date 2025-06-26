<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'phone' => '0123456789',
            'role' => 'super_admin', // Sử dụng vai trò cao nhất
            'password' => Hash::make('admin'), // Mật khẩu là 'password'
        ]);
    }
}