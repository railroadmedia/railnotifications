<?php

namespace Railroad\Railnotifications\Transformers;

use Doctrine\Common\Persistence\Proxy;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\Notification;

class NotificationsTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    public function transform(Notification $notification)
    {
        if ($notification->getRecipient()) {
            $this->defaultIncludes[] = 'recipient';
        }

        return [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'data' => $notification->getData(),
            'read_on' => $notification->getReadOn() ?
                $notification->getReadOn()
                    : null,
            'created_at' => $notification->getCreatedAt() ?
                $notification->getCreatedAt()
           : null,
            'updated_at' => $notification->getUpdatedAt() ?
                $notification->getUpdatedAt()
                  : null,
        ];
    }

    public function includeRecipient(Notification $notification)
    {
        $userProvider = app()->make(UserProviderInterface::class);

        $userTransformer = $userProvider->getUserTransformer();

        return $this->item(
            $notification->getRecipient(),
            $userTransformer,
            'user'
        );
    }
}
