<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Application\Benchmark;
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

    protected $key = null;

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
     * @var TLVStatus
     */
    protected $status;

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

    /**
     * Mapping types to its implementation class
     *
     * @var array
     */
    protected static $typeMap = array(
        'host'      => 'Icinga\\Module\\Toplevelview\\Tree\\TLVHostNode',
        'service'   => 'Icinga\\Module\\Toplevelview\\Tree\\TLVServiceNode',
        'hostgroup' => 'Icinga\\Module\\Toplevelview\\Tree\\TLVHostGroupNode',
    );

    /**
     * Mapping keys to a type
     *
     * Warning: order is important when keys overlap!
     *
     * @var array
     */
    protected static $typeKeyMap = array(
        'service'   => array('host', 'service'),
        'host'      => 'host',
        'hostgroup' => 'hostgroup',
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
        if ($root === null) {
            Benchmark::measure('Begin loading TLVTree from array');
        }

        // try to detect type
        if (! array_key_exists('type', $array)) {
            foreach (self::$typeKeyMap as $type => $keys) {
                if (! is_array($keys)) {
                    $keys = array($keys);
                }
                $matched = false;
                foreach ($keys as $k) {
                    if (array_key_exists($k, $array)) {
                        $matched = true;
                    } else {
                        continue 2;
                    }
                }
                // if all keys are present
                if ($matched === true) {
                    $array['type'] = $type;
                    break;
                }
            }
        }

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
            } elseif (is_array($value)) { // only array values for children
                foreach ($value as $i => $child) {
                    $childNode = self::fromArray($child, $node, $root);
                    $childNode->id = $i;
                    $node->appendChild($childNode);
                }
            }
        }

        $node->register();

        if ($root === $node) {
            Benchmark::measure('Finished loading TLVTree from array');
        }

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

    public function getKey()
    {
        if ($this->key === null) {
            throw new ProgrammingError('Can not get key for %s', get_class($this));
        }

        if (array_key_exists($this->key, $this->properties)) {
            return $this->properties[$this->key];
        } else {
            throw new ProgrammingError(
                'Can not retrieve key for %s in %s',
                $this->key,
                get_class($this)
            );
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
        if ($this->type !== 'node') {
            $this->root->registerObject($this->type, $this->getKey(), get_class($this));
        }
        return $this;
    }

    /**
     * @return TLVStatus
     * @throws ProgrammingError
     */
    public function getStatus()
    {
        if (static::$canHaveChildren === true) {
            if ($this->status === null) {
                $this->status = new TLVStatus;

                $missed = true;
                foreach ($this->getChildren() as $child) {
                    $this->status->merge($child->getStatus());
                    $missed = false;
                }

                // Note: old TLV does not count an empty branch as missing...
                if ($missed) {
                    $this->status->add('missing', 1);
                }
            }

            return $this->status;
        } else {
            throw new ProgrammingError('getStatus() needs to be implemented for %s', get_class($this));
        }
    }
}
