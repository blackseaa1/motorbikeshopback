@extends('customer.layouts.app')
@section('title', 'Blog - MotoToys')

@section('content')
  <main class="py-5">
    <div class="container">
    <h1 class="mb-4">Blog của chúng tôi</h1>
    <p class="lead mb-5">Những tin tức, mẹo vặt, và kiến thức mới nhất từ thế giới đồ chơi xe.</p>

    <div class="row">
      <div class="col-lg-8">
      @if (request('search'))
      <h4 class="mb-4">Kết quả tìm kiếm cho: "{{ request('search') }}"</h4>
    @endif

      @forelse($posts as $post)
      <div class="card mb-4">
      <a href="{{ route('blog.show', $post) }}"><img src="{{ $post->image_full_url }}" class="card-img-top"
        alt="{{ $post->title }}"></a>
      <div class="card-body">
      <p class="text-muted">Đăng ngày {{ $post->created_at->format('d/m/Y') }} bởi
        {{ $post->author->name ?? 'Admin' }}</p>
      <h2 class="card-title h4"><a href="{{ route('blog.show', $post) }}"
        class="text-decoration-none text-dark">{{ $post->title }}</a></h2>
      <p class="card-text">{{ Str::limit(strip_tags($post->content), 150) }}</p>
      <a href="{{ route('blog.show', $post) }}" class="btn btn-primary">Đọc thêm &rarr;</a>
      </div>
      </div>
    @empty
      <div class="alert alert-info w-100">
      Không tìm thấy bài viết nào phù hợp.
      </div>
    @endforelse

      <div class="d-flex justify-content-center">
        {{ $posts->links() }}
      </div>
      </div>

      @include('customer.blog.partials._sidebar', ['recentPosts' => $recentPosts])

    </div>
    </div>
  </main>
@endsection