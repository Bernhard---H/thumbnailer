<?php

namespace htlwy\album;

use htlwy\ExecTime;
use htlwy\Logger;
use htlwy\MemoryLimit;

/**
 * Stellt funktionen zum verkleinern von Fotos zur Verfuegung.
 * Die Klasse unterstuetz folgende Typen:
 * - JPEG
 * - PNG
 * - GIF

 */
class ImgResize
{
    /**
     * Legt die JPEG-Qualtiaet (0 bis 100) fuer das Thumbnail fest.
     *
     * @var int $quality
     */
    protected $quality = 85;

    /**
     * Datei(endungen) die verarbeitet werden können
     * @var array
     */
    protected static $fileExt = array('jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'png', 'gif');

    /**
     * Init-Konfiguration des Objekts
     */
    public function __construct()
    {
    }

    /**
     * Erstellt eine verkleinerte Version des Bildes, dessen Pfad als erster
     * Parameter angegeben wurde.
     *
     * @param string $picture Pfad zum Bild von dem ein Vorschaubild erzeugt
     *                         werden soll.
     * @param Size $size Zielgroesse des Fotos
     * @param string $spicture Pfad an dem das verkleinerte Bild gespeichert werden soll. Wird dieser
     *                         Parameter nicht angegeben, so wird das Original ueberschrieben.
     *
     * @return bool
     */
    public function resize($picture, Size $size, $spicture = null)
    {
        $exec = new ExecTime();
        if (!$this->pictureExists($picture) && !is_file($picture)) {
            return false;
        }

        list($tmp['width'], $tmp['height']) = getimagesize($picture);
        $resize = $this->calcImageSize(new Size($tmp['width'], $tmp['height']), $size);

        $this->chkMemLimit($resize);
        $exec->need(10);

        $source = $this->getImage($picture);

        $small = imagecreatetruecolor($resize->getNewWidth(), $resize->getNewHeight());
        if (!imagecopyresampled(
            $small,
            $source,
            0,
            0,
            0,
            0,
            $resize->getNewWidth(),
            $resize->getNewHeight(),
            $resize->getOldWidth(),
            $resize->getOldHeight()
        )
        ) {
            return false;
        }
        ImageDestroy($source);

        // Thumbnail als progressive Speichern
        imageinterlace($small, 1);


        if (!isset($spicture)) {
            $spicture = $picture;
        }
        if (!imagejpeg($small, $spicture, $this->quality)) {
            return false;
        }

        //Speicherplatz wieder freigeben
        ImageDestroy($small);

        return true;
    }

    /**
     * Prueft ob das angegebene Bild existiert. Haengt automatisch den
     * Pfad zum Document Root des Webservers an, siehe `Thumbnail::docRoot`.
     * Wird das Bild nicht gefunden, so wird automatisch die Dateiendung
     * variert.
     *
     * @param string $picture Vollstaendiger Pfad zum Bild. Wird die Datei nicht
     *                        gefunden, so wird der Pfad automatisch ergaenzt.
     *
     * @return bool Gibt `false` zurueck, wenn das Bild nicht gefunden
     *        wurde, sonst `true`.
     */
    protected function pictureExists(& $picture)
    {
        if (!file_exists($picture)) {
            if (file_exists(DOC_ROOT.'/'.$picture)) {
                $picture = DOC_ROOT.'/'.$picture;
                return true;
            } elseif (file_exists(dirname($picture))) {
                return $this->pictureExistsSub($picture);
            } elseif (file_exists(dirname(DOC_ROOT.'/'.$picture))) {
                return $this->pictureExistsSub($picture);
            }
            return false;
        }
        return true;
    }

    private function pictureExistsSub(& $picture)
    {
        $fileExt = array();
        for ($i = 0; $i < count(self::$fileExt); $i++) {
            $fileExt[$i] = self::$fileExt[$i].','.strtoupper(self::$fileExt[$i]);
        }
        $fileExt = '{'.implode(',', $fileExt).'}';

        $info = pathinfo($picture);
        $pattern = $info['dirname'].'/'.$info['filename'].'.'.$fileExt;

        foreach (glob($pattern, GLOB_NOSORT | GLOB_BRACE) as $file) {
            $picture = $file;
            return true;
        }
        return false;
    }

    /**
     * Bewertet anhand des Dateinamen/Pfades ob die gegebene Datei ein Bild ist.
     * Gibt nur `true` zurück, wenn das Bild auch von dieser Bibliothek verarbeitet werden kann.
     * @param string $picture
     * @return bool
     */
    public static function isPicture($picture)
    {
        $ext = strtolower(pathinfo($picture, PATHINFO_EXTENSION));
        if (in_array($ext, self::$fileExt)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Errechnet aus dem gegebenem Parameter und der angegebene Zielgroesse die benoetigten Werte zum
     * Erzeugen des verkleineren Fotos
     *
     * @param Size $old Muss ein Objekt vom Type `Size` sein, bei dem die
     *                   Werte `height` und `width` gesetzt sind.
     * @param Size $new
     *
     * @return Resize Assoziatives-Array mit den Werten fuer
     *        `imagecopyresampled()`
     */
    protected function calcImageSize(Size $old, Size $new)
    {
        $return = new Resize($old, $new);

        // hoehe und breite sind definiert
        if ($new->getHeight() != 0 && $new->getWidth() != 0) {
            $ratio = $old->getWidth() / $old->getHeight();
            $width = $new->getHeight() * $ratio;

            if ($width < $new->getWidth()) {
                // berechnete Breite ist kleiner => berechnete Breite verwenden
                $return->setNewWidth($width);
            } else {
                // berechnete Breite ist größer (oder gleich) => Höhe berechnen
                $ratio = $old->getHeight() / $old->getWidth();
                $return->setNewHeight($new->getWidth() * $ratio);
            }
        } // nur hoehe ist definiert
        elseif ($new->getHeight() != 0) {
            $ratio = $old->getWidth() / $old->getHeight();
            $return->setNewWidth($new->getHeight() * $ratio);
        } // nur breite ist definiert
        elseif ($new->getWidth() != 0) {
            $ratio = $old->getHeight() / $old->getWidth();
            $return->setNewHeight($new->getWidth() * $ratio);
        } // weder breite noch hoehe sind definiert
        else {
            $return->setNewWidth($old->getWidth());
            $return->setNewHeight($old->getHeight());
        }

        if ($old->getWidth() < $return->getNewWidth() || $old->getHeight() < $return->getNewHeight()) {
            $return->setNewWidth($old->getWidth());
            $return->setNewHeight($old->getHeight());
        }

        return $return;
    }

    /**
     * Berechnet mit einer Ueberschlagsrechnung den vermutlich benoetigten Arbeitspeicher und passt das
     * Memorylimit entsprechend an oder wirft eine Exception, wenn dieses nicht mehr angehoben werden kann.
     *
     * @param Resize $size
     *
     * @throws \htlwy\OutOfMemoryException
     */
    protected function chkMemLimit(Resize $size)
    {
        $mem = new MemoryLimit();
        $mem->picture($size);
    }

    /**
     * Erzeugt aus dem gegebenen Pfad ein GD-Image und gibt den Resource-Identifier zurueck
     *
     * @param string $picture Pfad zum Bild
     *
     * @return bool|resource GD-Image
     */
    protected function getImage($picture)
    {
        switch (strtolower(pathinfo($picture, PATHINFO_EXTENSION))) {
            case 'png':
                return imagecreatefrompng($picture);
            case 'jpeg':
            case 'jpg':
            case 'jpe':
            case 'jfif':
            case 'jif':
                return imagecreatefromjpeg($picture);
            case 'gif':
                return imagecreatefromgif($picture);
            default:
                if(VERBOSE){
                    new Logger('Konnte ein Bild vom Typ "'.pathinfo($picture, PATHINFO_EXTENSION).'" nicht erkennen');
                }
                return false;
        }
    }

    /**
     * Legt die JPEG-Qualtiaet (0 bis 100) fuer das Thumbnail fest. Default ist 85
     *
     * @param int $quality Wert von `0` bis `100`, wobei `100` die beste Qualitaet
     *                     darstellt.
     *
     * @return int Aktuell gesezter Wert.
     */
    public function quality($quality = null)
    {
        if (isset($quality)) {
            if (0 <= $quality && $quality <= 100) {
                $this->quality = $quality;
            }
        }
        return $this->quality;
    }

}


