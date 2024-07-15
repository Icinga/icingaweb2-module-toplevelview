<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\Forms\EditForm;
use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Web\Controller;
use Icinga\Web\Url;

class EditController extends Controller
{
    public function init()
    {
        $this->assertPermission('toplevelview/edit');

        $tabs = $this->getTabs();

        if ($name = $this->getParam('name')) {
            $tabs->add('tiles', [
                'title' => $this->translate('Tiles'),
                'url'   => Url::fromPath('toplevelview/show', ['name' => $name])
            ]);

            $tabs->add('index', [
                'title' => $this->translate('Edit'),
                'url'   => Url::fromPath('toplevelview/edit', ['name' => $name])
            ]);
        }

        $action = $this->getRequest()->getActionName();

        if ($tab = $tabs->get($action)) {
            $tab->setActive();
        }
    }

    public function indexAction()
    {
        $action = $this->getRequest()->getActionName();

        if ($action === 'add') {
            $this->view->title = sprintf('%s Top Level View', $this->translate('Add'));
            $view = new ViewConfig();
            $view->setConfigDir();
        } elseif ($action === 'clone') {
            $name = $this->params->getRequired('name');
            $this->view->title = sprintf('%s Top Level View', $this->translate('Clone'));
            $view = clone ViewConfig::loadByName($name);
        } else {
            $this->view->name = $name = $this->params->getRequired('name');
            $this->view->title = sprintf('%s Top Level View: %s', $this->translate('Edit'), $this->params->getRequired('name'));
            $view = ViewConfig::loadByName($name);
        }

        $this->view->form = $form = new EditForm();

        $view->setFormat(ViewConfig::FORMAT_YAML);
        $form->setViewConfig($view);
        $form->handleRequest();

        $this->setViewScript('edit/index');
    }

    public function addAction()
    {
        $this->indexAction();
    }

    public function cloneAction()
    {
        $this->indexAction();
    }
}
