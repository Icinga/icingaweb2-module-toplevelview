<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Toplevelview\Controllers;

use Icinga\Module\Toplevelview\Model\View;
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

        $c = new ViewConfig();
        $view = null;

        if ($action === 'add') {
            $this->view->title = sprintf('%s Top Level View', $this->translate('Add'));
            $view = new View('', $c::FORMAT_YAML);
        } elseif ($action === 'clone') {
            // Clone the view and give it to the View
            $name = $this->params->getRequired('name');
            $this->view->title = sprintf('%s Top Level View', $this->translate('Clone'));

            // Check if the user has permissions/restrictions for this View
            $restrictions = $c->getRestrictions('toplevelview/filter/edit');
            $c->assertAccessToView($restrictions, $name);

            $view = clone $c->loadByName($name);
        } else {
            $this->view->name = $name = $this->params->getRequired('name');
            $this->view->title = sprintf('%s Top Level View: %s', $this->translate('Edit'), $this->params->getRequired('name'));

            // Check if the user has permissions/restrictions for this View
            $restrictions = $c->getRestrictions('toplevelview/filter/edit');
            $c->assertAccessToView($restrictions, $name);

            $view = $c->loadByName($name);
        }

        $view->setFormat($c::FORMAT_YAML);

        $this->view->form = $form = new EditForm();
        $form->setViewConfig($c);
        $form->setViews($view);

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
