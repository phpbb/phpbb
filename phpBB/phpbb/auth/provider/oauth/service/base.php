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

namespace phpbb\auth\provider\oauth\service;

/**
 * Base OAuth abstract class that all OAuth services should implement
 */
abstract class base implements service_interface
{
	/**
	 * External OAuth service provider
	 *
	 * @var \OAuth\Common\Service\ServiceInterface
	 */
	protected $service_provider;

	/**
	 * {@inheritdoc}
	 */
	public function get_auth_scope()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_external_service_class()
	{
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_external_service_provider()
	{
		return $this->service_provider;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_external_service_provider(\OAuth\Common\Service\ServiceInterface $service_provider)
	{
		$this->service_provider = $service_provider;
	}
}
