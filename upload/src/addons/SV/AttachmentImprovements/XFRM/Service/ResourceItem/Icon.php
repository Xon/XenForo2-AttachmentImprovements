<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XFRM\Service\ResourceItem;

use SV\AttachmentImprovements\SvgFileWrapper;
use SV\AttachmentImprovements\SvgImage;
use SV\AttachmentImprovements\XF\Http\Upload as ExtendedUpload;
use SV\AttachmentImprovements\XFRM\Entity\ResourceItem;
use XF\Http\Upload;
use XF\Util\File;
use function is_callable;
use function strtolower;

/**
 * @extends \XFRM\Service\ResourceItem\Icon
 *
 * @property ResourceItem $resource
 */
class Icon extends XFCP_Icon
{
    /** @var SvgFileWrapper */
    protected $svFileWrapper = null;

    public function setImageFromUpload(Upload $upload)
    {
        if ($upload instanceof ExtendedUpload && $upload->canUseSvg())
        {
            if (!$upload->isValidFile($errors))
            {
                $this->error = reset($errors);
                return false;
            }
            /** @var SvgFileWrapper $wrapper */
            $wrapper = $upload->getFileWrapper();

            if ($wrapper->isSvgImage())
            {
                $this->svFileWrapper = $wrapper;

                return $this->setSvgImage($wrapper);
            }
        }

        return parent::setImageFromUpload($upload);
    }

    public function setImageFromExisting(): bool
    {
        $path = $this->resource->getAbstractedIconPath();
        if (!$this->app->fs()->has($path))
        {
            throw new \InvalidArgumentException("Resource does not have an icon ({$path})");
        }

        if ($this->resource->icon_ext === 'svg')
        {
            $tempFile = File::copyAbstractedPathToTempFile($path);

            $this->svFileWrapper = SvgFileWrapper::new($tempFile, basename($tempFile) . '.svg');
            return $this->setSvgImage($this->svFileWrapper);
        }

        return parent::setImageFromExisting();
    }

    public function updateIcon()
    {
        if ($this->type === SvgImage::IMAGETYPE_SVG)
        {
            return $this->updateSvgIcon();
        }

        return parent::updateIcon();
    }

    public function setImage($fileName)
    {
        if ($this->svFileWrapper !== null)
        {
            return $this->setSvgImage($this->svFileWrapper);
        }

        // ensure the icon isn't treated as a svg
        $this->resource->icon_ext = '';

        return parent::setImage($fileName);
    }

    public function setSvgImage(SvgFileWrapper $file): bool
    {
        $image = $file->getImageData();
        if ($image === null || !$image->isValid())
        {
            $this->error = \XF::phrase('sv_bad_svg_data')->render('raw');
            $this->fileName = null;

            return false;
        }

        $this->fileName = $file->getFilePath();
        $this->width = $image->getWidth();
        $this->height = $image->getHeight();
        $this->type = SvgImage::IMAGETYPE_SVG;

        return true;
    }

    public function updateSvgIcon(): bool
    {
        $wrapper = $this->svFileWrapper;
        if ($wrapper === null || !$wrapper->isSvgImage())
        {
            return false;
        }

        $targetSize = (int)$this->app->container('xfrmIconSizeMap')['m'];
        $outputFile = null;

        if ($this->width !== $targetSize || $this->height !== $targetSize)
        {
            $image = $wrapper->getImageData();
            if ($image === null)
            {
                return false;
            }

            $image->resize($targetSize, $targetSize);

            $newTempFile = File::getTempFile();
            if ($newTempFile && $image->save($newTempFile))
            {
                $this->width = $targetSize;
                $this->height = $targetSize;
                $outputFile = $newTempFile;
            }
        }
        else
        {
            $outputFile = $this->fileName;
        }

        if (!$outputFile)
        {
            throw new \RuntimeException("Failed to save image to temporary file; check internal_data/data permissions");
        }

        $this->resource->icon_ext = 'svg';
        $this->resource->icon_date = \XF::$time;

        $dataFile = $this->resource->getAbstractedIconPath();
        File::copyFileToAbstractedPath($outputFile, $dataFile);

        $this->resource->save();

        if ($this->logIp)
        {
            $ip = ($this->logIp === true ? $this->app->request()->getIp() : $this->logIp);
            $this->writeIpLog('update', $ip);
        }

        return true;
    }
}