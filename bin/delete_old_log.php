<?php
require_once(__DIR__ . '/../htdocs/index.php');

/**
 * Delete log entries which are errors/warnings and older than one month
 * We always keep Zend_Log::INFO level because we use it for statistics
 */
function deleteOldLogs()
{
	$db = _db();
	$db->query('LOCK TABLES `log` WRITE;');
	$res = $db->query('DELETE FROM `log` WHERE `priority` != ? AND `time` < DATE_SUB(NOW(), INTERVAL 1 MONTH);', array(Zend_Log::INFO));
	$db->query('UNLOCK TABLES;');
	$count = $res->rowCount();
	
	echo "$count logs entry deleted\n";
}

// we only run if this file was NOT included (otherwise, the file was included to access misc functions)
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
	deleteOldLogs();
}
