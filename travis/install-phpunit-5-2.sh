#!/bin/sh
#
# @copyright (c) 2015 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#

# $1 - URL to .tar.gz
download() {
	wget -q -O - "$1" | tar xzvf - --strip-components 1
}

mkdir phpunit
cd phpunit
download https://github.com/sebastianbergmann/phpunit/archive/3.6.12.tar.gz
download https://github.com/sebastianbergmann/php-file-iterator/archive/1.3.1.tar.gz
download https://github.com/sebastianbergmann/php-code-coverage/archive/1.1.3.tar.gz
download https://github.com/sebastianbergmann/php-token-stream/archive/1.1.3.tar.gz
download https://github.com/sebastianbergmann/php-text-template/archive/1.1.2.tar.gz
download https://github.com/sebastianbergmann/php-timer/archive/1.0.2.tar.gz
download https://github.com/sebastianbergmann/phpunit-mock-objects/archive/1.1.1.tar.gz
download https://github.com/sebastianbergmann/dbunit/archive/1.1.2.tar.gz
cd ..
