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
        $hostgroups = new Hostgroupsummary($root->getBackend(), array(
            'hostgroup_name',
            'hosts_down_handled',
            'hosts_down_unhandled',
            'hosts_total',
            'hosts_unreachable_handled',
            'hosts_unreachable_unhandled',
            'hosts_up',
            'services_critical_handled',
            'services_critical_unhandled',
            'services_ok',
            'services_total',
            'services_unknown_handled',
            'services_unknown_unhandled',
            'services_warning_handled',
            'services_warning_unhandled'
        ));
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

                // TODO: host is never unhandled in old TLV...
                $status->set(
                    'critical_handled',
                    $data->hosts_down_handled + $data->hosts_down_unhandled
                    + $data->hosts_unreachable_handled + $data->hosts_unreachable_unhandled
                    + $data->services_critical_handled
                );
                $status->set('critical_unhandled', $data->services_critical_unhandled);

                $status->set('warning_handled', $data->services_warning_handled);
                $status->set('warning_unhandled', $data->services_warning_unhandled);
                $status->set('unknown_handled', $data->services_unknown_handled);
                $status->set('unknown_unhandled', $data->services_unknown_unhandled);

                $status->set('missing', 0);
            } else {
                $status->add('missing', 1);
            }
        }
        return $this->status;
    }
}
