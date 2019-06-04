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

namespace phpbb\cp\menu;

interface item_interface
{
	/**
	 * Get this item's authentication string.
	 *
	 * This has to be a string that will be evaluated for authentication,
	 * whether or not a user can access (and thus see) this item.
	 *
	 * This can be build as a regular "if"-statement,
	 * with parenthesis "()" and combining operators "&&" and "||".
	 *
	 * Options are:
	 * 	acl_*				(bool) $this->auth->acl_get('*');
	 * 	aclf_*				(bool) $this->auth->acl_getf_global('*');
	 * 	cfg_*				(bool) $this->config['*'];
	 * 	request_*			(bool) $this->request->variable('*', false);
	 * 	ext_*				(bool) in_array('*', array_keys($ext_manager->all_enabled()));
	 *  authmethod_*		(bool) ($config['auth_method'] === '*');
	 *
	 * For example, "(acl_a_board && ext_vendor/extension)"
	 * will check if the user has the "a_board" permission:
	 * 		$this->auth->acl_get('a_board');
	 * and if the "vendor/extension" extension is enabled:
	 * 		in_array('vendor/extension', array_keys($ext_manager->all_enabled()));
	 *
	 * @return string
	 */
	public function get_auth();

	/**
	 * Get this item's icon.
	 *
	 * @todo
	 *
	 * @return string
	 */
	public function get_icon();

	/**
	 * Get this item's parent.
	 *
	 * This has to be the name of the service declaration in the .yml file,
	 * or it can be left empty if this item is a top level category.
	 * For listing it as an item/subcategory under the Extension tab,
	 * the string should be "acp_cat_extensions".
	 *
	 * @return string
	 */
	public function get_parent();

	/**
	 * Get this item's before sibling.
	 *
	 * This has to be the name of the service declaration in the .yml file,
	 * which has to be defined before this gets included. Otherwise it is just appended
	 * to the children of the provided "parent" string.
	 *
	 * An example would be, if you wanted to add your "Emoji" item before the
	 * "Smilies" item in the list, you would enter "acp_smilies".
	 *
	 * The before approach has been taken as the "after"-way is the regular way,
	 * as in when nothing is specified it gets appended to the children list.
	 * That would mean that an extension item could never be the first in a list.
	 * This way position is completely determinable, either before a specific item
	 * or appended to the children list of the specified parent.
	 *
	 * @return string
	 */
	public function get_before();

	/**
	 * Get this item's route.
	 *
	 * This should either be a string when it's a category, pointing to the first child to be loaded.
	 * Or it should  be an array when a route has to be created for this item.
	 *
	 * When it's an array, it should be identical to how one would declare in a routing.yml file,
	 * meaning it should have a "path" and "defaults" as keys. And a "_controller" in the "defaults".
	 *
	 * For example, the "Configuration" category has the string "acp_index", pointing to that route.
	 *  acp_cat_configuration:
	 * 		$route: acp_index
	 * While "acp_index" declaration has an array as such:
	 *  acp_index:
	 * 		$route:
	 * 			path: /index
	 * 			defaults:
	 * 				_controller: acp.main:main
	 *
	 * @return array|string
	 */
	public function get_route();

	/**
	 * Get this item's pagination variable.
	 *
	 * This should only be defined if pagination is required for this controller.
	 * The string defined here should be name of the variable used.
	 *
	 * If defined, an additional route will be created suffixed with "_pagination".
	 * @see \phpbb\cp\manager::get_route_pagination()
	 *
	 * All the settings from the initial "$route" declaration will be taken,
	 * where as the string defined here will be unset() from the "defaults".
	 *
	 * For example the BBCodes item looks as followed:
	 * acp_bbcodes:
	 * 		$page: 'page'
	 * 		$route:
	 * 			path: /bbcodes
	 * 			defaults:
	 * 				_controller: acp.bbcodes:main
	 * 				page: 1
	 *
	 * This will create 2 routes, namely "acp_bbcodes" and "acp_bbcodes_pagination",
	 * where "page" is unset() from the defaults in the pagination route.
	 *
	 * @return string
	 */
	public function get_page();

	/**
	 * Get this item's display property.
	 *
	 * This can be set to false,
	 * if this item should not be displayed in the menu.
	 *
	 * @return bool
	 */
	public function get_display();
}
