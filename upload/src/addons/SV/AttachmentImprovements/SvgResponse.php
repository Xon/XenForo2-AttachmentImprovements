<?php

namespace SV\AttachmentImprovements;

class SvgResponse extends \XF\Http\Response
{
    /**
     * @param \XF\Http\Response $response
     * @param string            $key
     * @param string            $value
     * @noinspection PhpUnusedParameterInspection
     */
    public static function updateInlineImageTypes(\XF\Http\Response $response, string $key, string $value)
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