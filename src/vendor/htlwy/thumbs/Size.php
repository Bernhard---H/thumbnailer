<?php

namespace htlwy\thumbs;

/**
 * Wird benoetigt um die Groesse eines Thumbnails festzulegen.
 *
 * Die angegebenen Werte muessen vom Typ Integer sein. Der Wert `0`
 * wird als nicht gesetzt interpretiert und stellt somit keine
 * Begrenzung dar oder verursacht das zurueckfallen auf einen den
 * Default-Wert.
 *
 * Wird die Breite/Hoehe explizit angegeben, so werden diese bevorzugt,
 * auch wenn sie nicht in der Bereichsangabe liegen.
 *
 * Achtung: Die angegebenen Werte werden zu keinem Zeitpunkt geprueft.
 *        Werden keine Integer-Werte oder logisch Falsche Werte angegeben
 *        z.B. `minWidth` > `maxWidth`, so kann es sein, dass es zu einer
 *        Fehlermeldung kommt. Es kann aber genauso gut sein, dass die
 *        generierten Grafiken nicht die gewuenschte groesse haben.
 */
class Size
{
    /**
     * Gibt die genaue Hoehe an.
     *
     * @var int $height
     */
    protected $height = 0;
    /**
     * Gibt die genaue Breite an.
     *
     * @var int $width
     */
    protected $width = 0;

    /**
     * Ermoeglicht es, die Werte in kurzer Schreibweise zu definieren.
     *
     *
     * @param int $width Der angegebene Integer-Wert definiert die
     *                      geforderte Breite.
     * @param int $height Der angegebene Integer-Wert definiert die
     *                      geforderte Hoehe.
     */
    public function __construct($width = null, $height = null)
    {
        if (isset($height)) {
            $this->setHeight(floor($height));
        }
        if (isset($width)) {
            $this->setWidth(floor($width));
        }
    }

    /**
     * Prueft alle Attribute auf `0`. Sind alle Attribute auf den Wert
     * `0` gesetzt, so gibt die Funktion `true` zurueck.
     *
     * @return bool
     */
    public function allZero()
    {
        if ($this->height == 0 && $this->width == 0) {
            return true;
        }
        return false;
    }

    /**
     * Gibt `true` zurueck, wenn eine absolute Groessenangabe nicht `0` ist.
     *
     * @return bool
     */
    public function definedAbsolut()
    {
        if ($this->height != 0 || $this->width != 0) {
            return true;
        }
        return false;
    }

    /**
     * Getter fuer $height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Setter fuer $height
     *
     * @param int $height Hoehe in Pixel; 0 wird als Wildcard interpretiert
     *
     * @return int aktuell gesetzte Hoehe
     * @throws \InvalidArgumentException Der Parameter enthielt einen unzulaessigen Wert.
     */
    public function setHeight($height)
    {
        if ($height >= 0) {
            $this->height = floor($height);
        } else {
            throw new \InvalidArgumentException('$height kann nicht kleiner 0 sein.');
        }
        return $this->height;
    }

    /**
     * Getter fuer $width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Setter fuer $width
     *
     * @param int $width Breite in Pixel; 0 wird als Wildcard interpretiert
     *
     * @return int aktuell gesetzte Breite
     * @throws \InvalidArgumentException Der Parameter enthielt einen unzulaessigen Wert.
     */
    public function setWidth($width)
    {
        if ($width >= 0) {
            $this->width = floor($width);
        } else {
            throw new \InvalidArgumentException('$width kann nicht kleiner 0 sein.');
        }
        return $this->width;
    }
}
