<?php

namespace Railroad\Railnotifications\Tests\Fixtures;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\User;
use Railroad\Railnotifications\Tests\TestCase;

class ContentProvider implements ContentProviderInterface
{

    /**
     * @inheritDoc
     */
    public function getContentById($id)
    {
        // TODO: Implement getContentById() method.
    }

    /**
     * @inheritDoc
     */
    public function getCommentById($id)
    {
        return [
            'id' => rand(),
            'user_id' => 1,
            'parent_id' => rand(),
            'content_id' => rand(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getContentTransformer()
    {
        // TODO: Implement getContentTransformer() method.
    }
}
