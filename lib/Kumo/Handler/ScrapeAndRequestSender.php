<?php
require_once 'Diggin/Scraper.php';
require_once 'Kumo/Handler/RequestMessageQueueSenderAbstract.php';
require_once 'Zend/Http/Response.php';

class Kumo_Handler_ScrapeAndRequestSender extends Kumo_Handler_RequestMessageQueueSenderAbstract
{

    protected $scraper = null;

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        $process = new Diggin_Scraper_Process();
        $process->setExpression($config['expression']);
        //$process->setName($config['name']);
        $process->setName('kumo');
        $process->setArrayFlag((boolean) true);
        //$process->setType($config['type']);
        $process->setType(isset($config['type']) ? $config['type']: 'TEXT');

        $this->scraper = new Diggin_Scraper();
        $this->scraper->process($process);
    }

    public function handle(Spizer_Document $doc)
    {

        if (!$doc instanceof Spizer_Document_Html) return;

        $headers = $doc->getAllHeaders();
        unset($headers['content-encoding']);

        $results = $this->scraper->scrape(new Zend_Http_Response($doc->getStatus(),
                                                      $headers,
                                                      $doc->getBody())
                               , $doc->getUrl());


        foreach ($results['kumo'] as $src) {
            $this->send($src);
        }

    }
}

