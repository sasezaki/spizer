<?php
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));


set_include_path(dirname(dirname(dirname(__FILE__))) . '/lib' . PATH_SEPARATOR . get_include_path());

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

define('SPIZER_VERSION', '0.1');

$opts = new Zend_Console_Getopt(array(
    'yml|y=s'     => 'Use a YAML configuration file',
    'ini|i=s'     => 'Use an INI configuration file',
    'xml|x=s'     => 'Use an XML configuration file',
    'section|s=s' => 'Specify configuration section to use (default is \'default\')',  
    'help|h'      => 'Show this help text',
    'version|v'   => 'Show version information and exit'
));

// Parse command line options
try {
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    fwrite(STDERR, "Error parsing command line options: {$e->getMessage()}\n");
    exit(1);
}

// If help, show usage and exit
if ($opts->h) {
    spizer_usage();
    exit(0);
}

// If version, show version information and exit
if ($opts->v) {
    fwrite(STDERR, "Spizer ver. " . SPIZER_VERSION . ", rev. \$Revision$ \n");
    exit(0);
}

// Load configuration
$section = 'default';
if ($opts->s) $section = $opts->s;

if ($opts->y) {
    if ($opts->x || $opts->i || $opts->p) die_single_configfile();
    $config = Spizer_Config::load($opts->y, Spizer_Config::YAML, $section);
    
} elseif ($opts->i) {
    if ($opts->x || $opts->y || $opts->p) die_single_configfile();
    $config = Spizer_Config::load($opts->i, Spizer_Config::INI, $section);
    
} elseif ($opts->x) {
    if ($opts->i || $opts->y || $opts->p) die_single_configfile();
    $config = Spizer_Config::load($opts->x, Spizer_Config::XML, $section);
    
} elseif ($opts->p) {
    if ($opts->x || $opts->y || $opts->i) die_single_configfile();
    $config = Spizer_Config::load($opts->p, Spizer_Config::PHP, $section);
    
} else {
    die_single_configfile();
}

// Make sure we have a URL
$args = $opts->getRemainingArgs();
$url = isset($args[0]) ? $args[0]: $config->url;
if (! $url) {
   spizer_usage();
   exit(1); 
}


// Set up engine
$engine = new Spizer_Engine((array) $config->engine);


/**
 * set up next-link
 */
Diggin_Scraper_Helper_Simplexml_Pagerize::setCache(
    $cache = Zend_Cache::factory($config->pagerize->cache->frontend,
                        $config->pagerize->cache->backend,
                        $config->pagerize->cache->frontendOptions->toArray(),
                        $config->pagerize->cache->backendOptions->toArray()
                        )
    );

//siteinfo配列をセットします
Diggin_Scraper_Helper_Simplexml_Pagerize::appendSiteInfo('mysiteinfo', $config->siteinfo->toArray());

//request wedata
/*
if (!$siteinfo = Diggin_Scraper_Helper_Simplexml_Pagerize::loadSiteinfo('wedata')) {
    require_once 'Diggin/Service/Wedata.php';
    $pagerize = Diggin_Service_Wedata::getItems('AutoPagerize');
}

if (isset($pagerize)) {
    Diggin_Scraper_Helper_Simplexml_Pagerize::appendSiteInfo('wedata',new Diggin_Siteinfo($pagerize));
}
*/

/**
 * Set up the logger object
 * 
 * The logger type is defined in the configuration file - if it contains an 
 * underscore in it's name, it is considered to be a user-defined logger - if
 * no underscore is found, the default 'Spizer_Logger_' prefix is added to 
 * the class name.
 */ 
$type = $config->logger->type; 
if (strpos($type, '_') === false) $type = 'Spizer_Logger_' . $type;
Zend_Loader::loadClass($type);

$logger = new $type($config->logger->options->toArray());
$engine->setLogger($logger);

// Set up the handler objects - same underscore rules apply here as well.
$handlerLoader =  new Zend_Loader_PluginLoader(array(
            'Spizer_Handler_' => 'Spizer/Handler',
            'Kumo_Handler' => 'Kumo/Handler'));

if ($config->handlers) {
    foreach ($config->handlers as $name => $hconf) {
        $type = $hconf->type; 
        if (! $type) continue; // Silenty ignore badly-defined loggers (@todo: Throw exception?)
        $handlerClass = $handlerLoader->load($type);

        $handler = new $handlerClass($hconf->options->toArray());
        $handler->setHandlerName($name);
        $engine->addHandler($handler);
    }
}

// If we have pcntl - set up a handler for sigterm
if (function_exists('pcntl_signal')) {
    declare(ticks = 1);
    pcntl_signal(SIGABRT, 'do_exit');
    pcntl_signal(SIGHUP,  'do_exit');
    pcntl_signal(SIGQUIT, 'do_exit');
    pcntl_signal(SIGINT,  'do_exit');
    pcntl_signal(SIGTERM, 'do_exit');
}

// Go!
$engine->run($url);


//run queue's
//var_dump($queue);

do_exit();
/**
 * -------
 * Execution ends here - Some functions are defined next
 * -------
 */

/**
 * Show usage message
 *
 * @return void
 */
function spizer_usage()
{
    global $argv;
    $ver = SPIZER_VERSION;
    
    $usage = <<<USAGE
Spizer - the flexible web spider, ver. $ver 
Usage: {$argv[0]} [-h] [-i|-p|-x|-y] [-s <section] <file> <Start URL>

You must specify one of the following configuration formats:
  --yml     | -y <file>   Load a YAML configuration file
  --xml     | -x <file>   Load an XML configuration file (not yet implemented)
  --ini     | -i <file>   Load an INI configuration file (not yet implemented)
  
Additionally, you can specifiy one of the following options:
  --section | -s          Configuration file section to load 
                          (default is 'default')
  --version | -v          Show version information and exit
  --help    | -h          Show this help text

USAGE;

    fwrite(STDERR, $usage);
}

/**
 * This piece of code repeats, so I put it into a function
 *
 * @return void
 */
function die_single_configfile()
{
    fwrite(STDERR, "Error: You must specifiy one (and only one) configration file.\n");
    fwrite(STDERR, "Use '{$argv[0]} --help' to get some assistance.\n");
    exit(1);
}

/**
 * Stop the execution and exit in a sane manner
 *
 * @return void
 */
function do_exit()
{
    global $engine;
    
    $c = $engine->getRequestCounter();
    unset($engine);
    
    file_put_contents('php://stdout', "Spizer preformed a total of $c HTTP requests.\n");
    exit(0);
}
