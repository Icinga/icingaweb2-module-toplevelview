<?php
/* Copyright (C) 2017 NETWAYS GmbH <support@netways.de> */

namespace Icinga\Module\Toplevelview\Tree;

use Icinga\Exception\ProgrammingError;

class TLVStatus
{
    protected $total;
    protected $critical_unhandled;
    protected $critical_handled;
    protected $warning_unhandled;
    protected $warning_handled;
    protected $unknown_unhandled;
    protected $unknown_handled;
    protected $ok;
    protected $missing;

    protected static $accessable = array(
        'critical_unhandled',
        'critical_handled',
        'warning_unhandled',
        'warning_handled',
        'unknown_unhandled',
        'unknown_handled',
        'ok',
        'missing',
        'total',
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
        foreach (static::$accessable as $key) {
            $this->add($key, $status->get($key));
        }
        // TODO: missing?
        return $this;
    }

    protected function assertAccess($key)
    {
        if (! in_array($key, static::$accessable)) {
            throw new ProgrammingError('You can not access %s', $key);
        }
    }

    public function get($key)
    {
        $this->assertAccess($key);
        return $this->$key;
    }

    public function set($key, $value)
    {
        $this->assertAccess($key);
        $this->$key = (int) $value;
        return $this;
    }

    public function add($key, $value = 1)
    {
        $this->assertAccess($key);
        if ($this->$key === null) {
            $this->$key = 0;
        }
        $this->$key += (int) $value;
        return $this;
    }

    public function zero()
    {
        foreach (static::$accessable as $key) {
            $this->$key = 0;
        }
        return $this;
    }

    public function getOverall()
    {
        foreach (static::$statusPriority as $key) {
            if ($this->$key !== null && $this->$key > 0) {
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
