@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center">
        <ul class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li><span class="px-3 py-2 rounded bg-gray-700 text-gray-500 text-sm cursor-not-allowed">&laquo;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 rounded bg-secondary text-gray-300 hover:bg-gray-600 text-sm transition">&laquo;</a></li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-3 py-2 text-gray-500 text-sm">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="px-3 py-2 rounded bg-accent text-white text-sm font-bold">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="px-3 py-2 rounded bg-secondary text-gray-300 hover:bg-gray-600 text-sm transition">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 rounded bg-secondary text-gray-300 hover:bg-gray-600 text-sm transition">&raquo;</a></li>
            @else
                <li><span class="px-3 py-2 rounded bg-gray-700 text-gray-500 text-sm cursor-not-allowed">&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
