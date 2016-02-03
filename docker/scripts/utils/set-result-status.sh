#!/usr/bin/env bash

last_commit=$(cat build/logs/last_commit)

result_file=$1
step=$2

if [ -s build/logs/${result_file} ]; then
  res=$(cat build/logs/${result_file} 2>/dev/null)
else
  res=2
fi

if [ $res -eq 0 ]; then
  echo Send success
  $(dirname "$0")/set-status.sh 'success' 'The Bamboo build is a success' "${step}"
elif [ $res -eq 1 ]; then
  echo Send Failure
  $(dirname "$0")/set-status.sh 'failure' 'The Bamboo build failed' "${step}"
else
  echo Send error
  $(dirname "$0")/set-status.sh 'error' 'The Bamboo build is in error' "${step}"
fi
