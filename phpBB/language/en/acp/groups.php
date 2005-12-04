<?php
/** 
*
* acp_groups [English]
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

$lang += array(
	'ACP_GROUPS_MANAGE_EXPLAIN'		=> 'From this panel you can administer all your usergroups, you can delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description.',
	'ADD_USERS'						=> 'Add Users',
	'ADD_USERS_EXPLAIN'				=> 'Here you can add new users to the group. You may select whether this group becomes the new default for the selected users. Additionally you can define them as group leaders. Please enter each username on a seperate line.',

	'CREATE_GROUP'			=> 'Create new group',

	'GROUPS_NO_MEMBERS'				=> 'This group has no members',
	'GROUPS_NO_MODS'				=> 'No group leaders defined',
	'GROUP_APPROVE'					=> 'Approve',
	'GROUP_APPROVED'				=> 'Approved Members',
	'GROUP_AVATAR'					=> 'Group avatar',
	'GROUP_AVATAR_EXPLAIN'			=> 'This image will be displayed in the Group Control Panel.',
	'GROUP_CLOSED'					=> 'Closed',
	'GROUP_COLOR'					=> 'Group colour',
	'GROUP_COLOR_EXPLAIN'			=> 'Defines the colour members usernames will appear in, leave blank for user default.',
	'GROUP_CREATED'					=> 'Group has been created successfully',
	'GROUP_DEFAULT'					=> 'Default',
	'GROUP_DEFS_UPDATED'			=> 'Default group set for all members',
	'GROUP_DELETE'					=> 'Delete',
	'GROUP_DELETED'					=> 'Group deleted and user default groups set successfully',
	'GROUP_DEMOTE'					=> 'Demote',
	'GROUP_DESC'					=> 'Group description',
	'GROUP_DETAILS'					=> 'Group details',
	'GROUP_DST'						=> 'Group daylight savings',
	'GROUP_EDIT_EXPLAIN'			=> 'Here you can edit an existing group. You can change its name, description and type (open, closed, etc.). You can also set certain groupwide options such as colouration, rank, etc. Changes made here override users current settings. Please note that group members can alter their avatar unless you set appropriate user permissions.',
	'GROUP_ERR_DESC_LONG'			=> 'Group description too long.',
	'GROUP_ERR_TYPE'				=> 'Inappropriate group type specified.',
	'GROUP_ERR_USERNAME'			=> 'No group name specified.',
	'GROUP_ERR_USERS_EXIST'			=> 'The specified users are already members of this group',
	'GROUP_ERR_USER_LONG'			=> 'Group name too long.',
	'GROUP_HIDDEN'					=> 'Hidden',
	'GROUP_LANG'					=> 'Group language',
	'GROUP_LEAD'					=> 'Group leaders',
	'GROUP_LIST'					=> 'Current members',
	'GROUP_LIST_EXPLAIN'			=> 'This is a complete list of all the current users with membership of this group. You can delete members (except in certain special groups) or add new ones as you see fit.',
	'GROUP_MEMBERS'					=> 'Group members',
	'GROUP_MEMBERS_EXPLAIN'			=> 'This is a complete listing of all the members of this usergroup. It includes seperate sections for leaders, pending and existing members. From here you can manage all aspects of who has membership of this group and what their role is. To remove a leader but keep them in the group use Demote rather than delete. Similarly use Promote to make an existing member a leader.',
	'GROUP_MESSAGE_LIMIT'			=> 'Group private message limit per folder',
	'GROUP_MESSAGE_LIMIT_EXPLAIN'	=> 'This setting overrides the per-user folder message limit. A value of 0 means the user default limit will be used.',
	'GROUP_MODS_ADDED'				=> 'New group moderators added successfully.',
	'GROUP_MODS_DEMOTED'			=> 'Group leaders demoted successfully',
	'GROUP_MODS_PROMOTED'			=> 'Group members promoted successfully',
	'GROUP_NAME'					=> 'Group name',
	'GROUP_OPEN'					=> 'Open',
	'GROUP_PENDING'					=> 'Pending Members',
	'GROUP_PROMOTE'					=> 'Promote',
	'GROUP_RANK'					=> 'Group rank',
	'GROUP_RECEIVE_PM'				=> 'Group able to receive private messages',
	'GROUP_REQUEST'					=> 'Request',
	'GROUP_SETTINGS'				=> 'Set user preferences',
	'GROUP_SETTINGS_EXPLAIN'		=> 'Here you can force changes in users current preferences. Please note these settings are not saved for the group itself. They are intended as a quick method of altering the preferences of all users in this group.',
	'GROUP_SETTINGS_SAVE'			=> 'Groupwide settings',
	'GROUP_TIMEZONE'				=> 'Group timezone',
	'GROUP_TYPE'					=> 'Group type',
	'GROUP_TYPE_EXPLAIN'			=> 'This determines which users can join or view this group.',
	'GROUP_UPDATED'					=> 'Group preferences updated successfully.',
	'GROUP_USERS_ADDED'				=> 'New users added to group successfully.',
	'GROUP_USERS_EXIST'				=> 'The selected users are already members.',
	'GROUP_USERS_REMOVE'			=> 'Users removed from group and new defaults set successfully',

	'NO_GROUP'					=> 'No group specified',
	'NO_USERS'					=> 'The requested users do not exist',

	'SPECIAL_GROUPS'			=> 'Predefined groups',
	'SPECIAL_GROUPS_EXPLAIN'	=> 'Pre-defined groups are special groups, they cannot be deleted or directly modified. However you can still add users and alter basic settings. By clicking "Default" you can set the relevant group to the default for all its members.',

	'TOTAL_MEMBERS'				=> 'Members',

	'USERS_APPROVED'				=> 'Users approved successfully.',
	'USER_DEFAULT'					=> 'User default',
	'USER_DEF_GROUPS'				=> 'User defined groups',
	'USER_DEF_GROUPS_EXPLAIN'		=> 'These are groups created by you or another admin on this board. You can manage memberships as well as edit group properties or even delete the group. By clicking "Default" you can set the relevant group to the default for all its members.',
	'USER_GROUP_DEFAULT'			=> 'Set as default group',
	'USER_GROUP_DEFAULT_EXPLAIN'	=> 'Saying yes here will set this group as the default group for the added users',
	'USER_GROUP_LEADER'				=> 'Set as group leader',
);

//	'FORCE_COLOR'			=> 'Force update',

?>