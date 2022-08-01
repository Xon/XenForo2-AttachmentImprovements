<?php

namespace SV\AttachmentImprovements;

use XF\Http\Response;

class ResponseMultiPart extends Response
{
    /**
     * @param Response    $response
     * @param string      $contentType
     * @param string|null $charset
     */
    public static function contentTypeForced(Response $response, string $contentType, ?string $charset)
    {
        $response->contentType = $contentType;
        if ($charset !== null)
        {
            $response->charset($charset);
        }
    }
}