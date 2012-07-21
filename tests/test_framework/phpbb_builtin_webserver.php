<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* Convenience wrapper for starting and stopping the builtin php webserver
*/
class phpbb_builtin_webserver
{
	protected $process = null;
	protected $pipes = null;

	/**
	* Makes sure the webserver is stopped before terminating php as the
	* process will otherwise hang forever waiting for requests in the
	* webserver child process.
	*/
	function __destruct()
	{
		$this->stop();
	}

	/**
	* Starts the webserver if possible and not running yet
	*
	* @return bool Whether the webserver was started or not
	*/
	public function start()
	{
		if (is_resource($this->process))
		{
			return true;
		}

		if (!function_exists('proc_open') || !function_exists('exec'))
		{
			return false;
		}

		// make sure ps works as expected, so we can kill processes later
		exec("ps -o pid --no-heading --ppid " . escapeshellarg(getmypid()) . " 2>&1", $output, $code);

		if (!trim(implode("\n", $output)) || $code != 0)
		{
			return false;
		}

		exec('php -r "echo phpversion();"', $output, $exit_code);

		if (version_compare(trim(implode("\n", $output)), '5.4-dev', '<'))
		{
			return false;
		}

		$this->process = proc_open(
			'php -S localhost:8000',
			array(
				0 => array('pipe', 'r'), // STDIN
				1 => array('file', '/dev/null', 'w'), // STDOUT
				2 => array('file', '/dev/null', 'w') // STDERR
			),
			$this->pipes,
			__DIR__ . '/../../phpBB/'
		);

		fclose($this->pipes[0]);

		// wait a moment to make sure the webserver started up
		sleep(1);

		if (!is_resource($this->process))
		{
			return false;
		}

		return true;
	}

	/**
	* Stops the builtin PHP webserver if it was started through this object
	*/
	public function stop()
	{
		if (is_resource($this->process))
		{
			$status = proc_get_status($this->process);

			$parent_pid = $status['pid'];

			//use ps to get all the children of this process, and kill them
			exec("ps -o pid --no-heading --ppid " . escapeshellarg($parent_pid), $output, $code);
			$pids = preg_split('/\s+/', implode("\n", $output));

			foreach($pids as $pid)
			{
				if (is_numeric($pid))
				{
					posix_kill($pid, 9);
				}
			}
			proc_close($this->process);
		}
	}
}

