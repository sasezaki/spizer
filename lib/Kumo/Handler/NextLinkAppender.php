<?php

require_once 'Spizer/Handler/Abstract.php';
require_once 'Diggin/Scraper/Helper/Simplexml/Pagerize.php';

class Kumo_Handler_NextLInkAppender extends Spizer_Handler_Abstract
{
    // target maybe only next-url
    private $targets = array();

    protected $config = array(
        'pre_ampasand_escape' => false
        );

    public function handle(Spizer_Document $doc)
    {
       // Add document URL to the list of visited pages
        $baseUrl = $this->toUrl($doc->getUrl());
        if (! in_array($baseUrl, $this->targets)) $this->targets[] = $baseUrl;
        // Silently skip all non-HTML documents
        if (! $doc instanceof Spizer_Document_Html) return;

        $pagerize = new Diggin_Scraper_Helper_Simplexml_Pagerize(simplexml_import_dom($doc->getDomDocument()),
                            array('baseUrl' => $this->toUrl($doc->getUrl()),
                                'preAmpFilter' => $this->config[ 'pre_ampasand_escape'])
                          );
        if ($nextLink = $pagerize->getNextLink()) {
            $this->addToQueue($nextLink, $baseUrl);
        }
    }

    //ignore port and fragment
    private function toUrl(Zend_Uri $uri)
    {
        return $uri->getScheme().'://'.$uri->getHost().$uri->getPath().$uri->getQuery();
    }

    //borrowed from Spizer_Handler_LinkAppender
    private function addToQueue($url, $referrer)
    {
        $url = (string) $url;
        
        if (! in_array($url, $this->targets)) {
            $request = new Spizer_Request($url);
            $request->setReferrer($referrer);
            $this->engine->getQueue()->append($request);
            
            $this->targets[] = $url;
        }
    }
}
