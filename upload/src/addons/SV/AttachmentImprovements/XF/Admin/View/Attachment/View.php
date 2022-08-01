<?php

namespace SV\AttachmentImprovements\XF\Admin\View\Attachment;

use SV\AttachmentImprovements\InternalPathUrlSupport;
use SV\AttachmentImprovements\SvgResponse;

class View extends XFCP_View
{
    public function renderRaw()
    {
        if (!empty($this->params['return304']))
        {
            return parent::renderRaw();
        }

        SvgResponse::updateInlineImageTypes($this->response, 'svg', 'image/svg+xml');

        $options = \XF::options();
        if ($options->SV_AttachImpro_XAR ?? false)
        {
            /** @var \XF\Entity\Attachment $attachment */
            $attachment = $this->params['attachment'];

            $attachmentFile = $attachment->Data->getAbstractedDataPath();
            if ($attachmentFile = InternalPathUrlSupport::convertAbstractFilenameToURL($attachmentFile))
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

        return parent::renderRaw();
    }
}