<?php

namespace Craft;

use RestApi\Exceptions\RestApiException;

class RestApi_ConfigService extends BaseApplicationComponent
{
    /**
     * Element Types
     *
     * @var array
     */
    protected $element_types = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->element_types = craft()->config->get('elementTypes', 'restApi');
    }

    /**
     * Get Element Type Config
     *
     * @param BaseElementModel $element Element
     *
     * @return array Config
     */
    public function getElementTypeConfig($element_type, $setting = null)
    {
        if (!$element_type) {
            return;
        }

        $config = isset($this->element_types[$element_type]) ? $this->element_types[$element_type] : null;

        if (!is_array($config)) {
            $exception = new RestApiException();

            $exception
                ->setStatus(415)
                ->setMessage(sprintf('Configuration for the `%s` element type is not defined.', $element_type));

            throw $exception;
        }

        if (isset($config['enabled']) && !$config['enabled']) {
            $exception = new RestApiException();

            $exception
                ->setStatus(415)
                ->setMessage(sprintf('The `%s` element type is not enabled.', $element_type));

            throw $exception;
        }

        if ($setting) {
            return isset($config[$setting]) ? $config[$setting] : null;
        }

        return $config;
    }

    /**
     * Get Validator
     *
     * @param sting $element_type Element Type
     *
     * @return RestApi\Validators\AbstractValidator Validator
     */
    public function getValidator($element_type)
    {
        $validator = $this->getElementTypeConfig($element_type, 'validator');

        if (!$validator) {
            $exception = new RestApiException();

            $exception
                ->setStatus(415)
                ->setMessage(sprintf('A validator for the `%s` element type is not defined.', $element_type));

            throw $exception;
        }

        return new $validator;
    }

    /**
     * Get Transformer
     *
     * @param sting $element_type Element Type
     *
     * @return League\Fractal\TransformerAbstract Transformer
     */
    public function getTransformer($element_type)
    {
        $transformer = $this->getElementTypeConfig($element_type, 'transformer');

        if (!$transformer) {
            $exception = new RestApiException();

            $exception
                ->setStatus(415)
                ->setMessage(sprintf('A transformer for the `%s` element type is not defined.', $element_type));

            throw $exception;
        }

        return $transformer;
    }

    /**
     * Add Transformer
     *
     * @param string                             $element_type Element Type
     * @param League\Fractal\TransformerAbstract $transformer  Transformer
     */
    public function addTransformer($element_type, $transformer)
    {
        $this->element_types[$element_type]['transformer'] = $transformer;
    }

    /**
     * Get Elements Permissions
     *
     * @return array Elements Permissions
     */
    public function getElementsPermissions()
    {
        $elements_permissions = [];

        $element_types = array_keys($this->element_types);

        foreach ($element_types as $element_type) {
            if ($element_type === '*') {
                continue;
            }

            $elements_permissions[$element_type] = $this->getElementPermissions($element_type);
        }

        return $elements_permissions;
    }

    /**
     * Get Element Permissions
     *
     * @param string $element_type Element Type
     *
     * @return array Element Permissions
     */
    public function getElementPermissions($element_type)
    {
        $element_permissions = $this->getElementTypeConfig($element_type, 'permissions');

        if (!$element_permissions) {
            $element_permissions = $this->getElementPermissions('*');
        }

        if (!$element_permissions) {
            $exception = new RestApiException();

            $exception
                ->setStatus(415)
                ->setMessage(sprintf('Permissions for the `%s` element type is not defined.', $element_type));

            throw $exception;
        }

        return $element_permissions;
    }

    /**
     * Get Compiled Elements Permissions
     *
     * @param bool $authenticated Authenticated
     *
     * @return array Permissions
     */
    public function getCompiledElementsPermissions($authenticated)
    {
        $elements_permissions = $this->getElementsPermissions();

        foreach ($elements_permissions as $element => $element_permissions) {
            $elements_permissions[$element] = ($authenticated) ? array_merge($element_permissions['public'], $element_permissions['authenticated']) : $element_permissions['public'];
        }

        return $elements_permissions;
    }
}
