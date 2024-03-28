<?php
/**
 * @noinspection PhpUnusedParameterInspection
 */

namespace SV\AttachmentImprovements;

use XF\AddOn\AddOn;
use XF\Entity\AddOn as AddOnEntity;

abstract class InstallerListener
{
    protected static function applySchema(AddOn $addOn): void
    {
        if (!(Setup::$supportedAddOns[$addOn->getAddOnId()] ?? false))
        {
            return;
        }
        $setup = new Setup($addOn, \XF::app());
        $setup->applySchema();
    }


    public static function addonPostRebuild(AddOn $addOn, AddOnEntity $installedAddOn, array $json)
    {
        self::applySchema($addOn);
    }

    public static function addonPostInstall(AddOn $addOn, AddOnEntity $installedAddOn, array $json, array &$stateChanges)
    {
        self::applySchema($addOn);
    }
}