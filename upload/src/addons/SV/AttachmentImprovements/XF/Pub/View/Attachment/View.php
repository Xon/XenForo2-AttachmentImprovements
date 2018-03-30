<?php

namespace SV\AttachmentImprovements\XF\Pub\View\Attachment;

use League\Flysystem\Adapter\Local;
use XF\Db\Exception;
use XF\Util\File;

class View extends XFCP_View
{
    public function renderRaw()
    {
        /** @var \XF\Entity\Attachment $attachment */
        $attachment = $this->params['attachment'];

        if (!headers_sent() && function_exists('header_remove'))
        {
            header_remove('Expires');
            header('Cache-control: private');
        }

        $extension = File::getFileExtension($attachment->filename);
        $imageTypes = [
            'svg'  => 'image/svg+xml',
            'gif'  => 'image/gif',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe'  => 'image/jpeg',
            'png'  => 'image/png'
        ];

        if (isset($imageTypes[$extension]) && ($attachment->Data->width && $attachment->Data->height))
        {
            $this->response->header('Content-type', $imageTypes[$extension], true);
            $this->response->setDownloadFileName($attachment['filename'], true);
        }
        else
        {
            $this->response->header('Content-type', 'application/octet-stream', true);
            $this->response->setDownloadFileName($attachment['filename']);
        }

        $this->response->header('ETag', '"' . $attachment['attach_date'] . '"', true);
        $this->response->header('Content-Length', $attachment['file_size'], true);
        $this->response->header('X-Content-Type-Options', 'nosniff');

        $attachmentFile = $attachment->Data->getAbstractedDataPath();

        $dataAdapter = \XF::fs()->getAdapter('internal-data://');

        if ($dataAdapter instanceof Local)
        {
            $pathPrefix = $dataAdapter->getPathPrefix();
        }
        else
        {
            return parent::renderRaw();
        }

        $attachmentPath = str_replace('internal-data://', $pathPrefix, $attachmentFile);
        $attachmentPath = str_replace(\XF::getRootDirectory(), '', $attachmentPath);

        $options = \XF::app()->options();
        if ($options->SV_AttachImpro_XAR)
        {
            if (\XF::$debugMode && $options->SV_AttachImpro_log)
            {
                \XF::app()->logException(new Exception('X-Accel-Redirect:' . $attachmentPath));
            }
            $this->response->header('X-Accel-Redirect', $attachmentPath);

            return '';
        }

        return parent::renderRaw();
    }
}