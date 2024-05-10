<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Application\Benchmark;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Icingadb\Model\Service;
use ipl\Stdlib\Filter;
use stdClass;

/**
 * TLVServiceNode represents a Service in the tree
 */
class TLVServiceNode extends TLVIcingaNode
{
    protected $type = 'service';

    protected $key = '{host}!{service}';

    public function getTitle()
    {
        return sprintf(
            '%s: %s',
            $this->get('host'),
            $this->get('service')
        );
    }

    public function register()
    {
        // also register host, because that's what we fetch data with
        $hostDummy = new TLVHostNode();
        $this->root->registerObject($hostDummy->getType(), $this->get('host'), get_class($hostDummy));

        // register myself
        return parent::register();
    }

    public function getKey()
    {
        return sprintf('%s!%s', $this->properties['host'], $this->properties['service']);
    }

    public static function fetch(TLVTree $root)
    {
        Benchmark::measure('Begin fetching services');

        if (! array_key_exists('service', $root->registeredObjects) or empty($root->registeredObjects['service'])) {
            throw new NotFoundError('No services registered to fetch!');
        }

        $hostnameFilter = Filter::any();

        foreach (array_keys($root->registeredObjects['host']) as $name) {
            $hostnameFilter->add(Filter::equal('host.name', $name));
        }

        $services = Service::on($root->getDb())->with([
            'host',
            'state'
        ]);

        $services->filter($hostnameFilter);

        foreach ($services as $service) {
            // TODO We cannot store the ORM Models with json_encore
            // Thus I'm converting things to objects that can be stored
            // Maybe there's a better way? iterator_to_array does not work.
            $s = new stdClass;
            $s->state = new stdClass;
            $s->notifications_enabled = $service->notifications_enabled;
            $s->display_name = $service->display_name;
            $s->state->hard_state = $service->state->hard_state;
            $s->state->is_flapping = $service->state->is_flapping;
            $s->state->is_handled = $service->state->is_handled;
            $s->state->in_downtime = $service->state->in_downtime;

            $key = sprintf('%s!%s', $service->host->name, $service->display_name);
            if (array_key_exists($key, $root->registeredObjects['service'])) {
                $root->registeredObjects['service'][$key] = $s;
            }
        }

        Benchmark::measure('Finished fetching services');
    }

    /**
     * getStatus returns the current status for the Service
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

        $service = $this->root->getFetched($this->type, $key);

        if ($service === null) {
            $status->add('missing', 1);
            return $this->status;
        }

        $status->zero();
        $status->add('total');

        // We only care about the hard state in TLV
        $state = $service->state->hard_state;

        // Get the service's current handled state
        $isHandled = $service->state->is_handled;

        // In TLV flapping means the state is handled
        $isHandled = $isHandled || $service->state->is_flapping;

        // Set downtime if notifications are disabled for the service
        if ($service->state->in_downtime || $service->notifications_enabled === false) {
            $status->add('downtime_active');
            if ($state !== 0) {
                $state = 10;
            }
        }

        if ($isHandled) {
            $handled = '_handled';
        } else {
            $handled = '_unhandled';
        }

        if ($state === 0 || $state === 99) {
            $status->add('ok', 1);
        } elseif ($state === 1) {
            $status->add('warning' . $handled, 1);
        } elseif ($state === 2) {
            $status->add('critical' . $handled, 1);
        } elseif ($state === 10) {
            $status->add('downtime_handled');
        } else {
            $status->add('unknown' . $handled, 1);
        }

        return $this->status;
    }
}
