<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Tree;

class TLVHostGroupNode extends TLVTreeNode
{
    protected static $titleKey = 'hostgroup';

    protected static $canHaveChildren = false;

    protected static $registeredHostgroups = array();

    protected function register()
    {
        if ($hostgroup = $this->get('hostgroup')) {
            self::$registeredHostgroups[$hostgroup] = null;
        }
        return $this;
    }
}
