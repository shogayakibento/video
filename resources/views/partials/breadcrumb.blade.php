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

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        @foreach($items as $item)
        {
            "@type": "ListItem",
            "position": {{ $loop->index + 1 }},
            "name": "{{ $item['label'] }}"
            @if(isset($item['url']))
            ,"item": "{{ $item['url'] }}"
            @endif
        }{{ $loop->last ? '' : ',' }}
        @endforeach
    ]
}
</script>
@endif
