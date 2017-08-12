<?php

namespace SV\AttachmentImprovements\XF\Pub\View\Attachment;

use SV\AttachmentImprovements\Helper\Attachment as AttachmentHelper;
use XF\Db\Exception;
use XF\Util\File;

class View extends XFCP_View
{
	public function renderRaw()
	{
		$attachment = $this->params['attachment'];

		if (!headers_sent() && function_exists('header_remove'))
		{
			header_remove('Expires');
			header('Cache-control: private');
		}

		$extension = File::getFileExtension($attachment->filename);
		$imageTypes = [
			'svg' => 'image/svg+xml',
			'gif' => 'image/gif',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'png' => 'image/png'
		];

		if (isset($imageTypes[$extension]) && ($attachment['width'] && $attachment['height']))
		{
			$this->response->header('Content-type', $imageTypes[$extension], true);
			$this->response->setDownloadFileName($attachment['filename'], true);
		}
		else
		{
			$this->response->header('Content-type', 'application/octet-stream', true);
			$this->response->setDownloadFileName($attachment['filename']);
		}

		$this->response->header('ETag', '"' . $attachment['attach_date'] . '"', true);
		$this->response->header('Content-Length', $attachment['file_size'], true);
		$this->response->header('X-Content-Type-Options', 'nosniff');

		$attachmentFile = $attachment->Data->getAbstractedDataPath();

		// I really do not like XF2's abstracted file system. It's too complicated for add-ons :(
		// TODO: Figure out how to get the internal data path of the file.

		$options = \XF::app()->options();
		if ($options->SV_AttachImpro_XAR)
		{
			if (AttachmentHelper::convertFilename($attachmentFile))
			{
				if (\XF::$debugMode && $options->SV_AttachImpro_log)
				{
					\XF::app()->logException(new Exception('X-Accel-Redirect:' . $attachmentFile));
				}
				$this->response->header('X-Accel-Redirect', $attachmentFile);

				return '';
			}
			if (\XF::$debugMode && $options->SV_AttachImpro_log)
			{
				\XF::app()->logException(new Exception('X-Accel-Redirect skipped'));
			}
		}

		return parent::renderRaw();
	}
}