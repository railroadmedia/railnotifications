<?php

namespace Railroad\Railnotifications\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use Railroad\Railnotifications\Contracts\UserProviderInterface;

class UserType extends IntegerType
{
    const USER_TYPE = 'railnotification_user';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getUnsignedDeclaration($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value !== null) {

            $userProvider = app()->make(UserProviderInterface::class);

            return $userProvider->getRailnotificationsUserById($value);
        }

        return null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value !== null) {

            $userProvider = app()->make(UserProviderInterface::class);

            return $userProvider->getUserId($value);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::USER_TYPE;
    }
}
