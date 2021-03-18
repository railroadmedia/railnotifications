<?php

namespace Railroad\Railnotifications\Tests\Fixtures;

use League\Fractal\TransformerAbstract;

class ContentTransformer extends TransformerAbstract
{
    /**
     * @return array
     */
    public function transform($content)
    {
        $title = $content->fetch('fields.title','');
        return [
            'id' => $content['id'],
            'title' => $title,
            'url' => $content->fetch('url', ''),
            'mobile_app_url' => $content->fetch('mobile_app_url', ''),
            'display_name' =>$title,
            'thumbnail_url' => $content->fetch('data.thumbnail_url')
        ];
    }
}
