<?php

//borrowed from Spizer_Engine

require_once 'Spizer/Engine.php';

class Kumo_Engine extends Spizer_Engine
{

    /**
     * some borrowd  Spizer_Engine->run
     *
     * Run the crawler 
     *
     * @param Kumo_Request_MessageQueue(Zend_Queue ext)
     */
    public function runQueue(Kumo_Request_MessageQueue $queue, $receive = 100, $timeout = 1)  
    {

        $messages = $queue->receive($receive, $timeout);
        
        // Set the default logger if not already set
        if (! $this->logger) {
            require_once 'Spizer/Logger/Xml.php';
            $this->logger = new Spizer_Logger_Xml();
        }

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
        
            // Wait if a delay was set
            if (isset($this->config['delay'])) sleep($this->config['delay']);
        }
    }
}

