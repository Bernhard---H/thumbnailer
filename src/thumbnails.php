<?php
/**
 * Verwaltet Thumbnails: indizieren, alte löschen
 */
chdir(__DIR__);
require_once('./inc/conf.php');

use \htlwy\thumbs\Cleanup;

new Cleanup();

require_once INC.'/database2.php';
try{
    $dbh = pdo_connect();
    $dbh->exec("TRUNCATE thumbnail_promise");
}catch (Exception $e){
    new \htlwy\Logger($e);
}