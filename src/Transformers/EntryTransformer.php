<?php

namespace RestfulApi\Transformers;

use Craft\EntryModel;

class EntryTransformer extends BaseTransformer
{
    /**
     * Transform
     *
     * @param BaseElementModel $element Entry
     *
     * @return array Entry
     */
    public function transform(EntryModel $element)
    {
        return [
            'id'            => (int) $element->id,
            'enabled'       => (bool) $element->enabled,
            'archived'      => (bool) $element->archived,
            'locale'        => $element->locale,
            'localeEnabled' => (bool) $element->localeEnabled,
            'slug'          => $element->slug,
            'uri'           => $element->uri,
            'dateCreated'   => $element->dateCreated,
            'dateUpdated'   => $element->dateUpdated,
            'root'          => ($element->root) ? (int) $element->root : null,
            'lft'           => ($element->lft) ? (int) $element->lft : null,
            'rgt'           => ($element->rgt) ? (int) $element->rgt : null,
            'level'         => ($element->level) ? (int) $element->level : null,
            'sectionId'     => (int) $element->sectionId,
            'typeId'        => (int) $element->typeId,
            'authorId'      => (int) $element->authorId,
            'postDate'      => $element->postDate,
            'expiryDate'    => $element->expiryDate,
            'parentId'      => (int) $element->parentId,
            'revisionNotes' => $element->revisionNotes,
        ];
    }
}
