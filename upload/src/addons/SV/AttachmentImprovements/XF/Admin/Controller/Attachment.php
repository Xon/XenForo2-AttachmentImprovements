<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XF\Admin\Controller;

use SV\AttachmentImprovements\RequestUnwrapper;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use function strlen, substr;

class Attachment extends XFCP_Attachment
{
    /**
     * @param ParameterBag $params
     * @return AbstractReply
     */
    public function actionIndex(ParameterBag $params)
    {
        $eTag = (string)$this->request->getServer('HTTP_IF_NONE_MATCH');
        if (strlen($eTag) !== 0 && substr($eTag, 0, 2) === 'W/')
        {
            $_SERVER['HTTP_IF_NONE_MATCH'] = substr($eTag, 2);
            RequestUnwrapper::syncServerVar($this->request, 'HTTP_IF_NONE_MATCH');
        }

        return parent::actionIndex($params);
    }
}