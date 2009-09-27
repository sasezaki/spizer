<?php

require_once 'Spizer/Handler/Abstract.php';
require_once 'Diggin/Scraper/Helper/Simplexml/Pagerize.php';

class Kumo_Handler_NextLinkAppender extends Spizer_Handler_Abstract
{
    // target maybe only next-url
    private $targets = array();

    protected $config = array(
        'status'       => null,
        'content-type' => null,
        'max_follow' => false
    );

    protected $page_count = 1;

    public function handle(Spizer_Document $doc)
    {
        // Silently skip all non-HTML documents
        if (! $doc instanceof Spizer_Document_Html) return;

        // Add document URL to the list of visited pages
        $baseUrl = $doc->getUrl();
       if (! in_array($baseUrl, $this->targets)) $this->targets[] = $baseUrl;

        $pagerize = new Diggin_Scraper_Helper_Simplexml_Pagerize(simplexml_import_dom($doc->getDomDocument()),
                            array('baseUrl' => $this->toUrl($doc->getUrl()))
                          );
        if ($nextLink = $pagerize->getNextLink()) {
            $max_follow = $this->config['max_follow'];
            if (!($max_follow) or $this->page_count <= $max_follow) {
                $this->addToQueue($nextLink, $baseUrl);
                ++$this->page_count;
            }
        }
    }

    /**
     * ignore port and fragment
     * for match siteinfo(wedata)'s url-regex
     *
     */
    private function toUrl(Zend_Uri $uri)
    {
        $port = $uri->getPort();

        $port = (($port > 0) and $port != '80') ? ':'.$port : '';

        return $uri->getScheme().'://'.$uri->getHost().$port.$uri->getPath().$uri->getQuery();
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
