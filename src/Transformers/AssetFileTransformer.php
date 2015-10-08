<?php

namespace RestfulApi\Transformers;

use Craft\AssetFileModel;

class AssetFileTransformer extends BaseTransformer
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
            'sourceId'      => (int) $element->folderId,
            'folderId'      => (int) $element->filename,
            'originalName'  => $element->originalName,
            'kind'          => $element->kind,
            'width'         => (int) $element->width,
            'height'        => (int) $element->height,
            'size'          => (int) $element->size,
            'dateModified'  => $element->dateModified,
        ];
    }

}
