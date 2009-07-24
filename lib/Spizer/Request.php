<?php

/**
 * Spizer - the flexible PHP web spider
 * Copyright 2009 Shahar Evron
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License. 
 * 
 * @license    Licensed under the Apache License 2.0, see COPYING for details
 */

require_once 'Zend/Uri/Http.php';

/**
 * Object representing an HTTP request, providing easy access to properties 
 * such as target URI, method, headers and body (in POST or PUT requests).
 * 
 * @todo Implement additinal features to handle non-GET requests
 * 
 * @package    Spizer
 * @subpackage Core
 * @author     Shahar Evron, shahar.evron@gmail.com
 * @license    Licensed under the Apache License 2.0, see COPYING for details
 */
class Spizer_Request
{
    /**
     * Target URI
     *
     * @var Zend_Uri_Http
     */
    protected $uri     = null;
    
    /**
     * Request method (default is GET)
     *
     * @var string
     */
    protected $method  = 'GET';
    
    /**
     * Array of HTTP headers
     *
     * @var array
     */
    protected $headers = array();
    
    /**
     * Request body (for POST and PUT requests). Note that when sending a body,
     * you should also set the 'Content-type' header.
     *
     * @var string
     */
    protected $body    = '';
    
    /**
     * First page referring this request 
     * *Not http-referer*
     *
     * @var string
     */
    protected $referrer = null;
    
    /**
     * Create a new request object
     * 
     * @param Zend_Uri_Http|string $url    Target URL
     * @param string               $method HTTP request method - default is GET
     */
    public function __construct ($uri, $method = 'GET')
    {
        if (! $uri instanceof Zend_Uri_Http) {
            if (! Zend_Uri_Http::check($uri)) {
                require_once 'Spizer/Exception.php';
                throw new Spizer_Exception("'$uri' is not a valid HTTP URL");
            }
            
            $uri = Zend_Uri::factory($uri);
        }
        
        $this->uri = $uri;
        $this->method = $method;
    }
    
    /**
     * Set this request's referrer
     *
     * @param Zend_Uri_Http|string $referrer
     */
    public function setReferrer($referrer)
    {
        $this->referrer = (string) $referrer;
    }
    
    /**
     * Get the referrer to this request
     *
     * @return string|null Will return null if no referrer is known
     */
    public function getReferrer()
    {
        return $this->referrer;
    }
    
    /**
     * Get the request URI 
     *
     * @return Zend_Uri_Http
     */
    public function getUri()
    {
        return $this->uri; 
    }
    
    /**
     * Get the request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Get an associative array of all headers
     *
     * @return array
     */
    public function getAllHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Get a single header according to it's name
     *
     * @param  string $header
     * @return string
     */
    public function getHeader($header)
    {
        $header = strtolower($header);
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        } else {
            return null;
        } 
    }

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

    /**
     * Get the request body for POST and PUT requests
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
