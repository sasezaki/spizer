<?php

require_once 'Spizer/Handler/Abstract.php';
require_once 'Kumo/Request/MessageQueue.php';
require_once 'Kumo/Request/MessageQueue/Message.php';

abstract class Kumo_Handler_RequestMessageQueueSenderAbstract extends Spizer_Handler_Abstract
{
    /**
     * @var Kumo_Request_MessageQueue
     */
    private $_queue = null;

    protected $config = array(
        'status'       => null,
        'content-type' => null,
        'adapter' => 'Array',
        'options' => array(
                    'name' => 'kumo', //base name for Zend_Queue
                    ),
        'timeout' => null,
        'debug' => false
    );

    public function getMessageQueue()
    {
        if ($this->_queue == null) {
            $this->_queue = new Kumo_Request_MessageQueue($this->config['adapter'], $this->config['options']);
        } 

        return $this->_queue;
    }

    protected function send($request)
    {
        $this->getMessageQueue()->send(new Kumo_Request_MessageQueue_Message($request));
    }

    public function __destruct()
    {
        if ($this->config['debug']) {
            foreach ($this->getMessageQueue()->receive(10) as $c => $message) {
                echo $message;

                if ($c = 10) break;
            }
        }
    }
}
