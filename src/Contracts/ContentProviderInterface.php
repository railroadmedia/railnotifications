<?php

namespace Railroad\Railnotifications\Contracts;


interface ContentProviderInterface
{
    /**
     * @param int $id
     * @return array|null
     */
    public function getContentById($id) ;

    /**
     * @param int $id
     * @return array|null
     */
    public function getCommentById($id);

    public function getContentTransformer();
}
