<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XF\Service\Attachment;

use SV\AttachmentImprovements\FileWrapperUnwrapper;
use SV\AttachmentImprovements\SvgFileWrapper;
use SV\AttachmentImprovements\SvgImage;
use XF\Entity\AttachmentData;
use XF\FileWrapper;
use XF\Util\File;
use function is_array;
use function strtolower;

/**
 * @extends \XF\Service\Attachment\Preparer
 */
class Preparer extends XFCP_Preparer
{
    public $filename = '';

    public function updateDataFromFile(AttachmentData $data, FileWrapper $file, array $extra = [])
    {
        $this->filename = $file->getFileName();

        return parent::updateDataFromFile($data, $file, $extra);
    }

    protected function canUseSvg(): bool
    {
        $user = \XF::visitor();
        return is_callable([$user, 'canUseSvg']) && $user->canUseSvg();
    }

    /**
     * @param FileWrapper $file
     * @param int         $userId
     * @param array       $extra
     * @return AttachmentData
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function insertDataFromFile(FileWrapper $file, $userId, array $extra = [])
    {
        $this->filename = $file->getFileName();

        if (!$file->isImage() && strtolower($file->getExtension()) === 'svg' && $this->canUseSvg())
        {
            $file = SvgFileWrapper::new($file->getFilePath(), $file->getFileName());
        }
        else if ($file->isImage() && $file->getImageType() === IMAGETYPE_JPEG && (\XF::options()->svAttachmentsStripExif ?? true))
        {
            // force a re-read of EXIF data
            FileWrapperUnwrapper::resetExifCache($file);
            $orientation = 0;
            // incase exif extension is being stupid
            $exif = $file->getExif();
            if (is_array($exif) && !empty($exif['IFD0']['Orientation']) && $exif['IFD0']['Orientation'] > 1)
            {
                $orientation = $exif['IFD0']['Orientation'];
            }
            $transformRequired = ($orientation > 1);

            $tempFile = $file->getFilePath();
            $image = \XF::app()->imageManager()->imageFromFile($tempFile);
            if ($image === null)
            {
                // not actually a valid JPEG image
                return parent::insertDataFromFile($file, $userId, $extra);
            }
            if ($transformRequired)
            {
                $image->transformByExif($orientation);
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
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function generateAttachmentThumbnail($sourceFile, &$width = null, &$height = null)
    {
        $newTempFile = parent::generateAttachmentThumbnail($sourceFile, $width, $height);
        if ($newTempFile === null)
        {
            $wrapper = SvgFileWrapper::new($sourceFile, $this->filename);

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
            if (empty($dimensions['thumbnail_width']) || empty($dimensions['thumbnail_height']))
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

            return null;
        }

        return $newTempFile;
    }
}