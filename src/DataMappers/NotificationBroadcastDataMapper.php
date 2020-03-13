<?php

namespace Railroad\Railnotifications\DataMappers;

use Illuminate\Database\Query\Builder;
use Railroad\Railmap\DataMapper\DatabaseDataMapperBase;
use Railroad\Railmap\Entity\Links\OneToOne;
use Railroad\Railnotifications\Entities\NotificationOld;
use Railroad\Railnotifications\Entities\NotificationBroadcastOld;

/**
 * Class NotificationBroadcastDataMapper
 *
 * @package Railroad\Railnotifications\DataMappers
 *
 * @method NotificationBroadcastOld[] getWithQuery(callable $queryCallback, $columns = ['*'])
 * @method NotificationBroadcastOld get($id)
 * @method NotificationBroadcastOld[] getMany($ids)
 */
class NotificationBroadcastDataMapper extends DatabaseDataMapperBase
{
    public $table = 'notification_broadcasts';

    /**
     * @return array
     */
    public function mapTo()
    {
        return [
            'id' => 'id',
            'channel' => 'channel',
            'type' => 'type',
            'status' => 'status',
            'report' => 'report',
            'notificationId' => 'notification_id',
            'aggregationGroupId' => 'aggregation_group_id',
            'broadcastOn' => 'broadcast_on',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
    }

    /**
     * @return Builder
     */
    public function gettingQuery()
    {
        return parent::gettingQuery()->orderBy('broadcast_on', 'desc');
    }

    public function links()
    {
        return ['notification' => new OneToOne(NotificationOld::class, 'notificationId', 'id', 'notification')];
    }

    /**
     * @return NotificationBroadcastOld
     */
    public function entity()
    {
        return new NotificationBroadcastOld();
    }
}