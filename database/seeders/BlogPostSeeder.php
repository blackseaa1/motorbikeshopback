<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use App\Models\Admin; // Import Admin model để lấy author_id

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy ID của admin đầu tiên
        $admin = Admin::first();
        if (!$admin) {
            // Fallback nếu AdminSeeder chưa chạy hoặc thất bại
            $this->call(AdminSeeder::class);
            $admin = Admin::first();
        }

        $posts = [
            [
                'title' => 'Hướng dẫn bảo dưỡng xe máy định kỳ tại nhà',
                'content' => 'Bảo dưỡng xe máy định kỳ là rất quan trọng để đảm bảo xe luôn hoạt động tốt và bền bỉ. Bài viết này sẽ hướng dẫn bạn các bước cơ bản để tự bảo dưỡng xe tại nhà, từ việc kiểm tra dầu nhớt, lốp xe cho đến hệ thống phanh và đèn tín hiệu. Giúp bạn tiết kiệm chi phí và tăng cường an toàn khi di chuyển.',
                'image_url' => 'blog_images/bao_duong_xe.jpg',
                'author_id' => $admin->id,
                'author_type' => 'App\Models\Admin', // Thêm trường 'author_type'
                // 'is_published' đã bị loại bỏ
                // 'published_at' đã bị loại bỏ
            ],
            [
                'title' => 'Chọn lốp xe máy phù hợp cho từng loại địa hình',
                'content' => 'Lốp xe là bộ phận tiếp xúc trực tiếp với mặt đường, đóng vai trò quan trọng trong an toàn và hiệu suất lái. Tùy thuộc vào loại địa hình bạn thường xuyên di chuyển (thành phố, đường trường, địa hình xấu), việc lựa chọn loại lốp phù hợp sẽ mang lại trải nghiệm tốt nhất. Chúng ta sẽ tìm hiểu về các loại lốp phổ biến như lốp đường phố, lốp dual-sport và lốp off-road.',
                'image_url' => 'blog_images/chon_lop_xe.jpg',
                'author_id' => $admin->id,
                'author_type' => 'App\Models\Admin', // Thêm trường 'author_type'
                // 'is_published' đã bị loại bỏ
                // 'published_at' đã bị loại bỏ
            ],
            [
                'title' => 'Công nghệ phanh ABS trên xe máy: Bạn cần biết gì?',
                'content' => 'Hệ thống chống bó cứng phanh (ABS) là một công nghệ an toàn quan trọng trên xe máy hiện đại. ABS giúp ngăn chặn bánh xe bị khóa cứng trong quá trình phanh gấp, giữ cho xe ổn định và người lái có thể điều khiển được xe. Bài viết này sẽ đi sâu vào cách hoạt động của ABS, lợi ích của nó và những điều cần lưu ý khi sử dụng xe có ABS.',
                'image_url' => 'blog_images/phanh_abs.jpg',
                'author_id' => $admin->id,
                'author_type' => 'App\Models\Admin', // Thêm trường 'author_type'
                // 'is_published' đã bị loại bỏ
                // 'published_at' đã bị loại bỏ
            ],
            [
                'title' => 'Phụ kiện độ xe: Tăng cường phong cách và hiệu suất',
                'content' => 'Việc độ xe không chỉ là sở thích mà còn là cách để cá nhân hóa chiếc xe của bạn, đồng thời có thể cải thiện hiệu suất. Từ việc thay đổi ống xả, nâng cấp hệ thống treo cho đến lắp đặt đèn LED và các chi tiết trang trí, thị trường phụ kiện độ xe rất đa dạng. Hãy cùng khám phá những phụ kiện phổ biến và cách chúng có thể biến đổi chiếc xe của bạn.',
                'image_url' => 'blog_images/phu_kien_do_xe.jpg',
                'author_id' => $admin->id,
                'author_type' => 'App\Models\Admin', // Thêm trường 'author_type'
                // 'is_published' đã bị loại bỏ
                // 'published_at' đã bị loại bỏ
            ],
        ];

        foreach ($posts as $postData) {
            BlogPost::firstOrCreate(
                ['title' => $postData['title']],
                $postData // Chuyển toàn bộ mảng dữ liệu
            );
        }
    }
}