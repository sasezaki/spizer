<?php

//require_once 'Zend/Uri/Http.php';
require_once 'Spizer/Request.php';
/**
 * wrapeer for Spizer_Request
 */
class Kumo_Request extends Spizer_Request
{
    /**
     * Set the body for POSt and Put requests
     * this is used Spizer_Engine:->run() 
     * same.$httpclien->setRawData()
     *
     * @param string (@todo handle array)
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    public function setHeader($key, $value)
    {
        $this->_headers[$key] = (string) $value;
    }

    public function setAllHeaders($headers)
    {
        $this->_headers = $headers;
    }
}
