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
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('image_url')->nullable();
            $table->unsignedBigInteger('author_id'); // ID của tác giả
            $table->string('author_type'); // Loại tác giả (App\Models\Admin hoặc App\Models\Customer)
            $table->string('status', 50)->default('draft'); // draft, published
            $table->timestamps();
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
