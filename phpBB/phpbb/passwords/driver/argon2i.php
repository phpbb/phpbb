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

namespace phpbb\passwords\driver;

class argon2i extends base_native
{
	/** @var int Maximum memory (in bytes) that may be used to compute the Argon2 hash */
	protected $memory_cost;

	/** @var int Number of threads to use for computing the Argon2 hash */
	protected $threads;

	/** @var int Maximum amount of time it may take to compute the Argon2 hash */
	protected $time_cost;

	/**
	* Constructor of passwords driver object
	*
	* @param \phpbb\config\config $config phpBB config
	* @param \phpbb\passwords\driver\helper $helper Password driver helper
	* @param int $memory_cost Maximum memory (optional)
	* @param int $threads Number of threads to use (optional)
	* @param int $time_cost Maximum amount of time (optional)
	*/
	public function __construct(\phpbb\config\config $config, helper $helper, $memory_cost = 65536, $threads = 2, $time_cost = 4)
	{
		parent::__construct($config, $helper);

		/**
		 * For Sodium implementation of argon2 algorithm (since PHP 7.4), set special value of 1 for "threads" cost factor
		 * See https://wiki.php.net/rfc/sodium.argon.hash and PHPBB3-16266
		 * Don't allow cost factors to be below default settings where possible
		 */
		$this->memory_cost = max($memory_cost, defined('PASSWORD_ARGON2_DEFAULT_MEMORY_COST') ? PASSWORD_ARGON2_DEFAULT_MEMORY_COST : 65536);
		$this->time_cost   = max($time_cost, defined('PASSWORD_ARGON2_DEFAULT_TIME_COST') ? PASSWORD_ARGON2_DEFAULT_TIME_COST : 4);
		$this->threads     = (defined('PASSWORD_ARGON2_PROVIDER') && PASSWORD_ARGON2_PROVIDER == 'sodium') ?
									1 : max($threads, defined('PASSWORD_ARGON2_DEFAULT_THREADS') ? PASSWORD_ARGON2_DEFAULT_THREADS : 1);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_algo_name()
	{
		return 'PASSWORD_ARGON2I';
	}

	/**
	* {@inheritdoc}
	*/
	public function get_options()
	{
		return [
			'memory_cost' => $this->memory_cost,
			'time_cost'   => $this->time_cost,
			'threads'     => $this->threads
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function get_prefix()
	{
		return '$argon2i$';
	}
}
