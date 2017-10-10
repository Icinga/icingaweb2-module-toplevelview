#!/bin/bash

set -ex

: ${MYSQL_ROOT_PASSWORD:=}
: ${MYSQL_USER:=}

mysqle() {
    MYSQL_PWD="${MYSQL_ROOT_PASSWORD}" mysql -u root "$@"
}

mysqle -e "CREATE DATABASE icinga"
mysqle -e "GRANT ALL ON icinga.* TO '${MYSQL_USER}'@'%'"
mysqle icinga < /docker-entrypoint-initdb.d/icinga/ido-mysql.sql
mysqle icinga < /docker-entrypoint-initdb.d/icinga/ido-data.sql
