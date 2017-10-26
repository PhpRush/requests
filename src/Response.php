<?php
namespace PhpRush\Requests;
// Content-Language:zh-CN
// Content-Length:2831
// Content-Type:text/html;charset=UTF-8
// Date:Thu, 26 Oct 2017 03:46:37 GMT
// Server:Apache-Coyote/1.1
class Response
{

    private $code = 0;

    private $body = '';

    private $headers = null;

    private $contentType = null;
    
    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $code;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }
    
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function getContentType()
    {
        return $this->contentType;
    }
}