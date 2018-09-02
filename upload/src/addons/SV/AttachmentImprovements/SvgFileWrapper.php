<?php

namespace SV\AttachmentImprovements;

class SvgFileWrapper extends \XF\FileWrapper
{
    protected $isSvg;
    /** @var SvgImage */
    protected $svgImage = null;

    public function getImageType()
    {
        if ($this->isImage() && $this->isSvg)
        {
            return SvgImage::IMAGETYPE_SVG;
        }

        return parent::getImageType();
    }

    /**
     * @return SvgImage
     */
    public function getImageData()
    {
        return $this->svgImage;
    }

    public function getImageWidth()
    {
        if ($this->isImage() && $this->isSvg)
        {
            $dimensions = $this->svgImage->getDimensions();
            return isset($dimensions['width']) ? $dimensions['width'] : 0;
        }

        return parent::getImageWidth();
    }

    public function getImageHeight()
    {
        if ($this->isImage() && $this->isSvg)
        {
            $dimensions = $this->svgImage->getDimensions();
            return isset($dimensions['height']) ? $dimensions['height'] : 0;
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
            $throwOnBadData = \XF::options()->SV_RejectAttachmentWithBadTags;
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
