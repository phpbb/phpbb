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
EXTNAME=$3

if [ "$TRAVIS_PHP_VERSION" == "5.5" -a "$DB" == "mysqli" ]
then
	phpBB/vendor/bin/phpcs 											\
		-s															\
		--extensions=php											\
		--standard=build/code_sniffer/ruleset-php-extensions.xml	\
		"--ignore=phpBB/ext/$EXTNAME/tests/*"						\
		"--ignore=phpBB/ext/$EXTNAME/vendor/*"						\
		"phpBB/ext/$EXTNAME"
fi
