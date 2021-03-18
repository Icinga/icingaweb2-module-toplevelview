#!/bin/bash

set -ex

: ${MYSQL_ROOT_PASSWORD:=}
: ${MYSQL_USER:=}

export MYSQL_PWD="${MYSQL_ROOT_PASSWORD}"

mysql -e "DROP DATABASE IF EXISTS icinga"
mysql -e "CREATE DATABASE icinga"
mysql -e "GRANT ALL ON icinga.* TO '${MYSQL_USER}'@'%'"
mysql -e "DROP DATABASE IF EXISTS icinga_legacy"
mysql -e "CREATE DATABASE icinga_legacy"
mysql -e "GRANT ALL ON icinga_legacy.* TO '${MYSQL_USER}'@'%'"
mysql icinga < /docker-entrypoint-initdb.d/icinga/ido-mysql.sql
mysql icinga < /docker-entrypoint-initdb.d/icinga/ido-data.sql
mysql icinga_legacy < /docker-entrypoint-initdb.d/icinga/ido-mysql.sql
mysql icinga_legacy < /docker-entrypoint-initdb.d/icinga/ido-legacy-data.sql
