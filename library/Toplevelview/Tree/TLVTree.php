<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Application\Logger;
use Icinga\Exception\IcingaException;
use Icinga\Exception\NotFoundError;
use Icinga\Exception\ProgrammingError;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Util\Json;
use Icinga\Web\FileCache;
use stdClass;

class TLVTree extends TLVTreeNode
{
    protected static $titleKey = 'name';

    public $registeredTypes = array();

    public $registeredObjects = array();

    protected $fetchedData = array();

    protected $fetched = false;

    protected $fetchTime;

    protected $cacheLifetime = 60;

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

    protected function getCacheName()
    {
        $config = $this->getConfig();
        return sprintf(
            '%s-%s.json',
            $config->getName(),
            $config->getTextChecksum()
        );
    }

    protected function loadCache()
    {
        $cacheName = $this->getCacheName();
        try {
            $cache = FileCache::instance('toplevelview');
            $currentTime = time();
            $newerThan = $currentTime - $this->getCacheLifetime();

            if ($cache->has($cacheName, $newerThan)) {
                $cachedData = Json::decode($cache->get($cacheName));

                if (
                    property_exists($cachedData, 'data')
                    && $cachedData->data !== null
                    && property_exists($cachedData, 'ts')
                    && $cachedData->ts <= $currentTime // too new maybe
                    && $cachedData->ts > $newerThan // too old
                ) {
                    foreach ($cachedData->data as $type => $objects) {
                        $this->registeredObjects[$type] = (array) $objects;
                        $this->fetchedData[$type] = true;
                    }

                    $this->fetchTime = $cachedData->ts;
                    $this->fetched = true;
                }
            }
        } catch (IcingaException $e) {
            Logger::error('Could not load from toplevelview cache %s: %s', $cacheName, $e->getMessage());
        }
    }

    protected function storeCache()
    {
        $cacheName = $this->getCacheName();
        try {
            $cache = FileCache::instance('toplevelview');

            $cachedData = new stdClass;
            $cachedData->ts = $this->fetchTime;
            $cachedData->data = $this->registeredObjects;

            $cache->store($cacheName, Json::encode($cachedData));
        } catch (IcingaException $e) {
            Logger::error('Could not store to toplevelview cache %s: %s', $cacheName, $e->getMessage());
        }
    }

    protected function fetchType($type)
    {
        if (! array_key_exists($type, $this->registeredTypes)) {
            throw new ProgrammingError('Type %s has not been registered', $type);
        }

        if (! array_key_exists($type, $this->fetchedData)) {
            /** @var TLVIcingaNode $class */
            $class = $this->registeredTypes[$type];
            $class::fetch($this);
            $this->fetchedData[$type] = true;
        }

        return $this;
    }

    protected function ensureFetched()
    {
        if ($this->fetched !== true) {
            $this->loadCache();

            if ($this->fetched !== true) {
                foreach (array_keys($this->registeredTypes) as $type) {
                    $this->fetchType($type);
                }

                $this->fetchTime = time();
                $this->fetched = true;

                $this->storeCache();
            }
        }

        return $this;
    }

    public function getFetched($type, $key)
    {
        $this->ensureFetched();

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

    /**
     * @return int time
     */
    public function getFetchTime()
    {
        $this->ensureFetched();

        return $this->fetchTime;
    }

    /**
     * @return int seconds
     */
    public function getCacheLifetime()
    {
        return $this->cacheLifetime;
    }

    /**
     * @param int $cacheLifetime In seconds
     *
     * @return $this
     */
    public function setCacheLifetime($cacheLifetime)
    {
        $this->cacheLifetime = $cacheLifetime;
        return $this;
    }
}
