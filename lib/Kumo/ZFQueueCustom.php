<?php

require_once 'Zend/Queue.php';
class Kumo_ZFQueueCutsom extends Zend_Queue
{

    public function __construct()
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'parent::__construct'), $args);

        $this->setMessageClass('Kumo_ZFQueueCustom_Message');
    }

}
