<?php

//http://openpear.org/repository/Diggin_Scraper_Adapter_Htmlscraping/trunk/library/Diggin/Scraper/Adapter/Htmlscraping.php
/** Diggin_Scraper_Adapter_Htmlscraping **/
require_once 'Diggin/Scraper/Adapter/Htmlscraping.php';

require_once 'Spizer/Response.php';
class Kumo_Response extends Spizer_Response
{
    public function getBody()
    {
        $responseAdapter = new Diggin_Scraper_Adapter_Htmlscraping();
        // get foramted XHTML,
        // and encoding to UTF-8 by header's ctype, meta-charset, responseBody
        $xhtml = $responseAdapter->getXhtml($this->response);

        //remove namepsace
        $xhtml = preg_replace(array('/\sxmlns:?[A-Za-z]*="[^"]+"/', "/\sxmlns:?[A-Za-z]*='[^']+'/"), '', $xhtml);

        return $xhtml;
    }

}
