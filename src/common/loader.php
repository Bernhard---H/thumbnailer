<?php
/**
 * Loads all classes from the vendor and lib directories
 */

/**
 * RedBeansPHP 4
 */
require_once __DIR__.'/lib/rb.php';

/**
 * Autoloader for classes in the vendor folder
 *
 * @source https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(
    function ($class) {

        // project-specific namespace prefix
        $prefix = 'Thumbnailer\\';

        // base directory for the namespace prefix
        $base_dir = 'phar://thumbnailer.phar/vendor/Thumbnailer/';

        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // no, move to the next registered autoloader
            return;
        }

        // get the relative class name
        $relative_class = substr($class, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // if the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    }
);


/**
 * hand over to configuration
 */
require_once __DIR__.'/config.php';
