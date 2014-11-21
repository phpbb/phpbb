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

namespace phpbb;

class file_downloader
{
	/** @var \phpbb\user */
	protected $user;

	/** @var string Error string */
	public $error_string = '';

	/** @var int Error number */
	public $error_number = 0;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user phpBB user object
	 */
	public function __construct(user $user)
	{
		$this->user = $user;
	}

	/**
	 * Retrieve contents from remotely stored file
	 *
	 * @param string	$host			File host
	 * @param string	$directory		Directory file is in
	 * @param string	$filename		Filename of file to retrieve
	 * @param int		$port			Port to connect to; default: 80
	 * @param int		$timeout		Connection timeout in seconds; default: 6
	 *
	 * @return mixed File data as string if file can be read and there is no
	 *			timeout, false if there were errors or the connection timed out
	 */
	function get($host, $directory, $filename, $port = 80, $timeout = 6)
	{
		if ($socket = @fsockopen($host, $port, $this->error_number, $this->error_string, $timeout))
		{
			@fputs($socket, "GET $directory/$filename HTTP/1.0\r\n");
			@fputs($socket, "HOST: $host\r\n");
			@fputs($socket, "Connection: close\r\n\r\n");

			$timer_stop = time() + $timeout;
			stream_set_timeout($socket, $timeout);

			$file_info = '';
			$get_info = false;

			while (!@feof($socket))
			{
				if ($get_info)
				{
					$file_info .= @fread($socket, 1024);
				}
				else
				{
					$line = @fgets($socket, 1024);
					if ($line == "\r\n")
					{
						$get_info = true;
					}
					else if (stripos($line, '404 not found') !== false)
					{
						$this->error_string = $this->user->lang('FILE_NOT_FOUND', $filename);
						return false;
					}
				}

				$stream_meta_data = stream_get_meta_data($socket);

				if (!empty($stream_meta_data['timed_out']) || time() >= $timer_stop)
				{
					$this->error_string = $this->user->lang['FSOCK_TIMEOUT'];
					return false;
				}
			}
			@fclose($socket);
		}
		else
		{
			if ($this->error_string)
			{
				$this->error_string = utf8_convert_message($this->error_string);
				return false;
			}
			else
			{
				$this->error_string = $this->user->lang['FSOCK_DISABLED'];
				return false;
			}
		}

		return $file_info;
	}

	/**
	 * Set error string
	 *
	 * @param string $error_string Error string
	 *
	 * @return self
	 */
	public function set_error_string(&$error_string)
	{
		$this->error_string = &$error_string;

		return $this;
	}

	/**
	 * Set error number
	 *
	 * @param int $error_number Error number
	 *
	 * @return self
	 */
	public function set_error_number(&$error_number)
	{
		$this->error_number = &$error_number;

		return $this;
	}
}
