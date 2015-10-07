<?php

namespace RestfulApi\Transformers;

use Craft\EntryTypeModel;

class EntryTypeTransformer extends BaseTransformer
{
    /**
     * Transform
     *
     * @param BaseElementModel $element Entry
     *
     * @return array Entry
     */
    public function transform(EntryTypeModel $element)
    {
        return [
            'id'            => (int) $element->id,
            'sectiondId'    => (int) $element->sectionId,
            'fieldLayoutId' => (int) $element->fieldLayoutId,
            'name'          => $element->name,
            'handle'        => $element->handle,
            'hasTitleField' => (bool) $element->hasTitleField,
            'titleLabel'    => $element->titleLabel,
            'titleFormat'   => $element->titleFormat,
        ];
    }
}
