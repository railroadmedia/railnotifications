<?php

namespace Railroad\Railnotifications\Tests\Fixtures;

use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\UserInterface;
use Railroad\Railnotifications\Entities\User;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->getId()
        ];
    }
}
