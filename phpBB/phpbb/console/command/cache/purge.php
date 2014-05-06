<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\user */
	protected $user;

	function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\log\log $log, \phpbb\user $user)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->auth = $auth;
		$this->log = $log;
		$this->user = $user;
		$this->user->add_lang(array('acp/common'));
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('cache:purge')
			->setDescription('Purge the cache.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->cache->purge();

		// Clear permissions
		$this->auth->acl_clear_prefetch();
		phpbb_cache_moderators($this->db, $this->cache, $this->auth);

		$this->log->add('admin', ANONYMOUS, '', 'LOG_PURGE_CACHE', time(), array());

		$output->writeln($this->user->lang('PURGE_CACHE_SUCCESS'));
	}
}
