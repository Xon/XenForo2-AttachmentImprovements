<?php

namespace SV\AttachmentImprovements;

class RequestUnwrapper extends \XF\Http\Request
{
    public static function syncServerVar(\XF\Http\Request $request, string $key)
    {
        $request->server[$key] = $_SERVER[$key];
    }
}