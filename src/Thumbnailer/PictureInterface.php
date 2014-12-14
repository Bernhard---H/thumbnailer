<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;


interface PictureInterface extends LinkableInterface, PictureIdInterface, ResizeableInterface
{

    /**
     * @return PictureSizeInterface
     */
    public function getSize();

    /**
     * Sends the picture file to standard out, in other words to a browser
     * @return void
     */
    public function echoPicture();
}