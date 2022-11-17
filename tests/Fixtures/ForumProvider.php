<?php

namespace Railroad\Railnotifications\Tests\Fixtures;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use League\Fractal\TransformerAbstract;
use Railroad\Railnotifications\Contracts\ContentProviderInterface;
use Railroad\Railnotifications\Contracts\RailforumProviderInterface;
use Railroad\Railnotifications\Contracts\UserProviderInterface;
use Railroad\Railnotifications\Entities\User;
use Railroad\Railnotifications\Tests\TestCase;

class ForumProvider implements RailforumProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getPostById($id)
    {
        // TODO: Implement getPostById() method.
    }

    public function getPostLikeCount(int $postId): int
    {
        // TODO: Implement getPostLikeCount() method.
    }

    /**
     * @inheritDoc
     */
    public function getThreadById($id)
    {
        // TODO: Implement getThreadById() method.
    }

    public function getThreadFollowerIds($id)
    {
        // TODO: Implement getThreadFollowerIds() method.
    }

    public function getAllPostIdsInThread($id)
    {
        // TODO: Implement getAllPostIdsInThread() method.
    }
}
