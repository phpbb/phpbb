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
namespace phpbb\console\command\cache;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class purge extends \phpbb\console\command\command
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	* Constructor
	*
	* @param \phpbb\user							$user	User instance
	* @param \phpbb\cache\driver\driver_interface	$cache	Cache instance
	* @param \phpbb\db\driver\driver_interface		$db		Database connection
	* @param \phpbb\auth\auth						$auth	Auth instance
	* @param \phpbb\log\log							$log	Logger instance
	* @param \phpbb\config\config					$config	Config instance
	*/
	public function __construct(\phpbb\user $user, \phpbb\cache\driver\driver_interface $cache, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\log\log_interface $log, \phpbb\config\config $config)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->auth = $auth;
		$this->log = $log;
		$this->config = $config;
		parent::__construct($user);
	}

	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('cache:purge')
			->setDescription($this->user->lang('PURGE_CACHE'))
		;
	}

	/**
	* Executes the command cache:purge.
	*
	* Purge the cache (including permissions) and increment the asset_version number
	*
	* @param InputInterface  $input  An InputInterface instance
	* @param OutputInterface $output An OutputInterface instance
	*
	* @return null
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->config->increment('assets_version', 1);
		$this->cache->purge();

		// Clear permissions
		$this->auth->acl_clear_prefetch();
		phpbb_cache_moderators($this->db, $this->cache, $this->auth);

		$this->log->add('admin', ANONYMOUS, '', 'LOG_PURGE_CACHE', time(), array());

		$output->writeln($this->user->lang('PURGE_CACHE_SUCCESS'));
	}
}
