<?php

namespace RestfulApi\Http;

use RestfulApi\Http\Request;
use Craft\UrlManager;
use Craft\UserModel;
use Craft\BaseController;
use RestfulApi\Exceptions\RestfulApiException;

class Dispatcher
{
    /**
     * Request
     *
     * @var RestfulApi\Http\Request
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // /**
    //  * Get Route Parameters
    //  *
    //  * @return array Route Parameters
    //  */
    // protected function getRouteParameters()
    // {
    //     $attributes = $this->router->getRouteParams();
    //     unset($attributes['variables']['matches']);

    //     return $attributes['variables'];
    // }

    /**
     * Handle
     *
     * @param Request $request Request
     *
     * @return void
     */
    public function handle(BaseController $context, array $variables)
    {
        $this->handleMiddleware($this->request, $context, $variables);
    }


    /**
     * Handle Middleware
     *
     * @param Request $request Request
     *
     * @return void
     */
    public function handleMiddleware(Request $request, BaseController $context, array $variables)
    {
        $middleswares = \Craft\craft()->config->get('middleware', 'restfulApi');

        foreach ($middleswares as $middleware) {
            $middleware = new $middleware($request, $context, $variables);

            $middleware->handle();
        }
    }
}
