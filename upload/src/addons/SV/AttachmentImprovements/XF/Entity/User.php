<?php

namespace SV\AttachmentImprovements\XF\Entity;

/**
 * @extends \XF\Entity\User
 */
class User extends XFCP_User
{
    public function canUseSvg(): bool
    {
        if (!(\XF::options()->svAttachmentImprov_svgAdminOnly ?? false))
        {
            return true;
        }

        if ($this->is_admin)
        {
            return true;
        }

        return false;
    }
}