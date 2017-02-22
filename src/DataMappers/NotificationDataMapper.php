<?php

namespace Railroad\Railnotifications\DataMappers;

use Railroad\Railmap\DataMapper\DatabaseDataMapperBase;
use Railroad\Railnotifications\Entities\Notification;

/**
 * Class NotificationDataMapper
 *
 * @package Railroad\Railforums\DataMappers
 *
 * @method Notification[] getWithQuery(callable $queryCallback, $columns = ['*'])
 * @method Notification get($id)
 * @method Notification[] getMany($ids)
 */
class NotificationDataMapper extends DatabaseDataMapperBase
{
    public $table = 'notifications';

    /**
     * @return array
     */
    public function mapTo()
    {
        return [
            'id' => 'id',
            'type' => 'type',
            'data' => 'data',
            'recipientId' => 'recipient_id',
            'readAt' => 'read_at',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
    }

    /**
     * @return array
     */
    public function types()
    {
        return ['data' => 'json'];
    }

    /**
     * @return Notification
     */
    public function entity()
    {
        return new Notification();
    }
}