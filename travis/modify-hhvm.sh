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

TRAVIS_PHP_VERSION=$1

if [ "$TRAVIS_PHP_VERSION" == "hhvm" ]
then
	sed -n '1h;1!H;${;g;s/hhvm.server.port/hhvm.jit = false\nhhvm.server.port/g;p;}' /etc/hhvm/server.ini &> /etc/hhvm/server.ini.bak
	cp /etc/server.ini.bak /etc/server.ini
	sudo service hhvm restart
fi
