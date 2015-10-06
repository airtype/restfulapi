<?php

namespace Craft;

use RestfulApi\Exceptions\RestfulApiException;

class RestfulApiPlugin extends BasePlugin
{
    /**
     * Get Name
     *
     * @return string Name
     */
    public function getName()
    {
         return Craft::t('Restful Api');
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
        $route_prefix = craft()->config->get('apiRoutePrefix', 'restfulApi');

        return [
            $route_prefix => ['action' => 'restfulApi/resourceRouter'],
            sprintf('%s/(?P<elementType>\w+)(/(?P<elementId>\d+)(/(?P<action>\w+))?)?', $route_prefix) => ['action' => 'restfulApi/resourceRouter'],
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

        if (craft()->config->get('paginationParameter', 'restfulApi') === craft()->config->get('pageTrigger')) {
            $exception = new RestfulApiException;
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
        $autoload = craft()->config->get('autoload', 'restfulApi');

        if ($autoload['transformers']) {
            Craft::import('plugins.*.transformers.*', true);
        } else {
            Craft::import('plugins.restfulapi.transformers.*', true);
        }

        if ($autoload['validators']) {
            Craft::import('plugins.*.validators.*', true);
        } else {
            Craft::import('plugins.restfulapi.validators.*', true);
        }

        Craft::import('plugins.restfulapi.vendor.autoload', true);
    }
}
