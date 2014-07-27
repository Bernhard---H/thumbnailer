<?php

namespace htlwy\album;

use htlwy\files\Files;
use htlwy\Logger;

/**
 * Erweitert die Klasse `Thumbnail`. Ist fuer die Verwatlung von Bilder
 * zustaendig.
 */
class Upload extends Thumbnail
{
    /**
     * Default Upload Direktory
     */
    const picDir = CDN_ROOT;
    /**
     * Name der Anwendung, fuer die Grafiken verwaltet werden sollen.
     * Bestimmt z.B. das Zielverzeichnis beim Upload.
     *
     * @var string
     */
    protected $app = '';
    /**
     * Unterteilt den Speicherbereich der Anwendung optional weiter in Kategorien.
     * @var string
     */
    protected $category = '';

    /**
     * @param string $app Name der Anwendung fuer die Bilder verwaltet werden
     *                    sollen.
     */
    public function __construct($app = null)
    {
        parent::__construct();
        $this->app($app);
    }

    /**
     * Definiert den Namen der Anwendung, fuer die Fotos werwaltet werden
     * sollen.
     *
     * @param string $app neuer Name der Anwendung
     *
     * @return string aktuell gesetzter Name
     */
    public function app($app = null)
    {
        if (isset($app)) {
            $this->app = Files::rm_sonderzeichen($app);
        }
        return $this->app;
    }

    /**
     * Unterteilt die Anwendung in Kategorien
     * z.B. Album
     *
     * @param string $category
     * @return string
     */
    public function category($category = null)
    {
        if (isset($category)) {
            $this->category = Files::rm_sonderzeichen($category);
        }
        return $this->category;
    }

    /**
     * Verarbeitet hochgeladene Grafiken, sodass sie den vordefinierten
     * Anforderungen entsprechen.
     * Diese Funktion akzeptiert folgende Optionen:
     * * files:
     *        Erwartet eine Untermenge von $_FILES. Ist der Parameter
     *        nicht gesetzt wird automatisch auf $_FILES zurueckgegriffen.
     * * target:
     *        Legt das Zielverzeichnis fest.
     * * newName:
     *        Der neue Name der Datei ohne Dateiendung. Werden mehrere Dateien
     *        hochgeladen oder existiert bereits eine Datei mit diesem Namen
     *        so werden die Dateien automatisch durchnummeriert.
     *
     * @param string $name Gibt den Namen des Input-Elements an.
     * @param array $options Erwartet ein Array mit Optionen, siehe Funktionsbeschreibung.
     * @param array $ids Erhaelt die IDs der hochgeladenen Bilder.
     *
     * @return bool
     */
    public function upload($name, array $options = null, & $ids = null)
    {
        if (isset($_FILES[$name])) {
            $files = $_FILES[$name];
        } else {
            if (VERBOSE) {
                new Logger('Das Formular "'.$name.'" existiert nicht oder es wurden keine Dateien hochgeladen.');
            }
            return false;
        }
        //-- Optionen-Array Verarbeiten ---------------------------------------
        if (isset($options['target'])) {
            if (is_dir($options['target'])) {
                $target = $options['target'];
                if (substr($target, -1) != '/') {
                    $target .= '/';
                }
            } else {
                if (VERBOSE) {
                    new Logger('Die Option "target" definiert keinen Pfad zu einem Ordner');
                }
                return false;
            }
        } else {
            $target = self::picDir.'/';
            if ($this->app == '') {
                $target .= '.temp/';
            } else {
                $target .= ucfirst($this->app).'/';
            }
            $target .= date('Y').'/';

            if ($this->category != '') {
                $target .= $this->category.'/';
            }
        }
        if (isset($options['newName'])) {
            $newName = Files::rm_sonderzeichen($options['newName']);
        }

        //-- Bild verarbeiten -------------------------------------------------
        foreach ($files['name'] as $index => $basename) {
            // pruefen ob beim upload an fehler aufgetreten ist
            $error = Files::uploadErrorToString($files['error'][$index]);
            if ($error != '') {
                if (VERBOSE) {
                    new Logger($error);
                }
                return false;
            }

            if (isset($options['newName']) && isset($newName)) {
                $filename = $newName;
            } else {
                $filename = pathinfo($basename, PATHINFO_FILENAME);
                $filename .= Files::rm_sonderzeichen($filename);
            }

            // vorhandene Dateien nicht ueberschreiben
            $filename = Files::noOverwrite($target.$filename.'.jpg');

            $ids[] = $this->picUploader($files['tmp_name'][$index], $filename);
        }
        return true;
    }

    /**
     * Verschiebt die hochgeladene Datei an den gewünschten Ort.
     *
     * @param string $tempName Temporärer Name aus dem `$_FILE` Array
     * @param string $targetName
     * @return bool|int
     */
    public function picUploader($tempName, $targetName)
    {
        $ext = pathinfo($targetName, PATHINFO_EXTENSION);
        if ($ext != 'jpg' && $ext != 'jpeg') {
            $targetName = substr_replace($targetName, '', -1 * strlen($ext)).'jpg';
        }
        $workName = TEMP.'/'.uniqid().'.'.$ext;

        if (!move_uploaded_file($tempName, $workName)) {
            if (VERBOSE) {
                new Logger('Die hochgeladene Datei '.basename($tempName).' konnte nicht verschoben werden.');
            }
            return false;
        }

        if (!$this->resize($workName, new size(4096, 4096), $targetName)) {
            if (VERBOSE) {
                new Logger('Die Grafik '.basename($tempName).' konnte nicht konvertiert und verkleinert werden.');
            }
            return false;
        }
        unlink($workName);
        return $this->addPictureToDB($targetName);
    }
}