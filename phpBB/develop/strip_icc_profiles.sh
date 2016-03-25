#!/bin/sh
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#

if [ "$#" -ne 1 ]
then
	SCRIPT=$(basename "$0")
	echo "Description: Finds and strips ICC Profiles from given image file." >&2
	echo "Usage: $SCRIPT /path/to/image/file" >&2
	echo "Exit Status: 0 if no ICC profiles have been stripped, otherwise 1." >&2
	echo "Requires: exiftool" >&2
	exit 1
fi

FILE=$1
HASH_OLD=$(md5sum "$FILE")
exiftool -icc_profile"-<=" -overwrite_original_in_place "$FILE" > /dev/null 2>&1
HASH_NEW=$(md5sum "$FILE")

if [ "$HASH_OLD" != "$HASH_NEW" ]
then
	echo "Stripped ICC Profile from $FILE."
	exit 1
fi
