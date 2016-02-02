#!/usr/bin/env bash

last_commit=$(cat build/logs/last_commit) 

status=$1
description=$2
step=$3

echo '---------------------'
echo curl -H "Authorization: token ${GITHUB_TOKEN}" --request POST --data "{\"state\": \"${status}\", \"description\": \"${description}\", \"target_url\": \"${BUILD_RESULT_URL}\", \"context\": \"phpBB continuous integration - ${step}\"}" https://api.github.com/repos/phpbb/phpbb/statuses/${last_commit}
echo '---------------------'
curl -H "Authorization: token ${GITHUB_TOKEN}" --request POST --data "{\"state\": \"${status}\", \"description\": \"${description}\", \"target_url\": \"${BUILD_RESULT_URL}\", \"context\": \"phpBB continuous integration - ${step}\"}" https://api.github.com/repos/phpbb/phpbb/statuses/${last_commit}
