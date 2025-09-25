# setup.sh - Customisations Team
# Commands to install and configure phpBB
echo "[Codespaces] Customisations Team configuration..."

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

# Symlink the webroot so it can be viewed
echo "[Codespaces] Create Symlink of webroot"
sudo rm -rf /var/www/html
sudo ln -s /workspaces/phpbb/phpBB /var/www/html

# Force the server URL to reflect the Codespace
# https://docs.github.com/en/codespaces/developing-in-a-codespace/default-environment-variables-for-your-codespace
if [ "$CODESPACES" = true ] ; then
    cp /workspaces/phpbb/.devcontainer/customisations-team/phpbb-config.yml /tmp/phpbb-config.yml
    CODESPACES_URL="${CODESPACE_NAME}-80.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
    echo "[Codespaces] Set the phpBB server name using default environment variables: $CODESPACES_URL"
    sed -i "s/localhost/$CODESPACES_URL/g" /tmp/phpbb-config.yml
fi

URL_EPV="https://github.com/phpbb/epv"
URL_TV="https://github.com/battye/phpbb-translation-validator"
URL_TITANIA="https://github.com/battye/customisation-db.git"

# Install Titania
echo "[Codespaces] Install Titania"
cd /workspaces/
git clone -b 3.3.x-phpbb-codespaces $URL_TITANIA titania
cd /workspaces/titania
chmod 755 files store
composer install --no-interaction
chmod 755 /workspaces/phpbb/phpBB/ext
mkdir /workspaces/phpbb/phpBB/ext/phpbb
sudo ln -s /workspaces/titania /workspaces/phpbb/phpBB/ext/phpbb/titania

# Install EPV
echo "[Codespaces] Install EPV"
cd /workspaces/
git clone -b master $URL_EPV epv
cd /workspaces/epv
composer install --no-interaction
rm -rf /workspaces/phpbb/phpBB/ext/phpbb/titania/vendor/phpbb/epv
sudo ln -s /workspaces/epv /workspaces/phpbb/phpBB/ext/phpbb/titania/vendor/phpbb/epv

# Install Translation Validator
echo "[Codespaces] Install Translation Validator"
cd /workspaces/
git clone -b master $URL_TV translation-validator
cd /workspaces/translation-validator
composer install --no-interaction
rm -rf /workspaces/phpbb/phpBB/ext/phpbb/titania/vendor/phpbb/translation-validator
sudo ln -s /workspaces/translation-validator /workspaces/phpbb/phpBB/ext/phpbb/titania/vendor/phpbb/translation-validator

# Install phpBB (last so that the extension is auto-installed)
echo "[Codespaces] Run phpBB CLI installation"
cd /workspaces/phpbb/phpBB && composer install --no-interaction
sudo php /workspaces/phpbb/phpBB/install/phpbbcli.php install /tmp/phpbb-config.yml
rm -rf /workspaces/phpbb/phpBB/install

# Set to debug mode
sed -i \
  -e "s|@define('PHPBB_ENVIRONMENT', 'production');|@define('PHPBB_ENVIRONMENT', 'development');|" \
  -e "s|// *@define('DEBUG_CONTAINER', true);|@define('DEBUG_CONTAINER', true);|" \
  /workspaces/phpbb/phpBB/config.php

# Finished
echo "[Codespaces] phpBB (Customisation Team) installation completed"
