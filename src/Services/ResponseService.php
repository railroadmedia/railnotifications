<?php

namespace Railroad\Railnotifications\Services;

use Doctrine\ORM\QueryBuilder;
use League\Fractal\Serializer\ArraySerializer;
use Railroad\Doctrine\Services\FractalResponseService;
use Railroad\Railnotifications\Transformers\NotificationsBroadcastTransformer;
use Railroad\Railnotifications\Transformers\NotificationsTransformer;
use Spatie\Fractal\Fractal;

class ResponseService extends FractalResponseService
{
    /**
     * @param $entityOrEntities
     * @param QueryBuilder|null $queryBuilder
     * @param array $includes
     * @return Fractal
     */
    public static function notification(
        $entityOrEntities,
        QueryBuilder $queryBuilder = null,
        array $includes = []
    ) {
        return self::create(
            $entityOrEntities,
            '',
            new NotificationsTransformer(),
            new ArraySerializer(),
            $queryBuilder
        )
            ->parseIncludes($includes);
    }

    /**
     * @param $entityOrEntities
     * @param QueryBuilder|null $queryBuilder
     * @param array $includes
     * @return Fractal
     */
    public static function notificationBroadcast(
        $entityOrEntities,
        QueryBuilder $queryBuilder = null,
        array $includes = []
    ) {
        return self::create(
            $entityOrEntities,
            '',
            new NotificationsBroadcastTransformer(),
            new ArraySerializer(),
            $queryBuilder
        )
            ->parseIncludes($includes);
    }
}