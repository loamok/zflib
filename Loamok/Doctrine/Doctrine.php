<?php
ini_set('display_errors', true);
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH',
              realpath(dirname(__FILE__) . '/../../../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));

// Typically, you will also want to add your library/ directory
// to the include_path, particularly if it contains your ZF install
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));
require_once "zfPath.php";
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library/Doctrine'),
    get_include_path(),
)));

require_once APPLICATION_PATH . '/../library/Doctrine/Doctrine.php';

//make classes autoload without doing require
require_once('Zend/Loader/Autoloader.php');
$autoloader = Zend_Loader_Autoloader::getInstance();

$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
Zend_Registry::set('config',$config);

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $config
);

$connection = $application->getBootstrap()->bootstrap('doctrine');
$config = $application->getOption('doctrine');

if(@$_SERVER["argv"][1] == "load-data") {
    $fixture = $_SERVER["argv"][2];

    require_once 'Loamok/Doctrine/Fixtures.php';
    require_once $config['data_fixtures_path']."/".$fixture.".php";
    $fixClass = "Application_Data_Fixture_{$fixture}";
    $clean = (@$_SERVER["argv"][3] == "clean")?true:false;
    $fix = new $fixClass($clean);

    $fix->run();
    exit();
}

$cli = new Doctrine_Cli($config);
$cli->run($_SERVER['argv']);
