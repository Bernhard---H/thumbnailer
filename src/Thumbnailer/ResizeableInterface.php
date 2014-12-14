<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Interface ResizeableInterface
 * @package Thumbnailer
 *
 * Object provides a method to create a new version of itself in a different size
 */
interface ResizeableInterface
{
    /**
     * Creates a picture object with a different size.
     *
     * Implementations of this method must support only `ResizeRules::KeepAspectRatio` and `ResizeRules::MaxFit` as
     * individual rules and used together. Other rules must be ignored.
     * If a rules object is provided but but does not set any of these rules, an `\InvalidArgumentException` must be
     * thrown.
     *
     * Implementations should not create a new picture-file on a filesystem (or wherever a picture may be stored) but
     * rather provide only the necessary data to create the file if requested by a user.
     *
     * Returned objects should not be used again as `resize` source but rather call the method on the parent picture
     *
     * @param PictureSizeInterface $size
     * @param ResizeRules $rules
     * @return ThumbPictureInterface
     * @throws \InvalidArgumentException
     */
    public function resize(PictureSizeInterface $size, ResizeRules $rules);
}