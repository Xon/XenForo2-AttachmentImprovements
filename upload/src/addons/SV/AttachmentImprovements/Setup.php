<?php

namespace SV\AttachmentImprovements;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;


class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	public function upgrade2000700Step1()
    {
        /** @var \XF\Entity\Option $option */
        $option = \XF::app()->finder('XF:Option')->whereId('attachmentImageExtensions')->fetchOne();
        if ($option)
        {
            if ($option->option_value == '1')
            {
                $option->option_value = $option->default_value;
                $option->save();
            }
        }
    }
}