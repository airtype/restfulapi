<?php

namespace Craft;

use RestfulApi\Http\Router;
use RestfulApi\Http\Dispatcher;
use RestfulApi\Http\Request;
use RestfulApi\Http\Response;
use RestfulApi\Exceptions\RestfulApiException;
use CDbException;
use League\Fractal\Serializer\ArraySerializer;
use RestfulApi\Transformers\ArrayTransformer;

use Relay\Runner;

class RestfulApi_HelperController extends BaseController
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
     * @var restfulApi\Http\Router
     */
    protected $router;

    /**
     * Dispatcher
     *
     * @var restfulApi\Http\Dispatcher
     */
    protected $dispatcher;

    /**
     * Request
     *
     * @var restfulApi\Http\Request
     */
    protected $request;

    /**
     * Response
     *
     * @var restfulApi\Http\Response
     */
    protected $response;

    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            $this->router     = \Craft\craft()->urlManager;
            $this->request    = new Request();
            $this->dispatcher = new Dispatcher($this->request);

            $runner = new Runner(\Craft\craft()->config->get('middleware', 'restfulApi'), function ($class) {
                return new $class();
            });

            $this->response = $runner(new Request(), new Response($this->request));

        } catch (RestfulApiException $exception) {
            $response = new Response();

            $response
                ->setStatus($exception->getStatusCode(), $exception->getStatusPhrase())
                ->setError($exception)
                ->send();
        } catch (\Craft\Exception $craftException) {
            $exception = new RestfulApiException();
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
    public function actionDispatch(array $variables = [])
    {
        try {
            // $this->dispatcher->handle($this, $variables);

            return $this->response->send();
        } catch (RestfulApiException $exception) {
            $exception->setInput($this->request->getParsedBody());

            $response = new Response();

            return $response
                ->setStatus($exception->getStatusCode(), $exception->getStatusPhrase())
                ->setError($exception)
                ->send();
        } catch (CDbException $CDbException) {
            $exception = new RestfulApiException();
            $exception->setMessage($CDbException->getMessage());

            $response = new Response();

            return $response
                ->setError($exception)
                ->send();
        } catch (\Craft\Exception $craftException) {
            $exception = new RestfulApiException();
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
