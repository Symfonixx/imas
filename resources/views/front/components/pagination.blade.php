@php
    /** @var \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Pagination\AbstractPaginator|null $paginator */
    $paginator = $paginator ?? null;
@endphp
@if ($paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <nav class="imas-pagination" aria-label="Pagination">
        {{ $paginator->links() }}
    </nav>
@endif
