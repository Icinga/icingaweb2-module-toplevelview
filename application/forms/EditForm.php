<?php
/* Icinga Web 2 | (c) 2014 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Toplevelview\Forms;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Model\View;

use Exception;
use Icinga\Web\Form;
use Icinga\Web\Notification;
use Icinga\Web\Url;

class EditForm extends Form
{
    /**
     * @var ViewConfig
     */
    protected $viewconfig;

    /**
     * @var View
     */
    protected $view;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('form_toplevelview_edit');
    }

    public function setViews(View $view)
    {
        $this->view = $view;
        return $this;
    }

    public function setViewConfig(ViewConfig $config)
    {
        $this->viewconfig = $config;
        return $this;
    }

    /**
     * @see Form::onSuccess()
     */
    public function onSuccess()
    {
        try {
            $this->view->setName($this->getValue('name'));
            $this->view->setText($this->getValue('config'));

            // ensure config can be parsed...
            $this->view->getMetaData();
            $this->view->getTree();

            $cancel = $this->getElement('btn_submit_cancel');
            $delete = $this->getElement('btn_submit_delete');

            if ($this->getElement('btn_submit_save_file')->getValue() !== null) {
                // Store the view to its YAML file
                $this->viewconfig->storeToFile($this->view);
                Notification::success($this->translate('Top Level View successfully saved'));
            } elseif ($cancel !== null && $cancel->getValue() !== null) {
                // Clear the stored session data for the view
                $this->viewconfig->clearSession($this->view);
                Notification::success($this->translate('Top Level View restored from disk'));
            } elseif ($delete != null && $delete->getValue() !== null) {
                // Delete the view's YAML file
                $this->viewconfig->delete($this->view);
                $this->setRedirectUrl('toplevelview');
                Notification::success($this->translate('Top Level View successfully deleted'));
            } else {
                // Store the view to the user's session by default
                $this->viewconfig->storeToSession($this->view);
                Notification::success($this->translate('Top Level View successfully saved for the current session'));
            }
            return true;
        } catch (Exception $e) {
            $this->error(sprintf('Could not save config: %s', $e->getMessage()));
            return false;
        }
    }

    public function getRedirectUrl()
    {
        if ($this->redirectUrl === null && ($name = $this->view->getName()) !== null) {
            $this->redirectUrl = Url::fromPath('toplevelview/show', ['name' => $name]);
        }
        return parent::getRedirectUrl();
    }

    /**
     * Populate form
     *
     * @see Form::onRequest()
     */
    public function onRequest()
    {
        $values = [];
        $values['name'] = $this->view->getName();
        $values['config'] = $this->view->getText();

        $this->populate($values);
    }

    /**
     * @see Form::createElements()
     */
    public function createElements(array $formData)
    {
        if ($this->view->hasBeenLoadedFromSession()) {
            $this->warning(
                $this->translate(
                    'This config is only stored in your session!'
                    . ' Make sure to save it to disk once your work is complete!'
                ),
                false
            );
        }

        $this->addElement(
            'text',
            'name',
            [
                'label' => $this->translate('File name'),
                'required' => true
            ]
        );
        $this->addElement(
            'textarea',
            'config',
            [
                'label' => $this->translate('YAML Config'),
                'class' => 'code-editor codemirror',
                'decorators' => [
                    ['Label', ['tag' => 'div', 'separator' => '']],
                    ['HtmlTag', ['tag' => 'div']],
                    'ViewHelper'
                ],
                'data-codemirror-mode' => 'yaml'
            ]
        );

        $this->addElement(
            'submit',
            'btn_submit_save_session',
            [
                'ignore'     => true,
                'label'      => $this->translate('Save for the current Session'),
                'decorators' => ['ViewHelper']
            ]
        );

        $this->addElement(
            'submit',
            'btn_submit_save_file',
            [
                'ignore'     => true,
                'label'      => $this->translate('Save to config file'),
                'decorators' => ['ViewHelper']
            ]
        );

        if ($this->view->hasBeenLoadedFromSession()) {
            $this->addElement(
                'submit',
                'btn_submit_cancel',
                [
                    'ignore'     => true,
                    'label'      => $this->translate('Cancel editing'),
                    'class'      => 'btn-cancel',
                    'decorators' => ['ViewHelper']
                ]
            );
        }

        if ($this->view->hasBeenLoaded()) {
            $this->addElement(
                'submit',
                'btn_submit_delete',
                [
                    'ignore'     => true,
                    'label'      => $this->translate('Delete config'),
                    'data-confirmation' => $this->translate('Confirm deletion'),
                    'class'      => 'btn-remove',
                    'decorators' => ['ViewHelper']
                ]
            );
        }
    }
}
