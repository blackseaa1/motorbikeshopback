<div id="productDetailTooltip" class="product-detail-tooltip" style="display: none; position: absolute; background-color: #fff; border: 1px solid #ddd; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px; z-index: 1000; max-width: 350px; border-radius: 8px;">
    <div class="d-flex align-items-center mb-3">
        <img id="tooltipProductThumbnail" src="" alt="Product Thumbnail" class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
        <div>
            <h5 id="tooltipProductName" class="mb-1"></h5>
            <p class="text-muted mb-0">Giá: <span id="tooltipProductPrice"></span></p>
        </div>
    </div>
    <p class="mb-1"><strong>Tồn kho:</strong> <span id="tooltipProductStock"></span></p>
    <p class="mb-1"><strong>Trạng thái:</strong> <span id="tooltipProductStatus"></span></p>
    <p class="mb-1"><strong>Danh mục:</strong> <span id="tooltipProductCategory"></span></p>
    <p class="mb-1"><strong>Thương hiệu:</strong> <span id="tooltipProductBrand"></span></p>
    <p class="mb-1"><strong>Chất liệu:</strong> <span id="tooltipProductMaterial"></span></p>
    <p class="mb-1"><strong>Màu sắc:</strong> <span id="tooltipProductColor"></span></p>
    <p class="mb-1"><strong>Thông số kỹ thuật:</strong> <span id="tooltipProductSpecs"></span></p>
    <p class="mb-1"><strong>Dòng xe tương thích:</strong> <span id="tooltipProductVehicleModels"></span></p>
    <p class="mb-0"><strong>Đánh giá:</strong> <span id="tooltipProductRating"></span> (<span id="tooltipProductReviewsCount"></span> đánh giá)</p>
</div>

<style>
    /* Basic styling for the tooltip, you might want to move this to a dedicated CSS file */
    .product-detail-tooltip {
        /* Add more styling as needed, e.g., animations, specific positioning */
    }
</style>