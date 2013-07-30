<?php
/**
 *
 * @package phpBB3
 * @copyright (c) 2005 phpBB Group
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


class phpbb_session_storage_storage_cache
	extends phpbb_session_storage_native
	implements
		phpbb_session_storage_interface,
		phpbb_session_banlist_interface,
		phpbb_session_keys_interface,
		phpbb_session_cleanup_interface,
		phpbb_session_user_interface
{
	protected $cache;
	const all_caches_key = 'ALL_SESSIONS';
	const user_id_prefix = 'SESSION_USER_';

	function __construct(phpbb_cache_driver_atomic_interface $cache_driver, $db, $time)
	{
		parent::__construct($db, $time);
		$this->cache = $cache_driver;
	}

	protected function add_to($key, $session_id)
	{
		$this->cache->atomic_operation($key,
			function ($sessions) use ($session_id)
			{
				$sessions[] = $session_id = 1;
				return $sessions;
			}
		);
	}

	protected function remove_from($key, $session_id)
	{
		$this->cache->atomic_operation($key,
			function ($sessions) use ($session_id)
			{
				unset($sessions[$session_id]);
				return $sessions;
			}
		);
	}

	protected function get_count($key)
	{
		return count($this->cache->get($key));
	}

	protected function user_key($user_id)
	{
		return self::user_id_prefix.$user_id;
	}

	function create($session_data)
	{
		$user_key = self::user_key($session_data['session_user_id']);
		$id = $session_data['session_id'];

		$this->add_to($user_key, $id);
		$this->cache->put($id, $session_data);
	}

	function update($session_id, $session_data)
	{
		$this->cache->put($session_id, $session_data);
	}

	function get($session_id)
	{
		return $this->cache->get($session_id);
	}

	function delete($session_id, $user_id = false)
	{
		if ($user_id !== false)
		{
			$session = $this->cache->get($session_id);
			if ($session['session_user_id'] != $user_id)
			{
				// Does not match, do not destroy
				return;
			}
		}
		else
		{
			$session = $this->cache->get($session_id);
			$user_id = $session['session_user_id'];
		}
		$this->remove_from($this->user_key($user_id), $session_id);
		$this->cache->destroy($session_id);
	}

	function num_active_sessions($minutes_considered_active)
	{
		// Doesn't actually use minutes_considered_active, just
		//   counts and hopes garbage collection is working properly
		// Alternate not-implemented-yet solution:
		// - Go through sessions in all_sessions_key
		// - Check expire date
		// - Put in key
		// - Use this key until it expires (every minute or w/e)
		return $this->get_all(self::all_caches_key);
	}

	function unset_admin($session_id)
	{
		$this->cache->atomic_operation($session_id,
			function($session_data)
			{
				$session_data['session_admin'] = 0;
				return $session_data;
			}
		);
	}
}
