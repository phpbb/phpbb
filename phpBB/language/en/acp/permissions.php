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

	'DELETE_ROLE'				=> 'Delete role',
	'DELETE_ROLE_CONFIRM'		=> 'Are you sure you want to remove this role? Items having this role assigned will <strong>not</strong> loosing their permission settings.',
	'DISPLAY_FOR_GROUP'			=> 'Display for group',
	'DISPLAY_FOR_GROUP_EXPLAIN'	=> 'The role only shows up for the selected group(s). This is helpful if you set options only meant for some groups for example.',
	'DISPLAY_ROLE_ITEMS'		=> 'View Items using this role',

	'EDIT_ROLE'					=> 'Edit Role',
	'EVERY_USER_GROUP'			=> 'Every user/group',

	'GROUPS_NOT_ASSIGNED'		=> 'No group assigned to this role',

	'LOOK_UP_FORUMS_EXPLAIN'	=> 'You are able to select more than one forum',
	'LOOK_UP_GROUP'				=> 'Look up Usergroup',
	'LOOK_UP_USER'				=> 'Look up User',

	'MANAGE_GROUPS'		=> 'Manage Groups',
	'MANAGE_USERS'		=> 'Manage Users',

	'NO_AUTH_SETTING_FOUND'		=> 'Permission settings not defined.',
	'NO_ROLE_AVAILABLE'			=> 'No role available',
	'NO_ROLE_NAME_SPECIFIED'	=> 'Please give the role a name.',
	'NO_ROLE_SELECTED'			=> 'Role could not be found.',

	'ONLY_FORUM_DEFINED'	=> 'You only defined forums in your selection. Please also select at least one user or one group.',

	'PERMISSION_APPLIED_TO_ALL'		=> 'Permissions and Role will also be applied to all checked objects',
	'PLUS_SUBFORUMS'				=> '+Subforums',
	'PRE_ALL_CLOSED'				=> 'All closed groups',
	'PRE_ALL_FREE'					=> 'All freely open groups',
	'PRE_ALL_HIDDEN'				=> 'All hidden groups',
	'PRE_ALL_OPEN'					=> 'All open groups',
	'PRE_ALL_SPECIAL'				=> 'All special groups',
	'PRE_NOT_CLOSED'				=> 'All groups excluding closed groups',
	'PRE_NOT_FREE'					=> 'All groups excluding freely open groups',
	'PRE_NOT_HIDDEN'				=> 'All groups excluding hidden groups',
	'PRE_NOT_OPEN'					=> 'All groups excluding open groups',
	'PRE_NOT_SPECIAL'				=> 'All groups excluding special groups',
	'PRE_NOT_SPECIAL_GUEST'			=> 'All groups excluding special guest groups',
	'PRE_NOT_SPECIAL_REGISTERED'	=> 'All groups excluding special registered groups',
	'PRE_ONLY_SPECIAL_GUEST'		=> 'Only special guest groups',
	'PRE_ONLY_SPECIAL_REGISTERED'	=> 'Only special registered groups',

	'REMOVE_ROLE'					=> 'Remove Role',
	'ROLE'							=> 'Role',
	'ROLE_ADD_SUCCESS'				=> 'Role successfully added.',
	'ROLE_ASSIGNED_TO'				=> 'Users/Groups assigned to %s',
	'ROLE_DELETED'					=> 'Role successfully removed.',
	'ROLE_DESCRIPTION'				=> 'Role Description',
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

	'USERS_NOT_ASSIGNED'			=> 'No user assigned to this role',
	'USER_IS_MEMBER_OF_DEFAULT'		=> 'is a member of the following default groups',
	'USER_IS_MEMBER_OF_CUSTOM'		=> 'is a member of the following custom groups',

	'VIEW_ASSIGNED_ITEMS'	=> 'View assigned items',
	'VIEW_PERMISSIONS'		=> 'View permissions',

	'WRONG_PERMISSION_TYPE'	=> 'Wrong permission type selected',
));

?>