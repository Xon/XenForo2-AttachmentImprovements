<?php

namespace SV\AttachmentImprovements\XF\Http;

use SV\AttachmentImprovements\SvgFileWrapper;

/**
 * @extends \XF\Http\Upload
 */
class Upload extends XFCP_Upload
{
    public function canUseSvg(): bool
    {
        if (strtolower($this->extension) !== 'svg')
        {
            return false;
        }

        $user = \XF::visitor();

        return is_callable([$user, 'canUseSvg']) && $user->canUseSvg();
    }

    public function getFileWrapper()
    {
        if ($this->tempFile && $this->canUseSvg())
        {
            return SvgFileWrapper::new($this->tempFile, $this->fileName);
        }

        return parent::getFileWrapper();
    }
}