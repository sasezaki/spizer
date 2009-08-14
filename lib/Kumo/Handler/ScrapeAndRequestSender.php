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
        $process->setName('kumo');
        $process->setArrayFlag(isset($config['arrayflag']) ? (boolean)$config['arrayflag']: true);
        $process->setType(isset($config['type']) ? $config['type']: 'TEXT');
        // use only first filter
        $process->setFilters(isset($config['filters']) ? array_shift($config['filters']) : false);

        $this->scraper = new Diggin_Scraper();
        $this->scraper->process($process);
    }

    public function handle(Spizer_Document $doc)
    {

        if (!$doc instanceof Spizer_Document_Html) return;

        $headers = $doc->getAllHeaders();

        //response is already decoded.
        //if () {
            unset($headers['transfer-encoding']);
            unset($headers['content-encoding']);
        //}

        $results = $this->scraper->scrape(new Zend_Http_Response($doc->getStatus(),
                                                      $headers,
                                                      $doc->getBody())
                               , $doc->getUrl());

        foreach ($results['kumo'] as $src) {
            $this->send($src);
        }

    }
}

