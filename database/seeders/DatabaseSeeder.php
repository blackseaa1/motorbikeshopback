<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Gọi các seeder theo thứ tự bạn muốn
        $this->call([
            AdminSeeder::class,
            ProductDefaultsSeeder::class,
            // Bạn có thể thêm các seeder khác vào đây
        ]);
    }
}
