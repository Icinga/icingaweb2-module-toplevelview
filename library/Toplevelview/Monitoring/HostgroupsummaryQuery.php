<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Monitoring;

use Icinga\Module\Monitoring\Backend\Ido\Query\HostgroupsummaryQuery as IcingaHostgroupsummaryQuery;
use Zend_Db_Expr;
use Zend_Db_Select;

/**
 * Patched version of HostgroupsummaryQuery
 */
class HostgroupsummaryQuery extends IcingaHostgroupsummaryQuery
{
    public function init()
    {
        $serviceOutDowntime = 'service_notifications_enabled = 1 AND service_in_downtime = 0 AND service_in_notification_period = 1';
        $serviceInDowntime = '(service_notifications_enabled = 0 OR service_in_downtime = 1 OR service_in_notification_period = 0)';
        $hostOutDowntime = 'host_notifications_enabled = 1 AND host_in_downtime = 0';
        $hostInDowntime = '(host_notifications_enabled = 0 OR host_in_downtime = 1)';
        $patchServicesHandled = "(service_handled = 1 OR service_is_flapping = 1) AND $serviceOutDowntime";
        $patchServicesUnhandled = "service_handled = 0 AND service_is_flapping = 0 AND $serviceOutDowntime";
        $patchHostsHandled = "(host_handled = 1 OR host_is_flapping = 1) AND $hostOutDowntime";
        $patchHostsUnhandled = "host_handled = 0 AND host_is_flapping = 0 AND $hostOutDowntime";

        $patchedColumnMap = array(
            'hostgroupsummary' => array(
                'hosts_down_handled'          => "SUM(CASE WHEN host_state = 1 AND $patchHostsHandled THEN 1 ELSE 0 END)",
                'hosts_down_unhandled'        => "SUM(CASE WHEN host_state = 1 AND $patchHostsUnhandled THEN 1 ELSE 0 END)",
                'hosts_unreachable_handled'   => "SUM(CASE WHEN host_state = 2 AND $patchHostsHandled THEN 1 ELSE 0 END)",
                'hosts_unreachable_unhandled' => "SUM(CASE WHEN host_state = 2 AND $patchHostsUnhandled THEN 1 ELSE 0 END)",
                'hosts_downtime_handled'      => "SUM(CASE WHEN host_state != 0 AND $hostInDowntime THEN 1 ELSE 0 END)",
                'hosts_downtime_active'       => "SUM(CASE WHEN $hostInDowntime THEN 1 ELSE 0 END)",
                'services_critical_handled'   => "SUM(CASE WHEN service_state = 2 AND $patchServicesHandled THEN 1 ELSE 0 END)",
                'services_critical_unhandled' => "SUM(CASE WHEN service_state = 2 AND $patchServicesUnhandled THEN 1 ELSE 0 END)",
                'services_unknown_handled'    => "SUM(CASE WHEN service_state = 3 AND $patchServicesHandled THEN 1 ELSE 0 END)",
                'services_unknown_unhandled'  => "SUM(CASE WHEN service_state = 3 AND $patchServicesUnhandled THEN 1 ELSE 0 END)",
                'services_warning_handled'    => "SUM(CASE WHEN service_state = 1 AND $patchServicesHandled THEN 1 ELSE 0 END)",
                'services_warning_unhandled'  => "SUM(CASE WHEN service_state = 1 AND $patchServicesUnhandled THEN 1 ELSE 0 END)",
                'services_downtime_handled'   => "SUM(CASE WHEN service_state != 0 AND $serviceInDowntime THEN 1 ELSE 0 END)",
                'services_downtime_active'    => "SUM(CASE WHEN $serviceInDowntime THEN 1 ELSE 0 END)",
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
                'host_notifications_enabled',
                'host_state',
                'host_is_flapping',
                'host_in_downtime',
                'service_handled'                => new Zend_Db_Expr('NULL'),
                'service_state'                  => new Zend_Db_Expr('NULL'),
                'service_notifications_enabled'  => new Zend_Db_Expr('NULL'),
                'service_in_notification_period' => new Zend_Db_Expr('NULL'),
                'service_is_flapping'            => new Zend_Db_Expr('NULL'),
                'service_in_downtime'            => new Zend_Db_Expr('NULL'),
            )
        );
        $this->subQueries[] = $hosts;
        $services = $this->createSubQuery(
            'Hostgroup',
            array(
                'hostgroup_alias',
                'hostgroup_name',
                'host_handled'               => new Zend_Db_Expr('NULL'),
                'host_state'                 => new Zend_Db_Expr('NULL'),
                'host_notifications_enabled' => new Zend_Db_Expr('NULL'),
                'host_is_flapping'           => new Zend_Db_Expr('NULL'),
                'host_in_downtime'           => new Zend_Db_Expr('NULL'),
                'service_handled',
                'service_state',
                'service_notifications_enabled',
                'service_in_notification_period',
                'service_is_flapping',
                'service_in_downtime',
            )
        );
        $this->subQueries[] = $services;
        $this->summaryQuery = $this->db->select()->union(array($hosts, $services), Zend_Db_Select::SQL_UNION_ALL);
        $this->select->from(array('hostgroupsummary' => $this->summaryQuery), array());
        $this->group(array('hostgroup_name', 'hostgroup_alias'));
        $this->joinedVirtualTables['hostgroupsummary'] = true;
    }
}
