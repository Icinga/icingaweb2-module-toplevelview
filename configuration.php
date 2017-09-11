<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

/** @var \Icinga\Application\Modules\Module $this */

// TODO:
//$this->providePermission('toplevelview/XXX', $this->translate('Whatever'));

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
