<?php
abstract class Kumo_Request_Store implements Iterator, Serializable
{
    private static $instance;
    private $config;
    
    private function __construct($config)
    {
        $this->config = $config;
    }

    public static function factory($class, $config)
    {
        if (self::$instance) {
            require_once 'Kumo/Request/Exception.php';
            throw new Kumo_Request_Exception("self::$instance is instanced before ");
        }
        
        $className = "Kumo_Request_Store_".$class;
        self::$instance = new $className($config);
        self::$instance->init();

        return self::$instance;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function getConfig()
    {
        return $this->config;
    }
}

