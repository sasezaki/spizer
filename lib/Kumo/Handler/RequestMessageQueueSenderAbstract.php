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
        'queueAdapter' => null,
        'queueOptions' => array(),
        'timeout' => null,
        'debug' => false
    );

    public function __construct(array $config = array())
    {
        if (!is_string($config['queueAdapter'])) {
            require_once 'Kumo/Handler/Exception.php';
            throw new Kumo_Handler_Exception('queueAdapter must String');
        }
        if (!is_array($config['queueOptions'])) {
            require_once 'Kumo/Handler/Exception.php';
            throw new Kumo_Handler_Exception('queueOptions must array');
        }

        parent::__construct($config);
    }

    public function getMessageQueue()
    {
        if ($this->_queue == null) {
            $this->_queue = new Kumo_Request_MessageQueue($this->config['queueAdapter'], $this->config['queueOptions']);
        }

        return $this->_queue;
    }

    protected function send($request)
    {
        $this->getMessageQueue()->send(new Kumo_Request_MessageQueue_Message($request));
    }

}
