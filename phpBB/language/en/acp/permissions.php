<?php
/** 
*
* acp_permissions [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACL_NO'				=> 'No',
	'ACL_SET'				=> 'Setting Permissions',
	'ACL_SET_EXPLAIN'		=> 'Permissions are based on a simple YES/NO system. Setting an option to NO for a user or usergroup overrides any other value assigned to it. If you do not wish to assign a value for an option for this user or group select UNSET. If values are assigned for this option elsewhere they will be used in preference, else NO is assumed. All objects marked (with the checkbox in front of them) will inherit the permission set you defined.',
	'ACL_SETTING'			=> 'Setting',

	'ACL_TYPE_A_'			=> 'Administrative Permissions',
	'ACL_TYPE_F_'			=> 'Forum Permissions',
	'ACL_TYPE_M_'			=> 'Moderative Permissions',
	'ACL_TYPE_U_'			=> 'User Permissions',

	'ACL_TYPE_GLOBAL_A_'	=> 'Administrative Permissions',
	'ACL_TYPE_GLOBAL_U_'	=> 'User Permissions',
	'ACL_TYPE_GLOBAL_M_'	=> 'Global Moderator Permissions',
	'ACL_TYPE_LOCAL_M_'		=> 'Forum Moderator Permissions',
	'ACL_TYPE_LOCAL_F_'		=> 'Forum Permissions',
	
	'ACL_UNSET'				=> 'Unset',
	'ACL_VIEW'				=> 'Viewing Permissions',
	'ACL_VIEW_EXPLAIN'		=> 'Here you can see the effective permissions the user/group is having. A red square indicates that the user/group does not have the permission, a green square indicates that the user/group does have the permission.',
	'ACL_YES'				=> 'Yes',

	'ACP_ADMINISTRATORS_EXPLAIN'				=> 'Here you can assign administrator rights to users or groups. All users with admin permissions can view the administration panel.',
	'ACP_FORUM_MODERATORS_EXPLAIN'				=> 'Here you can assign users and groups as forum moderators. To assign users access to forums, to define global moderative rights or administrators please use the appropriate page.',
	'ACP_FORUM_PERMISSIONS_EXPLAIN'				=> 'Here you can alter which users and groups can access which forums. To assign moderators or define administrators please use the appropriate page.',
	'ACP_GLOBAL_MODERATORS_EXPLAIN'				=> 'Here you can assign global moderator rights to users or groups. These moderators are like ordinary moderators except they have access to every forum on your board.',
	'ACP_GROUPS_FORUM_PERMISSIONS_EXPLAIN'		=> 'Here you can assign forum permissions to groups.',
	'ACP_GROUPS_PERMISSIONS_EXPLAIN'			=> 'Here you can assign global permissions to groups - user permissions, global moderator permissions and admin permissions. User permissions include capabilities such as the use of avatars, sending private messages, etc. Global moderator permissions are blabla, administrative permissions blabla. Individual users permissions should only be changed in rare occassions, the preferred method is putting users in groups and assigning the groups permissions.',
	'ACP_ADMIN_ROLES_EXPLAIN'					=> 'Here you are able to manage the roles for administrative permissions. Roles are effective permissions, if you change a role the items having this role assigned will change it\'s permissions too.',
	'ACP_FORUM_ROLES_EXPLAIN'					=> 'Here you are able to manage the roles for forum permissions. Roles are effective permissions, if you change a role the items having this role assigned will change it\'s permissions too.',
	'ACP_MOD_ROLES_EXPLAIN'						=> 'Here you are able to manage the roles for moderative permissions. Roles are effective permissions, if you change a role the items having this role assigned will change it\'s permissions too.',
	'ACP_USER_ROLES_EXPLAIN'					=> 'Here you are able to manage the roles for user permissions. Roles are effective permissions, if you change a role the items having this role assigned will change it\'s permissions too.',
	'ACP_USERS_FORUM_PERMISSIONS_EXPLAIN'		=> 'Here you can assign forum permissions to users.',
	'ACP_USERS_PERMISSIONS_EXPLAIN'				=> 'Here you can assign global permissions to users - user permissions, global moderator permissions and admin permissions. User permissions include capabilities such as the use of avatars, sending private messages, etc. Global moderator permissions are blabla, administrative permissions blabla. To alter these settings for large numbers of users the Group permissions system is the prefered method. Users permissions should only be changed in rare occassions, the preferred method is putting users in groups and assigning the groups permissions.',
	'ACP_VIEW_ADMIN_PERMISSIONS_EXPLAIN'		=> 'Here you can view the effective administrative permissions assigned to the selected users/groups',
	'ACP_VIEW_GLOBAL_MOD_PERMISSIONS_EXPLAIN'	=> 'Here you can view the global moderative permissions assigned to the selected users/groups',
	'ACP_VIEW_FORUM_PERMISSIONS_EXPLAIN'		=> 'Here you can view the forum permissions assigned to the selected users/groups and forums',
	'ACP_VIEW_FORUM_MOD_PERMISSIONS_EXPLAIN'	=> 'Here you can view the forum moderator permissions assigned to the selected users/groups and forums',
	'ACP_VIEW_USER_PERMISSIONS_EXPLAIN'			=> 'Here you can view the effective user permissions assigned to the selected users/groups',

	'ADD_GROUPS'				=> 'Add Groups',
	'ADD_USERS'					=> 'Add Users',
	'ALL_GROUPS'				=> 'Select all groups',
	'ALL_NO'					=> 'All No',
	'ALL_UNSET'					=> 'All Unset',
	'ALL_USERS'					=> 'Select all users',
	'ALL_YES'					=> 'All Yes',
	'APPLY_ALL_PERMISSIONS'		=> 'Apply all Permissions',
	'APPLY_PERMISSIONS'			=> 'Apply Permissions',
	'APPLY_PERMISSIONS_EXPLAIN'	=> 'The Permissions and Role defined for this item will only be applied to this item and all checked items.',
	'AUTH_UPDATED'				=> 'Permissions have been updated',

	'CREATE_ROLE'				=> 'Create Role',
	'CREATE_ROLE_FROM'			=> 'Use settings from...',
	'CUSTOM'					=> 'Custom...',

	'DEFAULT'					=> 'Default',
	'DELETE_ROLE'				=> 'Delete role',
	'DELETE_ROLE_CONFIRM'		=> 'Are you sure you want to remove this role? Items having this role assigned will <strong>not</strong> loosing their permission settings.',
	'DISPLAY_ROLE_ITEMS'		=> 'View Items using this role',

	'EDIT_ROLE'					=> 'Edit Role',

	'GROUPS_NOT_ASSIGNED'		=> 'No group assigned to this role',

	'LOOK_UP_FORUMS_EXPLAIN'	=> 'You are able to select more than one forum',
	'LOOK_UP_GROUP'				=> 'Look up Usergroup',
	'LOOK_UP_USER'				=> 'Look up User',

	'MANAGE_GROUPS'		=> 'Manage Groups',
	'MANAGE_USERS'		=> 'Manage Users',

	'NO_AUTH_SETTING_FOUND'		=> 'Permission settings not defined.',
	'NO_ROLE_ASSIGNED'			=> 'No role assigned...',
	'NO_ROLE_AVAILABLE'			=> 'No role available',
	'NO_ROLE_NAME_SPECIFIED'	=> 'Please give the role a name.',
	'NO_ROLE_SELECTED'			=> 'Role could not be found.',

	'ONLY_FORUM_DEFINED'	=> 'You only defined forums in your selection. Please also select at least one user or one group.',

	'PERMISSION_APPLIED_TO_ALL'		=> 'Permissions and Role will also be applied to all checked objects',
	'PLUS_SUBFORUMS'				=> '+Subforums',

	'REMOVE_ROLE'					=> 'Remove Role',
	'ROLE'							=> 'Role',
	'ROLE_ADD_SUCCESS'				=> 'Role successfully added.',
	'ROLE_ASSIGNED_TO'				=> 'Users/Groups assigned to %s',
	'ROLE_DELETED'					=> 'Role successfully removed.',
	'ROLE_DESCRIPTION'				=> 'Role Description',

	'ROLE_DESCRIPTION_ADMIN_FORUM'		=> 'Can access the forum management and forum permission settings.',
	'ROLE_DESCRIPTION_ADMIN_FULL'		=> 'Has access to all admin functions of this forum.<br />Not recommended.',
	'ROLE_DESCRIPTION_ADMIN_STANDARD'	=> 'Has access to most admin features but is not allowed to use server or system related tools.',
	'ROLE_DESCRIPTION_ADMIN_USERGROUP'	=> 'Can manage groups and users: Can change permissions, settings, manage bans, and manage ranks.',
	'ROLE_DESCRIPTION_FORUM_BOT'		=> 'This role is recommended for bots and search spiders.',
	'ROLE_DESCRIPTION_FORUM_FULL'		=> 'Can use all forum features, including posting of announcements and stickies. Can also ignore the flood limit.<br />Not recommended for normal users.',
	'ROLE_DESCRIPTION_FORUM_LIMITED'	=> 'Can use some forum features, but cannot attach files or use post icons.',
	'ROLE_DESCRIPTION_FORUM_NOACCESS'	=> 'Can neither see nor access the forum.',
	'ROLE_DESCRIPTION_FORUM_ONQUEUE'	=> 'Can use most forum features including attachments, but posts and topics need to be approved by a moderator.',
	'ROLE_DESCRIPTION_FORUM_POLLS'		=> 'Like Standard Access but can also create polls.',
	'ROLE_DESCRIPTION_FORUM_READONLY'	=> 'Can read the forum, but cannot create new topics or reply to posts.',
	'ROLE_DESCRIPTION_FORUM_STANDARD'	=> 'Can use most forum features including attachments, but cannot lock or delete own topics, and cannot create polls.',
	'ROLE_DESCRIPTION_MOD_FULL'			=> 'Can use all moderating features, including banning.',
	'ROLE_DESCRIPTION_MOD_QUEUE'		=> 'Can use the Moderation Queue to validate and edit posts, but nothing else.',
	'ROLE_DESCRIPTION_MOD_SIMPLE'		=> 'Can only use basic topic actions. Cannot send warnings or use moderation queue.',
	'ROLE_DESCRIPTION_MOD_STANDARD'		=> 'Can use most moderating tools, but cannot ban users or change the post author.',
	'ROLE_DESCRIPTION_USER_FULL'		=> 'Can use all available forum features for users, including changing the user name or ignoring the flood limit.<br />Not recommended.',
	'ROLE_DESCRIPTION_USER_LIMITED'		=> 'Can access some of the user features. Attachments, e-mails, or instant messages are not allowed.',
	'ROLE_DESCRIPTION_USER_NOAVATAR'	=> 'Has a limited feature set and is not allowed to use the Avatar feature.',
	'ROLE_DESCRIPTION_USER_NOPM'		=> 'Has a limited feature set, and is not allowed to use Private Messages.',
	'ROLE_DESCRIPTION_USER_STANDARD'	=> 'Can access most but not all user features. Cannot change user name or ignore the flood limit, for instance.',
	
	'ROLE_DESCRIPTION_EXPLAIN'		=> 'You are able to enter a short explanation of what the role is doing or for what it is meant for. The text you enter here will be displayed within the permissions screens too.',
	'ROLE_DETAILS'					=> 'Role Details',
	'ROLE_EDIT_SUCCESS'				=> 'Role successfully edited.',
	'ROLE_NAME'						=> 'Role Name',
	'ROLE_NAME_ALREADY_EXIST'		=> 'A role named <strong>%s</strong> already exist for the specified permission type.',
	'ROLE_NOT_ASSIGNED'				=> 'Role has not been assigned yet.',

	'SELECTED_FORUM_NOT_EXIST'		=> 'The selected forum(s) do not exist',
	'SELECTED_GROUP_NOT_EXIST'		=> 'The selected group(s) do not exist',
	'SELECTED_USER_NOT_EXIST'		=> 'The selected user(s) do not exist',
	'SELECT_FORUM_SUBFORUM_EXPLAIN'	=> 'The forum you select here will include all subforums into the selection',
	'SELECT_ROLE'					=> 'Select role...',
	'SELECT_TYPE'					=> 'Select type',
	'SET_PERMISSIONS'				=> 'Set permissions',
	'SET_ROLE_PERMISSIONS'			=> 'Set role permissions',
	'SET_USERS_PERMISSIONS'			=> 'Set users permissions',
	'SET_USERS_FORUM_PERMISSIONS'	=> 'Set users forum permissions',

	'TRACE_DEFAULT'					=> 'By default every permission is UNSET. So the permission can be overwritten by other settings.',
	'TRACE_FOR'						=> 'Trace for',
	'TRACE_GLOBAL_SETTING'			=> '%s (global)',
	'TRACE_GROUP_NO_TOTAL_NO'		=> 'This group\'s permission is set to NO like the total result so the old result is kept.',
	'TRACE_GROUP_NO_TOTAL_UNSET'	=> 'This group\'s permission is set to NO which becomes the new total value because it wasn\'t set yet.',
	'TRACE_GROUP_NO_TOTAL_YES'		=> 'This group\'s permission is set to NO which overwrites the total YES to a NO for this user.',
	'TRACE_GROUP_UNSET'				=> 'The permission is UNSET for this group so the old total value is kept.',
	'TRACE_GROUP_YES_TOTAL_NO'		=> 'This group\'s permission is set to YES but the total NO cannot be overwritten.',
	'TRACE_GROUP_YES_TOTAL_UNSET'	=> 'This group\'s permission is set to YES which becomes the new total value because it wasn\'t set yet.',
	'TRACE_GROUP_YES_TOTAL_YES'		=> 'This group\'s permission is set to YES and the total permission is already set to YES, so the total result is kept.',
	'TRACE_PERMISSION'				=> 'Trace permission - %s',
	'TRACE_SETTING'					=> 'Trace setting',

	'TRACE_USER_GLOBAL_YES_TOTAL_YES'	=> 'The forum independent user permission evaluates to YES but the total permission is already set to YES, so the the total result is kept. %sTrace global permission%s',
	'TRACE_USER_GLOBAL_YES_TOTAL_NO'	=> 'The forum independent user permission evaluates to YES which overwrites the current local result NO. %sTrace global permission%s',
	'TRACE_USER_GLOBAL_NO_TOTAL_KEPT'	=> 'The forum independent user permission evaluates to NO which doesn\'t influence the local permission. %sTrace global permission%s',
	'TRACE_USER_FOUNDER'			=> 'The user has the founder type set, therefore admin permissions are set to YES by default.',
	'TRACE_USER_KEPT'				=> 'The user permission is UNSET so the old total value is kept.',
	'TRACE_USER_NO_TOTAL_NO'		=> 'The user permission is set to NO and the total value is set to NO, so nothing is changed.',
	'TRACE_USER_NO_TOTAL_UNSET'		=> 'The user permission is set to NO which becomes the total value because it wasn\'t set yet.',
	'TRACE_USER_NO_TOTAL_YES'		=> 'The user permission is set to NO and overwrites the previous YES.',
	'TRACE_USER_UNSET_TOTAL_UNSET'	=> 'The user permission is UNSET and the total value wasn\'t set yet so it defaults to NO.',
	'TRACE_USER_YES_TOTAL_NO'		=> 'The user permission is set to YES but the total NO cannot be overwritten.',
	'TRACE_USER_YES_TOTAL_UNSET'	=> 'The user permission is set to YES which becomes the total value because it wasn\'t set yet.',
	'TRACE_USER_YES_TOTAL_YES'		=> 'The user permission is set to YES and the total value is set to YES, so nothing is changed.',
	'TRACE_WHO'						=> 'Who',
	'TRACE_TOTAL'					=> 'Total',

	'USERS_NOT_ASSIGNED'			=> 'No user assigned to this role',
	'USER_IS_MEMBER_OF_DEFAULT'		=> 'is a member of the following default groups',
	'USER_IS_MEMBER_OF_CUSTOM'		=> 'is a member of the following custom groups',

	'VIEW_ASSIGNED_ITEMS'	=> 'View assigned items',
	'VIEW_PERMISSIONS'		=> 'View permissions',

	'WRONG_PERMISSION_TYPE'	=> 'Wrong permission type selected',
));

?>