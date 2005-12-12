<?php
/** 
*
* acp_forums [English]
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

// Forum Admin
$lang = array_merge($lang, array(
	'AUTO_PRUNE_DAYS'			=> 'Auto-prune Post Age',
	'AUTO_PRUNE_DAYS_EXPLAIN'	=> 'Number of days since last post after which topic is removed.',
	'AUTO_PRUNE_FREQ'			=> 'Auto-prune Frequency',
	'AUTO_PRUNE_FREQ_EXPLAIN'	=> 'Time in days between pruning events.',
	'AUTO_PRUNE_VIEWED'			=> 'Auto-prune Post Viewed Age',
	'AUTO_PRUNE_VIEWED_EXPLAIN'	=> 'Number of days since topic was viewed after which topic is removed.',

	'CREATE_FORUM'	=> 'Create new forum',

	'DECIDE_MOVE_DELETE_CONTENT'		=> 'Delete content or move to forum',
	'DEFAULT_STYLE'						=> 'Default Style',
	'DELETE_ALL_POSTS'					=> 'Delete posts',
	'DELETE_SUBFORUMS'					=> 'Delete subforums and posts',
	'DISPLAY_ACTIVE_TOPICS'				=> 'Enable active topics',
	'DISPLAY_ACTIVE_TOPICS_EXPLAIN'		=> 'If set to yes active topics in selected subforums will be displayed under this category.',

	'EDIT_FORUM'				=> 'Edit forum',
	'ENABLE_INDEXING'			=> 'Enable search indexing',
	'ENABLE_INDEXING_EXPLAIN'	=> 'If set to yes posts made to this forum will be indexed for searching.',
	'ENABLE_RECENT'				=> 'Display active topics',
	'ENABLE_RECENT_EXPLAIN'		=> 'If set to yes topics made to this forum will be shown in the active topics list.',
	'ENABLE_TOPIC_ICONS'		=> 'Enable Topic Icons',

	'FOLDER'							=> 'Folder',
	'FORUM_ADMIN'						=> 'Forum Administration',
	'FORUM_ADMIN_EXPLAIN'				=> 'In phpBB3 there are no categories, everything is forum based. Each forum can have an unlimited number of sub-forums and you can determine whether each may be posted to or not (i.e. whether it acts like an old category). Here you can add, edit, delete, lock, unlock individual forums as well as set certain additional controls. If your posts and topics have got out of sync you can also resynchronise a forum.',
	'FORUM_AUTO_PRUNE'					=> 'Enable Auto-Pruning',
	'FORUM_AUTO_PRUNE_EXPLAIN'			=> 'Prunes the forum of topics, set the frequency/age parameters below.',
	'FORUM_CREATED'						=> 'Forum created successfully.',
	'FORUM_DATA_NEGATIVE'				=> 'Pruning parameters cannot be negative.',
	'FORUM_DELETE'						=> 'Delete Forum',
	'FORUM_DELETE_EXPLAIN'				=> 'The form below will allow you to delete a forum and decide where you want to put all topics (or forums) it contained.',
	'FORUM_DELETED'						=> 'Forum successfully deleted',
	'FORUM_DESC'						=> 'Description',
	'FORUM_DESC_EXPLAIN'				=> 'Any markup entered here will displayed as is.',
	'FORUM_EDIT_EXPLAIN'				=> 'The form below will allow you to customise this forum. Please note that moderation and post count controls are set via forum permissions for each user or usergroup.',
	'FORUM_IMAGE'						=> 'Forum Image',
	'FORUM_IMAGE_EXPLAIN'				=> 'Location, relative to the phpBB root directory, of an image to associate with this forum.',
	'FORUM_LINK'						=> 'Forum Link',
	'FORUM_LINK_EXPLAIN'				=> 'Full URL to location clicking this forum will take the user.',
	'FORUM_LINK_TRACK'					=> 'Track Link Redirects',
	'FORUM_LINK_TRACK_EXPLAIN'			=> 'Records the number of times a forum link was clicked.',
	'FORUM_NAME'						=> 'Forum Name',
	'FORUM_NAME_EMPTY'					=> 'You must enter a name for this forum.',
	'FORUM_PARENT'						=> 'Parent Forum',
	'FORUM_PASSWORD'					=> 'Forum Password',
	'FORUM_PASSWORD_CONFIRM'			=> 'Confirm Forum Password',
	'FORUM_PASSWORD_CONFIRM_EXPLAIN'	=> 'Only needs to be set if a forum password is entered.',
	'FORUM_PASSWORD_EXPLAIN'			=> 'Defines a password for this forum, use the permission system in preference.',
	'FORUM_PASSWORD_MISMATCH'			=> 'The passwords you entered did not match.',
	'FORUM_RESYNCED'					=> 'Forum "%s" successfully resynced',
	'FORUM_RULES'						=> 'Forum Rules',
	'FORUM_RULES_EXPLAIN'				=> 'Forum Rules are displayed at any page within the given forum.',
	'FORUM_RULES_LINK'					=> 'Link to Forum Rules',
	'FORUM_RULES_LINK_EXPLAIN'			=> 'You are able to enter the URL of the page/post containing your forum rules here. This setting will override the Forum Rules text you specified.',
	'FORUM_RULES_PREVIEW'				=> 'Forum Rules preview',
	'FORUM_SETTINGS'					=> 'Forum Settings',
	'FORUM_STATUS'						=> 'Forum Status',
	'FORUM_STYLE'						=> 'Forum Style',
	'FORUM_TOPICS_PAGE'					=> 'Topics Per Page',
	'FORUM_TOPICS_PAGE_EXPLAIN'			=> 'If non-zero this value will override the default topics per page setting.',
	'FORUM_TYPE'						=> 'Forum Type',
	'FORUM_UPDATED'						=> 'Forum informations updated successfully.',

	'GENERAL_FORUM_SETTINGS'	=> 'General Forum Settings',

	'LINK'					=> 'Link',
	'LIST_INDEX'			=> 'List Forum On Index',
	'LIST_INDEX_EXPLAIN'	=> 'Displays a link to this forum under the root parent forum on the index.',
	'LOCKED'				=> 'Locked',

	'MOVE_POSTS_TO'		=> 'Move posts',
	'MOVE_SUBFORUMS_TO'	=> 'Move subforums',

	'NO_DESTINATION_FORUM'			=> 'You have not specified a forum to move content to',
	'NO_FORUM_ACTION'				=> 'No action defined for what happens with the forum content',
	'NO_PARENT'						=> 'No Parent',
	'NO_PERMISSION_FORUM_ADD'		=> 'You do not have the neccessary permissions to delete forums',
	'NO_PERMISSION_FORUM_DELETE'	=> 'You do not have the neccessary permissions to add forums',

	'PARENT_NOT_EXIST'			=> 'Parent does not exist',
	'PARSE_BBCODE'				=> 'Parse BBCode',
	'PARSE_SMILIES'				=> 'Parse Smilies',
	'PARSE_URLS'				=> 'Parse Links',
	'PRUNE_ANNOUNCEMENTS'		=> 'Prune Announcements',
	'PRUNE_STICKY'				=> 'Prune Stickies',
	'PRUNE_OLD_POLLS'			=> 'Prune Old Polls',
	'PRUNE_OLD_POLLS_EXPLAIN'	=> 'Removes topics with polls not voted in for post age days.',
	
	'REDIRECT_ACL'	=> 'Now you are able to %sset permissions%s for this forum.',
	'RESYNC'		=> 'Sync',

	'SUBFORUM'		=> 'Subforum',

	'TYPE_CAT'			=> 'Category',
	'TYPE_FORUM'		=> 'Forum',
	'TYPE_LINK'			=> 'Link',

	'UNLOCKED'			=> 'Unlocked',

/*
	'REMOVE'		=> 'Remove',
	'EDIT'			=> 'Edit',
	'MOVE_UP'		=> 'Move up',
	'MOVE_DOWN'		=> 'Move down',
	'UPDATE'		=> 'Update',


	'ENABLE_NEWS'		=> 'Set as news forum',
	'ENABLE_NEWS_EXPLAIN' => 'If set to yes posts in this forum will be displayed as news items.',
	'PRUNE_FINISHED_POLLS'		=> 'Prune Closed Polls',
	'PRUNE_FINISHED_POLLS_EXPLAIN'=> 'Removes topics with polls which have ended.',
	'ACTIVE_TOPICS_PAGE'			=> 'Number of active topics',
	'ACTIVE_TOPICS_PAGE_EXPLAIN'	=> 'If non-zero this value will override the default topics per page setting.',
*/
));

?>