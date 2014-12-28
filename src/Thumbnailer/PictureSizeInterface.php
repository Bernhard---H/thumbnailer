<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Interface SizeInterface
 * @package Thumbnailer
 *
 * Defines the (intended) height and width of a picture
 */
interface PictureSizeInterface
{
    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return int
     */
    public function getHeight();
}
