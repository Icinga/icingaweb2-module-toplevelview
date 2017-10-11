<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Monitoring;

use Icinga\Module\Monitoring\Backend\Ido\Query\HostgroupQuery as IcingaHostgroupQuery;

/**
 * Patched version of HostgroupQuery
 */
class HostgroupQuery extends IcingaHostgroupQuery
{
    public function init()
    {
        $patchedColumnMap = array(
            'servicestatus' => array(
                'service_notifications_enabled'  => 'ss.notifications_enabled',
                'service_is_flapping'            => 'ss.is_flapping',
                'service_state'                  => 'CASE WHEN ss.has_been_checked = 0 OR ss.has_been_checked IS NULL THEN 99 ELSE CASE WHEN ss.state_type = 1 THEN ss.current_state ELSE ss.last_hard_state END END',
                'service_handled'                => 'CASE WHEN (ss.problem_has_been_acknowledged + COALESCE(hs.current_state, 0)) > 0 THEN 1 ELSE 0 END',
                'service_in_downtime'            => 'CASE WHEN (ss.scheduled_downtime_depth = 0) THEN 0 ELSE 1 END',
            ),
            'hoststatus'    => array(
                'host_notifications_enabled' => 'hs.notifications_enabled',
                'host_is_flapping'           => 'hs.is_flapping',
                'host_state'                 => 'CASE WHEN hs.has_been_checked = 0 OR hs.has_been_checked IS NULL THEN 99 ELSE CASE WHEN hs.state_type = 1 THEN hs.current_state ELSE hs.last_hard_state END END',
                'host_handled'               => 'CASE WHEN hs.problem_has_been_acknowledged > 0 THEN 1 ELSE 0 END',
                'host_in_downtime'           => 'CASE WHEN (hs.scheduled_downtime_depth = 0) THEN 0 ELSE 1 END',
            ),
            'servicenotificationperiod' => array(
                'service_notification_period'    => 'ntpo.name1',
                'service_in_notification_period' => 'CASE WHEN ntpr.timeperiod_id IS NOT NULL THEN 1 ELSE 0 END',
            ),
        );

        foreach ($patchedColumnMap as $table => $columns) {
            foreach ($columns as $k => $v) {
                $this->columnMap[$table][$k] = $v;
            }
        }

        parent::init();
    }

    protected function joinServicenotificationperiod()
    {
        $this->select->joinLeft(
            array('ntp' => $this->prefix . 'timeperiods'),
            'ntp.timeperiod_object_id = s.notification_timeperiod_object_id AND ntp.config_type = 1 AND ntp.instance_id = s.instance_id',
            array()
        );
        $this->select->joinLeft(
            array('ntpo' => $this->prefix . 'objects'),
            'ntpo.object_id = s.notification_timeperiod_object_id',
            array()
        );
        $this->select->joinLeft(
            array('ntpr' => $this->prefix . 'timeperiod_timeranges'),
            'ntpr.timeperiod_id = ntp.timeperiod_id 
                AND ntpr.day = DAYOFWEEK(UTC_DATE())
                AND ntpr.start_sec <= UNIX_TIMESTAMP() - UNIX_TIMESTAMP(UTC_DATE())
                AND ntpr.end_sec >= UNIX_TIMESTAMP() - UNIX_TIMESTAMP(UTC_DATE())
            ',
            array()
        );
    }
}
