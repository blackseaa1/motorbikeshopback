@extends('admin.layouts.app')

@section('title', 'Dashboard') @section('content')
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
                                <p class="card-text-large">1,250</p>
                            </div>
                            <i class="bi bi-boxes summary-icon icon-info"></i>
                        </div>
                        <p class="card-text-small mt-2 mb-0"><span class="text-success"><i
                                    class="bi bi-plus-circle-fill"></i> 25 mới</span> tuần này</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="card-title">Đơn Hàng Mới</h5>
                                <p class="card-text-large">32</p>
                            </div>
                            <i class="bi bi-cart-check-fill summary-icon icon-success"></i>
                        </div>
                        <p class="card-text-small mt-2 mb-0"><span class="text-primary">5 đang chờ xử lý</span></p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="card-title">Doanh Thu (Tháng)</h5>
                                <p class="card-text-large">150.7M</p>
                            </div>
                            <i class="bi bi-cash-coin summary-icon icon-primary"></i>
                        </div>
                        <p class="card-text-small mt-2 mb-0"><span class="text-success"><i
                                    class="bi bi-arrow-up-short"></i>12.5%</span> so với tháng trước</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="card-title">Sắp Hết Hàng</h5>
                                <p class="card-text-large">18</p>
                            </div>
                            <i class="bi bi-exclamation-triangle-fill summary-icon icon-danger"></i>
                        </div>
                        <p class="card-text-small mt-2 mb-0"><a href="#" class="text-decoration-none">Xem chi tiết</a></p>
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
                            <li class="list-group-item">
                                <img src="https://placehold.co/40x40/E9EEF7/6C757D?text=Pô+A" alt="Pô Akrapovič Titan R1"
                                    onerror="this.src='https://placehold.co/40x40/grey/white?text=Img'">
                                <div class="product-info">
                                    Pô Akrapovič Titan R1
                                    <small>Đã bán: 150</small>
                                </div>
                                <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Hot</span>
                            </li>
                            <li class="list-group-item">
                                <img src="https://placehold.co/40x40/E9EEF7/6C757D?text=Nón+A" alt="Nón Fullface AGV K3 SV"
                                    onerror="this.src='https://placehold.co/40x40/grey/white?text=Img'">
                                <div class="product-info">
                                    Nón Fullface AGV K3 SV
                                    <small>Đã bán: 120</small>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <img src="https://placehold.co/40x40/E9EEF7/6C757D?text=Đèn+L" alt="Đèn Trợ Sáng L4X Plus"
                                    onerror="this.src='https://placehold.co/40x40/grey/white?text=Img'">
                                <div class="product-info">
                                    Đèn Trợ Sáng L4X Plus
                                    <small>Đã bán: 95</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="card recent-orders-card">
                        <div class="card-header">Đơn Hàng Gần Đây</div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="bi bi-receipt-cutoff fs-4 text-primary me-2"></i>
                                <div class="product-info">
                                    Đơn #DHX0572 - Nguyễn Văn An
                                    <small>Tổng: 2.550.000đ</small>
                                </div>
                                <span class="badge bg-warning-subtle text-warning-emphasis order-status">Chờ xử lý</span>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-truck fs-4 text-success me-2"></i>
                                <div class="product-info">
                                    Đơn #DHX0571 - Trần Thị Bích
                                    <small>Tổng: 850.000đ</small>
                                </div>
                                <span class="badge bg-success-subtle text-success-emphasis order-status">Đã giao</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="recent-products-section mt-2" aria-labelledby="recentProductsHeading">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
            <h2 class="section-title" id="recentProductsHeading">Sản Phẩm Mới Thêm</h2>
            <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Thêm Sản Phẩm Mới</a>
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
                        <tr>
                            <td><input type="checkbox" class="form-check-input"
                                    aria-label="Select Pô Độ SC Project S1 Titan"></td>
                            <td><img src="https://placehold.co/60x60/E9EEF7/6C757D?text=Pô+SC"
                                    alt="Pô Độ SC Project S1 Titan" class="product-thumbnail-img"
                                    onerror="this.src='https://placehold.co/60x60/grey/white?text=Img'"></td>
                            <td>Pô Độ SC Project S1 Titan</td>
                            <td>Phụ tùng pô xe</td>
                            <td>3.500.000đ</td>
                            <td><span class="badge bg-warning-subtle text-warning-emphasis">Chờ duyệt</span></td>
                            <td>
                                <button class="btn btn-sm btn-action" title="Sửa sản phẩm"
                                    aria-label="Sửa sản phẩm Pô Độ SC Project S1 Titan"><i
                                        class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-action text-danger" title="Xóa sản phẩm"
                                    aria-label="Xóa sản phẩm Pô Độ SC Project S1 Titan"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="form-check-input" aria-label="Select Găng Tay Scoyco MC09">
                            </td>
                            <td><img src="https://placehold.co/60x60/E9EEF7/6C757D?text=Găng" alt="Găng Tay Scoyco MC09"
                                    class="product-thumbnail-img"
                                    onerror="this.src='https://placehold.co/60x60/grey/white?text=Img'"></td>
                            <td>Găng Tay Scoyco MC09</td>
                            <td>Đồ bảo hộ</td>
                            <td>650.000đ</td>
                            <td><span class="badge bg-success-subtle text-success-emphasis">Đã đăng</span></td>
                            <td>
                                <button class="btn btn-sm btn-action" title="Sửa sản phẩm"
                                    aria-label="Sửa sản phẩm Găng Tay Scoyco MC09"><i
                                        class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-action text-danger" title="Xóa sản phẩm"
                                    aria-label="Xóa sản phẩm Găng Tay Scoyco MC09"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" class="form-check-input"
                                    aria-label="Select Nhớt Motul 300V Factory Line 10W40"></td>
                            <td><img src="https://placehold.co/60x60/E9EEF7/6C757D?text=Nhớt"
                                    alt="Nhớt Motul 300V Factory Line 10W40" class="product-thumbnail-img"
                                    onerror="this.src='https://placehold.co/60x60/grey/white?text=Img'"></td>
                            <td>Nhớt Motul 300V Factory Line 10W40</td>
                            <td>Dầu nhớt & Phụ gia</td>
                            <td>480.000đ</td>
                            <td><span class="badge bg-success-subtle text-success-emphasis">Đã đăng</span></td>
                            <td>
                                <button class="btn btn-sm btn-action" title="Sửa sản phẩm"
                                    aria-label="Sửa sản phẩm Nhớt Motul 300V Factory Line 10W40"><i
                                        class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-action text-danger" title="Xóa sản phẩm"
                                    aria-label="Xóa sản phẩm Nhớt Motul 300V Factory Line 10W40"><i
                                        class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('styles')
@endpush

@push('scripts')
@endpush