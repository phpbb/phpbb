<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cron\task\core;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Cron task for cleaning plupload's temporary upload directory.
*
* @package phpBB3
*/
class tidy_plupload extends \phpbb\cron\task\base
{
	/**
	* How old a file must be (in seconds) before it is deleted.
	* @var int
	*/
	protected $max_file_age = 86400;

	/**
	* How often we run the cron (in seconds).
	* @var int
	*/
	protected $cron_frequency = 86400;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Config object
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* Directory where plupload stores temporary files.
	* @var string
	*/
	protected $plupload_upload_path;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path The root path
	* @param \phpbb\config\config $config The config
	*/
	public function __construct($phpbb_root_path, \phpbb\config\config $config)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->config = $config;

		$this->plupload_upload_path = $this->phpbb_root_path . $this->config['upload_path'] . '/plupload';
	}

	/**
	* {@inheritDoc}
	*/
	public function run()
	{
		// Remove old temporary file (perhaps failed uploads?)
		$last_valid_timestamp = time() - $this->max_file_age;
		try
		{
			$iterator = new \DirectoryIterator($this->plupload_upload_path);
			foreach ($iterator as $file)
			{
				if (strpos($file->getBasename(), $this->config['plupload_salt']) !== 0)
				{
					// Skip over any non-plupload files.
					continue;
				}

				if ($file->getMTime() < $last_valid_timestamp)
				{
					@unlink($file->getPathname());
				}
			}
		}
		catch (\UnexpectedValueException $e)
		{
			add_log(
				'critical',
				'LOG_PLUPLOAD_TIDY_FAILED',
				$this->plupload_upload_path,
				$e->getMessage(),
				$e->getTraceAsString()
			);
		}

		$this->config->set('plupload_last_gc', time(), true);
	}

	/**
	* {@inheritDoc}
	*/
	public function is_runnable()
	{
		return !empty($this->config['plupload_salt']) && is_dir($this->plupload_upload_path);
	}

	/**
	* {@inheritDoc}
	*/
	public function should_run()
	{
		return $this->config['plupload_last_gc'] < time() - $this->cron_frequency;
	}
}
