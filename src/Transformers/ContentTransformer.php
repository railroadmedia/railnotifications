<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\TransformerAbstract;
use Railroad\Railcontent\Entities\ContentEntity;

class ContentTransformer extends TransformerAbstract
{
    /**
     * @param ContentEntity $content
     * @return array
     */
    public function transform(ContentEntity $content)
    {
        $title = $content->fetch('fields.title','');
        return [
            'id' => $content['id'],
            'title' => $title,
            'url' => $content->fetch('url', ''),
            'mobile_app_url' => $content->fetch('new_mobile_app_url', ''),
            'musora_api_mobile_app_url' => $content->fetch('new_musora_api_mobile_app_url', ''),
            'display_name' =>$title,
            'thumbnail_url' => $content->fetch('data.thumbnail_url')
        ];
    }
}
