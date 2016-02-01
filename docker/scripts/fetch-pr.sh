#!/usr/bin/env bash

docker run \
    --user $(id -u):$(id -g) \
    --volume ${WORKING_DIR}:/data \
    --workdir /data \
    phpbb/build${IMAGES_TAG} sh -c 'git remote set-url origin "https://github.com/phpbb/phpbb.git" && git fetch origin +refs/pull/'${PR_NUMBER}'/merge && git checkout FETCH_HEAD'
