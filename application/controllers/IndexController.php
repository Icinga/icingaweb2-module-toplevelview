<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Web\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $this->view->views = ViewConfig::loadAll();
    }
}
