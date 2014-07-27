<?php

namespace htlwy\album;


/**
 * Diese Klasse stellt Methoden zum einfachen Einbetten von Bildern
 * zur Verfuegung
 */
class Embed extends Thumbnail
{
    /**
     * Definiert die maximale Groesse des verlinkten Fotos.
     * Default ist 2000x1500 Pixel
     * @var Size
     */
    public $linkSize = null;

    /**
     * Gibt den Faktor an, um den ein Bild maximal vergroesserr werden darf.
     * @var int
     */
    public $maxRatio = 1.35;

    /**
     * Minimal unterstuetzte Browser-Breite
     * @var int in Pixel
     */
    public $minBrowserWidth = 240;
    /**
     * Maximal unterstuetzte Browser-Breite
     * @var int in Pixel
     */
    public $maxBrowserWidth = 1900;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->linkSize = new Size(array(0, 2000), array(0, 1500));
    }

    /**
     * Erstellt aus den angegebenen Parametern einen HTML-String
     *
     * @param string $picture Pfad zum Bild
     * @param string $alt Alternativ-Text
     * @param string $width Anteil des Bildes an der gesamten Fenster-Breite
     *               in Prozent oder Pixel.
     * @param string $title Titel des Bildes
     * @return string
     */
    public function embed($picture, $alt, $width, $title = '')
    {
        if(preg_match('#([0-9]+)\s*(%|px)#', $width, $data) != 1)
        {
            if(VERBOSE)
            {
                hp_log(new \Exception('Die Groesse konnte nicht korrekt '.
                    'aus "'.$width.'" extrahiert werden.'));
            }
            return false;
        }
        //echo '<pre>';
        //print_r($data);
        //echo '</pre>';
        //		Array
        //		(
        //		    [0] => 610px
        //		    [1] => 610
        //		    [2] => px
        //		)

        $base_width = $data[1];
        $width_unit = $data[2];

        $ret = '';
        $this->Size($this->linkSize);

        $ret .= '
<div class="picture_container" style="width: '.$data[0].';">
    <a class="picture_link"
		href="'.$this->get($picture).'"
		target="_blank"
		title="'.nl2br($title).'"
	>';
        $ret .= '
		<span class="picture"
			data-picture
			data-alt="'.$alt.'"
			data-title="'.$title.'"
		>';
        if($width_unit == '%')
        {
            $width_factor = $base_width / 100;
            $width_from   = $this->minBrowserWidth * $width_factor / $this->maxRatio;
            $width_to     = $this->minBrowserWidth * $width_factor;
            do
            {
                $this->Size(new Size(array($width_from, $width_to)));
                $ret .= '
			<span class="picture_src"
				data-src="'.$this->get($picture).'"
				data-media="(min-width: '.floor($width_to / $width_factor).'px)"
			>
			</span>';

                $width_from = $width_to;
                $width_to *= $this->maxRatio;
            }
            while($width_from < $this->maxBrowserWidth * $width_factor);
        }
        else
        {
            $this->Size(new Size(array($base_width / $this->maxRatio, $base_width)));
            $ret .= '
			<span class="picture_src" data-src="'.$this->get($picture).'"></span>';
        }
        $ret .= '
		</span>';

        $this->Size(new Size(null, array(0, 200)));
        $ret .= '
		<!--img src="'.$this->get($picture).'" alt="'.$alt.'" title="'.$title.'"/-->';

        $this->Size(new Size(null, array(0, 400)));
        $ret .= '
		<noscript>
			<img src="'.$this->get($picture).'" alt="'.$alt.'" title="'.$title.'" />
		</noscript>
	</a>
</div>';

        return $ret;
    }

    /**
     * Gibt einen String mit JS- und CSS-Includes zurueck, der im
     * HTML-Head ergaenzt werden sollte.
     *
     * @return string HTML-String mit den Abhaengigkeiten.
     */
    public static function includes()
    {
        return '
		<script src="'.JS.'/lib/picturefill.min.js.gz" async></script>';
    }
}