<?php

require_once 'Spizer/Handler/Abstract.php';

abstract class Kumo_Handler_ZFQueueCustomSenderAbstract extends Spizer_Handler_Abstract
{
    protected $_zfQueueCustom = null;
    
    /**
     * @var Kumo_ZFQueueCustom
     */
    protected $_queue = null;

    const QUEUENAME_INCREMENT = true;

    protected $config = array(
        'base_name' => 'kumo', //base name for Zend_Queue
        'timeout' => null
    );

    public function __construct(array $config = array())
    {
        parent::_construct($config);

        $this->_queue = $this->getZFQueueCustom()->createQueue($this->createQueueName(), 
                                                               $this->config['timeout']);
    }


    // call from Spizer_Engine::__construct
    public function setZFQueueCustom(Kumo_ZFQueueCustom $queue)
    {
        $this->_zfQueueCustom = $queue;
    }

    public function getZFQueueCustom()
    {
        //if (!$this->_zfQueueCustom) {
        //    $this->_zfQueueCustom = new Kumo_ZFQueueCustom();
        //}

        return $this->_zfQueueCustom;
    }

    // user-define-convinience-method and call $this>_send
    //public function send()
    // $spiderqueue

    // base send-method
    protected function _send(Spizer_Request $request)
    {

        $this->_queue->send($request);
    }

    //@todo more strictly, flexible
    protected function createQueueName()
    {
        $queuebase = $this->config['base_name'];
        $queuename = (is_numeric(substr($queuebase, -1, 1))) ? $queuebase++ : $queuebase.'1';
        
        return $queuename;
    }
}
