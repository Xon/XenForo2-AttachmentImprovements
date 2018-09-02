<?php

namespace SV\AttachmentImprovements;

use XF\PrintableException;
use XF\Util\Xml;

class SvgImage
{
    const IMAGETYPE_SVG = 'imagetype_svg';

    protected $svgPath;

    /** @var \SimpleXMLElement */
    protected $svgData;

    /** @var int|null */
    protected $width  = null;
    /** @var int|null */
    protected $height = null;

    /** @var int|null */
    protected $thumbnailWidth  = null;
    /** @var int|null */
    protected $thumbnailHeight = null;

    protected $badTags;
    protected $badAttributes;

    /** @var bool */
    protected $validImage;
    /** @var bool */
    protected $throwOnBadData;

    /**
     * SvgImage constructor.
     *
     * @param       $svgPath
     * @param bool  $throwOnBadData
     * @param array $badTags
     * @param array $badAttributes
     */
    public function __construct($svgPath, $throwOnBadData = true, array $badTags = null, array $badAttributes = null)
    {
        $this->svgPath = $svgPath;
        $this->throwOnBadData = $throwOnBadData;

        if ($badTags == null)
        {
            $badTags = array_fill_keys(explode(',', strtolower(\XF::options()->SV_AttachImpro_badTags)), true);
        }

        if ($badAttributes == null)
        {
            $badAttributes = array_fill_keys(explode(',', strtolower(\XF::options()->SV_AttachImpro_badAttributes)), true);
        }

        $this->badTags = $badTags;
        $this->badAttributes = $badAttributes;

        $this->parse();
    }

    public function getDimensions()
    {
        if (!$this->validImage)
        {
            if ($this->throwOnBadData)
            {
                throw new PrintableException(\XF::phrase('sv_bad_svg_data'));
            }

            return null;
        }

        $dat = [
            'width'            => $this->width,
            'height'           => $this->height,
            'thumbnail_width'  => $this->thumbnailWidth,
            'thumbnail_height' => $this->thumbnailHeight
        ];

        return $dat;
    }

    public function isValid()
    {
        return $this->validImage;
    }

    protected function parse()
    {
        try
        {
            $xmlData = Xml::openFile($this->svgPath);
        }
        catch (\InvalidArgumentException $e)
        {
            \XF::logException($e);

            $this->validImage = false;

            return;
        }

        if (!$this->scanXml($xmlData))
        {
            $this->validImage = false;

            return;
        }

        $this->svgData = $xmlData;
        $this->validImage = true;

        $this->parseDimensions();
    }

    protected function scanXml(\SimpleXMLElement $node)
    {
        foreach ($node->attributes() AS $key => $val)
        {
            if (isset($this->badAttributes[strtolower($key)]))
            {
                return false;
            }
            $val = $this->scanXml($val);
            if (empty($val))
            {
                return false;
            }
        }

        $children = $node->children();
        foreach ($children as $key => $val)
        {
            if (isset($this->badTags[strtolower($key)]))
            {
                return false;
            }
            $val = $this->scanXml($val);
            if (empty($val))
            {
                return false;
            }
        }

        return true;
    }

    protected function parseDimensions()
    {
        if (!$this->validImage)
        {
            return;
        }

        $this->width = $this->extractDimension('width');
        $this->height = $this->extractDimension('height');

        if ($this->width && $this->height)
        {
            $thumbnailDimensions = \XF::options()->attachmentThumbnailDimensions;
            $aspectRatio = $this->width / $this->height;

            if ($this->width > $this->height && $this->width > $thumbnailDimensions)
            {
                $this->thumbnailWidth = $thumbnailDimensions;
                $this->thumbnailHeight = intval($thumbnailDimensions / $aspectRatio);
            }
            else if ($this->height > $thumbnailDimensions)
            {
                $this->thumbnailHeight = $thumbnailDimensions;
                $this->thumbnailWidth = intval($thumbnailDimensions * $aspectRatio);
            }
        }
    }

    protected function extractDimension($name)
    {
        $dimension = (string)$this->svgData[$name];
        if (strrpos($dimension, 'px') === strlen($dimension) - 2)
        {
            $dimension = substr($dimension, 0, -2);
        }

        return intval($dimension);
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getSvgData()
    {
        return $this->svgData;
    }

    /**
     * @param \SimpleXMLElement $svgData
     */
    public function setSvgData($svgData)
    {
        $this->svgData = $svgData;
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function resize($width, $height)
    {
        $this->width = intval($width);
        $this->height = intval($height);

        $this->svgData['width'] = strval($this->width);
        $this->svgData['height'] = strval($this->height);
    }

    /**
     * @param $filename
     * @return bool
     */
    public function save($filename)
    {
        return $this->svgData->saveXML($filename) !== false;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }
}