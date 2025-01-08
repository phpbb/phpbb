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

namespace phpbb\messenger;

use phpbb\config\config;
use phpbb\event\dispatcher;
use phpbb\di\service_collection;
use phpbb\filesystem\filesystem;

/**
 * Handling messenger file queue
 */
class queue
{
	/** @var string */
	protected $cache_file;

	/** @var config */
	protected $config;

	/** @var array */
	protected $data = [];

	/** @var dispatcher */
	protected $dispatcher;

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var service_collection */
	protected $messenger_method_collection;

	/** @var array */
	protected $queue_data = [];

	/**
	 * Messenger queue constructor.
	 *
	 * @param config $config
	 * @param dispatcher $dispatcher
	 * @param service_collection $messenger_method_collection
	 * @param string $cache_file
	 */
	public function __construct(config $config, dispatcher $dispatcher, service_collection $messenger_method_collection, $cache_file)
	{
		$this->config = $config;
		$this->dispatcher = $dispatcher;
		$this->messenger_method_collection = $messenger_method_collection;
		$this->filesystem = new filesystem();
		$this->cache_file = $cache_file;
	}

	/**
	 * Init a queue object
	 *
	 * @param string $object 	Queue object type: email/jabber/etc
	 * @param int $package_size Size of the messenger package to send
	 * @return void
	 */
	public function init(string $object, int $package_size): void
	{
		$this->data[$object] = [];
		$this->data[$object]['package_size'] = $package_size;
		$this->data[$object]['data'] = [];
	}

	/**
	 * Put message into the messenger file queue
	 *
	 * @param string $object 		Queue object type: email/jabber/etc
	 * @param array $message_data	Message data to send
	 * @return void
	 */
	public function put(string $object, array $message_data): void
	{
		$this->data[$object]['data'][] = $message_data;
	}

	/**
	 * Process the messenger file queue (using lock file)
	 *
	 * @return void
	 */
	public function process(): void
	{
		$lock = new \phpbb\lock\flock($this->cache_file);
		$lock->acquire();

		// avoid races, check file existence once
		$have_cache_file = file_exists($this->cache_file);
		if (!$have_cache_file || $this->config['last_queue_run'] > time() - $this->config['queue_interval'])
		{
			if (!$have_cache_file)
			{
				$this->config->set('last_queue_run', time(), false);
			}

			$lock->release();
			return;
		}

		$this->config->set('last_queue_run', time(), false);

		include($this->cache_file);

		/** @psalm-suppress InvalidTemplateParam */
		$messenger_collection_iterator = $this->messenger_method_collection->getIterator();
		foreach ($messenger_collection_iterator as $messenger_method)
		{
			if (isset($this->queue_data[$messenger_method->get_queue_object_name()]))
			{
				$messenger_method->process_queue($this->queue_data);
			}
		}

		if (!count($this->queue_data))
		{
			@unlink($this->cache_file);
		}
		else
		{
			if ($fp = @fopen($this->cache_file, 'wb'))
			{
				fwrite($fp, "<?php\nif (!defined('IN_PHPBB')) exit;\n\$this->queue_data = unserialize(" . var_export(serialize($this->queue_data), true) . ");\n\n?>");
				fclose($fp);

				if (function_exists('opcache_invalidate'))
				{
					@opcache_invalidate($this->cache_file);
				}

				try
				{
					$this->filesystem->phpbb_chmod($this->cache_file, \phpbb\filesystem\filesystem_interface::CHMOD_READ | \phpbb\filesystem\filesystem_interface::CHMOD_WRITE);
				}
				catch (\phpbb\filesystem\exception\filesystem_exception $e)
				{
					// Do nothing
				}
			}
		}

		$lock->release();
	}

	/**
	 * Save message data to the messenger file queue
	 *
	 * @return void
	 */
	public function save(): void
	{
		if (!count($this->data))
		{
			return;
		}

		$lock = new \phpbb\lock\flock($this->cache_file);
		$lock->acquire();

		if (file_exists($this->cache_file))
		{
			include($this->cache_file);

			foreach ($this->queue_data as $object => $data_ary)
			{
				if (isset($this->data[$object]) && count($this->data[$object]))
				{
					$this->data[$object]['data'] = array_merge($data_ary['data'], $this->data[$object]['data']);
				}
				else
				{
					$this->data[$object]['data'] = $data_ary['data'];
				}
			}
		}

		if ($fp = @fopen($this->cache_file, 'w'))
		{
			fwrite($fp, "<?php\nif (!defined('IN_PHPBB')) exit;\n\$this->queue_data = unserialize(" . var_export(serialize($this->data), true) . ");\n\n?>");
			fclose($fp);

			if (function_exists('opcache_invalidate'))
			{
				@opcache_invalidate($this->cache_file);
			}

			try
			{
				$this->filesystem->phpbb_chmod($this->cache_file, \phpbb\filesystem\filesystem_interface::CHMOD_READ | \phpbb\filesystem\filesystem_interface::CHMOD_WRITE);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Do nothing
			}

			$this->data = [];
		}

		$lock->release();
	}
}
