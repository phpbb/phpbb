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
namespace phpbb\console\command\extension;

use Symfony\Component\Console\Style\SymfonyStyle;

abstract class command extends \phpbb\console\command\command
{
	/** @var \phpbb\extension\manager */
	protected $manager;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var string Cache driver class */
	protected $cache_driver_class;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\user				$user				User object
	 * @param \phpbb\extension\manager	$manager			Extension manager object
	 * @param \phpbb\log\log			$log				Log object
	 * @param string					$cache_driver_class	Cache driver class
	 */
	public function __construct(\phpbb\user $user, \phpbb\extension\manager $manager, \phpbb\log\log $log, $cache_driver_class)
	{
		$this->manager = $manager;
		$this->log = $log;
		$this->cache_driver_class = $cache_driver_class;

		parent::__construct($user);
	}

	/**
	 * Check if APCu cache driver is used and enabled for CLI, otherwise display a notice.
	 *
	 * @param SymfonyStyle $io
	 * @return void
	 */
	protected function check_apcu_cache(SymfonyStyle $io)
	{
		if ($this->cache_driver_class === 'phpbb\\cache\\driver\\apcu' && !@ini_get('apc.enable_cli'))
		{
			$io->note($this->user->lang('CLI_APCU_CACHE_NOTICE'));
		}
	}
}
