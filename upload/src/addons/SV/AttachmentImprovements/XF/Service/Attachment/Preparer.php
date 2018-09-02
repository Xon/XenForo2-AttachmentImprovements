<?php

namespace SV\AttachmentImprovements\XF\Service\Attachment;

use SV\AttachmentImprovements\FileWrapperUnwrapper;
use SV\AttachmentImprovements\SvgImage;
use XF\FileWrapper;
use XF\Util\File;

class Preparer extends XFCP_Preparer
{
    public $filename = '';

    public function updateDataFromFile(\XF\Entity\AttachmentData $data, \XF\FileWrapper $file, array $extra = [])
    {
        $this->filename = $file->getFileName();

        return parent::updateDataFromFile($data, $file, $extra);
    }

    public function insertDataFromFile(FileWrapper $file, $userId, array $extra = [])
    {
        $this->filename = $file->getFileName();

        if (!$file->isImage() && $file->getExtension() === 'svg')
        {
            // inject SVG support
            $class = \XF::extendClass('SV\AttachmentImprovements\SvgFileWrapper');
            /** @var \SV\AttachmentImprovements\SvgFileWrapper $wrapper */
            $file = new $class($file->getFilePath(), $file->getFileName());
        }
        else if ($file->isImage() && $file->getImageType() == IMAGETYPE_JPEG && \XF::options()->svAttachmentsStripExif)
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

        return parent::insertDataFromFile($file, $userId, $extra);
    }

    /**
     * @param string   $sourceFile
     * @param int|null $width
     * @param int|null $height
     * @return null|string
     */
    public function generateAttachmentThumbnail($sourceFile, &$width = null, &$height = null)
    {
        $newTempFile = parent::generateAttachmentThumbnail($sourceFile, $width, $height);
        if (!$newTempFile)
        {
            // inject SVG support
            $class = \XF::extendClass('SV\AttachmentImprovements\SvgFileWrapper');
            /** @var \SV\AttachmentImprovements\SvgFileWrapper $wrapper */
            $wrapper = new $class($sourceFile, $this->filename);

            if ($wrapper->getImageType() !== SvgImage::IMAGETYPE_SVG)
            {
                return null;
            }

            $image = $wrapper->getImageData();
            if ($image === null)
            {
                return null;
            }

            $dimensions = $image->getDimensions();
            if ($dimensions === null)
            {
                return null;
            }
            $image->resize($dimensions['thumbnail_width'], $dimensions['thumbnail_height']);


            $newTempFile = File::getTempFile();

            if ($newTempFile && $image->save($newTempFile))
            {
                $width = $image->getWidth();
                $height = $image->getHeight();

                return $newTempFile;
            }
            else
            {
                return null;
            }
        }

        return $newTempFile;
    }
}