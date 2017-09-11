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
                'service_is_flapping'           => 'ss.is_flapping'
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
