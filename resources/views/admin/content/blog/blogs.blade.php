{{-- resources/views/admin/content/blog/blogs.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Quản lý Bài viết')


@section('content')
    <div id="adminBlogsPage">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-file-post me-2"></i>Quản lý Bài viết</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Bài viết</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0"><i class="bi bi-list-ul me-2"></i>Danh sách Bài viết</h2>
                    @can('create', App\Models\BlogPost::class)
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBlogModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Viết bài mới
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('admin.content.blogs.index') }}"
                            class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-dark' }} me-2"><i
                                class="bi bi-archive-fill me-1"></i> Tất cả</a>
                        <a href="{{ route('admin.content.blogs.index', ['status' => 'trashed']) }}"
                            class="btn btn-sm {{ $status === 'trashed' ? 'btn-dark' : 'btn-outline-dark' }}"><i
                                class="bi bi-trash-fill me-1"></i> Thùng rác</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Ảnh</th>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="blog-table-body">
                                @forelse ($blogs as $blog)
                                    <tr id="blog-row-{{ $blog->id }}"
                                        class="{{ $blog->trashed() ? 'row-trashed' : ($blog->status != 'published' ? 'row-inactive' : '') }}">
                                        <td>{{ $blog->id }}</td>
                                        <td><img src="{{ $blog->image_full_url }}" alt="{{ $blog->title }}"
                                                class="img-thumbnail"
                                                style="max-width: 50px; max-height: 50px; object-fit: contain;">
                                        </td>
                                        <td>{{ Str::limit($blog->title, 60) }}</td>
                                        <td>{{ $blog->author->name ?? 'N/A' }} <br><small
                                                class="text-muted">{{ $blog->author->role_name ?? 'Không rõ' }}</small></td>
                                        <td class="status-cell"><span
                                                class="badge {{ $blog->status_info['badge'] }}">{{ $blog->status_info['text'] }}</span>
                                        </td>
                                        <td>{{ $blog->created_at->format('d/m/Y') }}</td>
                                        <td class="text-center action-buttons">
                                            @if ($blog->trashed())
                                                {{-- Các nút trong thùng rác --}}
                                                @can('restore', $blog)
                                                    <button class="btn btn-success btn-sm btn-action btn-restore-blog"
                                                        data-id="{{ $blog->id }}" data-name="{{ $blog->title }}" title="Khôi phục"><i
                                                            class="bi bi-arrow-counterclockwise"></i></button>
                                                @endcan
                                                @can('forceDelete', $blog)
                                                    <button class="btn btn-danger btn-sm btn-action btn-force-delete-blog"
                                                        data-delete-url="{{ route('admin.content.blogs.forceDelete', $blog) }}"
                                                        data-name="{{ $blog->title }}" title="Xóa vĩnh viễn"><i
                                                            class="bi bi-trash-fill"></i></button>
                                                @endcan
                                            @else
                                                {{-- Các nút cho bài viết thông thường --}}
                                                @can('view', $blog)
                                                    <button class="btn btn-info btn-sm btn-action btn-view" data-id="{{ $blog->id }}"
                                                        title="Xem"><i class="bi bi-eye-fill"></i></button>
                                                @endcan
                                                @can('update', $blog)
                                                    <button class="btn btn-warning btn-sm btn-action btn-edit" data-id="{{ $blog->id }}"
                                                        title="Sửa"><i class="bi bi-pencil-square"></i></button>
                                                @endcan
                                                @can('toggleStatus', $blog)
                                                    <button
                                                        class="btn btn-sm btn-action toggle-status-blog-btn {{ $blog->isPublished() ? 'btn-secondary' : 'btn-success' }}"
                                                        data-url="{{ route('admin.content.blogs.toggleStatus', $blog) }}"
                                                        title="{{ $blog->isPublished() ? 'Chuyển thành bản nháp' : 'Xuất bản' }}"><i
                                                            class="bi {{ $blog->isPublished() ? 'bi-pause-circle-fill' : 'bi-play-circle-fill' }}"></i></button>
                                                @endcan
                                                @can('delete', $blog)
                                                    <button class="btn btn-danger btn-sm btn-action btn-delete"
                                                        data-id="{{ $blog->id }}" data-name="{{ $blog->title }}" title="Xóa"><i
                                                            class="bi bi-trash"></i></button>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Chưa có bài viết nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($blogs->hasPages())
                        <div class="mt-3 d-flex justify-content-end">{{ $blogs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Include các modals --}}
        @include('admin.content.blog.modals.create_blog_modal')
        @include('admin.content.blog.modals.view_blog_modal')
        @include('admin.content.blog.modals.update_blog_modal')
        @include('admin.content.blog.modals.confirm_delete_blog')
        @include('admin.content.blog.modals.confirm_force_delete_blog')
        @include('admin.content.blog.modals.confirm_restore_blog')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets_admin/js/blog_manager.js') }}"></script>
@endpush