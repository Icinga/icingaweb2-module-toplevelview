<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Application\Benchmark;
use Icinga\Exception\NotFoundError;

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

        $hostgroups = $root->getBackend()->select()
            ->from('hostgroupsummary', array(
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
                // TODO: service_notifications_enabled
                // TODO: service_notification_period
                // TODO: flapping?
            ))
            ->where('hostgroup_name', $names);

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

            if (($date = $this->root->getFetched($this->type, $key)) !== null) {
                $status->zero();

                $data = $this->root->registeredObjects[$this->type][$key];

                foreach ($data as $k => $v) {
                    $n = preg_split('~_~', $k, 3);

                    if ($n[0] === 'hosts') {
                        // TODO: host is never unhandled in old TLV...
                        $handled = '_handled';
                    } else {
                        if (count($n) > 2 && $n[2] === 'handled') {
                            $handled = '_handled';
                        } else {
                            $handled = '_unhandled';
                        }
                    }

                    $state = $n[1];
                    if ($state === 'total') {
                        $status->add('total', $v);
                    } elseif ($state === 'up' || $state === 'ok') {
                        $status->add('ok', $v);
                    } elseif ($state === 'down' || $state === 'unreachable' || $state === 'critical') {
                        $status->add('critical' . $handled, $v);
                    } elseif ($state === 'warning') {
                        $status->add('warning' . $handled, $v);
                    } elseif ($state === 'unknown') {
                        $status->add('unknown' . $handled, $v);
                    }
                }
            } else {
                $status->add('missing', 1);
            }
        }
        return $this->status;
    }
}
