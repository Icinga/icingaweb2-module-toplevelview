#!/bin/bash

set -e

CONFIG="$(docker-compose config)"
MYSQL_ROOT_PASSWORD="$(echo "$CONFIG" | grep MYSQL_ROOT_PASSWORD | cut -d: -f2 | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')"

set -x

docker exec -i \
    "$(docker-compose ps -q db)" \
    mysqldump -p"${MYSQL_ROOT_PASSWORD}" --no-create-info icinga \
    icinga_instances icinga_programstatus icinga_objects \
    icinga_hosts icinga_hoststatus \
    icinga_services icinga_servicestatus \
    icinga_hostgroups icinga_hostgroup_members \
    > ido-data.sql

docker exec -i \
    "$(docker-compose ps -q db)" \
    mysqldump -p"${MYSQL_ROOT_PASSWORD}" icinga \
    > full.sql
