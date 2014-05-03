#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

EXTNAME=$1
BRANCH=$2

# Move the extension in place
mkdir --parents phpBB/ext/$EXTNAME
cp -R ../tmp/* phpBB/ext/$EXTNAME

# Move the extensions travis/phpunit-*-travis.xml files in place
cp -R travis/* phpBB/ext/$EXTNAME/travis
