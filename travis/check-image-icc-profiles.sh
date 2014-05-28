#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e

DB=$1
TRAVIS_PHP_VERSION=$2

if [ "$TRAVIS_PHP_VERSION" == "5.5" -a "$DB" == "mysqli" ]
then
	find . -type f -not -path './phpBB/vendor/*' -iregex '.*\.\(gif\|jpg\|jpeg\|png\)$' | \
		parallel --gnu --keep-order 'phpBB/develop/strip_icc_profiles.sh {}'
fi
