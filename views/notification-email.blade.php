<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    {{--  Fonts  --}}
    <link href="https://fonts.googleapis.com/css?family=Open+Sans300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://dpwjbsxqtam5n.cloudfront.net/fonts/font-awesome-5/fontawesome-all.min.css">


    {{--  Global CSS  --}}
    <style>
        th {
            width: 100%;
            background: #ff383f;
            text-align: center;
            padding: 20px 15px;
        }

        th img {
            width: 250px;
            height: auto;
            display: inline-block;
            max-width: 100%;
        }

        table {
            margin: 0 auto;
            max-width: 100% !important;
            display: block;
            border-collapse: collapse;
        }

        table td {
            padding: 30px 0;
        }

        table td img {
            width: 60px;
            max-width: 60px;
            border-radius: 50%;
        }

        table td h1 {
            font: 300 16px/1em 'Open Sans', sans-serif;
            color: #777;
            margin-bottom: 15px;
        }

        table td h2 {
            font: 700 16px/1em 'Open Sans', sans-serif;
            color: #000;
            margin-bottom: 10px;
        }

        table td p {
            font: 300 16px/20px 'Open Sans', sans-serif;
            color: #000;
            margin-top: 0;
            margin-bottom: 15px;
        }

        table td a {
            display: inline-block;
            text-decoration: none;
            cursor: pointer;
            background: #ff383f;
            color: #fff !important;
            border-radius: 5px;
            text-transform: uppercase;
            padding: 10px;
        }

        table tfoot td {
            text-align: center;
        }

        table tfoot td a {
            background: none;
            border-radius: none;
            padding: 0;
        }

        table tfoot td img {
            width: 45px;
            height: auto;
            display: inline-block;
            margin: 0 5px;
        }

        table tfoot td h6 {
            font: 300 14px/1em 'Open Sans', sans-serif;
            color: #777;
            margin-bottom: 10px;
        }

        table tfoot td h6 a {
            font-size: 14px;
            text-transform: none;
            text-decoration: underline;
            color: #777 !important;
        }

        table tfoot td p {
            font: 300 12px/1em 'Open Sans', sans-serif;
            color: #777;
        }
    </style>
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
            <a href="https://facebook.com/pianoteofficial">
                <img src="{{ cdn('icons/facebook.png') }}">
            </a>
            <a href="https://youtube.com/user/pianolessonscom">
                <img src="{{ cdn('icons/youtube.png') }}">
            </a>
            <a href="https://instagram.com/pianoteofficial">
                <img src="{{ cdn('icons/instagram.png') }}">
            </a>
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