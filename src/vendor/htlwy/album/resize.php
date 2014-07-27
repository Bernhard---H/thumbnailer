<?php

namespace htlwy\album;

/**
 * Class Resize
 * Wird von `ImgResize` dazu verwendet, um alte und neue Groesse des Fotos zu speichern.
 * @package htlwy\album
 */
class Resize
{
    /**
     * Hoehe des neuen Fotos
     *
     * @var int $height
     */
    protected $newHeight;
    /**
     * Hoehe des alten Fotos
     *
     * @var int $height
     */
    protected $oldHeight;
    /**
     * Breite des neuen Fotos
     *
     * @var int $width
     */
    protected $newWidth;
    /**
     * Breite des alten Fotos
     *
     * @var int $width
     */
    protected $oldWidth;

    /**
     * Parsed die Werte aus `Size`
     *
     * @param Size $old
     * @param Size $new
     */
    public function __construct(Size $old = null, Size $new = null)
    {
        if (isset($old)) {
            $this->oldWidth = $old->getWidth();
            $this->oldHeight = $old->getHeight();
        }
        if (isset($new)) {
            $this->newWidth = $new->getWidth();
            $this->newHeight = $new->getHeight();
        }
    }

    /**
     * Getter fuer $newHeight
     *
     * @return int
     */
    public function getNewHeight()
    {
        return $this->newHeight;
    }

    /**
     * Setter fuer $newHeight
     *
     * @param int $height Hoehe in Pixel
     *
     * @return int aktuell gesetzte Hoehe
     * @throws \InvalidArgumentException Der Parameter enthielt einen unzulaessigen Wert.
     */
    public function setNewHeight($height)
    {
        if ($height > 0) {
            $this->newHeight = floor($height);
        } else {
            throw new \InvalidArgumentException('$height kann nicht kleiner oder gleich 0 sein.');
        }
        return $this->newHeight;
    }

    /**
     * Getter fuer $oldHeight
     *
     * @return int
     */
    public function getOldHeight()
    {
        return $this->oldHeight;
    }

    /**
     * Setter fuer $oldHeight
     *
     * @param int $height Hoehe in Pixel
     *
     * @return int aktuell gesetzte Hoehe
     * @throws \InvalidArgumentException Der Parameter enthielt einen unzulaessigen Wert.
     */
    public function setOldHeight($height)
    {
        if ($height > 0) {
            $this->oldHeight = floor($height);
        } else {
            throw new \InvalidArgumentException('$height kann nicht kleiner oder gleich 0 sein.');
        }
        return $this->oldHeight;
    }

    /**
     * Getter fuer $newWidth
     *
     * @return int
     */
    public function getNewWidth()
    {
        return $this->newWidth;
    }

    /**
     * Setter fuer $newWidth
     *
     * @param int $width Breite in Pixel
     *
     * @return int aktuell gesetzte Breite
     * @throws \InvalidArgumentException Der Parameter enthielt einen unzulaessigen Wert.
     */
    public function setNewWidth($width)
    {
        if ($width > 0) {
            $this->newWidth = floor($width);
        } else {
            throw new \InvalidArgumentException('$width kann nicht kleiner oder gleich 0 sein.');
        }
        return $this->newWidth;
    }

    /**
     * Getter fuer $oldWidth
     *
     * @return int
     */
    public function getOldWidth()
    {
        return $this->oldWidth;
    }

    /**
     * Setter fuer $oldWidth
     *
     * @param int $width Breite in Pixel
     *
     * @return int aktuell gesetzte Breite
     * @throws \InvalidArgumentException Der Parameter enthielt einen unzulaessigen Wert.
     */
    public function setOldWidth($width)
    {
        if ($width > 0) {
            $this->oldWidth = floor($width);
        } else {
            throw new \InvalidArgumentException('$width kann nicht kleiner oder gleich 0 sein.');
        }
        return $this->oldWidth;
    }
}