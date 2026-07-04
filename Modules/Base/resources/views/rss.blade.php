<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ $feed['title'] }}</title>
        <link>{{ $feed['link'] }}</link>
        <description>{{ $feed['description'] }}</description>
        <language>{{ $feed['language'] }}</language>
        <lastBuildDate>{{ $feed['lastBuildDate'] }}</lastBuildDate>
        <atom:link href="{{ $feed['selfUrl'] }}" rel="self" type="application/rss+xml" />
@foreach ($feed['items'] as $item)
        <item>
            <title>{{ $item['title'] }}</title>
            <link>{{ $item['link'] }}</link>
            <guid isPermaLink="true">{{ $item['guid'] }}</guid>
            <pubDate>{{ $item['pubDate'] }}</pubDate>
            <description><![CDATA[{!! $item['description'] !!}]]></description>
            <content:encoded><![CDATA[{!! $item['content'] !!}]]></content:encoded>
@if (! empty($item['category']))
            <category>{{ $item['category'] }}</category>
@endif
@if (! empty($item['imageUrl']) && ! empty($item['imageType']))
            <enclosure url="{{ $item['imageUrl'] }}" type="{{ $item['imageType'] }}" length="0" />
@endif
        </item>
@endforeach
    </channel>
</rss>
