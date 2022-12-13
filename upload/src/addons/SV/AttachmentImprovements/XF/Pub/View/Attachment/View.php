<?php

namespace SV\AttachmentImprovements\XF\Pub\View\Attachment;

use SV\AttachmentImprovements\InternalPathUrlSupport;
use SV\AttachmentImprovements\PartialResponseStream;
use SV\AttachmentImprovements\SvgResponse;
use SV\AttachmentImprovements\ResponseMultiPart;
use SV\AttachmentImprovements\XF\Entity\AttachmentData;
use XF\Http\ResponseStream;
use function count, explode, trim, md5, preg_match;

class View extends XFCP_View
{
    public function renderRaw()
    {
        if (!empty($this->params['return304']))
        {
            return parent::renderRaw();
        }

        /** @var \XF\Entity\Attachment $attachment */
        $attachment = $this->params['attachment'];
        /** @var AttachmentData $data */
        $data = $attachment->Data;
        if ($data->isSvg())
        {
            SvgResponse::updateInlineImageTypes($this->response, 'svg', 'image/svg+xml');
        }

        $options = \XF::options();
        if ($options->SV_AttachImpro_XAR ?? false)
        {
            $attachmentFile = $attachment->Data->getAbstractedDataPath();
            $attachmentFile = InternalPathUrlSupport::convertAbstractFilenameToURL($attachmentFile);
            if ($attachmentFile)
            {
                if (\XF::$debugMode && ($options->SV_AttachImpro_log ?? false))
                {
                    \XF::logError('X-Accel-Redirect:' . $attachmentFile, true);
                }
                $this->response
                    ->setAttachmentFileParams($attachment->filename, $attachment->extension)
                    ->header('ETag', '"' . $attachment->attach_date . '"')
                    ->header('X-Accel-Redirect', $attachmentFile);

                return '';
            }
        }
        else
        {
            if (isset($this->params['rangeSupport']))
            {
                $this->response->header('Accept-Ranges','bytes');

                $rangeRequest = isset($this->params['rangeRequest']) ? \strtolower($this->params['rangeRequest']) : null;
                if ($rangeRequest !== null)
                {
                    $chunkSize = 1024 * (int)($options->svPartialContentChunkSize ?? 0);
                    if (!preg_match('/^bytes\s*=\s*(\d+\s*-\s*(?:\d+|))\s*(?:\s*,\s*(\d+\s*-\s*\d+)\s*)*\s*$/', $rangeRequest, $matches))
                    {
                        $this->response
                            ->httpCode('416')
                            ->header('Content-Range','bytes */' . $attachment->file_size) // Required in 416.
                        ;
                        return '';
                    }
                    unset($matches[0]);
                    $ranges = [];

                    $fileSize = $attachment->file_size - 1;
                    foreach ($matches as $range)
                    {
                        $start = $end = 0;
                        $parts = explode('-', $range);
                        if (count($parts) === 2)
                        {
                            $parts[0] = trim($parts[0]);
                            $parts[1] = trim($parts[1]);
                            $start = (int)$parts[0];
                            $end = $parts[1] === '' ? $fileSize: (int)$parts[1];
                            if ($start < 0 || $end > $fileSize)
                            {
                                $start = 0;
                                $end = 0;
                            }
                        }

                        if ($start > $end || !$start && !$end)
                        {
                            $this->response
                                ->httpCode('416')
                                ->header('Content-Range', 'bytes */' . $attachment->file_size) // Required in 416.
                            ;

                            return '';
                        }

                        // cap at chunk size
                        if ($chunkSize && ($end - $start) + 1 > $chunkSize)
                        {
                            $end = $start + $chunkSize;
                        }

                        $ranges[] = [$start, $end];
                    }

                    $this->response
                        ->setAttachmentFileParams($attachment->filename, $attachment->extension)
                        ->header('ETag', '"' . $attachment->attach_date . '"')
                        ->httpCode(206)
                    ;
                    $internalContentType = $this->response->contentType();
                    $boundary = '';
                    if (count($ranges) > 1)
                    {
                        $boundary = md5('attachment' . $attachment->attach_date . \XF::$time);
                        ResponseMultiPart::contentTypeForced($this->response, 'multipart/byteranges; boundary='. $boundary, '');
                    }
                    else
                    {
                        $range = $ranges[0];
                        $this->response
                            ->header('Content-Range', "bytes {$range[0]}-{$range[1]}/{$attachment->file_size}")
                        ;
                    }

                    $resource = \XF::fs()->readStream($attachment->Data->getAbstractedDataPath());
                    return $this->responseStream($resource, $internalContentType, $boundary, $ranges);
                }
            }
        }

        return parent::renderRaw();
    }

    /**
     * @param Resource $resource
     * @param string   $internalContentType
     * @param string   $boundary
     * @param array    $ranges
     * @return PartialResponseStream
     * @throws \Exception
     */
    public function responseStream($resource, string $internalContentType, string $boundary, array $ranges): ResponseStream
    {
        $class = \XF::extendClass(PartialResponseStream::class);
        /** @var PartialResponseStream $obj */
        $obj = new $class($resource, $internalContentType, $boundary, $ranges);

        return $obj;
    }
}