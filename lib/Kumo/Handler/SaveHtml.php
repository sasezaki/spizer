<?php
//this very lazy saving-image
require_once 'Spizer/Handler/Abstract.php';
class Kumo_Handler_SaveHtml extends Spizer_Handler_Abstract
{
    protected $config = array(
        //'status' => null,
        //'content-type'=> null,
        'save_dir' => null,
        'have_files' => null
    );

    public function __construct($config = array())
    {
        if (!isset($config['save_dir'])) {
            require_once 'Kumo/Handler/Exception.php';
            throw new Kumo_Handler_Exception('not setting save_path');
        }

        parent::__construct($config);
    }

    public function handle(Spizer_Document $document)
    {
        //check document is image

        $filepath = $this->_config['save_dir'].DIRECTORY_SEPARATOR.rawurlencode($document->getUrl());
        file_put_contents($filepath, $document->getBody());
        $this->addHaveFiles($document->getUrl());
    }

    public function addHaveFiles($url)
    {
        file_put_contents($this->_config['have_files'], $url."\n", FILE_APPEND);
    }
}
