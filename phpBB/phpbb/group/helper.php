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

namespace phpbb\group;

class helper
{
	/** @var  \phpbb\language\language */
	protected $language;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language $language	Language object
	 */
	public function __construct(\phpbb\language\language $language)
	{
		$this->language = $language;
	}

	/**
	 * @param $group_name string	The stored group name
	 *
	 * @return string		Group name or translated group name if it exists
	 */
	public function get_name($group_name)
	{
		return $this->language->is_set('G_' . utf8_strtoupper($group_name)) ? $this->language->lang('G_' . utf8_strtoupper($group_name)) : $group_name;
	}
}
