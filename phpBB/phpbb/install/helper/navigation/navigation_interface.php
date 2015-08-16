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

namespace phpbb\install\helper\navigation;

/**
 * Interface for installer's navigation defining services
 */
interface navigation_interface
{
	/**
	 * Returns an array with the navigation items
	 *
	 * The returned array should have the following format:
	 * <code>
	 * array(
	 * 	'parent_nav_name' => array(
	 * 		'nav_name' => array(
	 * 			'label' => 'MY_MENU',
	 * 			'route' => 'phpbb_route_name',
	 * 		)
	 * 	)
	 * )
	 * </code>
	 *
	 * Navigation item setting options:
	 * 	- label: The language variable name
	 * 	- route: Name of the route which it is belongs to
	 *
	 * @return array
	 */
	public function get();
}
