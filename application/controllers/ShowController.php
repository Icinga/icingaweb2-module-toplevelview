<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Web\Controller;
use Icinga\Web\Url;

class ShowController extends Controller
{
    public function init()
    {
        $tabs = $this->getTabs();

        $tabs->add(
            'index',
            array(
                'title' => $this->translate('Tiles'),
                'url'   => Url::fromPath('toplevelview/show', array(
                    'name' => $this->params->getRequired('name')
                ))
            )
        );

        if (($id = $this->getParam('id')) !== null) {
            $tabs->add(
                'tree',
                array(
                    'title' => $this->translate('Tree'),
                    'url'   => Url::fromPath('toplevelview/tree', array(
                        'name' => $this->params->getRequired('name'),
                        'id'   => $id
                    ))
                )
            );
        }

        $action = $this->getRequest()->getActionName();
        if ($tab = $tabs->get($action)) {
            $tab->setActive();
        }
    }

    public function indexAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = ViewConfig::loadByName($name);
    }

    public function treeAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = $view = ViewConfig::loadByName($name);
        $this->view->node = $view->getTree()->getById($this->params->getRequired('id'));
    }

    public function sourceAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = $view = ViewConfig::loadByName($name);

        header('Content-Type: text/plain');
        var_dump($view->getTree());
        echo $view->getText();
        exit;
    }
}
