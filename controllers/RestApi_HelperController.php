<?php

namespace Craft;

use RestApi\Http\Router;
use RestApi\Http\Dispatcher;
use RestApi\Http\Request;
use RestApi\Http\Response;
use RestApi\Exceptions\RestApiException;
use CDbException;
use League\Fractal\Serializer\ArraySerializer;
use RestApi\Transformers\ArrayTransformer;

class RestApi_HelperController extends BaseController
{
    /**
     * Allow Anonymous
     *
     * @var boolean
     */
    protected $allowAnonymous = true;

    /**
     * Router
     *
     * @var restApi\Http\Router
     */
    protected $router;

    /**
     * Dispatcher
     *
     * @var restApi\Http\Dispatcher
     */
    protected $dispatcher;

    /**
     * Request
     *
     * @var restApi\Http\Request
     */
    protected $request;

    /**
     * Response
     *
     * @var restApi\Http\Response
     */
    protected $response;

    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            $this->router     = \Craft\craft()->urlManager;
            $this->dispatcher = new Dispatcher($this->router);
            $this->request    = new Request();
            $this->response   = new Response($this->request);
        } catch (RestApiException $exception) {
            $response = new Response();

            $response
                ->setStatus($exception->getStatusCode(), $exception->getStatusPhrase())
                ->setError($exception)
                ->send();
        } catch (\Craft\Exception $craftException) {
            $exception = new RestApiException();
            $exception->setMessage($craftException->getMessage());

            $response = new Response();

            return $response
                ->setError($exception)
                ->send();
        }
    }

    /**
     * Get Request
     *
     * @return Request Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Resource Router
     *
     * @param array $variables Variables
     *
     * @return void
     */
    public function actionResourceRouter(array $variables = [])
    {
        try {
            $this->dispatcher->handle($this, $variables);

            return $this->response->send();
        } catch (RestApiException $exception) {
            $exception->setInput($this->request->getParsedBody());

            $response = new Response();

            return $response
                ->setStatus($exception->getStatusCode(), $exception->getStatusPhrase())
                ->setError($exception)
                ->send();
        } catch (CDbException $CDbException) {
            $exception = new RestApiException();
            $exception->setMessage($CDbException->getMessage());

            $response = new Response();

            return $response
                ->setError($exception)
                ->send();
        } catch (\Craft\Exception $craftException) {
            $exception = new RestApiException();
            $exception->setMessage($craftException->getMessage());

            $response = new Response();

            return $response
                ->setError($exception)
                ->send();
        }
    }

    /**
     * Show Permissions
     *
     * @param array $permissions Permissions
     *
     * @return Response Response
     */
    public function actionShowPermissions($permissions)
    {
        return $this->response
            ->setSerializer(new ArraySerializer)
            ->setTransformer(new ArrayTransformer)
            ->setItem($permissions);
    }
}
