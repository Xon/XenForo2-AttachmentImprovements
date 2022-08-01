<?php

namespace SV\AttachmentImprovements;

use XF\FileWrapper;
use function clearstatcache, filesize;

class FileWrapperUnwrapper extends FileWrapper
{
    public static function refreshFileSize(FileWrapper $file)
    {
        clearstatcache();
        $file->fileSize = filesize($file->getFilePath());
    }

    public static function resetExifCache(FileWrapper $file)
    {
        $file->exif = null;
    }
}