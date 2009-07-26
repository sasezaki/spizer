<?php

require_once 'Spizer/Handler/Abstract.php';
require_once 'Kumo/CustomZFQueue.php';
require_once 'Kumo/CustomZFQueue/Message.php';

abstract class Kumo_Handler_CustomZFQueueSenderAbstract extends Spizer_Handler_Abstract
{
    /**
     * @var Kumo_CustomZFQueue
     */
    private $_queue = null;

    protected $config = array(
        'adapter' => 'Array',
        'options' => array(
                    'name' => 'kumo', //base name for Zend_Queue
                    ),
        'timeout' => null
    );

    public function getCustomZFQueue()
    {
        if ($this->_queue == null) {
            $this->_queue = new Kumo_CustomZFQueue($this->config['adapter'], $this->config['options']);
        } 

        return $this->_queue;
    }

    protected function send($request)
    {
        $this->getCustomZFQueue()->send(new Kumo_CustomZFQueue_Message($request));
    }
}
