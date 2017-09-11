<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

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
