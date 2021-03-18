<?php
/* TopLevelView module for Icingaweb2 - Copyright (c) 2021 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Clicommands;

use Icinga\Exception\ConfigurationError;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Toplevelview\Command;
use Icinga\Module\Toplevelview\Legacy\LegacyDbHelper;
use Zend_Db_Adapter_Pdo_Sqlite;

/**
 * Tools for the legacy DB
 */
class LegacyCommand extends Command
{
    public function init()
    {
        parent::init();

        if (! extension_loaded('pdo_sqlite')) {
            throw new ConfigurationError('You need the PHP extension "pdo_sqlite" in order to convert TopLevelView');
        }
    }

    /**
     * Delete unreferenced objects from the database
     *
     * Arguments:
     *   --db <file>  SQLite3 data from from old TopLevelView module
     *   --noop       Only show counts, don't delete
     */
    public function cleanupAction()
    {
        $dbFile = $this->params->getRequired('db');
        $noop = $this->params->shift('noop');
        $db = $this->sqlite($dbFile);

        $helper = new LegacyDbHelper($db);

        $result = $helper->cleanupUnreferencedObjects($noop);
        foreach ($result as $type => $c) {
            printf("%s: %d\n", $type, $c);
        }
    }

    /**
     * Migrate database ids from an IDO to another IDO
     *
     * Arguments:
     *   --db <file>      SQLite3 data from from old TopLevelView module
     *   --target <file>  Target database path (will be overwritten)
     *   --old <backend>  OLD IDO backend (configured in monitoring module)
     *   --new <backend>  New IDO backend (configured in monitoring module) (optional)
     *   --purge          Remove unresolvable data during update (see log)
     */
    public function idomigrateAction()
    {
        $dbFile = $this->params->getRequired('db');
        $old = $this->params->getRequired('old');
        $target = $this->params->getRequired('target');
        $new = $this->params->get('new');
        $purge = $this->params->shift('purge');

        $db = $this->sqlite($dbFile);

        $helper = new LegacyDbHelper($db, MonitoringBackend::instance($new));
        $helper->setOldBackend(MonitoringBackend::instance($old));

        // Use the copy as db
        $helper->setDb($helper->copySqliteDb($db, $target));

        $result = $helper->migrateObjectIds(false, $purge);
        foreach ($result as $type => $c) {
            printf("%s: %d\n", $type, $c);
        }
    }

    /**
     * Sets up the Zend PDO resource for SQLite
     *
     * @param string $file
     *
     * @return Zend_Db_Adapter_Pdo_Sqlite
     */
    protected function sqlite($file)
    {
        return new Zend_Db_Adapter_Pdo_Sqlite(array(
            'dbname' => $file,
        ));
    }
}
