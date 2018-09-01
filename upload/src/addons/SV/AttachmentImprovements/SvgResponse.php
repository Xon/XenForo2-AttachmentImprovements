<?php

namespace SV\AttachmentImprovements;

class SvgResponse extends \XF\Http\Response
{
    public function updateInlineImageTypes(\XF\Http\Response $response, $key, $value)
    {
        $response->inlineDisplaySafeTypes[$key] = $value;
    }
}