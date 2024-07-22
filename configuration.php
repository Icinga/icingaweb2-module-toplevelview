<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

/** @var \Icinga\Application\Modules\Module $this */

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Web\Url;

$this->providePermission('toplevelview/edit', $this->translate('Allow the user to edit Top Level Views'));

// TODO Implement these restrictions in the ViewConfig
// $this->provideRestriction(
//     'toplevelview/filter/edit',
//     $this->translate('Restrict edit rights to Views that match the filter (comma-separated values)')
// );

// $this->provideRestriction(
//     'toplevelview/filter/views',
//     $this->translate('Restrict access to Views that match the filter (comma-separated values)')
// );

/** @var \Icinga\Web\Navigation\NavigationItem $section */
$section = $this->menuSection('toplevelview');
$section
    ->setLabel('Top Level View')
    ->setUrl('toplevelview')
    ->setIcon('sitemap')
    ->setPriority(50);

try {
    if (extension_loaded('yaml')) {
        /** @var \Icinga\Application\Modules\MenuItemContainer $section */
        $c = new ViewConfig();
        $views = $c->loadAll();

        foreach ($views as $name => $viewConfig) {
            $section->add($name, array(
                'label' => $viewConfig->getMeta('name'),
                'url'   => Url::fromPath('toplevelview/show', array('name' => $name)),
            ));
        }
    }
} catch (Exception $e) {
    // don't fail here...
}

$this->provideJsFile('vendor/codemirror/codemirror.js');
$this->provideJsFile('vendor/codemirror/mode/yaml.js');
$this->provideJsFile('vendor/codemirror/addon/dialog/dialog.js');
$this->provideJsFile('vendor/codemirror/addon/search/searchcursor.js');
$this->provideJsFile('vendor/codemirror/addon/search/search.js');
$this->provideJsFile('vendor/codemirror/addon/search/matchesonscrollbar.js');
$this->provideJsFile('vendor/codemirror/addon/search/jump-to-line.js');
$this->provideJsFile('vendor/codemirror/addon/fold/foldcode.js');
$this->provideJsFile('vendor/codemirror/addon/fold/foldgutter.js');
$this->provideJsFile('vendor/codemirror/addon/fold/indent-fold.js');

$this->provideCssFile('vendor/codemirror/codemirror.css');
$this->provideCssFile('vendor/codemirror/addon/dialog/dialog.css');
$this->provideCssFile('vendor/codemirror/addon/search/matchesonscrollbar.css');
$this->provideCssFile('vendor/codemirror/addon/fold/foldgutter.css');
