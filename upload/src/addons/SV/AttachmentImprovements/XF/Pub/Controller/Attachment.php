<?php

namespace SV\AttachmentImprovements\XF\Pub\Controller;

use SV\AttachmentImprovements\RequestUnwrapper;
use SV\AttachmentImprovements\XF\Entity\AttachmentData;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;
use function strlen, substr;

class Attachment extends XFCP_Attachment
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\AbstractReply
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionIndex(ParameterBag $params)
    {
        $eTag = (string)$this->request->getServer('HTTP_IF_NONE_MATCH', '');
        if (strlen($eTag) !== 0 && substr($eTag, 0, 2) === 'W/')
        {
            $_SERVER['HTTP_IF_NONE_MATCH'] = substr($eTag, 2);
            RequestUnwrapper::syncServerVar($this->request, 'HTTP_IF_NONE_MATCH');
        }

        $response = parent::actionIndex($params);
        if ($response instanceof View)
        {
            $method = $this->request->getRequestMethod();
            if ($method === 'head' || $method === 'get')
            {
                /** @var \XF\Entity\Attachment $attachment */
                $attachment = $response->getParam('attachment');
                /** @var AttachmentData $data */
                $data = $attachment->Data ?? null;
                if ($data !== null && $data->isRangeRequestSupported())
                {
                    $response->setParam('rangeSupport', true);
                    $rangeRequest = $this->request->getServer('HTTP_RANGE');
                    if ($rangeRequest !== false)
                    {
                        $response->setParam('rangeRequest', $rangeRequest);
                    }
                }
            }
        }

        return $response;
    }
}