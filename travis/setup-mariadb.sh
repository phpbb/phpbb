#!/bin/bash
#
# @copyright (c) 2013 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

# MariaDB Series
VERSION='5.5'

# Operating system codename, e.g. "precise"
OS_CODENAME=$(lsb_release --codename --short)

# Manually purge MySQL to remove conflicting files (e.g. /etc/mysql/my.cnf)
sudo apt-get purge -qq mysql-common

if ! which add-apt-repository > /dev/null
then
	sudo apt-get update -qq
	sudo apt-get install -qq python-software-properties
fi

MIRROR_DOMAIN='ftp.osuosl.org'
sudo apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xcbcb082a1bb943db
sudo add-apt-repository "deb http://$MIRROR_DOMAIN/pub/mariadb/repo/$VERSION/ubuntu $OS_CODENAME main"
sudo apt-get update -qq

# Pin repository in order to avoid conflicts with MySQL from distribution
# repository. See https://mariadb.com/kb/en/installing-mariadb-deb-files
# section "Version Mismatch Between MariaDB and Ubuntu/Debian Repositories"
echo "
Package: *
Pin: origin $MIRROR_DOMAIN
Pin-Priority: 1000
" | sudo tee /etc/apt/preferences.d/mariadb

sudo debconf-set-selections <<< "mariadb-server-$VERSION mysql-server/root_password password rootpasswd"
sudo debconf-set-selections <<< "mariadb-server-$VERSION mysql-server/root_password_again password rootpasswd"
sudo apt-get install -qq mariadb-server

# Set root password to empty string.
echo "
USE mysql;
UPDATE user SET Password = PASSWORD('') where User = 'root';
FLUSH PRIVILEGES;
" | mysql -u root -prootpasswd

mysql --version
