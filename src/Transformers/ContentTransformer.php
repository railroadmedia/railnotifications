<?php

namespace Railroad\Railnotifications\Transformers;

use League\Fractal\TransformerAbstract;
use Railroad\Railcontent\Entities\Content;

class ContentTransformer extends TransformerAbstract
{
    public function transform(Content $content)
    {
        return [
            'id' => $content->getId(),
            'title' => $content->getTitle(),
            'url' => $content->fetch('url', ''),
        ];
    }
}
