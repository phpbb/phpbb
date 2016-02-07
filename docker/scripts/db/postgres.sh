#!/usr/bin/env bash

export PHPBB_TEST_DBMS='phpbb\db\driver\postgres'
export PHPBB_TEST_DBHOST='postgres'
export PHPBB_TEST_DBPORT='5432'
export PHPBB_TEST_DBNAME='phpbb_tests'
export PHPBB_TEST_DBUSER='postgres'
export PHPBB_TEST_DBPASSWD=''
export PHPBB_TEST_TABLE_PREFIX='phpbb_'
export DOCKER_LINK='--link postgres:postgres'

function start_db {
    docker run \
        -d \
        --name postgres \
        --env POSTGRES_PASSWORD='' \
        --env POSTGRES_USER='postgres' \
        postgres

    docker run --rm --link postgres:postgres waisbrot/wait
    sleep 1

    docker run --link postgres:postgres --rm postgres sh -c 'exec psql -h "$POSTGRES_PORT_5432_TCP_ADDR" -p "$POSTGRES_PORT_5432_TCP_PORT" -U postgres -c "create database phpbb_tests;"'
}
