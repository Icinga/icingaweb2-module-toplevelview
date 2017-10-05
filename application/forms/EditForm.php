<?php
/* Icinga Web 2 | (c) 2014 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Toplevelview\Forms;

use Exception;
use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Web\Form;
use Icinga\Web\Notification;
use Icinga\Web\Url;

class EditForm extends Form
{
    /**
     * @var ViewConfig
     */
    protected $viewConfig;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('form_toplevelview_edit');
    }

    public function setViewConfig(ViewConfig $viewConfig)
    {
        $this->viewConfig = $viewConfig;
        return $this;
    }

    /**
     * @see Form::onSuccess()
     */
    public function onSuccess()
    {
        try {
            $this->viewConfig->setName($this->getValue('name'));
            $this->viewConfig->setText($this->getValue('config'));

            // ensure config can be parsed...
            $this->viewConfig->getMetaData();
            $this->viewConfig->getTree();

            $this->viewConfig->storeToSession();

            $cancel = $this->getElement('btn_submit_cancel');
            $delete = $this->getElement('btn_submit_delete');

            if ($this->getElement('btn_submit_save_file')->getValue() !== null) {
                $this->viewConfig->store();
                Notification::success($this->translate('Top Level View successfully saved'));
            } else if ($cancel !== null && $cancel->getValue() !== null) {
                $this->viewConfig->clearSession();
                Notification::success($this->translate('Top Level View restored from disk'));
            } else if ($delete != null && $delete->getValue() !== null) {
                $this->viewConfig->delete();
                Notification::success($this->translate('Top Level View successfully deleted'));
            } else {
                Notification::success($this->translate('Top Level View successfully saved for the current session'));
            }
            return true;
        } catch (Exception $e) {
            $this->addError(sprintf('Could not save config: %s', $e->getMessage()));
            return false;
        }
    }

    public function getRedirectUrl()
    {
        if ($this->redirectUrl === null && ($name = $this->viewConfig->getName()) !== null) {
            $this->redirectUrl = Url::fromPath('toplevelview/show', array('name' => $name));
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
        $values = array();
        $values['name'] = $this->viewConfig->getName();
        $values['config'] = $this->viewConfig->getText();

        $this->populate($values);
    }

    /**
     * @see Form::createElements()
     */
    public function createElements(array $formData)
    {
        if ($this->viewConfig->hasBeenLoadedFromSession()) {
            $this->warning(
                $this->translate(
                    'This config is only stored in your session!'
                    . ' Make sure to save it to disk once your work is complete!'
                ), false
            );
        }

        $this->addElement(
            'text',
            'name',
            array(
                'label'    => $this->translate('File name'),
                'required' => true
            )
        );
        $this->addElement(
            'textarea',
            'config',
            array(
                //'required'             => true,
                'label'                => $this->translate('YAML Config'),
                'class'                => 'code-editor codemirror',
                'decorators'           => array(
                    array('Label', array('tag'=>'div', 'separator' => '')),
                    array('HtmlTag', array('tag' => 'div')),
                    'ViewHelper'
                ),
                'data-codemirror-mode' => 'yaml'
            )
        );

        $this->addElement(
            'submit',
            'btn_submit_save_session',
            array(
                'ignore'     => true,
                'label'      => $this->translate('Save for the current Session'),
                'decorators' => array('ViewHelper')
            )
        );

        $this->addElement(
            'submit',
            'btn_submit_save_file',
            array(
                'ignore'     => true,
                'label'      => $this->translate('Save to config file'),
                'decorators' => array('ViewHelper')
            )
        );

        if ($this->viewConfig->hasBeenLoadedFromSession()) {
            $this->addElement(
                'submit',
                'btn_submit_cancel',
                array(
                    'ignore'     => true,
                    'label'      => $this->translate('Cancel editing'),
                    'decorators' => array('ViewHelper')
                )
            );
        }

        if ($this->viewConfig->hasBeenLoaded()) {
            $this->addElement(
                'submit',
                'btn_submit_delete',
                array(
                    'ignore'     => true,
                    'label'      => $this->translate('Delete config'),
                    'decorators' => array('ViewHelper')
                )
            );
        }
    }
}
