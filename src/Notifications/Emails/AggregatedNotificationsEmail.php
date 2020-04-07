<?php

namespace Railroad\Railnotifications\Notifications\Emails;

use Illuminate\Mail\Mailable;

class AggregatedNotificationsEmail extends Mailable
{
    private $recipientEmail;
    private $renderedNotificationRows;

    public function __construct(
        $recipientEmail,
        $renderedNotificationRows
    ) {
        $this->recipientEmail = $recipientEmail;
        $this->renderedNotificationRows = $renderedNotificationRows;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->to($this->recipientEmail)
            ->from('system@pianote.com', 'Pianote')
            ->replyTo('support@pianote.com')
            ->subject(
                'You Have ' . count($this->renderedNotificationRows) . ' New Notifications - Pianote Forums'
            )
            ->view('railnotifications::all-notification-email')
            ->with(
                [
                    'notificationRows' => $this->renderedNotificationRows,
                ]
            );
    }
}
