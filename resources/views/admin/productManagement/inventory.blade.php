@extends('admin.layouts.app')

@section('title', 'Inventory') @section('content')
    <header class="content-header">
        <h1><i class="bi bi-list-check me-2"></i>Hàng Tồn Kho</h1>
    </header>
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="bi bi-box-seam me-2"></i>Quản lý Tồn kho Sản phẩm</h2>
                {{-- Nút này có thể dẫn đến chức năng nhập kho hoặc các hành động kho khác --}}
                {{-- <button class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> Nhập kho mới
                </button> --}}
            </div>
            <div class="card-body">
                {{-- Bộ lọc (Tùy chọn) --}}
                <form method="GET" action="#" class="mb-3"> {{-- Action # tạm thời --}}
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="filterProductName" class="form-label">Tên sản phẩm:</label>
                            <input type="text" class="form-control form-control-sm" id="filterProductName"
                                name="product_name" value="{{-- request('product_name') --}}">
                        </div>
                        <div class="col-md-3">
                            <label for="filterCategory" class="form-label">Danh mục:</label>
                            <select class="form-select form-select-sm" id="filterCategory" name="category_id">
                                <option value="">Tất cả danh mục</option>
                                {{-- Lặp qua $categories từ controller --}}
                                {{-- @foreach($categories as $category) --}}
                                {{-- <option value="{{ $category->id }}">{{ $category->name }}</option> --}}
                                {{-- @endforeach --}}
                                <option value="1">Phụ tùng</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStockStatus" class="form-label">Trạng thái tồn kho:</label>
                            <select class="form-select form-select-sm" id="filterStockStatus" name="stock_status">
                                <option value="">Tất cả</option>
                                <option value="in_stock">Còn hàng</option>
                                <option value="low_stock">Sắp hết hàng</option>
                                <option value="out_of_stock">Hết hàng</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Lọc</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID SP</th>
                                <th scope="col">Ảnh</th>
                                <th scope="col">Tên Sản phẩm</th>
                                <th scope="col">Danh mục</th>
                                <th scope="col" class="text-center">Số lượng tồn</th>
                                <th scope="col" class="text-center">Trạng thái</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Dữ liệu mẫu - Bạn sẽ lặp qua $products từ controller ở đây --}}
                            <tr>
                                <td>1</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=SP" alt="Ảnh SP"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Dầu nhớt ABC</td>
                                <td>Phụ tùng</td>
                                <td class="text-center">100</td>
                                <td class="text-center"><span class="badge bg-success">Còn hàng</span></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateStockModal" {{-- Thêm các data-* attribute để truyền dữ liệu
                                        sản phẩm vào modal --}} data-product-id="1" data-product-name="Dầu nhớt ABC"
                                        data-current-stock="100" title="Cập nhật số lượng">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <a href="#" class="btn btn-secondary btn-sm btn-action" title="Xem lịch sử kho">
                                        {{-- Giả sử route là admin.productManagement.inventory.history với product_id --}}
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=SP" alt="Ảnh SP"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Lốp xe Michelin XYZ</td>
                                <td>Lốp xe</td>
                                <td class="text-center">5</td>
                                <td class="text-center"><span class="badge bg-warning text-dark">Sắp hết hàng</span></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateStockModal" data-product-id="2"
                                        data-product-name="Lốp xe Michelin XYZ" data-current-stock="5"
                                        title="Cập nhật số lượng">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <a href="#" class="btn btn-secondary btn-sm btn-action" title="Xem lịch sử kho">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td><img src="https://placehold.co/50x50/EFEFEF/AAAAAA&text=SP" alt="Ảnh SP"
                                        class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;"></td>
                                <td>Bugi NGK</td>
                                <td>Phụ tùng</td>
                                <td class="text-center">0</td>
                                <td class="text-center"><span class="badge bg-danger">Hết hàng</span></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm btn-action" data-bs-toggle="modal"
                                        data-bs-target="#updateStockModal" data-product-id="3" data-product-name="Bugi NGK"
                                        data-current-stock="0" title="Cập nhật số lượng">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <a href="#" class="btn btn-secondary btn-sm btn-action" title="Xem lịch sử kho">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </td>
                            </tr>
                            {{-- Kết thúc lặp --}}
                        </tbody>
                    </table>
                </div>
                {{-- Phân trang (sẽ cần logic backend) --}}
                {{-- <div class="mt-3">
                    {{ $products->links() }}
                </div> --}}
            </div>
        </div>
    </div>

    {{-- Modal Cập nhật số lượng tồn kho --}}
    <div class="modal fade" id="updateStockModal" tabindex="-1" aria-labelledby="updateStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStockModalLabel"><i class="bi bi-box-arrow-in-down me-2"></i>Cập nhật
                        Số lượng Tồn kho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStockForm"> {{-- Bỏ action, method nếu chỉ dựng giao diện --}}
                        <input type="hidden" id="updateStockProductId" name="product_id">
                        <div class="mb-3">
                            <label class="form-label">Sản phẩm:</label>
                            <p><strong id="updateStockProductName">Dầu nhớt ABC</strong></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số lượng tồn hiện tại:</label>
                            <p><strong id="updateStockCurrentQuantity">100</strong></p>
                        </div>
                        <div class="mb-3">
                            <label for="updateStockNewQuantity" class="form-label">Số lượng tồn mới:<span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="updateStockNewQuantity" name="new_stock_quantity"
                                min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="updateStockNote" class="form-label">Ghi chú (ví dụ: Nhập kho, Điều chỉnh):</label>
                            <textarea class="form-control" id="updateStockNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" form="updateStockForm">Cập nhật Tồn kho</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const updateStockModal = document.getElementById('updateStockModal');
            if (updateStockModal) {
                updateStockModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const productId = button.dataset.productId;
                    const productName = button.dataset.productName;
                    const currentStock = button.dataset.currentStock;

                    updateStockModal.querySelector('#updateStockProductId').value = productId;
                    updateStockModal.querySelector('#updateStockProductName').textContent = productName;
                    updateStockModal.querySelector('#updateStockCurrentQuantity').textContent = currentStock;
                    updateStockModal.querySelector('#updateStockNewQuantity').value = currentStock; // Mặc định là số lượng hiện tại
                    updateStockModal.querySelector('#updateStockModalLabel').textContent = `Cập nhật Tồn kho: ${productName}`;
                });
            }
        });
    </script>
@endpush