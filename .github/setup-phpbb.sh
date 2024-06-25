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
PHP_VERSION=$2
NOTESTS=$3

if [ "$NOTESTS" == '1' ]
then
	.github/setup-exiftool.sh
	.github/setup-unbuffer.sh
fi

if [ "$NOTESTS" != '1' ]
then
	.github/setup-webserver.sh
fi

cd phpBB
php ../composer.phar install --dev --no-interaction
cd ..
