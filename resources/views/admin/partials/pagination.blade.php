@if ($paginator->hasPages())
    <nav class="admin-pager" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="admin-pager__btn is-disabled" aria-disabled="true" aria-label="Previous">Previous</span>
        @else
            <a class="admin-pager__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">Previous</a>
        @endif

        <div class="admin-pager__pages" aria-label="Page numbers">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-pager__ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="admin-pager__link is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="admin-pager__link" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="admin-pager__btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">Next</a>
        @else
            <span class="admin-pager__btn is-disabled" aria-disabled="true" aria-label="Next">Next</span>
        @endif
    </nav>
@endif
