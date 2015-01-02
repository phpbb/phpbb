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
	echo 'hhvm.jit = false' | sudo tee --append /etc/hhvm/server.ini
	sudo service hhvm restart
fi
