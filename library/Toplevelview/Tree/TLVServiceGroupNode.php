<?php
/* Copyright (C) 2024 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Application\Benchmark;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Icingadb\Model\ServicegroupSummary;
use ipl\Stdlib\Filter;
use stdClass;

/**
 * TLVServiceGroupNode represents a Servicegroup in the tree
 */
class TLVServiceGroupNode extends TLVIcingaNode
{
    protected $type = 'servicegroup';

    protected $key = 'servicegroup';

    protected static $titleKey = 'servicegroup';

    public function getTitle(): string
    {
        $key = $this->getKey();
        $obj = $this->root->getFetched($this->type, $key);

        $n = $this->get($this->key);

        if (isset($obj->display_name)) {
            $n = $obj->display_name;
        }

        return sprintf('%s', $n);
    }

    public static function fetch(TLVTree $root): void
    {
        Benchmark::measure('Begin fetching servicegroups');

        if (! array_key_exists('servicegroup', $root->registeredObjects) or empty($root->registeredObjects['servicegroup'])) {
            throw new NotFoundError('No servicegroups registered to fetch!');
        }

        $hgFilter = Filter::any();
        foreach (array_keys($root->registeredObjects['servicegroup']) as $name) {
            $hgFilter->add(Filter::equal('servicegroup_name', $name));
        }

        $servicegroups = ServicegroupSummary::on($root->getDb());

        $servicegroups->filter($hgFilter);

        foreach ($servicegroups as $servicegroup) {
            // TODO We cannot store the ORM Models with json_encore
            // Thus I'm converting things to objects that can be stored
            // Maybe there's a better way? iterator_to_array does not work.
            $sg = new stdClass;
            $sg->display_name = $servicegroup->display_name;
            $sg->services_total = $servicegroup->services_total;
            $sg->services_ok = $servicegroup->services_ok;
            $sg->services_warning_handled = $servicegroup->services_warning_handled;
            $sg->services_warning_unhandled = $servicegroup->services_warning_unhandled;
            $sg->services_critical_handled = $servicegroup->services_critical_handled;
            $sg->services_critical_unhandled = $servicegroup->services_critical_unhandled;
            $sg->services_unknown_handled = $servicegroup->services_unknown_handled;
            $sg->services_unknown_unhandled = $servicegroup->services_unknown_unhandled;

            $root->registeredObjects['servicegroup'][$servicegroup->name] = $sg;
        }

        Benchmark::measure('Finished fetching servicegroups');
    }

    /**
     * getStatus returns the current status for the Servicegroup
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

        $servicegroup = $this->root->getFetched($this->type, $key);

        if ($servicegroup === null) {
            $this->status->add('missing', 1);
            return $this->status;
        }

        $status->set('total', $servicegroup->services_total);
        $status->set('ok', $servicegroup->services_ok);

        $status->set('critical_handled', $servicegroup->services_critical_handled);
        $status->set('critical_unhandled', $servicegroup->services_critical_unhandled);
        $status->set('warning_handled', $servicegroup->services_warning_handled);
        $status->set('warning_unhandled', $servicegroup->services_warning_unhandled);
        $status->set('unknown_handled', $servicegroup->services_unknown_handled);
        $status->set('unknown_unhandled', $servicegroup->services_unknown_unhandled);

        // extra metadata for view
        $status->setMeta('services_total', $servicegroup->services_total);

        $status->set('missing', 0);

        return $this->status;
    }
}
