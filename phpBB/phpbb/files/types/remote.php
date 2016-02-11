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

use bantu\IniGetWrapper\IniGetWrapper;
use phpbb\config\config;
use phpbb\files\factory;
use phpbb\files\filespec;
use phpbb\language\language;
use phpbb\request\request_interface;

class remote extends base
{
	/** @var config phpBB config */
	protected $config;

	/** @var factory Files factory */
	protected $factory;

	/** @var language */
	protected $language;

	/** @var IniGetWrapper */
	protected $php_ini;

	/** @var request_interface */
	protected $request;

	/** @var \phpbb\files\upload */
	protected $upload;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/**
	 * Construct a form upload type
	 *
	 * @param config $config phpBB config
	 * @param factory $factory Files factory
	 * @param language $language Language class
	 * @param IniGetWrapper $php_ini ini_get() wrapper
	 * @param request_interface $request Request object
	 * @param string $phpbb_root_path phpBB root path
	 */
	public function __construct(config $config, factory $factory, language $language, IniGetWrapper $php_ini, request_interface $request, $phpbb_root_path)
	{
		$this->config = $config;
		$this->factory = $factory;
		$this->language = $language;
		$this->php_ini = $php_ini;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
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

		$url = parse_url($upload_url);

		$upload_ary['type'] = 'application/octet-stream';

		$url['path'] = explode('.', $url['path']);
		$ext = array_pop($url['path']);

		$url['path'] = implode('', $url['path']);
		$upload_ary['name'] = utf8_basename($url['path']) . (($ext) ? '.' . $ext : '');

		$remote_max_filesize = $this->get_max_file_size();

		$guzzle_options = [
			'timeout'			=> $this->upload->upload_timeout,
			'connect_timeout'	=> $this->upload->upload_timeout,
			'verify'			=> !empty($this->config['remote_upload_verify']) ? (bool) $this->config['remote_upload_verify'] : false,
		];
		$client = new \GuzzleHttp\Client($guzzle_options);

		try
		{
			$response = $client->get($upload_url, $guzzle_options);
		}
		catch (\GuzzleHttp\Exception\ClientException $clientException)
		{
			return $this->factory->get('filespec')->set_error($this->upload->error_prefix . 'URL_NOT_FOUND');
		}
		catch (\GuzzleHttp\Exception\RequestException $requestException)
		{
			if (strpos($requestException->getMessage(), 'cURL error 28') !== false || preg_match('/408|504/', $requestException->getCode()))
			{
				return $this->factory->get('filespec')->set_error($this->upload->error_prefix . 'REMOTE_UPLOAD_TIMEOUT');
			}
			else
			{
				return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'NOT_UPLOADED'));
			}
		}
		catch (\Exception $e)
		{
			return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'NOT_UPLOADED'));
		}

		$content_length = $response->getBody()->getSize();
		if ($remote_max_filesize && $content_length > $remote_max_filesize)
		{
			$max_filesize = get_formatted_filesize($remote_max_filesize, false);

			return $this->factory->get('filespec')->set_error($this->language->lang($this->upload->error_prefix . 'WRONG_FILESIZE', $max_filesize['value'], $max_filesize['unit']));
		}

		if ($content_length == 0)
		{
			return $this->factory->get('filespec')->set_error($this->upload->error_prefix . 'EMPTY_REMOTE_DATA');
		}

		$data = $response->getBody();

		$filename = tempnam(sys_get_temp_dir(), unique_id() . '-');

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
			->set_upload_namespace($this->upload);
		$this->upload->common_checks($file);

		return $file;
	}

	/**
	 * Get maximum file size for remote uploads
	 *
	 * @return int Maximum file size
	 */
	protected function get_max_file_size()
	{
		$max_file_size = $this->upload->max_filesize;
		if (!$max_file_size)
		{
			$max_file_size = $this->php_ini->getString('upload_max_filesize');

			if (!empty($max_file_size))
			{
				$unit = strtolower(substr($max_file_size, -1, 1));
				$max_file_size = (int) $max_file_size;

				switch ($unit)
				{
					case 'g':
						$max_file_size *= 1024;
					// no break
					case 'm':
						$max_file_size *= 1024;
					// no break
					case 'k':
						$max_file_size *= 1024;
					// no break
				}
			}
		}

		return $max_file_size;
	}
}
