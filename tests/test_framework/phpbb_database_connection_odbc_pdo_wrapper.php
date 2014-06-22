<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

if (!class_exists('PDO'))
{
	return;
}

/**
* Used for passing in information about the PDO driver
* since the PDO class reveals nothing about the DSN that
* the user provided.
*
* This is used in the custom PHPUnit ODBC driver
*/
class phpbb_database_connection_odbc_pdo_wrapper extends PDO
{
	// Name of the driver being used (i.e. mssql)
	public $driver = '';

	// Version number of driver since PDO::getAttribute(PDO::ATTR_CLIENT_VERSION) is pretty useless for this
	public $version = 0;

	function __construct($dbms, $version, $dsn, $user, $pass)
	{
		$this->driver = $dbms;
		$this->version = (double) $version;

		parent::__construct($dsn, $user, $pass);
	}
}
