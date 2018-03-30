<?php

namespace SV\AttachmentImprovements\XF\Http;

class Upload extends XFCP_Upload
{
    public function getFileWrapper()
    {
        if (!$this->tempFile)
        {
            throw new \LogicException("Cannot get file wrapper for invalid upload (no temp file)");
        }

        $class = \XF::extendClass('XF\FileWrapper');
        /** @var \XF\FileWrapper $wrapper */
        $wrapper = new $class($this->tempFile, $this->fileName);

        if (is_array($this->exif))
        {
            $wrapper->setExif($this->exif);
        }

        return $wrapper;
    }
}