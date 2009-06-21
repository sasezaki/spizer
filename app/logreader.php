<?php

require_once 'Zend/Db.php';

class KumoMod_Model_SpizerLog
{
    private $_logger;

    public function __construct($loggerPath = '../zend-test.sq3')
    {
        if (!$logger = realpath($loggerPath)) {
            throw new KumoMod_Exception();
        }

        $this->_logger = $logger;
    }

    private function _read($table)
    {
        $db = Zend_Db::factory('Pdo_Sqlite', array('dbname' => $this->_logger));
        return $db->fetchAll("select * from $table");
    }

    public function __get($var)
    {
        return $this->_read($var);
    }
}


if (debug_backtrace()) return;

$spizerlog = new KumoMod_Model_SpizerLog;

require_once 'Zend/Text/Table.php';
require_once 'Zend/Text/Table/Column.php';
$table = log2texttable($spizerlog->messages);

echo $table;

function log2texttable(array $arr)
{

    $table = new Zend_Text_Table(array('columnWidths' => array(3, 2, 15, 10, 60),
                                       'AutoSeparate' => Zend_Text_Table::AUTO_SEPARATE_NONE));

    foreach ($arr as $num => $colomn) {
        $table->appendRow($colomn);
    }

    return $table;
}
