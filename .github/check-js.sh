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

sudo npm install -g > /dev/null
npm ci > /dev/null
set -x
node_modules/eslint/bin/eslint.js "phpBB/**/*.js"
node_modules/eslint/bin/eslint.js "phpBB/**/*.js.twig"
node_modules/eslint/bin/eslint.js "gulpfile.js"
