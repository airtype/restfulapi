<?php

namespace RestfulApi\Transformers;

use Craft\MatrixBlockTypeModel;

class MatrixBlockTypeTransformer extends BaseTransformer
{
    /**
     * Transform
     *
     * @param MatrixBlockTypeModel $element Matrix Block Type
     *
     * @return array Matrix Block Type
     */
    public function transform(MatrixBlockTypeModel $element)
    {
        return [
            'id'            => (int) $element->id,
            'fieldId'       => (int) $element->fieldId,
            'fieldLayoutId' => (int) $element->fieldLayoutId,
            'name'          => $element->name,
            'handle'        => $element->handle,
            'sortOrder'     => (int) $element->sortOrder,
        ];
    }
}
