<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Entities\NotificationBroadcast;

class NotificationsBroadcastTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['notification'];

    /**
     * @param NotificationBroadcast $notificationBroadcast
     * @return array
     */
    public function transform(NotificationBroadcast $notificationBroadcast)
    {
        return [
            'id' => $notificationBroadcast->getId(),
            'channel' => $notificationBroadcast->getChannel(),
            'type' => $notificationBroadcast->getType(),
            'status' => $notificationBroadcast->getStatus(),
            'report' => $notificationBroadcast->getReport(),
            'aggregation_group_id' => $notificationBroadcast->getAggregationGroupId(),
            'broadcast_on' => $notificationBroadcast->getBroadcastOn() ?
                $notificationBroadcast->getBroadcastOn()
                    ->toDateTimeString() : null,
            'created_at' => $notificationBroadcast->getCreatedAt() ?
                $notificationBroadcast->getCreatedAt()
                    ->toDateTimeString() : null,
            'updated_at' => $notificationBroadcast->getUpdatedAt() ?
                $notificationBroadcast->getUpdatedAt()
                    ->toDateTimeString() : null,
        ];
    }

    /**
     * @param NotificationBroadcast $notificationBroadcast
     * @return Item
     */
    public function includeNotification(NotificationBroadcast $notificationBroadcast)
    {
        return $this->item(
            $notificationBroadcast->getNotification(),
            new NotificationsTransformer(),
            'notification'
        );
    }
}
