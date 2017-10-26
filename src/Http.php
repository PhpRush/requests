<?php
/**
 * Created by PhpStorm.
 * User: jiangjianyong
 * Date: 2017/10/25
 * Time: 18:28
 */
namespace PhpRush\Requests;

use PhpRush\Requests\Transport\cURL;

class Http
{

    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';
    
    private $url = '';

    private $method = '';

    private $timeout = 60;

    private $formData = null;

    private $headers = null;

    private $options = null;

    private $response = null;

    public function __construct()
    {
        $this->response = new Response();
    }

    public static function get($url, $headers = null, $options = null, $timeout = null)
    {
        $formData = null;
        return self::call($url, self::METHOD_GET, $formData, $headers, $options, $timeout);
    }

    public static function post($url, $formData = null, $headers = null, $options = null, $timeout = null)
    {
        return self::call($url, self::METHOD_POST, $formData, $headers, $options, $timeout);
    }

    protected static function call($url, $method, $formData = null, $headers = null, $options = null, $timeout = null)
    {
        $http = new self();
        $http->setUrl($url);
        $http->setMethod($method);
        
        if (! is_null($formData)) {
            $http->setFormData($formData);
        }
        
        if ($timeout) {
            $http->setTimeout($timeout);
        }
        
        if (! is_null($headers)) {
            $http->setHeaders($headers);
        }
        
        if (! is_null($options)) {
            $http->setOptions($options);
        }
        
        $http->send();
        
        return $http->getResponse();
    }

    public function send()
    {
        $curl = new cURL();
        $curl->request($this->url, $this->headers, $this->formData, $this->getRealOptions());
        
        $this->response->setBody($curl->getResponse());
        $this->response->setCode($curl->getInfo('http_code'));
        $this->response->setContentType($curl->getInfo('content_type'));
        $this->response->setHeaders($curl->getHeaders());
    }

    public function getRealOptions()
    {
        $options = empty($this->options) ? [] : $this->options;
        $options['method'] = $this->method;
        if ($this->timeout) {
            $options['timeout'] = $this->timeout;
        }
        
        return $options;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setFormData($formData)
    {
        $this->formData = $formData;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
