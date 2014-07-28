<?php

namespace htlwy;

use htlwy\thumbs\Resize;

/**
 * Class MemoryLimit
 * Verwaltet das Memory-Limit des Webservers.
 *
 * @package htlwy
 */
class MemoryLimit
{
    /**
     * Legt das Maximum fest, auf den das Memory Limit bei der Berechnung von Bildern gesetzt
     * werden darf. Default: 512 MiB
     */
    const maxMemoryLimit = 536870912; // 512 * 1024 * 1024

    /**
     * Berechnet mit einer Ueberschlagsrechnung den vermutlich benoetigten Arbeitspeicher zum verkleinern
     * eines Bildes und passt das Memorylimit entsprechend an oder wirft eine Exception, wenn dieses nicht
     * mehr angehoben werden kann.
     *
     * @param \htlwy\thumbs\Resize $size
     */
    public function picture(Resize $size)
    {
        // voraussichtlich benoetigter Speicher: 5 Byte/Pixel + 10% Fehler Buffer
        //  ... es werden etwas mehr als 5 Byte/Pixel benoetigt
        $memory_need =
            ($size->getOldHeight() * $size->getOldWidth() + $size->getNewHeight() * $size->getNewWidth()) *
            5 * 1.1 + memory_get_usage(true);

        $this->check($memory_need);
    }

    /**
     * Prueft das Memory-Limit und setzt es gegebenen falls neu
     *
     * @param int $limit Benoetigter Arbeitsspeicher in Byte
     *
     * @throws OutOfMemoryException
     */
    public function check($limit)
    {
        // memory_limit neu setzten
        if(ini_get('memory_limit') < $limit)
        {
            if($limit > self::maxMemoryLimit)
            {
                throw new OutOfMemoryException('Fuer das berechnen der Bilder steht vermutlich '.
                                               'nicht ausreichend Speicher zur verfuegung!');
            }
            else
            {
                ini_set('memory_limit', $limit);
            }
        }
    }
} 