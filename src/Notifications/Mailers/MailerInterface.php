<?php

namespace Railroad\Railnotifications\Notifications\Mailers;

interface MailerInterface
{
    public function send(array $notifications);
}