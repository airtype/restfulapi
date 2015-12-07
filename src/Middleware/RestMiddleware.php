<?php

namespace RestfulApi\Middleware;

use RestfulApi\Http\Request;
use RestfulApi\Exceptions\RestfulApiException;

class RestMiddleware extends MiddlewareAbstract
{
    /**
     * Handle
     *
     * @return void
     */
    public function handle()
    {
        // if ($this->request->getMethod() === 'GET' && !$this->request->getAttribute('elementType') && !$this->request->getAttribute('elementId') && !$this->request->getAttribute('action')) {
        //     $permissions = \Craft\craft()->restfulApi_config->getCompiledElementsPermissions($is_authenticated);

        //     return $this->context->actionShowPermissions($permissions);
        // }

        if ($this->request->getAttribute('action')) {
            $method = 'action'.ucwords($this->request->getAttribute('action'));

            if (!method_exists($this->context, $method)) {
                $exception = new Exception();
                $exception
                    ->setStatus(404)
                    ->setMessage(sprintf('`%s` method of `%s` was not found.', $method, get_class($this->context)));

                throw $exception;
            }

            return $this->context->$method($this->variables);
        }

        if ($this->request->getMethod() === 'GET' && !$this->request->getAttribute('elementId')) {
            return $this->context->actionIndex($this->variables);
        }

        if ($this->request->getMethod() === 'GET' && $this->request->getAttribute('elementId')) {
            return $this->context->actionShow($this->variables);
        }

        if ($this->request->getMethod() === 'POST' && !$this->request->getAttribute('elementId')) {
            return $this->context->actionStore($this->variables);
        }

        if (in_array($this->request->getMethod(), ['PUT', 'PATCH']) && $this->request->getAttribute('elementId')) {
            return $this->context->actionUpdate($this->variables);
        }

        if ($this->request->getMethod() === 'DELETE' && $this->request->getAttribute('elementId')) {
            return $this->context->actionDelete($this->variables);
        }

        $exception = new Exception();
        $exception
            ->setStatus(405)
            ->setMessage(sprintf('`%s` method not allowed for resource `%s`.', $this->request->getMethod(), $this->request->getAttribute('elementType')));

        throw $exception;
    }
}
