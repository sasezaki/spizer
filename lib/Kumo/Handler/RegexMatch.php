<?php
//http://svn.openpear.org/Diggin_Scraper_Adapter_Htmlscraping/trunk/library/Diggin/Http/Response/Encoding.php
require_once 'Diggin/Http/Response/Encoding.php';
class Kumo_Handler_RegexMatch extends Spizer_Handler_RegexMatch
{
    public function handle(Spizer_Document $document)
    {
        //get! encoding_to_utf-8
        $body = Diggin_Http_Response_Encoding::encode($document->getBody(), $document->getHeader('content-type'));
        
        if (preg_match($this->_config['match'], $body, $m, PREG_OFFSET_CAPTURE)) {
            $this->engine->log('RegexMatch', array(
                'message' => 'Document body matched lookup expression',
              'regex' => $this->_config['match'],
                'match' => $m[0][0],
                'offset' => $m[0][1]
            ));
        }
    }
}
