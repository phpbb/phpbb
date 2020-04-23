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
set +x

NOTESTS=$1

if [ "$NOTESTS" == '1' ]
then
	npm install -g > /dev/null
	npm install > /dev/null
	set -x
	node_modules/xo/cli.js "phpBB/adm/style/*.js"
	node_modules/xo/cli.js "phpBB/assets/javascript/*.js"
	node_modules/xo/cli.js "phpBB/style/all/js/*.js"
	node_modules/xo/cli.js "phpBB/style/prosilver/template/*.js"
fi
