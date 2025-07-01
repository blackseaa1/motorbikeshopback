<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\VehicleModel;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Lấy IDs của các category, brand, vehicle models đã seed
        $categoryIds = Category::pluck('id')->all();
        $brandIds = Brand::pluck('id')->all();
        $vehicleModelIds = VehicleModel::pluck('id')->all();

        // Đảm bảo có ít nhất một category và brand để tạo sản phẩm
        if (empty($categoryIds) || empty($brandIds)) {
            $this->call(CategorySeeder::class);
            $this->call(BrandSeeder::class);
            $categoryIds = Category::pluck('id')->all();
            $brandIds = Brand::pluck('id')->all();
        }
        if (empty($vehicleModelIds)) {
            $this->call(VehicleBrandSeeder::class); // Đảm bảo có brand xe trước
            $this->call(VehicleModelSeeder::class);
            $vehicleModelIds = VehicleModel::pluck('id')->all();
        }

        $productsData = [
            [
                'name' => 'Heo dầu Brembo M4',
                'description' => 'Heo dầu Brembo M4 chính hãng, cải thiện hiệu suất phanh tối ưu cho các dòng xe côn tay và sportbike.',
                'category_name' => 'Hệ thống phanh',
                'brand_name' => 'Brembo',
                'price' => 5500000,
                'stock_quantity' => 20,
                'material' => 'Hợp kim nhôm cao cấp',
                'color' => 'Titan',
                'specifications' => "Loại: Monobloc 4 piston\nKhoảng cách tâm lỗ: 100mm\nVật liệu: Nhôm đúc nguyên khối",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/brembo_m4_1.jpg',
                    'products/brembo_m4_2.jpg',
                ],
                'compatible_models' => ['Winner X', 'Exciter', 'GSX-S150'],
            ],
            [
                'name' => 'Phuộc Ohlin bình dầu sau',
                'description' => 'Phuộc Ohlin cao cấp cho hiệu suất giảm xóc tối ưu, mang lại cảm giác lái êm ái và ổn định trên mọi cung đường.',
                'category_name' => 'Phụ tùng khung gầm',
                'brand_name' => 'Ohlin',
                'price' => 12000000,
                'stock_quantity' => 15,
                'material' => 'Hợp kim nhôm, lò xo thép',
                'color' => 'Vàng/Đen',
                'specifications' => "Loại: Monoshock bình dầu rời\nĐiều chỉnh: Preload, Rebound, Compression\nChiều dài: 336mm",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/ohlin_phuoc_1.jpg',
                    'products/ohlin_phuoc_2.jpg',
                ],
                'compatible_models' => ['Exciter', 'Winner X', 'Air Blade'],
            ],
            [
                'name' => 'Pô Yoshimura R77 Full System',
                'description' => 'Hệ thống ống xả full system tăng cường sức mạnh động cơ và tạo ra âm thanh uy lực, thể thao cho xe.',
                'category_name' => 'Phụ tùng động cơ',
                'brand_name' => 'Yoshimura',
                'price' => 8500000,
                'stock_quantity' => 10,
                'material' => 'Titanium/Carbon',
                'color' => 'Carbon',
                'specifications' => "Loại: Full System\nVật liệu thân pô: Carbon\nVật liệu cổ pô: Titanium",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/yoshimura_r77_1.jpg',
                    'products/yoshimura_r77_2.jpg',
                ],
                'compatible_models' => ['YZF-R15', 'Ninja 400'],
            ],
            [
                'name' => 'Lốp Michelin Pilot Road 5',
                'description' => 'Lốp xe hiệu suất cao, bám đường tốt trong mọi điều kiện thời tiết, tăng cường an toàn khi di chuyển.',
                'category_name' => 'Lốp xe',
                'brand_name' => 'Michelin',
                'price' => 2800000,
                'stock_quantity' => 30,
                'material' => 'Cao su tổng hợp',
                'color' => 'Đen',
                'specifications' => "Kích thước: 120/70ZR17 (trước), 180/55ZR17 (sau)\nChỉ số tốc độ: W (270 km/h)\nChỉ số tải: 58 (236 kg)",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/michelin_road5_1.jpg',
                ],
                'compatible_models' => ['Z1000', 'Monster 937'],
            ],
            [
                'name' => 'Dầu nhớt Motul 300V Factory Line 10W40',
                'description' => 'Dầu nhớt tổng hợp cao cấp cho động cơ xe máy, bảo vệ động cơ tối đa và tăng cường hiệu suất.',
                'category_name' => 'Dầu nhớt và hóa chất',
                'brand_name' => 'Motul',
                'price' => 450000,
                'stock_quantity' => 50,
                'material' => 'Dầu tổng hợp Ester Core®',
                'color' => 'Vàng',
                'specifications' => "Dung tích: 1 lít\nĐộ nhớt: 10W-40\nTiêu chuẩn: API SN, JASO MA2",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/motul_300v_1.jpg',
                    'products/motul_300v_2.jpg',
                ],
                'compatible_models' => ['Winner X', 'Exciter', 'Air Blade', 'SH Mode', 'YZF-R15', 'MT-15', 'Satria F150', 'GSX-S150', 'Raider R150', 'Ninja 400', 'Z1000', 'Versys 650', 'Monster 937', 'S 1000 RR'],
            ],
            [
                'name' => 'Bugia Iridium Denso IU24',
                'description' => 'Bugia hiệu suất cao với điện cực Iridium giúp đánh lửa ổn định và tiết kiệm nhiên liệu.',
                'category_name' => 'Hệ thống điện',
                'brand_name' => 'Denso',
                'price' => 250000,
                'stock_quantity' => 40,
                'material' => 'Iridium',
                'color' => 'Bạc',
                'specifications' => "Loại: Iridium\nMã: IU24\nTương thích: Nhiều loại xe côn tay, xe số",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/denso_bugi_1.jpg',
                ],
                'compatible_models' => ['Winner X', 'Exciter', 'Air Blade', 'SH Mode'],
            ],
            [
                'name' => 'Má phanh Brembo Red Pad',
                'description' => 'Má phanh hiệu suất cao, mang lại lực phanh ổn định và bền bỉ trong nhiều điều kiện.',
                'category_name' => 'Hệ thống phanh',
                'brand_name' => 'Brembo',
                'price' => 750000,
                'stock_quantity' => 25,
                'material' => 'Hợp chất hữu cơ',
                'color' => 'Đỏ',
                'specifications' => "Loại: Red Pad (Compound hữu cơ)\nĐộ bền nhiệt: Cao\nĐộ mòn: Thấp",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/brembo_redpad_1.jpg',
                ],
                'compatible_models' => ['Winner X', 'Exciter'],
            ],
            [
                'name' => 'Đèn trợ sáng L4X',
                'description' => 'Đèn trợ sáng công suất lớn, cải thiện tầm nhìn ban đêm và an toàn giao thông.',
                'category_name' => 'Đèn và Điện',
                'brand_name' => 'Không thương hiệu', // Giả sử có 1 brand "Không thương hiệu" hoặc Brand ID mặc định
                'price' => 950000,
                'stock_quantity' => 30,
                'material' => 'Hợp kim nhôm',
                'color' => 'Đen',
                'specifications' => "Công suất: 40W\nNguồn sáng: LED Cree\nChống nước: IP68",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/l4x_light_1.jpg',
                ],
                'compatible_models' => ['Winner X', 'Exciter'], // Tương thích rộng rãi
            ],
            [
                'name' => 'Gương chiếu hậu Rizoma Ellipse',
                'description' => 'Gương chiếu hậu cao cấp với thiết kế hiện đại, tăng tính thẩm mỹ cho xe.',
                'category_name' => 'Phụ kiện độ xe',
                'brand_name' => 'Rizoma', // Giả định có brand Rizoma
                'price' => 2200000,
                'stock_quantity' => 18,
                'material' => 'Hợp kim nhôm CNC',
                'color' => 'Đen',
                'specifications' => "Loại: Gương hậu gắn ghi đông\nThiết kế: Ellipse\nVật liệu: Billet Aluminum",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/rizoma_mirror_1.jpg',
                ],
                'compatible_models' => ['MT-15', 'Z1000'],
            ],
            [
                'name' => 'Nón bảo hiểm fullface LS2 FF800',
                'description' => 'Nón bảo hiểm fullface an toàn, thiết kế thoải mái và thoáng khí.',
                'category_name' => 'Đồ bảo hộ', // Tạo thêm danh mục này nếu chưa có
                'brand_name' => 'LS2', // Giả định có brand LS2
                'price' => 1800000,
                'stock_quantity' => 25,
                'material' => 'Nhựa nhiệt dẻo HPTT',
                'color' => 'Đen nhám',
                'specifications' => "Chuẩn an toàn: ECE R22.05\nTrọng lượng: 1390 ± 50g\nKính: Chống trầy, chống tia UV",
                'status' => Product::STATUS_ACTIVE,
                'images' => [
                    'products/ls2_helmet_1.jpg',
                ],
                'compatible_models' => [], // Nón bảo hiểm thường không tương thích theo model xe
            ],
        ];

        // Lấy hoặc tạo brand "Không thương hiệu" nếu nó không tồn tại
        $noBrand = Brand::firstOrCreate(
            ['name' => 'Không thương hiệu'],
            ['description' => 'Sản phẩm không thuộc thương hiệu cụ thể.', 'status' => Brand::STATUS_ACTIVE]
        );

        // Tạo thêm danh mục nếu chưa có
        $protectiveGearCategory = Category::firstOrCreate(
            ['name' => 'Đồ bảo hộ'],
            ['description' => 'Các loại đồ bảo hộ cá nhân khi lái xe.', 'status' => Category::STATUS_ACTIVE]
        );


        foreach ($productsData as $productData) {
            $category = Category::where('name', $productData['category_name'])->first();
            $brand = Brand::where('name', $productData['brand_name'])->first();

            // Gán brand "Không thương hiệu" nếu không tìm thấy brand cụ thể
            if (!$brand) {
                $brand = $noBrand;
            }
            if (!$category) {
                // Nếu danh mục "Đồ bảo hộ" không có, sử dụng danh mục mặc định hoặc tạo mới
                if ($productData['category_name'] === 'Đồ bảo hộ') {
                    $category = $protectiveGearCategory;
                } else {
                    $this->command->warn("Bỏ qua sản phẩm '{$productData['name']}' do thiếu danh mục: '{$productData['category_name']}'.");
                    continue;
                }
            }


            $product = Product::firstOrCreate(
                ['name' => $productData['name']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock_quantity' => $productData['stock_quantity'],
                    'material' => $productData['material'],
                    'color' => $productData['color'],
                    'specifications' => $productData['specifications'],
                    'status' => $productData['status'],
                ]
            );

            // Attach vehicle models
            if (!empty($productData['compatible_models'])) {
                $modelsToAttach = [];
                foreach ($productData['compatible_models'] as $modelName) {
                    $vehicleModel = VehicleModel::where('name', $modelName)->first();
                    if ($vehicleModel) {
                        $modelsToAttach[] = $vehicleModel->id;
                    }
                }
                if (!empty($modelsToAttach)) {
                    $product->vehicleModels()->syncWithoutDetaching($modelsToAttach);
                }
            }

            // Attach images
            foreach ($productData['images'] as $imagePath) {
                // Giả định các ảnh đã có sẵn trong public/storage/products
                // hoặc đây là đường dẫn sẽ được lưu vào DB
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'image_url' => $imagePath],
                    ['product_id' => $product->id, 'image_url' => $imagePath]
                );
            }
        }
    }
}