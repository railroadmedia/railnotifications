<?php

namespace Railroad\Railnotifications\DataMappers;

use Illuminate\Database\Query\Builder;
use Railroad\Railmap\DataMapper\DatabaseDataMapperBase;
use Railroad\Railmap\Entity\Links\OneToMany;
use Railroad\Railnotifications\Entities\NotificationOld;
use Railroad\Railnotifications\Entities\NotificationBroadcastOld;

/**
 * Class NotificationDataMapper
 *
 * @package Railroad\Railnotifications\DataMappers
 *
 * @method NotificationOld[] getWithQuery(callable $queryCallback, $columns = ['*'])
 * @method NotificationOld get($id)
 * @method NotificationOld[] getMany($ids)
 */
class NotificationDataMapper extends DatabaseDataMapperBase
{
    public $table = 'notifications';
    public $with = ['broadcasts'];

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
     * @return int
     */
    public function getUnreadCount(int $recipientId)
    {
        return $this->count(
            function (Builder $query) use ($recipientId) {
                return $query->where('recipient_id', $recipientId)->whereNull('read_on');
            }
        );
    }

    /**
     * @param int $recipientId
     * @return int
     */
    public function getReadCount(int $recipientId)
    {
        return $this->count(
            function (Builder $query) use ($recipientId) {
                return $query->where('recipient_id', $recipientId)->whereNotNull('read_on');
            }
        );
    }

    /**
     * @param int $recipientId
     * @param int $amount
     * @param int $skip
     * @return NotificationOld[]
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
     * @param string $createdAfterDateTimeString
     * @return NotificationOld[]
     */
    public function getAllUnReadForRecipient(int $recipientId, string $createdAfterDateTimeString = null)
    {
        return $this->getWithQuery(
            function (Builder $query) use ($recipientId, $createdAfterDateTimeString) {
                if (!is_null($createdAfterDateTimeString)) {
                    $query->where('created_on', '>=', $createdAfterDateTimeString);
                }

                return $query->where('recipient_id', $recipientId)->whereNull('read_on');
            }
        );
    }

    /**
     * @param string|null $createdAfterDateTimeString
     * @return array
     */
    public function getAllRecipientIdsWithUnreadNotifications(string $createdAfterDateTimeString = null)
    {
        return array_unique(
            $this->list(
                function (Builder $query) use ($createdAfterDateTimeString) {
                    return $query->whereNull('read_on')->where(
                        'created_on',
                        '>=',
                        $createdAfterDateTimeString
                    );
                },
                'recipient_id'
            )
        );
    }

    /**
     * @return array
     */
    public function types()
    {
        return ['data' => 'json'];
    }

    public function links()
    {
        return [
            'broadcasts' => new OneToMany(
                NotificationBroadcastOld::class,
                'id',
                'notificationId',
                'broadcasts'
            )
        ];
    }

    /**
     * @return NotificationOld
     */
    public function entity()
    {
        return new NotificationOld();
    }
}