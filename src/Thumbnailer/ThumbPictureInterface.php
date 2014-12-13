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
interface ThumbPictureInterface extends LinkableInterface
{

    /**
     * Get the original (parent) picture
     * @return PictureReferenceInterface
     */
    public function getParentPicture();

}