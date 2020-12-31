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

# Move the extension in place
mkdir --parents phpBB/ext/$EXTNAME
cp -R ../tmp/* phpBB/ext/$EXTNAME

# Move the test files for extensions in place
cp -R .github/*.xml phpBB/ext/$EXTNAME/.github
cp -R .github/*.sh phpBB/ext/$EXTNAME/.github
