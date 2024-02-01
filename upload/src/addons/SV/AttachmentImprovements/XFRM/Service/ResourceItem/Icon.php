<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XFRM\Service\ResourceItem;

use SV\AttachmentImprovements\SvgFileWrapper;
use SV\AttachmentImprovements\SvgImage;
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
    /** @var string|null  */
    protected $uploadedFileName = null;
    /** @var bool */
    protected $isSvg = false;

    protected function canUseSvg(): bool
    {
        $user = \XF::visitor();
        return is_callable([$user, 'canUseSvg']) && $user->canUseSvg();
    }

    public function setImageFromUpload(Upload $upload)
    {
        if ($upload->getExtension() === 'svg' && $this->canUseSvg())
        {
            return $this->setSvgImageFromUpload($upload);
        }

        return parent::setImageFromUpload($upload);
    }

    public function updateIcon()
    {
        if ($this->type === SvgImage::IMAGETYPE_SVG)
        {
            $this->updateSvgIcon();
        }

        return parent::updateIcon();
    }

    public function setSvgImageFromUpload(Upload $upload): bool
    {
        $file = $upload->getFileWrapper();
        if (strtolower($file->getExtension()) !== 'svg')
        {
            return false;
        }

        $file = SvgFileWrapper::new($file->getFilePath(), $file->getFileName());
        $image = $file->getImageData();
        if ($image === null || !$image->isValid())
        {
            $this->error = \XF::phrase('sv_bad_svg_data')->render('raw');
            $this->fileName = null;

            return false;
        }

        $this->uploadedFileName = $file->getFileName();
        $this->fileName = $upload->getTempFile();
        $this->width = $image->getWidth();
        $this->height = $image->getHeight();
        $this->type = SvgImage::IMAGETYPE_SVG;

        return true;
    }

    public function updateSvgIcon(): void
    {
        if ($this->uploadedFileName === null)
        {
            return;
        }

        $targetSize = (int)$this->app->container('xfrmIconSizeMap')['m'];

        if ($this->width !== $targetSize || $this->height !== $targetSize)
        {
            $wrapper = SvgFileWrapper::new($this->fileName, $this->uploadedFileName);

            if ($wrapper->getImageType() !== SvgImage::IMAGETYPE_SVG)
            {
                return;
            }
            $image = $wrapper->getImageData();
            if ($image === null)
            {
                return;
            }

            $image->resize($targetSize, $targetSize);

            $newTempFile = File::getTempFile();
            if ($newTempFile && $image->save($newTempFile))
            {
                $this->width = $targetSize;
                $this->height = $targetSize;
                $this->fileName = $newTempFile;
            }
        }

        $this->resource->icon_ext = 'svg';
    }
}