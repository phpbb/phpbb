<?php
/***************************************************************************
 *                           lang_english.php  -  description
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 *  ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
// The future format of this file will be:
//
// ---> $lang['message'] = "text";
//
// message should be a GOOD representation of text, including capitalisation 
// and underscoring for spacing. Remember different languages often interpret 
// consecutive words in different ways, so if you're building a sentence then
// try and indicate what 'words' follow
//
// The number of phrases should be kept to a minimum so we should try and reuse
// as much as possible.
//

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Forums'] = "Forums";
$lang['Topic'] = "Topic";
$lang['Topics'] = "Topics";
$lang['Reply'] = "Reply";
$lang['Replies'] = "Replies";
$lang['Views'] = "Views";
$lang['Post'] = "Post";
$lang['Posts'] = "Posts";
$lang['Posted'] = "Posted";
$lang['Message'] = "Message";
$lang['Messages'] = "Messages";
$lang['User'] = "User";
$lang['Users'] = "Users";
$lang['Username'] = "Username";
$lang['Password'] = "Password";
$lang['Email'] = "Email";
$lang['Poster'] = "Poster";
$lang['Author'] = "Author";
$lang['is'] = "is";
$lang['are'] = "are";
$lang['by'] = "by";
$lang['All_Topics'] = "All Topics";
$lang['Time'] = "Time";
$lang['Hour'] = "Hour";
$lang['Hours'] = "Hours";
$lang['Day'] = "Day";
$lang['Days'] = "Days";
$lang['Week'] = "Week";
$lang['Weeks'] = "Weeks";
$lang['Month'] = "Month";
$lang['Months'] = "Months";
$lang['Year'] = "Year";
$lang['Years'] = "Years";

$lang['All_times'] = "All times are"; // This is followed by GMT and the timezone offset
$lang['GMT'] = "GMT";

$lang['Next'] = "Next";
$lang['Previous'] = "Previous";
$lang['Goto_page'] = "Goto page";
$lang['Page'] = "Page"; // Followed by the current page number then 'of x' where x is total pages
$lang['Pages'] = "Pages"; 
$lang['of'] = "of"; // See Page above
$lang['Go'] = "Go";

$lang['Submit'] = "Submit";
$lang['Reset'] = "Reset";
$lang['Cancel'] = "Cancel";
$lang['Yes'] = "Yes";
$lang['No'] = "No";

$lang['Private_messaging'] = "Send a Private Message";
$lang['Click_index'] = "Click <a href=\"index.$phpEx\">HERE</a> to return to the index";

//
// Global Header strings
//
$lang['There'] = "There";
$lang['Registered'] = "Registered";
$lang['Guest'] = "Guest";
$lang['Hidden'] = "Hidden";
$lang['None'] = "None";
$lang['online'] = "online";

$lang['You_last_visit'] = "You last visited on";

$lang['Welcome_to'] = "Welcome to"; // Followed by site name
$lang['Register'] = "Register";
$lang['Profile'] = "Profile";
$lang['Search'] = "Search";
$lang['Private_msgs'] = "Private Messages";
$lang['Memberlist'] = "Memberlist";
$lang['FAQ'] = "FAQ";
$lang['Usergroups'] = "Usergroups";
$lang['Last_Post'] = "Last Post";
$lang['Moderator'] = "Moderator/s";

//
// Stats block text
//
$lang['Posted_Total'] = "Our users have posted a total of"; // Number of posts
$lang['We_have'] = "We have"; // # registered users
$lang['Regedusers'] = "Registered users";
$lang['newestuser'] = "The newest Registered User is"; // username
$lang['browsing'] = "browsing";
$lang['arecurrently'] = "There are currently"; // # users browsing
$lang['theforums'] = "the forums";

$lang['No_new_posts'] = "No new posts";
$lang['New_posts'] = "New posts";
$lang['Joined'] = "Joined";

//
// Login
//
$lang['Login'] = "Login";
$lang['Logout'] = "Logout";
$lang['You_are_logged_in'] = "You are logged in as"; // This is followed by the username
$lang['You_are_not_logged_in'] = "You are not logged in";
$lang['Forgotten_password'] = "I forgot my password";
$lang['Log_me_in'] = "Log me on automatically each visit";

//
// Index page
//
$lang['No_Posts'] = "No Posts";
$lang['Forum_Index'] = "Forum Index";

//
// Viewforum
//
$lang['Display_topics'] = "Display Topics from previous";
$lang['Annoucement'] = "<b>Annoucement:</b>";
$lang['Sticky'] = "<b>Sticky:</b>";

//
// Viewtopic
//
$lang['Guest'] = 'Guest';

$lang['Topic_post_not_exist'] = "The topic or post you requested does not exist";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Message body";

$lang['Post_a_new_topic'] = "Post a new topic";
$lang['Post_new_topic_in'] = "Post new topic in:"; // Followed by forum name
$lang['Post_a_reply'] = "Post a reply";
$lang['Post_topic_as'] = "Post topic as";
$lang['Edit_Post'] = "Edit post";
$lang['Post_Normal'] = "Normal";
$lang['Post_Annoucement'] = "Announcement";
$lang['Post_Sticky'] = "Sticky";
$lang['Options'] = "Options";

$lang['Submit_post'] = "Submit Post";
$lang['Preview'] = "Preview";
$lang['Cancel_post'] = "Cancel post";

$lang['Flood_Error'] = "Your last post was less then " . $board_config['flood_interval'] . " seconds ago. You must wait before you post again!";
$lang['Sorry_edit_own_posts'] = "Sorry but you can only edit your own posts";
$lang['Empty_subject'] = "You must specifiy a subject when posting a new topic";
$lang['Empty_message'] = "You must enter a message when posting";
$lang['Annouce_and_sticky'] = "You cannot post a topic that is both an annoucement and a sticky topic";

$lang['Attach_signature'] = "Attach signature (signatures can be changed in profile)";
$lang['Disable'] = "Disable "; // This is followed by a type, eg. HTML, Smilies, etc. and then 'on this post'
$lang['HTML'] = "HTML";
$lang['BBCode'] = "BBCode";
$lang['Smilies'] = "Smilies"; 
$lang['in_this_post'] = " in this post";
$lang['Notify'] = "Notify";
$lang['is_ON'] = " is ON"; // this goes after either BBCode or HTML
$lang['is_OFF'] = " is OFF"; // see above

$lang['Stored'] = "Your message has been entered successfully";
$lang['Click'] = "Click"; // Followed by here and then either return to topic or view message
$lang['Here'] = "Here";
$lang['to_return_forum'] = "to return to the forum";
$lang['to_view_message'] = "to view your message";

//
// Private Messaging
//
$lang['Inbox'] = "Inbox";
$lang['Sent'] = "Sent";
$lang['Outbox'] = "Outbox";
$lang['Saved'] = "Saved";
$lang['Flag'] = "Flag";
$lang['Subject'] = "Subject";
$lang['From'] = "From";
$lang['To'] = "To";
$lang['Date'] = "Date"; 
$lang['Mark'] = "Mark"; 
$lang['Display_messages'] = "Display messages from previous"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "All Messages";

$lang['Cannot_send_privmsg'] = "Sorry but you are not currently allowed to send private messages.";
$lang['No_to_user'] = "You must specify a username to send this message";
$lang['No_such_user'] = "Sorry but no such user exists";

$lang['Message_sent'] = "Your message has been sent";

$lang['to_return_inbox'] = " to return to your Inbox"; // This follows a "Click HERE ... "
$lang['to_return_index'] = " to return to the Forum Index"; // This follows a "Click HERE ... "

$lang['Re'] = "Re"; // Re as in 'Response to'

$lang['Send_a_new_message'] = "Send a new private message";
$lang['Send_a_reply'] = "Reply to a private message";
$lang['Edit_message'] = "Edit private message";

$lang['Notification_subject'] = "New Private Message has arrived";
$lang['Notification_email'] = "Hello " . $to_userdata['username'] . "\n\n, You have received a new private message on your account at " . $board_config['sitename'] . ". To view it immediately click the following link " . $pm_url . ", you may of course visit the site later your message will be stored in your Inbox.\n";

$lang['Find_username'] = "Find a username";
$lang['Find'] = "Find";
$lang['No_match'] = "No matches found";

$lang['No_post_id'] = "No post ID was specified";
$lang['No_such_folder'] = "No such folder exists";
$lang['No_folder'] = "No folder specified";


//
// Profiles/Registration
//
$lang['Website'] = "Website";
$lang['From'] = "From";

$lang['Wrong_Profile'] = "You cannot modify a profile that is not your own.";
$lang['Bad_username'] = "The username you choose has been taken or is disallowed by the administrator.";
$lang['Sorry_banned_email'] = "Sorry but the email address you gave has been banned from registering on this system.";
$lang['Only_one_avatar'] = "Only one type of avatar can be specified";
$lang['File_no_data'] = "The file at the URL you gave contains no data";
$lang['No_connection_URL'] = "A connection could not be made to the URL you gave";
$lang['Incomplete_URL'] = "The URL you entered is incomplete";

$lang['Always_smile'] = "Always enable Smilies";
$lang['Always_html'] = "Always allow HTML";
$lang['Always_bbcode'] = "Always allow BBCode";
$lang['Always_add_sig'] = "Always attach my signature";
$lang['Board_theme'] = "Board Theme";
$lang['Board_lang'] = "Board Language";
$lang['No_themes'] = "No Themes In database";
$lang['Timezone'] = "Timezone";

$lang['Avatar_panel'] = "Avatar control panel";
$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Displays a small graphic image below your details in posts. Only one image can be displayed at a time, its width can be no greater than " . $board_config['avatar_max_width'] . " pixels, a height no greater than " . $board_config['avatar_max_height'] . " pixels and a file size no more than " . (round($board_config['avatar_filesize'] / 1024)) . " kB.";
$lang['Upload_Avatar_file'] = "Upload Avatar from your machine";
$lang['Upload_Avatar_URL'] = "Upload Avatar from a URL";
$lang['Upload_Avatar_URL_explain'] = "Enter the URL of the location containing the Avatar image, it will be copied to this site.";
$lang['Pick_local_Avatar'] = "Select Avatar from the gallery";
$lang['Link_remote_Avatar'] = "Link to off-site Avatar";
$lang['Link_remote_Avatar_explain'] = "Enter the URL of the location containing the Avatar image you wish to link to.";
$lang['Avatar_URL'] = "URL of Avatar Image";
$lang['Avatar_gallery'] = "Show gallery of Avatar images";

$lang['Delete_Image'] = "Delete Image";
$lang['Current_Image'] = "Current Image";

$lang['Notify_on_privmsg'] = "Notify on Private Message";
$lang['Hide_user'] = "Hide your online status";

$lang['Profile_updated'] = "Your profile has been updated<br><br>" . $lang['Click_index'];

$lang['Password_mismatch'] = "The passwords you entered did not match";
$lang['Invalid_username'] = "The username you requested has been taken or disallowed";
$lang['Fields_empty'] = "You must fill in the required fields";
$lang['Avatar_filetype'] = "The avatar filetype must be .jpg, .gif or .png";
$lang['Avatar_filesize'] = "The avatar image file size must more than 0 kB and less than " . round($board_config['avatar_filesize'] / 1024) . " kB";
$lang['Avatar_imagesize'] = "The avatar must be less than " . $board_config['avatar_max_width'] . " pixels wide and " . $board_config['avatar_max_height'] . " pixels high";

$lang['Account_added'] = "Thank you for registering, your account has been created. You may now login with your username and password";
$lang['Account_inactive'] = "Your account has been created. However, this forum requires account activation, an activation key has been sent to the email address you provided. Pease check your email for further information";

$lang['Welcome_subject'] = "Welcome to " . $board_config['sitename'] . " Forums";

$lang['Welcome_email_activate'] = $lang['Welcome_subject'] . ",

Please keep this email for your records.

Your account information is as follows:

----------------------------
Username: $username
Password: $password
----------------------------

Your account is currently inactive. You cannot use it until you visit the following link:

http://$SERVER_NAME$PHP_SELF?mode=activate&act_key=$act_key

Please do not forget your password as it has been encrypted in our database and we cannot retrieve it for you.
However, should you forget your password you can request a new one which will be activated in the same way as this account.

Thank you for registering.

"; // END
$lang['Welcome_email'] = $lang['Welcome_subject'] .  ",

Please keep this email for your records.

Your account information is as follows:

----------------------------
Username: $username
Password: $password
----------------------------

Please do not forget your password as it has been encrypted in our database and we cannot retrieve it for you.
However, should you forget your password we provide an easy to use script to generate and mail a new, random, password.

Thank you for registering.

"; // END

$lang['COPPA'] = "Your account has been created but has to be approved, please check your email for details.";
$lang['Welcome_COPPA'] = "Your account has been created, however in complance with the COPPA act you must print out this page and have you parent or guardian mail it to: <br>" . $lang['Mailing_address'] . "<br>Or fax it to: <br>" . $lang['Fax_info'] . "<br> Once this information has been received your account will be activated by the administrator and you will receive an email notification.";

//
// Memberslist
//
$lang['Top10'] = "List Top Ten posters";
$lang['Alphabetical'] = "Sort Alphabetically";

//
// Search <= Should be blank for now
//
$lang['Search_for_any'] = "Search for any terms or use query as entered";
$lang['Search_for_all'] = "Search for all terms";
$lang['Search_author'] = "Search for Author";
$lang['Limit_chars'] = "Limit charaters returned to";
$lang['Sort_by'] = "Sort by";
$lang['Sort_Ascending'] = "Sort Ascending";
$lang['Sort_Decending'] = "Sort Descending";
$lang['All'] = "All";

//
// Topic Admin <= Should be blank for now
//

//
// Auth related entries
//
$lang['Sorry_auth'] = "Sorry but only "; // This is followed by the auth type, eg. Registered and then one or more of the following entries

$lang['Anonymous_Users'] = "Anonymous Users";
$lang['Registered_Users'] = "Registered Users";
$lang['Users_granted_access'] = "Users granted special access";
$lang['Moderators'] = "Moderators";
$lang['Administrators'] = "Administrators";

$lang['can_read'] = " can read";
$lang['can_post_announcements'] = " can post announcements in";
$lang['can_post_sticky_topics'] = " can post sticky topics in";
$lang['can_post_new_topics'] = " can post new topics in";
$lang['can_reply_to_topics'] = " can reply to topics in";
$lang['can_edit_topics'] = " can edit topics in";
$lang['can_delete_topics'] = " can delete topics in";

$lang['this_forum'] = " this forum";

//
// Viewonline
//
$lang['Who_is_online'] = "Who is online";
$lang['Location'] = "Location";
$lang['Last_updated'] = "Last Updated";

$lang['Forum_index'] = "Forum index";
$lang['Loggin_on'] = "Logging on";
$lang['Searching_forums'] = "Searching forums";
$lang['Viewing_profile'] = "Viewing profile";
$lang['Viewing_online'] = "Viewing who is online";
$lang['Viewing_member_list'] = "Viewing member list";
$lang['Viewing_priv_msgs'] = "Viewing Private Messages";
$lang['Viewing_FAQ'] = "Viewing FAQ";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Information";
$lang['Critical_Information'] = "Critical Information";

$lang['You_been_banned'] = "You have been banned from this forum<br>Please contact the webmaster or board administrator for more information";
$lang['No_topics_post_one'] = "There are no posts in this forum<br>Click on the <b>Post New Topic</b> link on this page to post one";
$lang['Board_disable'] = "Sorry but this board is currently unavailable, please try again later";

$lang['General_Error'] = "General Error";
$lang['Critical_Error'] = "Critical Error";
$lang['An_error_occured'] = "An Error Occured";
$lang['A_critical_error'] = "A Critical Error Occured";

$lang['Error_login'] = "Login Failed<br>You have specified an incorrect/inactive username or invalid password, please go back and try again";

$lang['Not_Moderator'] = "You are not a moderator of this forum";
$lang['Not_Authorised'] = "Not Authorised";

//
// Moderator Control Panel
//
$lang['ModCp_Explain'] = "Using the form below you can preform mass operations on this forum. You can lock, unlock, move, or delete any number of topics";
$lang['Select'] = "Select";
$lang['Delete'] = "Delete";
$lang['Move'] = "Move";
$lang['Lock'] = "Lock";
$lang['Unlock'] = "Unlock";

//
// Old format ... _DON'T_add_any_ new entries here!!
//
$l_forum 	= "Forum";
$l_forums	= "Forums";
$l_topic	= "Topic";
$l_topics 	= "Topics";
$l_replies	= "Replies";
$l_poster	= "Poster";
$l_author	= "Author";
$l_views	= "Views";
$l_post 	= "Post";
$l_posts 	= "Posts";
$l_message	= "Message";
$l_messages	= "Messages";
$l_subject	= "Subject";
$l_body		= "$l_message Body";
$l_from		= "From";   // Message from
$l_moderator 	= "Moderator/s";
$l_username 	= "Username";
$l_password 	= "Password";
$l_email 	= "Email";
$l_emailaddress	= "Email Address";
$l_preferences	= "Preferences";
$l_welcometo    = "Welcome to";
$l_There = "There";
$l_is = "is";
$l_are = "are";
$l_Registered = "Registered";
$l_online = "online";
$l_users = "users";
$l_user = "user";
$l_and = "and";
$l_None = "None";
$l_log_me_in = "Log me in automatically";
$l_all_times = "All times are";
$l_hours = "hours";

$l_All_Topics = "All Topics";
$l_Day = "Day";
$l_Days = "Days";
$l_Week = "Week";
$l_Weeks = "Weeks";
$l_Month = "Month";
$l_Months = "Months";
$l_Year = "Year";

$l_anonymous	= "Anonymous";  // Post
$l_guest	= "Guest"; // Whosonline
$l_noposts	= "No $l_posts";
$l_joined	= "Joined";
$l_gotopage	= "Goto page";
$l_nextpage 	= "Next Page";
$l_prevpage     = "Previous Page";
$l_go		= "Go";
$l_selectforum	= "Select a $l_forum";

$l_date		= "Date";
$l_number	= "Number";
$l_name		= "Name";
$l_options 	= "Options";
$l_submit	= "Submit";
$l_confirm 	= "Confirm";
$l_enter 	= "Enter";
$l_by		= "by"; // Posted by
$l_ondate	= "on"; // This message is edited by: $username on $date
$l_new          = "New";

$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "Smilies";
$l_on		= "On";
$l_off		= "Off";
$l_yes		= "Yes";
$l_no		= "No";

$l_click 	= "Click";
$l_here 	= "here";
$l_toreturn	= "to return";
$l_returnindex	= "$l_toreturn to the forum index";
$l_returntopic	= "$l_toreturn to the forum topic list.";

$l_error	= "Error";
$l_dberror = "A database error occured, please try again later.";
$l_tryagain	= "Please go back and try again.";
$l_mismatch 	= "Passwords do not match.";
$l_userremoved 	= "This user has been removed from the User database";
$l_wrongpass	= "You entered the wrong password.";
$l_userpass	= "Please enter your username and password.";
$l_banned 	= "You have been banned from this forum. Contact the system administrator if you have any questions.";
$l_enterpassword= "You must enter your password.";

$l_nopost	= "You do not have access to post to this forum.";
$l_noread	= "You do not have access to read this forum.";

$l_lastpost 	= "Last $l_post";
$l_sincelast	= "since your last visit";
$l_newposts 	= "New $l_posts $l_sincelast";
$l_nonewposts 	= "No New $l_posts $l_sincelast";

// Index page
$l_indextitle	= "Forum Index";

// Members and profile
$l_reginfo	= "Registration Information";
$l_profile_info = "Profile Information";
$l_profile_info_notice = "This information will be publicly viewable";
$l_profile	= "Profile";
$l_register	= "Register";
$l_onlyreq 	= "Only requried if being changed";
$l_location 	= "From";
$l_view_users_posts	= "View posts by this user";
$l_per_day       = "$l_messages per day";
$l_of_total      = "of total";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icq_number	= "ICQ Number";
$l_icqadd	= "Add";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger 	= "MSN Messenger";
$l_website 	= "Web Site Address";
$l_occupation 	= "Occupation";
$l_interests 	= "Interests";
$l_signature 	= "Signature";
$l_sigexplain 	= "This is a block of text that can be added to posts you make. There is a 255 character limit";
$l_usertaken	= "The $l_username you picked has been taken.";
$l_userdisallowed= "The $l_username you picked has been disallowed by the administrator. $l_tryagain";
$l_infoupdated	= "Your Information has been updated";
$l_publicmail	= "Allow other users to view my $l_emailaddress";
$l_itemsreq	= "Items marked with a * are required unless stated otherwise";
$l_nouserid = "You must supply a user ID number in order to view profile data.";
$l_viewingprofile = "Viewing profile of ";
$l_hidden = "hidden";
$l_boardtemplate = "Select Template";
$l_date_format = "Date format";


$l_date_format_explanation = "The syntax used is identical to the PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> function";
$l_password_if_changed = "You only need to supply a password if you want to change it.";
$l_password_confirm_if_changed = "You only need to confirm your password if you changed it above.";

$l_top10 = "Top 10 Posters";
$l_alpha = "Sorta Alphabetical";

// Viewforum
$l_viewforum	= "View Forum";
$l_notopics	= "There are no topics for this forum. You can post one.";
$l_hotthres	= "More then $hot_threshold $l_posts";
$l_islocked	= "$l_topic is Locked (No new $l_posts may be made in it)";
$l_moderatedby	= "Moderated by";

$l_Days = "Day/s";
$l_All_posts = "All posts";

// Private forums
$l_privateforum	= "This is a <b>Private Forum</b>.";
$l_private 	= "$l_privateforum<br>Note: you must have cookies enabled in order to use private forums.";
$l_noprivatepost = "$l_privateforum You do not have access to post to this forum.";

// Viewtopic
$l_topictitle	= "View $l_topic";
$l_unregistered	= "Unregistered User";
$l_posted	= "Posted";
$l_profileof	= "View Profile of";
$l_viewsite	= "Goto the website of";
$l_icqstatus	= "$l_icq status";  // ICQ status
$l_editdelete	= "Edit/Delete This $l_post";
$l_replyquote	= "Reply with quote";
$l_viewip	= "View Posters IP (Moderators/Admins Only)";
$l_locktopic	= "Lock this $l_topic";
$l_unlocktopic	= "Unlock this $l_topic";
$l_movetopic	= "Move this $l_topic";
$l_deletetopic	= "Delete this $l_topic";
$l_nomoretopics = "There are no more topics in this view.";

// Functions
$l_loggedinas	= "Logged in as";
$l_notloggedin	= "Not logged in";
$l_logout	= "Logout";
$l_login	= "Login";

// Page_header
$l_separator	= "» »";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "Edit Profile";
$l_editprefs	= "Edit $l_preferences";
$l_search	= "Search";
$l_memberslist	= "Memberslist";
$l_faq		= "FAQ";
$l_privmsgs	= "Private $l_messages";
$l_sendpmsg	= "Send a Private Message";
$l_postedtotal  =
$l_wehave	= "We have";
$l_regedusers	= "registered users.";
$l_newestuser	= "The newest Registered User is";
$l_browsing	= "browsing";
$l_arecurrently = "There are currently";
$l_theforums	= "the forums.";

$l_statsblock   = '$statsblock = "Our users have posted a total of -$total_posts- $l_messages.<br>
We have -$total_users- Registered Users.<br>
The newest Registered User is -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"user is":"users are") ." <a href=\"$online_url\">currently browsing</a> the forums.<br>";';
$l_privnotify   = '$privnotify = "<br>You have $new_message <a href=\"$privmsg_url\">new private ".($new_message>1?"messages":"message")."</a>.";';

// Page_tail
$l_adminpanel	= "Administration Panel";
$l_poweredby	= "Powered by";
$l_version	= "Version";

// Auth

// Register
$l_acountadded = "Thank you for registering with $sitename. Your account has been successfully created.";
$l_nowactive = "Your account is now been activated. You may login and post with this account. Thank you for using $sitename forums.";
$l_notfilledin	= "Error - you did not fill in all the required fields.";
$l_invalidname	= "The username you chose \"$username\" has been taken or has been disallowed by the administrator.";

$l_mailingaddress =
"
	James Atkinson<br>
	c/o 100World.com Inc.<br>
	512-1529 West 6th Ave.<br>
	Vancouver BC, V6J 1R1<br>
	Canada<br>
";

$l_faxinfo = "
	Mark Fax with:
  ATTN: James Atkinson<br>
	RE: Forum Registration<br>
	<br>
	Fax Number: +1-604-742-1770<br>
";
$l_coppa = "Your account has been created, however in complance with the COPPA act you must print out this page and have you parent or guardian mail it to: <br>$l_mailingaddress<br>Or fax it to: <br>$l_faxinfo<br> Once this information has been recived your account will be activated by the administrator and you will recive and email notification.";
$l_welcomesubj	= "Welcome to ".$board_config['sitename']." Forums";
$l_welcomemail	= "
$l_welcomesubj,

Please keep this email for your records.


Your account information is as follows:

----------------------------
Username: $username
Password: $password
----------------------------

Please do not forget your password as it has been encrypted in our database and we cannot retrieve it for you.
However, should you forget your password we provide an easy to use script to generate and email a new, random, password.

Thank you for registering.

";


$l_beenadded	= "You have been added to the database.";
$l_thankregister= "Thank you for registering!";
$l_useruniq	= "Must be unique. No two users can have the same Username.";
$l_storecookie	= "Store my username in a cookie for 1 year";

// Prefs
$l_prefupdated	= "$l_preferences updated. $l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex";
$l_editprefs	= "Edit Your $l_preferences";
$l_alwayssig	= "Always attach my signature";
$l_alwayssmile	= "Always enable $l_smilies";
$l_alwayshtml	= "Always enable $l_html";
$l_alwaysbbcode	= "Always enable $l_bbcode";
$l_boardtheme	= "Board Theme";
$l_boardlang    = "Board Language";
$l_nothemes	= "No Themes In database";
$l_saveprefs	= "Save $l_preferences";
$l_timezone		= "Timezone";

// Viewonline
$l_whosonline	= "Who is Online";
$l_nousers	= "No Users are currently browsing the forums";
$l_forum_location = "Location in the forum";
$l_last_updated = "Last Updated";
$l_forum_index = "Forum Index";
$l_loggin_on = "Logging on";
$l_searching = "Searching the forum";
$l_registering = "Registering";
$l_viewing_profiles = "Viewing member profiles";
$l_viewing_members = "Viewing member list";
$l_altering_profile = "Altering their profile";
$l_viewing_online = "Viewing who is online";
$l_viewing_faq = "Viewing the board FAQ";

// Editpost
$l_editpost = "Edit Post";
$l_editpostin = "Editing post in:";
$l_notedit	= "You can't edit a post that isn't yours.";
$l_permdeny	= "You did not supply the correct $l_password or do not have permission to edit this post. $l_tryagain";
$l_editedby	= "This $l_message was edited by:";
$l_stored	= "Your $l_message has been stored in the database.";
$l_viewmsg	= "to view your $l_message.";
$l_deleted	= "Your $l_post has been deleted.";
$l_nouser	= "That $l_username doesn't exist.";
$l_passwdlost	= "I forgot my password!";
$l_delete	= "Delete this Post";

$l_disable	= "Disable";
$l_onthispost	= "on this Post";

$l_htmlis	= "$l_html is";
$l_bbcodeis	= "$l_bbcode is";

$l_notify	= "Notify by email when replies are posted";

$l_flooderror = "Your last post was less then ".$board_config['flood_interval']." seconds ago. You must wait befor you post again!";


// Newtopic
$l_postnew      = "Post New Topic";
$l_postnewin    = "Post New Topic in:";
$l_emptymsg	= "You must type a $l_message to post. You cannot post an empty $l_message.";
$l_emptysubj = "You must enter a $l_subject to post a new topic. You cannot post a new topic without a subject.";
$l_aboutpost	= "About Posting";
$l_regusers	= "All <b>Registered</b> users";
$l_anonusers	= "<b>Anonymous</b> users";
$l_modusers	= "Only <B>Moderators and Administrators</b>";
$l_anonhint	= "<br>(To post anonymously simply do not enter a username and password)";
$l_inthisforum	= "can post new topics and replies to this forum";
$l_attachsig	= "Show signature (This can be altered or added in your profile)";
$l_cancelpost	= "Cancel Post";
$l_preview      = "Preview Post";
// Reply
$l_postreplyto = "Post reply in:";
$l_nopostlock	= "You cannot post a reply to this topic, it has been locked.";
$l_topicreview  = "Topic Review";
$l_notifysubj	= "A reply to your topic has been posted.";
$l_notifybody	= 'Dear $m[username]\r\nYou are receiving this Email because a message
you posted on $sitename forums has been replied to, and
you selected to be notified on this event.

You may view the topic at:

http://$SERVER_NAME$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum

Or view the $sitename forum index at

http://$SERVER_NAME$url_phpbb

Thank you for using $sitename forums.

Have a nice day.

';


$l_quotemsg	= '[quote]\nOn $m[post_time], $m[username] wrote:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "You must enter the username you want to send the $l_message to.";
$l_sendothermsg	= "Send another Private Message";
$l_cansend	= "can send $l_privmsgs";  // All registered users can send PM's
$l_yourname	= "Your $l_username";
$l_recptname	= "Recipient $l_username";

// Replypmsg
$l_pmposted	= "Reply Posted, you can click <a href=\"viewpmsg.$phpEx\">here</a> to view your $l_privmsgs";

// Delpmsg
$l_deletesucces	= "Deletion successful.";

// Smilies
$l_smilesym	= "What to type";
$l_smileemotion	= "Emotion";
$l_smilepict	= "Picture";

// Sendpasswd
$l_wrongactiv	= "The activation key you provided is not correct. Please check email $l_message you recived and make sure you have copied the activation key exactly.";
$l_passchange	= "Your password has been successfully changed. You may now goto your <a href=\"bb_profile.$phpEx?mode=edit\">profile</a> and change your password to a more suitable one.";
$l_wrongmail	= "The email address you entered does not match the one stored in our database.";

$l_passsubj	= "$sitename Forums Password Change";

$l_pwdmessage	= "Dear $checkinfo[username],
You are receiving this email because you (or someone pretending to be you)
has requested a passwordchange on $sitename forums. If you believe you have
received this message in error simply delete it and your password will remain
the same.

Your new password as generated by the forums is: $newpw

In order for this change to take effect you must visit this page:

   http://$SERVER_NAME$PHP_SELF?actkey=$key

Once you have visited the page your password will be changed in our database,
and you may login to the profile section and change it as desired.

Thank you for using $sitename Forums

";

$l_passsent	= "Your password has changed to a new, random, password. Please check your email on how to complete the password change procedure.";
$l_emailpass	= "Email Lost Password";
$l_passexplain	= "Please fill out the form, a new password will be sent to your Email address";
$l_sendpass	= "Send Password";

// Full page login
$l_autologin = "Log me on automatically each visit";
$l_resend_password = "I have forgotten my password";

?>