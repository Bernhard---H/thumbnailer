<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Interface ThumbPictureInterface
 * @package Thumbnailer
 *
 *
 */
interface ThumbPictureInterface extends PictureInterface
{

    /**
     * Get the original (parent) picture
     * @return PictureInterface
     */
    public function getParentPicture();
}
