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
use phpbb\mimetype\extension_guesser;
use phpbb\storage\storage;
use Symfony\Component\HttpFoundation\Request as symfony_request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for /download/avatar/{file} routes
 */
class avatar extends controller
{
	/** @var config */
	protected $config;

	/** @var array */
	protected $allowed_extensions = ['png', 'gif', 'jpg', 'jpeg'];

	/**
	 * Constructor
	 *
	 * @param service				$cache
	 * @param config				$config
	 * @param driver_interface		$db
	 * @param extension_guesser		$extension_guesser
	 * @param storage				$storage
	 * @param symfony_request		$symfony_request
	 */
	public function __construct(service $cache, config $config, driver_interface $db, extension_guesser $extension_guesser, storage $storage, symfony_request $symfony_request)
	{
		parent::__construct($cache, $db , $extension_guesser, $storage, $symfony_request);

		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(string $file): Response
	{
		$file = $this->decode_filename($file);

		return parent::handle($file);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function is_allowed(string $file): bool
	{
		$ext = substr(strrchr($file, '.'), 1);

		// If filename have point and have an allowed extension
		return strpos($file, '.') && in_array($ext, $this->allowed_extensions, true);
	}

	/**
	 * Decode avatar filename
	 *
	 * @param string $file		Filename
	 *
	 * @return string Filename in filesystem
	 */
	protected function decode_filename(string $file): string
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

	/**
	 * {@inheritdoc}
	 */
	protected function prepare(StreamedResponse $response, string $file): void
	{
		$response->setPublic();

		$disposition = $response->headers->makeDisposition(
			ResponseHeaderBag::DISPOSITION_INLINE,
			rawurlencode($file)
		);

		$response->headers->set('Content-Disposition', $disposition);

		$time = new \DateTime();
		$response->setExpires($time->modify('+1 year'));

		parent::prepare($response, $file);
	}
}
