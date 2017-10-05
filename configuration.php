<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

/** @var \Icinga\Application\Modules\Module $this */

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Web\Url;

$this->providePermission('toplevelview/edit', $this->translate('Allow the user to edit Top Level Views'));

/** @var \Icinga\Web\Navigation\NavigationItem $section */
$section = $this->menuSection('toplevelview');
$section
    ->setLabel('Top Level View')
    ->setUrl('toplevelview')
    ->setIcon('sitemap')
    ->setPriority(20);

try {
    /** @var \Icinga\Application\Modules\MenuItemContainer $section */
    $views = ViewConfig::loadAll();

    foreach ($views as $name => $viewConfig) {
        $section->add($name, array(
            'label' => $viewConfig->getMeta('name'),
            'url'   => Url::fromPath('toplevelview/show', array('name' => $name)),
        ));

    }
} catch (Exception $e) {
    // don't fail here...
}

$this->provideJsFile('vendor/codemirror/codemirror.js');
$this->provideJsFile('vendor/codemirror/mode/yaml.js');
$this->provideCssFile('vendor/codemirror/codemirror.css');
