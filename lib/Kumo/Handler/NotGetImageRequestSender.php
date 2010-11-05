<?php
require_once 'Diggin/Scraper.php';
require_once 'Kumo/Handler/RequestMessageQueueSenderAbstract.php';
require_once 'Zend/Http/Response.php';

class Kumo_Handler_NotGetImageRequestSender extends Kumo_Handler_RequestMessageQueueSenderAbstract
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
        //$this->debug('********START****');

        if (!$doc instanceof Spizer_Document_Html) return;

        $headers = $doc->getAllHeaders();

        //response is already decoded.
        unset($headers['transfer-encoding']);
        unset($headers['content-encoding']);

        $results = $this->scraper->scrape(new Zend_Http_Response($doc->getStatus(),
                                                      $headers,
                                                      $doc->getBody())
                               , $doc->getUrl());
        //$this->debug($results);

        $targets = $this->filter(array_unique($results['kumo']));

        foreach ($targets as $src) {

            //$request = new Spizer_Request($src);
            $request = new Kumo_Request($src);
            $request->setReferrer($doc->getUrl());
            //if ($this->_config['referer'] === true) {
                $request->setHeader('Referer', $this->toRefererUrl($doc->getUrl()));
            //}

            $this->send($request);
        }
    }

    protected function debug()
    {
        $vars = func_get_args();
        foreach ($vars as $var) {
            Zend_Debug::dump($var);
        }
    }

    public function filter(array $urls)
    {
        return array_diff($urls, explode("\n", $this->_config['have_files']));
    }

    protected function toRefererUrl(Zend_Uri $uri)
    {
        if ($uri->getPort() == '80') { 
            return $uri->getScheme().'://'.$uri->getHost().$uri->getPath().$uri->getQuery();
        }

        return (string) $uri;
    }
}

