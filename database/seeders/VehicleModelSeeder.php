<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Support\Str; // Có thể bỏ nếu không dùng cho mục đích khác

class VehicleModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $honda = VehicleBrand::where('name', 'Honda')->first();
        $yamaha = VehicleBrand::where('name', 'Yamaha')->first();
        $suzuki = VehicleBrand::where('name', 'Suzuki')->first();
        $kawasaki = VehicleBrand::where('name', 'Kawasaki')->first();

        if ($honda) {
            $models = ['Winner X', 'Air Blade', 'SH Mode', 'CRF150L'];
            foreach ($models as $modelName) {
                VehicleModel::firstOrCreate(
                    ['name' => $modelName, 'vehicle_brand_id' => $honda->id],
                    [
                        // 'slug' => Str::slug($modelName . '-' . $honda->name), // Đã bỏ trường slug
                        'description' => 'Dòng xe ' . $modelName . ' của Honda',
                    ]
                );
            }
        }

        if ($yamaha) {
            $models = ['Exciter', 'Grande', 'YZF-R15', 'MT-15'];
            foreach ($models as $modelName) {
                VehicleModel::firstOrCreate(
                    ['name' => $modelName, 'vehicle_brand_id' => $yamaha->id],
                    [
                        // 'slug' => Str::slug($modelName . '-' . $yamaha->name), // Đã bỏ trường slug
                        'description' => 'Dòng xe ' . $modelName . ' của Yamaha',
                    ]
                );
            }
        }

        if ($suzuki) {
            $models = ['Satria F150', 'GSX-S150', 'Raider R150'];
            foreach ($models as $modelName) {
                VehicleModel::firstOrCreate(
                    ['name' => $modelName, 'vehicle_brand_id' => $suzuki->id],
                    [
                        // 'slug' => Str::slug($modelName . '-' . $suzuki->name), // Đã bỏ trường slug
                        'description' => 'Dòng xe ' . $modelName . ' của Suzuki',
                    ]
                );
            }
        }

        if ($kawasaki) {
            $models = ['Ninja 400', 'Z1000', 'Versys 650'];
            foreach ($models as $modelName) {
                VehicleModel::firstOrCreate(
                    ['name' => $modelName, 'vehicle_brand_id' => $kawasaki->id],
                    [
                        // 'slug' => Str::slug($modelName . '-' . $kawasaki->name), // Đã bỏ trường slug
                        'description' => 'Dòng xe ' . $modelName . ' của Kawasaki',
                    ]
                );
            }
        }
    }
}