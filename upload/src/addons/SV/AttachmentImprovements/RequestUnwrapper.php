<?php

namespace SV\AttachmentImprovements;

class RequestUnwrapper extends \XF\Http\Request
{
    public static function syncServerVar(\XF\Http\Request $request, $key)
    {
        $request->server[$key] = $_SERVER[$key];
    }
}