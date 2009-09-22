<?php

//borrowed from Spizer_Engine

require_once 'Spizer/Engine.php';

class Kumo_Engine extends Spizer_Engine
{

    /** Kumo_Request_MessageQueue */
    protected $requestQueue = null;
    /** Zend_ProgressBar */
    protected $progressBar = null;

    // @param boolean
    public $deleteMessage = false;

    public function setRequestQueue(Kumo_Request_MessageQueue $queue)
    {
        $this->requestQueue = $queue;
    }

    public function setProgressBar(Zend_ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    public function getProgressBar()
    {
        if (!$this->progressBar) {
            //require_once 'Zend/ProgressBar.php';
            //$this->progressBar
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

        $this->runMessages($messages);
        if($this->progressBar) $this->progressBar->finish();
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
            ++$this->requestCounter;
            if ($this->progressBar) $this->progressBar->update($this->requestCounter);
            if ($this->deleteMessage) $this->requestQueue->deleteMessage($message);

            // Wait if a delay was set
            if (isset($this->config['delay'])) sleep($this->config['delay']);
        }
    }

    /**
     * 
     * $messages = $queue->receive();
     * $engine->startlogger();
     * foreach() {
     *     $engine->runMessage();
     * }
     *
     */
    // @todo not implemented, yet.
    // for messages-loop handle
    /*
    public function runMessage($message)
    {
        //$request =;
        //
    }
    */
}

