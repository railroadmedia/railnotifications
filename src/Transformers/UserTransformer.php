<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Entities\User;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'display_name' => $user->getDisplayName(),
            'profile_image_url' => $user->getAvatar()
        ];
    }
}
