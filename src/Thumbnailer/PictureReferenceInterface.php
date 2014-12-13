<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Interface PictureReferenceInterface
 * @package Thumbnailer
 */
interface PictureReferenceInterface extends LinkableInterface
{

    /**
     * Full path on the local or mounted filesystem to the picture or `null` if the file is not jet available or is
     * served from another source, like a database.
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Every picture is identified by its sha1 hash value.
     *
     * @return string a 40 character hex number (sha1) hash
     */
    public function getID();

}
