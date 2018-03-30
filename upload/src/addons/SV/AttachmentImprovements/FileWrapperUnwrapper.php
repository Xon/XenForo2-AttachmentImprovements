<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\AttachmentImprovements;

use XF\FileWrapper;

class FileWrapperUnwrapper extends FileWrapper
{
    public static function refreshFileSize(FileWrapper $file)
    {
        clearstatcache();
        $file->fileSize = filesize($file->getFilePath());
    }
}