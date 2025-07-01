<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;

class VehicleModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $honda = VehicleBrand::where('name', 'Honda')->first();
        $yamaha = VehicleBrand::where('name', 'Yamaha')->first();
        $suzuki = VehicleBrand::where('name', 'Suzuki')->first();
        $kawasaki = VehicleBrand::where('name', 'Kawasaki')->first();
        $ducati = VehicleBrand::where('name', 'Ducati')->first();
        $bmw = VehicleBrand::where('name', 'BMW Motorrad')->first();

        $modelsData = [];

        if ($honda) {
            $modelsData[] = ['name' => 'Winner X', 'vehicle_brand_id' => $honda->id, 'year' => 2023, 'description' => 'Dòng xe côn tay thể thao.', 'status' => VehicleModel::STATUS_ACTIVE];
            $modelsData[] = ['name' => 'Air Blade', 'vehicle_brand_id' => $honda->id, 'year' => 2024, 'description' => 'Dòng xe tay ga phổ biến.', 'status' => VehicleModel::STATUS_ACTIVE];
            $modelsData[] = ['name' => 'SH Mode', 'vehicle_brand_id' => $honda->id, 'year' => 2024, 'description' => 'Xe tay ga cao cấp.', 'status' => VehicleModel::STATUS_ACTIVE];
        }
        if ($yamaha) {
            $modelsData[] = ['name' => 'Exciter', 'vehicle_brand_id' => $yamaha->id, 'year' => 2023, 'description' => 'Dòng xe côn tay thể thao của Yamaha.', 'status' => VehicleModel::STATUS_ACTIVE];
            $modelsData[] = ['name' => 'Grande', 'vehicle_brand_id' => $yamaha->id, 'year' => 2024, 'description' => 'Xe tay ga thời trang.', 'status' => VehicleModel::STATUS_ACTIVE];
            $modelsData[] = ['name' => 'YZF-R15', 'vehicle_brand_id' => $yamaha->id, 'year' => 2022, 'description' => 'Xe mô tô thể thao phân khối nhỏ.', 'status' => VehicleModel::STATUS_ACTIVE];
        }
        if ($suzuki) {
            $modelsData[] = ['name' => 'Satria F150', 'vehicle_brand_id' => $suzuki->id, 'year' => 2021, 'description' => 'Xe côn tay HyperUnderbone.', 'status' => VehicleModel::STATUS_ACTIVE];
        }
        if ($kawasaki) {
            $modelsData[] = ['name' => 'Ninja 400', 'vehicle_brand_id' => $kawasaki->id, 'year' => 2023, 'description' => 'Xe mô tô sportbike tầm trung.', 'status' => VehicleModel::STATUS_ACTIVE];
        }
        if ($ducati) {
            $modelsData[] = ['name' => 'Monster 937', 'vehicle_brand_id' => $ducati->id, 'year' => 2023, 'description' => 'Mô tô naked bike huyền thoại.', 'status' => VehicleModel::STATUS_ACTIVE];
        }
        if ($bmw) {
            $modelsData[] = ['name' => 'S 1000 RR', 'vehicle_brand_id' => $bmw->id, 'year' => 2024, 'description' => 'Siêu mô tô thể thao.', 'status' => VehicleModel::STATUS_ACTIVE];
        }

        foreach ($modelsData as $modelData) {
            VehicleModel::firstOrCreate(
                ['name' => $modelData['name'], 'vehicle_brand_id' => $modelData['vehicle_brand_id']],
                $modelData
            );
        }
    }
}