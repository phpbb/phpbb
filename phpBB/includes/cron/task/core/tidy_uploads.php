<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
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
	const max_file_age = 86400;

	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		global $config;
		
		// Remove old temporary file (perhaps failed uploads?)
		$dir = $config['upload_path'] . DIRECTORY_SEPARATOR . 'plupload';
		try
		{
			$it = new DirectoryIterator($dir);
			foreach ($it as $file)
			{
				if ($file->getBasename() === 'index.htm')
				{
					continue;
				}

				if ($file->getMTime() < time() - self::max_file_age)
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
		global $config;
		return file_exists($config['upload_path'] . DIRECTORY_SEPARATOR . 'plupload');
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
		global $config;
		return $config['plupload_last_gc'] < time() - self::max_file_age;
	}
}
