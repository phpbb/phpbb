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

class username extends \Twig_Extension
{
	/**
	 * Get the name of this extension
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'username';
	}

	/**
	 * Returns a list of global functions to add to the existing list.
	 *
	 * @return array An array of global functions
	 */
	public function getFunctions()
	{
		return array(
			new \Twig\TwigFunction('username', array($this, 'get_username')),
		);
	}

	/**
	 * Get username details for placing into templates.
	 *
	 * How to use in a template:
	 * - {{ username('mode', user_id, username, user_colour, guest_username, custom_profile_url) }}
	 * - {{ username('mode', user_row, guest_username, custom_profile_url) }}
	 * It's possible to provide the user identifier, name and colour separately,
	 * or provide the entire user row at once as an array.
	 *
	 * The mode, user_id and username are required (separately or through a user row).
	 * The other fields (user_colour|guest_username|custom_profile_url) are optional.
	 *
	 * @uses \get_username_string()
	 *
	 * @return string		A string based on what is wanted depending on $mode
	 */
	public function get_username()
	{
		$args = func_get_args();

		$mode = $args[0];
		$user = $args[1];

		// If the entire user row is provided
		if (is_array($user))
		{
			$user_id = isset($user['user_id']) ? $user['user_id'] : '';
			$username = isset($user['username']) ? $user['username'] : '';
			$user_colour = isset($user['user_colour']) ? $user['user_colour'] : '';
			$guest_username = isset($args[2]) ? $args[2] : false;
			$custom_profile_url = isset($args[3]) ? $args[3] : false;
		}
		else
		{
			// Options are provided separately
			$user_id = $user;
			$username = $args[2];
			$user_colour = isset($args[3]) ? $args[3] : '';
			$guest_username = isset($args[4]) ? $args[4] : false;
			$custom_profile_url = isset($args[5]) ? $args[5] : false;
		}

		return get_username_string($mode, $user_id, $username, $user_colour, $guest_username, $custom_profile_url);
	}
}
