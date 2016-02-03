#!/usr/bin/env bash

export PHPBB_TEST_DBMS='phpbb\db\driver\mysqli'
export PHPBB_TEST_DBHOST='mysql'
export PHPBB_TEST_DBPORT='3306'
export PHPBB_TEST_DBNAME='phpbb_tests'
export PHPBB_TEST_DBUSER='root'
export PHPBB_TEST_DBPASSWD=''
export PHPBB_TEST_TABLE_PREFIX='phpbb_'
export DOCKER_LINK='--link mysql:mysql'

function start_db {
    cat <<EOL > /tmp/phpbb.cnf
[mysqld]
default-storage-engine=MyISAM
default-tmp-storage-engine=MyISAM
tmpdir=/dev/shm/
datadir=/dev/shm/
EOL

    docker run \
        -d \
        --volume /tmp/phpbb.cnf:/etc/mysql/conf.d/phpbb.cnf \
        --name mysql \
        --shm-size=256M \
        --env MYSQL_ROOT_PASSWORD='' \
        --env MYSQL_DATABASE='phpbb_tests' \
        --env MYSQL_ALLOW_EMPTY_PASSWORD='yes' \
        mysql

    docker run --rm --link mysql:mysql waisbrot/wait
}
