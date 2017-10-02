#!/bin/sh

PHPBB_PATH="/home/vagrant/phpbb"
PHPBB_CONFIG="${PHPBB_PATH}/phpBB/config.php"
PHPBB_SQLITE="/tmp/phpbb.sqlite3"
PHPBB_INSTALL="${PHPBB_PATH}/vagrant/phpbb-install-config.yml"

# Ensure composer deps are installed
cd ${PHPBB_PATH}/phpBB
php ../composer.phar install

# Backup and remove current config.php file
if [ -e ${PHPBB_CONFIG} ]
then
    cp --backup=numbered ${PHPBB_CONFIG} ${PHPBB_CONFIG}.bak
    rm -rf ${PHPBB_CONFIG}
fi

# Delete any sqlite db
if [ -e ${PHPBB_SQLITE} ]
then
    rm -rf ${PHPBB_SQLITE}
fi

# Install phpBB
php ${PHPBB_PATH}/phpBB/install/phpbbcli.php install ${PHPBB_INSTALL}

# Update sqlite db file permissions
if [ -e ${PHPBB_SQLITE} ]
then
    sudo chown -R vagrant ${PHPBB_SQLITE}
fi

# Add DEBUG mode to phpBB to remove annoying installer warnings
echo "@define('DEBUG', true);" >> ${PHPBB_CONFIG}

# Change environment to development
sed -i '/^.*PHPBB_ENVIRONMENT.*$/s/production/development/' ${PHPBB_CONFIG}

# Display load time
sed -i '/^.*PHPBB_DISPLAY_LOAD_TIME.*$/s/^\/\/[[:blank:]]*//' ${PHPBB_CONFIG}

# Update the PHP memory limits (enough to allow phpunit tests to run)
sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php/7.1/fpm/php.ini

echo "Your board is ready at http://192.168.10.10/"
