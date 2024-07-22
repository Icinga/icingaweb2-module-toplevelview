<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Model\View;
use Icinga\Module\Toplevelview\Util\Auth;

use Icinga\Application\Icinga;
use Icinga\Exception\NotWritableError;
use Icinga\Exception\NotReadableError;
use Icinga\Util\DirectoryIterator;
use Icinga\Web\Session;

/**
 * Manages the View's configurations, loads and stores Views.
 */
class ViewConfig
{
    use Auth;

    const FORMAT_YAML = 'yml';
    const SESSION_PREFIX = 'toplevelview_view_';

    /**
     * The module's configuration directory
     * @var string
     */
    protected $config_dir;

    public function __construct()
    {
        // Ensure the Views configuration directory exists
        $config_dir_module = Icinga::app()
                           ->getModuleManager()
                           ->getModule('toplevelview')
                           ->getConfigDir();

        $config_dir = $config_dir_module . DIRECTORY_SEPARATOR . 'views';
        $this->ensureDirExists($config_dir_module);
        $this->ensureDirExists($config_dir);
        // Set the configuration directory
        $this->config_dir = $config_dir;
    }

    /**
     * getConfigDir returns the configuration directory
     *
     * @return string
     * @throws ProgrammingError When dir is not yet set
     */
    public function getConfigDir(): string
    {
        if ($this->config_dir === null) {
            throw new ProgrammingError('Configuration directory does not exit');
        }
        return $this->config_dir;
    }

    /**
     * ensureDirExists checks if a given path exists and creates the path if it doesn't
     *
     * @param string $path Path to create the directory at
     * @param string $mode Mode to create the directory with
     */
    protected function ensureDirExists($path, $mode = '2770'): void
    {
        if (file_exists($path)) {
            return;
        }

        if (mkdir($path) !== true) {
            throw new NotWritableError(
                'Configuration directory does not exit, and it could not be created: %s',
                $path
            );
        }

        $octalMode = intval($mode, 8);
        if ($mode !== null && false === @chmod($path, $octalMode)) {
            throw new NotWritableError('Failed to set file mode "%s" on file "%s"', $mode, $path);
        }
    }

    /**
     * loadFromSession loads a View stored in the user's session
     *
     * @param string $name name of the View
     * @param string $format format of the View
     * @return ?View
     */
    protected function loadFromSession($name, $format): ?View
    {
        // Try to load data from the session
        $sessionConfig = Session::getSession()->get(self::SESSION_PREFIX . $name);
        // If there is none, we return
        if ($sessionConfig === null) {
            return null;
        }
        // If there is data, create the View with the data
        $view = (new View($name, $format))->setText($sessionConfig);
        $view->hasBeenLoadedFromSession = true;
        $view->hasBeenLoaded = true;

        return $view;
    }

    /**
     * loadFromFile loads a View stored in a configuration file
     *
     * @param string $name name of the View
     * @param string $format format of the View
     * @return ?View
     */
    protected function loadFromFile($name, $format): ?View
    {
        // Try to load the data from the file
        $file_path = $this->getConfigDir() . DIRECTORY_SEPARATOR . $name . '.' . $format;
        $text = file_get_contents($file_path);
        // Throw error if we cannot read it
        if ($text === false) {
            throw new NotReadableError('Could not read file %s', $file_path);
        }
        // If there is data, create the View with the data
        $view = (new View($name, $format))->setText($text);
        $view->hasBeenLoadedFromSession = false;
        $view->hasBeenLoaded = true;

        return $view;
    }

    /**
     * writeFile writes the given content to a given path.
     * Used to store the View's YAML content.
     *
     * @param $path Path to the file
     * @param $content Content of the file
     * @param $mode Mode of the new file
     */
    protected function writeFile($path, $content, $mode = '0660'): void
    {
        $existing = file_exists($path);
        if (file_put_contents($path, $content) === false) {
            throw new NotWritableError('Could not save to %s', $path);
        }

        if ($existing === false) {
            $octalMode = intval($mode, 8);
            if ($mode !== null && false === @chmod($path, $octalMode)) {
                throw new NotWritableError('Failed to set file mode "%s" on file "%s"', $mode, $path);
            }
        }
    }

    /**
     * Load a View by its name
     *
     * @param string $name Name of the view to load
     * @param string $format The format of the view
     * @param ipl\Stdlib\Filter $restrictions Filter that represents the restriction
     *
     * @return ?View
     */
    public function loadByName($name, $format = self::FORMAT_YAML, $restrictions = null): ?View
    {
        // If restrictions are set, check if the user has access to view the View
        if (isset($restrictions)) {
            if (!$this->hasAccessToView($restrictions, $name)) {
                return null;
            }
        }

        // Try to load from session
        $view = $this->loadFromSession($name, $format);

        if (isset($view)) {
            return $view;
        }

        // Try to load the view from the file
        $view = $this->loadFromFile($name, $format);

        return $view;
    }

    /**
     * loadAll loads and returns all available Views.
     *
     * @param string|null $config_dir
     * @param string      $format
     *
     * @return View[]
     */
    public function loadAll($format = self::FORMAT_YAML): array
    {
        $suffix = '.' . $format;
        $restrictions = $this->getRestrictions('toplevelview/filter/views');
        $views = array();

        // Load the YAML files for the Views from the config directory
        $directory = new DirectoryIterator($this->config_dir, $suffix);

        foreach ($directory as $name => $path) {
            if (is_dir($path)) {
                // Do not descend and ignore directories
                continue;
            }
            $name = basename($name, $suffix);
            $view = $this->loadByName($name, $format, $restrictions);

            if (isset($view)) {
                $views[$name] = $view;
            }
        }

        // Try to load View from the session
        $len = strlen(self::SESSION_PREFIX);

        foreach (Session::getSession()->getAll() as $k => $v) {
            if (substr($k, 0, $len) === self::SESSION_PREFIX) {
                $name = substr($k, $len);
                if (! array_key_exists($name, $views)) {
                    $view = $this->loadByName($name, $format, $restrictions);

                    if (isset($view)) {
                        $views[$name] = $view;
                    }
                }
            }
        }
        // Sort and return the views
        ksort($views);

        return $views;
    }

    /**
     * storeToSession stores a View's text to the user's session
     *
     * @param $view
     */
    public function storeToSession($view): void
    {
        Session::getSession()->set(self::SESSION_PREFIX . $view->getName(), $view->getText());
    }

    /**
     * clearSession removes a view from the user's session
     *
     * @param $view
     */
    public function clearSession($view): void
    {
        Session::getSession()->delete(self::SESSION_PREFIX . $view->getName());
    }

    /**
     * storeToFile stores a View to its configuration file
     *
     * @param $view
     */
    public function storeToFile($view): void
    {
        $file_path = $this->getConfigDir() . DIRECTORY_SEPARATOR . $view->getName() . '.' . $view->getFormat();
        // Store a backup of the existing config
        if (file_exists($file_path)) {
            $this->storeBackup($view);
        }
        // Write the content to the file and clear the session
        $this->writeFile($file_path, $view->getText());
        $this->clearSession($view);
    }

    /**
     * delete removes a Views configuration file
     *
     * @param $view
     */
    public function delete($view): void
    {
        $file_path = $this->getConfigDir() . DIRECTORY_SEPARATOR . $view->getName() . '.' . $view->getFormat();

        $this->clearSession($view);

        if (file_exists($file_path)) {
            $this->storeBackup($view, true);
            unlink($file_path);
        }
    }

    /**
     * storeBackup stores a timestamped backup file of a View's file,
     * if the content has changed
     *
     * @param $view
     * @param $force Stores a backup even if the content hasn't changed
     */
    protected function storeBackup($view, $force = false): void
    {
        $backup_dir = $this->getConfigDir() . DIRECTORY_SEPARATOR . $view->getName();
        $this->ensureDirExists($backup_dir);

        $ts = (string) time();
        $backup = $backup_dir . DIRECTORY_SEPARATOR . $ts . '.' . $view->getFormat();

        if (file_exists($backup)) {
            throw new ProgrammingError('History file with timestamp already present: %s', $backup);
        }

        $existing_file = $this->getConfigDir() . DIRECTORY_SEPARATOR . $view->getName() . '.' . $view->getFormat();
        $oldText = file_get_contents($existing_file);

        if ($oldText === false) {
            throw new NotReadableError('Could not read file %s', $existing_file);
        }

        // Only store a backup if the text changed or forced is set to true
        if ($force || $oldText !== $view->getText()) {
            $this->writeFile($backup, $oldText);
        }
    }
}
