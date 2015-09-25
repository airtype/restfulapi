<?php

namespace Craft;

use RestApi\Http\Request;

class RestApiService extends BaseApplicationComponent
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
        return craft()->restApi_helper->getElements($request->getCriteria());
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
        return craft()->restApi_helper->getElement($request);
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
        $element = craft()->restApi_helper->getElement($request);

        $populated_element = craft()->restApi_helper->populateElement($element, $request);

        $validator = craft()->restApi_config->getValidator($populated_element->getElementType());

        craft()->restApi_helper->validateElement($populated_element, $validator);

        return craft()->restApi_helper->saveElement($populated_element, $request);
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
        craft()->restApi_helper->deleteElement($request);
    }
}
