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
	if [ ! -f doctum.phar ]; then
		# Download the latest (5.1.x) release if the file does not exist
		# Remove it to update your phar
		curl -O https://doctum.long-term.support/releases/5.1/doctum.phar
		rm -f doctum.phar.sha256
		curl -O https://doctum.long-term.support/releases/5.1/doctum.phar.sha256
		sha256sum --strict --check doctum.phar.sha256
		rm -f doctum.phar.sha256
		# You can fetch the latest (5.1.x) version code here:
		# https://doctum.long-term.support/releases/5.1/VERSION
	fi
	# Show the version to inform users of the script
	php doctum.phar --version
	php doctum.phar parse build/doctum-checkout.conf.php -v
fi
