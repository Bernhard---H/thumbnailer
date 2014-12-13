<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Interface LinkableInterface
 * @package Thumbnailer
 *
 * Linkable objects always return a valid URI
 */
interface LinkableInterface
{

    /**
     * Returns a valid URI which a browser can use to fetch the object (picture)
     * @return string
     */
    public function toLink();

    /**
     * magic method: is an alias for toLink()
     */
    public function __toString();

    /**
     * Defines the objects HTTP/HTML content-type
     * @return string
     */
    public function getContentType();
}