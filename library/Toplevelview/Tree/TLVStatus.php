<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Exception\ProgrammingError;

class TLVStatus
{
    protected $properties = array(
        'critical_unhandled' => null,
        'critical_handled' => null,
        'warning_unhandled' => null,
        'warning_handled' => null,
        'unknown_unhandled' => null,
        'unknown_handled' => null,
        'ok' => null,
        'missing' => null,
        'total' => null,
    );

    protected static $statusPriority = array(
        'critical_unhandled',
        'warning_unhandled',
        'missing',
        'unknown_unhandled', // TODO: ?
        'critical_handled',
        'warning_handled',
        'unknown_handled',
        'ok',
    );

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
        // TODO: missing?
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

    protected function cssFriendly($key)
    {
        return str_replace('_', ' ', $key);
    }
}
