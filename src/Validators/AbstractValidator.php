<?php

namespace RestfulApi\Validators;

use Craft\BaseElementModel;

abstract class AbstractValidator
{
    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Rules
     *
     * @var array
     */
    public $rules = [];

    /**
     * Validate
     *
     * @return array Errors
     */
    public function validate(BaseElementModel $element)
    {
        $this->validateElement($element);
        $this->validateContentModel($element);
        $this->validateRules($element);

        return $this;
    }

    /**
     * Validate Element
     *
     * @param BaseElementModel $element Element
     *
     * @return void
     */
    private function validateElement(BaseElementModel $element)
    {
        if (!$element->validate()) {
            $this->addErrors($element->getErrors());
        }
    }

    /**
     * Validate Content
     *
     * @param BaseElementModel $element Element
     *
     * @return void
     */
    private function validateContentModel(BaseElementModel $element)
    {
        $fields_key = \Craft\craft()->config->get('contentModelFieldsLocation', 'restfulApi');

        if (!\Craft\craft()->content->validateContent($element)) {
            $this->addErrors([$fields_key => $element->getContent()->getErrors()]);
        }
    }

    /**
     * Validate Rules
     *
     * @param BaseElementModel $element Element
     *
     * @return void
     */
    private function validateRules(BaseElementModel $element)
    {
        foreach ($this->rules as $rule) {
            $name = $rule[1];
            $attributes = $rule[0];
            $params = array_slice($rule ,2);

            $validator = \CValidator::createValidator($name, $element, $attributes, $params);
            $validator->validate($element);
        }

        $this->addErrors($element->getErrors());
    }

    /**
     * Add Errors
     *
     * @param array $errors Errors
     */
    private function addErrors(array $errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * Get Errors
     *
     * @return array Errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Has Errors
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return empty($this->errors) ? false : true;
    }

}
