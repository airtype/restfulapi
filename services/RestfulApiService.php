<?php

namespace Craft;

use RestfulApi\Http\Request;

class RestfulApiService extends BaseApplicationComponent
{
    /**
     * Get Elements
     *
     * @param Request $request Request
     *
     * @return array Elements
     */
    public function getElements(Request $request)
    {
        return craft()->restfulApi_helper->getElements($request->getCriteria());
    }

    /**
     * Get Element
     *
     * @param Request $request Request
     *
     * @return BaseElementModel Element
     */
    public function getElement(Request $request)
    {
        return craft()->restfulApi_helper->getElement($request);
    }

    /**
     * Save Element
     *
     * @param Request $request Request
     *
     * @return BaseElementModel Element
     */
    public function saveElement(Request $request)
    {
        $element = craft()->restfulApi_helper->getElement($request);

        $populated_element = craft()->restfulApi_helper->populateElement($element, $request);

        $validator = craft()->restfulApi_config->getValidator($populated_element->getElementType());

        craft()->restfulApi_helper->validateElement($populated_element, $validator);

        return craft()->restfulApi_helper->saveElement($populated_element, $request);
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
        craft()->restfulApi_helper->deleteElement($request);
    }
}
