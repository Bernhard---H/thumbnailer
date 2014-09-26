<?php
namespace htlwy\thumbnailer;
/**
 * folder containing thumbnailer.phar
 */
define('INSTALL_DIR', dirname(\Phar::running(false)));

/**
 * root directory in thumbnailer.phar
 */
define('BASE_DIR', 'phar://thumbnailer.phar');


// provide the user with a default configuration file
if(!file_exists(INSTALL_DIR.'/thumbnailer.ini'))
{
    file_put_contents(INSTALL_DIR.'/thumbnailer.ini', file_get_contents(BASE_DIR.'/data/default.ini'));
}
$user_config = parse_ini_file(INSTALL_DIR.'/thumbnailer.ini', true);
$default_config = parse_ini_file(BASE_DIR.'/data/default.ini', true);


$config = array_merge($default_config, $user_config);


// connect to DB
\R::setup($config['database']['dsn'], $config['database']['username'], $config['database']['password']);

