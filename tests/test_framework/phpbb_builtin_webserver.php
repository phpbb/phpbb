<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
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
	protected $port;
	protected $is_win;

	/**
	* Creates an instance of the php webserver wrapper but does not start it
	*
	* @param int $port The port to run the webserver on
	*/
	public function __construct($port)
	{
		$this->port = $port;
		$this->is_win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

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

		exec('php -r "echo phpversion();"', $output, $exit_code);

		if (version_compare(trim(implode("\n", $output)), '5.4-dev', '<') || $exit_code)
		{
			return false;
		}

		$null_device = $this->is_win ? 'nul': '/dev/null';

		$this->process = proc_open(
			'php -S localhost:' . $this->port,
			array(
				0 => array('pipe', 'r'), // STDIN
				1 => STDOUT, // array('file', $null_device, 'w')
				2 => STDERR, // array('file', $null_device, 'w')
			),
			$this->pipes,
			__DIR__ . '/../../phpBB/',
			array('bypass_shell' => true)
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

			if ($this->is_win)
			{
				exec("taskkill /f /t /pid " . escapeshellarg($status['pid']));
			}
			else
			{
				// kill process and all children
				posix_kill(-$status['pid'], SIGTERM);
			}

			proc_close($this->process);
		}
	}
}

