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
        // Tạo tài khoản Super Admin mặc định, sử dụng firstOrCreate để tránh trùng lặp
        Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'], // Tiêu chí tìm kiếm
            [
                'name' => 'Super Admin',
                'phone' => '0123456789',
                'role' => 'super_admin', // Sử dụng vai trò cao nhất
                'password' => Hash::make('admin'), // Mật khẩu là 'admin'
            ]
        );

        // Tạo thêm các tài khoản nhân viên (admin) mẫu khác
        $moreAdmins = [
            [
                'name' => 'Staff 1',
                'email' => 'staff1@example.com', // Đảm bảo email duy nhất
                'phone' => '0912345678',
                'role' => 'staff', // Vai trò nhân viên
                'password' => Hash::make('staff123'),
            ],
            [
                'name' => 'Staff 2',
                'email' => 'staff2@example.com', // Đảm bảo email duy nhất
                'phone' => '0987654321',
                'role' => 'editor', // Vai trò biên tập viên
                'password' => Hash::make('editor123'),
            ],
        ];

        foreach ($moreAdmins as $adminData) {
            Admin::firstOrCreate(
                ['email' => $adminData['email']], // Tiêu chí tìm kiếm dựa trên email
                $adminData
            );
        }
    }
}