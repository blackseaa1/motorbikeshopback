<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryService;

class DeliveryServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryServices = [
            [
                'name' => 'Giao hàng Tiết kiệm',
                'shipping_fee' => 0, // Phí giao hàng là 0 như yêu cầu trước
                'logo_url' => 'delivery_logos/ghtk_logo.png',
                'status' => 'active',
            ],
            [
                'name' => 'Giao hàng Nhanh',
                'shipping_fee' => 0,
                'logo_url' => 'delivery_logos/ghn_logo.png',
                'status' => 'active',
            ],
            [
                'name' => 'Viettel Post',
                'shipping_fee' => 0,
                'logo_url' => 'delivery_logos/viettelpost_logo.png',
                'status' => 'active',
            ],
            [
                'name' => 'J&T Express',
                'shipping_fee' => 0,
                'logo_url' => 'delivery_logos/jt_express_logo.png',
                'status' => 'inactive', // Tạm ẩn
            ],
        ];

        foreach ($deliveryServices as $serviceData) {
            DeliveryService::firstOrCreate(
                ['name' => $serviceData['name']],
                $serviceData
            );
        }
    }
}