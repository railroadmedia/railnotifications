<?php

namespace Railroad\Railnotifications\Tests\Fixtures;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\User;
use Railroad\Railnotifications\Tests\TestCase;


class UserProvider implements UserProviderInterface
{

    /**
     * @inheritDoc
     */
    public function getUserById(int $id)
    : ?\Railroad\Ecommerce\Entities\User {
        $user = DB::table('users')->find($id);

        if ($user) {
            return new \Railroad\Ecommerce\Entities\User($id, $user->email, $user->display_name, $user->profile_picture_url);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getRailnotificationsUserById(int $id)
    : ?User {
        $user = DB::table('users')->find($id);

        if ($user) {
            return new \Railroad\Railnotifications\Entities\User($id, $user->email, $user->display_name, $user->profile_picture_url);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getRailnotificationsUserId(User $user)
    : ?int {
        return $user->getId();
    }

    /**
     * @inheritDoc
     */
    public function getUserId(\Railroad\Ecommerce\Entities\User $user)
    : int {
        return $user->getId();
    }

    /**
     * @inheritDoc
     */
    public function getUsersByIds(array $ids)
    : array {
        // TODO: Implement getUsersByIds() method.
    }

    /**
     * @inheritDoc
     */
    public function getUserByLegacyId(int $id, string $brand)
    : array {
        // TODO: Implement getUserByLegacyId() method.
    }

    /**
     * @inheritDoc
     */
    public function getCurrentUser()
    : ?\Railroad\Ecommerce\Entities\User
    {
        // TODO: Implement getCurrentUser() method.
    }

    /**
     * @inheritDoc
     */
    public function getCurrentUserId()
    : ?int
    {
        // TODO: Implement getCurrentUserId() method.
    }

   /**
     * @inheritDoc
     */
    public function createUser(string $email, string $password)
    : ?\Railroad\Ecommerce\Entities\User {
        // TODO: Implement createUser() method.
    }

    /**
     * @return TransformerAbstract
     */
    public function getUserTransformer(): TransformerAbstract
    {
        return new UserTransformer();
    }
}
