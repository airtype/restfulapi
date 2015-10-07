<?php

namespace RestfulApi\Transformers;

use Craft\SectionModel;

class SectionTransformer extends BaseTransformer
{
    /**
     * Transform
     *
     * @param MatrixBlockTypeModel $element Matrix Block Type
     *
     * @return array Matrix Block Type
     */
    public function transform(SectionModel $element)
    {
        return [
            'id'               => (int) $element->id,
            'structureId'      => (int) $element->structureId,
            'name'             => $element->name,
            'handle'           => $element->handle,
            'type'             => $element->type,
            'hasUrls'          => (bool) $element->hasUrls,
            'template'         => $element->template,
            'maxLevels'        => (int) $element->maxLevels,
            'enableVersioning' => (bool) $element->enableVersioning,
        ];
    }
}
