<div class="tab-pane fade" id="reviews-pane" role="tabpanel" aria-labelledby="reviews-tab">
    <div class="row">
        {{-- Gọi partial danh sách review --}}
        @include('customer.products.partials._review_list')

        {{-- Gọi partial form review --}}
        @include('customer.products.partials._review_form')
    </div>
</div>