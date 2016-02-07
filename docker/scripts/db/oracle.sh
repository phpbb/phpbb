#!/usr/bin/env bash

export PHPBB_TEST_DBMS='phpbb\db\driver\oracle'
export PHPBB_TEST_DBHOST='oracle'
export PHPBB_TEST_DBPORT='1521'
export PHPBB_TEST_DBNAME='xe'
export PHPBB_TEST_DBUSER='system'
export PHPBB_TEST_DBPASSWD='oracle'
export PHPBB_TEST_TABLE_PREFIX='phpbb_'
export DOCKER_LINK='--link oracle:oracle'

function start_db {
    docker run -d --name oracle wnameless/oracle-xe-11g
    docker run --rm --link oracle:oracle -e TARGETS=oracle:1521 waisbrot/wait
}
