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

	function __construct(phpbb_cache_driver_interface $cache_driver)
	{
		parent::__construct();
		$this->cache = $cache_driver;
	}

}
