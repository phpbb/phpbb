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

namespace phpbb\files\types;

use \phpbb\files\factory;
use \phpbb\files\filespec;
use \phpbb\files\upload;
use \phpbb\language\language;
use \phpbb\request\request_interface;

class remote extends base
{
	/** @var factory Files factory */
	protected $factory;

	/** @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/** @var upload */
	protected $upload;

	/**
	 * Construct a form upload type
	 *
	 * @param factory           $factory
	 * @param request_interface $request
	 */
	public function __construct(factory $factory, language $language, request_interface $request)
	{
		$this->factory = $factory;
		$this->language = $language;
		$this->request = $request;
	}

	/**
	 * {@inheritdoc}
	 */
	public function upload()
	{
		$args = func_get_args();
		return $this->remote_upload($args[0]);
	}

	/**
	 * Remote upload method
	 * Uploads file from given url
	 *
	 * @param string $upload_url URL pointing to file to upload, for example http://www.foobar.com/example.gif
	 * @return filespec $file Object "filespec" is returned, all further operations can be done with this object
	 * @access public
	 */
	protected function remote_upload($upload_url)
	{
		$upload_ary = array();
		$upload_ary['local_mode'] = true;

		if (!preg_match('#^(https?://).*?\.(' . implode('|', $this->upload->allowed_extensions) . ')$#i', $upload_url, $match))
		{
			return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'URL_INVALID'));
		}

		if (empty($match[2]))
		{
			return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'URL_INVALID'));
		}

		$url = parse_url($upload_url);

		$host = $url['host'];
		$path = $url['path'];
		$port = (!empty($url['port'])) ? (int) $url['port'] : 80;

		$upload_ary['type'] = 'application/octet-stream';

		$url['path'] = explode('.', $url['path']);
		$ext = array_pop($url['path']);

		$url['path'] = implode('', $url['path']);
		$upload_ary['name'] = utf8_basename($url['path']) . (($ext) ? '.' . $ext : '');
		$filename = $url['path'];
		$filesize = 0;

		$remote_max_filesize = $this->upload->max_filesize;
		if (!$remote_max_filesize)
		{
			$max_filesize = @ini_get('upload_max_filesize');

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$remote_max_filesize = (int) $max_filesize;

				switch ($unit)
				{
					case 'g':
						$remote_max_filesize *= 1024;
					// no break
					case 'm':
						$remote_max_filesize *= 1024;
					// no break
					case 'k':
						$remote_max_filesize *= 1024;
					// no break
				}
			}
		}

		$errno = 0;
		$errstr = '';

		if (!($fsock = @fsockopen($host, $port, $errno, $errstr)))
		{
			return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'NOT_UPLOADED'));
		}

		// Make sure $path not beginning with /
		if (strpos($path, '/') === 0)
		{
			$path = substr($path, 1);
		}

		fputs($fsock, 'GET /' . $path . " HTTP/1.1\r\n");
		fputs($fsock, "HOST: " . $host . "\r\n");
		fputs($fsock, "Connection: close\r\n\r\n");

		// Set a proper timeout for the socket
		socket_set_timeout($fsock, $this->upload->upload_timeout);

		$get_info = false;
		$data = '';
		$length = false;
		$timer_stop = time() + $this->upload->upload_timeout;

		while ((!$length || $filesize < $length) && !@feof($fsock))
		{
			if ($get_info)
			{
				if ($length)
				{
					// Don't attempt to read past end of file if server indicated length
					$block = @fread($fsock, min($length - $filesize, 1024));
				}
				else
				{
					$block = @fread($fsock, 1024);
				}

				$filesize += strlen($block);

				if ($remote_max_filesize && $filesize > $remote_max_filesize)
				{
					$max_filesize = get_formatted_filesize($remote_max_filesize, false);

					return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']));
				}

				$data .= $block;
			}
			else
			{
				$line = @fgets($fsock, 1024);

				if ($line == "\r\n")
				{
					$get_info = true;
				}
				else
				{
					if (stripos($line, 'content-type: ') !== false)
					{
						$upload_ary['type'] = rtrim(str_replace('content-type: ', '', strtolower($line)));
					}
					else if ($this->upload->max_filesize && stripos($line, 'content-length: ') !== false)
					{
						$length = (int) str_replace('content-length: ', '', strtolower($line));

						if ($remote_max_filesize && $length && $length > $remote_max_filesize)
						{
							$max_filesize = get_formatted_filesize($remote_max_filesize, false);

							return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']));
						}
					}
					else if (stripos($line, '404 not found') !== false)
					{
						return $this->factory->get('filespec')->set_error($this->upload->error_prefix . 'URL_NOT_FOUND');
					}
				}
			}

			$stream_meta_data = stream_get_meta_data($fsock);

			// Cancel upload if we exceed timeout
			if (!empty($stream_meta_data['timed_out']) || time() >= $timer_stop)
			{
				return $this->factory->get('filespec')->set_error($this->upload->error_prefix . 'REMOTE_UPLOAD_TIMEOUT');
			}
		}
		@fclose($fsock);

		if (empty($data))
		{
			return $this->factory->get('filespec')->set_error($this->upload->error_prefix . 'EMPTY_REMOTE_DATA');
		}

		$tmp_path = (!@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'off') ? false : $this->phpbb_root_path . 'cache';
		$filename = tempnam($tmp_path, unique_id() . '-');

		if (!($fp = @fopen($filename, 'wb')))
		{
			return $this->factory->get('filespec')->set_error($this->upload->error_prefix . 'NOT_UPLOADED');
		}

		$upload_ary['size'] = fwrite($fp, $data);
		fclose($fp);
		unset($data);

		$upload_ary['tmp_name'] = $filename;

		/** @var filespec $file */
		$file = $this->factory->get('filespec')
			->set_upload_ary($upload_ary)
			->set_upload_namespace($this);
		$this->upload->common_checks($file);

		return $file;
	}
}
