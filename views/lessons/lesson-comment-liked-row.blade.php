<tr style="border-bottom:1px solid #d1d1d1">
    <td style="max-width:80px;" valign="top">
        <img src="{{ $avatarUrl }}">
    </td>
    <td style="max-width:99%;padding:30px 15px;">
        <h1>{{ explode('@', $displayName)[0] }} liked your lesson comment.</h1>
        <h2>{{ $title }}</h2>
        <p>{!! $content !!}</p>
        <a href="{{ $contentUrl }}">
            View Comment
        </a>
    </td>
</tr>