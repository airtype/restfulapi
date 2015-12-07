<?php

namespace RestfulApi\Middleware;

use RestfulApi\Http\Request;
use Craft\BaseController;

abstract class MiddlewareAbstract
{
    /**
     * Request
     *
     * @var RestfulApi\Http\Request
     */
    protected $request;

    /**
     * Context
     *
     * @var Craft\BaseController
     */
    protected $context;

    /**
     * Variables
     *
     * @var array
     */
    protected $variables;

    /**
     * Constructor
     *
     * @param Request $request Request
     */
    public function __construct(Request $request, BaseController $context, array $variables)
    {
        $this->request   = $request;
        $this->context   = $context;
        $this->variables = $variables;
    }

    /**
     * Handle
     *
     * @return void
     */
    abstract public function handle();
}
