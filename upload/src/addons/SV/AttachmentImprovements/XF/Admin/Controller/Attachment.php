<?php

namespace SV\AttachmentImprovements\XF\Admin\Controller;

use SV\AttachmentImprovements\RequestUnwrapper;
use XF\Mvc\ParameterBag;

class Attachment extends XFCP_Attachment
{
    public function actionIndex(ParameterBag $params)
    {
        $eTag = $this->request->getServer('HTTP_IF_NONE_MATCH');
        if ($eTag && substr($eTag, 0, 2) == 'W/')
        {
            $_SERVER['HTTP_IF_NONE_MATCH'] = substr($eTag, 2);
            RequestUnwrapper::syncServerVar($this->request, 'HTTP_IF_NONE_MATCH');
        }

        return parent::actionIndex($params);
    }
}