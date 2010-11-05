<?php

//borrowed from Spizer_Engine

require_once 'Spizer/Engine.php';

class Kumo_Engine extends Spizer_Engine
{

    /** Kumo_Request_MessageQueue */
    protected $requestQueue = null;
    /** Zend_ProgressBar */
    protected $progressBar = null;
    protected $progressBarAdapter = null;
    protected $doProgressBar = false;

    // @param boolean
    public $deleteMessage = false;

    public function setRequestQueue(Kumo_Request_MessageQueue $queue)
    {
        $this->requestQueue = $queue;
    }

    public function setProgressBarAdapter(Zend_ProgressBar_Adapter $adapter)
    {
        $this->progressBarAdapter = $adapter;
    }

    public function doProgressBar($boolean)
    {
        $this->doProgressBar = (boolean) $boolean;
    }

    public function getProgressBar($max)
    {
        if (!$this->progressBar) {
            require_once 'Zend/ProgressBar.php';
            $this->progressBar = new Zend_ProgressBar($this->progressBarAdapter, 0, $max);
        }

        return $this->progressBar;
    }

    /**
     * some borrowd  Spizer_Engine->run
     *
     * Run the crawler 
     *
     */
    public function runQueue($receive = 100, $timeout = 1)  
    {
        
        // Set the default logger if not already set
        if (! $this->logger) {
            require_once 'Spizer/Logger/Xml.php';
            $this->logger = new Spizer_Logger_Xml();
        }

        $messages = $this->requestQueue->receive($receive, $timeout);

        $counts = count($messages);
        //echo 'Kumo is Requesting count: '.$counts, PHP_EOL;
        if ($this->doProgressBar) $this->getProgressBar($counts);
        $this->runMessages($messages);
        if ($this->doProgressBar) $this->progressBar->finish();
    }

    protected function runMessages($messages)
    {
        // Go!
        foreach($messages as $count => $message) {

            $request = $message->getBody();

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
            ++$this->_requestCounter;

            if ($this->doProgressBar) $this->progressBar->update($this->_requestCounter);
            //@todo
            //if ($this->deleteMessage) $this->requestQueue->deleteMessage($message);
            $this->requestQueue->deleteMessage($message);

            // Wait if a delay was set
            if (isset($this->_config['delay'])) sleep($this->_config['delay']);
        }
    }

}

