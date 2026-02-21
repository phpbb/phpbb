#!/usr/bin/env bash

########################################################################
#
# Say hello
#
########################################################################
echo ""
echo -e "\033[0;32mphpBB Docker script\033[0m"
echo ""
echo "This script will start you a Docker development environment for phpBB."
echo "Please keep in mind, that the script never removes the containers, so you"
echo "will have to do that manually. Each container name contains the branch name"
echo "from which it was created, so you can easily identify what you do and do"
echo "not need anymore."
echo ""
echo "For more details and information on how to use this script, please refer to"
echo "the README.md file."
echo ""

########################################################################
#
# Get the path to phpBB.
#
########################################################################
if [ -z ${PHPBB_ROOT_PATH+x} ]; then
    echo "PHPBB_ROOT_PATH is not set. Please set it by 'export PHPBB_ROOT_PATH=/path/to/phpbb' or add this string to your .bashrc"
    exit
fi

########################################################################
#
# Configurable parameters.
#
########################################################################
DATABASE_TYPE="mysql"
DATABASE_VERSION="5.6"
PHP_VERSION="8.2"
SERVER_TYPE="nginx"
PHPBB_ENVIRONMENT="development"

########################################################################
#
# Process parameters
#
########################################################################
while [[ $# -gt 0 ]]
do
    KEY="$1"
    case $KEY in
        -p|--php-version)
        PHP_VERSION="$2"
        shift
        shift
        ;;
        -e|--environment)
        PHPBB_ENVIRONMENT="$2"
        shift
        shift
        ;;
    esac
done

########################################################################
#
# Internal parameters.
#
########################################################################
SCRIPT_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
INSTALL_DEPENDENCIES=0
INSTALL_PHPBB=0

########################################################################
#
# Cleanup script.
#
########################################################################
function cleanup {
    echo -n "Shuting down... "

    docker stop $SERVER_CONTAINER_NAME > /dev/null

    docker network disconnect $NETWORK_NAME $PHP_CONTAINER_NAME > /dev/null
    docker network disconnect $NETWORK_NAME $DATABASE_CONTAINER_NAME > /dev/null

    docker stop $PHP_CONTAINER_NAME > /dev/null
    docker stop $DATABASE_CONTAINER_NAME > /dev/null
    docker stop $STORAGE_CONTAINER_NAME > /dev/null

    docker rm $SERVER_CONTAINER_NAME > /dev/null

    docker network rm $NETWORK_NAME > /dev/null

    if [ -d $SCRIPT_PATH/tmp ]; then
        rm -rf $SCRIPT_PATH/tmp > /dev/null
    fi

    echo -e "\033[0;32mdone\033[0m"
}

trap cleanup EXIT

########################################################################
#
# Get the branch name.
#
########################################################################
cd ${PHPBB_ROOT_PATH}
BRANCHNAME=$(git rev-parse --abbrev-ref HEAD | sed "s/[^0-9a-zA-Z\-]/-/g")

########################################################################
#
# Check provided information.
#
########################################################################
#
# Figure out the XDebug version...
#
# Figure out the exact PHP version...
docker run -d --name phpbb-php-version-test-container php:$PHP_VERSION-fpm > /dev/null
PHP_VERSION_STRING=$(docker exec phpbb-php-version-test-container php --version)
docker stop phpbb-php-version-test-container > /dev/null
docker rm phpbb-php-version-test-container > /dev/null

PHP_VERSION_STRING=$(echo $PHP_VERSION_STRING | sed -e 's/^PHP \([0-9]\+\.[0-9]\+\.[0-9]\+\).*/\1/')
PHP_MAJOR_VERSION=$(echo $PHP_VERSION_STRING | sed -e 's/\([0-9]\+\)\.[0-9]\+\.[0-9]\+.*/\1/')
PHP_MINOR_VERSION=$(echo $PHP_VERSION_STRING | sed -e 's/[0-9]\+\.\([0-9]\+\)\.[0-9]\+.*/\1/')

if [ $PHP_MAJOR_VERSION -eq 5 ]; then
    if [ $PHP_MINOR_VERSION -gt 4 ]; then
        XDEBUG_VERSION="2.5.5"
    else
        XDEBUG_VERSION="2.4.1"
    fi
elif [ $PHP_MAJOR_VERSION -eq 7 ]; then
    if [ $PHP_MINOR_VERSION -ge 3 ]; then
        XDEBUG_VERSION="2.7.0beta1"
    else
        XDEBUG_VERSION="2.6.1"
    fi
elif [ $PHP_MAJOR_VERSION -eq 8 ]; then
	XDEBUG_VERSION="3.2.2"
else
    echo "PHP version is unsupported..."
    exit;
fi

########################################################################
#
# Create a network
#
########################################################################
NETWORK_NAME="phpbb-network-$BRANCHNAME"
echo -n "Creating the network... "
docker network create --driver bridge $NETWORK_NAME > /dev/null
echo -e "\033[0;32mdone\033[0m"

########################################################################
#
# Create filesystem...
#
########################################################################

STORAGE_CONTAINER_NAME="phpbb-storage-$BRANCHNAME"
echo -n "Starting storage container... "

if [ ! "$(docker ps -a -q -f name=$STORAGE_CONTAINER_NAME)" ]; then
    # Create a storage container.
    docker run \
        --name $STORAGE_CONTAINER_NAME \
        -v $PHPBB_ROOT_PATH:/var/www \
        -v /var/www/phpBB/cache \
        -v /var/www/phpBB/files \
        -v /var/www/phpBB/store \
        -v /var/www/phpBB/images/avatars/upload \
        -v /var/www/phpBB/vendor \
        -v /var/www/phpBB/vendor-ext \
        -v /var/www/html \
        -d -i -t ubuntu > /dev/null

    docker exec -d $STORAGE_CONTAINER_NAME rm /var/www/phpBB/config.php

    INSTALL_DEPENDENCIES=1
    INSTALL_PHPBB=1
else
    docker start $STORAGE_CONTAINER_NAME > /dev/null
fi

echo -e "\033[0;32mdone\033[0m"

########################################################################
#
# Spin up the database.
#
########################################################################
DATABASE_CONTAINER_NAME="phpbb-$DATABASE_TYPE-$DATABASE_VERSION-$BRANCHNAME"

echo -n "Starting database container ($DATABASE_TYPE:$DATABASE_VERSION)... "
if [ ! "$(docker ps -a -q -f name=$DATABASE_CONTAINER_NAME)" ]; then
    # Set the database config.
    cat <<EOL > /tmp/mysql.cnf
[mysqld]
default-storage-engine=MyISAM
default-tmp-storage-engine=MyISAM
tmpdir=/dev/shm/
datadir=/dev/shm/
EOL

    # Create a new conteiner.
    docker run \
        --name $DATABASE_CONTAINER_NAME \
        -p 3306:3306 \
        -e MYSQL_ROOT_PASSWORD=supersecret \
        -e MYSQL_DATABASE=phpbb \
        -e MYSQL_USER=phpbb \
        -e MYSQL_PASSWORD=phpbb \
        -d $DATABASE_TYPE:$DATABASE_VERSION \
        --character-set-server=utf8mb4 \
        --collation-server=utf8mb4_unicode_ci > /dev/null
else
    # Spin up the container.
    docker start $DATABASE_CONTAINER_NAME > /dev/null
fi

# Connect the container to the network
docker network connect \
    --alias db \
    $NETWORK_NAME $DATABASE_CONTAINER_NAME > /dev/null

echo -e "\033[0;32mdone\033[0m"

########################################################################
#
# Spin up PHP-FPM...
#
########################################################################
echo -n "Starting PHP-FPM (version: $PHP_VERSION)... "

PHP_IMAGE_NAME="phpbb/php:$PHP_VERSION-fpm"
PHP_CONTAINER_NAME="phpbb-php-$BRANCHNAME"

if [ ! "$(docker ps -a -q -f name=$PHP_CONTAINER_NAME)" ]; then
    # Generate the image if necessary.
    if [ ! "$(docker images -q $PHP_IMAGE_NAME)" ]; then
        echo ""
        echo -n "    Create new PHP image... "
        echo -n -e "\033[1;33mbuilding... \033[0m"

        mkdir $SCRIPT_PATH/tmp > /dev/null
        cd $SCRIPT_PATH/tmp > /dev/null
        touch Dockerfile > /dev/null

        echo "FROM php:$PHP_VERSION-fpm" >> Dockerfile
        echo "" >> Dockerfile
        echo "RUN apt-get update && apt-get install -y \\" >> Dockerfile
        echo "  libzip-dev zip \\" >> Dockerfile

        echo "  && docker-php-ext-install zip \\" >> Dockerfile
        echo "  && docker-php-ext-install mysqli pdo_mysql \\" >> Dockerfile
        # echo "  && pecl install xdebug-$XDEBUG_VERSION && docker-php-ext-enable xdebug \\" >> Dockerfile

        echo "  && rm -rf /var/lib/apt/lists/*" >> Dockerfile

        echo "  RUN echo '[xdebug]' > /usr/local/etc/php/conf.d/xdebug.ini" >> Dockerfile
        echo "  RUN echo 'xdebug.mode = debug,develop' >> /usr/local/etc/php/conf.d/xdebug.ini" >> Dockerfile
        echo "  RUN echo 'xdebug.start_with_request = yes' >> /usr/local/etc/php/conf.d/xdebug.ini" >> Dockerfile
        echo "  RUN echo 'xdebug.discover_client_host = 0' >> /usr/local/etc/php/conf.d/xdebug.ini" >> Dockerfile
        echo "  RUN echo 'xdebug.client_host = host.docker.internal' >> /usr/local/etc/php/conf.d/xdebug.ini" >> Dockerfile
        echo "  RUN echo 'xdebug.client_port = 9003' >> /usr/local/etc/php/conf.d/xdebug.ini" >> Dockerfile

        exec 3>&2
        exec 2> /dev/null
        docker build -t $PHP_IMAGE_NAME . > /dev/null
        exec 2>&3
        rm -rf $SCRIPT_PATH/tmp > /dev/null

        echo -e "\033[0;32mdone\033[0m"
    fi

    # Create container.
    docker run -d \
        --name $PHP_CONTAINER_NAME \
        --volumes-from $STORAGE_CONTAINER_NAME \
        $PHP_IMAGE_NAME > /dev/null
else
    # Restart the docker container
    docker start $PHP_CONTAINER_NAME > /dev/null
fi

# Attach it to the network.
docker network connect \
    --alias php \
    $NETWORK_NAME $PHP_CONTAINER_NAME > /dev/null

echo -e "\033[0;32mdone\033[0m"

########################################################################
#
# Install composer dependencies...
#
########################################################################
if [ $INSTALL_DEPENDENCIES -eq 1 ]; then
    echo -n "Installing composer dependencies... "
    echo -n -e "\033[1;33minstalling... \033[0m"

    exec 3>&2
    exec 2> /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && php ../composer.phar install' # > /dev/null
    exec 2>&3

    echo -e "\033[0;32mdone\033[0m"
fi

########################################################################
#
# Install phpBB...
#
########################################################################
if [ $INSTALL_PHPBB -eq 1 ]; then
    echo -n "Installing phpBB... "
    echo -n -e "\033[1;33minstalling... \033[0m"

    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data cache' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data files' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data store' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data images/avatars/upload' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && touch config.php && chown www-data:www-data config.php' > /dev/null

    docker exec $PHP_CONTAINER_NAME /bin/bash -c "cat <<EOL > /var/www/install_config.yml
installer:
    admin:
        name: admin
        password: adminadmin
        email: admin@example.org

    board:
        lang: en
        name: My Board
        description: My amazing new phpBB board

    database:
        dbms: mysqli
        dbhost: db
        dbport: ~
        dbuser: phpbb
        dbpasswd: phpbb
        dbname: phpbb
        table_prefix: phpbb_

    email:
        enabled: false
        smtp_delivery : ~
        smtp_host: ~
        smtp_port: ~
        smtp_user: ~
        smtp_pass: ~

    server:
        cookie_secure: false
        server_protocol: http://
        force_server_vars: false
        server_name: phpbb.local
        server_port: 80
        script_path: /

    extensions: []
EOL" > /dev/null

    # Install phpBB
    exec 3>&2
    exec 2> /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/ && php phpBB/install/phpbbcli.php install install_config.yml' > /dev/null
    exec 2>&3

    # Hack config.php
    docker exec --env PHPBB_ENVIRONMENT=$PHPBB_ENVIRONMENT $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && cat config.php | sed -i "s/@define('\''PHPBB_ENVIRONMENT'\'', '\''production'\'');/@define('\''PHPBB_ENVIRONMENT'\'', '\''$PHPBB_ENVIRONMENT'\'');/" config.php' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && echo "@define('\''DEBUG'\'', true);" >> config.php' > /dev/null

    # Restore permissions
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data cache' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data files' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data store' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && chown www-data:www-data images/avatars/upload' > /dev/null
    docker exec $PHP_CONTAINER_NAME /bin/bash -c 'cd /var/www/phpBB && touch config.php && chown www-data:www-data config.php' > /dev/null

    echo -e "\033[0;32mdone\033[0m"
fi

########################################################################
#
# Spin up a server...
#
########################################################################
echo -n "Starting the server ($SERVER_TYPE)... "
SERVER_CONTAINER_NAME="phpbb-$SERVER_TYPE-$BRANCHNAME"

if [ ! "$(docker ps -a -q -f name=$SERVER_CONTAINER_NAME)" ]; then
    docker run -d \
        --name $SERVER_CONTAINER_NAME \
        -p 80:80 \
        -p 443:443 \
        --volumes-from $STORAGE_CONTAINER_NAME \
        -v $SCRIPT_PATH/data/nginx.conf:/etc/nginx/conf.d/site.conf:ro \
        --network $NETWORK_NAME \
        nginx:latest > /dev/null
else
    docker start $SERVER_CONTAINER_NAME
fi

echo -e "\033[0;32mdone\033[0m"

########################################################################
#
# Final touches...
#
########################################################################
#
# Work around the host.docker.internal issue on linux...
#
if [[ $(uname -s) == Linux* ]]; then
    docker exec \
        -e HOST_IP=$(ip addr show docker0 | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1') \
        $PHP_CONTAINER_NAME /bin/bash -c 'echo "$HOST_IP host.docker.internal" >> /etc/hosts' > /dev/null
fi

########################################################################
#
# Wait for a key press and clean up...
#
########################################################################
echo ""
echo "Your development environment is ready and should be available at http://phpbb.local"
echo ""
read -p "Press [enter] to quit..."
echo ""
