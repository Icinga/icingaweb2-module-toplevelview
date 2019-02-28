<?php
/* Copyright (C) 2019 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Monitoring;

trait IgnoredNotificationPeriods
{
    protected $ignoredNotificationPeriods = [];

    public function ignoreNotificationPeriod($name)
    {
        $this->ignoredNotificationPeriods[$name] = true;
        return $this;
    }

    /**
     * @param string|array|iterable $list
     *
     * @return $this
     */
    public function ignoreNotificationPeriods($list)
    {
        if (is_string($list)) {
            /** @var string $list */
            $this->ignoredNotificationPeriods[$list] = true;
        } else {
            foreach ($list as $i) {
                $this->ignoredNotificationPeriods[$i] = true;
            }
        }

        return $this;
    }

    public function getIgnoredNotificationPeriods()
    {
        return array_keys($this->ignoredNotificationPeriods);
    }

    public function resetIgnoredNotificationPeriods()
    {
        $this->ignoredNotificationPeriods = [];
    }

    public function hasIgnoredNotifications()
    {
        return ! empty($this->ignoredNotificationPeriods);
    }
}
