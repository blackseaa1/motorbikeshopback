<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'phone' => '0987654321',
                'role' => Admin::ROLE_SUPER_ADMIN,
                'password' => Hash::make('admin'),
                'img' => null,
                'status' => Admin::STATUS_ACTIVE,
                'password_change_required' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        Admin::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Nhân viên A',
                'phone' => '0912345678',
                'role' => Admin::ROLE_STAFF,
                'password' => Hash::make('password'),
                'img' => null,
                'status' => Admin::STATUS_ACTIVE,
                'password_change_required' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}