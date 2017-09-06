<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Web;

use Icinga\Application\Icinga;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\IcingaException;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Web\Controller as IcingaController;

class Controller extends IcingaController
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
