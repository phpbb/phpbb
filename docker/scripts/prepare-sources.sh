#!/usr/bin/env bash

# Ensure the github oauth token is set
docker run \
    --user $(id -u):$(id -g) \
    --volume ${WORKING_DIR}:/data \
    --volume ${COMPOSER_HOME}:/composer/ \
    --workdir /data \
    phpbb/build${IMAGES_TAG} sh -c "COMPOSER_HOME=/composer php composer.phar config -g github-oauth.github.com ${GITHUB_TOKEN}"

docker run \
    --user $(id -u):$(id -g) \
    --volume ${WORKING_DIR}:/data \
    --volume ${COMPOSER_HOME}:/composer/ \
    --workdir /data \
    phpbb/build${IMAGES_TAG} sh -c '
    cd phpBB &&
    git config user.email "no-reply@phpbb.com" &&
    git config user.name "phpBB CI" &&
    COMPOSER_HOME=/composer php ../composer.phar install --dev'

docker run \
    --user $(id -u):$(id -g) \
    --volume ${WORKING_DIR}:/data \
    --workdir /data \
    phpbb/build${IMAGES_TAG} sh -c 'cd build; ../phpBB/vendor/bin/phing clean prepare'
