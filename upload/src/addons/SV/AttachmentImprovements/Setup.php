<?php

namespace SV\AttachmentImprovements;

use SV\StandardLib\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;

class Setup extends AbstractSetup
{
    use InstallerHelper;
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    /** @var array<string,bool> */
    public static $supportedAddOns = [
        'XFRM' => true,
    ];

    public function installStep1(): void
    {
        $this->applySchema();
    }


    public function uninstallStep1(): void
    {
        $sm = $this->schemaManager();

        foreach ($this->getRemoveAlterTables() as $tableName => $callback)
        {
            if ($sm->tableExists($tableName))
            {
                $sm->alterTable($tableName, $callback);
            }
        }
    }

    public function postRebuild(): void
    {
        parent::postRebuild();
        $this->applySchema();
    }

    public function applySchema() : void
    {
        $sm = $this->schemaManager();

        foreach ($this->getAlterTables() as $tableName => $callback)
        {
            if ($sm->tableExists($tableName))
            {
                $sm->alterTable($tableName, $callback);
            }
        }
    }

    protected function getAlterTables(): array
    {
        $tables = [];

        $tables['xf_rm_resource'] = function (Alter $table) {
            $this->addOrChangeColumn($table, 'icon_ext', 'varchar', 5)->nullable(true)->setDefault(null);
        };

        return $tables;
    }

    protected function getRemoveAlterTables(): array
    {
        $tables = [];

        $tables['xf_rm_resource'] = function (Alter $table) {
            $table->dropColumns('icon_ext');
        };

        return $tables;
    }
}