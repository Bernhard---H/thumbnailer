<?php

use Thumbnailer\Setup\Config;

$config = new Config();


// connect to DB
\R::setup($config['database']['dsn'], $config['database']['username'], $config['database']['password']);

