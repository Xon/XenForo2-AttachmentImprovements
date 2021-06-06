<?php

namespace SV\AttachmentImprovements;

abstract class InternalPathUrlSupport
{

    public static function convertAbstractFilenameToURL(string $attachmentFile, bool $canonical = false)
    {
        list($prefix, $path) = \explode('://', $attachmentFile, 2);
        if ($prefix === 'internal-data')
        {
            return self::applyInternalDataUrl($path, $canonical);
        }
        else if ($prefix === 'data')
        {
            return \XF::app()->applyExternalDataUrl($path);
        }
        return null;
    }

    public static function applyInternalDataUrl(string $internalPath, bool $canonical = false)
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

        if (!\preg_match('#^(/|[a-z]+:)#i', $url))
        {
            $url = '/' . $url;
        }

        return $url;
    }
}