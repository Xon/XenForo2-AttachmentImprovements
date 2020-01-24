<?php

namespace SV\AttachmentImprovements;

use XF\Http\Response;

/**
 * Class ResponseMultiPart
 *
 * @package SV\LiveContent
 */
class ResponseMultiPart extends Response
{
    /**
     * @param Response $response
     * @param string   $contentType
     * @param string   $charset
     */
    public static function contentTypeForced(Response $response, $contentType, $charset)
    {
        $response->contentType = $contentType;
        if ($charset !== null)
        {
            $response->charset($charset);
        }
    }
}