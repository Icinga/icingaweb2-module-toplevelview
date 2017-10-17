<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Application\Benchmark;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Toplevelview\Monitoring\Hostgroupsummary;

class TLVHostGroupNode extends TLVIcingaNode
{
    protected $type = 'hostgroup';

    protected $key = 'hostgroup';

    protected static $titleKey = 'hostgroup';

    public static function fetch(TLVTree $root)
    {
        Benchmark::measure('Begin fetching hostgroups');

        if (! array_key_exists('hostgroup', $root->registeredObjects) or empty($root->registeredObjects['hostgroup'])) {
            throw new NotFoundError('No hostgroups registered to fetch!');
        }

        $names = array_keys($root->registeredObjects['hostgroup']);

        // Note: this uses a patched version of Hostsgroupsummary / HostgroupsummaryQuery !
        $hostgroups = new Hostgroupsummary(
            $root->getBackend(),
            array(
                'hostgroup_name',
                'hosts_down_handled',
                'hosts_down_unhandled',
                'hosts_total',
                'hosts_unreachable_handled',
                'hosts_unreachable_unhandled',
                'hosts_downtime_handled',
                'hosts_downtime_active',
                'hosts_up',
                'services_critical_handled',
                'services_critical_unhandled',
                'services_ok',
                'services_total',
                'services_unknown_handled',
                'services_unknown_unhandled',
                'services_warning_handled',
                'services_warning_unhandled',
                'services_downtime_handled',
                'services_downtime_active',
            ),
            $root->get('notification_periods'),
            $root->get('host_never_unhandled')
        );

        $hostgroups->where('hostgroup_name', $names);

        foreach ($hostgroups as $hostgroup) {
            $root->registeredObjects['hostgroup'][$hostgroup->hostgroup_name] = $hostgroup;
        }

        Benchmark::measure('Finished fetching hostgroups');
    }

    public function getStatus()
    {
        if ($this->status === null) {
            $this->status = $status = new TLVStatus();
            $key = $this->getKey();

            if (($data = $this->root->getFetched($this->type, $key)) !== null) {
                $status->set('total', $data->hosts_total + $data->services_total);
                $status->set('ok', $data->hosts_up + $data->services_ok);

                $status->set('critical_handled', $data->services_critical_handled);
                $status->set('critical_unhandled', $data->services_critical_unhandled);

                if ($this->getRoot()->get('host_never_unhandled') === true) {
                    $status->add(
                        'critical_handled',
                        $data->hosts_down_handled
                        + $data->hosts_unreachable_handled
                        + $data->hosts_down_unhandled
                        + $data->hosts_unreachable_unhandled
                    );
                } else {
                    $status->add(
                        'critical_handled',
                        $data->hosts_down_handled
                        + $data->hosts_unreachable_handled
                    );
                    $status->add(
                        'critical_unhandled',
                        $data->hosts_down_unhandled
                        + $data->hosts_unreachable_unhandled
                    );
                }

                $status->set('warning_handled', $data->services_warning_handled);
                $status->set('warning_unhandled', $data->services_warning_unhandled);
                $status->set('unknown_handled', $data->services_unknown_handled);
                $status->set('unknown_unhandled', $data->services_unknown_unhandled);

                $status->set(
                    'downtime_handled',
                    $data->hosts_downtime_handled
                    + $data->services_downtime_handled
                );
                $status->set(
                    'downtime_active',
                    $data->hosts_downtime_active
                    + $data->services_downtime_active
                );

                // extra metadata for view
                $status->setMeta('hosts_total', $data->hosts_total);
                $status->setMeta(
                    'hosts_unhandled',
                    $data->hosts_down_unhandled
                    + $data->hosts_unreachable_unhandled
                );

                $status->set('missing', 0);
            } else {
                $status->add('missing', 1);
            }
        }
        return $this->status;
    }
}
