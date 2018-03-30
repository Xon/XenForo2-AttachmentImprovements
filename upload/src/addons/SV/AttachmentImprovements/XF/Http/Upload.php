<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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