<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\BlogPost;
use App\Models\Customer;
use Carbon\Carbon;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Lấy một admin hoặc tạo nếu không có (ví dụ cho mục đích seed, trong thực tế AdminSeeder sẽ chạy trước)
        $admin = Admin::first();
        if (!$admin) {
            // Fallback: Nếu không có admin, tạo một admin tạm thời.
            // Trong môi trường production, bạn nên đảm bảo AdminSeeder đã chạy.
            $admin = Admin::firstOrCreate(
                ['email' => 'default_admin@example.com'],
                [
                    'name' => 'Default Admin',
                    'phone' => null,
                    'role' => 'admin',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'status' => Admin::STATUS_ACTIVE,
                ]
            );
        }

        // Lấy một khách hàng hoặc tạo nếu không có
        $customer = Customer::first();
        if (!$customer) {
            $customer = Customer::firstOrCreate(
                ['email' => 'default_customer@example.com'],
                [
                    'name' => 'Default Customer',
                    'phone' => null,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'status' => Customer::STATUS_ACTIVE,
                ]
            );
        }


        $posts = [
            [
                'title' => 'Hướng dẫn bảo dưỡng xe máy định kỳ tại nhà',
                'content' => 'Bảo dưỡng xe máy định kỳ là rất quan trọng để đảm bảo xe luôn hoạt động tốt và bền bỉ. Bài viết này sẽ hướng dẫn bạn các bước cơ bản để tự bảo dưỡng xe tại nhà, từ việc kiểm tra dầu nhớt, lốp xe cho đến hệ thống phanh và đèn tín hiệu. Giúp bạn tiết kiệm chi phí và tăng cường an toàn khi di chuyển.',
                'image_url' => 'blog_images/bao_duong_xe.jpg',
                'author_id' => $admin->id,
                'author_type' => get_class($admin),
                'status' => BlogPost::STATUS_PUBLISHED,
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'Chọn lốp xe máy phù hợp cho từng loại địa hình',
                'content' => 'Lốp xe là bộ phận tiếp xúc trực tiếp với mặt đường, đóng vai trò quan trọng trong an toàn và hiệu suất lái. Tùy thuộc vào loại địa hình bạn thường xuyên di chuyển (thành phố, đường trường, địa hình xấu), việc lựa chọn loại lốp phù hợp sẽ mang lại trải nghiệm tốt nhất. Chúng ta sẽ tìm hiểu về các loại lốp phổ biến như lốp đường phố, lốp dual-sport và lốp off-road.',
                'image_url' => 'blog_images/chon_lop_xe.jpg',
                'author_id' => $admin->id,
                'author_type' => get_class($admin),
                'status' => BlogPost::STATUS_PUBLISHED,
                'created_at' => Carbon::now()->subDays(8),
            ],
            [
                'title' => 'Công nghệ phanh ABS trên xe máy: Bạn cần biết gì?',
                'content' => 'Hệ thống chống bó cứng phanh (ABS) là một công nghệ an toàn quan trọng trên xe máy hiện đại. ABS giúp ngăn chặn bánh xe bị khóa cứng trong quá trình phanh gấp, giữ cho xe ổn định và người lái có thể điều khiển được xe. Bài viết này sẽ đi sâu vào cách hoạt động của ABS, lợi ích của nó và những điều cần lưu ý khi sử dụng xe có ABS.',
                'image_url' => 'blog_images/phanh_abs.jpg',
                'author_id' => $customer->id, // Ví dụ: bài viết của khách hàng
                'author_type' => get_class($customer),
                'status' => BlogPost::STATUS_PENDING, // Chờ duyệt
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Phụ kiện độ xe: Tăng cường phong cách và hiệu suất',
                'content' => 'Việc độ xe không chỉ là sở thích mà còn là cách để cá nhân hóa chiếc xe của bạn, đồng thời có thể cải thiện hiệu suất. Từ việc thay đổi ống xả, nâng cấp hệ thống treo cho đến lắp đặt đèn LED và các chi tiết trang trí, thị trường phụ kiện độ xe rất đa dạng. Hãy cùng khám phá những phụ kiện phổ biến và cách chúng có thể biến đổi chiếc xe của bạn.',
                'image_url' => 'blog_images/phu_kien_do_xe.jpg',
                'author_id' => $admin->id,
                'author_type' => get_class($admin),
                'status' => BlogPost::STATUS_DRAFT, // Bản nháp
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Top 5 địa điểm phượt xe máy đẹp nhất Việt Nam',
                'content' => 'Việt Nam với những cung đường đèo hùng vĩ, bờ biển dài thơ mộng và các bản làng độc đáo luôn là thiên đường cho những người yêu phượt xe máy. Bài viết này sẽ giới thiệu 5 địa điểm không thể bỏ lỡ cho chuyến đi sắp tới của bạn.',
                'image_url' => 'blog_images/phuot_vietnam.jpg',
                'author_id' => $admin->id,
                'author_type' => get_class($admin),
                'status' => BlogPost::STATUS_PUBLISHED,
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Lịch trình bảo dưỡng xe máy theo từng mốc km',
                'content' => 'Để chiếc xe của bạn luôn bền bỉ và hoạt động ổn định, việc tuân thủ lịch trình bảo dưỡng định kỳ là vô cùng cần thiết. Bài viết này sẽ tổng hợp các hạng mục cần kiểm tra và thay thế theo từng mốc số km cụ thể, giúp bạn chăm sóc xe một cách khoa học nhất.',
                'image_url' => 'blog_images/lich_bao_duong.jpg',
                'author_id' => $admin->id,
                'author_type' => get_class($admin),
                'status' => BlogPost::STATUS_ARCHIVED, // Ẩn bài viết
                'created_at' => Carbon::now()->subWeeks(1),
            ],
        ];

        foreach ($posts as $postData) {
            BlogPost::firstOrCreate(
                ['title' => $postData['title']],
                $postData
            );
        }
    }
}