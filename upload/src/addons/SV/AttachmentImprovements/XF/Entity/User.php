<?php

namespace SV\AttachmentImprovements\XF\Entity;

use function preg_split;
use function strtolower;
use function trim;

/**
 * @extends \XF\Entity\User
 */
class User extends XFCP_User
{
    public function canUseSvg(): bool
    {
        $options = \XF::options();

        if (($options->svAttachmentImprov_svgAdminOnly ?? false) && !$this->is_admin)
        {
            return false;
        }

        $extensions = preg_split('/\s+/', strtolower(trim($options->attachmentExtensions ?? '')), -1, PREG_SPLIT_NO_EMPTY);
        if (!in_array('svg', $extensions, true))
        {
            return false;
        }

        return true;
    }
}