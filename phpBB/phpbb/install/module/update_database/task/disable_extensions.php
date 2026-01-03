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

namespace phpbb\install\module\update_database\task;

use phpbb\extension\manager;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\helper\update_helper;
use phpbb\install\task_base;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Disables all extensions during the installation process
 */
class disable_extensions extends task_base
{
	/** @var config  */
	protected config $install_config;

	/** @var iohandler_interface */
	protected iohandler_interface $iohandler;

	/** @var ContainerInterface|manager */
	protected ContainerInterface|manager $extension_manager;

	/** @var ContainerInterface|\phpbb\config\config */
	protected ContainerInterface|\phpbb\config\config $config;

	/** @var update_helper */
	protected update_helper $update_helper;

	/**
	 * @var array List of extensions included with phpBB
	 */
	public static array $default_extensions = [
		'phpbb/viglink',
	];

	/**
	 * Constructor
	 *
	 * @param container_factory			$container
	 * @param config					$install_config
	 * @param iohandler_interface		$iohandler
	 * @param update_helper				$update_helper
	 */
	public function __construct(container_factory $container, config $install_config, iohandler_interface $iohandler, update_helper $update_helper)
	{
		$this->install_config = $install_config;
		$this->iohandler = $iohandler;
		$this->extension_manager = $container->get('ext.manager');
		$this->config = $container->get('config');
		$this->update_helper = $update_helper;

		parent::__construct();
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(): void
	{
		$update_info = $this->install_config->get('update_info_unprocessed', []);
		$version_from = !empty($update_info) ? $update_info['version']['from'] : $this->config['version_update_from'];

		// Only run when updating to 4.0.0-a2
		if (!empty($version_from) && $this->update_helper->phpbb_version_compare($version_from, '4.0.0-a2', '<'))
		{
			$all_enabled_extensions = array_keys($this->extension_manager->all_enabled());
			$all_enabled_extensions = array_diff($all_enabled_extensions, self::$default_extensions);
			$i = $this->install_config->get('disable_extensions_index', 0);
			$enabled_extensions = array_slice($all_enabled_extensions, $i, null, true);

			foreach ($enabled_extensions as $extension)
			{
				$this->extension_manager->disable($extension);
				$i++;

				if ($this->install_config->get_time_remaining() <= 0 || $this->install_config->get_memory_remaining() <= 0)
				{
					break;
				}
			}

			$this->install_config->set('disable_extensions_index', $i);

			if ($i < count($all_enabled_extensions))
			{
				throw new resource_limit_reached_exception();
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count(): int
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name(): string
	{
		return 'TASK_DISABLE_EXTENSIONS';
	}
}
