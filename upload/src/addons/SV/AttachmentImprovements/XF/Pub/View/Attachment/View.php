<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\AttachmentImprovements\XF\Pub\View\Attachment;

use League\Flysystem\Adapter\Local;
use XF\Db\Exception;
use XF\Util\File;

class View extends XFCP_View
{
    public function renderRaw()
    {
        if (!empty($this->params['return304']))
        {
            return parent::renderRaw();
        }

        SvgResponse::updateInlineImageTypes($this->response, 'svg', 'image/svg+xml')

        if (\XF::app()->options()->SV_AttachImpro_XAR)
        {
            $attachmentFile = $attachment->Data->getAbstractedDataPath();
            if ($this->convertFilenameToURL($attachmentFile))
            {
                if (\XF::$debugMode && $options->SV_AttachImpro_log)
                {
                    \XF::app()->logException(new Exception('X-Accel-Redirect:' . $attachmentFile));
                }
                $this->response->header('X-Accel-Redirect', $attachmentFile);

                return '';
            }
        }

        return parent::renderRaw();
    }

    public function convertFilenameToURL(&$attachmentFile)
    {
        $xfCodeRoot = \XF::getRootDirectory();
        $attachmentFile = str_replace('internal-data://', '', $attachmentFile);
        $internalData = XenForo_Helper_File::getInternalDataPath();
        $internalDataUrl = $this->getInternalDataUrl();
        if ($internalDataUrl && strpos($attachmentFile, $internalData) === 0)
        {
            $attachmentFile = str_replace($internalData, $internalDataUrl, $attachmentFile);
            return true;
        }
        else if (strpos($attachmentFile, $xfCodeRoot) === 0)
        {
            $attachmentFile = str_replace($xfCodeRoot, '', $attachmentFile);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getInternalDataUrl()
    {
        return \XF::app()->config()->internalDataUrl;
    }
}