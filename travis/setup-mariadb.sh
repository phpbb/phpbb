#!/bin/bash
#
# @copyright (c) 2013 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e

# MariaDB Series
VERSION='5.5'

# Operating system codename, e.g. "precise"
OS_CODENAME=$(lsb_release --codename --short)

if ! which add-apt-repository > /dev/null
then
	sudo apt-get update -qq
	sudo apt-get install -qq python-software-properties
fi

sudo apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xcbcb082a1bb943db
sudo add-apt-repository "deb http://ftp.osuosl.org/pub/mariadb/repo/$VERSION/ubuntu $OS_CODENAME main"
sudo apt-get update -qq

sudo debconf-set-selections <<< "mariadb-server-$VERSION mysql-server/root_password password rootpasswd"
sudo debconf-set-selections <<< "mariadb-server-$VERSION mysql-server/root_password_again password rootpasswd"
sudo apt-get install -qq mariadb-server

# Set root password to empty string.
echo "
USE mysql;
UPDATE user SET Password = PASSWORD('') where User = 'root';
FLUSH PRIVILEGES;
" | mysql -u root -prootpasswd
