<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview;

use Icinga\Application\Icinga;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\IcingaException;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Cli\Command as IcingaCommand;

class Command extends IcingaCommand
{
    /** @var  MonitoringBackend */
    protected $monitoringBackend;

    public function init()
    {
        parent::init();

        if (! extension_loaded('yaml')) {
            throw new ConfigurationError('You need the PHP extension "yaml" in order to use TopLevelView');
        }
    }

    /**
     * Retrieves the Icinga MonitoringBackend
     *
     * @param string|null $name
     *
     * @return MonitoringBackend
     * @throws IcingaException When monitoring is not enabled
     */
    protected function monitoringBackend($name = null)
    {
        if ($this->monitoringBackend === null) {
            if (! Icinga::app()->getModuleManager()->hasEnabled('monitoring')) {
                throw new IcingaException('The module "monitoring" must be enabled and configured!');
            }
            $this->monitoringBackend = MonitoringBackend::instance($name);
        }
        return $this->monitoringBackend;
    }
}
