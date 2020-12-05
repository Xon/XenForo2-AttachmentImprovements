<?php

namespace SV\AttachmentImprovements\XF\Pub\Controller;

use SV\AttachmentImprovements\RequestUnwrapper;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class Attachment extends XFCP_Attachment
{
    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\AbstractReply
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionIndex(ParameterBag $params)
    {
        $eTag = $this->request->getServer('HTTP_IF_NONE_MATCH');
        if ($eTag && substr($eTag, 0, 2) == 'W/')
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
                $response->setParam('rangeSupport', true);
                $rangeRequest = $this->request->getServer('HTTP_RANGE');
                if ($rangeRequest !== false)
                {
                    $response->setParam('rangeRequest', $rangeRequest);
                }
            }
        }

        return $response;
    }
}