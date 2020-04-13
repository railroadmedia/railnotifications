<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    {{--  Fonts  --}}
    <link href="https://fonts.googleapis.com/css?family=Open+Sans300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://dpwjbsxqtam5n.cloudfront.net/fonts/font-awesome-5/fontawesome-all.min.css">


    {{--  Global CSS  --}}

</head>

<body>

<table>
    <tr>
        <th colspan="2">
            <a target="_blank" href="{{ url()->route('members.home') }}">
                <img src="{{ cdn('logo/logo-white.png') }}">
            </a>
        </th>
    </tr>

    @foreach(array_slice($notificationRows, 0, 10) as $notificationRow)
        {!! $notificationRow !!}
    @endforeach

    @if(count($notificationRows) > 10)
        <tr style="border-bottom:1px solid #d1d1d1;text-align:center;">
            <td colspan="2">
                <a style="white-space:nowrap;display:inline-block;"
                   href="{{ url()->route('members.profile.notifications') }}">
                    {{ count($notificationRows) - 10 }}
                    More Notifications
                </a>
            </td>
        </tr>
    @endif

    <tfoot>
    <tr>
        <td colspan="2">
{{--            <a href="https://facebook.com/pianoteofficial">--}}
{{--                <img src="{{ cdn('icons/facebook.png') }}">--}}
{{--            </a>--}}
{{--            <a href="https://youtube.com/user/pianolessonscom">--}}
{{--                <img src="{{ cdn('icons/youtube.png') }}">--}}
{{--            </a>--}}
{{--            <a href="https://instagram.com/pianoteofficial">--}}
{{--                <img src="{{ cdn('icons/instagram.png') }}">--}}
{{--            </a>--}}
            <h6>Don't like receiving these emails? Change your <a
                        href="{{ url()->route('members.profile.settings') }}">notification settings</a> to limit
                the emails you receive.</h6>
            <p>Please do not reply to this email</p>
        </td>
    </tr>
    </tfoot>
</table>

</body>
</html>