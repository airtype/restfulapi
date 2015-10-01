<?php

namespace RestfulApi\Http;

use RestfulApi\Http\Psr7\ServerRequest;
use League\Url\Url;
use Streamer\Stream as Streamer;
use RestfulApi\Http\Psr7\Stream;
use Craft\ElementCriteriaModel;

class Request extends ServerRequest
{
    /**
     * Criteria
     *
     * @var Craft\ElementCriteriaModel
     */
    protected $criteria;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Message
        $this->setDefaultProtocolVersion();
        $this->setDefaultHeaders();
        $this->setBody();

        // Request
        $this->setDefaultRequestTarget();
        $this->setDefaultMethod();
        $this->setDefaultUri();

        // Server Request
        $this->setDefaultServerParams();
        $this->setDefaultCookieParams();
        $this->setDefaultQueryParams();
        $this->setDefaultUploadedFiles();
        $this->setDefaultParsedBody();
        $this->setDefaultAttributes();

        // Other
        $this->setDefaultCriteria();
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
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = array_map('trim', explode(',', $value));
            }
        }

        $this->headers = $headers;
    }

    /**
     * Set Body
     *
     * @return void
     */
    protected function setBody()
    {
        $streamer = new Streamer(fopen('php://input', 'r'));
        $this->body = new Stream($streamer);
    }

    /**
     * Set Default Request Target
     *
     * @return void
     */
    protected function setDefaultRequestTarget()
    {
        $this->request_target = \Craft\craft()->request->getUrl();
    }

    /**
     * Set Default Method
     *
     * @return void
     */
    protected function setDefaultMethod()
    {
        $this->method = \Craft\craft()->request->getRequestType();
    }

    /**
     * Set Default Uri
     *
     * @return void
     */
    protected function setDefaultUri()
    {
        $this->uri = Url::createFromServer($_SERVER);
    }

    /**
     * Set Default Server Params
     *
     * @return void
     */
    protected function setDefaultServerParams()
    {
        $this->server_params = $_SERVER;
    }

    /**
     * Set Default Cookie Params
     *
     * @return void
     */
    protected function setDefaultCookieParams()
    {
        $this->cookie_params = $_COOKIE;
    }

    /**
     * Set Default Query Params
     *
     * @return void
     */
    protected function setDefaultQueryParams()
    {
        $this->query_params = \Craft\craft()->request->getQuery();
    }

    /**
     * Set Default Uploaded Files
     *
     * @return void
     */
    protected function setDefaultUploadedFiles()
    {
        $files = [];

        foreach ($_FILES as $file) {
            $files[] = new UploadedFile($file);
        }

        $this->uploaded_files = $files;
    }

    /**
     * Set Default Parsed Body
     *
     * @return void
     */
    protected function setDefaultParsedBody()
    {
        if (in_array($this->getHeaderLine('Content-Type'), ['application/x-www-form-urlencoded', 'multipart/form-data']) && $this->method === 'POST') {
            $this->parsed_body = $_POST;
        } else {
            mb_parse_str($this->getBody()->getContents(), $parsed_body);

            $this->parsed_body = $parsed_body;
        }
    }

    /**
     * Set Default Attributes
     *
     * @return void
     */
    protected function setDefaultAttributes()
    {
        $attributes = \Craft\craft()->urlManager->getRouteParams()['variables'];
        unset($attributes['matches']);

        $this->attributes = $attributes;
    }

    /**
     * Set Default Criteria
     *
     * @return ElementCriteriaModel Criteria
     */
    protected function setDefaultCriteria()
    {
        $element_type         = $this->getAttribute('elementType');
        $pagination_parameter = \Craft\craft()->config->get('paginationParameter', 'restfulApi');

        if ($element_type) {
            $params = $this->getQueryParams();

            $criteria = \Craft\craft()->elements->getCriteria($element_type, $params);

            if (isset($criteria->$pagination_parameter)) {
                $criteria->offset = ($criteria->$pagination_parameter - 1) * $criteria->limit;
                unset($criteria->$pagination_parameter);
            }

            $this->criteria = $criteria;
        }
    }

    /**
     * Set Criteria
     *
     * @param ElementCriteriaModel $criteria Criteria
     */
    public function setCriteria(ElementCriteriaModel $criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Get Criteria
     *
     * @return ElementCriteriaModel Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Get Pararm
     *
     * @param array  $params Params
     * @param string $param  Param
     *
     * @return mixed Param
     */
    private function getParam($params, $param)
    {
        return isset($params[$param]) ? $params[$param] : null;
    }

    /**
     * Get Server Param
     *
     * @param string $param String
     *
     * @return mixed Param
     */
    public function getServerParam($param)
    {
        return $this->getParam($this->server_params, $param);
    }

    /**
     * Get Cookie Param
     *
     * @param string $param String
     *
     * @return mixed Param
     */
    public function getCookieParam($param)
    {
        return $this->getParam($this->cookie_params, $param);
    }

    /**
     * Get Query Param
     *
     * @param string $param String
     *
     * @return mixed Param
     */
    public function getQueryParam($param)
    {
        return $this->getParam($this->query_params, $param);
    }

}
