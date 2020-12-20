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

EXTNAME=$1
NOTESTS=$2

if [ "$NOTESTS" == "1" ]
then
	phpBB/vendor/bin/phpcs 											\
		-s															\
		--extensions=php											\
		--standard=build/code_sniffer/ruleset-php-extensions.xml	\
		--ignore=*/"$EXTNAME"/tests/*,*/"$EXTNAME"/vendor/*			\
		phpBB/ext/"$EXTNAME"
fi
