<?php
require_once 'Zend/Debug.php';
require_once 'Spizer/Handler/Abstract.php';
class Kumo_Handler_Debug extends Spizer_Handler_Abstract
{

    public function handle(Spizer_Document $doc)
    {
        Zend_Debug::Dump((string)$doc->getUrl());
        //Zend_Debug::Dump($doc->getUrl());
    }
    
}
