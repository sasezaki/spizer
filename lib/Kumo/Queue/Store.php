<?php
//not implemented,yet.
class Kumo_Queue_Store
{
    private $queues = array();
    public function add();
    
    public function __sleep()
    {
        return array('queues');
    }
    public function __wakeup()
    {
    }
}
