@extends('notifications.notification-email')

@section('page-body')
    @foreach(array_slice($notificationRows, 0, 10) as $notificationRow)
        {!! $notificationRow !!}
    @endforeach

    @if(count($notificationRows) > 0)
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
@stop
