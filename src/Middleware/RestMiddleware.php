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
        // if ($method === 'GET' && !$request->getAttribute('elementType') && !$element_id && !$request->getAttribute('action')) {
        //     $permissions = \Craft\\Craft\craft()->restfulApi_config->getCompiledElementsPermissions($is_authenticated);

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

        $action = $this->getAction($request->getMethod(), $request->getAttribute('elementId'));

        return $this->$action($request, $response);
    }

    /**
     * Get Action
     *
     * @param string  $method       Method
     * @param string  $element_type Element Type
     * @param boolean $element_id   Element Id
     *
     * @return [type]  [description]
     */
    private function getAction($method, $element_type, $element_id = null)
    {
        if ($method === 'GET' && !$element_id) {
            return 'actionIndex';
        }

        if ($method === 'GET' && $element_id) {
            return 'actionShow';
        }

        if ($method === 'POST' && !$element_id) {
            return 'actionStore';
        }

        if (in_array($method, ['PUT', 'PATCH']) && $element_id) {
            return 'actionUpdate';
        }

        if ($method === 'DELETE' && $element_id) {
            return 'actionDelete';
        }

        $exception = new Exception();
        $exception
            ->setStatus(405)
            ->setMessage(sprintf('`%s` method not allowed for resource `%s`.', $method, $request->getAttribute('elementType')));

        throw $exception;
    }

    /**
     * Action Show
     *
     * @param array $variables Variables
     *
     * @return Response Response
     */
    private function actionIndex(Request $request, Response $response, array $variables = [])
    {
        $elements = \Craft\craft()->restfulApi->getElements($request);

        return $response->setPaginatedCollection($elements);
    }

    /**
     * Action Show
     *
     * @param array $variables Variables
     *
     * @return Response Response
     */
    private function actionShow(Request $request, Response $response, array $variables = [])
    {
        $element = \Craft\craft()->restfulApi->getElement($request);

        return $response
            ->setItem($element);
    }

    /**
     * Action Store
     *
     * @param array $variables Variables
     *
     * @return Response Response
     */
    private function actionStore(Request $request, Response $response, array $variables = [])
    {
        $element = \Craft\craft()->restfulApi->saveElement($request);

        return $response
            ->setCreated()
            ->setItem($element);
    }

    /**
     * Action Update
     *
     * @param array $variables Variables
     *
     * @return Response Response
     */
    private function actionUpdate(Request $request, Response $response, array $variables = [])
    {
        $element = \Craft\craft()->restfulApi->saveElement($request);

        return $response
            ->setStatus(200, 'Element updated successfully.')
            ->setItem($element);
    }

    /**
     * Action Delete
     *
     * @param array $variables Variables
     *
     * @return Response Response
     */
    private function actionDelete(Request $request, Response $response, array $variables = [])
    {
        \Craft\craft()->restfulApi->deleteElement($request);

        return $response->setStatus(204, 'Element deleted successfully.');
    }
}
