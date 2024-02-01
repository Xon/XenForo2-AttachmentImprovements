<?php

namespace SV\AttachmentImprovements;

use XF\Http\Response;

class SvgResponse extends Response
{
    /** @noinspection PhpUnusedParameterInspection */
    public static function updateInlineImageTypes(Response $response, string $key, string $value): void
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