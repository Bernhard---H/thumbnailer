<?php
/**
 * Thumbnailer
 */

namespace Thumbnailer;

/**
 * Class ResizeType
 * @package Thumbnailer
 */
final class ResizeRules
{
    private $value;

    /**
     * Do not change the aspect ratio, ignore the provided values if necessary;
     * Unless specifically requested never create a bigger picture than the original source picture.
     */
    const KEEP_ASPECT_RATIO = 1;

    /**
     * If used as only parameter: Use the exact parameters the user entered to create the new picture.
     * If in conjunction with other types: Try to get as close as possible to the users values without violation
     * other rules
     */
    const MAX_FIT = 2;

    /**
     * Default value is `KEEP_ASPECT_RATIO` and `MAX_FIT`; to combine multiple settings add a single pipe character `|`
     * between them.
     * @param int $value
     */
    public function __construct($value = 3)
    {
        if ($value <= 0 || 3 < $value) {
            throw new \InvalidArgumentException("Provided value '".$value."' is not in the range of ResizeType.");
        }
        $this->value = $value;
    }

    /**
     * Compares two objects of ResizeType. Returns `true` if the provided object is a subset of $this.
     * @param self $subset
     * @return bool
     */
    public function isSubset(self $subset)
    {
        return $subset->value & $this->value == $subset->value;
    }

    /**
     * Just returns the raw value of the object
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}


