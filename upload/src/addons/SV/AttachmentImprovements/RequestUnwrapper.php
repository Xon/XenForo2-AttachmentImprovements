<?php

namespace SV\AttachmentImprovements;

use XF\Http\Request;

class RequestUnwrapper extends Request
{
    public static function syncServerVar(Request $request, string $key): void
    {
        $request->server[$key] = $_SERVER[$key];
    }
}