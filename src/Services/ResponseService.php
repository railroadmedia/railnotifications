<?php

namespace Railroad\Railnotifications\Services;

use Doctrine\ORM\QueryBuilder;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\JsonApiSerializer;
use Railroad\Doctrine\Services\FractalResponseService;
use Railroad\Railnotifications\Transformers\NotificationsTransformer;
use Spatie\Fractal\Fractal;

class ResponseService extends FractalResponseService
{
    /**
     * @param $entityOrEntities
     * @param QueryBuilder|null $queryBuilder
     * @param array $includes
     * @param array $filterOptions
     * @return Fractal
     */
    public static function notification(
        $entityOrEntities,
        QueryBuilder $queryBuilder = null,
        array $includes = [],
        array $filterOptions = []
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
}