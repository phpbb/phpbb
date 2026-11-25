#!/bin/bash
#
# This file is part of the phpBB Forum Software package.
#
# @copyright (c) phpBB Limited <https://www.phpbb.com>
# @license GNU General Public License, version 2 (GPL-2.0)
#
# For full copyright and license information, please see
# the docs/CREDITS.txt file.
#
# Installs phpBB using the CLI installer (install/phpbbcli.php) against the
# given database, driven by a generated install-config.yml.
set -e
set -x

DB=$1

PHPBB_ROOT_PATH=$(realpath "$(dirname "$0")/../phpBB")
CONFIG_FILE="$PHPBB_ROOT_PATH/install-config.yml"

case "$DB" in
	mysql|mariadb)
		DBMS="mysqli"
		DBHOST="127.0.0.1"
		DBPORT="3306"
		DBUSER="root"
		DBPASSWD="~"
		DBNAME="phpbb_tests"
		;;
	postgres)
		DBMS="postgres"
		DBHOST="127.0.0.1"
		DBPORT="5432"
		DBUSER="postgres"
		DBPASSWD="postgres"
		DBNAME="phpbb_tests"
		;;
	sqlite3)
		DBMS="sqlite3"
		# Use the same path as the functional test framework default, so that
		# the board installed here is the one the functional tests connect to.
		DBHOST="$(realpath "$PHPBB_ROOT_PATH/../tests")/phpbb_unit_tests.sqlite3"
		DBPORT="~"
		DBUSER="~"
		DBPASSWD="~"
		DBNAME="~"
		;;
	mssql)
		DBMS="mssqlnative"
		DBHOST="127.0.0.1"
		DBPORT="~"
		DBUSER="sa"
		DBPASSWD="Pssw0rd_12"
		DBNAME="phpbb_tests"
		;;
	*)
		echo "Unknown database type: $DB" >&2
		exit 1
		;;
esac

cat > "$CONFIG_FILE" << EOF
installer:
    admin:
        name: admin
        password: adminadmin
        email: admin@example.org

    board:
        lang: en
        name: CI Installer Board
        description: phpBB installed by the CI installer test

    database:
        dbms: ${DBMS}
        dbhost: ${DBHOST}
        dbport: ${DBPORT}
        dbuser: ${DBUSER}
        dbpasswd: ${DBPASSWD}
        dbname: ${DBNAME}
        table_prefix: phpbb_

    email:
        enabled: false
        smtp_delivery: ~
        smtp_host: ~
        smtp_port: ~
        smtp_user: ~
        smtp_pass: ~

    server:
        cookie_secure: false
        server_protocol: http://
        force_server_vars: false
        server_name: localhost
        server_port: 80
        script_path: /

    extensions: []
EOF

INSTALL_LOG=$(mktemp)

if ! php "$PHPBB_ROOT_PATH/install/phpbbcli.php" install "$CONFIG_FILE" > "$INSTALL_LOG" 2>&1
then
	cat "$INSTALL_LOG"
	rm -f "$INSTALL_LOG"
	exit 1
fi

# The installer swallows task-level errors (they are reported through the I/O
# handler as "[ERROR] ..." lines) and still exits with code 0, so surface any
# reported error and fail the step with the actual message.
if grep -q '\[ERROR\]' "$INSTALL_LOG"
then
	echo "The installer reported errors:" >&2
	cat "$INSTALL_LOG" >&2
	rm -f "$INSTALL_LOG"
	exit 1
fi

cat "$INSTALL_LOG"
rm -f "$INSTALL_LOG"

# The installer reports errors through the I/O handler without returning a
# non-zero exit code, so verify that the config file was actually written.
if [ ! -f "$PHPBB_ROOT_PATH/config.php" ]
then
	echo "phpBB installer did not produce a config.php file." >&2
	exit 1
fi

# The CLI installer writes PHPBB_ENVIRONMENT = "production" into config.php. In
# the production environment the board disables itself while the install/
# directory is still present (allow_install_dir defaults to false), so the
# functional tests would render the "board disabled" page instead of the forum
# list. Switch to the "test" environment, like the in-process installer used by
# phpbb_functional_test_case, so the board behaves identically.
sed -i "/PHPBB_ENVIRONMENT/s/production/test/" "$PHPBB_ROOT_PATH/config.php"
