<tr style="border-bottom:1px solid #d1d1d1">
    <td style="width:80px;" valign="top">
        <img src="{{ $avatarUrl }}">
    </td>
    <td style="max-width:99%;padding:30px 15px;">
        <h1></h1>
        <h2>{{ $title }}</h2>
        <p>{{ mb_strimwidth(htmlspecialchars(strip_tags($content)), 0, 250, "...") }}</p>
        <a href="{{ $contentUrl }}">
            View
        </a>
    </td>
</tr>