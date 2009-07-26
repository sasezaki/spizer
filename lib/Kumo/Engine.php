<?php

//borrowed from Spizer_Engine

require_once 'Spizer/Engine.php';

class Kumo_Engine extends Spizer_Engine
{
    protected $_zfQueueCustom;
   
    /**
     * Create a new Spizer Engine object
     *
     * @param array $config Configuration array
     */
    public function __construct(array $config = array(), $zfQueueCustomConfig = null)
    {
        parent::__construct($config);

        $zfQueueCustomConfig = array_merge(array(
                                                'adapter' => 'Array',
                                                'options' =>  array('name' => 'spizer')
                                           ),
                                           $zfQueueCustomConfig);

        $this->_zfQueueCustom = new Kumo_ZFQueueCustom($zfQueueCustomconfig['adapter'], ['options']);
    }

    /**
     * override Spizer_Engine
     *
     * Run the crawler until we hit the last URL
     *
     * @param string $url URL to start crawling from
     */
    public function run($url)
	{
		if (! Zend_Uri_Http::check($url)) {
			require_once 'Spizer/Exception.php';
			throw new Spizer_Exception("'$url' is not a valid HTTP URI");
		}
		$this->baseUri = Zend_Uri::factory($url);
		$this->queue->append($url);
		
		// Set the default logger if not already set
		if (! $this->logger) {
		    require_once 'Spizer/Logger/Xml.php';
		    $this->logger = new Spizer_Logger_Xml();
		}
		
		// Go!
		while (($request = $this->queue->next())) {
		    $this->logger->startPage();
            $this->logger->logRequest($request);
    
            // Prepare HTTP client for next request
            $this->httpClient->resetParameters();
            $this->httpClient->setUri($request->getUri());
            $this->httpClient->setMethod($request->getMethod());
            $this->httpClient->setHeaders($request->getAllHeaders());
            $this->httpClient->setRawData($request->getBody());
            
            // Send request, catching any HTTP related issues that might happen
            try {
                $response = new Spizer_Response($this->httpClient->request());
            } catch (Zend_Exception $e) {
       	        fwrite(STDERR, "Error executing request: {$e->getMessage()}.\n");
       	        fwrite(STDERR, "Request information:\n");
       	        fwrite(STDERR, "  {$request->getMethod()} {$request->getUri()}\n");
       	        fwrite(STDERR, "  Referred  by: {$request->getReferrer()}\n");
       	    }
       	    
    	    $this->logger->logResponse($response);
	    
	        // Call handlers
	        $this->callHandlers($request, $response);
	    
    	    // End page
	        $this->logger->endPage();
	        ++$this->requestCounter;
	    
    	    // Wait if a delay was set
	    	if (isset($this->config['delay'])) sleep($this->config['delay']);
		}
	}
}

