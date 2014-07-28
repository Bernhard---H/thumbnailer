<?php
/**
 * generates the phar archive
 */

chdir(__DIR__);

$phar = new Phar(
    'thumbnailer.phar',
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    'thumbnailer.phar'
);

$phar->buildFromDirectory('../src', '/\.php$/');

$phar->setDefaultStub('cli/index.php', 'web/index.php');
