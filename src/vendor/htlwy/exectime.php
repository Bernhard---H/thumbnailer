<?php

namespace htlwy;


/**
 * Unterstuetzt die Verwaltung von PHPs `max_execution_time` und setzt diese bei bedarf auf
 * einen groesseren Wert.
 *
 * @package htlwy
 */
class ExecTime
{
    /**
     * Maximale Zeit, die das Script wirklich laufen darf.
     */
    const absoluteMaxExecutionTime = 300; // 5 min
    /**
     * Definiert den Startzeitpunkt des Scripts
     * @var float
     */
    protected static $start = 0.0;
    /**
     * Zeitpunkt zu dem die Codeausführung voraussichtlich endet
     * @var float
     */
    protected static $end = 0.0;

    /**
     * Versucht herauszufinden, wann das Script gestartet wurde.
     */
    public function __construct()
    {
        if(self::$start == 0)
        {
            if(isset($_SERVER['REQUEST_TIME_FLOAT']))
            {
                self::$start = $_SERVER['REQUEST_TIME_FLOAT'];
            }
            elseif(isset($_SERVER['REQUEST_TIME']))
            {
                if($_SERVER['REQUEST_TIME'] + 1 < microtime(true))
                {
                    // kommastellen werden abgeschnitten => +1 Sekunde
                    self::$start = $_SERVER['REQUEST_TIME'] + 1;
                }
                else
                {
                    self::$start = microtime(true);
                }
            }
            else
            {
                self::$start = microtime(true);
            }
            self::$end = self::$start + (int)ini_get('max_execution_time');
        }
    }

    /**
     * Setzt die Zeit, die mindestens noch fuer das erfolgreiche beenden des Scripts benoetigt
     * wird. => Der Wert von `max_execution_time` wird on the fly angepasst.
     *
     * @throws \RuntimeException Der Wert von `max_execution_time` aufgrund der
     * Ueberschreitung von `self::absoluteMaxExecutionTime` nicht mehr weiter angehoben werden.
     *
     * @param int $time
     */
    public function need($time)
    {
        if($time > self::$end - microtime(true))
        {
            // aktuelles Zeitlimit reicht nicht aus -> Zeitlimit Zaehler zuruecksetzten,
            // neuen Wert definieren
            if(self::$end - self::$start + $time < self::absoluteMaxExecutionTime)
            {
                set_time_limit($time);
                self::$end += $time;

                if(VERBOSE)
                {
                    hp_log('Das Limit von "max_execution_time" wurde auf '.
                        (self::$end - self::$start).' angehoben.');
                }
            }
            else
            {
                // limit ueberschritten
                throw new \RuntimeException('Der Wert von "max_execution_time" konnte nicht '.
                    'mehr weiter angehoben werden.');
            }
        }
    }
}

