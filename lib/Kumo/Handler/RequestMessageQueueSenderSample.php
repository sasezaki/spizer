<?php

require_once 'Spizer/Handler/Abstract.php';
require_once 'Kumo/Handler/RequestMessageQueueSenderAbstract.php';

/**
 * Send Spizer_Reqeust to Zend_Queue
 */ 
class Kumo_Handler_RequestMessageQueueSenderSample extends Kumo_Handler_RequestMessageQueueSenderAbstract
{
    //$targets 

    protected $config = array(
        'adapter' => 'Array',
        'options' => array(
                    'name' => 'kumo_req', // name for Zend_Queue
                    ),
        'timeout' => null
    );

    public function handle(Spizer_Document $doc)
    {
        //$doc
        //
        $req = 'http://test.org/'.time();
        $req = new Spizer_Request($req);
        $this->send($req);

        var_dump($this->getMessageQueue()->receive(1));
    }

    //public function setCallbackHandler(Spizer_Handler_Abstract $handler)
    //{}
}
