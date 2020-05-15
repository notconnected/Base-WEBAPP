<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/application/Entities"), $isDevMode);

// database configuration parameters
$conn = array(
    'dbname' => $_SERVER['MYSQL_DATABASE'] ?? 'payments',
    'user' => $_SERVER['MYSQL_USER'] ?? 'pma',
    'password' => $_SERVER['MYSQL_PASSWORD'] ?? 'password',
    'host' => $_SERVER['MYSQL_HOST'] ?? 'localhost',
    'driver' => 'pdo_mysql',
    'charset' => 'utf8'
);

//Paths
define("BASE_PATH", realpath(dirname(__FILE__))); 
define("APP_PATH", BASE_PATH.'/application');
define('LANGUAGES_PATH', APP_PATH.'/languages'); 
  
$locale = 'ru_RU'; 

putenv('LC_ALL=' . $locale); 
putenv('LANG=' . $locale); 
putenv('LANGUAGE=' . $locale); 
setlocale(LC_ALL, $locale, $locale . '.utf8'); 
bind_textdomain_codeset($locale, 'UTF-8'); 
bindtextdomain($locale, LANGUAGES_PATH); 
textdomain($locale);

// obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);