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

SLOWTESTS=$1

if [ "$SLOWTESTS" == '1' ]
then
	sudo apt-get -y install ldap-utils slapd php-ldap
	mkdir /tmp/slapd
	slapd -f travis/ldap/slapd.conf -h ldap://localhost:3389 &
	sleep 3
	ldapadd -h localhost:3389 -D "cn=admin,dc=example,dc=com" -w adminadmin -f travis/ldap/base.ldif
fi
