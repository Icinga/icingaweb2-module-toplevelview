<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview;

use Icinga\Application\Benchmark;
use Icinga\Application\Icinga;
use Icinga\Exception\NotImplementedError;
use Icinga\Exception\NotReadableError;
use Icinga\Exception\ProgrammingError;
use Icinga\Module\Toplevelview\Tree\TLVTree;
use Icinga\Util\DirectoryIterator;

class ViewConfig
{
    const FORMAT_YAML = 'yml';

    protected $config_dir;

    protected $name;

    protected $format;

    protected $file_path;

    protected $view;

    protected $raw;

    protected $tree;

    /**
     * Content of the file
     *
     * @var string
     */
    protected $text;

    public static function loadByName($name, $config_dir = null, $format = self::FORMAT_YAML)
    {
        $object = new static;
        $object
            ->setName($name)
            ->setConfigDir($config_dir)
            ->setFormat($format);

        return $object;
    }

    public static function loadAll($config_dir = null, $format = self::FORMAT_YAML)
    {
        $suffix = '.' . $format;

        $config_dir = static::configDir($config_dir);
        $directory = new DirectoryIterator($config_dir, $suffix);

        $views = array();
        foreach ($directory as $name => $path) {
            $name = basename($name, $suffix);
            $views[$name] = static::loadByName($name, $config_dir, $format);
        }

        return $views;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        if ($this->file_path === null) {
            $this->file_path = $this->config_dir . DIRECTORY_SEPARATOR . $this->name . '.' . $this->format;
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
     * @throws NotReadableError
     */
    public function load()
    {
        $file_path = $this->getFilePath();
        $this->text = file_get_contents($file_path);
        if ($this->text === false) {
            throw new NotReadableError('Could not read file %s', $file_path);
        }
        $this->view = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        if ($this->text === null) {
            $this->load();
        }
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
        return $this;
    }

    public function store()
    {
        // TODO: implement write to file
    }

    /**
     * @return string
     */
    public function getConfigDir()
    {
        return $this->config_dir;
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
        $data = array();
        foreach($this->raw as $key => $value) {
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
}
