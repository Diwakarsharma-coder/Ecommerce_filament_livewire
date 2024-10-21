@if ($paginator->hasPages())
    <nav aria-label="Page navigation example">
        <ul class="flex justify-center space-x-1">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <a class="px-3 py-1.5 text-gray-500 bg-gray-200 rounded-md cursor-not-allowed" href="#" tabindex="-1">Previous</a>
                </li>
            @else
                <li class="page-item">
                    <a class="px-3 py-1.5 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100" href="{{ $paginator->previousPageUrl() }}">Previous</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="px-3 py-1.5 text-gray-500 bg-gray-200 rounded-md">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="px-3 py-1.5 text-white bg-blue-500 rounded-md">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="px-3 py-1.5 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="px-3 py-1.5 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                </li>
            @else
                <li class="page-item disabled">
                    <a class="px-3 py-1.5 text-gray-500 bg-gray-200 rounded-md cursor-not-allowed" href="#">Next</a>
                </li>
            @endif
        </ul>
    </nav>
@endif
