<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Interface FilePictureInterface
 * @package Thumbnailer
 *
 * For all pictures that are available through default file io operations.
 */
interface FilePictureInterface extends PictureInterface
{
    /**
     * Full path on the local or mounted filesystem to the picture or `null` if the file is not jet available or is
     * served from another source, like a database.
     *
     * @return string|null
     */
    public function getPath();
}
