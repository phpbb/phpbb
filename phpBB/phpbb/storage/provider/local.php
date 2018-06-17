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

namespace phpbb\storage\provider;

class local implements provider_interface
{
	/**
	 * {@inheritdoc}
	 */
	public function get_name()
	{
		return 'local';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_adapter_class()
	{
		return \phpbb\storage\adapter\local::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options()
	{
		return [
			'path' => array('type' => 'text'),
			'depth' => array('type' => 'text'),
			'safe_filename' => array(
				'type' => 'radio',
				'options' => array(
					'YES' => '1',
					'NO' => '0',
				),
			),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available()
	{
		return true;
	}
}
