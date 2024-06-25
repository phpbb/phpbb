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
MYISAM=$2

if [ "$DB" == "postgres" ]
then
	psql -c 'DROP DATABASE IF EXISTS phpbb_tests;' -U postgres
	psql -c 'create database phpbb_tests;' -U postgres
fi

if [ "$MYISAM" == '1' ]
then
	mysql -h 127.0.0.1 -u root -e 'SET GLOBAL default_storage_engine=MyISAM;'
fi
