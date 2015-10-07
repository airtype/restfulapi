<?php

namespace RestfulApi\Transformers;

use Craft\EntryModel;

class EntryTransformer extends BaseTransformer
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->availableIncludes = array_merge($this->availableIncludes, [
            'author',
            'section',
            'type',
        ]);
    }

    /**
     * Transform
     *
     * @param EntryModel $element Entry
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

    /**
     * Include Section
     *
     * @param EntryModel $element Entry
     *
     * @return League\Fractal\Resource\Item Section
     */
    public function includeSection(EntryModel $element)
    {
        $section = $element->getSection();

        if ($section) {
            return $this->item($section, new SectionTransformer);
        }
    }


    /**
     * Include Author
     *
     * @param EntryModel $element Entry
     *
     * @return League\Fractal\Resource\Item Author
     */
    public function includeAuthor(EntryModel $element)
    {
        $author = $element->getAuthor();

        if ($author) {
            return $this->item($author, new UserTransformer);
        }
    }

    /**
     * Include Type
     *
     * @param EntryModel $element Entry
     *
     * @return League\Fractal\Resource\Item Type
     */
    public function includeType(EntryModel $element)
    {
        $type = $element->getType();

        if ($type) {
            return $this->item($type, new EntryTypeTransformer);
        }
    }
}
