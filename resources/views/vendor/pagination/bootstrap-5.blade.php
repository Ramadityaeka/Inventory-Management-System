@if ($paginator->hasPages())
    <div class="d-flex flex-column align-items-center gap-2 py-2">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm justify-content-center mb-0">
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

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" style="padding: 0.25rem 0.5rem;">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link" style="padding: 0.25rem 0.5rem;">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}" style="padding: 0.25rem 0.5rem;">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

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

        <small class="text-muted">
            Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} hasil
        </small>
    </div>
@endif
