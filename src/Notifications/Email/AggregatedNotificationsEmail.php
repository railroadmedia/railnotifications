<?php

namespace Railroad\Railnotifications\Notifications\Email;

use Illuminate\Mail\Mailable;

class AggregatedNotificationsEmail extends Mailable
{
    private $recipientEmail;

    private $renderedNotificationRows;

    public $subject;

    /**
     * AggregatedNotificationsEmail constructor.
     *
     * @param $recipientEmail
     * @param $renderedNotificationRows
     * @param $subject
     */
    public function __construct(
        $recipientEmail,
        $renderedNotificationRows,
        $subject
    ) {
        $this->recipientEmail = $recipientEmail;
        $this->renderedNotificationRows = $renderedNotificationRows;
        $this->subject = $subject;
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
            ->from(config('railnotifications.email_address_from'), config('railnotifications.email_brand_from'))
            ->replyTo(config('railnotifications.email_reply_address'))
            ->subject($this->subject)
            ->view('railnotifications::all-notification-email')
            ->with(
                [
                    'notificationRows' => $this->renderedNotificationRows,
                    'to' => $this->recipientEmail,
                ]
            );
    }
}
