<?php

namespace SV\AttachmentImprovements\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Extends \XF\Entity\AttachmentData
 */
class AttachmentData extends XFCP_AttachmentData
{
    /**
     * @return string|null
     */
    public function getThumbnailUrl()
    {
        if (!$this->thumbnail_width && $this->extension !== 'svg')
        {
            return parent::getThumbnailUrl();
        }

        $dataId = $this->data_id;

        $path = sprintf('attachments/%d/%d-%s.svg',
            floor($dataId / 1000),
            $dataId,
            $this->file_hash
        );
        return $this->app()->applyExternalDataUrl($path);
    }
}