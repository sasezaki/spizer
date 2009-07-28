<?php
require_once 'Zend/Queue.php';
final class Kumo_Request_MessageQueue extends Zend_Queue
{

    public function __construct()
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'parent::__construct'), $args);

        $this->setMessageClass('Kumo_Request_MessageQueue_Message');
    }

    public function send($message)
    {
        parent::send((string) $message);
    }

}
