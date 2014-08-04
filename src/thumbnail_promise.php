<?php
/**
 * Diese Datei löst Thumbnail-Promises auf und gibt die Bilder zurück.
 */
namespace htlwy\album;

require_once 'inc/conf.php';
require_once INC.'/database2.php';

try {
    if (!isset($_GET['id'])) {
        header('HTTP/ 404 Not Found', true, 404);
        exit;
    }
    $dbh = pdo_connect();
    $stmt = $dbh->prepare("SELECT path, width, height FROM thumbnail_promise WHERE id = :id");
    $stmt->execute(array(':id' => $_GET['id']));

    $data = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!isset($data['path'])) {
        header('HTTP/ 404 Not Found', true, 404);
        exit;
    }
    
    $stmt = $dbh->prepare("DELETE FROM thumbnail_promise WHERE id = :id");
    $stmt->execute(array(':id' => $_GET['id']));

    $thumbnail = new Thumbnail(new Size($data['width'], $data['height']));
    $path = DOC_ROOT.'/'.$thumbnail->get($data['path'], true);

    if (file_exists($path)) {
        header('Content-Type: image/jpeg');
        readfile($path);
        exit;
    } else {
        header('HTTP/500 Internal Server Error', true, 500);
        echo $path;
    }

} catch (\Exception $e) {
    header('HTTP/500 Internal Server Error', true, 500);
    new \htlwy\Logger($e);
}
