<?php

namespace Railroad\Railnotifications\Transformers;

use Doctrine\Common\Persistence\Proxy;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;

class UserNotificationsSettingsTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    public function transform(Notification $notification)
    {

        return [
            'id' => $notification->getId(),
         ];
    }
}
