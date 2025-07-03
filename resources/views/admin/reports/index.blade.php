@extends('admin.layouts.app')

@section('title', 'Báo Cáo & Thống Kê')

@section('content')
    <header class="content-header">
        <h1><i class="bi bi-bar-chart-line-fill me-2"></i>Báo Cáo & Thống Kê</h1>
    </header>

    <section class="reports-section">
        <div class="row">
            {{-- Thống kê doanh thu theo ngày trong tháng --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Doanh Thu Theo Ngày (Tháng Hiện Tại)</h5>
                        <div class="d-flex align-items-center">
                            <select id="dailyRevenueMonthSelect" class="form-select form-select-sm me-2"
                                style="width: auto;">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>Tháng {{ $i }}</option>
                                @endfor
                            </select>
                            <select id="dailyRevenueYearSelect" class="form-select form-select-sm" style="width: auto;">
                                @for ($i = date('Y') - 5; $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>Năm {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            <canvas id="dailyRevenueChart"></canvas>
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Doanh Thu</th>
                                    </tr>
                                </thead>
                                <tbody id="dailyRevenueTableBody">
                                    {{-- Data will be loaded here by JavaScript --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thống kê doanh thu theo tháng trong năm --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Doanh Thu Theo Tháng (Năm Hiện Tại)</h5>
                        <select id="monthlyRevenueYearSelect" class="form-select form-select-sm" style="width: auto;">
                            @for ($i = date('Y') - 5; $i <= date('Y') + 1; $i++)
                                <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>Năm {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            <canvas id="monthlyRevenueChart"></canvas>
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tháng</th>
                                        <th>Doanh Thu</th>
                                    </tr>
                                </thead>
                                <tbody id="monthlyRevenueTableBody">
                                    {{-- Data will be loaded here by JavaScript --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thống kê sản phẩm sắp hết hàng --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sản Phẩm Sắp Hết Hàng</h5>
                        <div class="d-flex align-items-center">
                            <label for="lowStockThreshold" class="me-2">Ngưỡng tồn kho:</label>
                            <input type="number" id="lowStockThreshold" class="form-control form-control-sm" value="20"
                                min="1" style="width: 80px;">
                            <button id="applyLowStockThreshold" class="btn btn-sm btn-dark ms-2">Áp dụng</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Thương hiệu</th>
                                        <th>Số lượng tồn</th>
                                        <th>Giá</th>
                                    </tr>
                                </thead>
                                <tbody id="lowStockProductsTableBody">
                                    {{-- Data will be loaded here by JavaScript --}}
                                </tbody>
                            </table>
                        </div>
                        <div id="lowStockNoData" class="alert alert-info text-center mt-3 d-none">
                            Không có sản phẩm nào sắp hết hàng.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thống kê sản phẩm bán chạy --}}
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sản Phẩm Bán Chạy Nhất</h5>
                        <div class="d-flex align-items-center">
                            <label for="bestSellingStartDate" class="me-2">Từ tháng</label>
                            <input type="date" id="bestSellingStartDate" class="form-control form-control-sm me-2"
                                value="{{ date('Y-m-01') }}">
                            <label for="bestSellingEndDate" class="me-2">Đến tháng</label>
                            <input type="date" id="bestSellingEndDate" class="form-control form-control-sm me-2"
                                value="{{ date('Y-m-t') }}">
                            <label for="bestSellingLimit" class="me-2">Số lượng:</label>
                            <input type="number" id="bestSellingLimit" class="form-control form-control-sm" value="10"
                                min="1" max="100" style="width: 80px;">
                            <button id="applyBestSellingFilter" class="btn btn-sm btn-light ms-2">Áp dụng</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Số lượng đã bán</th>
                                        <th>Tổng doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody id="bestSellingProductsTableBody">
                                    {{-- Data will be loaded here by JavaScript --}}
                                </tbody>
                            </table>
                        </div>
                        <div id="bestSellingNoData" class="alert alert-info text-center mt-3 d-none">
                            Không có dữ liệu sản phẩm bán chạy trong khoảng thời gian này.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    {{-- Main reports script to load individual report scripts --}}
    <script src="{{ asset('assets_admin/js/reports_main.js') }}"></script>
    {{-- REMOVED: product_detail_hover.js and order_detail_view.js from here --}}
    {{-- They are now dynamically loaded by reports_main.js --}}
@endpush

{{-- Modals and Tooltips --}}
@include('admin.reports.partials._product_detail_tooltip')
@include('admin.reports.partials._order_detail_modal')