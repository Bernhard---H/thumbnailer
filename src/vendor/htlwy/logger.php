<?php
namespace htlwy;

/**
 * Class Logger
 * Schreibt die Nachricht aus dem Konstrucktor in die Log-Datei
 *
 * @package htlwy
 */
class Logger extends \Exception
{
    const FILE = '/homepage.log';

    public function __construct($log)
    {
        if($log instanceof \Exception)
        {
            parent::__construct(null, null, $log);
        }
        else
        {
            parent::__construct(self::arrayToString($log));
        }

        $content = date('Y-m-d H:i:s')."\t".self::exceptionToString($this);
        if(!file_put_contents(LOGS.self::FILE, $content, FILE_APPEND ))
        {
            trigger_error('Logger: schreiben in Log-Datei >>'.LOGS.self::FILE.'<< ist Fehlgeschlagen;', E_ERROR);
        }
        if(DEBUGMODE >= 1 && VERBOSE)
        {
            echo '<br />'.nl2br($content).'<br />';
        }
    }

    /**
     * arbeitet wie print_r() gibt den generierten string aber zurueck
     *
     * @param array $array Array das formatiert werden soll
     *
     * @return string
     */
    public static function arrayToString($array)
    {
        return self::arrayToString_helper($array, "");
    }
    private static function arrayToString_helper($array, $_tabs = "")
    {
        if(is_array($array))
        {
            $ret = $_tabs."Array\r\n".$_tabs."{\r\n";
            foreach($array as $key => $value)
            {
                $ret .= $_tabs."\t[".$key."] => ".self::arrayToString_helper($value, $_tabs."\t");
            }
            $ret .= $_tabs."}\r\n";
        }
        else
        {
            $ret = $array."\r\n";
        }
        return $ret;
    }

    /**
     * bereitet Exception fuer Logger auf
     *
     * @param \Exception $exception
     *
     * @return string
     */
    public static function exceptionToString(\Exception $exception)
    {
        $ret = '';
        $ret .= get_class($exception).":\t";
        if($exception->getCode() != 0)
        {
            $ret .= $exception->getCode()."\t";
        }
        $ret .= $exception->getMessage()."\t";
        $ret .= 'in '.$exception->getFile()."\t";
        $ret .= 'at Line: '.$exception->getLine()."\r\n";
        if($exception->getPrevious() != null)
        {
            $ret .= "\t\t@original\t".self::exceptionToString($exception->getPrevious());
        }
        return $ret;
    }
}