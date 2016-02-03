#!/usr/bin/env bash

pwd=$(dirname "$0")

db=$1
php=$2

. ${pwd}/../db/${db}.sh

start_db

docker run \
    --env TEST_UID=$(id -u) \
    --env TEST_GID=$(id -g) \
    ${DOCKER_LINK} \
    --env PHPBB_TEST_DBMS="${PHPBB_TEST_DBMS}" \
    --env PHPBB_TEST_DBHOST="${PHPBB_TEST_DBHOST}" \
    --env PHPBB_TEST_DBPORT="${PHPBB_TEST_DBPORT}" \
    --env PHPBB_TEST_DBNAME="${PHPBB_TEST_DBNAME}" \
    --env PHPBB_TEST_DBUSER="${PHPBB_TEST_DBUSER}" \
    --env PHPBB_TEST_DBPASSWD="${PHPBB_TEST_DBPASSWD}" \
    --env PHPBB_TEST_TABLE_PREFIX="${PHPBB_TEST_TABLE_PREFIX}" \
    --env PHPBB_FUNCTIONAL_URL="http://localhost/" \
    --volume ${WORKING_DIR}:/data \
    --workdir /data \

    phpbb/php-ft-${php}-${db}${IMAGES_TAG} php -d memory_limit=-1 phpBB/vendor/bin/phpunit --group functional --log-junit build/logs/phpunit.xml
