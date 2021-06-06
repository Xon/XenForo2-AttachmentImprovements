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
    protected $width = null;
    /** @var int|null */
    protected $height = null;

    /** @var int|null */
    protected $thumbnailWidth = null;
    /** @var int|null */
    protected $thumbnailHeight = null;

    /** @var array */
    protected $badTags = [];
    /** @var array */
    protected $badAttributes = [];

    /** @var bool */
    protected $validImage;
    /** @var bool */
    protected $throwOnBadData;

    public function __construct(string $svgPath, bool $throwOnBadData = true, array $badTags = null, array $badAttributes = null)
    {
        $this->svgPath = $svgPath;
        $this->throwOnBadData = $throwOnBadData;

        if ($badTags === null)
        {
            $badTags = \array_fill_keys(\explode(',', \strtolower(\XF::options()->SV_AttachImpro_badTags)), true);
        }

        if ($badAttributes === null)
        {
            $badAttributes = \array_fill_keys(\explode(',', \strtolower(\XF::options()->SV_AttachImpro_badAttributes)), true);
        }

        $this->badTags = $badTags;
        $this->badAttributes = $badAttributes;

        $this->parse();
    }

    public function getDimensions(): array
    {
        if (!$this->validImage)
        {
            if ($this->throwOnBadData)
            {
                throw new PrintableException(\XF::phrase('sv_bad_svg_data'));
            }

            return [];
        }

        return [
            'width'            => (int)$this->width,
            'height'           => (int)$this->height,
            'thumbnail_width'  => (int)$this->thumbnailWidth,
            'thumbnail_height' => (int)$this->thumbnailHeight,
        ];
    }

    public function isValid(): bool
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

    protected function scanXml(\SimpleXMLElement $node): bool
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
                $this->thumbnailHeight = \intval($thumbnailDimensions / $aspectRatio);
            }
            else if ($this->height > $thumbnailDimensions)
            {
                $this->thumbnailHeight = $thumbnailDimensions;
                $this->thumbnailWidth = \intval($thumbnailDimensions * $aspectRatio);
            }
        }
    }

    protected function extractDimension(string $name): int
    {
        $dimension = (string)$this->svgData[$name];
        if (\strrpos($dimension, 'px') === \strlen($dimension) - 2)
        {
            $dimension = \substr($dimension, 0, -2);
        }

        return \intval($dimension);
    }

    public function getSvgData(): \SimpleXMLElement
    {
        return $this->svgData;
    }

    public function setSvgData(\SimpleXMLElement $svgData)
    {
        $this->svgData = $svgData;
    }

    public function resize(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;

        $this->svgData['width'] = \strval($this->width);
        $this->svgData['height'] = \strval($this->height);
    }

    public function save(string $filename): bool
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