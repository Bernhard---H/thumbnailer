<?php

namespace htlwy\thumbs;

use htlwy\files\Files;
use htlwy\Logger;

/**
 * Die Instantzen dieser Klasse ermoeglichen das einfache Verwalten von
 * Thumbnails. Hierfuer stehen Methoden zur Verfuegung, mit denen auf die
 * Vorschaubilder zugegriffen werden kann, diese erzeugt, umbenannt und
 * wieder entfernt werden koennen.
 * Nachfolgend wird als Original-Bild immer jenes Foto bezeichnet, von
 * dem aus die Thumbnails erstellt werden. Mit diesem kann anschliessend
 * das Thumbnail auch wieder abgefragt werden.
 *
 * <hr />
 * Diese Regular-Expression waehlt alle gueltigen Bezeichnungen fuer
 * Thumbnail-Ordner aus, gleichzeitig werden die Werte fuer Hoehe und Breite extrahiert:
 *
 *     $thumb_regex = '#^'.preg_quote(self::targetPrefix).'(([0-9]+)x([0-9]+))$#';
 *
 *
 * <hr />
 * Beispielcode:
 *
 *     require_once('./inc/conf.php');
 *
 *     // new Size( max-breite, max-hoehe);
 *     // Seitenverhaeltnis wird immer eingehalten,
 *     // alle ausgegebenen Bilder sind JPEG,
 *     // Thumbs werden (meist) erst auf verlangen des Browsers erstellt
 *     $thumbnailer = new \htlwy\thumbs\Thumbnail(new \htlwy\thumbs\Size(200, 400));
 *
 *     $dbh = pdo_connect();
 *     $stmt = $dbh->query("SELECT path, alt FROM irgendWelcheBilder WHERE a = b");
 *
 *     while ($pic = $stmt->fetch(PDO::FETCH_ASSOC)) {
 *         echo '
 *         <div>
 *             <img src="'.$thumbnailer->get($pic['path']).'" alt="'.$pic['alt'].'" />
 *         </div>';
 *     }
 *
 */
class Thumbnail extends ImgResize
{
    /**
     * Wird beim generieren des Thumbnail-Pfades dem Namen des
     * Thumbnail-Ordners vorangestellt. Wird dieser Wert geaendert
     * werden alle bisher erzeugten Thumbnails zu Leichen (nicht mehr
     * verwalteter Muell), die ihrerseits zu weiterem Muell fuehren
     * koennen.
     */
    const targetPrefix = '.thumbs_';

    /**
     * Regex zur auswahl aller Thumbnail-Ordner
     *
     * @var string regular expression
     */
    public static $thumb_regex = '';

    /**
     * Basispfad für Thumbnail-Promises
     * @var string
     */
    public static $promise_base = '';

    /**
     * Definiert die gewuenschte Groesse
     *
     * @var Size $size
     */
    protected $size;

    /**
     * Zuletzt in die Datenbank eingefuegte ID eines Thumbnails
     *
     * @var int
     */
    protected $lastThumbID;

    /**
     * Hash-Wert des zulezt der Datenbank hinzugefuegten Thumbnails
     *
     * @var string
     */
    protected $lastThumbHash;

    /**
     * Init-Konfiguration des Objekts
     *
     * @param Size $size Setzt das Attribut `$size` der Klasse.
     */
    public function __construct(Size $size = null)
    {
        parent::__construct();
        if (isset($size)) {
            $this->size = $size;
        } else {
            $this->size = new Size();
        }
        require_once(INC.'/database_rb.php');
    }

    /**
     * Gibt den Pfad zum Thumbnail fuer das angegebene Bild in der
     * definierten groesse zurueck.
     * Die Methode durchsucht solange alle Thumbnailverzeichnisse, bis
     * ein Ordner mit passendem Namen und einem Thumbnail des gewuenschten
     * Fotos gefunden wurde.
     * Ist die Groesse des Thumbnails unbegrenzt, also alle Werte sind
     * `0`, dann gibt die Funktion den Pfad zum Orgiginal-Bild zurueck.
     * Der Hash-Wert muss unbedingt als String angegeben werden,
     * da dieser eine valide Hex-Codierte Zahl darstellt und somit von PHP als Integer
     * interpretiert wuerde. => Fuer die Funktion ist der angegebene Wert eine ID.
     * Die ID ist dementsprechend als Integer zu casten, falls dise als String vorliegt oder
     * der Datentyp nicht genau definiert ist.
     *
     * @param mixed $picture Pfad zum Original-Bild oder ID des Bildes oder die
     *                       ersten Zeichen des Hash-Wertes
     * @param bool $noPromise Gibt an ob die Funktion als Pfad eine Promise-URI zurückgeben darf
     *
     * @internal param \htlwy\thumbs\Size $size Temporaer zu verwendendes `Size`-Objekt
     * @return string Pfad zum Thumbnail; kann direkt in `<img>` Element
     *        eingesetzt werden.
     */
    public function get($picture, $noPromise = false)
    {
        if (!isset($size)) {
            $size = $this->size;
        }
        if (is_int($picture)) {
            $pic = \R::load('pictures', $picture);
            if ($pic->id == 0) {
                if (VERBOSE) {
                    new Logger('Das Bild mit der ID "'.$picture.'" konnte nicht gefunden werden.');
                }
                return false;
            }
        } elseif (preg_match('#^[0-9a-e]+$#i', $picture) == 1) {
            $pic = \R::findOne('pictures', 'hash LIKE :picid', array(':picid' => $picture.'%'));
            if ($pic->id == 0) {
                if (VERBOSE) {
                    new Logger('Das Bild mit dem Hash "'.$picture.'" konnte nicht gefunden werden.');
                }
                return false;
            }
        }

        if (isset($pic)) {
            if ($size->allZero()) {
                return $pic->path;
            }
            $thumbnail = \R::getCell(
                "SELECT t.path FROM pictures p JOIN thumbnails t ON p.id = t.pictures_id
                WHERE p.id = :id AND (t.width = :width OR t.height = :height OR (p.width < :width AND t.width = p.width))
                LIMIT 1",
                array(':id' => $pic->id, ':width' => $size->getWidth(), ':height' => $size->getHeight())
            );
            $picture = DOC_ROOT.$pic->path;
        } else {
            if (!$this->pictureExists($picture)) {
                return false;
            }
            if ($size->allZero()) {
                return Files::rmDocRoot($picture);
            }

            // zuerst DB durchsuchen
            $thumbnail = \R::getCell(
                "SELECT t.path FROM pictures p JOIN thumbnails t ON p.id = t.pictures_id
                WHERE p.path = :path AND (t.width = :width OR t.height = :height OR (p.width < :width AND t.width = p.width))
                LIMIT 1",
                array(':path' => Files::rmDocRoot($picture), ':width' => $size->getWidth(), ':height' => $size->getHeight())
            );
        }
        if(isset($thumbnail) && $thumbnail!==false){
            return $thumbnail;
        }

        $fileinfo = pathinfo($picture);
        foreach (glob($fileinfo['dirname'].'/'.self::targetPrefix.'*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            if (is_dir($dir) && preg_match(self::$thumb_regex, basename($dir), $data) == 1) {
                $width = $data[2];
                $height = $data[3];

                if ($size->getWidth() == $width || $size->getHeight() == $height) {
                    $tmpPicture = $dir.'/'.$fileinfo['filename'].'.jpg';
                    if (file_exists($tmpPicture)) {
                        return Files::rmDocRoot($tmpPicture);
                    }
                }
            }
        }

        // es konnte kein passendes Thumbnail gefunden werden
        if (VERBOSE) {
            new Logger('Konnte kein passendes Thumbnail fuer '.$picture.' finden, generiere neues Thumbnail.');
        }

        if ($noPromise) {
            $this->create($picture, $thumbnail);
        } else {
            return $this->getPromise($picture);
        }

        return Files::rmDocRoot($thumbnail);
    }

    private function fromHashIDSub($bean)
    {
        $size = $this->size;
        foreach ($bean->ownThumbnails as $thumb) {
            if ($this->getSub1($size->getWidth(), $thumb->width) ||
                $this->getSub1($size->getHeight(), $thumb->height)
            ) {
                return $thumb->path;
            }
        }
        return null;
    }

    private function getSub1($a, $b)
    {
        if ($a == 0) {
            return true;
        } elseif ($a == $b) {
            return true;
        }
        return false;
    }

    /**
     * Erzeugt eine Promise-URI für das gegeben Bild
     * @param $picture
     * @return string
     */
    protected function getPromise($picture)
    {
        require_once INC.'/database2.php';

        $ret = self::$promise_base.'?id=';
        try {
            $dbh = pdo_connect();
            $dbh->beginTransaction();

            $stmt = $dbh->prepare(
                "SELECT id FROM thumbnail_promise WHERE path = :path AND width = :width AND height = :height"
            );
            $param = array(
                ':path' => $picture,
                ':width' => $this->size->getWidth(),
                ':height' => $this->size->getHeight()
            );
            $stmt->execute($param);
            list($id) = $stmt->fetch(\PDO::FETCH_NUM);

            if (empty($id)) {
                $stmt = $dbh->prepare(
                    "INSERT INTO thumbnail_promise (path, width, height) VALUES (:path, :width, :height) "
                );
                if ($stmt->execute($param)) {
                    $ret .= $dbh->lastInsertId();
                } else {
                    throw new \Exception('Fehler beim Promise erstellen.');
                }
            } else {
                $ret .= $id;
            }
            $dbh->commit();
            return $ret;
        } catch (\Exception $e) {
            if (isset($dbh)) {
                $dbh->rollBack();
            }
            new Logger($e);
        }
        return false;
    }

    /**
     * Erstellt eine verkleinerte Version des Bildes, dessen Pfad als erster
     * Parameter angegeben wird.
     *
     * @param string $picture Pfad zum Bild von dem ein Vorschaubild erzeugt
     *                            werden soll.
     * @param string $thumbnail Gibt den Pfad zum generierten Thumbnail zurueck.
     *
     * @return bool Gibt `false` zurueck, wenn der Vorgang fehlgeschlagen,
     *        ist sonst `true`.
     */
    public function create($picture, & $thumbnail = null)
    {
        if (!$this->pictureExists($picture) && !is_file($picture)) {
            return false;
        }

        list($tmp['width'], $tmp['height']) = getimagesize($picture);
        $resize = $this->calcImageSize(new Size($tmp['width'], $tmp['height']), $this->size);
        $thumbnail = $this->calcTarget($picture, $resize);
        if (file_exists($thumbnail)) {
            if (VERBOSE) {
                new Logger('Konnte kein Thumbnail erzeugen da '.$thumbnail.' bereits existiert.');
            }
            return true;
        }

        if (!$this->resize($picture, $this->size, $thumbnail)) {
            return false;
        }

        $this->addThumbnailToDB($thumbnail, $picture);

        return true;
    }

    /**
     * Berechnet den Pfad zum Thumbnail. Ist dieser nicht vorhanden
     * so wird er angelegt.
     *
     * @throws \BadFunctionCallException Der 2. Parameter wurde nicht gesetzt,
     * obwohl er benoetigt wird.
     *
     * @param string $picture Pfad zum Original-Bild
     * @param Resize $size Zielgroesse des Bildes; wird nur benoetigt,
     *                        wenn kein `$this->target` angegeben ist.
     *
     * @return string Pfad zum Vorschaubild inkl. Dateiname
     */
    protected function calcTarget($picture, Resize $size)
    {
        $orig = pathinfo($picture);

        $return = $orig['dirname'];
        $return .= '/'.self::targetPrefix.$size->getNewWidth().'x'.$size->getNewHeight();
        $return .= '/'.$orig['filename'].'.jpg';

        // Verzeichnis rekursiv erzeugen, wenn nicht vorhanden
        if (!file_exists(dirname($return))) {
            mkdir(dirname($return), 0755, true);
        }

        return $return;
    }

    /**
     * Speichert das erzeugte Thumbnail und seine Metadaten in der Datenbank
     *
     * @param string $picture Vollstaendiger Pfad zum Foto
     * @param string $parent Pfad vom urspruenglichen Foto
     *
     * @return int ID die dem Thumbnail zugewiesen wurde
     */
    protected function addThumbnailToDB($picture, $parent)
    {
        $pic = \R::findOne('pictures', 'path = ?', array(Files::rmDocRoot($parent)));
        $thumb = \R::dispense('thumbnails');
        if (!isset($pic)) {
            $pic = \R::load('pictures', $this->addPictureToDB($parent));
        }
        $pic->ownThumbnailsList[] = $thumb;

        $thumb->path = Files::rmDocRoot($picture);
        $thumb->hash = hash_file('ripemd128', $picture);
        list($tmp_width, $tmp_height) = getimagesize($picture);
        $thumb->width = $tmp_width;
        $thumb->height = $tmp_height;

        \R::store($pic);
        $this->lastThumbID = \R::store($thumb);
        $this->lastThumbHash = $thumb->hash;
        return $this->lastThumbID;
    }

    /**
     * Speichert das Original-Bild und seine Metadaten in der Datenbank.
     *
     * @param string $picture Vollstaendiger Pfad zum Foto
     *
     * @return int ID die dem Bild zugewiesen wurde
     */
    protected function addPictureToDB($picture)
    {
        $pic = \R::dispense('pictures');

        $pic->path = Files::rmDocRoot($picture);
        $pic->hash = hash_file('ripemd128', $picture);
        list($tmp_width, $tmp_height) = getimagesize($picture);
        $pic->width = $tmp_width;
        $pic->height = $tmp_height;

        return \R::store($pic);
    }

    /**
     * ID des zuletzt der Datenbank hinzugefuegtem Thumbnail.
     *
     * @return int
     */
    public function lastThumbID()
    {
        return $this->lastThumbID;
    }

    /**
     * Hash des Zulezt in die Datenbank eingefuegten Thumbnails
     *
     * @return string
     */
    public function LastThumbHash()
    {
        return $this->lastThumbHash;
    }

    /**
     * Legt die JPEG-Qualtiaet (0 bis 100) fuer das Thumbnail fest.
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

    /**
     * Definiert die gewuenschte Groesse
     *
     * @param Size
     *
     * @return int Aktuell gesezter Wert.
     */
    public function Size(Size $size = null)
    {
        if (isset($size)) {
            $this->size = $size;
        }
        return $this->size;
    }

}

// generiert regex
//Thumbnail::$thumb_regex = '#^'.preg_quote(Thumbnail::targetPrefix).
//    '(([0-9]+)x([0-9]+)|([0-9]+)\-([0-9]+)x([0-9]+)\-([0-9]+))$#';
Thumbnail::$thumb_regex = '#^'.preg_quote(Thumbnail::targetPrefix).'(([0-9]+)x([0-9]+))$#';

Thumbnail::$promise_base = HP_HEADER.'/thumbnail_promise.php';