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

namespace phpbb\storage\controller;

use phpbb\cache\service;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\storage\storage;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class avatar extends controller
{
	/** @var config */
	protected $config;

	protected $allowed_extensions = ['png', 'gif', 'jpg', 'jpeg'];

	public function __construct(service $cache, config $config, driver_interface $db, storage $storage)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->storage = $storage;
		$this->response = new StreamedResponse();
	}

	public function handle($file)
	{
		$file = $this->decode_avatar_filename($file);

		return parent::handle($file);
	}

	protected function is_allowed($file)
	{
		$ext = substr(strrchr($file, '.'), 1);

		// If filename have point and have an allowed extension
		return strpos($file, '.') && in_array($ext, $this->allowed_extensions);
	}

	protected function decode_avatar_filename($file)
	{
		$avatar_group = false;

		if (isset($file[0]) && $file[0] === 'g')
		{
			$avatar_group = true;
			$file = substr($file, 1);
		}

		$ext	= substr(strrchr($file, '.'), 1);
		$file	= (int) $file;

		return $this->config['avatar_salt'] . '_' . ($avatar_group ? 'g' : '') . $file . '.' . $ext;
	}

	protected function send($file)
	{
		if (!headers_sent())
		{
			$disposition = $this->response->headers->makeDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				rawurlencode($file)
			);

			$this->response->headers->set('Content-Disposition', $disposition);

			$time = new \Datetime();
			$this->response->setExpires($time->modify('+1 year'));
		}

		parent::send($file);
	}
}
