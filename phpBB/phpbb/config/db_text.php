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

namespace phpbb\config;

/**
* Manages configuration options with an arbitrary length value stored in a TEXT
* column. In constrast to class \phpbb\config\db, values are never cached and
* prefetched, but every get operation sends a query to the database.
*/
class db_text
{
	/**
	* Database connection
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* Name of the database table used.
	* @var string
	*/
	protected $table;

	/**
	* @param \phpbb\db\driver\driver_interface $db        Database connection
	* @param string          $table     Table name
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $table)
	{
		$this->db = $db;
		$this->table = $this->db->sql_escape($table);
	}

	/**
	* Sets the configuration option with the name $key to $value.
	*
	* @param string $key       The configuration option's name
	* @param string $value     New configuration value
	*
	* @return null
	*/
	public function set($key, $value)
	{
		$this->set_array(array($key => $value));
	}

	/**
	* Gets the configuration value for the name $key.
	*
	* @param string $key       The configuration option's name
	*
	* @return string|null      String result on success
	*                          null if there is no such option
	*/
	public function get($key)
	{
		$map = $this->get_array(array($key));

		return isset($map[$key]) ? $map[$key] : null;
	}

	/**
	* Removes the configuration option with the name $key.
	*
	* @param string $key       The configuration option's name
	*
	* @return null
	*/
	public function delete($key)
	{
		$this->delete_array(array($key));
	}

	/**
	* Mass set configuration options: Receives an associative array,
	* treats array keys as configuration option names and associated
	* array values as their configuration option values.
	*
	* @param array $map        Map from configuration names to values
	*
	* @return null
	*/
	public function set_array(array $map)
	{
		$this->db->sql_transaction('begin');

		foreach ($map as $key => $value)
		{
			$sql = 'UPDATE ' . $this->table . "
				SET config_value = '" . $this->db->sql_escape($value) . "'
				WHERE config_name = '" . $this->db->sql_escape($key) . "'";
			$result = $this->db->sql_query($sql);

			if (!$this->db->sql_affectedrows($result))
			{
				$sql = 'INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', array(
					'config_name'	=> (string) $key,
					'config_value'	=> (string) $value,
				));
				$this->db->sql_query($sql);
			}
		}

		$this->db->sql_transaction('commit');
	}

	/**
	* Mass get configuration options: Receives a set of configuration
	* option names and returns the result as a key => value map where
	* array keys are configuration option names and array values are
	* associated config option values.
	*
	* @param array $keys       Set of configuration option names
	*
	* @return array            Map from configuration names to values
	*/
	public function get_array(array $keys)
	{
		$sql = 'SELECT *
			FROM ' . $this->table . '
			WHERE ' . $this->db->sql_in_set('config_name', $keys, false, true);
		$result = $this->db->sql_query($sql);

		$map = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$map[$row['config_name']] = $row['config_value'];
		}
		$this->db->sql_freeresult($result);

		return $map;
	}

	/**
	* Mass delete configuration options.
	*
	* @param array $keys       Set of configuration option names
	*
	* @return null
	*/
	public function delete_array(array $keys)
	{
		$sql = 'DELETE
			FROM ' . $this->table . '
			WHERE ' . $this->db->sql_in_set('config_name', $keys, false, true);
		$result = $this->db->sql_query($sql);
	}
}
