<?php
require_once 'Spizer/Handler/Abstract.php';
class Kumo_Handler_SleepSec extends Spizer_Handler_Abstract
{

    public function __construct($config = array())
    {
        if (!isset($config['sleep'])) {
            require_once 'Kumo/Handler/Exception.php';
            throw new Kumo_Handler_Exception('not setting save_path');
        }

        parent::__construct($config);
    }

    public function handle(Spizer_Document $document)
    {
        if ($this->_config['debug']) {
            echo 'sleeping.. '. $this->_config['sleep']. 'sec'. PHP_EOL; 
        }
        sleep($this->_config['sleep']);
    }
}
