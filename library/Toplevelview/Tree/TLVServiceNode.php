<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Tree;

class TLVServiceNode extends TLVTreeNode
{
    protected static $canHaveChildren = false;

    protected static $registeredServices = array();

    public function getTitle()
    {
        return sprintf(
            '%s: %s',
            $this->get('host'),
            $this->get('service')
        );
    }

    protected function register()
    {
        if ($host = $this->get('host') && $service = $this->get('service')) {
            self::$registeredServices[$host][$service] = null;
        }
        return $this;
    }


}
