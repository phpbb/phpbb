# setup.sh - Development Team
# Commands to install and configure phpBB
echo "[Codespaces] Development Team configuration..."

# Start MySQL
echo "[Codespaces] Start MySQL"
sudo service mysql start

# Start Apache
echo "[Codespaces] Start Apache"
sudo apache2ctl start

# Add SSH key
# echo "[Codespaces] Add SSH key"
# echo "$SSH_KEY" > /home/vscode/.ssh/id_rsa && chmod 600 /home/vscode/.ssh/id_rsa

# Create a MySQL user to use
echo "[Codespaces] Create MySQL user"
sudo mysql -u root<<EOFMYSQL
    CREATE USER 'phpbb'@'localhost' IDENTIFIED BY 'phpbb';
    GRANT ALL PRIVILEGES ON *.* TO 'phpbb'@'localhost' WITH GRANT OPTION;
    CREATE DATABASE IF NOT EXISTS phpbb;
EOFMYSQL

# Download dependencies
# echo "[Codespaces] Install Composer dependencies"
# composer install --no-interaction

# Symlink the webroot so it can be viewed
echo "[Codespaces] Create Symlink of webroot"
sudo rm -rf /var/www/html
sudo ln -s /workspaces/phpbb/phpBB /var/www/html

# Force the server URL to reflect the Codespace
# https://docs.github.com/en/codespaces/developing-in-a-codespace/default-environment-variables-for-your-codespace
if [ "$CODESPACES" = true ] ; then
    cp /workspaces/phpbb/.devcontainer/development-team/phpbb-config.yml /tmp/phpbb-config.yml
    CODESPACES_URL="${CODESPACE_NAME}-80.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
    echo "[Codespaces] Set the phpBB server name using default environment variables: $CODESPACES_URL"
    sed -i "s/localhost/$CODESPACES_URL/g" /tmp/phpbb-config.yml
fi

# Copy phpBB config
# echo "[Codespaces] Copy phpBB configuration"
# cp /workspaces/phpbb/.devcontainer/resources/phpbb-config.yml /workspaces/phpbb/phpBB/install/install-config.yml

# Install phpBB
echo "[Codespaces] Run phpBB CLI installation"
cd /workspaces/phpbb/phpBB && composer install --no-interaction
sudo php /workspaces/phpbb/phpBB/install/phpbbcli.php install /tmp/phpbb-config.yml
rm -rf /workspaces/phpbb/phpBB/install

# Finished
echo "[Codespaces] phpBB (Development Team) installation completed"
