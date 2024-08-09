<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

/**
 * TLVStatus represents the status for a TLVTreeNode that is shown in the view
 */
class TLVStatus
{
    protected $properties = array(
        'critical_unhandled' => null,
        'critical_handled'   => null,
        'warning_unhandled'  => null,
        'warning_handled'    => null,
        'unknown_unhandled'  => null,
        'unknown_handled'    => null,
        'downtime_handled'   => null,
        'downtime_active'    => null,
        'ok'                 => null,
        'missing'            => null,
        'total'              => null,
    );

    protected static $statusPriority = array(
        'critical_unhandled',
        'warning_unhandled',
        'unknown_unhandled',
        'critical_handled',
        'warning_handled',
        'unknown_handled',
        'ok',
        'downtime_handled',
        'missing',
    );

    protected $meta = array();

    public function merge(TLVStatus $status)
    {
        $properties = $status->getProperties();
        foreach (array_keys($this->properties) as $key) {
            if ($this->properties[$key] === null) {
                $this->properties[$key] = $properties[$key];
            } else {
                $this->properties[$key] += $properties[$key];
            }
        }
        return $this;
    }

    public function get($key)
    {
        return $this->properties[$key];
    }

    public function set($key, $value)
    {
        $this->properties[$key] = (int) $value;
        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function add($key, $value = 1)
    {
        if ($this->properties[$key] === null) {
            $this->properties[$key] = 0;
        }
        $this->properties[$key] += (int) $value;
        return $this;
    }

    public function zero()
    {
        foreach (array_keys($this->properties) as $key) {
            $this->properties[$key] = 0;
        }
        return $this;
    }

    public function getOverall()
    {
        foreach (static::$statusPriority as $key) {
            if ($this->properties[$key] !== null && $this->properties[$key] > 0) {
                return $this->cssFriendly($key);
            }
        }
        return 'missing';
    }

    protected function cssFriendly($key): string
    {
        return str_replace('_', ' ', $key);
    }

    public function getMeta($key)
    {
        if (array_key_exists($key, $this->meta)) {
            return $this->meta[$key];
        } else {
            return null;
        }
    }

    public function getAllMeta()
    {
        return $this->meta;
    }

    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }
}
