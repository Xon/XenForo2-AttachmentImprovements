<?php

namespace SV\AttachmentImprovements;

use XF\Http\Response;

class ResponseMultiPart extends Response
{
    public static function contentTypeForced(Response $response, string $contentType, ?string $charset): void
    {
        $response->contentType = $contentType;
        if ($charset !== null)
        {
            $response->charset($charset);
        }
    }
}