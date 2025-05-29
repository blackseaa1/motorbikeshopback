<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product; // Hoặc App\Product nếu namespace khác
use Illuminate\Support\Facades\DB;

class MigrateProductIsActiveToStatusSeeder extends Seeder
{
    public function run()
    {
        // Tắt mass assignment protection tạm thời nếu cần
        // Product::unguard();

        Product::where('is_active', true)->update(['status' => 'active']);
        Product::where('is_active', false)->update(['status' => 'inactive']);

        // Product::reguard();

        // Hoặc dùng DB facade nếu không muốn dùng Eloquent events/observers
        // DB::table('products')->where('is_active', true)->update(['status' => 'active']);
        // DB::table('products')->where('is_active', false)->update(['status' => 'inactive']);

        $this->command->info('Product statuses migrated from is_active.');
    }
}
