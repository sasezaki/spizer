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
        foreach ($doc->getLinks() as $link) {
            if(@parse_url($link, PHP_URL_HOST)) {
                $req = new Spizer_Request($link);
                $req->setReferrer($doc->getUrl());
                $this->send($req);
            }
        }

        var_dump($this->getMessageQueue()->receive(1)->current()->getBody());
    }

    //public function setCallbackHandler(Spizer_Handler_Abstract $handler)
    //{}
}
