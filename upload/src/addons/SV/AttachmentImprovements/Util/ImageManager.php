<?php

namespace SV\AttachmentImprovements\Util;

use XF\Image\Manager;

abstract class ImageManager extends Manager
{
    public static function getMaxResizePixels(Manager $imageManager): ?int
    {
        return $imageManager->maxResizePixels;
    }
}