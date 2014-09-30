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
            $this->normalize();
        }
    }

    /**
     * normalizes the format of certain configurations in `self::$config`
     * e.g. paths end with a slash
     */
    protected function normalize()
    {
        $dir = self::$config['dir'];

        $dir['webserver'] = $this->normalizePath($dir['webserver']);
        $dir['app'] = $this->normalizePath($dir['app']);

        $dir['app'] = $this->mergeRelativePath($dir['webserver'], $dir['app']);

        self::$config['dir'] = $dir;
    }

    /**
     * makes sure that `$path` ends with a slash,
     * converts (windows-)backslash into slash
     *
     * note: the function only supports paths to a directory
     *
     * @param string $path
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        return $path;
    }

    /**
     * Merges two variables containing a path.
     *
     * if `$sub` is a relative path (does not start with a slash)
     *  it will simply be concatenated to `$main`
     * if `$sub` is an absolute path (starting with a slash)
     *  only `$sub` will be returned.
     *
     * @param string $main
     * @param string $sub
     *
     * @return string
     */
    protected function mergeRelativePath($main, $sub)
    {
        if (substr($sub, 0, 1) == '/') {
            return $sub;
        } else {
            if (substr($sub, 0, 2) == './') {
                $sub = substr($sub, 2);
            }
            return $main.$sub;
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
    public function getIterator()
    {
        return new \ArrayIterator(self::$config);
    }
}