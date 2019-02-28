<?php
/* Copyright (C) 2019 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Monitoring;

trait Options
{
    protected $options = [];

    public function getOption($key)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return null;
    }

    public function setOptions($options, $flush = false)
    {
        if ($flush) {
            $this->options = [];
        }

        if (! empty($options)) {
            foreach ($options as $k => $v) {
                $this->options[$k] = $v;
            }
        }

        return $this;
    }
}
