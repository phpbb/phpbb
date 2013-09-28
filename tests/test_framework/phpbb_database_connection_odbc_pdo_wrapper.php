<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	// Name of the driver being used (i.e. mssql, firebird)
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
