<?php

namespace SV\AttachmentImprovements;

class SvgFileWrapper extends \XF\FileWrapper
{
    /** @var bool */
    protected $isSvg = false;
    /** @var SvgImage */
    protected $svgImage = null;

    public function getImageType(): string
    {
        if ($this->isImage() && $this->isSvg)
        {
            return SvgImage::IMAGETYPE_SVG;
        }

        return parent::getImageType();
    }

    public function getImageData(): SvgImage
    {
        return $this->svgImage;
    }

    public function getImageWidth(): int
    {
        if ($this->isImage() && $this->isSvg)
        {
            $dimensions = $this->svgImage->getDimensions();

            return $dimensions['width'] ?? 0;
        }

        return parent::getImageWidth();
    }

    public function getImageHeight(): int
    {
        if ($this->isImage() && $this->isSvg)
        {
            $dimensions = $this->svgImage->getDimensions();

            return $dimensions['height'] ?? 0;
        }

        return parent::getImageHeight();
    }

    protected function analyzeImage()
    {
        $this->isImage = false;

        if (!$this->fileSize)
        {
            return;
        }

        if ($this->extension == 'svg')
        {
            $throwOnBadData = (bool)(\XF::options()->SV_RejectAttachmentWithBadTags ?? true);
            $class = \XF::extendClass('SV\AttachmentImprovements\SvgImage');
            $this->svgImage = new $class($this->filePath, $throwOnBadData);
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
