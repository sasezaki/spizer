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
        if (isset($config['filters'])) {
            if (($match = $config['filters']['matchpattern']) &&
                ($replace = $config['filters']['replacement'])) {

                require_once 'Zend/Filter/PregReplace.php';
                $pregreplace = new Zend_Filter_PregReplace();
                $pregreplace->setMatchPattern($match);
                $pregreplace->setReplacement($replace);

                $process->setFilters(array($pregreplace));
            }
        }

        $this->scraper = new Diggin_Scraper();
        $this->scraper->process($process);
    }

    public function handle(Spizer_Document $doc)
    {
        if (!$doc instanceof Spizer_Document_Html) return;

        $headers = $doc->getAllHeaders();

        //response is already decoded.
        unset($headers['transfer-encoding']);
        unset($headers['content-encoding']);

        try {
            $results = $this->scraper->scrape(new Zend_Http_Response($doc->getStatus(),
                                                      $headers,
                                                      $doc->getBody())
                               , $doc->getUrl());
        } catch (Diggin_Scraper_Exception $dse) {
            if (isset($this->_config['throwIfNotfound'])) {
                throw $dse;
            }
        }

        if (!isset($results)) return;
        if ($this->_config['debug']) {
            echo 'count scrape results '. count($results['kumo']) . PHP_EOL;
        }

        foreach ($results['kumo'] as $src) {
            $this->send($src);
        }

    }
}

