<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Web\Controller;

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
        $c = new ViewConfig();
        $views = $c->loadAll();

        $this->view->views = $views;

        $this->setAutorefreshInterval(30);
    }
}
