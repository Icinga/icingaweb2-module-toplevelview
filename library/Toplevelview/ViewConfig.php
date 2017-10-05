<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview;

use Icinga\Application\Benchmark;
use Icinga\Application\Icinga;
use Icinga\Exception\InvalidPropertyException;
use Icinga\Exception\NotImplementedError;
use Icinga\Exception\NotReadableError;
use Icinga\Exception\NotWritableError;
use Icinga\Exception\ProgrammingError;
use Icinga\Module\Toplevelview\Tree\TLVTree;
use Icinga\Util\DirectoryIterator;
use Icinga\Web\Session;

class ViewConfig
{
    const FORMAT_YAML = 'yml';
    const SESSION_PREFIX = 'toplevelview_view_';

    protected $config_dir;

    protected $name;

    protected $format;

    protected $file_path;

    protected $view;

    protected $raw;

    protected $tree;

    protected $hasBeenLoaded = false;
    protected $hasBeenLoadedFromSession = false;

    /**
     * Content of the file
     *
     * @var string
     */
    protected $text;

    /**
     * @param             $name
     * @param string|null $config_dir
     * @param string      $format
     *
     * @return static
     */
    public static function loadByName($name, $config_dir = null, $format = self::FORMAT_YAML)
    {
        $object = new static;
        $object
            ->setName($name)
            ->setConfigDir($config_dir)
            ->setFormat($format)
            ->load();

        return $object;
    }

    /**
     * @param string|null $config_dir
     * @param string      $format
     *
     * @return static[]
     */
    public static function loadAll($config_dir = null, $format = self::FORMAT_YAML)
    {
        $suffix = '.' . $format;

        $config_dir = static::configDir($config_dir);
        $directory = new DirectoryIterator($config_dir, $suffix);

        $views = array();
        foreach ($directory as $name => $path) {
            if (is_dir($path)) {
                // no not descend and ignore directories
                continue;
            }
            $name = basename($name, $suffix);
            $views[$name] = static::loadByName($name, $config_dir, $format);
        }

        // try to load from session
        $len = strlen(self::SESSION_PREFIX);
        foreach (static::session()->getAll() as $k => $v) {
            if (substr($k, 0, $len) === self::SESSION_PREFIX) {
                $name = substr($k, $len);
                if (! array_key_exists($name, $views)) {
                    $views[$name] = static::loadByName($name, $config_dir, $format);
                }
            }
        }

        ksort($views);

        return $views;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        if ($this->file_path === null) {
            if ($this->format === null) {
                throw new ProgrammingError('format not set!');
            }
            $this->file_path = $this->getConfigDir() . DIRECTORY_SEPARATOR . $this->name . '.' . $this->format;
        }
        return $this->file_path;
    }

    /**
     * @param string $file_path
     *
     * @return $this
     */
    public function setFilePath($file_path)
    {
        $this->file_path = $file_path;
        return $this;
    }

    /**
     * @return $this
     */
    public function load()
    {
        if ($this->text === null) {
            $this->loadFromSession();
        }
        if ($this->text === null) {
            $this->loadFromFile();
        }
        return $this;
    }

    public function loadFromFile()
    {
        $file_path = $this->getFilePath();
        $this->text = file_get_contents($file_path);
        if ($this->text === false) {
            throw new NotReadableError('Could not read file %s', $file_path);
        }
        $this->view = null;
        $this->hasBeenLoadedFromSession = false;
        $this->hasBeenLoaded = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        $this->raw = null;
        $this->tree = null;
        return $this;
    }

    protected function storeBackup($force = false)
    {
        $backupDir = $this->getConfigBackupDir();

        if (! file_exists($backupDir) && mkdir($backupDir) !== true) {
            throw new NotWritableError(
                'Config backup directory did not exit, and it could not be created: %s',
                $backupDir
            );
        }

        $ts = (string) time();
        $backup = $backupDir . DIRECTORY_SEPARATOR . $ts . '.' . $this->format;

        if (file_exists($backup)) {
            throw new ProgrammingError('History file with timestamp already present: %s', $backup);
        }

        $existingFile = $this->getFilePath();
        $oldText = file_get_contents($existingFile);
        if ($oldText === false) {
            throw new NotReadableError('Could not read file %s', $existingFile);
        }

        // only save backup if changed or forced
        if ($force || $oldText !== $this->text) {
            if (file_put_contents($backup, $oldText) === false) {
                throw new NotWritableError('Could not save backup to %s', $backup);
            }
        }
    }

    public function store()
    {
        $file_path = $this->getFilePath();

        // ensure to save history
        if (file_exists($file_path)) {
            $this->storeBackup();
        }

        $status = file_put_contents($file_path, $this->text);
        if ($status === false) {
            throw new NotWritableError('Could not write file %s', $file_path);
        }
        $this->clearSession();
        return $this;
    }

    /**
     * @return string
     * @throws ProgrammingError When dir is not yet set
     */
    public function getConfigDir()
    {
        if ($this->config_dir === null) {
            throw new ProgrammingError('config_dir not yet set!');
        }
        return $this->config_dir;
    }

    /**
     * @return string
     */
    public function getConfigBackupDir()
    {
        return $this->getConfigDir() . DIRECTORY_SEPARATOR . $this->name;
    }

    /**
     * @param string $config_dir
     *
     * @return $this
     * @throws NotReadableError
     */
    public function setConfigDir($config_dir = null)
    {
        $this->config_dir = static::configDir($config_dir);
        $this->file_path = null;
        return $this;
    }

    public static function configDir($config_dir = null)
    {
        if ($config_dir === null) {
            $config_dir = Icinga::app()->getModuleManager()->getModule('toplevelview')->getConfigDir() .
                DIRECTORY_SEPARATOR . 'views';
        }

        if (! is_readable($config_dir)) {
            throw new NotReadableError('Could not open config dir %s', $config_dir);
        }
        return $config_dir;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->file_path = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        $this->file_path = null;
        return $this;
    }

    public function getMeta($key)
    {
        $this->ensureParsed();
        if ($key !== 'children' && array_key_exists($key, $this->raw)) {
            return $this->raw[$key];
        } else {
            return null;
        }
    }

    public function setMeta($key, $value)
    {
        if ($key === 'children') {
            throw new ProgrammingError('You can not edit children here!');
        }
        $this->raw[$key] = $value;
        return $this;
    }

    public function getMetaData()
    {
        $this->ensureParsed();
        $data = array();
        foreach ($this->raw as $key => $value) {
            if ($key !== 'children') {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    protected function ensureParsed()
    {
        if ($this->raw === null) {
            Benchmark::measure('Begin parsing YAML document');

            $text = $this->getText();
            if ($text === null) {
                // new ViewConfig
                $this->raw = array();
            } elseif ($this->format == self::FORMAT_YAML) {
                // TODO: use stdClass instead of Array?
                $this->raw = yaml_parse($text);
                if (! is_array($this->raw)) {
                    throw new InvalidPropertyException('Could not parse YAML config!');
                }
            } else {
                throw new NotImplementedError("Unknown format '%s'", $this->format);
            }

            Benchmark::measure('Finished parsing YAML document');
        }
    }

    /**
     * Loads the Tree for this configuration
     *
     * @return TLVTree
     */
    public function getTree()
    {
        if ($this->tree === null) {
            $this->ensureParsed();
            $this->tree = $tree = TLVTree::fromArray($this->raw);
            $tree->setConfig($this);
        }
        return $this->tree;
    }

    protected function getSessionVarName()
    {
        return self::SESSION_PREFIX . $this->name;
    }

    public static function session()
    {
        // TODO: is this CLI safe?
        return Session::getSession();
    }

    public function loadFromSession()
    {
        if (($sessionConfig = $this->session()->get($this->getSessionVarName())) !== null) {
            $this->text = $sessionConfig;
            $this->hasBeenLoadedFromSession = true;
            $this->hasBeenLoaded = true;
        }
        return $this;
    }

    public function clearSession()
    {
        $this->session()->delete($this->getSessionVarName());
    }

    public function storeToSession()
    {
        $this->session()->set($this->getSessionVarName(), $this->text);
    }

    /**
     * @return bool
     */
    public function hasBeenLoadedFromSession()
    {
        return $this->hasBeenLoadedFromSession;
    }

    /**
     * @return bool
     */
    public function hasBeenLoaded()
    {
        return $this->hasBeenLoaded;
    }

    public function __clone()
    {
        $this->name = null;
        $this->raw = null;
        $this->tree = null;

        $this->hasBeenLoaded = false;
        $this->hasBeenLoadedFromSession = false;
    }

    public function delete()
    {
        $file_path = $this->getFilePath();

        $this->clearSession();

        if (file_exists($file_path)) {
            $this->storeBackup(true);
            unlink($file_path);
        }

        return $this;
    }
}
