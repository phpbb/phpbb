<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp.php [ English ]
// STARTED   : Sat Dec 16, 2000
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// DO NOT CHANGE
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
	'ACTION'				=> 'Action',
	'ADD_FEEDBACK'			=> 'Add feedback', 
	'ADD_FEEDBACK_EXPLAIN'	=> 'If you would like to add a report on this please fill out the following form. Only use plain text, HTML, BBCode, etc. are not permitted.', 
	'ALL_ENTRIES'			=> 'All entries',
	'ALREADY_REPORTED'		=> 'This post has already been reported',
	'APPROVE'				=> 'Approve',
	'APPROVE_POST'			=> 'Approve Post',
	'APPROVE_POST_CONFIRM'	=> 'Are you sure you want to approve this post?',
	'APPROVE_POSTS'			=> 'Approve Posts',
	'APPROVE_POSTS_CONFIRM'	=> 'Are you sure you want to approve the selected posts?',

	'CANNOT_MOVE_SAME_FORUM'=> 'You cannot move a topic to the forum it\'s already in',
	'CAN_LEAVE_BLANK'		=> 'This can be left blank.',
	'CHANGE_POSTER'			=> 'Change poster',

	'DELETE_POSTS'			=> 'Delete posts',
	'DELETE_POSTS_CONFIRM'	=> 'Are you sure you want to delete these posts?',
	'DELETE_POST_CONFIRM'	=> 'Are you sure you want to delete this post?',
	'DELETE_TOPICS'			=> 'Delete selected topics',
	'DELETE_TOPICS_CONFIRM'	=> 'Are you sure you want to delete these topics?',
	'DELETE_TOPIC_CONFIRM'	=> 'Are you sure you want to delete this topic?',
	'DISAPPROVE'			=> 'Disapprove',
	'DISPLAY_LOG'			=> 'Display entries from previous',
	'DISPLAY_OPTIONS'		=> 'Display options',

	'EMPTY_REPORT'			=> 'You must enter a description when selecting this reason',
	'EMPTY_TOPICS_REMOVED_WARNING'	=> 'Please note that one or several topics have been removed from the database because they were or become empty',

	'FEEDBACK'				=> 'Feedback',
	'FORK'					=> 'Fork',
	'FORK_TOPIC'			=> 'Fork Topic',
	'FORK_TOPIC_CONFIRM'	=> 'Are you sure you want to copy this topic?',
	'FORK_TOPICS'			=> 'Fork selected topics',
	'FORK_TOPICS_CONFIRM'	=> 'Are you sure you want to copy the selected topics?',
	'FORUM_DESC'			=> 'Description',
	'FORUM_NAME'			=> 'Forum Name',
	'FORUM_NOT_EXIST'		=> 'The forum you selected does not exist',
	'FORUM_NOT_POSTABLE'	=> 'The forum you selected cannot be posted to',
	'FORUM_STATUS'			=> 'Forum Status',
	'FORUM_STYLE'			=> 'Forum Style',

	'GLOBAL_ANNOUNCEMENT'	=> 'Global Announcement',
	
	'IP_INFO'				=> 'IP Information',

	'LATEST_LOGS'			=> 'Latest 5 logged actions',
	'LATEST_REPORTED'		=> 'Latest 5 reports',
	'LATEST_UNAPPROVED'		=> 'Latest 5 posts awaiting for approval',
	'LEAVE_SHADOW'			=> 'Leave shadow topic in place',
	'LOCK'					=> 'Lock',
	'LOCK_POST_POST'		=> 'Lock Post',
	'LOCK_POST_POST_CONFIRM'=> 'Are you sure you want to prevent editing this post?',
	'LOCK_POST_POSTS'		=> 'Lock selected posts',
	'LOCK_POST_POSTS_CONFIRM'=> 'Are you sure you want to prevent editing the selected posts?',
	'LOCK_TOPIC_CONFIRM'	=> 'Are you sure you want to lock this topic?',
	'LOCK_TOPICS'			=> 'Lock selected topics',
	'LOCK_TOPICS_CONFIRM'	=> 'Are you sure you want to lock all selected topics?',
	'LOGS_CURRENT_TOPIC'	=> 'Currently viewing logs of:',
	'LOG_APPROVE_TOPIC'		=> '<b>Approved topic</b><br />&#187; %s',
	'LOG_FORK'				=> '<b>Copied topic</b><br />&#187; from %s',
	'LOG_LOCK'				=> '<b>Locked topic</b><br />&#187; %s',
	'LOG_LOCK_POST'			=> '<b>Locked post</b><br />&#187; %s',
	'LOG_MERGE'				=> '<b>Merged posts</b> into topic<br />&#187;%s',
	'LOG_MOVE'				=> '<b>Moved topic</b><br />&#187; from %s',
	'LOG_TOPIC_DELETED'		=> '<b>Deleted topic</b><br />&#187; %s',
	'LOG_TOPIC_RESYNC'		=> '<b>Resynchronised topic counters</b><br />&#187; %s',
	'LOG_TOPIC_TYPE_CHANGED'=> '<b>Changed topic type</b><br />&#187; %s',
	'LOG_UNLOCK'			=> '<b>Unlocked topic</b><br />&#187; %s',
	'LOG_UNLOCK_POST'		=> '<b>Unlocked post</b><br />&#187; %s',
	'LOG_UNRATE'			=> '<b>Unrated post</b><br />&#187; %s',
	'LOGVIEW_VIEWTOPIC'		=> 'View Topic',
	'LOGVIEW_VIEWLOGS'		=> 'View Topic Log',
	'LOGVIEW_VIEWFORUM'		=> 'View Forum',
	'LOOKUP_ALL'			=> 'Look up all IP',
	'LOOKUP_IP'				=> 'Look up IP',

	'MCP_ADD'						=> 'Add a warning',
	'MCP_MAIN'						=> 'Main',
	'MCP_MAIN_FORUM_VIEW'			=> 'View Forum',
	'MCP_MAIN_FRONT'				=> 'Front Page',
	'MCP_MAIN_POST_DETAILS'			=> 'Post Details',
	'MCP_MAIN_TOPIC_VIEW'			=> 'View Topic',
	'MCP_MAKE_ANNOUNCEMENT'			=> 'Make Announcement',
	'MCP_MAKE_ANNOUNCEMENT_CONFIRM'	=> 'Are you sure you want to change this topic to an announcement?',
	'MCP_MAKE_ANNOUNCEMENTS'		=> 'Make Announcements',
	'MCP_MAKE_ANNOUNCEMENTS_CONFIRM'=> 'Are you sure you want to change the selected topics to announcements?',
	'MCP_MAKE_GLOBAL'				=> 'Make Global Announcement',
	'MCP_MAKE_GLOBAL_CONFIRM'		=> 'Are you sure you want to change this topic to an global announcement?',
	'MCP_MAKE_GLOBALS'				=> 'Make Global Announcements',
	'MCP_MAKE_GLOBALS_CONFIRM'		=> 'Are you sure you want to change the selected topics to global announcements?',
	'MCP_MAKE_STICKY'				=> 'Make Sticky',
	'MCP_MAKE_STICKY_CONFIRM'		=> 'Are you sure you want to stick this topic?',
	'MCP_MAKE_STICKIES'				=> 'Make Stickies',
	'MCP_MAKE_STICKIES_CONFIRM'		=> 'Are you sure you want to stick the selected topics?',
	'MCP_MAKE_NORMAL'				=> 'Make Standard Topic',
	'MCP_MAKE_NORMAL_CONFIRM'		=> 'Are you sure you want to revert this topic?',
	'MCP_MAKE_NORMALS'				=> 'Make Standard Topics',
	'MCP_MAKE_NORMALS_CONFIRM'		=> 'Are you sure you want to revert the selected topics?',

	'MCP_QUEUE'				=> 'Moderation Queue',
	'MCP_QUEUE_REPORTS'		=> 'Reports',
	'MCP_QUEUE_UNAPPROVED_POSTS'	=> 'Posts awaiting for approval',
	'MCP_QUEUE_UNAPPROVED_TOPICS'	=> 'Topics awaiting for approval',
	'MCP_VIEW_ALL'			=> 'View all (%s)',
	'MCP_VIEW_LOGS'			=> 'View logs',
	'MCP_VIEW_RECENT'		=> 'View recent (%s)',
	'MCP_VIEW_USER'			=> 'View warnings for a specific user',
	'MCP_WARNINGS'			=> 'Warnings',
	'MERGE_POSTS'			=> 'Merge posts',
	'MERGE_POSTS_CONFIRM'	=> 'Are you sure you want to merge the selected posts?',
	'MERGE_TOPIC_EXPLAIN'	=> 'Using the form below you can merge selected posts into another topic. These posts will not be reordered and will appear as if the users posted them to the new topic.<br />Please enter the destination topic id or click on the "Select" button to search for one',
	'MERGE_TOPIC_ID'		=> 'Destination topic id',
	'MESSAGE_REPORTED_SUCCESS'	=> 'This message has been successfully reported',
	'MOD_OPTIONS'			=> 'Moderator Options',
	'MORE_INFO'				=> 'Further information',
	'MOVE_TOPIC_CONFIRM'	=> 'Are you sure you want to move the topic into a new forum?',
	'MOVE_TOPICS'			=> 'Move selected topics',
	'MOVE_TOPICS_CONFIRM'	=> 'Are you sure you want to move the selected topics into a new forum?',

	'NOTIFY_POSTER_APPROVAL'=> 'Notify poster about approval?',
	'NOTIFY_POSTER_DISAPPROVAL' => 'Notify poster about disapproval?',
	'NOT_MODERATOR'			=> 'You are not a moderator of this forum',
	'NO_DESTINATION_FORUM'	=> 'Please select a forum for destination',
	'NO_ENTRIES'			=> 'No log entries for this period',
	'NO_FINAL_TOPIC_SELECTED'	=> 'You have to select a destination topic for merging posts',
	'NO_MATCHES_FOUND'		=> 'No matches found',
	'NO_POST_SELECTED'		=> 'You must select at least one post to perform this action',
	'NO_TOPIC_SELECTED'		=> 'You must select at least one topic to perform this action',

	'OTHER_IPS'				=> 'Other IP addresses this user has posted from',
	'OTHER_USERS'			=> 'Users posting from this IP',

	'POSTER'				=> 'Poster',
	'POSTS_APPROVED_SUCCESS'=> 'The selected posts have been approved',
	'POSTS_DELETED_SUCCESS'	=> 'The selected posts have been successfully removed from the database',
	'POSTS_LOCKED_SUCCESS'	=> 'The selected posts have been locked successfully',
	'POSTS_MERGED_SUCCESS'	=> 'The selected posts have been merged',
	'POSTS_UNLOCKED_SUCCESS'=> 'The selected posts have been unlocked successfully',
	'POSTS_PER_PAGE'		=> 'Posts per page',
	'POSTS_PER_PAGE_EXPLAIN'=> '(Set to 0 to view all posts)',
	'POST_APPROVED_SUCCESS'	=> 'The selected post has been approved',
	'POST_DELETED_SUCCESS'	=> 'The selected post has been successfully removed from the database',
	'POST_DETAILS'			=> 'Post details',
	'POST_LOCKED_SUCCESS'	=> 'Post locked successsfully',
	'POST_NOT_EXIST'		=> 'The post you requested does not exist',
	'POST_REPORTED_SUCCESS'	=> 'This post has been successfully reported',
	'POST_UNLOCKED_SUCCESS'	=> 'Post unlocked successsfully',
	'POST_UNRATED_SUCCESS'	=> 'Post unrated successfully',

	'READ_USERNOTES'		=> 'User notes',
	'READ_WARNINGS'			=> 'User warnings',
	'REPORTER'				=> 'Reporter',
	'REPORT_TIME'			=> 'Report time',
	'REPORTS_TOTAL'			=> 'In total there are <b>%d</b> reports to review',
	'REPORTS_ZERO_TOTAL'	=> 'There are no reports to review',
	'REPORT_MESSAGE'		=> 'Report this message',
	'REPORT_MESSAGE_EXPLAIN'=> 'Use this form to report the selected message to the board administrators. Reporting should generally be used only if the message breaks forum rules.',
	'REPORT_NOTIFY'			=> 'Notify me',
	'REPORT_NOTIFY_EXPLAIN'	=> 'Informs you when your report is dealt with',
	'REPORT_POST'			=> 'Report this post',
	'REPORT_POST_EXPLAIN'	=> 'Use this form to report the selected post to the forum moderators and board administrators. Reporting should generally be used only if the post breaks forum rules.',
	'REPORT_TOTAL'			=> 'In total there is <b>1</b> report to review',
	'RESYNC'				=> 'Resync',
	'RETURN_MESSAGE'		=> 'Click %sHere%s to return to the message',
	'RETURN_NEW_FORUM'		=> 'Click %sHere%s to return to the new forum',
	'RETURN_NEW_TOPIC'		=> 'Click %sHere%s to return to the new topic',

	'SELECT_ACTION'			=> 'Select desired action',
	'SELECT_TOPIC'			=> 'Select topic',
	'SORT_ACTION'			=> 'Log action',
	'SORT_DATE'				=> 'Date',
	'SORT_IP'				=> 'IP address',
	'SPLIT_AFTER'			=> 'Split from selected post',
	'SPLIT_FORUM'			=> 'Forum for new topic',
	'SPLIT_POSTS'			=> 'Split selected posts',
	'SPLIT_SUBJECT'			=> 'New topic title',
	'SPLIT_TOPIC_ALL'		=> 'Split topic from selected posts',
	'SPLIT_TOPIC_ALL_CONFIRM'	=> 'Are you sure you want to split this topic?',
	'SPLIT_TOPIC_BEYOND'	=> 'Split topic at selected post',
	'SPLIT_TOPIC_BEYOND_CONFIRM'	=> 'Are you sure you want to split this topic at the selected post?',
	'SPLIT_TOPIC_EXPLAIN'	=> 'Using the form below you can split a topic in two, either by selecting the posts individually or by splitting at a selected post',

	'THIS_POST_IP'			=> 'IP for this post',
	'TOPICS_APPROVED_SUCCESS'	=> 'The selected topics have been approved',
	'TOPICS_DELETED_SUCCESS'=> 'The selected topics have been successfully removed from the database',
	'TOPICS_FORKED_SUCCESS'	=> 'The selected topics have been copied successfully',
	'TOPICS_LOCKED_SUCCESS'	=> 'The selected topics have been locked',
	'TOPICS_MOVED_SUCCESS'	=> 'The selected topics have been moved successfully',
	'TOPICS_RESYNC_SUCCESS'	=> 'The selected topics have been resynchronised',
	'TOPICS_UNLOCKED_SUCCESS'	=> 'The selected topics have been unlocked',
	'TOPIC_APPROVED_SUCCESS'=> 'The selected topic has been approved',
	'TOPIC_DELETED_SUCCESS'	=> 'The selected topic has been successfully removed from the database',
	'TOPIC_FORKED_SUCCESS'	=> 'The selected topic has been copied successfully',
	'TOPIC_LOCKED_SUCCESS'	=> 'The selected topic has been locked',
	'TOPIC_MOVED_SUCCESS'	=> 'The selected topic has been moved successfully',
	'TOPIC_NOT_EXIST'		=> 'The topic you selected does not exist',
	'TOPIC_REPORTED'		=> 'This topic has been reported',
	'TOPIC_RESYNC_SUCCESS'	=> 'The selected topic has been resynchronised',
	'TOPIC_SPLIT_SUCCESS'	=> 'The selected topic has been split successfully',
	'TOPIC_TIME'			=> 'Topic time',
	'TOPIC_TYPE_CHANGED'	=> 'Topic type changed successfully.',
	'TOPIC_UNLOCKED_SUCCESS'=> 'The selected topic has been unlocked',

	'UNAPPROVED_POSTS_TOTAL'=> 'In total there are <b>%d</b> posts waiting for approval',
	'UNAPPROVED_POSTS_ZERO_TOTAL'	=> 'There are no posts waiting for approval',
	'UNAPPROVED_POST_TOTAL'	=> 'In total there is <b>1</b> post waiting for approval',
	'UNLOCK'				=> 'Unlock',
	'UNLOCK_POST'			=> 'Unlock Post',
	'UNLOCK_POST_EXPLAIN'	=> 'Allow editing',
	'UNLOCK_POST_POST'		=> 'Unlock Post',
	'UNLOCK_POST_POST_CONFIRM'=> 'Are you sure you want to allow editing this post?',
	'UNLOCK_POST_POSTS'		=> 'Unlock selected posts',
	'UNLOCK_POST_POSTS_CONFIRM'=> 'Are you sure you want to allow editing the selected posts?',
	'UNLOCK_TOPIC'			=> 'Unlock Topic',
	'UNLOCK_TOPIC_CONFIRM'	=> 'Are you sure you want to unlock this topic?',
	'UNLOCK_TOPICS'			=> 'Unlock selected topics',
	'UNLOCK_TOPICS_CONFIRM'	=> 'Are you sure you want to unlock all selected topics?', 
	'UNRATE_POST'			=> 'Unrate post',
	'UNRATE_POST_EXPLAIN'	=> 'Reset post rating',
	'USER_CANNOT_POST'		=> 'You cannot post in this forum',
	'USER_CANNOT_REPORT'	=> 'You cannot report posts in this forum',
	'USER_FEEDBACK_ADDED'	=> 'User feedback added successfully',

	'VIEW_DETAILS'			=> 'View Details',

	'YOU_SELECTED_TOPIC'	=> 'You selected topic number %d: %s',

	'report_reasons'		=> array(
		'TITLE'	=> array(
			'WAREZ'		=> 'Warez',
			'SPAM'		=> 'Spam',
			'OFF_TOPIC'	=> 'Off-topic',
			'OTHER'		=> 'Other'
		),
		'DESCRIPTION' => array(
			'WAREZ'		=> 'The post contains links to illegal or pirated software',
			'SPAM'		=> 'The reported post has the only purpose to advertise for a website or another product',
			'OFF_TOPIC'	=> 'The reported post is off topic',
			'OTHER'		=> 'The reported post does not fit into any other category, please use the description field'
		)
	)
);

?>