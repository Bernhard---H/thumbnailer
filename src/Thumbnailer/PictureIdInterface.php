<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Interface PictureReferenceInterface
 * @package Thumbnailer
 */
interface PictureIdInterface
{

    /**
     * Every picture is identified by its sha1 hash value.
     *
     * @return string a 40 character hex number (sha1) hash
     */
    public function getID();

}
