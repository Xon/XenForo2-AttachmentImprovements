<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XF\Entity;

use League\Flysystem\Filesystem as FlyFilesystem;
use XF\LocalFsAdapter;
use function explode;
use function sprintf, floor;
use function strtolower;

class AttachmentData extends XFCP_AttachmentData
{
    /**
     * @param bool $canonical
     * @return string|null
     */
    public function getThumbnailUrl($canonical = false)
    {
        if (!$this->isSvg())
        {
            return parent::getThumbnailUrl($canonical);
        }

        $dataId = $this->data_id;

        $path = sprintf('attachments/%d/%d-%s.svg',
            floor($dataId / 1000),
            $dataId,
            $this->file_hash
        );

        return $this->app()->applyExternalDataUrl($path);
    }

    public function isRangeRequestSupported(): bool
    {
        $path = $this->getAbstractedDataPath();
        /** @noinspection PhpUnusedLocalVariableInspection */
        [$prefix, $path] = explode('://', $path, 2);
        $fs = \XF::fs()->getFilesystem($prefix);
        if ($fs instanceof FlyFilesystem)
        {
            $adapter = $fs->getAdapter();
            if ($adapter instanceof LocalFsAdapter)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $dataId
     * @param string $fileHash
     * @return string
     */
    protected function _getAbstractedThumbnailPath($dataId, $fileHash)
    {
        if (!$this->isSvg())
        {
            return parent::_getAbstractedThumbnailPath($dataId, $fileHash);
        }

        return sprintf('data://attachments/%d/%d-%s.svg',
            floor($dataId / 1000),
            $dataId,
            $fileHash
        );
    }

    public function isSvg(): bool
    {
        if (strtolower($this->extension) !== 'svg')
        {
            return false;
        }

        if ($this->thumbnail_width === 0 || $this->thumbnail_height === 0)
        {
            return false;
        }

        if (!(\XF::options()->svAllowInlineDisplayOfSvgAttachments ?? true))
        {
            return false;
        }

        return true;
    }

    public function getTypeGrouping(): string
    {
        $typeGroup = parent::getTypeGrouping();

        if ($this->isSvg() && $typeGroup === 'file')
        {
            return 'image';
        }

        return $typeGroup;
    }
}