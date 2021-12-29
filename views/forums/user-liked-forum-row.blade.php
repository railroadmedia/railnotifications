<tr style="border-bottom:1px solid #d1d1d1">
    <td style="max-width:99%;padding:30px 15px;">

        <h1>{{ $totalLikes == 1? $totalLikes : explode('@', $displayName)[0] . ' people'  }} liked your forum post.</h1>
        <h2>{{ $title }}</h2>
        <p>{!! $content !!}</p>
        <a href="{{ $contentUrl }}">
            View Post
        </a>
    </td>
</tr>
