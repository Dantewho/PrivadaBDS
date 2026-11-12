@if ($paginator->hasPages())
    @php($lastPage = $paginator->lastPage())
    <nav class="pagination admin-pagination" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="page-control is-disabled" aria-disabled="true" aria-label="Anterior">‹</span>
        @else
            <a class="page-control" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">‹</a>
        @endif
        @foreach (range(1, min(4, $lastPage)) as $page)
            @if ($page === $paginator->currentPage())
                <span class="page-number is-current" aria-current="page">{{ $page }}</span>
            @else
                <a class="page-number" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endforeach
        @if ($lastPage > 5)
            <span class="page-ellipsis" aria-hidden="true">…</span>
        @elseif ($lastPage === 5)
            @if ($paginator->currentPage() === 5)
                <span class="page-number is-current" aria-current="page">5</span>
            @else
                <a class="page-number" href="{{ $paginator->url(5) }}">5</a>
            @endif
        @endif
        @if ($lastPage > 5)
            @if ($paginator->currentPage() === $lastPage)
                <span class="page-number is-current" aria-current="page">{{ $lastPage }}</span>
            @else
                <a class="page-number" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
            @endif
        @endif
        @if ($paginator->hasMorePages())
            <a class="page-control" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">›</a>
        @else
            <span class="page-control is-disabled" aria-disabled="true" aria-label="Siguiente">›</span>
        @endif
    </nav>
@endif
