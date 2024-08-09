<?php

namespace Icinga\Module\Toplevelview\Clicommands;

use Icinga\Application\Icinga;
use Icinga\Application\Logger;
use Icinga\Cli\Command;
use Icinga\Exception\NotWritableError;
use Icinga\Util\DirectoryIterator;

/**
 * The cleanup command is used to remove data
 * from the Top Level View module.
 */
class CleanupCommand extends Command
{
    const FORMAT_YAML = 'yml';

    public function init(): void
    {
        Logger::getInstance()->setLevel(Logger::INFO);
    }

    /**
     * Load all directories in a given path.
     * @param string $path Path to the directory
     *
     * @return array List of fully qualified directory names
     */
    protected function listDirs(string $path): array
    {
        $result = [];
        foreach (new DirectoryIterator($path) as $p) {
            if (is_dir($p)) {
                $result[] = $p;
            }
        }
        return $result;
    }

    /**
     * Load all YML files in a given path.
     * @param string $path Path to the directory
     *
     * @return array List of fully qualified file names
     */
    protected function listYMLFiles(string $path): array
    {
        $result = [];
        foreach (new DirectoryIterator($path) as $file) {
            if (str_ends_with($file, self::FORMAT_YAML)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * Remove a list of files from the filesystem.
     * @param array $files list of files
     *
     * @return int Exit status of the operation
     */
    protected function removeFiles(array $files): int
    {
        $rc = 0;
        foreach ($files as $f) {
            try {
                unlink($f);
            } catch (NotWritableError $error) {
                Logger::error('Could not remove: %s', $f);
                $rc = 1;
            }
        }
        return $rc;
    }

    /**
     * Clean up older backups of Top Level View configuration files.
     *
     * This command removes the backups for one or all views.
     * By default it will remove all but one (the latest) backup.
     *
     * USAGE
     *
     * icingacli toplevelview cleanup backups [options]
     *
     * OPTIONS
     *   --keep                 Number of backups to keep after the cleanup
     *   --view                 Name of a view to remove backups for
     *
     * EXAMPLES
     *
     *   icingacli toplevelview cleanup backups
     *   icingacli toplevelview cleanup backups --keep 4
     *   icingacli toplevelview cleanup backups --view myview --keep 2
     */
    public function backupsAction(): void
    {
        $keep = (int) $this->params->get('keep', 1);
        $view = $this->params->get('view');

        // Validate CLI parameters
        if ($keep < 0) {
            $this->showUsage('backups');
            exit(1);
        }

        // Load the module's configuration directory
        $configDirModule = Icinga::app()
                           ->getModuleManager()
                           ->getModule('toplevelview')
                           ->getConfigDir();

        // Get all the backup directories for all views
        $configDir = $configDirModule . DIRECTORY_SEPARATOR . 'views';
        $backupDirs = $this->listDirs($configDir);

        // If the view is provided check if it exists and use it for the cleanup
        if (isset($view)) {
            $viewDir = $configDir . DIRECTORY_SEPARATOR . $view;
            if (!in_array($viewDir, $backupDirs)) {
                Logger::error('No such view available: %s', $view);
                exit(1);
            }
            $backupDirs = [$viewDir];
        }

        // Get all the .yml files and find out which to remove
        $rc = 0;
        foreach ($backupDirs as $dir) {
            $allFiles = $this->listYMLFiles($dir);
            $deleteThese = ($keep === 0) ? $allFiles : array_slice($allFiles, 0, -$keep);
            $rc = $this->removeFiles($deleteThese);
        }

        exit($rc);
    }
}
