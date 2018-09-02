<?php

namespace SV\AttachmentImprovements\XF\Pub\View\Attachment;

use SV\AttachmentImprovements\InternalPathUrlSupport;
use SV\AttachmentImprovements\SvgResponse;
use XF\Db\Exception;

class View extends XFCP_View
{
    public function renderRaw()
    {
        if (!empty($this->params['return304']))
        {
            return parent::renderRaw();
        }

        SvgResponse::updateInlineImageTypes($this->response, 'svg', 'image/svg+xml');

        if (\XF::app()->options()->SV_AttachImpro_XAR)
        {
            /** @var \XF\Entity\Attachment $attachment */
            $attachment = $this->params['attachment'];
            $options= \XF::options();

            $attachmentFile = $attachment->Data->getAbstractedDataPath();
            if ($attachmentFile = InternalPathUrlSupport::convertAbstractFilenameToURL($attachmentFile))
            {
                if (\XF::$debugMode && $options->SV_AttachImpro_log)
                {
                    \XF::app()->logException(new Exception('X-Accel-Redirect:' . $attachmentFile));
                }
                $this->response
                    ->setAttachmentFileParams($attachment->filename, $attachment->extension)
                    ->header('ETag', '"' . $attachment->attach_date . '"')
                    ->header('X-Accel-Redirect', $attachmentFile);

                return '';
            }
        }

        return parent::renderRaw();
    }

}