<?php

namespace SV\AttachmentImprovements;

abstract class InternalPathUrlSupport
{

    public static function convertAbstractFilenameToURL($attachmentFile, $canonical = false)
    {
        $attachmentFile = str_replace('internal-data://', '', $attachmentFile);

        return self::applyInternalDataUrl($attachmentFile, $canonical);
    }

    public static function applyInternalDataUrl($internalPath, $canonical = false)
    {
        $app = \XF::app();
        $internalDataUrl = $app->config('internalDataUrl');
        if ($internalDataUrl instanceof \Closure)
        {
            $url = $internalDataUrl($internalPath, $canonical);
        }
        else if ($internalDataUrl === null)
        {
            $url = "internal_data/$internalPath";
        }
        else
        {
            $url = "$internalDataUrl/$internalPath";
        }

        /** @var \Closure $pather */
        $pather = $app->container('request.pather');

        $url = $pather($url, $canonical ? 'canonical' : 'nopath');

        if (!(preg_match('#^(/|[a-z]+:)#i', $url))
        {
            $url = '/' . $url;
        }

        return $url;
    }
}