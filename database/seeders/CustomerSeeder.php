<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Nguyễn Văn A',
                'email' => 'customer_a@example.com',
                'phone' => '0901112233',
                'password' => Hash::make('password123'),
                'status' => Customer::STATUS_ACTIVE,
                'password_change_required' => false,
                'img' => 'customer_avatars/default_customer_a.png',
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subMonths(6),
            ],
            [
                'name' => 'Trần Thị B',
                'email' => 'customer_b@example.com',
                'phone' => '0904445566',
                'password' => Hash::make('password123'),
                'status' => Customer::STATUS_ACTIVE,
                'password_change_required' => false,
                'img' => 'customer_avatars/default_customer_b.png',
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subMonths(3),
            ],
            [
                'name' => 'Lê Văn C',
                'email' => 'customer_c@example.com',
                'phone' => '0907778899',
                'password' => Hash::make('password123'),
                'status' => Customer::STATUS_ACTIVE,
                'password_change_required' => true, // Buộc đổi mật khẩu
                'img' => null,
                'created_at' => Carbon::now()->subWeeks(2),
                'updated_at' => Carbon::now()->subWeeks(2),
            ],
            [
                'name' => 'Phạm Thị D (Bị khóa)',
                'email' => 'customer_d@example.com',
                'phone' => '0909998877',
                'password' => Hash::make('password123'),
                'status' => Customer::STATUS_SUSPENDED, // Bị khóa
                'password_change_required' => false,
                'img' => null,
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subMonths(1),
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::firstOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
        }
    }
}