<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Application\Benchmark;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Icingadb\Model\Hostgroupsummary;
use ipl\Stdlib\Filter;
use stdClass;

/**
 * TLVHostGroupNode represents a Hostgroup in the tree
 */
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

        $hgFilter = Filter::any();
        foreach (array_keys($root->registeredObjects['hostgroup']) as $name) {
            $hgFilter->add(Filter::equal('hostgroup_name', $name));
        }

        $hostgroups = Hostgroupsummary::on($root->getDb());

        $hostgroups->filter($hgFilter);

        foreach ($hostgroups as $hostgroup) {
            // TODO We cannot store the ORM Models with json_encore
            // Thus I'm converting things to objects that can be stored
            // Maybe there's a better way? iterator_to_array does not work.
            $hg = new stdClass;
            $hg->hosts_total = $hostgroup->hosts_total;
            $hg->hosts_up = $hostgroup->hosts_up;
            $hg->hosts_total = $hostgroup->hosts_total;
            $hg->services_total = $hostgroup->services_total;
            $hg->hosts_up = $hostgroup->hosts_up;
            $hg->services_ok = $hostgroup->services_ok;
            $hg->hosts_down_handled = $hostgroup->hosts_down_handled;
            $hg->hosts_down_unhandled = $hostgroup->hosts_down_unhandled;
            $hg->services_warning_handled = $hostgroup->services_warning_handled;
            $hg->services_warning_unhandled = $hostgroup->services_warning_unhandled;
            $hg->services_critical_handled = $hostgroup->services_critical_handled;
            $hg->services_critical_unhandled = $hostgroup->services_critical_unhandled;
            $hg->services_unknown_handled = $hostgroup->services_unknown_handled;
            $hg->services_unknown_unhandled = $hostgroup->services_unknown_unhandled;

            $root->registeredObjects['hostgroup'][$hostgroup->name] = $hg;
        }

        Benchmark::measure('Finished fetching hostgroups');
    }

    /**
     * getStatus returns the current status for the Hostgroup
     *
     * @return TLVStatus
     */
    public function getStatus(): TLVStatus
    {
        if ($this->status !== null) {
            return $this->status;
        }

        $this->status = $status = new TLVStatus();
        $key = $this->getKey();

        $hostgroup = $this->root->getFetched($this->type, $key);

        if ($hostgroup === null) {
            $this->status->add('missing', 1);
            return $this->status;
        }

        $status->set('total', $hostgroup->hosts_total + $hostgroup->services_total);
        $status->set('ok', $hostgroup->hosts_up + $hostgroup->services_ok);

        $status->set('critical_handled', $hostgroup->services_critical_handled);
        $status->set('critical_unhandled', $hostgroup->services_critical_unhandled);

        // Override the host status to handled if the option is set
        if ($this->getRoot()->get('override_host_problem_to_handled') === true) {
            $status->add(
                'critical_handled',
                $hostgroup->hosts_down_handled
                + $hostgroup->hosts_down_unhandled
            );
        } else {
            $status->add(
                'critical_handled',
                $hostgroup->hosts_down_handled
            );
            $status->add(
                'critical_unhandled',
                $hostgroup->hosts_down_unhandled
            );
        }

        $status->set('warning_handled', $hostgroup->services_warning_handled);
        $status->set('warning_unhandled', $hostgroup->services_warning_unhandled);
        $status->set('unknown_handled', $hostgroup->services_unknown_handled);
        $status->set('unknown_unhandled', $hostgroup->services_unknown_unhandled);

        // extra metadata for view
        $status->setMeta('hosts_total', $hostgroup->hosts_total);
        $status->setMeta(
            'hosts_unhandled',
            $hostgroup->hosts_down_unhandled
        );

        $status->set('missing', 0);

        return $this->status;
    }
}
