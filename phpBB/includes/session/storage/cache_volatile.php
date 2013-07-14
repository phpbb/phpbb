<?php
/**
 *
 * @package phpBB3
 * @copyright (c) 2005 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/native.php';
require_once dirname(__FILE__) . '/../../cache/driver/interface.php';

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}


class phpbb_session_storage_cache_volatile extends phpbb_session_storage_native implements phpbb_session_storage
{
	protected $cache;

	function __construct(phpbb_cache_driver_interface $cache_driver)
	{
		parent::__construct();
		$this->cache = $cache_driver;
	}

}
