@extends('customer.layouts.app')
@section('title', $blogPost->title)

@section('content')
  <main class="py-5">
    <div class="container">
    <div class="row">
      <div class="col-lg-8">
      <article>
        <header class="mb-4">
        <h1 class="fw-bolder mb-1">{{ $blogPost->title }}</h1>
        <div class="text-muted fst-italic mb-2">
          Đăng ngày {{ $blogPost->created_at->format('d/m/Y') }} bởi {{ $blogPost->author->name ?? 'Admin' }}
        </div>
        @if($blogPost->status !== 'published')
      <span class="badge {{ $blogPost->status_info['badge'] }}">{{ $blogPost->status_info['text'] }}</span>
      @endif
        </header>
        <figure class="mb-4">
        <img class="img-fluid rounded" src="{{ $blogPost->image_full_url }}" alt="{{ $blogPost->title }}" />
        </figure>
        <section class="mb-5 article-content">
        {!! $blogPost->content !!}
        </section>
      </article>
      </div>

      @include('customer.blog.partials._sidebar', ['recentPosts' => $recentPosts])

    </div>
    </div>
  </main>
@endsection