<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\TransformerAbstract;
use Railroad\Railcontent\Entities\Content;

class ContentTransformer extends TransformerAbstract
{
    /**
     * @param Content $content
     * @return array
     */
    public function transform(Content $content)
    {
        return [
            'id' => $content->getId(),
            'title' => $content->getTitle(),
            'url' => $content->fetch('url', ''),
            'display_name' => $content->getTitle(),
        ];
    }
}
