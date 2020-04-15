<?php

namespace Railroad\Railnotifications\Contracts;

use Railroad\Railnotifications\Entities\User as RailnotificationUser;

interface UserProviderInterface
{
    /**
     * @param int $id
     * @return User|null
     */
    public function getRailnotificationsUserById(int $id)
    : ?RailnotificationUser;

    /**
     * @param RailnotificationUser $user
     * @return int|null
     */
    public function getRailnotificationsUserId(RailnotificationUser $user) :?int;

    /**
     * @param int $userId
     * @param array|null $types
     * @return array|null
     */
    public function getUserFirebaseTokens(int $userId, $types = [])
    : ?array;

    /**
     * @param int $userId
     * @param array $tokens
     * @return mixed
     */
    public function deleteUserFirebaseTokens(int $userId, array $tokens);

    /**
     * @param $userId
     * @param $oldToken
     * @param $newToken
     * @return mixed
     */
    public function updateUserFirebaseToken($userId, $oldToken, $newToken);
}
