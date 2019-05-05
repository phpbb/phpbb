#!/bin/sh

PHPBB_PATH="/home/vagrant/phpbb"
PHPBB_CONFIG="${PHPBB_PATH}/phpBB/config.php"
PHPBB_INSTALL="${PHPBB_PATH}/vagrant/phpbb-install-config.yml"

# Ensure composer deps are installed
cd ${PHPBB_PATH}/phpBB
php7.2 ../composer.phar install --ignore-platform-reqs

# Backup current config.php file
if [ -e ${PHPBB_CONFIG} ]
then
    cp --backup=numbered ${PHPBB_CONFIG} ${PHPBB_CONFIG}.bak
    rm -rf ${PHPBB_CONFIG}
fi

# Install phpBB
php7.2 ${PHPBB_PATH}/phpBB/install/phpbbcli.php install ${PHPBB_INSTALL}

# Add DEBUG mode to phpBB to remove annoying installer warnings
sed -i "$ a @define('DEBUG', true);" ${PHPBB_CONFIG}

# Update the PHP memory limits (enough to allow phpunit tests to run)
sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php7.2/fpm/php.ini

echo "Your board is ready at http://192.168.10.10/"
