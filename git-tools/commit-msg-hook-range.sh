#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
# Calls the git commit-msg hook on all non-merge commits in a given commit range.
#
set -e

if [ "$#" -ne 1 ];
then
	echo "Expected one argument (commit range, e.g. eef1b586...1666476b)."
	exit
fi

DIR=$(dirname "$0")
COMMIT_MSG_HOOK_PATH="$DIR/hooks/commit-msg"

COMMIT_RANGE="$1"

for COMMIT_HASH in $(git rev-list --no-merges "$COMMIT_RANGE")
do
	# The git commit-msg hook takes a path to a file containing a commit
	# message. So we have to extract the commit message into a file first,
	# which then also needs to be deleted after our work is done.
	COMMIT_MESSAGE_PATH="$DIR/commit_msg.$COMMIT_HASH"
	git log -n 1 --pretty=format:%B "$COMMIT_HASH" > "$COMMIT_MESSAGE_PATH"
	"$COMMIT_MSG_HOOK_PATH" "$COMMIT_MESSAGE_PATH"
	rm "$COMMIT_MESSAGE_PATH"
done
