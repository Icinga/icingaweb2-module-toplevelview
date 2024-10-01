<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Web;

use Icinga\Exception\ConfigurationError;
use ipl\Web\Compat\CompatController;

/**
 * Controller wraps around the Icinga\Web\Controller to
 * check for the PHP YAML extension
 *
 * @codeCoverageIgnore
 * @throws ConfigurationError if the PHP yaml extension is not loaded
 */
class Controller extends CompatController
{
    public function init()
    {
        parent::init();

        if (! extension_loaded('yaml')) {
            throw new ConfigurationError('You need the PHP extension "yaml" in order to use TopLevelView');
        }
    }

    protected function setViewScript($name, $controller = null): void
    {
        if ($controller !== null) {
            $name = sprintf('%s/%s', $controller, $name);
        }
        $this->_helper->viewRenderer->setNoController(true);
        $this->_helper->viewRenderer->setScriptAction($name);
    }
}
