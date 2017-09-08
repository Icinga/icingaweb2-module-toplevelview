<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Monitoring;

use Icinga\Module\Monitoring\Backend\Ido\Query\HostgroupsummaryQuery as IcingaHostgroupsummaryQuery;
use Zend_Db_Expr;
use Zend_Db_Select;

/**
 * Patched version of HostgroupsummaryQuery
 *
 * Changes:
 * - join service_notifications_enabled from status tables
 * - modify handled logic to include disabled notifications and flapping
 * - use patched version of HostgroupQuery for subQueries
 */
class HostgroupsummaryQuery extends IcingaHostgroupsummaryQuery
{
    public function init()
    {
        // TODO: service_notification_period

        $patchHandled = '(service_handled = 1 OR service_notifications_enabled = 0 OR service_is_flapping = 1)';
        $patchUnhandled = 'service_handled = 0 AND service_notifications_enabled = 1 AND service_is_flapping = 0';

        $patchedColumnMap = array(
            'hostgroupsummary' => array(
                'services_critical_handled'   => "SUM(CASE WHEN service_state = 2 AND $patchHandled THEN 1 ELSE 0 END)",
                'services_critical_unhandled' => "SUM(CASE WHEN service_state = 2 AND $patchUnhandled THEN 1 ELSE 0 END)",
                'services_unknown_handled'    => "SUM(CASE WHEN service_state = 3 AND $patchHandled THEN 1 ELSE 0 END)",
                'services_unknown_unhandled'  => "SUM(CASE WHEN service_state = 3 AND $patchUnhandled THEN 1 ELSE 0 END)",
                'services_warning_handled'    => "SUM(CASE WHEN service_state = 1 AND $patchHandled THEN 1 ELSE 0 END)",
                'services_warning_unhandled'  => "SUM(CASE WHEN service_state = 1 AND $patchUnhandled THEN 1 ELSE 0 END)",
            )
        );

        foreach ($patchedColumnMap as $table => $columns) {
            foreach ($columns as $k => $v) {
                $this->columnMap[$table][$k] = $v;
            }
        }
        parent::init();
    }

    protected function createSubQuery($queryName, $columns = array())
    {
        if ($queryName === 'Hostgroup') {
            // use locally patched query
            return new HostgroupQuery($this->ds, $columns);
        } else {
            return parent::createSubQuery($queryName, $columns);
        }
    }

    protected function joinBaseTables()
    {
        $this->countQuery = $this->createSubQuery(
            'Hostgroup',
            array()
        );
        $hosts = $this->createSubQuery(
            'Hostgroup',
            array(
                'hostgroup_alias',
                'hostgroup_name',
                'host_handled',
                'host_state',
                'service_handled'               => new Zend_Db_Expr('NULL'),
                'service_state'                 => new Zend_Db_Expr('NULL'),
                'service_notifications_enabled' => new Zend_Db_Expr('NULL'),
                'service_is_flapping'           => new Zend_Db_Expr('NULL'),
            )
        );
        $this->subQueries[] = $hosts;
        $services = $this->createSubQuery(
            'Hostgroup',
            array(
                'hostgroup_alias',
                'hostgroup_name',
                'host_handled' => new Zend_Db_Expr('NULL'),
                'host_state'   => new Zend_Db_Expr('NULL'),
                'service_handled',
                'service_state',
                'service_notifications_enabled',
                'service_is_flapping'
            )
        );
        $this->subQueries[] = $services;
        $this->summaryQuery = $this->db->select()->union(array($hosts, $services), Zend_Db_Select::SQL_UNION_ALL);
        $this->select->from(array('hostgroupsummary' => $this->summaryQuery), array());
        $this->group(array('hostgroup_name', 'hostgroup_alias'));
        $this->joinedVirtualTables['hostgroupsummary'] = true;
    }
}
