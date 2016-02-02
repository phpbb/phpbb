#!/usr/bin/env bash

pwd=$(dirname "$0")

db=$1
php=$2

. ${pwd}/../db/${db}.sh

start_db

docker run \
    --user $(id -u):$(id -g) \
    ${DOCKER_LINK} \
    --env PHPBB_TEST_DBMS="${PHPBB_TEST_DBMS}" \
    --env PHPBB_TEST_DBHOST="${PHPBB_TEST_DBHOST}" \
    --env PHPBB_TEST_DBPORT="${PHPBB_TEST_DBPORT}" \
    --env PHPBB_TEST_DBNAME="${PHPBB_TEST_DBNAME}" \
    --env PHPBB_TEST_DBUSER="${PHPBB_TEST_DBUSER}" \
    --env PHPBB_TEST_DBPASSWD="${PHPBB_TEST_DBPASSWD}" \
    --env PHPBB_TEST_TABLE_PREFIX="${PHPBB_TEST_TABLE_PREFIX}" \
    --volume ${WORKING_DIR}:/data \
    --workdir /data \
    phpbb/php-ut-${php}-${db} php -d memory_limit=-1 phpBB/vendor/bin/phpunit --group __nogroup__ --log-junit build/logs/phpunit.xml
