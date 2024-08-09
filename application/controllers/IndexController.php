<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Web\Controller;

use Icinga\Application\Icinga;

/**
 * IndexController loads all existing Views from their YAML files.
 */
class IndexController extends Controller
{
    public function indexAction()
    {
        $this->getTabs()->add('index', [
            'title' => 'Top Level View',
            'url'   => 'toplevelview',
        ])->activate('index');

        // Load add views from the existing YAML files
        $config_dir_module = Icinga::app()
                           ->getModuleManager()
                           ->getModule('toplevelview')
                           ->getConfigDir();

        $c = new ViewConfig($config_dir_module);
        $views = $c->loadAll();

        $this->view->views = $views;

        $this->setAutorefreshInterval(30);
    }
}
