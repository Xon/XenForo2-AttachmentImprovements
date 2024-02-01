<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\AttachmentImprovements\XFRM\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;
use function floor;
use function preg_replace;
use function sprintf;

/**
 * @extends \XFRM\Entity\ResourceItem
 * @property ?string $icon_ext
 */
class ResourceItem extends XFCP_ResourceItem
{
    public function getAbstractedIconPath($sizeCode = null)
    {
        $path = parent::getAbstractedIconPath($sizeCode);

        if ($this->icon_ext !== null)
        {
            $path = preg_replace('#\.jpg$#', '.'.$this->icon_ext, $path, 1);
        }

        return $path;
    }

    public function getIconUrl($sizeCode = null, $canonical = false)
    {
        $path = parent::getIconUrl($sizeCode, $canonical);

        if ($this->icon_ext !== null &&  $path !== null)
        {
            $path = preg_replace('#\.jpg(\?\d+)$#', '.' . $this->icon_ext . '$1', $path, 1);
        }

        return $path;
    }

    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['icon_ext'] = ['type' => self::STR, 'maxLength' => 4, 'nullable' => true, 'default' => null];
    
        return $structure;
    }
}