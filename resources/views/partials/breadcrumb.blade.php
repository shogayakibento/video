@if(count($items) > 1)
<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        @foreach($items as $item)
            <li class="breadcrumb-item">
                @if(!$loop->last && isset($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    <span class="breadcrumb-sep" aria-hidden="true">›</span>
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

@php
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->values()->map(function ($item, $idx) {
            $entry = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $item['label'],
            ];
            if (isset($item['url'])) {
                $entry['item'] = $item['url'];
            }
            return $entry;
        })->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif
