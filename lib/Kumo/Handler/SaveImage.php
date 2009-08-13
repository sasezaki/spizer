<?php
//this very lazy saving-image
require_once 'Spizer/Handler/Abstract.php';
class Kumo_Handler_SaveImage extends Spizer_Handler_Abstract
{
    protected $config = array(
        'save_path' => null
    );

    public function __construct($config = array())
    {
        if (!isset($config['save_path'])) {
            require_once 'Kumo/Handler/Exception.php';
            throw new Kumo_Handler_Exception('not setting save_path');
        }

        parent::__construct($config);
    }

    public function handle(Spizer_Document $document)
    {

        //check document is image
        $content_type = $document->getHeader('content-type');
        if(!preg_match('#image/.*#i', $content_type)) return;

        $filepath = $this->config['save_path'].DIRECTORY_SEPARATOR.rawurlencode($document->getUrl());
        file_put_contents($filepath, $document->getBody(), FILE_BINARY);
    }
}
