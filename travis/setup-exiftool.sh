#!/bin/sh
#
# @copyright (c) 2014 phpBB Group
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
#
set -e

sudo apt-get update
sudo apt-get install -y parallel libimage-exiftool-perl
