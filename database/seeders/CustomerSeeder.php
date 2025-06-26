<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
// use Carbon\Carbon; // Carbon không còn cần thiết nếu email_verified_at bị loại bỏ

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Nguyễn Văn A',
                'email' => 'customer_a@example.com',
                'phone' => '0901112233',
                'password' => Hash::make('password123'),
                // Các trường 'email_verified_at' và 'must_change_password' đã được loại bỏ
            ],
            [
                'name' => 'Trần Thị B',
                'email' => 'customer_b@example.com',
                'phone' => '0904445566',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Lê Văn C',
                'email' => 'customer_c@example.com',
                'phone' => '0907778899',
                'password' => Hash::make('password123'),
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