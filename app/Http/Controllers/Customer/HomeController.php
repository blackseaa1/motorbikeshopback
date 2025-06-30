<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand; // Import Brand model
use App\Models\Category; // Import Category model
use App\Models\Product;
use App\Models\BlogPost;
use App\Models\Review; // Import Review model for products
use App\Mail\ContactFormSubmission; // Import Mailable class
use Illuminate\Support\Facades\Mail; // Import Mail facade
use Illuminate\Support\Facades\Log; // Import Log facade

class HomeController extends Controller
{
    public function index()
    {
        // Lấy các sản phẩm mới nhất (sẽ dùng cho cả latestProducts và featuredProducts)
        $latestProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->latest() // Sắp xếp theo created_at DESC
            ->limit(8) // Lấy 8 sản phẩm mới nhất
            ->with('images') // Tải ảnh sản phẩm
            ->get();

        // Lấy các bài blog mới nhất
        $latestBlogPosts = BlogPost::where('status', BlogPost::STATUS_PUBLISHED)
            ->latest() // Sắp xếp theo created_at DESC
            ->limit(3) // Lấy 3 bài blog mới nhất
            ->get();

        // Tải các review cho sản phẩm nếu cần (cho trang chủ)
        $latestProducts->loadAvg(['reviews' => function ($query) {
            $query->where('status', Review::STATUS_APPROVED);
        }], 'rating');
        $latestProducts->loadCount(['reviews' => function ($query) {
            $query->where('status', Review::STATUS_APPROVED);
        }]);

        // THÊM: Gán $featuredProducts. Bạn có thể thay đổi logic lấy sản phẩm nổi bật nếu muốn.
        // Hiện tại, dùng chung latestProducts làm featuredProducts.
        $featuredProducts = $latestProducts;

        // THÊM: Lấy danh sách Brands (ví dụ: 6 thương hiệu ngẫu nhiên đang hoạt động)
        $brands = Brand::where('status', 'active')->inRandomOrder()->take(6)->get();

        // THÊM: Lấy danh sách Categories (ví dụ: tất cả danh mục đang hoạt động, sắp xếp mới nhất)
        $categories = Category::where('status', 'active')->latest()->get();


        return view('customer.home', compact('latestProducts', 'latestBlogPosts', 'featuredProducts', 'brands', 'categories')); // THÊM 'brands' và 'categories'
    }

    public function contact()
    {
        return view('customer.contact');
    }

    /**
     * Xử lý gửi form liên hệ.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContactForm(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Gửi email đến địa chỉ MAIL_FROM_ADDRESS hoặc một địa chỉ cụ thể
            Mail::to(config('mail.from.address')) // Gửi đến địa chỉ cấu hình trong .env/mail.php
                ->send(new ContactFormSubmission($validatedData));

            return back()->with('success', 'Tin nhắn của bạn đã được gửi thành công! Chúng tôi sẽ liên hệ lại sớm nhất có thể.');
        } catch (\Exception $e) {
            Log::error('Failed to send contact form email: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại sau.');
        }
    }
}
