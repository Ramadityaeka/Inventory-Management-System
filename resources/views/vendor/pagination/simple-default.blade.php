@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm justify-content-center mb-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="padding: 0.25rem 0.5rem;">
                        <i class="bi bi-chevron-left" style="font-size: 0.875rem;"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="padding: 0.25rem 0.5rem;">
                        <i class="bi bi-chevron-left" style="font-size: 0.875rem;"></i>
                    </a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="padding: 0.25rem 0.5rem;">
                        <i class="bi bi-chevron-right" style="font-size: 0.875rem;"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" style="padding: 0.25rem 0.5rem;">
                        <i class="bi bi-chevron-right" style="font-size: 0.875rem;"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
