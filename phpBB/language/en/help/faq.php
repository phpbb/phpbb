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

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'HELP_FAQ_ATTACHMENTS_ALLOWED_ANSWER'	=> 'Each board administrator can allow or disallow certain attachment types. If you are unsure what is allowed to be uploaded, contact the board administrator for assistance.',
	'HELP_FAQ_ATTACHMENTS_ALLOWED_QUESTION'	=> 'What attachments are allowed on this board?',
	'HELP_FAQ_ATTACHMENTS_OWN_ANSWER'	=> 'To find your list of attachments that you have uploaded, go to your User Control Panel and follow the links to the attachments section.',
	'HELP_FAQ_ATTACHMENTS_OWN_QUESTION'	=> 'How do I find all my attachments?',

	'HELP_FAQ_BLOCK_ATTACHMENTS'	=> 'Attachments',
	'HELP_FAQ_BLOCK_BOOKMARKS'	=> 'Subscriptions and Bookmarks',
	'HELP_FAQ_BLOCK_FORMATTING'	=> 'Formatting and Topic Types',
	'HELP_FAQ_BLOCK_FRIENDS'	=> 'Friends and Foes',
	'HELP_FAQ_BLOCK_GROUPS'	=> 'User Levels and Groups',
	'HELP_FAQ_BLOCK_ISSUES'	=> 'phpBB Issues',
	'HELP_FAQ_BLOCK_LOGIN'	=> 'Login and Registration Issues',
	'HELP_FAQ_BLOCK_PMS'	=> 'Private Messaging',
	'HELP_FAQ_BLOCK_POSTING'	=> 'Posting Issues',
	'HELP_FAQ_BLOCK_SEARCH'	=> 'Searching the Forums',
	'HELP_FAQ_BLOCK_USERSETTINGS'	=> 'User Preferences and settings',

	'HELP_FAQ_BOOKMARKS_DIFFERENCE_ANSWER'	=> 'In phpBB 3.0, bookmarking topics worked much like bookmarking in a web browser. You were not alerted when there was an update. As of phpBB 3.1, bookmarking is more like subscribing to a topic. You can be notified when a bookmarked topic is updated. Subscribing, however, will notify you when there is an update to a topic or forum on the board. Notification options for bookmarks and subscriptions can be configured in the User Control Panel, under “Board preferences”.',
	'HELP_FAQ_BOOKMARKS_DIFFERENCE_QUESTION'	=> 'What is the difference between bookmarking and subscribing?',
	'HELP_FAQ_BOOKMARKS_FORUM_ANSWER'	=> 'To subscribe to a specific forum, click the “Subscribe forum” link, at the bottom of page, upon entering the forum.',
	'HELP_FAQ_BOOKMARKS_FORUM_QUESTION'	=> 'How do I subscribe to specific forums?',
	'HELP_FAQ_BOOKMARKS_REMOVE_ANSWER'	=> 'To remove your subscriptions, go to your User Control Panel and follow the links to your subscriptions.',
	'HELP_FAQ_BOOKMARKS_REMOVE_QUESTION'	=> 'How do I remove my subscriptions?',
	'HELP_FAQ_BOOKMARKS_TOPIC_ANSWER'	=> 'You can bookmark or subscribe to a specific topic by clicking the appropriate link in the “Topic tools” menu, conveniently located near the top and bottom of a topic discussion.<br />Replying to a topic with the “Notify me when a reply is posted” option checked will also subscribe you to the topic.',
	'HELP_FAQ_BOOKMARKS_TOPIC_QUESTION'	=> 'How do I bookmark or subscribe to specific topics?',

	'HELP_FAQ_FORMATTING_ANNOUNCEMENT_ANSWER'	=> 'Announcements often contain important information for the forum you are currently reading and you should read them whenever possible. Announcements appear at the top of every page in the forum to which they are posted. As with global announcements, announcement permissions are granted by the board administrator.',
	'HELP_FAQ_FORMATTING_ANNOUNCEMENT_QUESTION'	=> 'What are announcements?',
	'HELP_FAQ_FORMATTING_BBOCDE_ANSWER'	=> 'BBCode is a special implementation of HTML, offering great formatting control on particular objects in a post. The use of BBCode is granted by the administrator, but it can also be disabled on a per post basis from the posting form. BBCode itself is similar in style to HTML, but tags are enclosed in square brackets [ and ] rather than &lt; and &gt;. For more information on BBCode see the guide which can be accessed from the posting page.',
	'HELP_FAQ_FORMATTING_BBOCDE_QUESTION'	=> 'What is BBCode?',
	'HELP_FAQ_FORMATTING_GLOBAL_ANNOUNCE_ANSWER'	=> 'Global announcements contain important information and you should read them whenever possible. They will appear at the top of every forum and within your User Control Panel. Global announcement permissions are granted by the board administrator.',
	'HELP_FAQ_FORMATTING_GLOBAL_ANNOUNCE_QUESTION'	=> 'What are global announcements?',
	'HELP_FAQ_FORMATTING_HTML_ANSWER'	=> 'No. It is not possible to post HTML on this board and have it rendered as HTML. Most formatting which can be carried out using HTML can be applied using BBCode instead.',
	'HELP_FAQ_FORMATTING_HTML_QUESTION'	=> 'Can I use HTML?',
	'HELP_FAQ_FORMATTING_ICONS_ANSWER'	=> 'Topic icons are author chosen images associated with posts to indicate their content. The ability to use topic icons depends on the permissions set by the board administrator.',
	'HELP_FAQ_FORMATTING_ICONS_QUESTION'	=> 'What are topic icons?',
	'HELP_FAQ_FORMATTING_IMAGES_ANSWER'	=> 'Yes, images can be shown in your posts. If the administrator has allowed attachments, you may be able to upload the image to the board. Otherwise, you must link to an image stored on a publicly accessible web server, e.g. http://www.example.com/my-picture.gif. You cannot link to pictures stored on your own PC (unless it is a publicly accessible server) nor images stored behind authentication mechanisms, e.g. hotmail or yahoo mailboxes, password protected sites, etc. To display the image use the BBCode [img] tag.',
	'HELP_FAQ_FORMATTING_IMAGES_QUESTION'	=> 'Can I post images?',
	'HELP_FAQ_FORMATTING_LOCKED_ANSWER'	=> 'Locked topics are topics where users can no longer reply and any poll it contained was automatically ended. Topics may be locked for many reasons and were set this way by either the forum moderator or board administrator. You may also be able to lock your own topics depending on the permissions you are granted by the board administrator.',
	'HELP_FAQ_FORMATTING_LOCKED_QUESTION'	=> 'What are locked topics?',
	'HELP_FAQ_FORMATTING_SMILIES_ANSWER'	=> 'Smilies, or Emoticons, are small images which can be used to express a feeling using a short code, e.g. :) denotes happy, while :( denotes sad. The full list of emoticons can be seen in the posting form. Try not to overuse smilies, however, as they can quickly render a post unreadable and a moderator may edit them out or remove the post altogether. The board administrator may also have set a limit to the number of smilies you may use within a post.',
	'HELP_FAQ_FORMATTING_SMILIES_QUESTION'	=> 'What are Smilies?',
	'HELP_FAQ_FORMATTING_STICKIES_ANSWER'	=> 'Sticky topics within the forum appear below announcements and only on the first page. They are often quite important so you should read them whenever possible. As with announcements and global announcements, sticky topic permissions are granted by the board administrator.',
	'HELP_FAQ_FORMATTING_STICKIES_QUESTION'	=> 'What are sticky topics?',

	'HELP_FAQ_FRIENDS_BASIC_ANSWER'	=> 'You can use these lists to organise other members of the board. Members added to your friends list will be listed within your User Control Panel for quick access to see their online status and to send them private messages. Subject to template support, posts from these users may also be highlighted. If you add a user to your foes list, any posts they make will be hidden by default.',
	'HELP_FAQ_FRIENDS_BASIC_QUESTION'	=> 'What are my Friends and Foes lists?',
	'HELP_FAQ_FRIENDS_MANAGE_ANSWER'	=> 'You can add users to your list in two ways. Within each user’s profile, there is a link to add them to either your Friend or Foe list. Alternatively, from your User Control Panel, you can directly add users by entering their member name. You may also remove users from your list using the same page.',
	'HELP_FAQ_FRIENDS_MANAGE_QUESTION'	=> 'How can I add / remove users to my Friends or Foes list?',

	'HELP_FAQ_GROUPS_ADMINISTRATORS_ANSWER'	=> 'Administrators are members assigned with the highest level of control over the entire board. These members can control all facets of board operation, including setting permissions, banning users, creating usergroups or moderators, etc., dependent upon the board founder and what permissions he or she has given the other administrators. They may also have full moderator capabilities in all forums, depending on the settings put forth by the board founder.',
	'HELP_FAQ_GROUPS_ADMINISTRATORS_QUESTION'	=> 'What are Administrators?',
	'HELP_FAQ_GROUPS_COLORS_ANSWER'	=> 'It is possible for the board administrator to assign a colour to the members of a usergroup to make it easy to identify the members of this group.',
	'HELP_FAQ_GROUPS_COLORS_QUESTION'	=> 'Why do some usergroups appear in a different colour?',
	'HELP_FAQ_GROUPS_DEFAULT_ANSWER'	=> 'If you are a member of more than one usergroup, your default is used to determine which group colour and group rank should be shown for you by default. The board administrator may grant you permission to change your default usergroup via your User Control Panel.',
	'HELP_FAQ_GROUPS_DEFAULT_QUESTION'	=> 'What is a “Default usergroup”?',
	'HELP_FAQ_GROUPS_MODERATORS_ANSWER'	=> 'Moderators are individuals (or groups of individuals) who look after the forums from day to day. They have the authority to edit or delete posts and lock, unlock, move, delete and split topics in the forum they moderate. Generally, moderators are present to prevent users from going off-topic or posting abusive or offensive material.',
	'HELP_FAQ_GROUPS_MODERATORS_QUESTION'	=> 'What are Moderators?',
	'HELP_FAQ_GROUPS_TEAM_ANSWER'	=> 'This page provides you with a list of board staff, including board administrators and moderators and other details such as the forums they moderate.',
	'HELP_FAQ_GROUPS_TEAM_QUESTION'	=> 'What is “The team” link?',
	'HELP_FAQ_GROUPS_USERGROUPS_ANSWER'	=> 'Usergroups are groups of users that divide the community into manageable sections board administrators can work with. Each user can belong to several groups and each group can be assigned individual permissions. This provides an easy way for administrators to change permissions for many users at once, such as changing moderator permissions or granting users access to a private forum.',
	'HELP_FAQ_GROUPS_USERGROUPS_JOIN_ANSWER'	=> 'You can view all usergroups via the “Usergroups” link within your User Control Panel. If you would like to join one, proceed by clicking the appropriate button. Not all groups have open access, however. Some may require approval to join, some may be closed and some may even have hidden memberships. If the group is open, you can join it by clicking the appropriate button. If a group requires approval to join you may request to join by clicking the appropriate button. The user group leader will need to approve your request and may ask why you want to join the group. Please do not harass a group leader if they reject your request; they will have their reasons.',
	'HELP_FAQ_GROUPS_USERGROUPS_JOIN_QUESTION'	=> 'Where are the usergroups and how do I join one?',
	'HELP_FAQ_GROUPS_USERGROUPS_LEAD_ANSWER'	=> 'A usergroup leader is usually assigned when usergroups are initially created by a board administrator. If you are interested in creating a usergroup, your first point of contact should be an administrator; try sending a private message.',
	'HELP_FAQ_GROUPS_USERGROUPS_LEAD_QUESTION'	=> 'How do I become a usergroup leader?',
	'HELP_FAQ_GROUPS_USERGROUPS_QUESTION'	=> 'What are usergroups?',

	'HELP_FAQ_ISSUES_ADMIN_ANSWER'	=> 'All users of the board can use the “Contact us” form, if the option was enabled by the board administrator.<br />Members of the board can also use the “The team” link.',
	'HELP_FAQ_ISSUES_ADMIN_QUESTION'	=> 'How do I contact a board administrator?',
	'HELP_FAQ_ISSUES_FEATURE_ANSWER'	=> 'This software was written by and licensed through phpBB Limited. If you believe a feature needs to be added please visit the <a href="https://www.phpbb.com/ideas/">phpBB Ideas Centre</a>, where you can upvote existing ideas or suggest new features.',
	'HELP_FAQ_ISSUES_FEATURE_QUESTION'	=> 'Why isn’t X feature available?',
	'HELP_FAQ_ISSUES_LEGAL_ANSWER'	=> 'Any of the administrators listed on the “The team” page should be an appropriate point of contact for your complaints. If this still gets no response then you should contact the owner of the domain (do a <a href="http://www.google.com/search?q=whois">whois lookup</a>) or, if this is running on a free service (e.g. Yahoo!, free.fr, f2s.com, etc.), the management or abuse department of that service. Please note that the phpBB Limited has <strong>absolutely no jurisdiction</strong> and cannot in any way be held liable over how, where or by whom this board is used. Do not contact the phpBB Limited in relation to any legal (cease and desist, liable, defamatory comment, etc.) matter <strong>not directly related</strong> to the phpBB.com website or the discrete software of phpBB itself. If you do email phpBB Limited <strong>about any third party</strong> use of this software then you should expect a terse response or no response at all.',
	'HELP_FAQ_ISSUES_LEGAL_QUESTION'	=> 'Who do I contact about abusive and/or legal matters related to this board?',
	'HELP_FAQ_ISSUES_WHOIS_PHPBB_ANSWER'	=> 'This software (in its unmodified form) is produced, released and is copyright <a href="https://www.phpbb.com/">phpBB Limited</a>. It is made available under the GNU General Public License, version 2 (GPL-2.0) and may be freely distributed. See <a href="https://www.phpbb.com/about/">About phpBB</a> for more details.',
	'HELP_FAQ_ISSUES_WHOIS_PHPBB_QUESTION'	=> 'Who wrote this bulletin board?',

	'HELP_FAQ_LOGIN_AUTO_LOGOUT_ANSWER'	=> 'If you do not check the <em>Remember me</em> box when you login, the board will only keep you logged in for a preset time. This prevents misuse of your account by anyone else. To stay logged in, check the <em>Remember me</em> box during login. This is not recommended if you access the board from a shared computer, e.g. library, internet cafe, university computer lab, etc. If you do not see this checkbox, it means a board administrator has disabled this feature.',
	'HELP_FAQ_LOGIN_AUTO_LOGOUT_QUESTION'	=> 'Why do I get logged off automatically?',
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANSWER'	=> 'There are several reasons why this could occur. First, ensure your username and password are correct. If they are, contact a board administrator to make sure you haven’t been banned. It is also possible the website owner has a configuration error on their end, and they would need to fix it.',
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANYMORE_ANSWER'	=> 'It is possible an administrator has deactivated or deleted your account for some reason. Also, many boards periodically remove users who have not posted for a long time to reduce the size of the database. If this has happened, try registering again and being more involved in discussions.',
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_ANYMORE_QUESTION'	=> 'I registered in the past but cannot login any more?!',
	'HELP_FAQ_LOGIN_CANNOT_LOGIN_QUESTION'	=> 'Why can’t I login?',
	'HELP_FAQ_LOGIN_CANNOT_REGISTER_ANSWER'	=> 'It is possible a board administrator has disabled registration to prevent new visitors from signing up. A board administrator could have also banned your IP address or disallowed the username you are attempting to register. Contact a board administrator for assistance.',
	'HELP_FAQ_LOGIN_CANNOT_REGISTER_QUESTION'	=> 'Why can’t I register?',
	'HELP_FAQ_LOGIN_COPPA_ANSWER'	=> 'COPPA, or the Children’s Online Privacy Protection Act of 1998, is a law in the United States requiring websites which can potentially collect information from minors under the age of 13 to have written parental consent or some other method of legal guardian acknowledgment, allowing the collection of personally identifiable information from a minor under the age of 13. If you are unsure if this applies to you as someone trying to register or to the website you are trying to register on, contact legal counsel for assistance. Please note that phpBB Limited and the owners of this board cannot provide legal advice and is not a point of contact for legal concerns of any kind, except as outlined in question “Who do I contact about abusive and/or legal matters related to this board?”.',
	'HELP_FAQ_LOGIN_COPPA_QUESTION'	=> 'What is COPPA?',
	'HELP_FAQ_LOGIN_DELETE_COOKIES_ANSWER'	=> '“Delete cookies” deletes the cookies created by phpBB which keep you authenticated and logged into the board. Cookies also provide functions such as read tracking if they have been enabled by a board administrator. If you are having login or logout problems, deleting board cookies may help.',
	'HELP_FAQ_LOGIN_DELETE_COOKIES_QUESTION'	=> 'What does the “Delete cookies” do?',
	'HELP_FAQ_LOGIN_LOST_PASSWORD_ANSWER'	=> 'Don’t panic! While your password cannot be retrieved, it can easily be reset. Visit the login page and click <em>I forgot my password</em>. Follow the instructions and you should be able to log in again shortly.<br />However, if you are not able to reset your password, contact a board administrator.',
	'HELP_FAQ_LOGIN_LOST_PASSWORD_QUESTION'	=> 'I’ve lost my password!',
	'HELP_FAQ_LOGIN_REGISTER_ANSWER'	=> 'You may not have to, it is up to the administrator of the board as to whether you need to register in order to post messages. However; registration will give you access to additional features not available to guest users such as definable avatar images, private messaging, emailing of fellow users, usergroup subscription, etc. It only takes a few moments to register so it is recommended you do so.',
	'HELP_FAQ_LOGIN_REGISTER_CONFIRM_ANSWER'	=> 'First, check your username and password. If they are correct, then one of two things may have happened. If COPPA support is enabled and you specified being under 13 years old during registration, you will have to follow the instructions you received. Some boards will also require new registrations to be activated, either by yourself or by an administrator before you can logon; this information was present during registration. If you were sent an email, follow the instructions. If you did not receive an email, you may have provided an incorrect email address or the email may have been picked up by a spam filer. If you are sure the email address you provided is correct, try contacting an administrator.',
	'HELP_FAQ_LOGIN_REGISTER_CONFIRM_QUESTION'	=> 'I registered but cannot login!',
	'HELP_FAQ_LOGIN_REGISTER_QUESTION'	=> 'Why do I need to register?',

	'HELP_FAQ_PMS_CANNOT_SEND_ANSWER'	=> 'There are three reasons for this; you are not registered and/or not logged on, the board administrator has disabled private messaging for the entire board, or the board administrator has prevented you from sending messages. Contact a board administrator for more information.',
	'HELP_FAQ_PMS_CANNOT_SEND_QUESTION'	=> 'I cannot send private messages!',
	'HELP_FAQ_PMS_SPAM_ANSWER'	=> 'We are sorry to hear that. The email form feature of this board includes safeguards to try and track users who send such posts, so email the board administrator with a full copy of the email you received. It is very important that this includes the headers that contain the details of the user that sent the email. The board administrator can then take action.',
	'HELP_FAQ_PMS_SPAM_QUESTION'	=> 'I have received a spamming or abusive email from someone on this board!',
	'HELP_FAQ_PMS_UNWANTED_ANSWER'	=> 'You can automatically delete private messages from a user by using message rules within your User Control Panel. If you are receiving abusive private messages from a particular user, report the messages to the moderators; they have the power to prevent a user from sending private messages.',
	'HELP_FAQ_PMS_UNWANTED_QUESTION'	=> 'I keep getting unwanted private messages!',

	'HELP_FAQ_POSTING_BUMP_ANSWER'	=> 'By clicking the “Bump topic” link when you are viewing it, you can “bump” the topic to the top of the forum on the first page. However, if you do not see this, then topic bumping may be disabled or the time allowance between bumps has not yet been reached. It is also possible to bump the topic simply by replying to it, however, be sure to follow the board rules when doing so.',
	'HELP_FAQ_POSTING_BUMP_QUESTION'	=> 'How do I bump my topic?',
	'HELP_FAQ_POSTING_CREATE_ANSWER'	=> 'To post a new topic in a forum, click "New Topic". To post a reply to a topic, click "Post Reply". You may need to register before you can post a message. A list of your permissions in each forum is available at the bottom of the forum and topic screens. Example: You can post new topics, You can post attachments, etc.',
	'HELP_FAQ_POSTING_CREATE_QUESTION'	=> 'How do I create a new topic or post a reply?',
	'HELP_FAQ_POSTING_DRAFT_ANSWER'	=> 'This allows you to save drafts to be completed and submitted at a later date. To reload a saved draft, visit the User Control Panel.',
	'HELP_FAQ_POSTING_DRAFT_QUESTION'	=> 'What is the “Save” button for in topic posting?',
	'HELP_FAQ_POSTING_EDIT_DELETE_ANSWER'	=> 'Unless you are a board administrator or moderator, you can only edit or delete your own posts. You can edit a post by clicking the edit button for the relevant post, sometimes for only a limited time after the post was made. If someone has already replied to the post, you will find a small piece of text output below the post when you return to the topic which lists the number of times you edited it along with the date and time. This will only appear if someone has made a reply; it will not appear if a moderator or administrator edited the post, though they may leave a note as to why they’ve edited the post at their own discretion. Please note that normal users cannot delete a post once someone has replied.',
	'HELP_FAQ_POSTING_EDIT_DELETE_QUESTION'	=> 'How do I edit or delete a post?',
	'HELP_FAQ_POSTING_FORUM_RESTRICTED_ANSWER'	=> 'Some forums may be limited to certain users or groups. To view, read, post or perform another action you may need special permissions. Contact a moderator or board administrator to grant you access.',
	'HELP_FAQ_POSTING_FORUM_RESTRICTED_QUESTION'	=> 'Why can’t I access a forum?',
	'HELP_FAQ_POSTING_NO_ATTACHMENTS_ANSWER'	=> 'Attachment permissions are granted on a per forum, per group, or per user basis. The board administrator may not have allowed attachments to be added for the specific forum you are posting in, or perhaps only certain groups can post attachments. Contact the board administrator if you are unsure about why you are unable to add attachments.',
	'HELP_FAQ_POSTING_NO_ATTACHMENTS_QUESTION'	=> 'Why can’t I add attachments?',
	'HELP_FAQ_POSTING_POLL_ADD_ANSWER'	=> 'The limit for poll options is set by the board administrator. If you feel you need to add more options to your poll than the allowed amount, contact the board administrator.',
	'HELP_FAQ_POSTING_POLL_ADD_QUESTION'	=> 'Why can’t I add more poll options?',
	'HELP_FAQ_POSTING_POLL_CREATE_ANSWER'	=> 'When posting a new topic or editing the first post of a topic, click the “Poll creation” tab below the main posting form; if you cannot see this, you do not have appropriate permissions to create polls. Enter a title and at least two options in the appropriate fields, making sure each option is on a separate line in the textarea. You can also set the number of options users may select during voting under “Options per user”, a time limit in days for the poll (0 for infinite duration) and lastly the option to allow users to amend their votes.',
	'HELP_FAQ_POSTING_POLL_CREATE_QUESTION'	=> 'How do I create a poll?',
	'HELP_FAQ_POSTING_POLL_EDIT_ANSWER'	=> 'As with posts, polls can only be edited by the original poster, a moderator or an administrator. To edit a poll, click to edit the first post in the topic; this always has the poll associated with it. If no one has cast a vote, users can delete the poll or edit any poll option. However, if members have already placed votes, only moderators or administrators can edit or delete it. This prevents the poll’s options from being changed mid-way through a poll.',
	'HELP_FAQ_POSTING_POLL_EDIT_QUESTION'	=> 'How do I edit or delete a poll?',
	'HELP_FAQ_POSTING_QUEUE_ANSWER'	=> 'The board administrator may have decided that posts in the forum you are posting to require review before submission. It is also possible that the administrator has placed you in a group of users whose posts require review before submission. Please contact the board administrator for further details.',
	'HELP_FAQ_POSTING_QUEUE_QUESTION'	=> 'Why does my post need to be approved?',
	'HELP_FAQ_POSTING_REPORT_ANSWER'	=> 'If the board administrator has allowed it, you should see a button for reporting posts next to the post you wish to report. Clicking this will walk you through the steps necessary to report the post.',
	'HELP_FAQ_POSTING_REPORT_QUESTION'	=> 'How can I report posts to a moderator?',
	'HELP_FAQ_POSTING_SIGNATURE_ANSWER'	=> 'To add a signature to a post you must first create one via your User Control Panel. Once created, you can check the <em>Attach a signature</em> box on the posting form to add your signature. You can also add a signature by default to all your posts by checking the appropriate radio button in the User Control Panel. If you do so, you can still prevent a signature being added to individual posts by un-checking the add signature box within the posting form.',
	'HELP_FAQ_POSTING_SIGNATURE_QUESTION'	=> 'How do I add a signature to my post?',
	'HELP_FAQ_POSTING_WARNING_ANSWER'	=> 'Each board administrator has their own set of rules for their site. If you have broken a rule, you may be issued a warning. Please note that this is the board administrator’s decision, and the phpBB Limited has nothing to do with the warnings on the given site. Contact the board administrator if you are unsure about why you were issued a warning.',
	'HELP_FAQ_POSTING_WARNING_QUESTION'	=> 'Why did I receive a warning?',

	'HELP_FAQ_SEARCH_BLANK_ANSWER'	=> 'Your search returned too many results for the webserver to handle. Use “Advanced search” and be more specific in the terms used and forums that are to be searched.',
	'HELP_FAQ_SEARCH_BLANK_QUESTION'	=> 'Why does my search return a blank page!?',
	'HELP_FAQ_SEARCH_FORUM_ANSWER'	=> 'Enter a search term in the search box located on the index, forum or topic pages. Advanced search can be accessed by clicking the “Advance Search” link which is available on all pages on the forum. How to access the search may depend on the style used.',
	'HELP_FAQ_SEARCH_FORUM_QUESTION'	=> 'How can I search a forum or forums?',
	'HELP_FAQ_SEARCH_MEMBERS_ANSWER'	=> 'Visit to the “Members” page and click the “Find a member” link.',
	'HELP_FAQ_SEARCH_MEMBERS_QUESTION'	=> 'How do I search for members?',
	'HELP_FAQ_SEARCH_NO_RESULT_ANSWER'	=> 'Your search was probably too vague and included many common terms which are not indexed by phpBB. Be more specific and use the options available within Advanced search.',
	'HELP_FAQ_SEARCH_NO_RESULT_QUESTION'	=> 'Why does my search return no results?',
	'HELP_FAQ_SEARCH_OWN_ANSWER'	=> 'Your own posts can be retrieved either by clicking the “Show your posts” link within the User Control Panel or by clicking the “Search user’s posts” link via your own profile page or by clicking the “Quick links” menu at the top of the board. To search for your topics, use the Advanced search page and fill in the various options appropriately.',
	'HELP_FAQ_SEARCH_OWN_QUESTION'	=> 'How can I find my own posts and topics?',

	'HELP_FAQ_USERSETTINGS_AVATAR_ANSWER'	=> 'There are two images which may appear along with a username when viewing posts. One of them may be an image associated with your rank, generally in the form of stars, blocks or dots, indicating how many posts you have made or your status on the board. Another, usually larger, image is known as an avatar and is generally unique or personal to each user.',
	'HELP_FAQ_USERSETTINGS_AVATAR_DISPLAY_ANSWER'	=> 'Within your User Control Panel, under “Profile” you can add an avatar by using one of the four following methods: Gravatar, Gallery, Remote or Upload. It is up to the board administrator to enable avatars and to choose the way in which avatars can be made available. If you are unable to use avatars, contact a board administrator.',
	'HELP_FAQ_USERSETTINGS_AVATAR_DISPLAY_QUESTION'	=> 'How do I display an avatar?',
	'HELP_FAQ_USERSETTINGS_AVATAR_QUESTION'	=> 'What are the images next to my username?',
	'HELP_FAQ_USERSETTINGS_CHANGE_SETTINGS_ANSWER'	=> 'If you are a registered user, all your settings are stored in the board database. To alter them, visit your User Control Panel; a link can usually be found by clicking on your username at the top of board pages. This system will allow you to change all your settings and preferences.',
	'HELP_FAQ_USERSETTINGS_CHANGE_SETTINGS_QUESTION'	=> 'How do I change my settings?',
	'HELP_FAQ_USERSETTINGS_EMAIL_LOGIN_ANSWER'	=> 'Only registered users can send email to other users via the built-in email form, and only if the administrator has enabled this feature. This is to prevent malicious use of the email system by anonymous users.',
	'HELP_FAQ_USERSETTINGS_EMAIL_LOGIN_QUESTION'	=> 'When I click the email link for a user it asks me to login?',
	'HELP_FAQ_USERSETTINGS_HIDE_ONLINE_ANSWER'	=> 'Within your User Control Panel, under “Board preferences”, you will find the option <em>Hide your online status</em>. Enable this option and you will only appear to the administrators, moderators and yourself. You will be counted as a hidden user.',
	'HELP_FAQ_USERSETTINGS_HIDE_ONLINE_QUESTION'	=> 'How do I prevent my username appearing in the online user listings?',
	'HELP_FAQ_USERSETTINGS_LANGUAGE_ANSWER'	=> 'Either the administrator has not installed your language or nobody has translated this board into your language. Try asking a board administrator if they can install the language pack you need. If the language pack does not exist, feel free to create a new translation. More information can be found at the <a href="https://www.phpbb.com/">phpBB</a>&reg; website.',
	'HELP_FAQ_USERSETTINGS_LANGUAGE_QUESTION'	=> 'My language is not in the list!',
	'HELP_FAQ_USERSETTINGS_RANK_ANSWER'	=> 'Ranks, which appear below your username, indicate the number of posts you have made or identify certain users, e.g. moderators and administrators. In general, you cannot directly change the wording of any board ranks as they are set by the board administrator. Please do not abuse the board by posting unnecessarily just to increase your rank. Most boards will not tolerate this and the moderator or administrator will simply lower your post count.',
	'HELP_FAQ_USERSETTINGS_RANK_QUESTION'	=> 'What is my rank and how do I change it?',
	'HELP_FAQ_USERSETTINGS_SERVERTIME_ANSWER'	=> 'If you are sure you have set the timezone correctly and the time is still incorrect, then the time stored on the server clock is incorrect. Please notify an administrator to correct the problem.',
	'HELP_FAQ_USERSETTINGS_SERVERTIME_QUESTION'	=> 'I changed the timezone and the time is still wrong!',
	'HELP_FAQ_USERSETTINGS_TIMEZONE_ANSWER'	=> 'It is possible the time displayed is from a timezone different from the one you are in. If this is the case, visit your User Control Panel and change your timezone to match your particular area, e.g. London, Paris, New York, Sydney, etc. Please note that changing the timezone, like most settings, can only be done by registered users. If you are not registered, this is a good time to do so.',
	'HELP_FAQ_USERSETTINGS_TIMEZONE_QUESTION'	=> 'The times are not correct!',
));
