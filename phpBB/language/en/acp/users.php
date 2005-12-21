<?php
/** 
*
* acp_users [English]
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
	'ADMIN_SIG_PREVIEW'		=> 'Signature preview',
	
	'BAN_SUCCESSFULL'		=> 'Ban entered successfully',

	'CONFIRM_EMAIL_EXPLAIN'	=> 'You only need to specify this if you are changing the users email address.',
	
	'DELETE_POSTS'			=> 'Delete posts',
	'DELETE_USER'			=> 'Delete user',
	'DELETE_USER_EXPLAIN'	=> 'Please note that deleting a user is final, they cannot be recovered',

	'FORCE_REACTIVATION_SUCCESS'	=> 'Successfully forced re-activation',
	'FOUNDER'						=> 'Founder',
	'FOUNDER_EXPLAIN'				=> 'Founders can never be banned, deleted or altered by non-founder members',

	'IP_WHOIS_FOR'			=> 'IP whois for %s',

	'LAST_ACTIVE'			=> 'Last active',

	'MOVE_POSTS_EXPLAIN'	=> 'Please select the forum to which you wish to move all the posts this user has made.',

	'QUICK_TOOLS'			=> 'Quick tools',

	'REGISTERED'			=> 'Registered',
	'REGISTERED_IP'			=> 'Registered from IP',
	'RETAIN_POSTS'			=> 'Retain posts',

	'SELECT_FORM'			=> 'Select form',
	'SELECT_USER'			=> 'Select User',

	'USER_ADMIN'					=> 'User Administration',
	'USER_ADMIN_ACTIVATE'			=> 'Activate account',
	'USER_ADMIN_ACTIVATED'			=> 'User activated successfully',
	'USER_ADMIN_AVATAR_REMOVED'		=> 'Successfully removed avatar from user account',
	'USER_ADMIN_BAN_EMAIL'			=> 'Ban by email',
	'USER_ADMIN_BAN_EMAIL_REASON'	=> 'Email address banned via user management',
	'USER_ADMIN_BAN_IP'				=> 'Ban by IP',
	'USER_ADMIN_BAN_IP_REASON'		=> 'IP banned via user management',
	'USER_ADMIN_BAN_NAME_REASON'	=> 'Username banned via user management',
	'USER_ADMIN_BAN_USER'			=> 'Ban by username',
	'USER_ADMIN_DEACTIVATE'			=> 'Deactivate account',
	'USER_ADMIN_DEACTIVED'			=> 'User deactivated successfully',
	'USER_ADMIN_DEL_ATTACH'			=> 'Delete all attachments',
	'USER_ADMIN_DEL_AVATAR'			=> 'Delete avatar',
	'USER_ADMIN_DEL_POSTS'			=> 'Delete all posts',
	'USER_ADMIN_DEL_SIG'			=> 'Delete signature',
	'USER_ADMIN_EXPLAIN'			=> 'Here you can change your users information and certain specific options. To modify the users permissions please use the user and group permissions system.',
	'USER_ADMIN_FORCE'				=> 'Force re-activation',
	'USER_ADMIN_MOVE_POSTS'			=> 'Move all posts',
	'USER_ADMIN_SIG_REMOVED'		=> 'Successfully removed signature from user account',
	'USER_ATTACHMENTS_REMOVED'		=> 'Successfully removed all attachments made by this user',
	'USER_AVATAR_UPDATED'			=> 'Successfully updated user avatars details',
	'USER_CUSTOM_PROFILE_FIELDS'	=> 'Custom profile fields',
	'USER_DELETED'					=> 'User deleted successfully',
	'USER_OVERVIEW_UPDATED'			=> 'User details updated',
	'USER_POSTS_DELETED'			=> 'Successfully removed all posts made by this user',
	'USER_POSTS_MOVED'				=> 'Successfully moved users posts to target forum',
	'USER_PREFS_UPDATED'			=> 'User preferences updated',
	'USER_PROFILE'					=> 'User Profile',
	'USER_PROFILE_UPDATED'			=> 'User profile updated',
	'USER_SIG_UPDATED'				=> 'User signature successfully updated',
	'USER_TOOLS'					=> 'Basic tools',

	'WARNINGS'				=> 'Warnings',
	'WARNINGS_EXPLAIN'		=> 'You can directly alter the warnings this users has received.',
));

?>