<?php

namespace RestfulApi\Transformers;

use Craft\AssetFileModel;

class ImgTransformsTransformer extends BaseTransformer
{
    /**
     * Transform
     *
     * @param AssetFileModel $element Asset
     *
     * @return array Asset
     */
    public function transform(AssetFileModel $element)
    {
        $transforms = \Craft\craft()->assetTransforms->allTransforms;
        $toReturn = [];

        foreach($transforms as $transform){
            $handle = $transform->handle;
            $toReturn[$handle]['url'] = $element->getUrl($handle);
            $toReturn[$handle]['width'] = $element->getWidth($handle);
            $toReturn[$handle]['height'] = $element->getHeight($handle);
        }

        return $toReturn;
    }
}
