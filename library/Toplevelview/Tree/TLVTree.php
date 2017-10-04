<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Exception\NotFoundError;
use Icinga\Exception\ProgrammingError;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Toplevelview\ViewConfig;

class TLVTree extends TLVTreeNode
{
    protected static $titleKey = 'name';

    public $registeredTypes = array();

    public $registeredObjects = array();

    protected $fetchedData = array();

    protected $fetched = false;

    /**
     * @var MonitoringBackend
     */
    protected $backend;

    /**
     * @var ViewConfig
     */
    protected $config;

    public function getById($id)
    {
        $ids = explode('-', $id);
        $currentNode = $this;

        foreach ($ids as $i) {
            $children = $currentNode->getChildren();
            if (! empty($children) && array_key_exists($i, $children)) {
                $currentNode = $children[$i];
            } else {
                throw new NotFoundError(
                    'Could not find ID %s after %s for path %s',
                    $i,
                    $currentNode->getFullId(),
                    $id
                );
            }
        }

        return $currentNode;
    }

    /**
     * @return ViewConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ViewConfig $config
     *
     * @return $this
     */
    public function setConfig(ViewConfig $config)
    {
        $this->config = $config;
        return $this;
    }

    public function registerObject($type, $name, $class)
    {
        if (array_key_exists($type, $this->registeredTypes) && $this->registeredTypes[$type] !== $class) {
            throw new ProgrammingError(
                'Tried to register the same type by multiple classes: %s - %s - %s',
                $type,
                $this->registeredTypes[$type],
                $class
            );
        }

        $this->registeredTypes[$type] = $class;
        $this->registeredObjects[$type][$name] = null;
    }

    public function fetchType($type)
    {
        if (! array_key_exists($type, $this->registeredTypes)) {
            throw new ProgrammingError('Type %s has not been registered', $type);
        }

        if (! array_key_exists($type, $this->fetchedData)) {
            /** @var TLVIcingaNode $class */
            $class = $this->registeredTypes[$type];
            $this->fetchedData[$type] = $class::fetch($this);
        }

        return $this;
    }

    public function fetch()
    {
        foreach (array_keys($this->registeredTypes) as $type) {
            $this->fetchType($type);
        }

        return $this;
    }

    public function getFetched($type, $key)
    {
        if ($this->fetched !== true) {
            $this->fetch();
        }
        if (
            array_key_exists($key, $this->registeredObjects[$type])
            && $this->registeredObjects[$type][$key] !== null
        ) {
            return $this->registeredObjects[$type][$key];
        } else {
            return null;
        }
    }

    public function getBackend()
    {
        return $this->backend;
    }

    public function setBackend(MonitoringBackend $backend)
    {
        $this->backend = $backend;
        return $this;
    }
}
