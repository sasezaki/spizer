<?php
require_once 'Zend/Debug.php';
require_once 'Spizer/Handler/Abstract.php';
class Kumo_Handler_Debug extends Spizer_Handler_Abstract
{

    public function handle(Spizer_Document $doc)
    {
        if (isset($this->config['do']) && $this->config['do']) {
            echo (string)$doc->getUrl();
        }
    }
    
}
