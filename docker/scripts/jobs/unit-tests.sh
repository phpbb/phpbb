#!/usr/bin/env bash

pwd=$(dirname "$0")

db=$1
php=$2

. ${pwd}/../db/${db}.sh

start_db

docker run \
    --user $(id -u):$(id -g) \
    --link mysql:mysql \
    --env PHPBB_TEST_DBMS="phpbb\db\driver\mysqli" \
    --env PHPBB_TEST_DBHOST="mysql" \
    --env PHPBB_TEST_DBPORT="3306" \
    --env PHPBB_TEST_DBNAME="phpbb_tests" \
    --env PHPBB_TEST_DBUSER="root" \
    --env PHPBB_TEST_DBPASSWD="" \
    --env PHPBB_TEST_TABLE_PREFIX="phpbb_" \
    --volume ${bamboo.working.directory}:/data \
    --workdir /data \
    phpbb/php-ut-5.6-mysql php -d memory_limit=-1 phpBB/vendor/bin/phpunit --group __nogroup__ --log-junit build/logs/phpunit.xml
