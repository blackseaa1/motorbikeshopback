@extends('customer.account.layouts.app')

@section('title', 'Sổ địa chỉ')

@section('account_content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sổ địa chỉ</h5>
            <a href="{{ route('account.addresses.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Thêm địa chỉ mới
            </a>
        </div>
        <div class="card-body">
            @if($addresses->isEmpty())
                <div class="alert alert-info text-center">
                    Bạn chưa có địa chỉ nào được lưu.
                </div>
            @else
                <div class="row g-4">
                    @foreach($addresses as $address)
                        <div class="col-md-6">
                            <div class="card h-100 {{ $address->is_default ? 'border-primary' : '' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="card-title">{{ $address->full_name }}</h6>
                                        @if($address->is_default)
                                            <span class="badge bg-primary">Mặc định</span>
                                        @endif
                                    </div>
                                    <p class="card-text text-muted mb-1">
                                        {{ $address->address_line }}, {{ $address->ward->name }}, {{ $address->district->name }},
                                        {{ $address->province->name }}
                                    </p>
                                    <p class="card-text text-muted">
                                        Điện thoại: {{ $address->phone }}
                                    </p>
                                    <hr>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('account.addresses.edit', $address->id) }}"
                                            class="btn btn-secondary btn-sm">Sửa</a>

                                        {{-- Nút đặt làm mặc định (chỉ hiển thị nếu chưa phải mặc định) --}}
                                        @if(!$address->is_default)
                                            <form action="{{ route('account.addresses.setDefault', $address->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-primary btn-sm">Đặt làm mặc định</button>
                                            </form>
                                        @endif

                                        {{-- Nút Xóa với modal xác nhận --}}
                                        <button type="button" class="btn btn-outline-danger btn-sm ms-auto" data-bs-toggle="modal"
                                            data-bs-target="#deleteAddressModal"
                                            data-delete-url="{{ route('account.addresses.destroy', $address->id) }}">
                                            Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Thêm liên kết phân trang --}}
                <div class="d-flex justify-content-center mt-4">
                    {{ $addresses->links('customer.vendor.pagination') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Xác nhận Xóa Địa chỉ --}}
    <div class="modal fade" id="deleteAddressModal" tabindex="-1" aria-labelledby="deleteAddressModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAddressModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa địa chỉ này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <form id="deleteAddressForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Script để xử lý modal xác nhận xóa --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteAddressModal = document.getElementById('deleteAddressModal');
            if (deleteAddressModal) {
                deleteAddressModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const deleteUrl = button.getAttribute('data-delete-url');
                    const form = deleteAddressModal.querySelector('#deleteAddressForm');
                    form.setAttribute('action', deleteUrl);
                });
            }
        });
    </script>
@endpush