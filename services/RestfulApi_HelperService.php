<?php

namespace Craft;

use RestfulApi\Http\Request;
use RestfulApi\Validators\AbstractValidator;
use RestfulApi\Exceptions\RestfulApiException;

class RestfulApi_HelperService extends BaseApplicationComponent
{
    /**
     * Get Element Types
     *
     * @return array Element Types
     */
    public function getElementTypes()
    {
        $element_types = craft()->elements->getAllElementTypes();

        foreach ($element_types as $key => $element_type) {
            $element_types[get_class($element_type)] = $key;
            unset($element_types[$key]);
        }

        return $element_types;
    }

    /**
     * Get Element Types By Class
     *
     * @param string $class Class
     *
     * @return string Element Type
     */
    public function getElementTypeByClass($class)
    {
        $element_types = $this->getElementTypes();

        if (!isset($element_types[$class])) {
            $exception = new RestfulApiException();

            $exception
                ->setStatus(400)
                ->setMessage(sprintf('The class `%s` does not map to a valid element type.', $class));
        }

        return $element_types[$class];
    }

    /**
     * Get Elements
     *
     * @param ElementCriteriaModel $criteria Criteria
     *
     * @return array Elements
     */
    public function getElements(ElementCriteriaModel $criteria)
    {
        return $criteria->find();
    }

    /**
     * Get Element
     *
     * @param int $id
     *
     * @return object
     */
    public function getElement(Request $request)
    {
        $element_id = $request->getAttribute('elementId');

        if ($element_id) {
            $criteria = $request->getCriteria();

            $element = $criteria->first();
        } else {
            $element = sprintf('Craft\\%sModel', $request->getAttribute('elementType'));

            $element = new $element;
        }

        if (!$element) {
            $exception = new RestfulApiException();

            $exception
                ->setStatus(404)
                ->setMessage('Element not found.');

            throw $exception;
        }

        return $element;
    }

    /**
     * Populate Element
     *
     * @param BaseElementModel $element Element
     * @param Request          $request Request
     *
     * @return BaseElementModel Element
     */
    public function populateElement(BaseElementModel $element, Request $request)
    {
        $fields_key = craft()->config->get('contentModelFieldsLocation', 'restfulApi');

        $attributes = $request->getParsedBody();

        $element->setAttributes($attributes);

        if (isset($attributes[$fields_key])) {
            if (isset($attributes[$fields_key]['title'])) {
                $element->getContent()->title = $attributes[$fields_key]['title'];
            }

            $element->setContent($attributes[$fields_key]);
        }

        return $element;
    }

    /**
     * Validate Element
     *
     * @param BaseElementModel $element   Element
     * @param Validator        $validator Validator
     *
     * @return void
     */
    public function validateElement(BaseElementModel $element, AbstractValidator $validator)
    {
        $validator->validate($element);

        if ($validator->hasErrors()) {
            $exception = new RestfulApiException();

            $exception
                ->setStatus(422)
                ->setMessage('The request contains invalid arguments.')
                ->setErrors($validator->getErrors());

            throw $exception;
        }
    }

    /**
     * Save Element
     *
     * @param array $params Parameters
     *
     * @return BaseElementModel $model
     */
    public function saveElement(BaseElementModel $element, Request $request)
    {
        $element_type = craft()->elements->getElementType($element->getElementType());

        $result = $element_type->saveElement($element, null);

        if (!$result) {
            $exception = new RestfulApiException();

            $exception
                ->setStatus(400)
                ->setMessage('Element could not be stored.');

            throw $exception;
        }

        craft()->content->saveContent($element);

        return $element;
    }

    /**
     * Delete Element
     *
     * @param Request $request Request
     *
     * @return void
     */
    public function deleteElement(Request $request)
    {
        craft()->elements->deleteElementById($request->getAttribute('elementId'));
    }
}
