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
use \phpbb\language\language;
use \phpbb\request\request_interface;

class local extends base
{
	/** @var factory Files factory */
	protected $factory;

	/** @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/** @var \phpbb\files\upload */
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
		return $this->local_upload($args[0], isset($args[1]) ? $args[1] : false);
	}

	/**
	 * Move file from another location to phpBB
	 *
	 * @param string $source_file Filename of source file
	 * @param array|bool $filedata Array with filedata or false
	 *
	 * @return filespec Object "filespec" is returned, all further operations can be done with this object
	 */
	protected function local_upload($source_file, $filedata = false)
	{
		$upload = $this->get_upload_ary($source_file, $filedata);

		/** @var filespec $file */
		$file = $this->factory->get('filespec')
			->set_upload_ary($upload)
			->set_upload_namespace($this->upload);

		if ($file->init_error())
		{
			$file->error[] = '';
			return $file;
		}

		if (isset($upload['error']))
		{
			$error = $this->upload->assign_internal_error($upload['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// PHP Upload filesize exceeded
		if ($file->get('filename') == 'none')
		{
			$max_filesize = @ini_get('upload_max_filesize');
			$unit = 'MB';

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$max_filesize = (int) $max_filesize;

				$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
			}

			$file->error[] = (empty($max_filesize)) ?$this->language->lang($this->upload->error_prefix . 'PHP_SIZE_NA') : $this->language->lang($this->upload->error_prefix . 'PHP_SIZE_OVERRUN', $max_filesize, $this->language->lang($unit));
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $this->language->lang($this->upload->error_prefix . 'NOT_UPLOADED');
			return $file;
		}

		$this->upload->common_checks($file);
		$this->request->overwrite('local', $upload, request_interface::FILES);

		return $file;
	}

	/**
	 * Retrieve upload array
	 *
	 * @param string $source_file Source file name
	 * @param array $filedata File data array
	 *
	 * @return array Upload array
	 */
	protected function get_upload_ary($source_file, $filedata)
	{
		$upload = array();

		$upload['local_mode'] = true;
		$upload['tmp_name'] = $source_file;

		if ($filedata === false)
		{
			$upload['name'] = utf8_basename($source_file);
			$upload['size'] = 0;
		}
		else
		{
			$upload['name'] = $filedata['realname'];
			$upload['size'] = $filedata['size'];
			$upload['type'] = $filedata['type'];
		}

		return $upload;
	}
}
