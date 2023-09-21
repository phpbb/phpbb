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

sudo apt-get -y install ldap-utils slapd
mkdir /var/tmp/slapd
cp .github/ldap/slapd.conf /var/tmp/slapd/slapd.conf
slapd -d 256 -d 128 -f /var/tmp/slapd/slapd.conf -h ldap://localhost:3389 &
sleep 3
ldapadd -H ldap://localhost:3389 -D "cn=admin,dc=example,dc=com" -w adminadmin -f .github/ldap/base.ldif
