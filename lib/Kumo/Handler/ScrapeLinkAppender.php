<?php
require_once 'Diggin/Scraper.php';
require_once 'Spizer/Handler/Abstract.php';
require_once 'Zend/Http/Response.php';

class Kumo_Handler_ScrapeLinkAppender
    extends Spizer_Handler_Abstract
{

    protected $scraper = null;
    protected $_targets = array();
    protected $_callonce = false;
    protected static $_called = false;

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        $this->_callonce = isset($config['call-once']) ? true : false;

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
        if ($this->_callonce) {
            if (true === $this->_callonce) {
                $this->_callonce = 1;
            } elseif (1 == $this->_callonce) {
                return;
            }
        }
        //var_dump(__METHOD__);

        if (!$doc instanceof Spizer_Document_Html) return;

        $headers = $doc->getAllHeaders();

        //response is already decoded.
        unset($headers['transfer-encoding']);
        unset($headers['content-encoding']);

        $results = $this->scraper->scrape(new Zend_Http_Response($doc->getStatus(),
                                                      $headers,
                                                      $doc->getBody())
                               , $doc->getUrl());

        $this->_addQueue($results['kumo']);
    }

    protected function _addQueue($urls, $referer = null)
    {
        $urls = (array) $urls;
         
        foreach ($urls as $url) {
            if (! in_array($url, $this->_targets)) {
                $request = new Spizer_Request($url);
                //$request->setReferrer($referrer);
                $this->_engine->getQueue()->append($request);
            
                $this->_targets[] = $url;
            }
        }
    }

}

