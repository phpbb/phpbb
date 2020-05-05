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

namespace phpbb\template\twig\extension;

class avatar extends \Twig_Extension
{
	/**
	 * Get the name of this extension
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'avatar';
	}

	/**
	 * Returns a list of global functions to add to the existing list.
	 *
	 * @return array An array of global functions
	 */
	public function getFunctions()
	{
		return array(
			new \Twig\TwigFunction('avatar', array($this, 'get_avatar')),
		);
	}

	/**
	 * Get avatar for placing into templates.
	 *
	 * How to use in a template:
	 * - {{ avatar('mode', row, alt, ignore_config, lazy) }}
	 *
	 * The mode and row (group_row or user_row) are required.
	 * The other fields (alt|ignore_config|lazy) are optional.
	 *
	 * @uses \phpbb_get_group_avatar()
	 * @uses \phpbb_get_user_avatar()
	 *
	 * @return string	The avatar HTML for the specified mode
	 */
	public function get_avatar()
	{
		$args = func_get_args();

		$mode = (string) $args[0];
		$row = (array) $args[1];
		$alt = isset($args[2]) ? (string) $args[2] : false;
		$ignore_config = isset($args[3]) ? (bool) $args[3] : false;
		$lazy = isset($args[4]) ? (bool) $args[4] : false;

		// To prevent having to redefine alt attribute ('USER_AVATAR'|'GROUP_AVATAR'), we check if an alternative has been provided
		switch ($mode)
		{
			case 'group':
				return $alt ? phpbb_get_group_avatar($row, $alt, $ignore_config, $lazy) : phpbb_get_group_avatar($row);
			break;

			case 'user':
				return $alt ? phpbb_get_user_avatar($row, $alt, $ignore_config, $lazy) : phpbb_get_user_avatar($row);
			break;

			default:
				return '';
			break;
		}
	}
}
