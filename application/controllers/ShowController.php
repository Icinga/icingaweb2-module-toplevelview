<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Web\Controller;
use Icinga\Web\Url;
use Icinga\Web\Widget\Tab;

class ShowController extends Controller
{
    public function init()
    {
        $tabs = $this->getTabs();

        $tiles = Url::fromPath('toplevelview/show', array(
            'name' => $this->params->getRequired('name')
        ));

        $tabs->add(
            'index',
            array(
                'title' => $this->translate('Tiles'),
                'url'   => $tiles
            )
        );

        if (($id = $this->getParam('id')) !== null) {
            $tabs->add(
                'tree',
                array(
                    'title' => $this->translate('Tree'),
                    'url'   => Url::fromPath('toplevelview/show/tree', array(
                        'name' => $this->params->getRequired('name'),
                        'id'   => $id
                    ))
                )
            );
        }


        $fullscreen = new Tab(array(
            'title' => $this->translate('Fullscreen'),
            'url'   => ((string) $tiles) . '&view=compact&showFullscreen'
        ));
        $fullscreen->setTargetBlank();
        $tabs->add('fullscreen', $fullscreen);

        $action = $this->getRequest()->getActionName();
        if ($tab = $tabs->get($action)) {
            $tab->setActive();
        }
    }

    public function indexAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = $view = ViewConfig::loadByName($name);
        $tree = $view->getTree();

        if (($lifetime = $this->getParam('cache')) !== null) {
            $tree->setCacheLifetime($lifetime);
        }

        $this->setAutorefreshInterval(30);
    }

    public function treeAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = $view = ViewConfig::loadByName($name);
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
        $this->view->view = $view = ViewConfig::loadByName($name);

        $this->view->text = $view->getText();
        $this->setViewScript('index', 'text');
    }
}
