<?php

namespace SV\AttachmentImprovements;

use enshrined\svgSanitize\Sanitizer;
use XF\PrintableException;
use XF\Util\Xml;
use function array_fill_keys, explode, strtolower, strval, array_map, strlen, strrpos, substr;
use function file_get_contents;
use function file_put_contents;
use function func_get_args;
use function libxml_disable_entity_loader;
use function libxml_use_internal_errors;
use function simplexml_import_dom;

class SvgImage
{
    public const IMAGETYPE_SVG = 'imagetype_svg';

    /** @var string|null */
    protected $svgPath;

    /** @var \SimpleXMLElement|null */
    protected $svgData;

    /** @var int|null */
    protected $originalWidth = null;
    /** @var int|null */
    protected $originalHeight = null;

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

    /** @var \XF\Phrase|string */
    protected $error = null;
    /**
     * @var Sanitizer
     */
    protected $sanitizer;
    /** @var bool */
    protected $validImage;
    /** @var bool */
    protected $throwOnBadData;

    public static function new(string $svgPath, bool $throwOnBadData = true, array $badTags = null, array $badAttributes = null): self
    {
        $class = \XF::extendClass(self::class);
        /** @var self $obj */
        $obj = new $class(...func_get_args());

        return $obj;
    }

    public function __construct(string $svgPath, bool $throwOnBadData = true, array $badTags = null, array $badAttributes = null)
    {
        $this->svgPath = $svgPath;
        $this->throwOnBadData = $throwOnBadData;

        if ($badTags === null)
        {
            $badTags = array_fill_keys(explode(',', strtolower(\XF::options()->SV_AttachImpro_badTags ?? '')), true);
        }

        if ($badAttributes === null)
        {
            $badAttributes = array_fill_keys(explode(',', strtolower(\XF::options()->SV_AttachImpro_badAttributes ?? '')), true);
        }

        $this->badTags = $badTags;
        $this->badAttributes = $badAttributes;

        $this->parse();
    }

    public function getDimensions(): array
    {
        if (!$this->validImage)
        {
            $this->error = \XF::phrase('sv_bad_svg_data');
            if ($this->throwOnBadData)
            {
                throw new PrintableException($this->error);
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

    protected function getSvgSanitizer(): Sanitizer
    {
        if ($this->sanitizer === null)
        {
            $this->sanitizer = new Sanitizer();
        }

        return $this->sanitizer;
    }

    protected function logError(?string $phraseKey): void
    {
        $this->validImage = false;
        $this->error = \XF::phrase($phraseKey);
        if ($this->throwOnBadData)
        {
            throw new PrintableException($this->error);
        }
    }

    protected function parse(): void
    {
        $sanitizer = $this->getSvgSanitizer();
        $sanitizer->removeRemoteReferences(true);

        $contents = file_get_contents($this->svgPath);
        $sanitized = $sanitizer->sanitize($contents);
        if ($sanitized === false)
        {
            $this->logError('could_not_upload_svg_asset_after_sanitization');
            return;
        }

        $xmlData = $this->loadXml($sanitized);
        if ($xmlData === null)
        {
            $this->logError('could_not_upload_svg_asset_after_sanitization');
            return;
        }

        if (!$this->scanXml($xmlData))
        {
            $this->logError('sv_bad_svg_data');
            return;
        }

        $this->svgData = $xmlData;
        $this->validImage = true;

        $this->parseDimensions();
    }

    protected function loadXml(string $xml): ?\SimpleXMLElement
    {
        $dom = new \DOMDocument();
        $disableEntityLoader = LIBXML_VERSION < 20900;
        if ($disableEntityLoader)
        {
            $entityLoader = libxml_disable_entity_loader(true);
        }
        $internalErrors = libxml_use_internal_errors(true);
        try
        {
            if (!$dom->loadXML($xml))
            {
                return null;
            }
        }
        finally
        {
            if ($disableEntityLoader)
            {
                libxml_disable_entity_loader($entityLoader);
            }
            libxml_use_internal_errors($internalErrors);
        }

        return simplexml_import_dom($dom);
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

    protected function parseDimensions(): void
    {
        if (!$this->validImage)
        {
            return;
        }

        $this->originalWidth = $this->width = $this->extractDimension('width');
        $this->originalHeight = $this->height = $this->extractDimension('height');

        if ($this->width == 0 || $this->height == 0)
        {
            // extract from viewBox
            $dimensions = explode(' ', (string)($xmlData['viewBox'] ?? ''), 4);
            $dimensions = array_map('\intval', $dimensions);

            $this->width = (int)($dimensions[2] ?? 0);
            $this->height = (int)($dimensions[3] ?? 0);
        }

        if ($this->width > 0 && $this->height > 0)
        {
            $thumbnailDimensions = \XF::options()->attachmentThumbnailDimensions;
            $aspectRatio = $this->width / $this->height;

            if ($this->width > $this->height && $this->width > $thumbnailDimensions)
            {
                $this->thumbnailWidth = $thumbnailDimensions;
                $this->thumbnailHeight = (int)($thumbnailDimensions / $aspectRatio);
            }
            else if ($this->height > $thumbnailDimensions)
            {
                $this->thumbnailHeight = $thumbnailDimensions;
                $this->thumbnailWidth = (int)($thumbnailDimensions * $aspectRatio);
            }
        }
    }

    protected function extractDimension(string $name): int
    {
        $dimension = (string)($this->svgData[$name] ?? '');
        if (strlen($dimension) == 0)
        {
            return 0;
        }

        if (strrpos($dimension, 'px') === strlen($dimension) - 2)
        {
            $dimension = substr($dimension, 0, -2);
        }

        return (int)$dimension;
    }

    public function getSvgData(): ?\SimpleXMLElement
    {
        return $this->svgData;
    }

    public function setSvgData(\SimpleXMLElement $svgData = null): void
    {
        $this->svgData = $svgData;
    }

    public function resize(int $width, int $height): void
    {
        $this->width = $width;
        $this->height = $height;

        if (!$this->svgData['viewBox'])
        {
            $this->svgData['viewBox'] = "0 0 {$this->originalWidth} {$this->originalHeight}";
        }
        $this->svgData['preserveAspectRatio'] = 'xMidYMid meet';
        $this->svgData['width'] = strval($this->width);
        $this->svgData['height'] = strval($this->height);
    }

    public function save(string $filename): bool
    {
        return $this->svgData->saveXML($filename) !== false;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }
}