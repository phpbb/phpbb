#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

DB=$1
TRAVIS_PHP_VERSION=$2

if [ "$DB" == "postgres" ]
then
	psql -c 'DROP DATABASE IF EXISTS phpbb_tests;' -U postgres
	psql -c 'create database phpbb_tests;' -U postgres
fi

if [ "$TRAVIS_PHP_VERSION" == "5.3" -a "$DB" == "mysqli" ]
then
	mysql -e 'SET GLOBAL storage_engine=MyISAM;'
fi

if [ "$DB" == "mysql" -o "$DB" == "mysqli" -o "$DB" == "mariadb" ]
then
	mysql -e 'create database IF NOT EXISTS phpbb_tests;'
fi
