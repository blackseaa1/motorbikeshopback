<div class="col-lg-4">
    <div class="card mb-4">
        <h5 class="card-header">Tìm kiếm</h5>
        <div class="card-body">
            {{-- Form tìm kiếm trỏ đến route 'blog' với method GET --}}
            <form action="{{ route('blog.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Tìm kiếm bài viết..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Go!</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Bài viết gần đây</h5>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @forelse($recentPosts as $recentPost)
                    <li class="mb-2">
                        <a href="{{ route('blog.show', $recentPost) }}">{{ $recentPost->title }}</a>
                    </li>
                @empty
                    <li>Chưa có bài viết nào.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>