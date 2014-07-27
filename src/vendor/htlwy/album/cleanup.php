<?php

namespace htlwy\album;

use htlwy\files\Files;
use htlwy\Logger;

/**
 * Entfernt Thumbnails, die Veraltet sind oder deren Original nicht mehr existiert. Stellt
 * sicher, dass alle Thumbnails indiziert sind.
 */
class Cleanup extends Thumbnail
{

    /**
     * startet den Vorgang fuer das gesamte Document Root Directory
     */
    public function __construct()
    {
        parent::__construct();
        $this->search_dir(DOC_ROOT);
    }

    /**
     * Rekursive Subfunktion zu `clean()`
     *
     * @param string $dir aktuell zu bearbeitender Pfad
     *
     * @return void
     */
    protected function search_dir($dir)
    {
        foreach(scandir($dir) as $subDir)
        {
            if(is_dir($dir.'/'.$subDir))
            {
                if(preg_match(self::$thumb_regex, $subDir) == 1)
                {
                    $this->thumbdir_cleanup($dir.'/'.$subDir);
                }
                elseif($subDir != '.' && $subDir != '..')
                {
                    $this->search_dir($dir.'/'.$subDir);
                }
            }
        }
    }

    /**
     * Wird fuer jedes gueltige thumbnail-Verzeichnis aufgerufen.
     *
     * @param string $dir aktuell zu bearbeitender Pfad
     *
     * @return void
     */
    protected function thumbdir_cleanup($dir)
    {
        $dir_header = Files::rmDocRoot($dir);
        foreach(scandir($dir) as $thumb)
        {
            if(!is_dir($dir.'/'.$thumb))
            {
                // alle nicht .jpg-Dateien entfernen
                if(preg_match('/\.jpg$/', $thumb) === 0)
                {
                    hp_log('Entferne nicht erlaubte Datei aus Thumb-Verzeichnis: '.$dir_header.'/'.$thumb);
                    Files::dispose($dir.'/'.$thumb);
                }
                else
                {
                    $this->inspect_thumb($dir.'/'.$thumb);
                }
            }
            // alle Verzeichnisse im Thumbnail-Ordner entfernen
            elseif($thumb != '.' && $thumb != '..')
            {
                hp_log('Entferne nicht erlaubten Ordner aus Thumbnail-Verzeichnis: '.$dir_header.'/'.$thumb);
                Files::dispose($dir.'/'.$thumb);
            }
        }
        if(count(scandir($dir)) == 2)
        {
            hp_log('Entferne leeres Thumbnail-Verzeichnis: '.$dir_header);
            Files::dispose($dir);
        }
    }

    /**
     * Untersucht Thumbnail auf verschiedene Eigenschaften z.B. wie alt das Bild ist,
     * ob das Original noch vorhanden ist oder ein Eintrag in der DB existiert.
     *
     * @param $picture
     *
     * @internal param $path
     */
    protected function inspect_thumb($picture)
    {
        $picture_header = Files::rmDocRoot($picture);
        $stat           = stat($picture);

        // ist Datei juenger als 90 Tage oder in den letzten 90 Tagen verwendet worde?
        if($stat['mtime'] > time() - 3600 * 24 * 90 || $stat['atime'] > time() - 3600 * 24 * 90)
        {
            // thumbnail in der Datenbank vorhanden?
            if(\R::findOne('thumbnails', 'path = ?', array($picture_header)) == null)
            {
                $parent = dirname(dirname($picture)).'/'.basename($picture);
                if($this->pictureExists($parent))
                {
                    // Original-Bild kann gefunden werden
                    // => in die DB eintragen
                    $this->addThumbnailToDB($picture, $parent);
                }
                else
                {
                    new Logger('Entferne Thumbnail-Orphan: '.$picture_header);
                   Files::dispose($picture);
                }
            }
        }
        else
        {
            // Thumbnail hat Lebensdauer ueberschritten
            new Logger('Entferne veraltetes Thumbnail: '.$picture_header);
            \R::trashAll(\R::find('thumbnails', 'path = ?', array($picture_header)));
            Files::dispose($picture);
        }
    }

}