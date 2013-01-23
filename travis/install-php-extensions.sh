#!/bin/bash
#
# @copyright (c) 2013 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e

function add_ext_to_php_ini
{
	echo "extension=$1.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
}

# redis
git clone git://github.com/nicolasff/phpredis.git
cd phpredis
phpize
./configure
make
make install
cd ..
add_ext_to_php_ini 'redis'
