<?php

namespace RestApi\Transformers;

use League\Fractal\TransformerAbstract;
use Craft\BaseElementModel;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Resource\Collection;
use RestApi\Transformers\ArrayTransformer;

class BaseTransformer extends TransformerAbstract
{
    /**
     * Available Includes
     *
     * @var array
     */
    protected $availableIncludes = ['content'];

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

        foreach ($element->getFieldLayout()->getFields() as $fieldLayoutField) {
            $field = $fieldLayoutField->getField();

            $value = $element->getFieldValue($field->handle);

            if (get_class($field->getFieldType()) === 'Craft\\RichTextFieldType') {
                $value = $value->getRawContent();
            }

            if (is_object($value) && get_class($value) === 'Craft\\ElementCriteriaModel') {
                $class = get_class($value->getElementType());
                $element_type = \Craft\craft()->restApi_helper->getElementTypeByClass($class);

                $manager = new Manager();
                $manager->parseIncludes('content');
                $manager->setSerializer(new ArraySerializer);

                $transformer = \Craft\craft()->restApi_config->getTransformer($element_type);

                $value = $value->find();

                $body = new Collection($value, new $transformer);

                $value = $manager->createData($body)->toArray();

                $value = $value['data'];
            }

            $content[$field->handle] = $value;
        }

        return $this->item($content, new ContentTransformer);
    }
}
