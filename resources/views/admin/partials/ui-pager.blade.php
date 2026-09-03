{{--
    Le pagine di un elenco, nel linguaggio del back office. Si usa così:
    {{ $lista->links('admin.partials.ui-pager') }}
    Laravel passa $paginator e $elements.
--}}
@if ($paginator->hasPages())
    <nav class="ui-pager" role="navigation" aria-label="Pagine dell'elenco">
        <p class="ui-pager__info">
            Da <b>{{ $paginator->firstItem() }}</b> a <b>{{ $paginator->lastItem() }}</b>
            di <b>{{ $paginator->total() }}</b>
        </p>

        <div class="ui-pager__nav">
            @if ($paginator->onFirstPage())
                <span class="ui-action ui-action--icon is-off" aria-hidden="true">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                </span>
            @else
                <a class="ui-action ui-action--icon ui-pager__prev" href="{{ $paginator->previousPageUrl() }}"
                   rel="prev" aria-label="Pagina precedente">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="ui-pager__gap" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="ui-pager__page is-on" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="ui-pager__page" href="{{ $url }}" aria-label="Pagina {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="ui-action ui-action--icon" href="{{ $paginator->nextPageUrl() }}"
                   rel="next" aria-label="Pagina successiva">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                </a>
            @else
                <span class="ui-action ui-action--icon is-off" aria-hidden="true">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                </span>
            @endif
        </div>
    </nav>
@endif
