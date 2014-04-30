#!/bin/bash
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e
set -x

function find_php_ini
{
	echo $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")
}

php_ini_file=$(find_php_ini)

#mbstring
if [ `php -r "echo (int) version_compare(PHP_VERSION, '5.6.0-a3', '>=');"` == "1" ]
then
	echo 'mbstring.http_input=pass' >> "$php_ini_file"
	echo 'mbstring.http_output=pass' >> "$php_ini_file"
fi
