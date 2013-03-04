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
		$this->table = $table;
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
		$this->setAll(array($key => $value));
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
		$map = $this->getAll(array($key));

		return isset($map[$key]) ? $map[$key] : null;
	}

	/**
	* Removes a configuration option
	*
	* @param string $key       The configuration option's name
	*
	* @return null
	*/
	public function delete($key)
	{
		$this->deleteAll(array($key));
	}

	/**
	* Sets a configuration option's value
	*
	* @param array $map        Map from configuration names to values
	*
	* @return null
	*/
	public function setAll(array $map)
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
	* Gets a set of configuration options as a key => value map.
	*
	* @param array $keys       Set of configuration option names
	*
	* @return array            Map from configuration names to values
	*/
	public function getAll(array $keys)
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

		return $map;
	}

	/**
	* Removes multiple configuration options
	*
	* @param array $keys       Set of configuration option names
	*
	* @return null
	*/
	public function deleteAll(array $keys)
	{
		$sql = 'DELETE
			FROM ' . $this->table . '
			WHERE ' . $this->db->sql_in_set('config_name', $keys, false, true);
		$result = $this->db->sql_query($sql);
	}
}
