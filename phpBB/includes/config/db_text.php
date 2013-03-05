<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Manages configuration options with an arbitrary length value stored in a TEXT
* column. In constrast to class phpbb_config_db, values are never cached and
* prefetched, but every get operation sends a query to the database.
*
* @package phpBB3
*/
class phpbb_config_db_text
{
	/**
	* Database connection
	* @var phpbb_db_driver
	*/
	protected $db;

	/**
	* Name of the database table used.
	* @var string
	*/
	protected $table;

	/**
	* @param phpbb_db_driver $db        Database connection
	* @param string          $table     Table name
	*/
	public function __construct(phpbb_db_driver $db, $table)
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
		$this->set_all(array($key => $value));
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
		$map = $this->get_all(array($key));

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
		$this->delete_all(array($key));
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
	public function set_all(array $map)
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
					'config_name'	=> $key,
					'config_value'	=> $value,
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
	public function get_all(array $keys)
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
	public function delete_all(array $keys)
	{
		$sql = 'DELETE
			FROM ' . $this->table . '
			WHERE ' . $this->db->sql_in_set('config_name', $keys, false, true);
		$result = $this->db->sql_query($sql);
	}
}
