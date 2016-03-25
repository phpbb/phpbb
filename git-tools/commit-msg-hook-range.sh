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
# Calls the git commit-msg hook on all non-merge commits in a given commit range.
#

if [ "$#" -ne 1 ];
then
	echo "Expected one argument (commit range, e.g. phpbb/develop..ticket/12345)."
	exit
fi

DIR=$(dirname "$0")
COMMIT_RANGE="$1"
COMMIT_MSG_HOOK_PATH="$DIR/hooks/commit-msg"
COMMIT_MSG_HOOK_FATAL=$(git config --bool phpbb.hooks.commit-msg.fatal 2> /dev/null)
git config phpbb.hooks.commit-msg.fatal true

EXIT_STATUS=0
for COMMIT_HASH in $(git rev-list --no-merges "$COMMIT_RANGE")
do
	echo "Inspecting commit message of commit $COMMIT_HASH"

	# The git commit-msg hook takes a path to a file containing a commit
	# message. So we have to extract the commit message into a file first,
	# which then also needs to be deleted after our work is done.
	COMMIT_MESSAGE_PATH="$DIR/commit_msg.$COMMIT_HASH"
	git log -n 1 --pretty=format:%B "$COMMIT_HASH" > "$COMMIT_MESSAGE_PATH"

	# Invoke hook on commit message file.
	"$COMMIT_MSG_HOOK_PATH" "$COMMIT_MESSAGE_PATH"

	# If any commit message hook complains with a non-zero exit status, we
	# will send a non-zero exit status upstream.
	if [ $? -ne 0 ]
	then
		EXIT_STATUS=1
	fi

	rm "$COMMIT_MESSAGE_PATH"
done

# Restore phpbb.hooks.commit-msg.fatal config
if [ -n "$COMMIT_MSG_HOOK_FATAL" ]
then
	git config phpbb.hooks.commit-msg.fatal "$COMMIT_MSG_HOOK_FATAL"
fi

exit $EXIT_STATUS
