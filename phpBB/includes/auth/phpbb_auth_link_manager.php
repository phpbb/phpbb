<?php
/**
*
* @package auth
* @copyright (c) 2012 phpBB Group
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
* Links authentication methods with users.
*
* @package auth
*/
class phpbb_auth_link_manager {
	/**
	 * The DBAL being used by phpBB.
	 *
	 * @var DBAL
	 */
	protected $db;

	/**
	 * Stores the DBAL for later use.
	 */
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}

	/**
	 * Gets link between a provider, user, and index.
	 *
	 * @param string $provider
	 * @param string $index
	 * @return array - On success, the array $link is returned.
	 *			boolean - Return false on failure.
	 */
	public function get_link_by_index($provider, $index)
	{
		$sql = $sql = 'SELECT *
				FROM ' . AUTH_LINK_TABLE . '
				WHERE link_provider = \'' . $this->db->sql_escape($provider) . '\'
					AND link_index = \'' . $this->db_sql_escape($index) . '\'';
		$result = $this->db->sql_query($sql);
		$link = $this->db->sql_fetchrow($result);

		if (is_array($link))
		{
			return $link;
		}
		return false;
	}

	/**
	 * Gets link between a provider, user, and index.
	 *
	 * @param string $provider
	 * @param integer $user
	 * @return array - On success, the array $link is returned.
	 *			boolean - Return false on failure.
	 */
	public function get_link_by_user($provider, $user)
	{
		$sql = $sql = 'SELECT *
				FROM ' . AUTH_LINK_TABLE . '
				WHERE link_provider = \'' . $this->db->sql_escape($provider) . '\'
					AND user_id = \'' . $this->db_sql_escape($user) . '\'';
		$result = $this->db->sql_query($sql);
		$link = $this->db->sql_fetchrow($result);

		if (is_array($link))
		{
			return $link;
		}
		return false;
	}

	/**
	 * Adds a link between a provider and user according to an index.
	 *
	 * @param string $provider
	 * @param integers $user
	 * @param string $index
	 * @return boolean - true on success
	 */
	public function add_link($provider, $user, $index)
	{
		if(is_empty($provider) || !is_int($user) || is_empty($index))
		{
			throw new phpbb_auth_exception('You may not provide an empty variable to link.');
		}

		$data = array(
			'user_id'		=> $user,
			'link_provider' => $provider,
			'link_index'	=> $index,
		);
		$sql = 'INSERT INTO ' . AUTH_LINK_TABLE . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);
		return true;
	}

	/**
	 * Deletes an authentication link, links, or truncates the entire table.
	 *
	 * @param string $provider
	 * @param string $index
	 * @param integer $user
	 * @return boolean
	 */
	public function delete_link($provider = null, $index = null, $user = null)
	{
		if($provider === null && $index === null && $user === null)
		{
			$sql = 'TRUNCATE ' . AUTH_LINK_TABLE;
		}
		elseif($provider === null)
		{
			throw new phpbb_auth_exception('Provider must be specified unless ' . AUTH_LINK_TABLE . ' is being truncated.');
		}
		else
		{
			$sql_ary = array('link_provider' => $provider);
			if($index !== null)
			{
				$sql_ary['link_index'] = $index;
			}
			if(is_int($user))
			{
				$sql_ary['user_id'] = $user;
			}
			$sql = 'DELETE FROM ' . AUTH_LINK_TABLE . '
					WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
		}
		$this->db->sql_query($sql);

		return true;
	}
}

