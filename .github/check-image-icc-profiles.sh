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

find . -type f -a -iregex '.*\.\(gif\|jpg\|jpeg\|png\)$' -a -not -wholename '*vendor/*' | \
	parallel --gnu --keep-order 'phpBB/develop/strip_icc_profiles.sh {}'
