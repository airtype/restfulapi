<?php

namespace RestApi\Transformers;

use Craft\TagModel;

class TagTransformer extends BaseTransformer
{
    /**
     * Transform Model
     *
     * @param BaseElementModel $element Tag
     *
     * @return array Tag
     */
    public function transform(TagModel $element)
    {
        return [
            'id'            => (int) $element->id,
            'enabled'       => (int) $element->enabled,
            'archived'      => (int) $element->archived,
            'locale'        => $element->locale,
            'localeEnabled' => (int) $element->localeEnabled,
            'slug'          => $element->slug,
            'uri'           => $element->uri,
            'dateCreated'   => $element->dateCreated,
            'dateUpdated'   => $element->dateUpdated,
            'root'          => ($element->root) ? (int) $element->root : null,
            'lft'           => ($element->lft) ? (int) $element->lft : null,
            'rgt'           => ($element->rgt) ? (int) $element->rgt : null,
            'level'         => ($element->level) ? (int) $element->level : null,
            'groupId'       => (int) $element->groupId,
        ];
    }

}
