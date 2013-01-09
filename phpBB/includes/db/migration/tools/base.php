<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_tools_base
{
	/** @var phpbb_auth */
	protected $auth = null;

	/** @var phpbb_cache_service */
	protected $cache = null;

	/** @var phpbb_config */
	protected $config = null;

	/** @var dbal */
	protected $db = null;

	/** @var phpbb_template */
	protected $template = null;

	/** @var phpbb_user */
	protected $user = null;

	/** @var string */
	protected $phpbb_root_path = null;

	/** @var string */
	protected $php_ext = null;

	public function __construct(dbal $db, phpbb_cache_driver_interface $cache, phpbb_template $template, $user, phpbb_auth $auth, phpbb_config $config, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}
}