<?php
/** 
*
* help_faq [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$help = array(
	array(
		0 => '--',
		1 => 'Login and Registration Issues'
	),
	array(
		0 => 'Why can’t I login?',
		1 => 'There are several reasons why this could occur. First, ensure your username and password are correct. If they are, contact the board owner to make sure you haven’t been banned. It is also possible the website owner has a configuration error on their end, and they would need to fix it.'
	),
	array(
		0 => 'Why do I need to register at all?',
		1 => 'You may not have to, it is up to the administrator of the board as to whether you need to register in order to post messages. However; registration will give you access to additional features not available to guest users such as definable avatar images, private messaging, emailing of fellow users, usergroup subscription, etc. It only takes a few moments to register so it is recommended you do so.'
	),
	array(
		0 => 'Why do I get logged off automatically?',
		1 => 'If you do not check the <em>Log me in automatically</em> box when you login, the board will only keep you logged in for a preset time. This prevents misuse of your account by anyone else. To stay logged in, check the box during login. This is not recommended if you access the board from a shared computer, e.g. library, internet cafe, university computer lab, etc. If you do not see this checkbox, it means the board administrator has disabled this feature.'
	),
	array(
		0 => 'How do I prevent my username appearing in the online user listings?',
		1 => 'Within your User Control Panel under “Board preferences” you will find an option <em>Hide your online status</em>. Enabling this this option with <samp>Yes</samp>, you’ll only appear to the administrators, moderators and yourself. You will be counted as a hidden user.'
	),
	array(
		0 => 'I’ve lost my password!',
		1 => 'Don’t panic! While your password cannot be retrieved, it can be reset. To do this go to the login page and click <em>I’ve forgotten my password</em>, follow the instructions and you should be able to log in again in no time.'
	),
	array(
		0 => 'I registered but cannot login!',
		1 => 'First, check your username and password. If they are correct, then one of two things may have happened. If COPPA support is enabled and you specified being under 13 years old during registration, you will have to follow the instructions you received. Some boards will also require new registrations to be activated, either by yourself or by an administrator before you can logon; this information was present during registration. If you were sent an e-mail, follow the instructions. If you did not receive an e-mail, you may have provided an incorrect e-mail address or the e-mail may have been picked up by a spam filer. If you are sure the e-mail address you provided is correct, try contacting an administrator.'
	),
	array(
		0 => 'I registered in the past but cannot login any more?!',
		1 => 'The most likely reasons for this are: you entered an incorrect username or password (check the e-mail sent to you when you first registered) or the administrator has deactivated or deleted your account for some reason. If it is the latter case then perhaps you did not post anything? It is usual for boards to periodically remove users who have not posted anything so as to reduce the size of the database. Try registering again and get involved in discussions.'
	),
	array(
		0 => 'What is COPPA?',
		1 => 'COPPA, or Child Online Privacy and Protection Act of 1998 is a law in the United States that requires websites that can potentially collect information from minors under the age of 13 to have written parental consent, or some other method of the legal guardians acknowledging they are allowing the collection of personally identifiable information from a minor under the age of 13. If you are unsure if this applies to you as someone trying to register, or to the website you are trying to register on, contact legal counsel for assistance. Please note that the phpBB Teams cannot provide legal advice and are not the point of contact for legal concerns of any kind, except as outlined below.',
	),
	array(
		0 => 'Why can’t I register?',
		1 => 'It is possible the website owner has banned your IP address or disallowed the username you are attempting to register. The website owner could have also disabled registration to prevent new visitors from signing up. Contact the board administrator for assistance.',
	),
	array(
		0 => 'What does the “Delete all board cookies” do?',
		1 => '“Delete all board cookies” deletes the cookies created by phpBB which keep you authenticated and logged into the board, and also provides functions such as read tracking if set by the board owner. Deleting board cookies may help if you are having login or logout problems.',
	),
	array(
		0 => '--',
		1 => 'User Preferences and settings'
	),
	array(
		0 => 'How do I change my settings?',
		1 => 'If you are registered, all your settings are stored in the database. To alter them please visit your User Control Panel (generally shown at the top of pages). This will allow you to change all your settings and preferences.'
	),
	array(
		0 => 'The times are not correct!',
		1 => 'It is possible the time displayed is from a timezone different from the one you are in. If this is the case, visit your User Control Panel and change your timezone to match your particular area, e.g. London, Paris, New York, Sydney, etc. Please note that changing the timezone, like most settings, can only be done by registered users. If you are not registered, this is a good time to do so.'
	),
	array(
		0 => 'I changed the timezone and the time is still wrong!',
		1 => 'If you are sure you have set the timezone and Summer Time/<abbr title="Daylight Saving Time">DST</abbr> correctly and the time is still different, then the time as stored on the servers clock is incorrect and the administrators will need to make the correction.'
	),
	array(
		0 => 'My language is not in the list!',
		1 => 'The most likely reasons for this are either the administrator did not install your language or someone has not translated this board into your language. Try asking the board administrator if they can install the language pack you need. If the language pack does not exist then please feel free to create a new translation. More information can be found at the phpBB website (see link at bottom of pages).'
	),
	array(
		0 => 'How do I show an image below my username?',
		1 => 'There may be two images below a username when viewing posts. Depending on the used style, the first is an image associated with your rank, generally these take the form of stars or blocks indicating how many posts you have made or your status on the board. The second being a larger image known as an avatar, this is generally unique or personal to each user. It is up to the board administrator to enable avatars and they have a choice over the way in which avatars can be made available. If you are unable to use avatars then this is the decision of the board admin, you should ask them for their reasons.'
	),
	array(
		0 => 'How do I change my rank?',
		1 => 'In general you cannot directly change the wording of any rank (ranks appear below your username in topics and on your profile depending on the style used). Most boards use ranks to indicate the number of posts you have made and to identify certain users, e.g. moderators and administrators may have a special rank. Please do not abuse the board by posting unnecessarily just to increase your rank, you will probably find the moderator or administrator will simply lower your post count.'
	),
	array(
		0 => 'When I click the e-mail link for a user it asks me to login?',
		1 => 'Sorry but only registered users can send e-mail to people via the built-in e-mail form (if the administrator enabled this feature). This is to prevent malicious use of the e-mail system by anonymous users.'
	),
	array(
		0 => '--',
		1 => 'Posting Issues'
	),
	array(
		0 => 'How do I post a topic in a forum?',
		1 => 'To post a new topic in one of the forums, click the relevant button on either the forum or topic screens. You may need to register before you can post a message, the facilities available to you are listed at the bottom of the forum and topic screens (the <em>You can post new topics, You can vote in polls, etc.</em> list).'
	),
	array(
		0 => 'How do I edit or delete a post?',
		1 => 'Unless you are the board administrator or a moderator you can only edit or delete your own posts. You can edit a post (sometimes for only a limited time after it was made) by clicking the <em>edit</em> button for the relevant post. If someone has already replied to the post you will find a small piece of text output below the post when you return to the topic, which lists the number of times you edited it along with when. This will not appear if no one has replied, nor will it appear if moderators or administrators edited the post. Though they may leave a note as to why they’ve edited the post at their own digression. Please note that normal users cannot delete a post once someone has replied.'
	),
	array(
		0 => 'How do I add a signature to my post?',
		1 => 'To add a signature to a post you must first create one, this is done via your User Control Panel. Once created you can check the <em>Add signature</em> box on the posting form to add your signature. You can also add a signature by default to all your posts by checking the appropriate radio box in your profile (you can still prevent a signature being added to individual posts by un-checking the add signature box within the posting form).'
	),
	array(
		0 => 'How do I create a poll?',
		1 => 'Creating a poll is easy, when you post a new topic (or edit the first post of a topic, if you have permission) you should see the “Poll creation” tab below the main posting form (if you cannot see this then you do not have appropriate permissions to create polls). You should enter a title for the poll in “Poll question” and then at least two options in the “Poll options” textarea (limit is set by the board administrator), with each option in a seperate line. You can also set the number of options users may select during voting under “Options per user”, setting a time limit in days for the poll (0 is a poll of infinite duration) and lastly the option if users are allowed to amend their votes.'
	),
	array(
		0 => 'How do I edit or delete a poll?',
		1 => 'As with posts, polls can only be edited by the original poster, a moderator or an administrator. To edit a poll, click to edit the first post in the topic; this always has the poll associated with it. If no one has cast a vote, users can delete the poll or edit any poll option. However, if members have already placed votes, only moderators or administrators can edit or delete it. This prevents the poll’s options from being changed mid-way through a poll.'
	),
	array(
		0 => 'Why can’t I access a forum?',
		1 => 'Some forums may be limited to certain users or groups. To view, read, post, etc. you may need special authorization, only the moderators and board administrators can grant this access, you should contact them.'
	),
	array(
		0 => 'Why can’t I add attachments?',
		1 => 'The ability to add attachments can be done on a per forum, per group, or per user basis. The board administrator may not have allowed attachments to be added for the specific forum you are posting in, or perhaps only certain groups can post attachments. Contact the board administrator if you are unsure about why you are unable to add attachments.'
	),
	array(
		0 => 'Why did I receive a warning?',
		1 => 'Each board administrator has their own set of rules for their site. If they feel you have broken one of their rules, they may issue you a warning. Please note that this is the board administrator’s decision, and the phpBB Group does not have anything to do with the warning on the given site.'
	),
	array(
		0 => 'How can I report posts to a moderator?',
		1 => 'If the board administrator has allowed it, go to the post you want to report and you should see a button that is for reporting posts. Clicking this will walk you through the steps necessary to report the post.'
	),
	array(
		0 => 'What is the “Save” button for in topic posting?',
		1 => 'This allows you to save messages to be completed and submitted at a later date. To reload them, go to the User Control Panel and follow the self explanatory options there.'
	),
	array(
		0 => 'Why does my post need to be approved?',
		1 => 'The board administrator may decide that the forum you are posting to needs to have posts reviewed first. It is also possible that the administrator has placed you in to a group of users whom he or she feels is a group that needs to have their posts reviewed before being submitted to the site. Please contact the board administrator for further details.'
	),
	array(
		0 => 'How do I bump my topic?',
		1 => 'By clicking the “Bump topic” link when you are viewing it, you can “bump” the topic to the top of the forum on the first page. However, if you do not see this, then topic bumping may be disabled or the time frame entered not yet reached. It is also possible to bump the topic simply by replying to it. However, be sure to follow the board rules.'
	),
	array(
		0 => '--',
		1 => 'Formatting and Topic Types'
	),
	array(
		0 => 'What is BBCode?',
		1 => 'BBCode is a special implementation of HTML, whether you can use BBCode is determined by the administrator (you can also disable it on a per post basis from the posting form). BBCode itself is similar in style to HTML, tags are enclosed in square brackets [ and ] rather than &lt; and &gt; and it offers greater control over what and how something is displayed. For more information on BBCode see the guide which can be accessed from the posting page.'
	),
	array(
		0 => 'Can I use HTML?',
		1 => 'No. It is not possible to post HTML on this board and have it rendered as HTML. Most formatting which can be carried out using HTML can also be applied using BBCode instead.'
	),
	array(
		0 => 'What are Smilies?',
		1 => 'Smilies, or Emoticons, are small images which can be used to express some feeling using a short code, e.g. :) means happy, :( means sad. The full list of emoticons can be seen via the posting form. Try not to overuse smilies though, they can quickly render a post unreadable and a moderator may decide to edit them out or remove the post altogether. The board administrator may also have set a limit to the number of such smilies you may use within a post.'
	),
	array(
		0 => 'Can I post images?',
		1 => 'Yes, images can be shown in your posts. However; if the administrator has allowed attachments, you may be able to upload the image to the board. Otherwise, you must link to an image stored on a publicly accessible web server, e.g. http://www.example.com/my-picture.gif. You cannot link to pictures stored on your own PC (unless it is a publicly accessible server) nor images stored behind authentication mechanisms, e.g. hotmail or yahoo mailboxes, password protected sites, etc. To display the image use the BBCode [img] tag.'
	),
	array(
		0 => 'What are global announcements?',
		1 => 'Global announcements contain important information and you should read them as soon as possible. Global announcements will appear at the top of every forum and also within your User Control Panel. Whether or not you can post a global announcement depends on the permissions required, these are set by the administrator.'
	),
	array(
		0 => 'What are announcements?',
		1 => 'Announcements often contain important information for the forum you are currently reading and you should read them as soon as possible. Announcements appear at the top of every page in the forum to which they are posted. As with global announcements, whether or not you can post an announcement depends on the permissions required, these are set by the administrator.'
	),
	array(
		0 => 'What are sticky topics?',
		1 => 'Sticky topics appear below any announcements within the forum and only on the first page. They are often quite important so you should read them where possible. As with announcements the board administrator determines what permissions are required to post sticky topics in each forum.'
	),
	array(
		0 => 'What are locked topics?',
		1 => 'Locked topics are set this way by either the forum moderator or board administrator. You cannot reply to locked topics and any poll it contained is automatically ended. Topics may be locked for many reasons. You may also be able to lock your own topics depending on the permissions you are given by the board administrator.'
	),
	array(
		0 => 'What are topic icons?',
		1 => 'Topic icons are images which can be associated with posts to indicate their content. The ability to use topic icons depends on the permissions set by the administrator.'
	),
	array(
		0 => '--',
		1 => 'User Levels and Groups'
	),
	array(
		0 => 'What are Administrators?',
		1 => 'Administrators are members assigned with the highest level of control over the entire board. These members can control all facets of board operation, including setting permissions, banning users, creating usergroups or moderators, etc., dependant upon the board founder and what permissions he or she has given the other administrators. They may also have full moderator capabilities in all forums, depending on the settings put forth by the board founder.'
	),
	array(
		0 => 'What are Moderators?',
		1 => 'Moderators are individuals (or groups of individuals) who look after the forums from day to day. They have the power to edit or delete posts and lock, unlock, move, delete and split topics in the forum they moderate. Generally, moderators are there to prevent people going <em>off-topic</em> or posting abusive or offensive material.'
	),
	array(
		0 => 'What are usergroups?',
		1 => 'Usergroups are a way in which board administrators can group users. Each user can belong to several groups (this differs from most other boards) and each group can be assigned individual permissions. This makes it easy for administrators to set up several users as moderators of a forum, or to give them access to a private forum, etc.'
	),
	array(
		0 => 'How do I join a usergroup?',
		1 => 'To join a usergroup, click the usergroups link within your User Control Panel; you can then view all usergroups. Not all groups are <em>open access</em>, however. Some may require approval to join, some may be closed and some may even have hidden memberships. If the group is open, you can join it by clicking the appropriate button. If a group requires approval to join you may request to join by clicking the appropriate button. The user group leader will need to approve your request and they may ask why you want to join the group. Do not harass a group leader should they reject your request; they will have their reasons.'
	),
	array(
		0 => 'How do I become a usergroup leader?',
		1 => 'When usergroups are initially created by an administrator, usually a usergroup leader is assigned. If you are interested in creating a usergroup, your first point of contact should be an administrator; try sending a private message.',
	),
	array(
		0 => 'Why do some usergroups appear in a different colour?',
		1 => 'It is possible for the board administrator to assign a colour to the members of a usergroup to make it easy to identify the members of this group.'
	),
	array(
		0 => 'What is a “Default usergroup”?',
		1 => 'If you are a member of more than one usergroup, your default is used to determine which group colour and group rank should be shown for you by default. The board administrator may grant you permission to change your default usergroup via your User Control Panel.'
	),
	array(
		0 => 'What is “The team” link?',
		1 => 'This page provides you with a list of board staff, including board administrators and moderators and other details such as the forums they moderate.'
	),
	array(
		0 => '--',
		1 => 'Private Messaging'
	),
	array(
		0 => 'I cannot send private messages!',
		1 => 'There are three reasons for this; you are not registered and/or not logged on, the board administrator has disabled private messaging for the entire board, or the board administrator has prevented you from sending messages. If it is the latter case you should try asking the administrator why.'
	),
	array(
		0 => 'I keep getting unwanted private messages!',
		1 => 'You may block a user from sending you private messages by using message rules within your User Control Panel. If you are receiving abusive private messages from someone inform the board admin, they have the power to prevent a user from sending private messages at all.'
	),
	array(
		0 => 'I have received a spamming or abusive e-mail from someone on this board!',
		1 => 'We are sorry to hear that. The e-mail form feature of this board includes safeguards to try and track users who send such posts. You should e-mail the board administrator with a full copy of the e-mail you received. It is very important that this includes the headers that contain the details of the user that sent the e-mail. They can then take action.'
	),
	array(
		0 => '--',
		1 => 'Friends and Foes'
	),
	array(
		0 => 'What are my Friends and Foes lists?',
		1 => 'You can use these lists to organise other members of the board. Members added to your friends list will be listed within your User Control Panel for quick access to see their online status and to send them private messages. Subject to template support, posts from these users may also be highlighted. If you add a user to your foes list, any posts they make will be hidden by default.'
	),
	array(
		0 => 'How can I add / remove users to my Friends or Foes list?',
		1 => 'You can add users to your list in two ways. Within each user’s profile, there is a link to add them to either your Friend or Foe list. Alternatively, from your User Control Panel, you can directly add users by entering their member name. You may also remove users from your list using the same page.'
	),
	array(
		0 => '--',
		1 => 'Searching the Forums'
	),
	array(
		0 => 'How can I search a forum or forums?',
		1 => 'By entering a search term in the search box located on the index view, forum view or topic view. Advanced search can be accessed by clicking the “Search” link which is available on all pages on the forum. How to access the search may depend on the style used.'
	),
	array(
		0 => 'Why does my search return no results?',
		1 => 'Your search was probably too vague and included many common terms which are not indexed by phpBB3. Be more specific and use the options available within Advanced search.'
	),
	array(
		0 => 'Why does my search return a blank page!?',
		1 => 'Your search returned too many results for the webserver to handle. Use Advanced search and be more specific in the terms used and forums that are to be searched.'
	),
	array(
		0 => 'How do I search for members?',
		1 => 'Go to the “Members” page and click the “Find a member” link. Once there, fill out the self explanatory options.'
	),
	array(
		0 => 'How can I find my own posts and topics?',
		1 => 'Your own posts can be retrieved either by clicking the “Search user’s posts” within the User Control Panel or via your own profile page. To search for your topics, use the Advanced search page and fill in the various options appropriately.'
	),
	array(
		0 => '--',
		1 => 'Topic Subscriptions and Bookmarks'
	),
	array(
		0 => 'What is the difference between bookmarking and subscribing?',
		1 => 'Bookmarking in phpBB3 is much like bookmarking in your web browser. You aren’t alerted when there’s an update, but you can come back to the topic later. Subscribing, however, will notify you when there is an update to the topic or forum on the board via your preferred method or methods.'
	),
	array(
		0 => 'How do I subscribe to specific forums or topics?',
		1 => 'To subscribe to a specific forum, upon entering the forum you will see a “Subscribe forum” link. To subscribe to a topic, reply to the topic with the subscribe checkbox checked or click the “Subscribe topic” link within the topic itself.'
	),
	array(
		0 => 'How do I remove my subscriptions?',
		1 => 'To remove your subscriptions, go to your User Control Panel and follow the links to your subscriptions.'
	),
	array(
		0 => '--',
		1 => 'Attachments'
	),
	array(
		0 => 'What attachments are allowed on this board?',
		1 => 'Each board administrator can allow or disallow certain attachment types. If you are unsure what is allowed to be uploaded, contact the board administrator for assistance.'
	),
	array(
		0 => 'How do I find all my attachments?',
		1 => 'To find your list of attachments that you have uploaded, go to your User Control Panel and follow the links to the attachments section.'
	),
	array(
		0 => '--',
		1 => 'phpBB 3 Issues'
	),
	array(
		0 => 'Who wrote this bulletin board?',
		1 => 'This software (in its unmodified form) is produced, released and is copyright <a href="http://www.phpbb.com/">phpBB Group</a>. It is made available under the GNU General Public License and may be freely distributed, see the link for more details.'
	),
	array(
		0 => 'Why isn’t X feature available?',
		1 => 'This software was written by and licensed through phpBB Group. If you believe a feature needs to be added then please visit the phpbb.com website and see what phpBB Group have to say. Please do not post feature requests to the board at phpbb.com, the Group uses SourceForge to handle tasking of new features. Please read through the forums and see what, if any, our position may already be for a feature and then follow the procedure given there.'
	),
	array(
		0 => 'Who do I contact about abusive and/or legal matters related to this board?',
		1 => 'Any of the administrators listed on the “The team” page should be an appropriate point of contact for your complaints. If this still gets no response then you should contact the owner of the domain (do a <a href="http://www.google.com/search?q=whois">whois lookup</a>) or, if this is running on a free service (e.g. Yahoo!, free.fr, f2s.com, etc.), the management or abuse department of that service. Please note that the phpBB Group has <strong>absolutely no jurisdiction</strong> and cannot in any way be held liable over how, where or by whom this board is used. Do not contact the phpBB Group in relation to any legal (cease and desist, liable, defamatory comment, etc.) matter <strong>not directly related</strong> to the phpBB.com website or the discrete software of phpBB itself. If you do e-mail phpBB Group <strong>about any third party</strong> use of this software then you should expect a terse response or no response at all.'
	)
);

?>