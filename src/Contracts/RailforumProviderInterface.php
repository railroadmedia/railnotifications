<?php

namespace Railroad\Railnotifications\Contracts;


interface RailforumProviderInterface
{
    /**
     * @param int $id
     * @return array|null
     */
    public function getPostById($id) ;

    public function getPostLikeCount(int $postId) : int;

    /**
     * @param int $id
     * @return array|null
     */
    public function getThreadById($id);

    public function getThreadFollowerIds($id);

    public function getAllPostIdsInThread($id);
}
