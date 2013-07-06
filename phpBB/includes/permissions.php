<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_permissions
{
	/**
	* Event dispatcher object
	* @var phpbb_event_dispatcher
	*/
	protected $dispatcher;

	/**
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Constructor
	*
	* @param	phpbb_event_dispatcher	$phpbb_dispatcher	Event dispatcher
	* @param	phpbb_user				$user				User Object
	* @return	null
	*/
	public function __construct(phpbb_event_dispatcher $phpbb_dispatcher, phpbb_user $user)
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
		*		'acl_<type><permission>' => array(
		*			'lang'	=> 'Language Key with a Short description', // Optional, if not set,
		*						// the permissions identifier 'acl_<type><permission>' is used with
		*						// all uppercase.
		*			'cat'	=> 'Identifier of the category, the permission should be displayed in',
		*		),
		*		Example:
		*		'acl_u_viewprofile' => array(
		*			'lang'	=> 'ACL_U_VIEWPROFILE',
		*			'cat'	=> 'profile',
		*		),
		* @since 3.1-A1
		*/
		$vars = array('types', 'categories', 'permissions');
		extract($phpbb_dispatcher->trigger_event('core.permissions', $vars));

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
	* @return	string	Language string
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
	*	'acl_<type><permission>' => array(
	*		'lang'	=> 'Language Key with a Short description', // Optional, if not set,
	*					// the permissions identifier 'acl_<type><permission>' is used with
	*					// all uppercase.
	*		'cat'	=> 'Identifier of the category, the permission should be displayed in',
	*	),
	*	Example:
	*	'acl_u_viewprofile' => array(
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
	* @return	string
	*/
	public function get_permission_category($permission)
	{
		return (isset($this->permissions[$permission]['cat'])) ? $this->permissions[$permission]['cat'] : 'misc';
	}

	/**
	* Returns the language string of a permission
	*
	* @return	string	Language string
	*/
	public function get_permission_lang($permission)
	{
		return (isset($this->permissions['acl_' . $permission]['lang'])) ? $this->user->lang($this->permissions['acl_' . $permission]['lang']) : $this->user->lang('ACL_' . strtoupper($permission));
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
		'acl_u_viewprofile'	=> array('lang' => 'ACL_U_VIEWPROFILE', 'cat' => 'profile'),
		'acl_u_chgname'		=> array('lang' => 'ACL_U_CHGNAME', 'cat' => 'profile'),
		'acl_u_chgpasswd'	=> array('lang' => 'ACL_U_CHGPASSWD', 'cat' => 'profile'),
		'acl_u_chgemail'	=> array('lang' => 'ACL_U_CHGEMAIL', 'cat' => 'profile'),
		'acl_u_chgavatar'	=> array('lang' => 'ACL_U_CHGAVATAR', 'cat' => 'profile'),
		'acl_u_chggrp'		=> array('lang' => 'ACL_U_CHGGRP', 'cat' => 'profile'),
		'acl_u_chgprofileinfo'	=> array('lang' => 'ACL_U_CHGPROFILEINFO', 'cat' => 'profile'),

		'acl_u_attach'		=> array('lang' => 'ACL_U_ATTACH', 'cat' => 'post'),
		'acl_u_download'	=> array('lang' => 'ACL_U_DOWNLOAD', 'cat' => 'post'),
		'acl_u_savedrafts'	=> array('lang' => 'ACL_U_SAVEDRAFTS', 'cat' => 'post'),
		'acl_u_chgcensors'	=> array('lang' => 'ACL_U_CHGCENSORS', 'cat' => 'post'),
		'acl_u_sig'			=> array('lang' => 'ACL_U_SIG', 'cat' => 'post'),

		'acl_u_sendpm'		=> array('lang' => 'ACL_U_SENDPM', 'cat' => 'pm'),
		'acl_u_masspm'		=> array('lang' => 'ACL_U_MASSPM', 'cat' => 'pm'),
		'acl_u_masspm_group'=> array('lang' => 'ACL_U_MASSPM_GROUP', 'cat' => 'pm'),
		'acl_u_readpm'		=> array('lang' => 'ACL_U_READPM', 'cat' => 'pm'),
		'acl_u_pm_edit'		=> array('lang' => 'ACL_U_PM_EDIT', 'cat' => 'pm'),
		'acl_u_pm_delete'	=> array('lang' => 'ACL_U_PM_DELETE', 'cat' => 'pm'),
		'acl_u_pm_forward'	=> array('lang' => 'ACL_U_PM_FORWARD', 'cat' => 'pm'),
		'acl_u_pm_emailpm'	=> array('lang' => 'ACL_U_PM_EMAILPM', 'cat' => 'pm'),
		'acl_u_pm_printpm'	=> array('lang' => 'ACL_U_PM_PRINTPM', 'cat' => 'pm'),
		'acl_u_pm_attach'	=> array('lang' => 'ACL_U_PM_ATTACH', 'cat' => 'pm'),
		'acl_u_pm_download'	=> array('lang' => 'ACL_U_PM_DOWNLOAD', 'cat' => 'pm'),
		'acl_u_pm_bbcode'	=> array('lang' => 'ACL_U_PM_BBCODE', 'cat' => 'pm'),
		'acl_u_pm_smilies'	=> array('lang' => 'ACL_U_PM_SMILIES', 'cat' => 'pm'),
		'acl_u_pm_img'		=> array('lang' => 'ACL_U_PM_IMG', 'cat' => 'pm'),
		'acl_u_pm_flash'	=> array('lang' => 'ACL_U_PM_FLASH', 'cat' => 'pm'),

		'acl_u_sendemail'	=> array('lang' => 'ACL_U_SENDEMAIL', 'cat' => 'misc'),
		'acl_u_sendim'		=> array('lang' => 'ACL_U_SENDIM', 'cat' => 'misc'),
		'acl_u_ignoreflood'	=> array('lang' => 'ACL_U_IGNOREFLOOD', 'cat' => 'misc'),
		'acl_u_hideonline'	=> array('lang' => 'ACL_U_HIDEONLINE', 'cat' => 'misc'),
		'acl_u_viewonline'	=> array('lang' => 'ACL_U_VIEWONLINE', 'cat' => 'misc'),
		'acl_u_search'		=> array('lang' => 'ACL_U_SEARCH', 'cat' => 'misc'),

		// Forum Permissions
		'acl_f_list'		=> array('lang' => 'ACL_F_LIST', 'cat' => 'actions'),
		'acl_f_read'		=> array('lang' => 'ACL_F_READ', 'cat' => 'actions'),	
		'acl_f_search'		=> array('lang' => 'ACL_F_SEARCH', 'cat' => 'actions'),
		'acl_f_subscribe'	=> array('lang' => 'ACL_F_SUBSCRIBE', 'cat' => 'actions'),
		'acl_f_print'		=> array('lang' => 'ACL_F_PRINT', 'cat' => 'actions'),	
		'acl_f_email'		=> array('lang' => 'ACL_F_EMAIL', 'cat' => 'actions'),	
		'acl_f_bump'		=> array('lang' => 'ACL_F_BUMP', 'cat' => 'actions'),
		'acl_f_user_lock'	=> array('lang' => 'ACL_F_USER_LOCK', 'cat' => 'actions'),
		'acl_f_download'	=> array('lang' => 'ACL_F_DOWNLOAD', 'cat' => 'actions'),	
		'acl_f_report'		=> array('lang' => 'ACL_F_REPORT', 'cat' => 'actions'),

		'acl_f_post'		=> array('lang' => 'ACL_F_POST', 'cat' => 'post'),
		'acl_f_sticky'		=> array('lang' => 'ACL_F_STICKY', 'cat' => 'post'),
		'acl_f_announce'	=> array('lang' => 'ACL_F_ANNOUNCE', 'cat' => 'post'),
		'acl_f_reply'		=> array('lang' => 'ACL_F_REPLY', 'cat' => 'post'),
		'acl_f_edit'		=> array('lang' => 'ACL_F_EDIT', 'cat' => 'post'),
		'acl_f_delete'		=> array('lang' => 'ACL_F_DELETE', 'cat' => 'post'),
		'acl_f_ignoreflood' => array('lang' => 'ACL_F_IGNOREFLOOD', 'cat' => 'post'),
		'acl_f_postcount'	=> array('lang' => 'ACL_F_POSTCOUNT', 'cat' => 'post'),
		'acl_f_noapprove'	=> array('lang' => 'ACL_F_NOAPPROVE', 'cat' => 'post'),	

		'acl_f_attach'		=> array('lang' => 'ACL_F_ATTACH', 'cat' => 'content'),	
		'acl_f_icons'		=> array('lang' => 'ACL_F_ICONS', 'cat' => 'content'),
		'acl_f_bbcode'		=> array('lang' => 'ACL_F_BBCODE', 'cat' => 'content'),	
		'acl_f_flash'		=> array('lang' => 'ACL_F_FLASH', 'cat' => 'content'),
		'acl_f_img'			=> array('lang' => 'ACL_F_IMG', 'cat' => 'content'),
		'acl_f_sigs'		=> array('lang' => 'ACL_F_SIGS', 'cat' => 'content'),
		'acl_f_smilies'		=> array('lang' => 'ACL_F_SMILIES', 'cat' => 'content'),

		'acl_f_poll'		=> array('lang' => 'ACL_F_POLL', 'cat' => 'polls'),
		'acl_f_vote'		=> array('lang' => 'ACL_F_VOTE', 'cat' => 'polls'),
		'acl_f_votechg'		=> array('lang' => 'ACL_F_VOTECHG', 'cat' => 'polls'),

		// Moderator Permissions
		'acl_m_edit'		=> array('lang' => 'ACL_M_EDIT', 'cat' => 'post_actions'),
		'acl_m_delete'		=> array('lang' => 'ACL_M_DELETE', 'cat' => 'post_actions'),
		'acl_m_approve'		=> array('lang' => 'ACL_M_APPROVE', 'cat' => 'post_actions'),
		'acl_m_report'		=> array('lang' => 'ACL_M_REPORT', 'cat' => 'post_actions'),
		'acl_m_chgposter'	=> array('lang' => 'ACL_M_CHGPOSTER', 'cat' => 'post_actions'),

		'acl_m_move'	=> array('lang' => 'ACL_M_MOVE', 'cat' => 'topic_actions'),
		'acl_m_lock'	=> array('lang' => 'ACL_M_LOCK', 'cat' => 'topic_actions'),
		'acl_m_split'	=> array('lang' => 'ACL_M_SPLIT', 'cat' => 'topic_actions'),
		'acl_m_merge'	=> array('lang' => 'ACL_M_MERGE', 'cat' => 'topic_actions'),

		'acl_m_info'	=> array('lang' => 'ACL_M_INFO', 'cat' => 'misc'),
		'acl_m_warn'	=> array('lang' => 'ACL_M_WARN', 'cat' => 'misc'),
		'acl_m_ban'		=> array('lang' => 'ACL_M_BAN', 'cat' => 'misc'),

		// Admin Permissions
		'acl_a_board'		=> array('lang' => 'ACL_A_BOARD', 'cat' => 'settings'),
		'acl_a_server'		=> array('lang' => 'ACL_A_SERVER', 'cat' => 'settings'),
		'acl_a_jabber'		=> array('lang' => 'ACL_A_JABBER', 'cat' => 'settings'),
		'acl_a_phpinfo'		=> array('lang' => 'ACL_A_PHPINFO', 'cat' => 'settings'),

		'acl_a_forum'		=> array('lang' => 'ACL_A_FORUM', 'cat' => 'forums'),
		'acl_a_forumadd'	=> array('lang' => 'ACL_A_FORUMADD', 'cat' => 'forums'),
		'acl_a_forumdel'	=> array('lang' => 'ACL_A_FORUMDEL', 'cat' => 'forums'),
		'acl_a_prune'		=> array('lang' => 'ACL_A_PRUNE', 'cat' => 'forums'),

		'acl_a_icons'		=> array('lang' => 'ACL_A_ICONS', 'cat' => 'posting'),
		'acl_a_words'		=> array('lang' => 'ACL_A_WORDS', 'cat' => 'posting'),
		'acl_a_bbcode'		=> array('lang' => 'ACL_A_BBCODE', 'cat' => 'posting'),
		'acl_a_attach'		=> array('lang' => 'ACL_A_ATTACH', 'cat' => 'posting'),

		'acl_a_user'		=> array('lang' => 'ACL_A_USER', 'cat' => 'user_group'),
		'acl_a_userdel'		=> array('lang' => 'ACL_A_USERDEL', 'cat' => 'user_group'),
		'acl_a_group'		=> array('lang' => 'ACL_A_GROUP', 'cat' => 'user_group'),
		'acl_a_groupadd'	=> array('lang' => 'ACL_A_GROUPADD', 'cat' => 'user_group'),
		'acl_a_groupdel'	=> array('lang' => 'ACL_A_GROUPDEL', 'cat' => 'user_group'),
		'acl_a_ranks'		=> array('lang' => 'ACL_A_RANKS', 'cat' => 'user_group'),
		'acl_a_profile'		=> array('lang' => 'ACL_A_PROFILE', 'cat' => 'user_group'),
		'acl_a_names'		=> array('lang' => 'ACL_A_NAMES', 'cat' => 'user_group'),
		'acl_a_ban'			=> array('lang' => 'ACL_A_BAN', 'cat' => 'user_group'),

		'acl_a_viewauth'	=> array('lang' => 'ACL_A_VIEWAUTH', 'cat' => 'permissions'),
		'acl_a_authgroups'	=> array('lang' => 'ACL_A_AUTHGROUPS', 'cat' => 'permissions'),
		'acl_a_authusers'	=> array('lang' => 'ACL_A_AUTHUSERS', 'cat' => 'permissions'),
		'acl_a_fauth'		=> array('lang' => 'ACL_A_FAUTH', 'cat' => 'permissions'),
		'acl_a_mauth'		=> array('lang' => 'ACL_A_MAUTH', 'cat' => 'permissions'),
		'acl_a_aauth'		=> array('lang' => 'ACL_A_AAUTH', 'cat' => 'permissions'),
		'acl_a_uauth'		=> array('lang' => 'ACL_A_UAUTH', 'cat' => 'permissions'),
		'acl_a_roles'		=> array('lang' => 'ACL_A_ROLES', 'cat' => 'permissions'),
		'acl_a_switchperm'	=> array('lang' => 'ACL_A_SWITCHPERM', 'cat' => 'permissions'),

		'acl_a_styles'		=> array('lang' => 'ACL_A_STYLES', 'cat' => 'misc'),
		'acl_a_extensions'	=> array('lang' => 'ACL_A_EXTENSIONS', 'cat' => 'misc'),
		'acl_a_viewlogs'	=> array('lang' => 'ACL_A_VIEWLOGS', 'cat' => 'misc'),
		'acl_a_clearlogs'	=> array('lang' => 'ACL_A_CLEARLOGS', 'cat' => 'misc'),
		'acl_a_modules'		=> array('lang' => 'ACL_A_MODULES', 'cat' => 'misc'),
		'acl_a_language'	=> array('lang' => 'ACL_A_LANGUAGE', 'cat' => 'misc'),
		'acl_a_email'		=> array('lang' => 'ACL_A_EMAIL', 'cat' => 'misc'),
		'acl_a_bots'		=> array('lang' => 'ACL_A_BOTS', 'cat' => 'misc'),
		'acl_a_reasons'		=> array('lang' => 'ACL_A_REASONS', 'cat' => 'misc'),
		'acl_a_backup'		=> array('lang' => 'ACL_A_BACKUP', 'cat' => 'misc'),
		'acl_a_search'		=> array('lang' => 'ACL_A_SEARCH', 'cat' => 'misc'),
	);
}
