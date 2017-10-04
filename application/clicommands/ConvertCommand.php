<?php
/* TopLevelView module for Icingaweb2 - Copyright (c) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Clicommands;

use Icinga\Exception\ConfigurationError;
use Icinga\Module\Toplevelview\Command;
use Icinga\Module\Toplevelview\Config\ConfigEmitter;
use Icinga\Module\Toplevelview\ConfigStore;
use Icinga\Module\Toplevelview\Legacy\LegacyDbHelper;
use Zend_Db_Adapter_Pdo_Sqlite;

/**
 * Converts a TopLevelView database into the new YAML configuration format
 */
class ConvertCommand extends Command
{
    protected $dbConnections = array();

    public function init()
    {
        parent::init();

        if (! extension_loaded('pdo_sqlite')) {
            throw new ConfigurationError('You need the PHP extension "pdo_sqlite" in order to convert TopLevelView');
        }
    }

    /**
     * List all hierarchies in the database
     *
     * Arguments:
     *   --db <file>  SQLite3 data from from old TopLevelView module
     */
    public function listAction()
    {
        $dbFile = $this->params->getRequired('db');
        $db = $this->sqlite($dbFile);

        $helper = new LegacyDbHelper($db);
        foreach ($helper->fetchHierarchies() as $root) {
            printf("[%d] %s\n", $root['id'], $root['name']);
        }
    }

    /**
     * Generate a YAML config file for Icinga Web 2 module
     *
     * Arguments:
     *   --db <file>      SQLite3 data from from old TopLevelView module
     *   --id <id>        Database id to export (see list)
     *   --output <file>  Write to file (default '-' for stdout)
     *   --name <name>    If name is specified instead of file,
     *                    config is saved under that name
     */
    public function convertAction()
    {
        $dbFile = $this->params->getRequired('db');
        $db = $this->sqlite($dbFile);

        $id = $this->params->getRequired('id');

        $output = $this->params->get('output', null);
        $name = $this->params->get('name', null);
        $format = $this->params->get('format', 'yaml');

        $helper = new LegacyDbHelper($db, $this->monitoringBackend());
        $tree = $helper->fetchTree($id);

        $emitter = ConfigEmitter::fromLegacyTree($tree);

        if ($name !== null and $output === null) {
            $store = new ConfigStore();
            $store[$name] = $emitter->emitYAML($format);
            printf("Saved as config %s\n", $name);
            exit(0);
        }

        $text = $emitter->emit($format);
        if ($output === null || $output === '-') {
            echo $text;
        } else {
            file_put_contents($output, $text);
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
