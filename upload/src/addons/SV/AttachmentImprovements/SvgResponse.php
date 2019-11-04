<?php

namespace SV\AttachmentImprovements;

class SvgResponse extends \XF\Http\Response
{
    public static function updateInlineImageTypes(/** @noinspection PhpUnusedParameterInspection */ \XF\Http\Response $response, $key, $value)
    {
        $container = \XF::app()->container();
        $inlineImageTypes = $container->offsetGet('inlineImageTypes');
        if (!isset($inlineImageTypes[$key]))
        {
            $inlineImageTypes[$key] = $value;
            $container->set('inlineImageTypes', $inlineImageTypes);
        }
    }
}