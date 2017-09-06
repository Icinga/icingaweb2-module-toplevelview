<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Tree;

class TLVHostNode extends TLVTreeNode
{
    protected static $titleKey = 'host';

    protected static $canHaveChildren = false;

    protected static $registeredHosts = array();

    protected function register()
    {
        if ($host = $this->get('host')) {
            self::$registeredHosts[$host] = null;
        }
        return $this;
    }
}
