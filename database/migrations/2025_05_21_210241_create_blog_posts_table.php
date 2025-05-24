<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id(); // INT PRIMARY KEY AUTO_INCREMENT (SQL là AUTO_INCREMENT, docx là INT PRIMARY KEY) [cite: 77, 110]
            $table->string('title'); // VARCHAR(255) NOT NULL, Tiêu đề bài viết [cite: 77, 110]
            $table->text('content')->nullable(); // TEXT, Nội dung bài viết [cite: 77, 110]
            $table->text('image_url')->nullable(); // TEXT, URL ảnh minh họa của bài đăng [cite: 77, 110]
            $table->foreignId('author_id')->constrained('customers')->onDelete('cascade')->onUpdate('cascade'); // FK đến customers(id) (khách viết blog) [cite: 77, 110] (Nếu admin cũng có thể viết, cần xem xét lại cấu trúc này)
            $table->timestamps(); // created_at DATETIME, updated_at DATETIME [cite: 77, 110]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};