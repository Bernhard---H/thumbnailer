<?php
namespace Thumbnailer\Setup;

use Thumbnailer\Exceptions\AccessDenied;

/**
 * folder containing Thumbnailer.phar
 */
define('INSTALL_DIR', dirname(\Phar::running(false)));

/**
 * root directory in Thumbnailer.phar
 */
define('BASE_DIR', 'phar://thumbnailer.phar');


class Config implements \IteratorAggregate, \ArrayAccess, \Countable
{
    protected static $config = array();

    /**
     * if necessary, loads the configuration from the ini files
     */
    public function __construct()
    {
        if (empty(self::$config)) {
            // provide the user with a default configuration file
            if (!file_exists(INSTALL_DIR.'/thumbnailer.ini.php')) {
                copy(BASE_DIR.'/data/default.ini.php', INSTALL_DIR.'/thumbnailer.ini.php');
            }
            $user_config = parse_ini_file(INSTALL_DIR.'/thumbnailer.ini.php', true);
            $default_config = parse_ini_file(BASE_DIR.'/data/default.ini.php', true);

            self::$config = array_merge($default_config, $user_config);
        }
    }

    /**
     * implements \Countable
     *
     * @param int $mode
     * @return int
     */
    public function count($mode = COUNT_NORMAL)
    {
        return count(self::$config, $mode);
    }

    /**
     * implements \ArrayAccess
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset(self::$config[$offset]);
    }

    /**
     * implements \ArrayAccess
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws AccessDenied
     */
    public function offsetSet($offset, $value)
    {
        throw new AccessDenied(
            'Unauthorized access to method "'.__METHOD__.'". '.
            'Instances of the class "'.__CLASS__.'" are read-only.'
        );
    }

    /**
     * implements \ArrayAccess
     *
     * @param mixed $offset
     * @throws AccessDenied
     */
    public function offsetUnset($offset)
    {
        throw new AccessDenied(
            'Unauthorized access to method "'.__METHOD__.'". '.
            'Instances of the class "'.__CLASS__.'" are read-only.'
        );
    }

    /**
     * implements \ArrayAccess
     *
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset(self::$config[$offset]) ? self::$config[$offset] : null;
    }

    /**
     * implements \IteratorAggregate
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator() {
        return new \ArrayIterator(self::$config);
    }
}