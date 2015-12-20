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

namespace phpbb;

class permissions
{
	/**
	* Event dispatcher object
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $dispatcher;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Constructor
	*
	* @param	\phpbb\event\dispatcher_interface	$phpbb_dispatcher	Event dispatcher
	* @param	\phpbb\user				$user				User Object
	*/
	public function __construct(\phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\user $user)
	{
		$this->dispatcher = $phpbb_dispatcher;
		$this->user = $user;

		$categories = $this->categories;
		$types = $this->types;
		$permissions = $this->permissions;

		/**
		* Allows to specify additional permission categories, types and permissions
		*
		* @event core.permissions
		* @var	array	types			Array with permission types (a_, u_, m_, etc.)
		* @var	array	categories		Array with permission categories (pm, post, settings, misc, etc.)
		* @var	array	permissions		Array with permissions. Each Permission has the following layout:
		*		'<type><permission>' => array(
		*			'lang'	=> 'Language Key with a Short description', // Optional, if not set,
		*						// the permissions identifier '<type><permission>' is used with
		*						// all uppercase.
		*			'cat'	=> 'Identifier of the category, the permission should be displayed in',
		*		),
		*		Example:
		*		'u_viewprofile' => array(
		*			'lang'	=> 'ACL_U_VIEWPROFILE',
		*			'cat'	=> 'profile',
		*		),
		* @since 3.1.0-a1
		*/
		$vars = array('types', 'categories', 'permissions');
		extract($phpbb_dispatcher->trigger_event('core.permissions', compact($vars)));

		$this->categories = $categories;
		$this->types = $types;
		$this->permissions = $permissions;
	}

	/**
	* Returns an array with all the permission categories (pm, post, settings, misc, etc.)
	*
	* @return	array	Layout: cat-identifier => Language key
	*/
	public function get_categories()
	{
		return $this->categories;
	}

	/**
	* Returns the language string of a permission category
	*
	* @param	string	$category	Identifier of the category
	* @return	string		Language string
	*/
	public function get_category_lang($category)
	{
		return $this->user->lang($this->categories[$category]);
	}

	/**
	* Returns an array with all the permission types (a_, u_, m_, etc.)
	*
	* @return	array	Layout: type-identifier => Language key
	*/
	public function get_types()
	{
		return $this->types;
	}

	/**
	* Returns the language string of a permission type
	*
	* @param	string	$type	Identifier of the type
	* @param	mixed	$scope	Scope of the type (should be 'global', 'local' or false)
	* @return	string	Language string
	*/
	public function get_type_lang($type, $scope = false)
	{
		if ($scope && isset($this->types[$scope][$type]))
		{
			$lang_key = $this->types[$scope][$type];
		}
		else if (isset($this->types[$type]))
		{
			$lang_key = $this->types[$type];
		}
		else
		{
			$lang_key = 'ACL_TYPE_' . strtoupper(($scope) ? $scope . '_' . $type : $type);
		}

		return $this->user->lang($lang_key);
	}

	/**
	* Returns an array with all the permissions.
	* Each Permission has the following layout:
	*	'<type><permission>' => array(
	*		'lang'	=> 'Language Key with a Short description', // Optional, if not set,
	*					// the permissions identifier '<type><permission>' is used with
	*					// all uppercase.
	*		'cat'	=> 'Identifier of the category, the permission should be displayed in',
	*	),
	*	Example:
	*	'u_viewprofile' => array(
	*		'lang'	=> 'ACL_U_VIEWPROFILE',
	*		'cat'	=> 'profile',
	*	),
	*
	* @return	array
	*/
	public function get_permissions()
	{
		return $this->permissions;
	}

	/**
	* Returns the category of a permission
	*
	* @param	string	$permission	Identifier of the permission
	* @return	string		Returns the category identifier of the permission
	*/
	public function get_permission_category($permission)
	{
		return (isset($this->permissions[$permission]['cat'])) ? $this->permissions[$permission]['cat'] : 'misc';
	}

	/**
	* Checks if a category has been defined
	*
	* @param	string	$category	Identifier of the category
	* @return	bool	True if the category is defined, false otherwise
	*/
	public function category_defined($category)
	{
		return isset($this->categories[$category]);
	}

	/**
	* Checks if a permission has been defined
	*
	* @param	string	$permission	Identifier of the permission
	* @return	bool	True if the permission is defined, false otherwise
	*/
	public function permission_defined($permission)
	{
		return isset($this->permissions[$permission]);
	}

	/**
	* Returns the language string of a permission
	*
	* @param	string	$permission	Identifier of the permission
	* @return	string	Language string
	*/
	public function get_permission_lang($permission)
	{
		return (isset($this->permissions[$permission]['lang'])) ? $this->user->lang($this->permissions[$permission]['lang']) : $this->user->lang('ACL_' . strtoupper($permission));
	}

	protected $types = array(
		'u_'			=> 'ACL_TYPE_U_',
		'a_'			=> 'ACL_TYPE_A_',
		'm_'			=> 'ACL_TYPE_M_',
		'f_'			=> 'ACL_TYPE_F_',
		'global'		=> array(
			'm_'			=> 'ACL_TYPE_GLOBAL_M_',
		),
	);

	protected $categories = array(
		'actions'		=> 'ACL_CAT_ACTIONS',
		'content'		=> 'ACL_CAT_CONTENT',
		'forums'		=> 'ACL_CAT_FORUMS',
		'misc'			=> 'ACL_CAT_MISC',
		'permissions'	=> 'ACL_CAT_PERMISSIONS',
		'pm'			=> 'ACL_CAT_PM',
		'polls'			=> 'ACL_CAT_POLLS',
		'post'			=> 'ACL_CAT_POST',
		'post_actions'	=> 'ACL_CAT_POST_ACTIONS',
		'posting'		=> 'ACL_CAT_POSTING',
		'profile'		=> 'ACL_CAT_PROFILE',
		'settings'		=> 'ACL_CAT_SETTINGS',
		'topic_actions'	=> 'ACL_CAT_TOPIC_ACTIONS',
		'user_group'	=> 'ACL_CAT_USER_GROUP',
	);

	protected $permissions = array(
		// User Permissions
		'u_viewprofile'	=> array('lang' => 'ACL_U_VIEWPROFILE', 'cat' => 'profile'),
		'u_chgname'		=> array('lang' => 'ACL_U_CHGNAME', 'cat' => 'profile'),
		'u_chgpasswd'	=> array('lang' => 'ACL_U_CHGPASSWD', 'cat' => 'profile'),
		'u_chgemail'	=> array('lang' => 'ACL_U_CHGEMAIL', 'cat' => 'profile'),
		'u_chgavatar'	=> array('lang' => 'ACL_U_CHGAVATAR', 'cat' => 'profile'),
		'u_chggrp'		=> array('lang' => 'ACL_U_CHGGRP', 'cat' => 'profile'),
		'u_chgprofileinfo'	=> array('lang' => 'ACL_U_CHGPROFILEINFO', 'cat' => 'profile'),

		'u_attach'		=> array('lang' => 'ACL_U_ATTACH', 'cat' => 'post'),
		'u_download'	=> array('lang' => 'ACL_U_DOWNLOAD', 'cat' => 'post'),
		'u_savedrafts'	=> array('lang' => 'ACL_U_SAVEDRAFTS', 'cat' => 'post'),
		'u_chgcensors'	=> array('lang' => 'ACL_U_CHGCENSORS', 'cat' => 'post'),
		'u_sig'			=> array('lang' => 'ACL_U_SIG', 'cat' => 'post'),

		'u_sendpm'		=> array('lang' => 'ACL_U_SENDPM', 'cat' => 'pm'),
		'u_masspm'		=> array('lang' => 'ACL_U_MASSPM', 'cat' => 'pm'),
		'u_masspm_group'=> array('lang' => 'ACL_U_MASSPM_GROUP', 'cat' => 'pm'),
		'u_readpm'		=> array('lang' => 'ACL_U_READPM', 'cat' => 'pm'),
		'u_pm_edit'		=> array('lang' => 'ACL_U_PM_EDIT', 'cat' => 'pm'),
		'u_pm_delete'	=> array('lang' => 'ACL_U_PM_DELETE', 'cat' => 'pm'),
		'u_pm_forward'	=> array('lang' => 'ACL_U_PM_FORWARD', 'cat' => 'pm'),
		'u_pm_emailpm'	=> array('lang' => 'ACL_U_PM_EMAILPM', 'cat' => 'pm'),
		'u_pm_printpm'	=> array('lang' => 'ACL_U_PM_PRINTPM', 'cat' => 'pm'),
		'u_pm_attach'	=> array('lang' => 'ACL_U_PM_ATTACH', 'cat' => 'pm'),
		'u_pm_download'	=> array('lang' => 'ACL_U_PM_DOWNLOAD', 'cat' => 'pm'),
		'u_pm_bbcode'	=> array('lang' => 'ACL_U_PM_BBCODE', 'cat' => 'pm'),
		'u_pm_smilies'	=> array('lang' => 'ACL_U_PM_SMILIES', 'cat' => 'pm'),
		'u_pm_img'		=> array('lang' => 'ACL_U_PM_IMG', 'cat' => 'pm'),
		'u_pm_flash'	=> array('lang' => 'ACL_U_PM_FLASH', 'cat' => 'pm'),

		'u_sendemail'	=> array('lang' => 'ACL_U_SENDEMAIL', 'cat' => 'misc'),
		'u_sendim'		=> array('lang' => 'ACL_U_SENDIM', 'cat' => 'misc'),
		'u_ignoreflood'	=> array('lang' => 'ACL_U_IGNOREFLOOD', 'cat' => 'misc'),
		'u_hideonline'	=> array('lang' => 'ACL_U_HIDEONLINE', 'cat' => 'misc'),
		'u_viewonline'	=> array('lang' => 'ACL_U_VIEWONLINE', 'cat' => 'misc'),
		'u_search'		=> array('lang' => 'ACL_U_SEARCH', 'cat' => 'misc'),

		// Forum Permissions
		'f_list'		=> array('lang' => 'ACL_F_LIST', 'cat' => 'actions'),
		'f_read'		=> array('lang' => 'ACL_F_READ', 'cat' => 'actions'),
		'f_search'		=> array('lang' => 'ACL_F_SEARCH', 'cat' => 'actions'),
		'f_subscribe'	=> array('lang' => 'ACL_F_SUBSCRIBE', 'cat' => 'actions'),
		'f_print'		=> array('lang' => 'ACL_F_PRINT', 'cat' => 'actions'),
		'f_email'		=> array('lang' => 'ACL_F_EMAIL', 'cat' => 'actions'),
		'f_bump'		=> array('lang' => 'ACL_F_BUMP', 'cat' => 'actions'),
		'f_user_lock'	=> array('lang' => 'ACL_F_USER_LOCK', 'cat' => 'actions'),
		'f_download'	=> array('lang' => 'ACL_F_DOWNLOAD', 'cat' => 'actions'),
		'f_report'		=> array('lang' => 'ACL_F_REPORT', 'cat' => 'actions'),

		'f_post'		=> array('lang' => 'ACL_F_POST', 'cat' => 'post'),
		'f_sticky'		=> array('lang' => 'ACL_F_STICKY', 'cat' => 'post'),
		'f_announce'	=> array('lang' => 'ACL_F_ANNOUNCE', 'cat' => 'post'),
		'f_announce_global'	=> array('lang' => 'ACL_F_ANNOUNCE_GLOBAL', 'cat' => 'post'),
		'f_reply'		=> array('lang' => 'ACL_F_REPLY', 'cat' => 'post'),
		'f_edit'		=> array('lang' => 'ACL_F_EDIT', 'cat' => 'post'),
		'f_delete'		=> array('lang' => 'ACL_F_DELETE', 'cat' => 'post'),
		'f_softdelete'	=> array('lang' => 'ACL_F_SOFTDELETE', 'cat' => 'post'),
		'f_ignoreflood' => array('lang' => 'ACL_F_IGNOREFLOOD', 'cat' => 'post'),
		'f_postcount'	=> array('lang' => 'ACL_F_POSTCOUNT', 'cat' => 'post'),
		'f_noapprove'	=> array('lang' => 'ACL_F_NOAPPROVE', 'cat' => 'post'),

		'f_attach'		=> array('lang' => 'ACL_F_ATTACH', 'cat' => 'content'),
		'f_icons'		=> array('lang' => 'ACL_F_ICONS', 'cat' => 'content'),
		'f_bbcode'		=> array('lang' => 'ACL_F_BBCODE', 'cat' => 'content'),
		'f_flash'		=> array('lang' => 'ACL_F_FLASH', 'cat' => 'content'),
		'f_img'			=> array('lang' => 'ACL_F_IMG', 'cat' => 'content'),
		'f_sigs'		=> array('lang' => 'ACL_F_SIGS', 'cat' => 'content'),
		'f_smilies'		=> array('lang' => 'ACL_F_SMILIES', 'cat' => 'content'),

		'f_poll'		=> array('lang' => 'ACL_F_POLL', 'cat' => 'polls'),
		'f_vote'		=> array('lang' => 'ACL_F_VOTE', 'cat' => 'polls'),
		'f_votechg'		=> array('lang' => 'ACL_F_VOTECHG', 'cat' => 'polls'),

		// Moderator Permissions
		'm_edit'		=> array('lang' => 'ACL_M_EDIT', 'cat' => 'post_actions'),
		'm_delete'		=> array('lang' => 'ACL_M_DELETE', 'cat' => 'post_actions'),
		'm_approve'		=> array('lang' => 'ACL_M_APPROVE', 'cat' => 'post_actions'),
		'm_report'		=> array('lang' => 'ACL_M_REPORT', 'cat' => 'post_actions'),
		'm_chgposter'	=> array('lang' => 'ACL_M_CHGPOSTER', 'cat' => 'post_actions'),
		'm_info'		=> array('lang' => 'ACL_M_INFO', 'cat' => 'post_actions'),
		'm_softdelete'	=> array('lang' => 'ACL_M_SOFTDELETE', 'cat' => 'post_actions'),

		'm_move'	=> array('lang' => 'ACL_M_MOVE', 'cat' => 'topic_actions'),
		'm_lock'	=> array('lang' => 'ACL_M_LOCK', 'cat' => 'topic_actions'),
		'm_split'	=> array('lang' => 'ACL_M_SPLIT', 'cat' => 'topic_actions'),
		'm_merge'	=> array('lang' => 'ACL_M_MERGE', 'cat' => 'topic_actions'),

		'm_warn'		=> array('lang' => 'ACL_M_WARN', 'cat' => 'misc'),
		'm_pm_report'	=> array('lang' => 'ACL_M_PM_REPORT', 'cat' => 'misc'),
		'm_ban'			=> array('lang' => 'ACL_M_BAN', 'cat' => 'misc'),

		// Admin Permissions
		'a_board'		=> array('lang' => 'ACL_A_BOARD', 'cat' => 'settings'),
		'a_server'		=> array('lang' => 'ACL_A_SERVER', 'cat' => 'settings'),
		'a_jabber'		=> array('lang' => 'ACL_A_JABBER', 'cat' => 'settings'),
		'a_phpinfo'		=> array('lang' => 'ACL_A_PHPINFO', 'cat' => 'settings'),

		'a_forum'		=> array('lang' => 'ACL_A_FORUM', 'cat' => 'forums'),
		'a_forumadd'	=> array('lang' => 'ACL_A_FORUMADD', 'cat' => 'forums'),
		'a_forumdel'	=> array('lang' => 'ACL_A_FORUMDEL', 'cat' => 'forums'),
		'a_prune'		=> array('lang' => 'ACL_A_PRUNE', 'cat' => 'forums'),

		'a_icons'		=> array('lang' => 'ACL_A_ICONS', 'cat' => 'posting'),
		'a_words'		=> array('lang' => 'ACL_A_WORDS', 'cat' => 'posting'),
		'a_bbcode'		=> array('lang' => 'ACL_A_BBCODE', 'cat' => 'posting'),
		'a_attach'		=> array('lang' => 'ACL_A_ATTACH', 'cat' => 'posting'),

		'a_user'		=> array('lang' => 'ACL_A_USER', 'cat' => 'user_group'),
		'a_userdel'		=> array('lang' => 'ACL_A_USERDEL', 'cat' => 'user_group'),
		'a_group'		=> array('lang' => 'ACL_A_GROUP', 'cat' => 'user_group'),
		'a_groupadd'	=> array('lang' => 'ACL_A_GROUPADD', 'cat' => 'user_group'),
		'a_groupdel'	=> array('lang' => 'ACL_A_GROUPDEL', 'cat' => 'user_group'),
		'a_ranks'		=> array('lang' => 'ACL_A_RANKS', 'cat' => 'user_group'),
		'a_profile'		=> array('lang' => 'ACL_A_PROFILE', 'cat' => 'user_group'),
		'a_names'		=> array('lang' => 'ACL_A_NAMES', 'cat' => 'user_group'),
		'a_ban'			=> array('lang' => 'ACL_A_BAN', 'cat' => 'user_group'),

		'a_viewauth'	=> array('lang' => 'ACL_A_VIEWAUTH', 'cat' => 'permissions'),
		'a_authgroups'	=> array('lang' => 'ACL_A_AUTHGROUPS', 'cat' => 'permissions'),
		'a_authusers'	=> array('lang' => 'ACL_A_AUTHUSERS', 'cat' => 'permissions'),
		'a_fauth'		=> array('lang' => 'ACL_A_FAUTH', 'cat' => 'permissions'),
		'a_mauth'		=> array('lang' => 'ACL_A_MAUTH', 'cat' => 'permissions'),
		'a_aauth'		=> array('lang' => 'ACL_A_AAUTH', 'cat' => 'permissions'),
		'a_uauth'		=> array('lang' => 'ACL_A_UAUTH', 'cat' => 'permissions'),
		'a_roles'		=> array('lang' => 'ACL_A_ROLES', 'cat' => 'permissions'),
		'a_switchperm'	=> array('lang' => 'ACL_A_SWITCHPERM', 'cat' => 'permissions'),

		'a_styles'		=> array('lang' => 'ACL_A_STYLES', 'cat' => 'misc'),
		'a_extensions'	=> array('lang' => 'ACL_A_EXTENSIONS', 'cat' => 'misc'),
		'a_viewlogs'	=> array('lang' => 'ACL_A_VIEWLOGS', 'cat' => 'misc'),
		'a_clearlogs'	=> array('lang' => 'ACL_A_CLEARLOGS', 'cat' => 'misc'),
		'a_modules'		=> array('lang' => 'ACL_A_MODULES', 'cat' => 'misc'),
		'a_language'	=> array('lang' => 'ACL_A_LANGUAGE', 'cat' => 'misc'),
		'a_email'		=> array('lang' => 'ACL_A_EMAIL', 'cat' => 'misc'),
		'a_bots'		=> array('lang' => 'ACL_A_BOTS', 'cat' => 'misc'),
		'a_reasons'		=> array('lang' => 'ACL_A_REASONS', 'cat' => 'misc'),
		'a_backup'		=> array('lang' => 'ACL_A_BACKUP', 'cat' => 'misc'),
		'a_search'		=> array('lang' => 'ACL_A_SEARCH', 'cat' => 'misc'),
	);
}
