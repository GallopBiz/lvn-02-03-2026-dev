@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $from = $paginator->firstItem() ?: 0;
    $to = $paginator->lastItem() ?: 0;
    $total = $paginator->total();
    $pages = [];

    if ($lastPage <= 7) {
        $pages = range(1, $lastPage);
    } elseif ($currentPage <= 4) {
        $pages = [1, 2, 3, 4, 5, 'ellipsis', $lastPage];
    } elseif ($currentPage >= $lastPage - 3) {
        $pages = [1, 'ellipsis', $lastPage - 4, $lastPage - 3, $lastPage - 2, $lastPage - 1, $lastPage];
    } else {
        $pages = [1, 'ellipsis', $currentPage - 1, $currentPage, $currentPage + 1, 'ellipsis', $lastPage];
    }
@endphp

<div class="row" id="{{ $tableId }}_pagination_wrapper">
    <div class="col-sm-12 col-md-5">
        <div class="dataTables_info" id="{{ $tableId }}_info" role="status" aria-live="polite">
            Showing {{ number_format($from) }} to {{ number_format($to) }} of {{ number_format($total) }} entries
        </div>
    </div>
    <div class="col-sm-12 col-md-7">
        <div class="dataTables_paginate paging_simple_numbers text-md-right" id="{{ $tableId }}_paginate">
            <ul class="pagination justify-content-md-end">
                <li class="paginate_button page-item previous {{ $paginator->onFirstPage() ? 'disabled' : '' }}" id="{{ $tableId }}_previous">
                    <a href="{{ $paginator->onFirstPage() ? '#' : $paginator->previousPageUrl() }}" aria-controls="{{ $tableId }}" data-dt-idx="0" tabindex="0" class="page-link">Previous</a>
                </li>
                @foreach($pages as $index => $page)
                    @if($page === 'ellipsis')
                        <li class="paginate_button page-item disabled" id="{{ $tableId }}_ellipsis_{{ $index }}">
                            <a href="#" aria-controls="{{ $tableId }}" data-dt-idx="{{ $index + 1 }}" tabindex="0" class="page-link">...</a>
                        </li>
                    @else
                        <li class="paginate_button page-item {{ $currentPage === $page ? 'active' : '' }}">
                            <a href="{{ $paginator->url($page) }}" aria-controls="{{ $tableId }}" data-dt-idx="{{ $index + 1 }}" tabindex="0" class="page-link">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
                <li class="paginate_button page-item next {{ $paginator->hasMorePages() ? '' : 'disabled' }}" id="{{ $tableId }}_next">
                    <a href="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '#' }}" aria-controls="{{ $tableId }}" data-dt-idx="{{ count($pages) + 1 }}" tabindex="0" class="page-link">Next</a>
                </li>
            </ul>
        </div>
    </div>
</div>
