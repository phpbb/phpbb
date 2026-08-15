#!/bin/bash

# setup.sh - Development Team
# Commands to install and configure phpBB
echo "[Docker-Dev] Development Team configuration..."

sleep 2

# Start MySQL
echo "[Docker-Dev] Start MySQL..."
service mysql start
echo "done."

# Start Apache
echo "[Docker-Dev] Start Apache..."
apache2ctl start
echo "done."

## --- Run these only the first time container is created
if [ ! -d "/var/www/docker_install" ]; then

echo "Folder /var/www/docker_install does NOT exist"

# Create a MySQL user to use
echo "[Docker-Dev] Create MySQL user..."
mysql -u root<<EOFMYSQL
    CREATE USER 'phpbb'@'localhost' IDENTIFIED BY 'phpbb';
    GRANT ALL PRIVILEGES ON *.* TO 'phpbb'@'localhost' WITH GRANT OPTION;
    CREATE DATABASE IF NOT EXISTS phpbb;
EOFMYSQL
echo "done."

# Download dependencies
echo "[Docker-Dev] Install Composer dependencies..."
cd /var/www/html && composer install --no-interaction
echo "done."

# Symlink the webroot so it can be viewed
echo "[Docker-Dev] Copying phpbb to /var/www/html..."
rsync -a /workspaces/phpbb/phpBB/ /var/www/html/
echo "done."

# Copy phpBB config
echo "Copying phpBB configuration..."
cp /var/phpbb/phpbb-config.yml /var/www/html/install/phpbb-config.yml
echo "done."

# Install phpBB
echo "[Docker-Dev] Run phpBB CLI installation..."
cd /var/www/html && composer install --no-interaction
php /var/www/html/install/phpbbcli.php install /var/www/html/install/phpbb-config.yml
echo "done."

# -- Remove install dir
echo "[Docker-Dev] Removing phpBB/install directory..."
mv /var/www/html/install /var/www/html/done_install
echo "done."

# Finished
echo "[Docker-Dev] Adjusting permissions on /var/www/html/cache..."
mkdir -p /var/www/html/cache/production
chmod -R 777 /var/www/html/cache
echo "done."

# Finished
echo "[Docker-Dev] phpBB (Development Team) installation completed"

# Create a file inside container to detect installation complete on re-run
mkdir -p /var/www/docker_install

else
    echo "Folder /var/www/docker_install exists. Init skipped."
fi

# Commands to install and configure phpBB
echo "[Docker-Dev] Container started"

# Run sync
echo "[Docker-Dev] Keeping repo files and container files in sync..."
while true; do
    rsync -acu --itemize-changes --exclude-from='/var/phpbb/rsync-exclude.txt' /workspaces/phpbb/phpBB/ /var/www/html/
    # rsync -acu --exclude-from='/var/phpbb/rsync-exclude.txt' /workspaces/phpbb/phpBB/ /var/www/html/
    sleep 2
 done
