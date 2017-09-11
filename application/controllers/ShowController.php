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

        $tabs->add(
            'source',
            array(
                'title' => $this->translate('Source'),
                'url'   => Url::fromPath('toplevelview/show/source', array(
                    'name' => $this->params->getRequired('name')
                ))
            )
        );

        $fullscreen = new Tab(array(
            'title' => $this->translate('Go Fullscreen'),
            'icon'  => 'dashboard',
            'url'   => ((string) $tiles) . '&view=compact&showFullscreen'
        ));
        $fullscreen->setTargetBlank();
        $tabs->addAsDropdown('fullscreen', $fullscreen);

        $action = $this->getRequest()->getActionName();
        if ($tab = $tabs->get($action)) {
            $tab->setActive();
        }
    }

    public function indexAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = $view = ViewConfig::loadByName($name);
        $view->getTree()->setBackend($this->monitoringBackend());
    }

    public function treeAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = $view = ViewConfig::loadByName($name);
        $tree = $view->getTree();
        $this->view->node = $tree->getById($this->params->getRequired('id'));
        $tree->setBackend($this->monitoringBackend());
    }

    public function sourceAction()
    {
        $this->view->name = $name = $this->params->getRequired('name');
        $this->view->view = $view = ViewConfig::loadByName($name);

        $this->view->text = $view->getText();
        $this->setViewScript('index', 'text');
    }
}
