<?php
/* TopLevelView module for Icingaweb2 - Copyright (c) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Legacy;

use Icinga\Application\Benchmark;
use Icinga\Exception\NotFoundError;
use Icinga\Exception\ProgrammingError;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use stdClass;
use Zend_Db_Adapter_Pdo_Abstract;

class LegacyDbHelper
{
    /** @var Zend_Db_Adapter_Pdo_Abstract */
    protected $db;

    /** @var MonitoringBackend */
    protected $backend;

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
                'p.id = ?', $root_id
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

            if (
                property_exists($node, 'host_object_id')
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
}
