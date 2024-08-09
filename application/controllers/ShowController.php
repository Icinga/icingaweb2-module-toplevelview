<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Web\Controller;

use Icinga\Web\Url;
use Icinga\Application\Icinga;

class ShowController extends Controller
{
    public function init()
    {
        $tabs = $this->getTabs();
        $url = Url::fromRequest()->setParams(clone $this->params);
        $tiles = Url::fromPath('toplevelview/show', ['name' => $this->params->getRequired('name')]);

        $tabs->add('index', [
            'title' => $this->translate('Tree'),
            'url'   => $tiles
        ]);

        // Add new Tabs for the entire tree
        if (($id = $this->getParam('id')) !== null) {
            $tabs->add('tree', [
                'title' => $this->translate('Tiles'),
                'url'   => Url::fromPath('toplevelview/show/tree', [
                    'name' => $this->params->getRequired('name'),
                    'id'   => $id
                ])
            ]);
        }

        $action = $this->getRequest()->getActionName();

        if ($tab = $tabs->get($action)) {
            $tab->setActive();
        }
    }

    public function indexAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');

        $config_dir_module = Icinga::app()
                           ->getModuleManager()
                           ->getModule('toplevelview')
                           ->getConfigDir();

        $c = new ViewConfig($config_dir_module);

        // Check if the user has permissions/restrictions for this View
        $restrictions = $c->getRestrictions('toplevelview/filter/views');
        $c->assertAccessToView($restrictions, $name);

        $view = $c->loadByName($name);
        $this->view->view = $view;

        $tree = $view->getTree();

        if (($lifetime = $this->getParam('cache')) !== null) {
            $tree->setCacheLifetime($lifetime);
        }

        $this->setAutorefreshInterval(30);
    }

    public function treeAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');

        $config_dir_module = Icinga::app()
                           ->getModuleManager()
                           ->getModule('toplevelview')
                           ->getConfigDir();

        $c = new ViewConfig($config_dir_module);

        // Check if the user has permissions/restrictions for this View
        $restrictions = $c->getRestrictions('toplevelview/filter/views');
        $c->assertAccessToView($restrictions, $name);

        $view = $c->loadByName($name);
        $this->view->view = $view;

        $tree = $view->getTree();

        $this->view->node = $tree->getById($this->params->getRequired('id'));

        if (($lifetime = $this->getParam('cache')) !== null) {
            $tree->setCacheLifetime($lifetime);
        }

        $this->setAutorefreshInterval(30);
    }

    public function sourceAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');

        $config_dir_module = Icinga::app()
                           ->getModuleManager()
                           ->getModule('toplevelview')
                           ->getConfigDir();

        $c = new ViewConfig($config_dir_module);

        // Check if the user has permissions/restrictions for this View
        $restrictions = $c->getRestrictions('toplevelview/filter/views');
        $c->assertAccessToView($restrictions, $name);

        $view = $c->loadByName($name);
        $this->view->view = $view;

        $this->view->text = $view->getText();
        $this->setViewScript('index', 'text');
    }
}
