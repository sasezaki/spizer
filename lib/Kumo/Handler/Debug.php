<?php
require_once 'Spizer/Handler/Abstract.php';
class Kumo_Handler_Debug extends Spizer_Handler_Abstract
{
    protected $config = array(
        'do' => false,
        'dumper' => 'echo',
        'url' => false,
        'contenttype' => false,
        'handlers' => true,
        'documenttype' => false
        );

    public function handle(Spizer_Document $doc)
    {
        echo (string)$doc->getUrl(), PHP_EOL;
    }
    
    protected function dump()
    {
        if (!$this->_config['do']) return;

        $args = func_get_args();

        Zend_Debug::dump($args);

        /*
        if ($this->_config['dumper'] == 'echo') {
            foreach ($args as $arg) {
                echo $arg;
            }
        } elseif ($this->_config['dumper'] == 'Zend_Debug') {
            Zend_Debug::dump($args);
        }*/
    } 

}
