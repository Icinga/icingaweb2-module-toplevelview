<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

/** @var \Icinga\Application\Modules\Module $this */

$this->providePermission('toplevelview/edit', $this->translate('Allow the user to edit Top Level Views'));

/** @var \Icinga\Web\Navigation\NavigationItem $section */
$section = $this->menuSection('toplevelview');
$section
    ->setLabel('Top Level View')
    ->setUrl('toplevelview')
    ->setIcon('sitemap')
    ->setPriority(20);

/* TODO
$section->add('view_X', array(
    'url'   => 'toplevelview/show?name=X',
    'label' => NAME,
));
*/
