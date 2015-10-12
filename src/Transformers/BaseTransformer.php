<?php

namespace RestfulApi\Transformers;

use League\Fractal\TransformerAbstract;
use Craft\BaseElementModel;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use RestfulApi\Transformers\ArrayTransformer;

class BaseTransformer extends TransformerAbstract
{
    /**
     * Depth
     *
     * @var integer
     */
    protected $depth = 0;

    /**
     * Available Includes
     *
     * @var array
     */
    protected $availableIncludes = ['content'];

    public function __construct($depth = 0)
    {
        $this->depth = $depth;
    }

    /**
     * Include Content
     *
     * @param BaseElementModel $element Element
     *
     * @return League\Fractal\Resource\Item Content
     */
    public function includeContent(BaseElementModel $element)
    {
        $content = [];

        if ($this->depth > \Craft\craft()->config->get('contentRecursionLimit', 'restfulApi') - 1) {
            return;
        }

        foreach ($element->getFieldLayout()->getFields() as $fieldLayoutField) {
            $field = $fieldLayoutField->getField();

            $value = $element->getFieldValue($field->handle);

            if (get_class($field->getFieldType()) === 'Craft\\RichTextFieldType') {
                $value = $value->getRawContent();
            }

            if (is_object($value) && get_class($value) === 'Craft\\ElementCriteriaModel') {
                $class = get_class($value->getElementType());
                $element_type = \Craft\craft()->restfulApi_helper->getElementTypeByClass($class);

                $manager = new Manager();
                $manager->parseIncludes(array_merge(['content'], explode(',', \Craft\craft()->request->getParam('include'))));
                $manager->setSerializer(new ArraySerializer);

                $transformer = \Craft\craft()->restfulApi_config->getTransformer($element_type);

                $value = $value->find();

                $body = new Collection($value, new $transformer($this->depth + 1));

                $value = $manager->createData($body)->toArray();

                $value = (!empty($value['data'])) ? $value['data'] : null;
            }

            $content[$field->handle] = $value;
        }

        if ($content) {
            return $this->item($content, new ContentTransformer($this->depth), 'content');
        }
    }

    public function __call($method, $args)
    {
        return;
    }
}
