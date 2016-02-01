#!/usr/bin/env bash

docker run \
    --user $(id -u):$(id -g) \
    --volume ${WORKING_DIR}:/data \
    --workdir /data \
    debian tar -p -x -z -f source_code.tar.gz
