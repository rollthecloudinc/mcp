<?php
// load base XML file to determine path to config and app root directories
$objBaseXML = simplexml_load_file('base.xml');

// directory seperator
define('DS',(string) $objBaseXML->ds);

// package seperator
define('PKG',(string) $objBaseXML->pkg);

// absolute www directory path
define('WWW',str_replace('//','/',dirname(__FILE__)));

// absolute root path
define('ROOT',WWW.DS.$objBaseXML->root);

// absolute config path
define('CONFIG',WWW.DS.$objBaseXML->config);

// absolute cache path
define('CACHE',WWW.DS.$objBaseXML->cache);

// absolute files path
define('FILES',WWW.DS.$objBaseXML->files);

require_once(ROOT.DS.'App'.DS.'Lib'.DS.'Console'.DS.'Console.php');
require_once(ROOT.DS.'App'.DS.'Lib'.DS.'Import'.DS.'Import.php');
require_once(ROOT.DS.'App'.DS.'Lib'.DS.'Request'.DS.'Request.php');

// Application dependencies
$objConsole = new Console();
$objRequest = new Request();
$objImport = new Import($objConsole);

// Import master control program
$objImport->import('App.Core.MCP');

// create master program controller
$objMCP = MCP::createInstance($objConsole,$objImport,$objRequest);

// execute master component
$objMCP->kick_off($_SERVER['SCRIPT_NAME']);
?>