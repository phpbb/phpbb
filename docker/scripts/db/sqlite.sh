#!/usr/bin/env bash

export PHPBB_TEST_DBMS='phpbb\db\driver\sqlite3'
export PHPBB_TEST_DBHOST='/dev/shm/phpbb_unit_tests.sqlite3'
export PHPBB_TEST_DBPORT=''
export PHPBB_TEST_DBNAME=''
export PHPBB_TEST_DBUSER=''
export PHPBB_TEST_DBPASSWD=''
export PHPBB_TEST_TABLE_PREFIX='phpbb_'
export DOCKER_LINK=''

function start_db {
    true
}
