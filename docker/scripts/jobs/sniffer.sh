#!/usr/bin/env bash

docker run \
    --user $(id -u):$(id -g) \
    --volume ${WORKING_DIR}:/data \
    --workdir /data \
    php:5.6 sh -c '
    cd build &&
    ../phpBB/vendor/bin/phing sniff &&
    echo 0 > logs/sniffs_res ||
    echo 1 > logs/sniffs_res'
