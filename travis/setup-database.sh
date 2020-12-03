#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
set -e
set -x

DB=$1
TRAVIS_PHP_VERSION=$2
NOTESTS=$3

if [ "$NOTESTS" == '1' ]
then
	exit 0
fi

if [ "$DB" == "postgres" ]
then
	psql -c 'DROP DATABASE IF EXISTS phpbb_tests;' -U postgres
	psql -c 'create database phpbb_tests;' -U postgres
fi

if [ "$TRAVIS_PHP_VERSION" == "5.6" -a "$DB" == "mysql" ]
then
	mysql -e 'SET GLOBAL storage_engine=MyISAM;'
fi
