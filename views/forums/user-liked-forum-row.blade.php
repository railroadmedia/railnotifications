<tr style="border-bottom:1px solid #d1d1d1">
    <td style="width:80px;" valign="top">
        <img style="border-radius:0;" src="{{ cdn('icons/like.png') }}">
    </td>
    <td style="max-width:99%;padding:30px 15px;">

        <h1>{{ $totalLikes == 1? $totalLikes : $totalLikes . ' people'  }} liked your forum post.</h1>
        <h2>{{ $title }}</h2>
        <p>{{ mb_strimwidth(htmlspecialchars(strip_tags($content)), 0, 250, "...") }}</p>
        <a href="{{ $contentUrl }}">
            View Post
        </a>
    </td>
</tr>
