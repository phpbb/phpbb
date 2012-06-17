<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
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

/**
* Tidy plupload temporary directory cron task.
*
* @package phpBB3
*/
class phpbb_cron_task_core_tidy_uploads extends phpbb_cron_task_base
{
	/**
	* How old a file must be before it's deleted (24 hours)
	*/
	const MAX_FILE_AGE = 86400;

	/**
	* Config array
	* @var array
	*/
	protected $config;

	/**
	* Constructor method
	*/
	public function __construct()
	{
		global $config;
		$this->config = $config;
	}

	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		// Remove old temporary file (perhaps failed uploads?)
		$dir = $this->config['upload_path'] . '/plupload';
		try
		{
			$it = new DirectoryIterator($dir);
			foreach ($it as $file)
			{
				if ($file->getBasename() === 'index.htm')
				{
					continue;
				}

				if ($file->getMTime() < time() - self::MAX_FILE_AGE)
				{
					@unlink($file->getPathname());
				}
			}
		}
		catch (UnexpectedValueException $e) {}

		set_config('plupload_last_gc', time(), true);
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* Tidy cache cron task runs if the cache implementation in use
	* supports tidying.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return file_exists($this->config['upload_path'] . '/plupload');
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between cache tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['plupload_last_gc'] < time() - self::MAX_FILE_AGE;
	}
}
