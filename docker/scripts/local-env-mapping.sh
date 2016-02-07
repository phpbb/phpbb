#!/usr/bin/env bash

export IMAGES_TAG=':latest'

export WORKING_DIR="$(git rev-parse --show-toplevel)"
export PR_NUMBER=''
export GITHUB_TOKEN="$(cd "$WORKING_DIR"/phpBB;php ../composer.phar config github-oauth.github.com)"
export COMPOSER_HOME="$(cd "$WORKING_DIR"/phpBB;php ../composer.phar config home)"
export BUILD_RESULT_URL=''
