<?php
//this very lazy saving-image
require_once 'Spizer/Handler/Abstract.php';
class Kumo_Handler_SaveImage extends Spizer_Handler_Abstract
{
    protected $config = array(
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
        $content_type = $document->getHeader('content-type');
        if(!preg_match('#image/.*#i', $content_type)) return;

        $filepath = $this->config['save_dir'].DIRECTORY_SEPARATOR.rawurlencode($document->getUrl());
        file_put_contents($filepath, $document->getBody(), FILE_BINARY);
        $this->addHaveFiles($document->getUrl());
    }

    public function addHaveFiles($url)
    {
        file_put_contents($this->config['have_files'], $url."\n", FILE_APPEND);
    }
}
