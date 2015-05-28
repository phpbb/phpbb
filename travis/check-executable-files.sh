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
root="$3"
path="${root}phpBB/"

if [ "$TRAVIS_PHP_VERSION" == "5.3" -a "$DB" == "mysqli" ]
then
	# Check the permissions of the files

	# The following variables MUST NOT contain any wildcard
	# Directories to skip
	directories_skipped="-path ${path}develop -o -path ${path}vendor"

	# Files to skip
	files_skipped="-false"

	# Files which have to be executable
	executable_files="-path ${path}bin/*"

	incorrect_files=$( 								\
		find ${path}								\
			'('										\
				'('									\
					${directories_skipped}			\
				')'									\
				-a -type d -prune -a -type f		\
			')' -o 									\
			'('										\
				-type f -a							\
				-not '('							\
					${files_skipped}				\
				')' -a								\
				'('									\
					'('								\
						'('							\
							${executable_files}		\
						')' -a						\
						-not -perm +100				\
					')' -o							\
					'('								\
						-not '('					\
							${executable_files}		\
						')' -a						\
						-perm +111					\
					')'								\
				')'									\
			')'										\
		)

	if [ "${incorrect_files}" != '' ]
	then
		echo "The following files do not have proper permissions:";
		ls -la ${incorrect_files}
		exit 1;
	fi
fi
