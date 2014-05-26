#!/bin/sh
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

SCRIPT=$(basename "$0")
if [ "$#" -ne 1 ]; then
	echo "Description: Finds and strips ICC Profiles from image files." >&2
	echo "Usage: $SCRIPT /root/directory" >&2
	echo "Exit Status: 0 if no ICC profiles have been stripped, otherwise 1." >&2
	echo "Requires: exiftool" >&2
	exit 1
fi

ROOT=$1
STATUS=0
for FILE in $(find "$ROOT" -type f -iregex '.*\.\(gif\|jpg\|jpeg\|png\)$')
do
	HASH_OLD=$(md5sum "$FILE")
	exiftool -icc_profile"-<=" -overwrite_original_in_place "$FILE" > /dev/null 2>&1
	HASH_NEW=$(md5sum "$FILE")

	if [ "$HASH_OLD" != "$HASH_NEW" ]
	then
		echo "Stripped ICC Profile from $FILE."
		STATUS=1
	fi
done

exit $STATUS
