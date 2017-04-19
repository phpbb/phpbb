#!/bin/sh

PHPBB_PATH="/home/vagrant/phpbb"
PHPBB_CONFIG="${PHPBB_PATH}/phpBB/config.php"
PHPBB_INSTALL="${PHPBB_PATH}/vagrant/phpbb-install-config.yml"

# Ensure composer deps are installed
cd ${PHPBB_PATH}/phpBB
php ../composer.phar install

# Backup current config.php file
if [ -e ${PHPBB_CONFIG} ]
then
    cp --backup=numbered ${PHPBB_CONFIG} ${PHPBB_CONFIG}.bak
fi

# Delete any sqlite db and config file
rm -rf /tmp/phpbb.sqlite3
rm -rf ${PHPBB_CONFIG}

# Install phpBB
php ${PHPBB_PATH}/phpBB/install/phpbbcli.php install ${PHPBB_INSTALL}

# Update sqlite db file permissions
sudo chown -R vagrant /tmp/phpbb.sqlite3

# Add DEBUG mode to phpBB to remove annoying installer warnings
sed -i "$ a @define('DEBUG', true);" ${PHPBB_CONFIG}

# Update the PHP memory limits (enough to allow phpunit tests to run)
sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php5/fpm/php.ini

echo "Your board is ready at http://192.168.10.10/"
