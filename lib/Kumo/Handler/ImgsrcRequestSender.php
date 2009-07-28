<?php

require_once 'Spizer/Handler/Abstract.php';
require_once 'Kumo/Handler/CustomZFQueueSenderAbstract.php';

/**
 * Send Spizer_Reqeust to Zend_Queue
 */ 
class Kumo_Handler_ImgsrcRequestSender extends Kumo_Handler_CustomZFQueueSenderAbstract
{
    //$targets 

    protected $config = array(
        'adapter' => 'Array',
        'options' => array(
                    'name' => 'kumo_img', // name for Zend_Queue
                    ),
        'timeout' => null
        'xpath' => '//img'
    );

    public function handle(Spizer_Document $doc)
    {
        $imgs = $doc->getImages();
        foreach ($imgs as $img) {
            $req = new Spizer_Request($req);
            $this->send($req);
        }

    }

}
