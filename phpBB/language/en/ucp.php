<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp.php [ English ]
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
	'ACCOUNT_ACTIVE'			=> 'Your account has now been activated. Thank you for registering',
	'ACCOUNT_ACTIVE_ADMIN'		=> 'The account has now been activated',
	'ACCOUNT_ADDED'				=> 'Thank you for registering, your account has been created. You may now login with your username and password',
	'ACCOUNT_COPPA'				=> 'Your account has been created but has to be approved, please check your email for details.',
	'ACCOUNT_INACTIVE'			=> 'Your account has been created. However, this forum requires account activation, an activation key has been sent to the email address you provided. Please check your email for further information',
	'ACCOUNT_INACTIVE_ADMIN'	=> 'Your account has been created. However, this forum requires account activation by the administrator. An email has been sent to them and you will be informed when your account has been activated',
	'ADD_FOES'					=> 'Add new foes',
	'ADD_FOES_EXPLAIN'			=> 'You may enter several usernames each on a different line',
	'ADD_FRIENDS'				=> 'Add new friends',
	'ADD_FRIENDS_EXPLAIN'		=> 'You may enter several usernames each on a different line',
	'ADMIN_EMAIL'				=> 'Administrators can email me information',
	'AGREE'						=> 'I agree to these terms',
	'ALLOW_PM'					=> 'Allow users to send you private messages',
	'ALLOW_PM_EXPLAIN'			=> 'Note that admins and moderators will always be able to send you messages.',
	'ALREADY_ACTIVATED'			=> 'You have already activated your account',
	'ATTACHMENTS_DELETED'		=> 'Attachments successfully deleted',
	'ATTACHMENT_DELETED'		=> 'Attachment successfully deleted',
	'AVATAR_CATEGORY'			=> 'Category',
	'AVATAR_EXPLAIN'			=> 'Maximum dimensions; width %1$d pixels, height %2$d pixels, filesize %3$dkB.',
	'AVATAR_GALLERY'			=> 'Local gallery',
	'AVATAR_PAGE'				=> 'Page',

	'BACK_TO_DRAFTS'			=> 'Back to saved drafts',
	'BIRTHDAY'					=> 'Birthday',
	'BIRTHDAY_EXPLAIN'			=> 'Setting a year will list your age when it is your birthday.',
	'BOARD_DATE_FORMAT'			=> 'My date format',
	'BOARD_DATE_FORMAT_EXPLAIN'	=> 'The syntax used is identical to the PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> function',
	'BOARD_DST'					=> 'Daylight Saving Time is in effect',
	'BOARD_LANGUAGE'			=> 'My language',
	'BOARD_STYLE'				=> 'My board style',
	'BOARD_TIMEZONE'			=> 'My timezone',

	'CHANGE_PASSWORD'			=> 'Change password',
	'CHANGE_PASSWORD_EXPLAIN'	=> 'Must be between %1$d and %2$d characters.',
	'CONFIRMATION'				=> 'Confirmation of registration',
	'CONFIRM_CODE'				=> 'Confirmation code',
	'CONFIRM_CODE_EXPLAIN'		=> 'Enter the code exactly as you see it in the image, it is case sensitive, zero has a diagonal line through it.',
	'CONFIRM_CODE_WRONG'		=> 'The confirmation code you entered was incorrect.',
	'CONFIRM_DELETE_ATTACHMENT'	=> 'Are you sure you want to delete this attachment?',
	'CONFIRM_DELETE_ATTACHMENTS'=> 'Are you sure you want to delete these attachments?',
	'CONFIRM_EMAIL'				=> 'Confirm email address',
	'CONFIRM_EMAIL_EXPLAIN'		=> 'You only need to specify this if you are changing your email address.',
	'CONFIRM_EXPLAIN'			=> 'To prevent automated registrations the board administrator requires you to enter a confirmation code. The code is displayed in the image you should see below. If you are visually impaired or cannot otherwise read this code please contact the %sBoard Administrator%s.',
	'CONFIRM_PASSWORD'			=> 'Confirm password',
	'CONFIRM_PASSWORD_EXPLAIN'	=> 'You only need to confirm your password if you changed it above',
	'COPPA_BIRTHDAY'			=> 'To continue with the registration procedure please tell us when you were born.',
	'COPPA_COMPLIANCE'			=> 'COPPA Compliance',
	'COPPA_EXPLAIN'				=> 'Please note that clicking submit will create your account. However it cannot be activated until a parent or guardian approves your registration. You will be emailed a copy of the necessary form with details of where to send it.',
	'CURRENT_IMAGE'				=> 'Current Image',
	'CURRENT_PASSWORD'			=> 'Current password',
	'CURRENT_PASSWORD_EXPLAIN'	=> 'You must confirm your current password if you wish to change it, alter your email address or username.',
	'CUR_PASSWORD_ERROR'		=> 'The current password you entered is incorrect.',

	'DEFAULT_ADD_SIG'			=> 'Attach my signature by default',
	'DEFAULT_BBCODE'			=> 'Enable BBCode by default',
	'DEFAULT_HTML'				=> 'Enable HTML by default',
	'DEFAULT_NOTIFY'			=> 'Notify me upon replies by default',
	'DEFAULT_SMILE'				=> 'Enable smilies by default',
	'DELETE_ALL'				=> 'Delete all',
	'DELETE_AVATAR'				=> 'Delete Image',
	'DELETE_MARKED'				=> 'Delete Marked',
	'DISABLE_CENSORS'			=> 'Enable Word censoring',
	'DISPLAY_GALLERY'			=> 'Display gallery',
	'DOWNLOADS'					=> 'Downloads',
	'DRAFTS_DELETED'			=> 'All selected drafts were successfully deleted.',
	'DRAFTS_EXPLAIN'			=> 'Here you can view, edit and delete your saved drafts.',
	'DRAFT_UPDATED'				=> 'Draft successfully updated.',

	'EDIT_DRAFT_EXPLAIN'		=> 'Here you are able to edit your draft.',
	'EMAIL_REMIND'				=> 'This must be the email address you supplied when registering.',
	'EMPTY_DRAFT'				=> 'You must enter a message to submit your changes',
	'EMPTY_DRAFT_TITLE'			=> 'You must enter a draft title',

	'FOES_EXPLAIN'				=> 'Foes are users which will be ignored by default. Posts by these users will not be fully visible and personal messages will not be permitted. Please note that you cannot ignore moderators or administrators.',
	'FOES_UPDATED'				=> 'Your foes list has been updated successfully',
	'FRIENDS'					=> 'Friends',
	'FRIENDS_EXPLAIN'			=> 'Friends enable you quick access to members you communicate with frequently. If the template has relevant support any posts made by a friend may be highlighted.',
	'FRIENDS_OFFLINE'			=> 'Offline',
	'FRIENDS_ONLINE'			=> 'Online',
	'FRIENDS_UPDATED'			=> 'Your friends list has been updated successfully',

	'HIDE_ONLINE'				=> 'Hide my online status',

	'IMPORTANT_NEWS'			=> 'Important announcements',

	'LANGUAGE'					=> 'Language',
	'LINK_REMOTE_AVATAR'		=> 'Link off-site',
	'LINK_REMOTE_AVATAR_EXPLAIN'=> 'Enter the URL of the location containing the Avatar image you wish to link to.',
	'LINK_REMOTE_SIZE'			=> 'Avatar dimensions',
	'LINK_REMOTE_SIZE_EXPLAIN'	=> 'Specify the width and height of the avatar, leave blank to attempt automatic verification.',
	'LOGIN_REDIRECT'			=> 'You have been successfully logged in.',
	'LOGOUT_REDIRECT'			=> 'You have been successfully logged out.',

	'MINIMUM_KARMA'				=> 'Minimum User Karma',
	'MINIMUM_KARMA_EXPLAIN'		=> 'Posts by users with Karma less than this will be ignored.',

	'NEW_EMAIL_ERROR'			=> 'The email addresses you entered do not match.',
	'NEW_PASSWORD'				=> 'Password',
	'NEW_PASSWORD_ERROR'		=> 'The passwords you entered do not match.',
	'NEW_PASSWORD_EXPLAIN'		=> 'Must be between %1$d and %2$d characters.',
	'NOTIFY_METHOD'				=> 'Notification method',
	'NOTIFY_METHOD_BOTH'		=> 'Both',
	'NOTIFY_METHOD_EMAIL'		=> 'Email only',
	'NOTIFY_METHOD_EXPLAIN'		=> 'Method for sending messages sent via this board.',
	'NOTIFY_METHOD_IM'			=> 'Jabber only',
	'NOTIFY_ON_PM'				=> 'Email me on new private messages',
	'NOT_AGREE'					=> 'I do not agree to these terms',
	'NO_FOES'					=> 'No foes currently defined',
	'NO_FRIENDS'				=> 'No friends currently defined',
	'NO_FRIENDS_OFFLINE'		=> 'No friends offline',
	'NO_FRIENDS_ONLINE'			=> 'No friends online',
	'NO_WATCHED_FORUMS'			=> 'You are not watching any forums.',
	'NO_WATCHED_TOPICS'			=> 'You are not watching any topics.',

	'PASSWORD_ACTIVATED'		=> 'Your new password has been activated',
	'PASSWORD_UPDATED'			=> 'Your password has been sent successfully to your original email address.',
	'PM_DISABLED'				=> 'Private messaging has been disabled on this board',
	'POPUP_ON_PM'				=> 'Pop up window on new private message',
	'PREFERENCES_UPDATED'		=> 'Your preferences have been updated.',
	'PROFILE_INFO_NOTICE'		=> 'Please note that this information will be viewable to other members. Be careful when including any personal details. Any fields marked with a * must be completed.',
	'PROFILE_UPDATED'			=> 'Your profile has been updated.',

	'REGISTRATION'				=> 'Registration',
	'RETURN_PAGE'				=> 'Click %sHere%s to return to the previous page',
	'RETURN_UCP'				=> 'Click %sHere%s to return to the User Control Panel',

	'SEARCH_YOUR_POSTS'			=> 'Show your posts',
	'SEND_PASSWORD'				=> 'Send password',
	'SHOW_EMAIL'				=> 'Users can contact me by email',
	'SIGNATURE_EXPLAIN'			=> 'This is a block of text that can be added to posts you make. There is a %d character limit',
	'SIGNATURE_PREVIEW'			=> 'Your signature will appear like this in posts',
	'SIGNATURE_TOO_LONG'		=> 'Your signature is too long.',
	'SORT'						=> 'Sort',
	'SORT_COMMENT'				=> 'File Comment',
	'SORT_DOWNLOADS'			=> 'Downloads',
	'SORT_EXTENSION'			=> 'Extension',
	'SORT_FILENAME'				=> 'Filename',
	'SORT_POST_TIME'			=> 'Post Time',
	'SORT_SIZE'					=> 'Filesize',

	'TIMEZONE'					=> 'Timezone',
	'TOO_MANY_REGISTERS'		=> 'You have exceeded the maximum number of registration attempts for this session. Please try again later.',

	'UCP'						=> 'User Control Panel',
	'UCP_ADMIN_ACTIVATE'		=> 'Please note that you will need to enter a valid email address before your account is activated. The administrator will review your account and if approved you will an email at the address you specified.',
	'UCP_AGREEMENT'				=> 'While the administrators and moderators of this forum will attempt to remove or edit any generally objectionable material as quickly as possible, it is impossible to review every message. Therefore you acknowledge that all posts made to these forums express the views and opinions of the author and not the administrators, moderators or webmaster (except for posts by these people) and hence will not be held liable.<br /><br />You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated or any other material that may violate any applicable laws. Doing so may lead to you being immediately and permanently banned (and your service provider being informed). The IP address of all posts is recorded to aid in enforcing these conditions. You agree that the webmaster, administrator and moderators of this forum have the right to remove, edit, move or close any topic at any time should they see fit. As a user you agree to any information you	have entered above being stored in a database. While this information will not be disclosed to any third party without your consent the webmaster, administrator and moderators cannot be held responsible for any hacking attempt that may lead to the data being compromised.<br /><br />This forum system uses cookies to store information on your local computer. These cookies do not contain any of the information you have entered above, they serve only to improve your viewing pleasure. The email address is used only for confirming your registration details and password (and for sending new passwords should you forget your current one).<br /><br />By clicking Register below you agree to be bound by these conditions.',
	'UCP_AIM'					=> 'AOL Instant Messenger',
	'UCP_AVATAR'				=> 'Your avatar',
	'UCP_COPPA_BEFORE'			=> 'Before %s',
	'UCP_COPPA_ON_AFTER'		=> 'On or After %s',
	'UCP_DRAFTS'				=> 'Saved drafts',
	'UCP_EMAIL_ACTIVATE'		=> 'Please note that you will need to enter a valid email address before your account is activated. You will recieve an email at the address you provide that contains an account activation link.',
	'UCP_FOES'					=> 'Foes',
	'UCP_FRIENDS'				=> 'Friends',
	'UCP_FRONT'					=> 'Front page',
	'UCP_ICQ'					=> 'ICQ Number',
	'UCP_JABBER'				=> 'Jabber Address',
	'UCP_MAIN'					=> 'Overview',
	'UCP_MSNM'					=> 'MSN Messenger',
	'UCP_NO_ATTACHMENTS'		=> 'You have posted no files',
	'UCP_OPTIONS'				=> 'Options',
	'UCP_PERSONAL'				=> 'Personal Settings',
	'UCP_POST'					=> 'Posting Messages',
	'UCP_PREFS'					=> 'Preferences',
	'UCP_PROFILE'				=> 'Profile',
	'UCP_PROFILE_INFO'			=> 'Your Profile',
	'UCP_REG_DETAILS'			=> 'Registration details',
	'UCP_SIGNATURE'				=> 'Your signature',
	'UCP_VIEW'					=> 'Viewing Posts',
	'UCP_WATCHED'				=> 'Watched items',
	'UCP_WELCOME'				=> 'Welcome to the User Control Panel. From here you can monitor, view and update your profile, preferences, subscribed forums and topics. You can also send messages to other users (if permitted). Please ensure you read any announcements before continuing.',
	'UCP_YIM'					=> 'Yahoo Messenger',
	'UCP_ZEBRA'					=> 'Friends and Foes',
	'UNWATCH_MARKED'			=> 'Unwatch marked',
	'UPLOAD_AVATAR_FILE'		=> 'Upload from your machine',
	'UPLOAD_AVATAR_URL'			=> 'Upload from a URL',
	'UPLOAD_AVATAR_URL_EXPLAIN'	=> 'Enter the URL of the location containing the image, it will be copied to this site.',
	'USERNAME_ALPHA_ONLY_EXPLAIN'	=> 'Username must be between %1$d and %2$d chars long and use only alphanumeric characters',
	'USERNAME_ALPHA_SPACERS_EXPLAIN'=> 'Username must be between %1$d and %2$d chars long and use alphanumeric, space or -+_[] characters.',
	'USERNAME_CHARS_ANY_EXPLAIN'=> 'Length must be between %1$d and %2$d characters.',
	'USERNAME_TAKEN_USERNAME'	=> 'The username you entered is already in use, please select an alternative.',

	'VIEW_AVATARS'				=> 'Display Avatars',
	'VIEW_EDIT'					=> 'View/Edit',
	'VIEW_FLASH'				=> 'Display Flash animations',
	'VIEW_IMAGES'				=> 'Display Images within posts',
	'VIEW_SIGS'					=> 'Display Signatures',
	'VIEW_SMILIES'				=> 'Display Smileys as images',
	'VIEW_TOPICS_DAYS'			=> 'Display topics from previous days',
	'VIEW_TOPICS_DIR'			=> 'Display topic order direction',
	'VIEW_TOPICS_KEY'			=> 'Display topics ordering by',

	'WATCHED_FORUMS'			=> 'Watched Forums',
	'WATCHED_TOPICS'			=> 'Watched Topics',
	'WRONG_ACTIVATION'			=> 'The activation key you supplied does not match any in the database',

	'YOUR_DETAILS'				=> 'Your activity',
	'YOUR_FOES'					=> 'Your foes',
	'YOUR_FOES_EXPLAIN'			=> 'To remove usernames select them and click submit',
	'YOUR_FRIENDS'				=> 'Your friends',
	'YOUR_FRIENDS_EXPLAIN'		=> 'To remove usernames select them and click submit',
	'YOUR_KARMA'				=> 'Your Karma level',
	'YOUR_WARNINGS'				=> 'Your Warning level',
);

?>