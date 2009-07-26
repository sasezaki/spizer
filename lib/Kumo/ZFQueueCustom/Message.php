<?php
require_once 'Zend/Queue/Message.php';
require_once 'Zend/Json.php';
require_once 'Spizer/Request.php';

class Kumo_ZFQueueCustom_Message extends Zend_Queue_Message
{
    public function __construct($mixed)
    {
        $this->setBody($mixed);
    }

    public function setBody($mixed)
    {

        if (is_string($mixed)) {
            //handle as url
            $request = array('url' => $mixed
                             'method' => 'GET');
        } else if ($mixed instanceof Spizer_Request) {

            $request = array( 
                        'uri' => (string) $mixed->getUri(),
                        'method' => $mixed->getMethod(),
                        'headers' => $mixed->getAllHeaders(),
                        'body' => $mixed->getBody(),
                        'referrer' => $mixed->getReferrer()
                        );
        }
        // else if $mixed instanceof Zend_Http_Client

        $this->_data['body'] = Zend_Json::encode($request);
    }

    public function getBody()
    {
        $data = Zend_Json::decode($this->_data['body']);
        
        $request = new Spizer_Request($data['uri'], $data['method']);
        //$request->setHeaders();
        $request->setBody($data['body']);
        $request->setReferrer($data['referrer']);
        
        return $request;
    }

}
