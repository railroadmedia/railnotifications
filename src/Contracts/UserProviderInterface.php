<?php

namespace Railroad\Railnotifications\Contracts;

use League\Fractal\TransformerAbstract;
use Railroad\Ecommerce\Entities\User;
use Railroad\Railnotifications\Entities\User as RailnotificationUser;

interface UserProviderInterface
{
    /**
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id)
    : ?User;

    /**
     * @param int $id
     * @return User|null
     */
    public function getRailnotificationsUserById(int $id)
    : ?RailnotificationUser;

    /**
     * @param User $user
     * @return int
     */
    public function getRailnotificationsUserId(RailnotificationUser $user)
    : ?int;

    /**
     * @param User $user
     * @return int
     */
    public function getUserId(User $user)
    : int;

    /**
     * @param array $ids
     * @return User[]
     */
    public function getUsersByIds(array $ids)
    : array;

    /**
     * @param int $id
     * @param string $brand
     * @return array
     */
    public function getUserByLegacyId(int $id, string $brand)
    : array;

    /**
     * @return User|null
     */
    public function getCurrentUser()
    : ?User;

    /**
     * @return int|null
     */
    public function getCurrentUserId()
    : ?int;

    /**
     * @return TransformerAbstract
     */
    public function getUserTransformer()
    : TransformerAbstract;

    /**
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function createUser(string $email, string $password)
    : ?User;

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
}
