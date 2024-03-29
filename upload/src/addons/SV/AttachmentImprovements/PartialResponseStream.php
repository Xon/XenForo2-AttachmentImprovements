<?php
/**
 * @noinspection PhpMissingParentCallCommonInspection
 */

namespace SV\AttachmentImprovements;

use XF\Http\ResponseStream;
use function func_get_args;
use function strlen, count, fseek, fread;

class PartialResponseStream extends ResponseStream
{
    /** @var array<array{0:int,1:int}> */
    protected $ranges = [];

    /** @var string */
    protected $internalContentType;
    /** @var string */
    protected $boundary;

    public static function new($resource, string $internalContentType, string $boundary, array $ranges): self
    {
        $class = \XF::extendClass(self::class);
        /** @var self $obj */
        $obj = new $class(...func_get_args());

        return $obj;
    }

    public function __construct($resource, string $internalContentType, string $boundary, array $ranges)
    {
        if (count($ranges) === 0)
        {
            throw new \InvalidArgumentException('Must have a set of ranges');
        }

        $length = 0;
        foreach ($ranges as $range)
        {
            $length += ($range[1] - $range[0]) + 1;
        }

        $this->boundary = $boundary;
        $this->internalContentType = $internalContentType;
        $this->ranges = $ranges;
        parent::__construct($resource, $length);
    }

    public function getLength(): int
    {
        $content = $this->getContents();

        return strlen($content);
    }

    protected function readChunks(bool $returnBuffer): ?string
    {
        $output = '';

        $multiPart = count($this->ranges) > 1;
        foreach ($this->ranges as $range)
        {
            $length = $range[1] - $range[0] + 1;

            if (fseek($this->resource, $range[0]) === -1)
            {
                // seek failed, bail
                break;
            }
            $tmp = fread($this->resource, $length);
            if ($tmp === false)
            {
                // read failed, bail
                break;
            }
            if ($multiPart)
            {
                $tmp = "\n--{$this->boundary}\nContent-Type: {$this->internalContentType}\nContent-Range: bytes {$range[0]}-{$range[1]}/{$length}\n\n" . $tmp;
            }

            $output .= $tmp;
        }

        if ($multiPart)
        {
            $output .= "\n--{$this->boundary}--\n";
        }
        $this->length = strlen($output);

        if ($returnBuffer)
        {
            return $output;
        }
        echo $output;

        return null;
    }

    public function output(): void
    {
        if ($this->contents === null)
        {
            $this->readChunks(false);
        }
        else
        {
            echo $this->contents;
        }
    }

    public function getContents(): string
    {
        if ($this->contents === null)
        {
            $this->contents = $this->readChunks(true);
        }

        return $this->contents;
    }
}