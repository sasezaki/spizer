<?php

require_once 'Spizer/Handler/Abstract.php';
require_once 'Kumo/Handler/CustomZFQueueSenderAbstract.php';

/**
 * Send Spizer_Reqeust to Zend_Queue
 */ 
class Kumo_Handler_RequestSender extends Kumo_Handler_CustomZFQueueSenderAbstract
{
    //$targets 

    protected $config = array(
        'adapter' => 'Array',
        'options' => array(
                    'name' => 'kumo_req', //base name for Zend_Queue
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

        var_dump($this->getCustomZFQueue()->receive(1));
    }

}
