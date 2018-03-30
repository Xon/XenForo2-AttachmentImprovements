<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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