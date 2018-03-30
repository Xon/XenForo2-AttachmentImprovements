<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 23/11/2017
 * Time: 12:32 AM
 */

namespace SV\AttachmentImprovements\XF;

use SV\AttachmentImprovements\SvgImage;

class FileWrapper extends XFCP_FileWrapper
{
    protected $isSvg;
    /** @var SvgImage */
    protected $svgImage = null;

    public function getImageType()
    {
        if ($this->isSvg)
        {
            return SvgImage::IMAGETYPE_SVG;
        }

        return parent::getImageType();
    }

    public function getImageWidth()
    {
        if ($this->isImage() && $this->isSvg)
        {
            return $this->svgImage->getDimensions()['width'];
        }

        return parent::getImageWidth();
    }

    public function getImageHeight()
    {
        if ($this->isImage() && $this->isSvg)
        {
            return $this->svgImage->getDimensions()['height'];
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
            $this->svgImage = new SvgImage($this->filePath);
            $this->isSvg = true;
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