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
     * Full path to the picture.
     *
     * If `isEditable` returns `true` the returned path must point to an existing file. On `false` the method may
     * return null or point to a none existing file.
     *
     * The first returned `string` value may not be changed throughout the objects lifetime.
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Indicates if the picture file is ready for editing => the file must be writable
     * @return bool
     */
    public function isEditable();

    /**
     * Changes the state of the picture to be editable.
     * @return string Path to the picture
     * @throws PictureNotEditableException
     */
    public function edit();

    /**
     * Sends the picture file to standard out, in other words to a browser
     * @return void
     */
    public function echoPicture();
}
