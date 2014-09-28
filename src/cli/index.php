<?php
/**
 * Entrypoint for CLI requests
 */

require_once 'phar://thumbnailer.phar/common/loader.php';


$arguments = getopt('h', array('help'));

if (isset($arguments['h']) || isset($arguments['help']) || empty($arguments)) {
    include BASE_DIR.'/cli/help.php';
}