<?php

namespace Craft;

use RestApi\Exceptions\RestApiException;

class RestApiPlugin extends BasePlugin
{
    /**
     * Get Name
     *
     * @return string Name
     */
    public function getName()
    {
         return Craft::t('Rest Api');
    }

    /**
     * Get Version
     *
     * @return string Version
     */
    public function getVersion()
    {
        return '0.0.0';
    }

    /**
     * Get Developer
     *
     * @return string Developer
     */
    public function getDeveloper()
    {
        return 'Airtype';
    }

    /**
     * Get Developer Url
     *
     * @return string Developer Url
     */
    public function getDeveloperUrl()
    {
        return 'http://airtype.com';
    }

    /**
     * Register Site Routes
     *
     * @return array Site Routes
     */
    public function registerSiteRoutes()
    {
        $route_prefix = craft()->config->get('apiRoutePrefix', 'restApi');

        return [
            $route_prefix => ['action' => 'restApi/resourceRouter'],
            sprintf('%s/(?P<elementType>\w+)(/(?P<elementId>\d+)(/(?P<action>\w+))?)?', $route_prefix) => ['action' => 'restApi/resourceRouter'],
        ];
    }

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        $this->autoload_files();

        if (craft()->config->get('paginationParameter', 'restApi') === craft()->config->get('pageTrigger')) {
            $exception = new RestApiException;
            $exception->setMessage('The `paginationParameter` cannot be the same as `pageTrigger`.');

            throw $exception;
        }
    }

    /**
     * Autoload Files
     *
     * @return void
     */
    public function autoload_files()
    {
        $autoload = craft()->config->get('autoload', 'restApi');

        if ($autoload['transformers']) {
            Craft::import('plugins.*.transformers.*', true);
        } else {
            Craft::import('plugins.restApi.transformers.*', true);
        }

        if ($autoload['validators']) {
            Craft::import('plugins.*.validators.*', true);
        } else {
            Craft::import('plugins.restApi.validators.*', true);
        }

        Craft::import('plugins.restApi.vendor.autoload', true);
    }
}
