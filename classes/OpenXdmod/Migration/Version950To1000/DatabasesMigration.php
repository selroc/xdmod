<?php
/**
 * Update database from version 9.5.0 To 10.0.0.
 */

namespace OpenXdmod\Migration\Version950To1000;

use OpenXdmod\Migration\DatabasesMigration as AbstractDatabasesMigration;
use OpenXdmod\Setup\Console;
use CCR\DB;
use ETL\Utilities;

/**
 * Migrate databases from version 9.5.0 to 10.0.0.
 */
class DatabasesMigration extends AbstractDatabasesMigration
{
    public function execute()
    {
        parent::execute();

        $dbh = DB::factory('datawarehouse');
        $mysql_helper = \CCR\DB\MySQLHelper::factory($dbh);
        $console = Console::factory();
        $pipelinesToRun = [];

        if ($mysql_helper->tableExists('mod_shredder.staging_storage_usage')) {
            $pipelinesToRun[] = 'storage-table-definition-update-9-5-0_10-0-0';
        }

        if ($mysql_helper->tableExists('modw_cloud.event')) {
            $pipelinesToRun[] = 'cloud-migration-9-5-0_10-0-0';
        }

        if ($mysql_helper->tableExists('modw_cloud.cloud_resource_specs')) {
            $pipelinesToRun[] = 'cloud-resource-specs-migration-9-5-0_10-0-0';
        }

        if ($mysql_helper->tableExists('modw_cloud.event')) {
            $pipelinesToRun[] = 'cloud-migration-innodb-9-5-0_10-0-0';
        }

        $console->displayMessage(<<<"EOT"
This version of Open XDMoD converts any table with the MyISAM engine to InnoDB. This converstion may take some time to complete.
EOT
        );

        foreach ($pipelinesToRun as $pipeline) {
            Utilities::runEtlPipeline(
                [$pipeline],
                $this->logger,
                ['last-modified-start-date' => '2017-01-01 00:00:00']
            );
        }
    }
}
