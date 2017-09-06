<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Exception\NotFoundError;
use Icinga\Module\Toplevelview\ViewConfig;

class TLVTree extends TLVTreeNode
{
    protected static $titleKey = 'title';

    /**
     * @var ViewConfig
     */
    protected $config;

    public function getById($id)
    {
        $ids = explode('-', $id);
        $currentNode = $this;

        foreach ($ids as $i) {
            $children = $currentNode->getChildren();
            if (! empty($children) && array_key_exists($i, $children)) {
                $currentNode = $children[$i];
            } else {
                throw new NotFoundError(
                    'Could not find ID %s after %s for path %s',
                    $i,
                    $currentNode->getFullId(),
                    $id
                );
            }
        }

        return $currentNode;
    }

    /**
     * @return ViewConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ViewConfig $config
     *
     * @return $this
     */
    public function setConfig(ViewConfig $config)
    {
        $this->config = $config;
        return $this;
    }
}
