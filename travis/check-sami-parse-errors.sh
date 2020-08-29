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

DB=$1
TRAVIS_PHP_VERSION=$2
NOTESTS=$3

if [ "$NOTESTS" == '1' ]
then
	phpBB/vendor/bin/doctum.php parse build/doctum-checkout.conf.php -v
fi
