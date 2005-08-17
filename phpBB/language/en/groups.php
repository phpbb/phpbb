<?php
/** 
*
* groups [English]
*
* @package phpBB3
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
	'ALREADY_DEFAULT_GROUP'	=> 'The selected group is already your default group',
	'ALREADY_IN_GROUP'		=> 'You are already a member of the selected group',

	'CHANGED_DEFAULT_GROUP'	=> 'Successfully changed default group',
	
	'GROUP_AVATAR'		=> 'Group avatar', 
	'GROUP_CHANGE_DEFAULT'	=> 'Are you sure you want to change your default membership to the group "%s"?',
	'GROUP_CLOSED'		=> 'Closed',
	'GROUP_DESC'		=> 'Group description',
	'GROUP_HIDDEN'		=> 'Hidden',
	'GROUP_INFORMATION'	=> 'Usergroup Information', 
	'GROUP_MEMBERS'		=> 'Group members',
	'GROUP_NAME'		=> 'Group name',
	'GROUP_OPEN'		=> 'Open',
	'GROUP_RANK'		=> 'Group rank', 
	'GROUP_TYPE'		=> 'Group type',
	'GROUP_IS_CLOSED'	=> 'This is a closed group, new members cannot automatically join.',
	'GROUP_IS_OPEN'		=> 'This is an open group, members can apply to join.',
	'GROUP_IS_HIDDEN'	=> 'This is a hidden group, only members of this group can view its membership.',
	'GROUP_IS_FREE'		=> 'This is a freely open group, all new members are welcome.', 
	'GROUP_IS_SPECIAL'	=> 'This is a special group, special groups are managed by the board administrators.', 

	'LOG_USER_GROUP_CHANGE'	=> '<b>User changed default group</b><br />&#187; %s',
	'LOGIN_EXPLAIN_GROUP'	=> 'You need to login to view group details',
	
	'NOT_MEMBER_OF_GROUP'	=> 'The requested operation cannot be taken because you are not a member of the selected group',

	'PRIMARY_GROUP'		=> 'Primary group',

	'REMOVE_SELECTED'		=> 'Remove selected',

	'USER_GROUP_CHANGE'		=> 'From "%1$s" group to "%2$s"',
);

?>