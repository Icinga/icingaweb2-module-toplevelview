<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Data\Tree\TreeNode;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\NotImplementedError;
use Icinga\Exception\ProgrammingError;

class TLVTreeNode extends TreeNode
{
    /**
     * @var string
     */
    protected $type = 'node';

    /**
     * @var TLVTree
     */
    protected $root;

    /**
     * @var TLVTreeNode
     */
    protected $parent;

    /**
     * @var string
     */
    protected $fullId;

    /**
     * @var array
     */
    protected $properties = array();

    protected static $canHaveChildren = true;

    /**
     * The key which represents the display title
     *
     * @var string
     */
    protected static $titleKey = 'name';

    protected static $typeMap = array(
        'host'      => 'Icinga\\Module\\Toplevelview\\Tree\\TLVHostNode',
        'service'   => 'Icinga\\Module\\Toplevelview\\Tree\\TLVServiceNode',
        'hostgroup' => 'Icinga\\Module\\Toplevelview\\Tree\\TLVHostGroupNode',
    );

    /**
     * @param                  $array
     * @param TLVTreeNode|null $parent
     * @param TLVTree          $root
     *
     * @return static
     *
     * @throws NotImplementedError
     * @throws ProgrammingError
     */
    public static function fromArray($array, TLVTreeNode $parent = null, TLVTree $root = null)
    {
        if (array_key_exists('type', $array)) {
            $type = $array['type'];
            if (array_key_exists($type, self::$typeMap)) {
                $node = new self::$typeMap[$type];
                $node->type = $type;
            } else {
                throw new NotImplementedError('Could not find type "%s" for %s', $type, var_export($array, true));
            }
        } elseif ($root === null) {
            $node = new static;
        } else {
            $node = new self;
        }

        if ($root === null) {
            $node->root = true; // is root
            $node->parent = null;
            $root = $parent = $node;
        } elseif ($parent === null) {
            throw new ProgrammingError('You must specify the direct parent!');
        } else {
            $node->root = $root;
            $node->parent = $parent;
        }

        foreach ($array as $key => $value) {
            if ($key !== 'children') {
                $node->properties[$key] = $value;
            } else {
                foreach ($value as $i => $child) {
                    $childNode = self::fromArray($child, $node, $root);
                    $childNode->id = $i;
                    $node->appendChild($childNode);
                }
            }
        }

        $node->register();

        return $node;
    }

    /**
     * Retrieve all objects as breadcrumb
     *
     * @param array $list for recursion
     *
     * @return TLVTreeNode[]
     */
    public function getBreadCrumb(&$list = array())
    {
        array_unshift($list, $this);
        if ($this->parent !== $this->root) {
            $this->parent->getBreadCrumb($list);
        }
        return $list;
    }

    /**
     *
     * @return mixed|string
     */
    public function getFullId()
    {
        if ($this->fullId === null) {
            $id = (string) $this->id;
            if ($this->parent !== $this->root) {
                $this->fullId = $this->parent->getFullId() . '-' . $id;
            } else {
                $this->fullId = $id;
            }
        }
        return $this->fullId;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function setProperties($array)
    {
        $this->properties = $array;
        return $this;
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        } else {
            return null;
        }
    }

    public function set($key, $value)
    {
        $this->properties[$key] = $value;
        return $this;
    }

    public function getTitle()
    {
        if (array_key_exists(static::$titleKey, $this->properties)) {
            return $this->properties[static::$titleKey];
        } else {
            return null;
        }
    }

    /**
     * @return TLVTree
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return TLVTreeNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return TLVTreeNode[]
     */
    public function getChildren()
    {
        return parent::getChildren();
    }

    /**
     * Append a child node as the last child of this node
     *
     * @param   TreeNode $child The child to append
     *
     * @return $this
     *
     * @throws ConfigurationError When node does not allow children
     */
    public function appendChild(TreeNode $child)
    {
        if (static::$canHaveChildren === true) {
            $this->children[] = $child;
        } else {
            throw new ConfigurationError('Can not add children below type %s', $this->type);
        }
        return $this;
    }

    protected function register()
    {
        return $this;
    }

    public function getStatus()
    {

    }
}
