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
	# Define a node version.
	TRAVIS_NODE_VERSION="4"

	# Clear out whatever version of NVM Travis has.
	# Their version of NVM is probably old.
	rm -rf ~/.nvm
	# Grab NVM.
	git clone https://github.com/creationix/nvm.git ~/.nvm > /dev/null
	# Checkout the latest stable tag.
	# Note that you can just hardcode a preferred version here.
	(cd ~/.nvm && git checkout `git describe --abbrev=0 --tags`)
	# Install the desired version of Node
	source ~/.nvm/nvm.sh
	nvm install $TRAVIS_NODE_VERSION > /dev/null
	npm install -g > /dev/null
	npm install > /dev/null
	set -x
	stylelint "phpBB/styles/prosilver/theme/*.css"
	# Disable admin stylelint for now
	stylelint "phpBB/adm/style/*.css"
fi
