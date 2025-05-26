@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex justify-content-center align-items-center flex-column">
        <ul class="pagination mb-3">
            <!-- Previous Button -->
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->onFirstPage() ? '#' : $paginator->previousPageUrl() }}"
                   aria-label="{{ __('pagination.previous') }}"
                   {{ $paginator->onFirstPage() ? 'aria-disabled="true" tabindex="-1"' : '' }}>
                    <svg class="bi" width="16" height="16" fill="currentColor">
                        <use xlink:href="#chevron-left"/>
                    </svg>
                    <span class="ms-1">Trước</span>
                </a>
            </li>

            <!-- Pagination Elements -->
            @foreach ($elements as $element)
                <!-- Ellipsis -->
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">...</span>
                    </li>
                @endif

                <!-- Page Numbers -->
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}"
                            {{ $page == $paginator->currentPage() ? 'aria-current="page"' : '' }}>
                            <a class="page-link" href="{{ $page == $paginator->currentPage() ? '#' : $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                @endif
            @endforeach

            <!-- Next Button -->
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '#' }}"
                   aria-label="{{ __('pagination.next') }}"
                   {{ $paginator->hasMorePages() ? '' : 'aria-disabled="true" tabindex="-1"' }}>
                    <span class="me-1">Kế tiếp</span>
                    <svg class="bi" width="16" height="16" fill="currentColor">
                        <use xlink:href="#chevron-right"/>
                    </svg>
                </a>
            </li>
        </ul>

        <!-- Results Info -->
        <small class="text-muted text-center">
            Đang hiển thị {{ $paginator->firstItem() }} đến {{ $paginator->lastItem() }} của {{ $paginator->total() }} kết quả
        </small>
    </nav>

    <!-- Custom CSS -->
    <style>
        .pagination {
            gap: 0.5rem;
        }

        .page-item {
            transition: all 0.3s ease;
        }

        .page-link {
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.95rem;
            color: #333;
            background-color: #f8f9fa;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.1s ease;
        }

        .page-link:hover {
            background-color: #e2e6ea;
            color: #007bff;
            transform: translateY(-1px);
        }

        .page-item.active .page-link {
            background-color: #007bff;
            color: white;
            font-weight: 600;
        }

        .page-item.disabled .page-link {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .page-link svg {
            vertical-align: middle;
        }

        @media (max-width: 576px) {
            .page-link {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }

            .page-link span {
                display: none;
            }
        }
    </style>

    <!-- SVG Icons for Previous/Next -->
    <svg style="display: none;">
        <symbol id="chevron-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
        </symbol>
        <symbol id="chevron-right" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
        </symbol>
    </svg>
@endif