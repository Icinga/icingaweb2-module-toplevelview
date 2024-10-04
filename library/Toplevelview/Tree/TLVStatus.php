<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

/**
 * TLVStatus represents the status for a TLVTreeNode that is shown in the view
 */
class TLVStatus
{
    /**
     * Properties track each tree nodes Icinga states
     */
    protected $properties = [
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
    ];

    /**
     * statusPriority decribes the priority from worst to best
     */
    protected static $statusPriority = [
        'critical_unhandled',
        'warning_unhandled',
        'unknown_unhandled',
        'critical_handled',
        'warning_handled',
        'unknown_handled',
        'ok',
        'downtime_handled',
        'missing',
    ];

    /**
     * meta tracks get overall count of hosts and services if this status object
     */
    protected $meta = [];

    /**
     * merge merges another TLVStatus object's properties into this object
     */
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

    /**
     * get returns the given key's value from the properties
     *
     * @param string $key key of the property
     */
    public function get($key)
    {
        return $this->properties[$key];
    }

    /**
     * set sets the given key/value in the properties
     *
     * @param string $key key of the property
     * @param int $value value to set to property to
     */
    public function set($key, $value)
    {
        $this->properties[$key] = (int) $value;
        return $this;
    }

    /**
     * getProperties returns all properties
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * add adds the given value (integer) to the given property
     *
     * @param string $key key of the property
     * @param int $value value to add to the property
     */
    public function add($key, $value = 1)
    {
        if ($this->properties[$key] === null) {
            $this->properties[$key] = 0;
        }
        $this->properties[$key] += (int) $value;
        return $this;
    }

    /**
     * zero sets all properties to zero (0)
     */
    public function zero()
    {
        foreach (array_keys($this->properties) as $key) {
            $this->properties[$key] = 0;
        }
        return $this;
    }

    /**
     * getOverall returns the worst state of this TLVStatus,
     * given the statusPriority.
     *
     * @return string
     */
    public function getOverall(): string
    {
        foreach (static::$statusPriority as $key) {
            if ($this->properties[$key] !== null && $this->properties[$key] > 0) {
                return $this->cssFriendly($key);
            }
        }
        return 'missing';
    }

    /**
     * cssFriendly transforms the given key to be CSS friendly,
     * meaning using spaces between the state and the handled indicator
     */
    protected function cssFriendly($key): string
    {
        return str_replace('_', ' ', $key);
    }

    /**
     * getMeta returns the given key's value from the metadata
     */
    public function getMeta($key)
    {
        if (array_key_exists($key, $this->meta)) {
            return $this->meta[$key];
        } else {
            return null;
        }
    }

    /**
     * setMeta sets the given key/value in the metadata
     */
    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }
}
