<?php

namespace Railroad\Railnotifications\Notifications\Emails;

use Illuminate\Mail\Mailable;

class AggregatedNotificationsEmail extends Mailable
{
    private $recipientEmail;

    private $renderedNotificationRows;

    public $subject;

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
            ->from(config('railnotifications.emailAddressFrom'), config('railnotifications.emailBrandFrom'))
            ->replyTo(config('railnotifications.replyAddress'))
            ->subject($this->subject)
            ->view('railnotifications::all-notification-email')
            ->with(
                [
                    'notificationRows' => $this->renderedNotificationRows,
                ]
            );
    }
}
