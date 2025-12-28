<?php

namespace SV\AttachmentImprovements\Util;

abstract class ImageManager extends \XF\Image\Manager
{
    public static function getMaxResizePixels(\XF\Image\Manager $imageManager): ?int
    {
        return $imageManager->maxResizePixels;
    }
}