<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Monitoring;

use Icinga\Module\Monitoring\Backend\Ido\Query\HostgroupQuery as IcingaHostgroupQuery;

/**
 * Patched version of HostgroupQuery
 *
 * Changes:
 * - add service_notifications_enabled to servicestatus join
 */
class HostgroupQuery extends IcingaHostgroupQuery
{
    public function init()
    {
        $patchedColumnMap = array(
            'servicestatus' => array(
                'service_notifications_enabled' => 'ss.notifications_enabled',
                'service_is_flapping'           => 'ss.is_flapping',
                'service_state'                 => 'CASE WHEN ss.has_been_checked = 0 OR ss.has_been_checked IS NULL THEN 99 ELSE CASE WHEN ss.state_type = 1 THEN ss.current_state ELSE ss.last_hard_state END END'
            ),
            'hoststatus'    => array(
                'host_state' => 'CASE WHEN hs.has_been_checked = 0 OR hs.has_been_checked IS NULL THEN 99 ELSE CASE WHEN hs.state_type = 1 THEN hs.current_state ELSE hs.last_hard_state END END'
            )
        );

        foreach ($patchedColumnMap as $table => $columns) {
            foreach ($columns as $k => $v) {
                $this->columnMap[$table][$k] = $v;
            }
        }

        parent::init();
    }
}
