<?php
/* Copyright (C) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Monitoring;

use Icinga\Data\ConnectionInterface;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Monitoring\DataView\ServiceStatus as IcingaServiceStatus;

class ServiceStatus extends IcingaServiceStatus
{
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * @param ConnectionInterface $connection
     * @param array|null          $columns
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(ConnectionInterface $connection, array $columns = null)
    {
        /** @var MonitoringBackend $connection */
        $this->connection = $connection;
        $this->query = new ServicestatusQuery($connection->getResource(), $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return array_merge(
            parent::getColumns(),
            array(
                //'service_in_notification_period',
                'servicenotificationperiod_name',
            )
        );
    }
}
