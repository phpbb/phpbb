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

if [ "$TRAVIS_PHP_VERSION" == "5.5" -a "$DB" == "mysqli" ]
then
	find . -type f -not -path './phpBB/vendor/*' -iregex '.*\.\(gif\|jpg\|jpeg\|png\)$' | \
		parallel --gnu --keep-order 'phpBB/develop/strip_icc_profiles.sh {}'
fi
