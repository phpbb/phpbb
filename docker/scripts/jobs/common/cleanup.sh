#!/usr/bin/env bash

# We assume the docker daemon is dedicated to the current job (the jobs runs on isolated docker daemon)
# Stop running containers
for container in $(docker ps -q)
do
    docker stop $container || true
done

# Removing containers
for container in $(docker ps -a -q)
do
    docker rm -v $container || true
done
