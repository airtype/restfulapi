<?php

namespace RestfulApi\Http;

use League\Fractal\Manager;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\ResourceAbstract;
use RestfulApi\Resource\CraftItem;
use RestfulApi\Resource\CraftCollection;
use RestfulApi\Pagination\CraftPaginateVariableAdapter;
use RestfulApi\Transformers\ErrorTransformer;
use Craft\BaseElementModel;
use Craft\ElementCriteriaModel;
use RestfulApi\Exceptions\RestfulApiException;
use Streamer\Stream as Streamer;
use RestfulApi\Http\Psr7\Stream;

class Response extends \RestfulApi\Http\Psr7\Response
{
    /**
     * Request
     *
     * @var restfulApi\Http\Request
     */
    protected $request;

    /**
     * Manager
     *
     * @var League\Fractal\Manager
     */
    protected $manager;

    /**
     * Serializer
     *
     * @var League\Fractal\Serializer\DataArraySerializer
     */
    protected $serializer;

    /**
     * Transformers
     *
     * @var array
     */
    protected $transformers;

    /**
     * Transformer
     *
     * @var League\Fractal\TransformerAbstract
     */
    protected $transformer;

    /**
     * Error Transformer
     *
     * @var League\Fractal\TransformerAbstract
     */
    protected $error_transformer;

    /**
     * Includes
     *
     * @var array
     */
    protected $includes;

    /**
     * Item
     *
     * @var array
     */
    protected $item;

    /**
     * Collection
     *
     * @var array
     */
    protected $collection;

    /**
     * Paginator
     *
     * @var restfulApi\Pagination\CraftPaginateVariableAdapter
     */
    protected $paginator;

    /**
     * Meta
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Temp
     *
     * @var array
     */
    protected $temp = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->manager = new Manager();

        // Message
        $this->setDefaultProtocolVersion();
        $this->setDefaultHeaders();
        $this->setDefaultBody();

        // Response
        $this->setDefaultStatusCode();
        $this->setDefaultReasonPhrase();
        $this->setDefaultIncludes();

        // Fractal
        $this->setDefaultSerializer();

        if ($this->request) {
            $this->setDefaultTransformer();
        }
    }

    /**
     * Set Default Protocol Version
     *
     * @return void
     */
    protected function setDefaultProtocolVersion()
    {
        $this->protocol_version = \Craft\craft()->request->getHttpVersion();
    }

    /**
     * Set Default Headers
     *
     * @return void
     */
    protected function setDefaultHeaders()
    {
        $this->headers = \Craft\craft()->config->get('defaultHeaders', 'restfulApi');
    }

    /**
     * Set Default Body
     *
     * @return void
     */
    protected function setDefaultBody()
    {
        $streamer = new Streamer(fopen('php://temp', 'w+'));
        $this->body = new Stream($streamer);
    }

    /**
     * Set Default Status Code
     *
     * @return void
     */
    protected function setDefaultStatusCode()
    {
        $this->status_code = 200;
    }

    /**
     * Set Default Reason Phrase
     *
     * @return void
     */
    protected function setDefaultReasonPhrase()
    {
        $this->reason_phrase = 'OK';
    }

    /**
     * Set Includes
     *
     * @param array $includes Includes
     */
    protected function setIncludes(array $includes)
    {
        $this->includes = $includes;

        return $this;
    }

    /**
     * Get Includes
     *
     * @return array Includes
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * Add Include
     *
     * @param string $key   Key
     * @param string $value Value
     */
    public function addInclude($key, $value)
    {
        $this->includes[$key] = $value;

        return $this;
    }

    /**
     * Set Default Includes
     *
     * @return void
     */
    protected function setDefaultIncludes()
    {
        if ($this->request) {
            $includes = $this->request->getQueryParam('include');

            if (strpos($includes, \Craft\craft()->config->get('contentModelFieldsLocation', 'restfulApi')) !== false) {
                $includes = str_replace(\Craft\craft()->config->get('contentModelFieldsLocation', 'restfulApi'), 'content', $includes);
            }

            $this->includes = $includes;
        }
    }

    /**
     * Set Serializer
     *
     * @param SerializerAbstract $serializer Serializer
     *
     * @return Response Response
     */
    public function setSerializer(SerializerAbstract $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Set Default Serializer
     *
     * @return void
     */
    private function setDefaultSerializer()
    {
        $serializer_key = \Craft\craft()->config->get('defaultSerializer', 'restfulApi');
        $serializer = \Craft\craft()->config->get('serializers', 'restfulApi')[$serializer_key];

        $this->serializer = new $serializer;
    }

    /**
     * Get Serializer
     *
     * @return SerializerAbstract Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Set Transformer
     *
     * @param string              $element_type Element Type
     * @param TransformerAbstract $transformer  Transformer
     *
     * @return Response Response
     */
    public function addTransformer($element_type, TransformerAbstract $transformer)
    {
        \Craft\craft()->restfulApi_config->addTransformer($element_type, $transformer);

        $this->setDefaultTransformer();

        return $this;
    }

    /**
     * Set Default Transformer
     *
     * @return TransformerAbstract Transformer
     */
    private function setDefaultTransformer()
    {
        $element_type = $this->request->getAttribute('elementType');

        if ($element_type) {
            $this->transformer = \Craft\craft()->restfulApi_config->getTransformer($element_type);
        }
    }

    /**
     * Get Transformer
     *
     * @param string $element_type Element Type
     *
     * @return TransformerAbstract Transformer
     */
    public function getTransformer($element_type = null)
    {
        if ($element_type) {
            return \Craft\craft()->restfulApi_config->getTransformer($element_type);
        } else {
            return $this->transformer;
        }
    }

    /**
     * Set Transformer
     *
     * @param TransformerAbstract $transformer Transformer
     *
     * @return Response Response
     */
    public function setTransformer(TransformerAbstract $transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Set Headers
     *
     * @param array $headers Headers
     *
     * @return Response Response
     */
    public function setHeaders(array $headers)
    {
        HeaderHelper::setNoCache();
        HeaderHelper::setContentTypeByExtension('json');

        return $this;
    }

    /**
     * Get Headers
     *
     * @return array Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Add Header
     *
     * @param string $key   Key
     * @param string $value Value
     *
     * @return Response Response
     */
    public function addHeader($key, $value)
    {
        if ($key) {
            $this->headers[$key] = $value;
        } else {
            $this->headers[] = $value;
        }

        return $this;
    }

    /**
     * Set Status
     *
     * @param int $http_status_code Http Status Code
     *
     * @return Response Response
     */
    public function setStatus($status, $message = '')
    {
        $response = $this->withStatus($status, $message);

        $this->status_code = $response->status_code;
        $this->reason_phrase = $response->reason_phrase;

        return $this;
    }

    /**
     * Set Item
     *
     * @param mixed $item Item
     *
     * @return Response Response
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Set Collection
     *
     * @param array $items Items
     *
     * @return Response Response
     */
    public function setCollection($items)
    {
        $this->collection = $items;

        return $this;
    }

    /**
     * Set Paginated Collection
     *
     * @param array $items Items
     *
     * @return Response Response
     */
    public function setPaginatedCollection(array $items)
    {
        $this->setCollection($items);

        $this->paginator = new CraftPaginateVariableAdapter($this->request->getCriteria());

        return $this;
    }

    /**
     * Set Created
     *
     * @param BaseElementModel|null $model Model
     *
     * @return Response Response
     */
    public function setCreated($location = null)
    {
        $this->setStatus(201);

        if ($location) {
            $response = $response->addWithHeader('Location', $location);

            $this->headers = $response->headers;
        }

        return $this;
    }

    /**
     * Set Error
     *
     * @param RestfulApiException $exception Exception
     *
     * @return Response Response
     */
    public function setError(RestfulApiException $exception)
    {
        $body = [
            'error' => [
                'message' => $exception->getMessage(),
            ],
        ];

        if ($exception->hasErrors()) {
            $body['error']['errors'] = $exception->getErrors();
        }

        if (\Craft\craft()->config->get('devMode', 'restfulApi')) {
            if ($exception->hasInput()) {
                $body['error']['input'] = $exception->getInput();
            }

            $body['error']['debug'] = $exception->getTrace();
        }

        $this->transformer = \Craft\craft()->config->get('exceptionTransformer', 'restfulApi');

        $this->item = $body;

        return $this;
    }

    /**
     * Set body
     *
     * @param mixed $body Body
     *
     * @return Response Response
     */
    public function setBody($body)
    {

        $streamer = new Streamer(fopen('php://input', 'r'));
        $this->body = new Stream($streamer);

        return $this;
    }

    /**
     * Set Meta
     *
     * @param array $meta Meta
     *
     * @return Response Response
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get Meta
     *
     * @return array Meta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Add Meta
     *
     * @param string $key   Key
     * @param mixed  $value Value
     *
     * @return Response Response
     */
    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;

        return $this;
    }

    /**
     * Send
     *
     * @return void
     */
    public function send()
    {
        $this->applyFractal();

        $this->applyMeta();

        $this->applyStatus();

        $this->applyHeaders();

        $this->applyBody();

        ob_start();

        echo $this->getBody()->getContents();

        \Craft\craft()->end();
    }

    /**
     * Apply Status
     *
     * @return void
     */
    private function applyStatus()
    {
        $header = [
            null,
            sprintf('HTTP/%s %d %s', $this->getProtocolVersion(), $this->getStatusCode(), $this->getReasonPhrase())
        ];

        \Craft\HeaderHelper::setHeader($header);
    }

    /**
     * Apply Headers
     *
     * @return void
     */
    private function applyHeaders()
    {
        $headers = [];

        foreach ($this->headers as $header => $values) {
            $headers[$header] = implode(', ', $values);
        }

        \Craft\HeaderHelper::setHeader($headers);
    }

    /**
     * Apply Body
     *
     * @return void
     */
    private function applyBody()
    {
        if ($this->temp) {
            $body = \Craft\JsonHelper::encode($this->temp);

            $this->body->write($body);

            $this->body->rewind();
        }
    }

    /**
     * Apply Fractal
     *
     * @return void
     */
    private function applyFractal()
    {
        if (isset($this->includes)) {
            $this->manager->parseIncludes($this->includes);
        }

        if (is_string($this->transformer)) {
            $this->transformer = new $this->transformer;
        }

        if ($this->item) {
            $body = new CraftItem($this->item, $this->transformer);
        }

        if (isset($this->collection)) {
            $body = new CraftCollection($this->collection, $this->transformer);

            if ($this->paginator) {
                $body->setPaginator($this->paginator);
            }
        }

        if (isset($body)) {
            $this->manager->setSerializer($this->serializer);

            $this->temp = $this->manager->createData($body)->toArray();
        }
    }

    /**
     * Apply Meta
     *
     * @return void
     */
    private function applyMeta()
    {
        if (isset($this->temp['meta'])) {
            $this->temp['meta'] = array_merge($this->temp['meta'], $this->meta);
        }
    }
}
