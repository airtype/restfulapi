<?php

namespace Craft;

use RestApi\Http\Request;
use RestApi\Validators\AbstractValidator;
use RestApi\Exceptions\RestApiException;

class RestApi_HelperService extends BaseApplicationComponent
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
            $exception = new RestApiException();

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
        $pagination_parameter = craft()->config->get('paginationParameter', 'restApi');

        if (isset($criteria->$pagination_parameter)) {
            $criteria->offset = $criteria->$pagination_parameter;
            unset($criteria->$pagination_parameter);
        }

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
            $element = craft()->elements->getElementById($element_id);
        } else {
            $element = sprintf('Craft\\%sModel', $request->getAttribute('elementType'));

            $element = new $element;
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
        $fields_key = craft()->config->get('contentModelFieldsLocation', 'restApi');

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
            $exception = new RestApiException();

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
            $exception = new RestApiException();

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
