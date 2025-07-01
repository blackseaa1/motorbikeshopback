<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryService;

class DeliveryServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $deliveryServices = [
            [
                'name' => 'Giao hàng Tiết kiệm',
                'shipping_fee' => 0.00, // Phí giao hàng là 0 như yêu cầu
                'logo_url' => 'delivery_service_logos/ghtk_logo.png', // Giả định có file này
                'status' => DeliveryService::STATUS_ACTIVE,
            ],
            [
                'name' => 'Giao hàng Nhanh',
                'shipping_fee' => 0.00,
                'logo_url' => 'delivery_service_logos/ghn_logo.png', // Giả định có file này
                'status' => DeliveryService::STATUS_ACTIVE,
            ],
            [
                'name' => 'Viettel Post',
                'shipping_fee' => 0.00,
                'logo_url' => 'delivery_service_logos/viettelpost_logo.png', // Giả định có file này
                'status' => DeliveryService::STATUS_ACTIVE,
            ],
            [
                'name' => 'J&T Express',
                'shipping_fee' => 0.00,
                'logo_url' => 'delivery_service_logos/jt_express_logo.png', // Giả định có file này
                'status' => DeliveryService::STATUS_INACTIVE, // Tạm ẩn
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