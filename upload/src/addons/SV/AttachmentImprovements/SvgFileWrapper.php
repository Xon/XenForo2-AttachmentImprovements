<?php

namespace SV\AttachmentImprovements;

use XF\FileWrapper;
use function func_get_args;
use function strtolower;

class SvgFileWrapper extends FileWrapper
{
    /** @var bool */
    protected $isSvg = false;
    /** @var SvgImage */
    protected $svgImage = null;

    public static function new(string $filepath, string $filename): self
    {
        $class = \XF::extendClass(self::class);
        /** @var self $obj */
        $obj = new $class(...func_get_args());

        return $obj;
    }

    public function isSvgImage(): bool
    {
        return $this->isImage() && $this->isSvg;
    }

    /**
     * @return int|string|null
     */
    public function getImageType()
    {
        if ($this->isSvgImage())
        {
            return SvgImage::IMAGETYPE_SVG;
        }

        return parent::getImageType();
    }

    public function getImageData(): ?SvgImage
    {
        return $this->isSvgImage() ? $this->svgImage : null;
    }

    public function getImageWidth(): int
    {
        if ($this->isSvgImage())
        {
            $dimensions = $this->svgImage->getDimensions();

            return $dimensions['width'] ?? 0;
        }

        return parent::getImageWidth();
    }

    public function getImageHeight(): int
    {
        if ($this->isSvgImage())
        {
            $dimensions = $this->svgImage->getDimensions();

            return $dimensions['height'] ?? 0;
        }

        return parent::getImageHeight();
    }

    protected function analyzeImage(): void
    {
        $this->isImage = false;

        if (!$this->fileSize)
        {
            return;
        }

        if (strtolower($this->extension) === 'svg')
        {
            $throwOnBadData = (bool)(\XF::options()->SV_RejectAttachmentWithBadTags ?? true);
            $this->svgImage = SvgImage::new($this->filePath, $throwOnBadData);
            $this->isSvg = $this->svgImage->isValid();
        }

        if (!$this->isSvg)
        {
            $this->svgImage = null;
            parent::analyzeImage();
        }
        else
        {
            $this->isImage = true;
        }
    }
}
