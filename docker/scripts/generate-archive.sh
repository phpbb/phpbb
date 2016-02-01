#!/usr/bin/env bas

cat <<EOL > generate-archive.sh
git remote set-url origin "https://github.com/phpbb/phpbb.git"
git fetch origin +refs/pull/${PR_NUMBER}/head
last_commit=\$(git rev-parse FETCH_HEAD)
echo "\$last_commit" > build/logs/last_commit
tar --exclude-backups --exclude-vcs --exclude='.git' --exclude='generate-archive.sh' -p -c -z -f source_code.tar.gz *
EOL

docker run \
    --user $(id -u):$(id -g) \
    --volume ${WORKING_DIR}:/data \
    --workdir /data \
    phpbb/build{IMAGES_TAG} sh generate-archive.sh

rm generate-archive.sh
