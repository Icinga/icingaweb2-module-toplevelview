<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Web;

use Icinga\Application\Icinga;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\IcingaException;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Web\Controller as IcingaController;

/**
 * Controller wraps around the Icinga\Web\Controller to
 * check for the PHP YAML extension
 */
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

    protected function setViewScript($name, $controller = null)
    {
        if ($controller !== null) {
            $name = sprintf('%s/%s', $controller, $name);
        }
        $this->_helper->viewRenderer->setNoController(true);
        $this->_helper->viewRenderer->setScriptAction($name);
    }
}
