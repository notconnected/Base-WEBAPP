<?php
header('Access-Control-Allow-Origin: *');
require_once('../bootstrap.php');

try { 
    session_start();
    $app = new \Core\Application($entityManager);
    $app->run();
} catch (Exception $e) {
    header("Exception: {$e->getMessage()}\n", true, $e->getCode() ?? 400);
}