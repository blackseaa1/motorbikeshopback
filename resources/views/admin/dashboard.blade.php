@extends('admin.layouts.app')

@section('title', 'Dashboard') @section('content')
  <div class="dashboard-page-identifier" hidden></div>
    <header class="content-header">
        <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    </header>

    <section class="summary-cards-section" aria-labelledby="summaryCardsHeading">
        <h2 id="summaryCardsHeading" class="visually-hidden">Summary Statistics</h2>
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="card-title">Tổng Sản Phẩm</h5>
                                {{-- Link to Product Management --}}
                                <a href="{{ route('admin.productManagement.products.index') }}" class="text-decoration-none text-dark">
                                    <p class="card-text-large">{{ number_format($totalProducts) }}</p>
                                </a>
                            </div>
                            <i class="bi bi-boxes summary-icon icon-info"></i>
                        </div>
                        <p class="card-text-small mt-2 mb-0"><span class="text-success"><i
                                    class="bi bi-plus-circle-fill"></i> {{ number_format($newProductsThisWeek) }} mới</span> tuần này</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="card-title">Đơn Hàng Mới</h5>
                                {{-- Link to Orders Management, filtered by pending status --}}
                                <a href="{{ route('admin.sales.orders.index', ['status' => \App\Models\Order::STATUS_PENDING]) }}" class="text-decoration-none text-dark">
                                    <p class="card-text-large">{{ number_format($newOrdersThisWeek) }}</p>
                                </a>
                            </div>
                            <i class="bi bi-cart-check-fill summary-icon icon-success"></i>
                        </div>
                        <p class="card-text-small mt-2 mb-0"><span class="text-primary">{{ number_format($pendingOrders) }} đang chờ xử lý</span></p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="card-title">Doanh Thu (Tháng)</h5>
                                {{-- Link to Reports page --}}
                                <a href="{{ route('admin.reports') }}" class="text-decoration-none text-dark">
                                    <p class="card-text-large">{{ number_format($monthlyRevenue) }}đ</p>
                                </a>
                            </div>
                            <i class="bi bi-cash-coin summary-icon icon-primary"></i>
                        </div>
                        <p class="card-text-small mt-2 mb-0">
                            @if($revenueComparison > 0)
                                <span class="text-success"><i class="bi bi-arrow-up-short"></i>{{ number_format($revenueComparison, 1) }}%</span> so với tháng trước
                            @elseif($revenueComparison < 0)
                                <span class="text-danger"><i class="bi bi-arrow-down-short"></i>{{ number_format(abs($revenueComparison), 1) }}%</span> so với tháng trước
                            @else
                                <span class="text-muted">Không đổi</span> so với tháng trước
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="card-title">Sắp Hết Hàng</h5>
                                <p class="card-text-large">{{ number_format($lowStockProductsCount) }}</p>
                            </div>
                            <i class="bi bi-exclamation-triangle-fill summary-icon icon-danger"></i>
                        </div>
                        {{-- Link to Inventory page --}}
                        <p class="card-text-small mt-2 mb-0"><a href="{{ route('admin.productManagement.inventory.index') }}" class="text-decoration-none">Xem chi tiết</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="charts-and-lists-section" aria-labelledby="chartsAndListsHeading">
        <h2 id="chartsAndListsHeading" class="visually-hidden">Charts and Lists</h2>
        <div class="row">
            <div class="col-lg-7">
                <div class="chart-container">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <h3 class="chart-title mb-0">Tổng Quan Doanh Thu 6 Tháng</h3>
                        <small class="text-muted">Dữ liệu cập nhật hàng ngày</small>
                    </div>
                    <div class="chart-placeholder">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="right-sidebar-cards">
                    <div class="card mb-4">
                        <div class="card-header">Sản Phẩm Bán Chạy Nhất</div>
                        <ul class="list-group list-group-flush">
                            @forelse($bestSellingProducts as $product)
                                <li class="list-group-item">
                                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                                        class="product-thumbnail-img"
                                        onerror="this.src='https://placehold.co/40x40/grey/white?text=Img'">
                                    <div class="product-info">
                                        {{ $product->name }}
                                        <small>Đã bán: {{ number_format($product->total_quantity_sold) }}</small>
                                    </div>
                                    {{-- You can add a badge based on sales quantity if needed --}}
                                    {{-- <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Hot</span> --}}
                                </li>
                            @empty
                                <li class="list-group-item text-muted">Không có sản phẩm bán chạy nào.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card recent-orders-card">
                        <div class="card-header">Đơn Hàng Gần Đây</div>
                        <ul class="list-group list-group-flush">
                            @forelse($recentOrders as $order)
                                <li class="list-group-item">
                                    @if($order->status === \App\Models\Order::STATUS_COMPLETED || $order->status === \App\Models\Order::STATUS_DELIVERED)
                                        <i class="bi bi-truck fs-4 text-success me-2"></i>
                                    @elseif($order->status === \App\Models\Order::STATUS_PENDING || $order->status === \App\Models\Order::STATUS_PROCESSING)
                                        <i class="bi bi-receipt-cutoff fs-4 text-warning me-2"></i>
                                    @else
                                        <i class="bi bi-receipt-cutoff fs-4 text-muted me-2"></i>
                                    @endif
                                    <div class="product-info">
                                        Đơn #{{ $order->id }} - {{ $order->guest_name }}
                                        <small>Tổng: {{ number_format($order->total_price) }}đ</small>
                                    </div>
                                    <span class="badge {{ $order->status_badge_class }} order-status">{{ $order->status_text }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">Không có đơn hàng gần đây nào.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="recent-products-section mt-2 d-none" aria-labelledby="recentProductsHeading">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
            <h2 class="section-title" id="recentProductsHeading">Sản Phẩm Mới Thêm</h2>
            {{-- Fixed the route name from admin.product_management.products.index to admin.productManagement.products.index --}}
            <a href="{{ route('admin.productManagement.products.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Thêm Sản Phẩm Mới</a>
        </div>
        <div class="recent-products-table-wrapper">
            <div class="table-responsive">
                <table class="table table-hover align-middle recent-products-table">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 5%;"><input type="checkbox" class="form-check-input"
                                    aria-label="Select all products"></th>
                            <th scope="col" style="width: 10%;">Hình Ảnh</th>
                            <th scope="col" style="width: 30%;">Tên Sản Phẩm</th>
                            <th scope="col" style="width: 20%;">Danh Mục</th>
                            <th scope="col" style="width: 10%;">Giá Bán</th>
                            <th scope="col" style="width: 15%;">Trạng Thái</th>
                            <th scope="col" style="width: 10%;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestProducts as $product)
                            <tr>
                                <td><input type="checkbox" class="form-check-input"
                                        aria-label="Select {{ $product->name }}"></td>
                                <td><img src="{{ $product->thumbnail_url }}"
                                        alt="{{ $product->name }}" class="product-thumbnail-img"
                                        onerror="this.src='https://placehold.co/60x60/grey/white?text=Img'"></td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? 'N/A' }}</td>
                                <td>{{ number_format($product->price) }}đ</td>
                                <td><span class="badge {{ $product->status_badge_class }}">{{ $product->status_text }}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-action" title="Sửa sản phẩm"
                                        aria-label="Sửa sản phẩm {{ $product->name }}"><i
                                            class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-sm btn-action text-danger" title="Xóa sản phẩm"
                                        aria-label="Xóa sản phẩm {{ $product->name }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Không có sản phẩm mới nào được thêm gần đây.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('styles')
@endpush

@push('scripts')
    {{-- Chỉ tải script biểu đồ ở trang này --}}
    <script src="{{ asset('assets_admin/js/dashboard_chart.js') }}"></script>
@endpush
