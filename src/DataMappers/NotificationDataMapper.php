<?php

namespace Railroad\Railnotifications\DataMappers;

use Illuminate\Database\Query\Builder;
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
            'readOn' => 'read_on',
            'createdOn' => 'created_on',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
    }

    /**
     * @return Builder
     */
    public function gettingQuery()
    {
        return parent::gettingQuery()->orderBy('created_on', 'desc');
    }

    /**
     * @param int $recipientId
     * @param int $amount
     * @param int $skip
     * @return Notification[]
     */
    public function getManyForRecipientPaginated(int $recipientId, int $amount, int $skip)
    {
        return $this->getWithQuery(
            function (Builder $query) use ($recipientId, $amount, $skip) {
                return $query->where('recipient_id', $recipientId)->limit($amount)->skip($skip);
            }
        );
    }

    /**
     * @param int $recipientId
     * @return Notification[]
     */
    public function getAllUnReadForRecipient(int $recipientId)
    {
        return $this->getWithQuery(
            function (Builder $query) use ($recipientId) {
                return $query->where('recipient_id', $recipientId)->whereNull('read_on');
            }
        );
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