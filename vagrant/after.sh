#!/bin/sh

PHPBB_PATH="/home/vagrant/phpbb"
PHPBB_CONFIG="${PHPBB_PATH}/phpBB/config.php"
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

# Install phpBB
php ${PHPBB_PATH}/phpBB/install/phpbbcli.php install ${PHPBB_INSTALL}

# Add DEBUG mode to phpBB to remove annoying installer warnings
echo "@define('DEBUG', true);" >> ${PHPBB_CONFIG}

# Change environment to development
sed -i '/^.*PHPBB_ENVIRONMENT.*$/s/production/development/' ${PHPBB_CONFIG}

# Update the PHP memory limits (enough to allow phpunit tests to run)
sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php/7.2/fpm/php.ini

# Fix for urls with app.php
sed -i "s/cgi.fix_pathinfo=.*/cgi.fix_pathinfo=1/" /etc/php/7.2/fpm/php.ini

# Restart php-fpm to apply php.ini changes
systemctl restart php7.2-fpm.service

echo "Your board is ready at http://192.168.10.10/"
