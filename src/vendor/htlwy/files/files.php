<?php

namespace htlwy\files;

/**
 * Enthaelt nuetzliche Funktionen fuer Datei-Handling
 *
 * @package htlwy\files
 */
class Files
{
    /**
     * Entfernt alle Sonderzeichen aus einem String. Diese Funktion ruft
     * intern `rm_umlaute` und entfernt anschliessen alle verbleibenden
     * Sonderzeichen.
     * Der zurueckgegebene String besteht aus folgedem Zeichensatz (ohne
     * Leerzeichen):
     * A-Z a-z 0-9 . - _
     * Achtung: Unter Windows kann es zu Problemen kommen. Ueber die
     * Superglobale $_GET werden keine Sonderzeichen transportiert.
     *
     * @param      string String aus dem die Sonderzeichen entfernt werden sollen.
     * @param bool $path Ist dieser Parameter `true`, so sind zusaetzlich noch
     *                    Slash und Backslash zulaessig.
     *
     * @return string
     */
    public static function rm_sonderzeichen($string, $path = false)
    {
        $string = self::rm_umlaute($string);

        if ($path) {
            $string = preg_replace('/[^A-Za-z0-9_\.\-\/\\]+/', '', $string);
        } else {
            $string = preg_replace('/[^A-Za-z0-9_\.\-]+/', '', $string);
        }
        return $string;
    }

    /**
     * Entfernt die Umlaute, das Scharfe-S und Leerzeichen aus dem angegebenen
     * String und ersetzt diese.
     * Achtung: Unter Windows kann es zu Problemen kommen. Ueber die
     * Superglobale $_GET werden keine Sonderzeichen transportiert.
     *
     * @param string
     *
     * @return string
     */
    public static function rm_umlaute($string)
    {
        $string = str_ireplace('ö', 'oe', $string);
        $string = str_ireplace('ä', 'ae', $string);
        $string = str_ireplace('ü', 'ue', $string);
        $string = str_ireplace('Ö', 'OE', $string);
        $string = str_ireplace('Ä', 'AE', $string);
        $string = str_ireplace('Ü', 'UE', $string);
        $string = str_ireplace('ß', 'ss', $string);
        $string = str_ireplace(' ', '-', $string);

        return $string;
    }

    /**
     * Verschiebt die angegebene Datei oder Ordner in einen Papierkorb im TEMP Verzeichnis
     * antstatt diese zu löschen. Die Datei wird nach 5 Tagen automatisch aus dem Papierkorb
     * entfernt.
     *
     * Dateien aus dem TEMP Ordner werden direkt gelöscht.
     *
     * @param string $path
     * @param bool $contentOnly Leeren des angegebenen Ordners
     *
     * @return bool
     */
    public static function dispose($path, $contentOnly = false)
    {
        if (stristr(str_replace('\\', '/', $path), TEMP) === false) {
            $new = TEMP.'/.dispose/'.basename($path);
            return self::rename($path, $new, false, $contentOnly);
        } else {
            return self::rmall($path, $contentOnly);
        }
    }

    /**
     * Benennt Ordner und Dateien um
     *
     * @param string $old Quellverzeichnis oder Quelldatei
     * @param string $new
     * @param bool $overwrite
     * @param bool $contentOnly Nur der Inhalt des Ordners wird in den neuen Ordner verschoben. Wird bei
     *                            Dateien ignoriert.
     *
     * @return bool
     */
    public static function rename($old, $new, $overwrite = true, $contentOnly = false)
    {
        if (is_dir($old) && $contentOnly) {
            $err = false;
            foreach (scandir($old) as $content) {
                if ($content == '.' || $content == '..') {
                } elseif (!self::rename($old.'/'.$content, $new.'/'.$content, $overwrite)) {
                    $err = true;
                }
            }
            return !$err;
        } else {
            if (!file_exists(dirname($new))) {
                mkdir(dirname($new), 0755, true);
            }
            if (!$overwrite) {
                $new = self::noOverwrite($new);
            }
            return rename($old, $new);
        }
    }

    /**
     * Verändert den gegebenen Pfad so, dass die Datei oder das Verzeichnis nichr überschrieben wird.
     *
     * @param string $path
     *
     * @return string
     */
    public static function noOverwrite($path)
    {
        if (file_exists($path)) {
            $i = 1;
            $file = pathinfo($path);
            do {
                $path = $file['dirname'].'/';
                if (is_dir($path) && substr($file['basename'], 0, 1) == '.') {
                    // fuer versteckte verzeichnisse
                    $path .= $file['basename'].'-'.$i;
                } else {
                    $path .= $file['filename'].'-'.$i;
                    if (isset($file['extension'])) {
                        $path .= '.'.$file['extension'];
                    }
                }
                $i++;
            } while (file_exists($path));
        }
        return $path;
    }

    /**
     * Entfernt rekursiv alle Dateien und Verzeichnisse im angegebenen Pfad
     *
     * @param string $path Pfad der entfernt werden soll
     * @param bool $contentOnly Der angegebene Ordner soll nicht entfernt werden, nur
     *                            dessen Inhalt.
     *
     * @return bool Gibt `false` im Fehlerfall zurueck
     */
    public static function rmall($path, $contentOnly = false)
    {
        if (!file_exists($path)) {
            return false;
        }

        if (is_dir($path)) {
            foreach (scandir($path) as $value) {
                if ($value != '.' && $value != '..') {
                    if (!self::rmall($path.'/'.$value)) {
                        return false;
                    }
                }
            }
            if (!$contentOnly) {
                if (!rmdir($path)) {
                    return false;
                }
            }
        } else {
            if (!unlink($path)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verwandelt die Integer Dateigroesse in eine lesbare Form.
     *
     * @param int $bytes Dateigroesse
     *
     * @return string Formatierte Dateigroesse
     */
    public static function natsize($bytes)
    {
        $suffix = array('Byte', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');

        $index = 0;
        $prefix = $bytes;

        while ($prefix >= 1024 * 8) {
            $prefix /= 1024;
            $index++;
        }

        if (isset($suffix[$index])) {
            return number_format($prefix, 0, ',', '.').'&nbsp;'.$suffix[$index];
        } else {
            return number_format($bytes, 0, ',', '.').'&nbsp;Byte';
        }
    }

    /**
     * Convertiert Textdateien von CrLf zu Lf; ausgenommen TXT-Dateien
     *
     * @param string $target Pfad zur Textdatei
     */
    public static function crlf_to_lf($target)
    {
        // txt ... no Lf for txt-files!
        // md ... README Dateien
        $textfiles = array('css', 'htm', 'html', 'js', 'md', 'php', 'xml');

        $fileext = strtolower(pathinfo($target, PATHINFO_EXTENSION));

        if (in_array($fileext, $textfiles)) {
            $file_content = file_get_contents($target);
            $file_content = str_ireplace(chr(13).chr(10), chr(10), $file_content);
            file_put_contents($target, $file_content);
        }
    }

    /**
     * Sendet die angegebene Datei an den Browser und beendet bei erfolg die
     * Script-Ausfuehrung.
     *
     * @param string $path Voll qualifizierter Pfad zur Datei die heruntergeladen werden soll.
     *
     * @return bool Gibt im Fehlerfall false zurueck.
     */
    public static function download($path)
    {
        if (file_exists($path)) {
            if (is_file($path)) {
                header('Content-Type: application');
                header('Content-Disposition: attachment; filename="'.basename($path).'"');
                header('Connection: close');
                header('Content-Length: '.self::dirsize($path));
                readfile($path);
                exit;
            } else {
                $zip = new \ZipArchive();
                $file = TEMP.'/htlwww_'.substr(sha1(date('c')), 0, 10).'.zip';
                $zip->open($file, \ZipArchive::OVERWRITE);

                $error = false;
                if (!self::zipAddDir($zip, $path)) {
                    $error = true;
                }
                $zip->close();

                if (!$error) {
                    header('Location: '.self::rmDocRoot($file));
                    echo $file;
                    exit;
                }
            }
        }
        return false;
    }

    /**
     * Gibt die Dateigroesse der angegebenen Datei bzw. die Summe aller Dateigroessen im
     * angegebenen Verzeichnis zurueck.
     *
     * @param string $dir Pfad, dessen Groesse berechnet werden soll
     *
     * @return int
     */
    public static function dirsize($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            if (substr($dir, -2) != '/.' && substr($dir, -3) != '/..') {
                foreach (scandir($dir) as $value) {
                    if ($value != '..' && $value != '.') {
                        $size += self::dirsize($dir.'/'.$value);
                    }
                }
            }
        } else {
            $size += filesize($dir);
        }
        return $size;
    }

    /**
     * adds the content of a directory to an object of the ZipArchive class
     *
     * @param \ZipArchive $zip
     * @param string $dir path to the content that should be added
     * @param null $base not needed ... used to work recursively
     *
     * @return bool
     */
    public static function zipAddDir(&$zip, $dir, $base = null)
    {
        $error = false;
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        if (!isset($base)) {
            $base = dirname($dir).'/';
        }
        if (!$zip->addEmptyDir(str_ireplace($base, '', $dir))) {
            $error = true;
        }
        foreach (scandir($dir) as $value) {
            if (is_dir($dir.$value)) {
                if ($value != '.' && $value != '..') {
                    if (!self::zipAddDir($zip, $dir.$value, $base)) {
                        $error = true;
                        break;
                    }
                }
            } else {
                $zw = str_ireplace($base, '', $dir.$value);
                if (!$zip->addFile($dir.$value, $zw)) {
                    $error = true;
                    break;
                }
            }
        }
        return !$error;
    }

    /**
     * Entfernt aus dem gegebenen Pfad den Anteil zum Document Root Verzeichnis des Webservers,
     * sodass dieser direkt im HTML verwendet werden kann.
     *
     * @param string $path absolut angegebener Pfad
     *
     * @return string
     */
    public static function rmDocRoot($path)
    {
        $path = str_replace('\\', '/', $path);
        return preg_replace('#^'.preg_quote(DOC_ROOT).'#', '', $path, 1);
    }

    /**
     * Prueft ob beim Upload ein Fehler aufgetreten ist.
     *
     * @return bool|int Gibt `false` bei fehlerfreiem Upload zurueck,
     * ist ein Fehler aufgetreten wird die FehlerID zurueckgegeben.
     */
    public static function uploadError()
    {
        foreach ($_FILES as $form) {
            foreach ($form['error'] as $errorID) {
                $errorMsg = self::uploadErrorToString($errorID);
                if ($errorMsg != '') {
                    if (VERBOSE) {
                        new \htlwy\Logger($errorMsg);
                    }
                    return $errorID;
                }
            }
        }
        return false;
    }

    /**
     * Verwandelt den Fehlercode in $_FILES zu einem passenden string.
     *
     * @param int $id $_FILES Fehlercode
     *
     * @return string Passende Fehlermeldung oder Leerstring, wenn alles OK ist.
     */
    public static function uploadErrorToString($id)
    {
        switch ($id) {
            case UPLOAD_ERR_OK:
                return '';
            case UPLOAD_ERR_INI_SIZE:
                return 'Die Datei hat das Limit fuer die Dateigroesse aus der php.ini ueberschritten.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Die Datei hat das Limit fuer die Dateigroesse '.
                'aus dem HTML-Element MAX_FILE_SIZE ueberschritten.';
            case UPLOAD_ERR_PARTIAL:
                return 'Die Datei konnte nicht vollstaendig hogeladen werden.';
            case UPLOAD_ERR_NO_FILE:
                return 'Es wurde keine Datei hochgeladen.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Es wurde kein Verzeichnis zum hoch';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Die Datei konnte nicht hochgeladen werden, da schreibrechte fehlen.';
            case UPLOAD_ERR_EXTENSION:
                return 'Eine PHP-Erweiterung hat den Upload abgebrochen.';
            default:
                return 'Beim Upload ist ein unbekannter Fehler aufgetreten: '.$id;
        }
    }
}


