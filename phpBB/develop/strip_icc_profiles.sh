#!/bin/sh
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
