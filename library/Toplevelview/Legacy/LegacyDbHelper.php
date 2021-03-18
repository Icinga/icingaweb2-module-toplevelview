<?php
/* TopLevelView module for Icingaweb2 - Copyright (c) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Legacy;

use Icinga\Application\Benchmark;
use Icinga\Application\Logger;
use Icinga\Exception\IcingaException;
use Icinga\Exception\NotFoundError;
use Icinga\Exception\ProgrammingError;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use stdClass;
use Zend_Db_Adapter_Pdo_Abstract;
use Zend_Db_Adapter_Pdo_Sqlite;

class LegacyDbHelper
{
    /** @var Zend_Db_Adapter_Pdo_Abstract */
    protected $db;

    /** @var MonitoringBackend */
    protected $backend;

    /** @var MonitoringBackend */
    protected $oldBackend;

    protected static $idoObjectIds = [
        'host'      => 1,
        'service'   => 2,
        'hostgroup' => 3,
    ];

    public function __construct(Zend_Db_Adapter_Pdo_Abstract $db, MonitoringBackend $backend = null)
    {
        $this->db = $db;
        $this->backend = $backend;
    }

    public function fetchHierarchies()
    {
        $query = $this->db->select()
            ->from('toplevelview_view_hierarchy AS h', array(
                'id',
            ))
            ->joinLeft('toplevelview_view AS v', 'v.id = h.view_id', array(
                'name',
                'display_name',
            ))
            ->where('h.level = ?', 0)
            ->where('h.root_id = h.id');

        return $this->db->fetchAll($query);
    }

    /**
     * Purges stale object references from the database
     *
     * Apparently the original editor replaces the tree data,
     * but leaves unreferenced objects where the view_id has
     * no referenced row in toplevelview_view.
     *
     * @param bool $noop Only check but don't delete
     *
     * @return array object types with counts cleaned up
     */
    public function cleanupUnreferencedObjects($noop = false)
    {
        $results = [
            'host'      => 0,
            'hostgroup' => 0,
            'service'   => 0
        ];

        foreach (array_keys($results) as $type) {
            $query = $this->db->select()
                ->from("toplevelview_${type} AS o", ['id'])
                ->joinLeft('toplevelview_view AS v', 'v.id = o.view_id', [])
                ->where('v.id IS NULL');

            Logger::debug("searching for unreferenced %s objects: %s", $type, (string) $query);

            $ids = $this->db->fetchCol($query);
            $results[$type] = count($ids);

            if (! $noop) {
                Logger::debug("deleting unreferenced %s objects: %s", $type, json_encode($ids));
                $this->db->delete("toplevelview_${type}", sprintf('id IN (%s)', join(', ', $ids)));
            }
        }

        return $results;
    }

    /**
     * Migrate object ids from an old MonitoringBackend to a new one
     *
     * Since data is not stored as names, we need to lookup a name for each id,
     * and get the new id from the other backend.
     *
     * @param bool $noop
     *
     * @return int[]
     */
    public function migrateObjectIds($noop = false)
    {
        $result = [
            'host'      => 0,
            'service'   => 0,
            'hostgroup' => 0,
        ];

        foreach (array_keys($result) as $type) {
            $query = $this->db->select()
                ->from("toplevelview_${type}", ['id', "${type}_object_id AS object_id"]);

            Logger::debug("querying stored objects of type %s: %s", $type, (string) $query);

            $objects = [];

            // Load objects indexed by object_id
            foreach ($this->db->fetchAll($query) as $row) {
                $objects[$row['object_id']] = (object) $row;
            }

            // Load names from old DB
            $idoObjects = $this->oldBackend->getResource()->select()
                ->from('icinga_objects', ['object_id', 'name1', 'name2'])
                ->where('objecttype_id', self::$idoObjectIds[$type]);

            // Amend objects with names from old DB
            foreach ($idoObjects->fetchAll() as $row) {
                $id = $row->object_id;
                if (array_key_exists($id, $objects)) {
                    $idx = $row->name1;
                    if ($row->name2 !== null) {
                        $idx .= '!' . $row->name2;
                    }

                    $objects[$id]->name = $idx;
                }
            }

            // Load names from new DB and index by name
            $newObjects = [];
            foreach ($this->backend->getResource()->fetchAll($idoObjects) as $row) {
                $idx = $row->name1;
                if ($row->name2 !== null) {
                    $idx .= '!' . $row->name2;
                }

                $newObjects[$idx] = $row;
            }

            // Process all objects and store new id
            $errors = 0;
            foreach ($objects as $object) {
                if (! property_exists($object, 'name')) {
                    Logger::error("object %s %d has not been found in old IDO", $type, $object->object_id);
                    $errors++;
                } else if (! array_key_exists($object->name, $newObjects)) {
                    Logger::error("object %s %d '%s' has not been found in new IDO",
                        $type, $object->object_id, $object->name);
                    $errors++;
                } else {
                    $object->new_object_id = $newObjects[$object->name]->object_id;
                    $result[$type]++;
                }
            }

            if ($errors > 0) {
                throw new IcingaException("errors have occured during IDO id migration - see log");
            }

            if (! $noop) {
                foreach ($objects as $object) {
                    $this->db->update(
                        "toplevelview_${type}",
                        ["${type}_object_id" => $object->new_object_id],
                        ['id', $object->id]
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Sqlite $db
     * @param string                     $target
     *
     * @return Zend_Db_Adapter_Pdo_Sqlite
     */
    public function copySqliteDb(Zend_Db_Adapter_Pdo_Sqlite $db, $target)
    {
        // Lock database for copy
        $db->query('PRAGMA locking_mode = EXCLUSIVE');
        $db->query('BEGIN EXCLUSIVE');

        $file = $db->getConfig()['dbname'];
        if (! copy($file, $target)) {
            throw new IcingaException("could not copy '%s' to '%s'", $file, $target);
        }

        $db->query('COMMIT');
        $db->query('PRAGMA locking_mode = NORMAL');

        return new Zend_Db_Adapter_Pdo_Sqlite([
            'dbname' => $target,
        ]);
    }

    protected function fetchDatabaseHierarchy($root_id)
    {
        $query = $this->db->select()
            ->from('toplevelview_view_hierarchy AS p', array())
            ->joinInner(
                'toplevelview_view_hierarchy AS n',
                'n.root_id = p.root_id AND (n.lft BETWEEN p.lft AND p.rgt) AND n.level >= p.level',
                array('id', 'level')
            )->joinInner(
                'toplevelview_view AS v',
                'v.id = n.view_id',
                array('name', 'display_name')
            )->joinLeft(
                'toplevelview_host AS h',
                'h.view_id = v.id',
                array('h.host_object_id')
            )->joinLeft(
                'toplevelview_service AS s',
                's.view_id = v.id',
                array('s.service_object_id')
            )->joinLeft(
                'toplevelview_hostgroup AS hg',
                'hg.view_id = v.id',
                array('hg.hostgroup_object_id')
            )->where(
                'p.id = ?',
                $root_id
            )->group(array(
                'n.root_id',
                'n.lft',
                // 'n.id',
                'h.host_object_id',
                's.service_object_id',
                'hg.hostgroup_object_id',
            ))->order(array(
                'n.lft',
                'hg.hostgroup_object_id',
                'h.host_object_id',
                's.service_object_id',
            ));

        $nodes = $this->db->fetchAll($query);

        if (empty($nodes)) {
            throw new NotFoundError('Could not find tree for root_id %d', $root_id);
        }

        return $nodes;
    }

    protected function buildTree($root_id, $nodes, &$hosts, &$services, &$hostgroups)
    {
        /** @var stdClass $tree */
        $tree = null;
        /** @var stdClass $currentParent */
        $currentParent = null;
        $currentNode = null;
        $currentLevel = null;
        $currentId = null;
        $chain = array();

        $currentHostId = null;

        $hosts = array();
        $hostgroups = array();
        $services = array();

        foreach ($nodes as $node) {
            $node = (object) $node;
            $node->id = (int) $node->id;
            $node->level = (int) $node->level;

            if ($currentId === null || $currentId !== $node->id) {
                // only add the node once (all hosts, services and hostgroups are attached on the same node)
                $chain[$node->level] = $node;

                if ($tree === null || $node->id === $root_id) {
                    $currentParent = $tree = $node;

                    // minor tweak: remove "top level view" from title
                    $newTitle = preg_replace('/^Top\s*Level\s*View\s*/i', '', $tree->name);
                    if (strlen($newTitle) > 4) {
                        $tree->name = $tree->display_name = $newTitle;
                    }

                    // add old default behavior for status
                    $tree->host_never_unhandled = true;
                    $tree->notification_periods = true;
                    $tree->ignored_notification_periods = ['notification_none']; // migration for Director
                } elseif ($node->level > $currentLevel) {
                    // level down
                    $currentParent = $chain[$node->level - 1];

                    if (! property_exists($currentParent, 'children')) {
                        $currentParent->children = array();
                    }
                    $currentParent->children[] = $node;
                } elseif ($node->level === $currentLevel) {
                    // same level
                    $currentParent->children[] = $node;
                } elseif ($node->level < $currentLevel) {
                    // level up
                    $currentParent = $chain[$node->level - 1];
                    $currentParent->children[] = $node;
                }

                if ($node->name === $node->display_name) {
                    unset($node->display_name);
                }

                $currentId = $node->id;
                $currentNode = $node;

                // clear current host when node changes
                $currentHostId = null;

                // remove unused values
                unset($node->id);
                unset($node->level);
            }

            if (property_exists($node, 'host_object_id')
                && $node->host_object_id !== null
                && $currentHostId !== $node->host_object_id
            ) {
                $currentHostId = $node->host_object_id;

                $host = new stdClass;
                $host->host = 'UNKNOWN_HOST_' . $node->host_object_id;
                $host->type = 'host';
                $host->object_id = $node->host_object_id;

                if (! property_exists($currentNode, 'children')) {
                    $currentNode->children = array();
                }

                $currentNode->children['host_' . $node->host_object_id] = $host;
                $hosts[$node->host_object_id][] = $host;
            }
            unset($currentNode->host_object_id);

            if (property_exists($node, 'service_object_id') && $node->service_object_id !== null) {
                $service = new stdClass;
                $service->host = 'UNKNOWN_HOST';
                $service->service = 'UNKNOWN_SERVICE_' . $node->service_object_id;
                $service->type = 'service';
                $service->object_id = $node->service_object_id;

                if (! property_exists($currentNode, 'children')) {
                    $currentNode->children = array();
                }
                $currentNode->children['hostservice_' . $node->service_object_id] = $service;
                $services[$node->service_object_id][] = $service;
            }
            unset($currentNode->service_object_id);

            if (property_exists($node, 'hostgroup_object_id') && $node->hostgroup_object_id !== null) {
                $hostgroup = new stdClass;
                $hostgroup->hostgroup = 'UNKNOWN_HOSTGROUP_' . $node->hostgroup_object_id;
                $hostgroup->type = 'hostgroup';
                $hostgroup->object_id = $node->hostgroup_object_id;

                if (! property_exists($currentNode, 'children')) {
                    $currentNode->children = array();
                }
                $currentNode->children['hostgroup_' . $node->hostgroup_object_id] = $hostgroup;
                $hostgroups[$node->hostgroup_object_id][] = $hostgroup;
            }
            unset($currentNode->hostgroup_object_id);
        }

        return $tree;
    }

    public function fetchTree($root_id)
    {
        Benchmark::measure('fetchTree: begin');

        $nodes = $this->fetchDatabaseHierarchy($root_id);

        Benchmark::measure('fetchTree: fetchAll done');

        $tree = $this->buildTree($root_id, $nodes, $hosts, $services, $hostgroups);

        Benchmark::measure('fetchTree: done building tree');

        if (! empty($hosts)) {
            $hostNames = $this->fetchHosts(array_keys($hosts));
            foreach ($hosts as $objectId => $nodes) {
                if (array_key_exists($objectId, $hostNames)) {
                    foreach ($nodes as $node) {
                        $node->host = $hostNames[$objectId];
                    }
                }
            }
        }

        Benchmark::measure('fetchTree: done getting host info');

        if (! empty($services)) {
            $icingaServices = $this->fetchServices(array_keys($services));
            foreach ($services as $objectId => $nodes) {
                if (array_key_exists($objectId, $icingaServices)) {
                    foreach ($nodes as $node) {
                        $s = $icingaServices[$objectId];
                        $node->host = $s->host_name;
                        $node->service = $s->service_name;
                    }
                }
            }
        }

        Benchmark::measure('fetchTree: done getting service info');

        if (! empty($hostgroups)) {
            $icingaHostgroups = $this->fetchHostgroups(array_keys($hostgroups));
            foreach ($hostgroups as $objectId => $nodes) {
                if (array_key_exists($objectId, $icingaHostgroups)) {
                    foreach ($nodes as $node) {
                        $node->hostgroup = $icingaHostgroups[$objectId];
                    }
                }
            }
        }

        Benchmark::measure('fetchTree: done getting service info');

        return $tree;
    }

    protected function fetchHosts($ids)
    {
        return $this->monitoringBackend()->getResource()->select()
            ->from('icinga_objects', array(
                'object_id',
                'name1',
            ))
            ->where('object_id', $ids)
            ->where('objecttype_id', 1)
            ->fetchPairs();
    }

    protected function fetchServices($ids)
    {
        $rows = $this->monitoringBackend()->getResource()->select()
            ->from('icinga_objects', array(
                'object_id'    => 'object_id',
                'host_name'    => 'name1',
                'service_name' => 'name2',
            ))
            ->where('object_id', $ids)
            ->where('objecttype_id', 2)
            ->fetchAll();

        $services = array();
        foreach ($rows as $row) {
            $services[$row->object_id] = $row;
        }
        return $services;
    }

    protected function fetchHostgroups($ids)
    {
        return $this->monitoringBackend()->getResource()->select()
            ->from('icinga_objects', array(
                'object_id',
                'name1',
            ))
            ->where('object_id', $ids)
            ->where('objecttype_id', 3)
            ->fetchPairs();
    }

    protected function monitoringBackend()
    {
        if ($this->backend === null) {
            throw new ProgrammingError('monitoringBackend has not been set at runtime!');
        }
        return $this->backend;
    }

    /**
     * @param MonitoringBackend $oldBackend
     *
     * @return LegacyDbHelper
     */
    public function setOldBackend(MonitoringBackend $oldBackend)
    {
        $this->oldBackend = $oldBackend;
        return $this;
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Sqlite $db
     *
     * @return $this
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
}
