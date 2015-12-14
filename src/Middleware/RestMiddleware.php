<?php

namespace RestfulApi\Middleware;

use RestfulApi\Http\Request;
use RestfulApi\Http\Response;
use RestfulApi\Exceptions\RestfulApiException;

class RestMiddleware
{
    /**
     * Handle
     *
     * @return void
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        // if ($request->getMethod() === 'GET' && !$request->getAttribute('elementType') && !$request->getAttribute('elementId') && !$request->getAttribute('action')) {
        //     $permissions = \Craft\craft()->restfulApi_config->getCompiledElementsPermissions($is_authenticated);

        //     return $this->context->actionShowPermissions($permissions);
        // }

        if ($request->getAttribute('action')) {
            $method = 'action'.ucwords($request->getAttribute('action'));

            if (!method_exists($this->context, $method)) {
                $exception = new Exception();
                $exception
                    ->setStatus(404)
                    ->setMessage(sprintf('`%s` method of `%s` was not found.', $method, get_class($this->context)));

                throw $exception;
            }

            return $this->context->$method($this->variables);
        }

        if ($request->getMethod() === 'GET' && !$request->getAttribute('elementId')) {
            return $this->context->actionIndex($this->variables);
        }

        if ($request->getMethod() === 'GET' && $request->getAttribute('elementId')) {
            return $this->context->actionShow($this->variables);
        }

        if ($request->getMethod() === 'POST' && !$request->getAttribute('elementId')) {
            return $this->context->actionStore($this->variables);
        }

        if (in_array($request->getMethod(), ['PUT', 'PATCH']) && $request->getAttribute('elementId')) {
            return $this->context->actionUpdate($this->variables);
        }

        if ($request->getMethod() === 'DELETE' && $request->getAttribute('elementId')) {
            return $this->context->actionDelete($this->variables);
        }

        $exception = new Exception();
        $exception
            ->setStatus(405)
            ->setMessage(sprintf('`%s` method not allowed for resource `%s`.', $request->getMethod(), $request->getAttribute('elementType')));

        throw $exception;
    }
}
