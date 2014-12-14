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
     * Recreate the original object from its id
     *
     * @param string $id
     */
    public function __construct($id);

    /**
     * Unique ID for every picture, e.g. a sha1 hash of the file
     *
     * @return string
     */
    public function getId();
}

