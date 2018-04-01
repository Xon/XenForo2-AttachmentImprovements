<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SV\AttachmentImprovements\XF\Service\Attachment;

use SV\AttachmentImprovements\FileWrapperUnwrapper;
use XF\FileWrapper;

class Preparer extends XFCP_Preparer
{
    public function insertDataFromFile(FileWrapper $file, $userId, array $extra = [])
    {
        if ($file->isImage() && $file->getImageType() == IMAGETYPE_JPEG && \XF::options()->svAttachmentsStripExif)
        {
            // force a re-read of EXIF data
            FileWrapperUnwrapper::resetExifCache($file);
            $orientation = 0;
            // incase exif extension is being stupid
            $exif = $file->getExif();
            if ($exif)
            {
                if (!empty($exif['IFD0']['Orientation']) && $exif['IFD0']['Orientation'] > 1)
                {
                    $orientation = $exif['IFD0']['Orientation'];
                }
                $transformRequired = ($orientation > 1);

                $tempFile = $file->getFilePath();
                $image = \XF::app()->imageManager()->imageFromFile($tempFile);
                if ($transformRequired)
                {
                    $image->transformByExif($orientation);
                }
            }
            // xenforo strips the image of EXIF data by-default, in-case exif parsing fails but is present re-save anyway.
            $image->save($tempFile, null, 100);
            FileWrapperUnwrapper::refreshFileSize($file);
            // wipe EXIF data for the final time
            FileWrapperUnwrapper::resetExifCache($file);
        }

        return parent::insertDataFromFile($file, $userId, $extra); // TODO: Change the autogenerated stub
    }
}