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

$lang['and'] = "and"; // used within a sentence in various places

$lang['Admin_panel'] = "Go to Administration Panel";

$lang['You'] = "You"; // This is followed by the auth results for a given function (see below)
$lang['can'] = "<b>can</b>";
$lang['cannot'] = "<b>cannot</b>";
$lang['read_posts'] = "read posts in this forum";
$lang['post_topics'] = "post new topics in this forum";
$lang['reply_posts'] = "reply to posts in this forum";
$lang['edit_posts'] = "edit your posts in this forum";
$lang['delete_posts'] = "delete your posts in this forum";
$lang['moderate_forum'] = "moderate this forum";

$lang['View_latest_post'] = "View latest post";

$lang['ICQ'] = "ICQ Number";
$lang['AIM'] = "AIM Address";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Error'] = "Error";

$lang['HERE'] = "HERE";

$lang['IP_Address'] = "IP Address";

$lang['Jump_to'] = "Jump to";
$lang['Select_forum'] = "Select a forum";
$lang['Go'] = "Go";

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

$lang['Mark_all_topics'] = "Mark all topics read";
$lang['Mark_all_forums'] = "Mark all forums read";

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
$lang['Topic_is_locked'] = "Topic is locked";
$lang['Forum_is_locked'] = "Forum is locked";
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
$lang['No_forums'] = "This board has no forums";

$lang['Private_Messages'] = "Private Messages";
$lang['Who_is_Online'] = "Who is Online";

//
// Viewforum
//
$lang['View_forum'] = "View Forum";

$lang['Forum_not_exist'] = "The forum you selected does not exist, please go back and try again";
$lang['Reached_on_error'] = "You have reached this page in error, please go back and try again";

$lang['Display_topics'] = "Display topics from previous";
$lang['All_Topics'] = "All Topics";
$lang['Topic_Announcement'] = "<b>Announcement:</b>";
$lang['Topic_Sticky'] = "<b>Sticky:</b>";

//
// Viewtopic
//
$lang['View_topic'] = "View topic";

$lang['Guest'] = 'Guest';
$lang['Post_subject'] = "Post subject";
$lang['View_next_topic'] = "View next topic";
$lang['View_previous_topic'] = "View previous topic";

$lang['No_newer_topics'] = "There are no newer topics in this forum";
$lang['No_older_topics'] = "There are no older topics in this forum";
$lang['Topic_post_not_exist'] = "The topic or post you requested does not exist";
$lang['Display_posts'] = "Display posts from previous";
$lang['All_Posts'] = "All Posts";
$lang['Newest_First'] = "Newest First";
$lang['Oldest_First'] = "Oldest First";

$lang['Read_profile'] = "Read profile of"; // Followed by username of poster
$lang['Send_email'] = "Send email to "; // Followed by username of poster
$lang['Visit_website'] = "Visit posters website";
$lang['ICQ_status'] = "ICQ Status";
$lang['Edit_delete_post'] = "Edit/Delete this post";
$lang['Reply_with_quote'] = "Reply with quote";
$lang['View_IP'] = "View IP of poster";
$lang['Delete_post'] = "Delete this post";

$lang['Edited_by'] = "Last edited by"; // followed by -> [username] on ...
$lang['on'] = "on";
$lang['edited'] = "edited"; // followed by -> [num] times in total
$lang['time_in_total'] = "time in total";
$lang['times_in_total'] = "times in total";

$lang['Lock_topic'] = "Lock this topic";
$lang['Unlock_topic'] = "Unlock this topic";
$lang['Move_topic'] = "Move this topic";
$lang['Delete_topic'] = "Delete this topic";
$lang['Split_topic'] = "Split this topic";


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
$lang['Post_Announcement'] = "Announcement";
$lang['Post_Sticky'] = "Sticky";
$lang['Topic_Moved'] = "Moved";
$lang['Options'] = "Options";

$lang['Confirm'] = "Confirm";
$lang['Confirm_delete'] = "Are you sure you want to delete this post?";
$lang['Submit_post'] = "Submit Post";
$lang['Preview'] = "Preview";
$lang['Cancel_post'] = "Cancel post";

$lang['Flood_Error'] = "Your last post was less then " . $board_config['flood_interval'] . " seconds ago. You must wait before you post again!";
$lang['Sorry_edit_own_posts'] = "Sorry but you can only edit your own posts";
$lang['Empty_subject'] = "You must specifiy a subject when posting a new topic";
$lang['Empty_message'] = "You must enter a message when posting";
$lang['Announce_and_sticky'] = "You cannot post a topic that is both an announcement and a sticky topic";
$lang['Forum_locked'] = "This forum is locked you cannot post, reply to or edit topics";
$lang['Topic_locked'] = "This topic is locked you cannot edit posts or make replies";
$lang['No_post_id'] = "You must select a post to edit";
$lang['No_topic_id'] = "You must select a topic to reply to";
$lang['No_valid_mode'] = "You can only post, reply edit or quote messages, please return and try again";
$lang['No_such_post'] = "There is no such post, please return and try again";

$lang['Attach_signature'] = "Attach signature (signatures can be changed in profile)";
$lang['Disable'] = "Disable "; // This is followed by a type, eg. HTML, Smilies, etc. and then 'on this post'
$lang['HTML'] = "HTML";
$lang['BBCode'] = "BBCode";
$lang['Smilies'] = "Smilies";
$lang['in_this_post'] = " in this post";
$lang['Notify'] = "Notify me when a reply is posted";
$lang['Delete_post'] = "Delete this post";
$lang['is_ON'] = " is ON"; // this goes after either BBCode or HTML
$lang['is_OFF'] = " is OFF"; // see above

$lang['Stored'] = "Your message has been entered successfully";
$lang['Deleted'] = "Your message has been deleted successfully";
$lang['Click'] = "Click"; // Followed by here and then either return to topic or view message
$lang['Here'] = "Here";
$lang['to_return_forum'] = "to return to the forum";
$lang['to_view_message'] = "to view your message";
$lang['to_return_topic'] = "to return to the topic";

//
// Private Messaging
//
$lang['Private_Messaging'] = "Private Messaging";

$lang['You_have'] = "You have"; // followed by "x new message/s"
$lang['new'] = "new"; // see above
$lang['message'] = "message"; // see above
$lang['messages'] = "messages"; // see above
$lang['No_new_pm'] = "You have no new messages";
$lang['Login_check_pm'] = "Login to check your private messages";

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

$lang['No_messages_folder'] = "You have no messages in this folder";

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
$lang['Viewing_profile_of'] = "Viewing profile of"; // followed by username
$lang['Preferences'] = "Preferences";
$lang['Items_required'] = "Items marked with a * are required unless stated otherwise";
$lang['Registration_info'] = "Registration Information";
$lang['Profile_info'] = "Profile Information";
$lang['Profile_info_warn'] = "This information will be publicly viewable";
$lang['Avatar_panel'] = "Avatar control panel";

$lang['Website'] = "Website";
$lang['From'] = "From";
$lang['Contact'] = "Contact";
$lang['Email_address'] = "Email address";
$lang['Email'] = "Email";
$lang['Private_message'] = "Send Private Message";
$lang['Hidden_email'] = "[ Hidden email address ]";
$lang['Search_user_posts'] = "Search for posts by this user";
$lang['Interests'] = "Interests";
$lang['Occupation'] = "Occupation";

$lang['posts_per_day'] = "posts per day";
$lang['of_total'] = "of total"; // follows percentage of total posts

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
$lang['Board_template'] = "Board Template";
$lang['Board_theme'] = "Board Theme";
$lang['Board_lang'] = "Board Language";
$lang['No_themes'] = "No Themes In database";
$lang['Timezone'] = "Timezone";
$lang['Date_format'] = "Date format";
$lang['Date_format_explain'] = "The syntax used is identical to the PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> function";
$lang['Signature'] = "Signature";
$lang['Signature_explain'] = "This is a block of text that can be added to posts you make. There is a 255 character limit";
$lang['Public_view_email'] = "Always show my Email Address";

$lang['password_if_changed'] = "You only need to supply a password if you want to change it";
$lang['password_confirm_if_changed'] = "You only need to confirm your password if you changed it above";

$lang['is'] = "is"; // follows HTML or BBCode
$lang['are'] = "are"; // follows Smilies
$lang['OFF'] = "OFF";
$lang['ON'] = "ON";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Displays a small graphic image below your details in posts. Only one image can be displayed at a time, its width can be no greater than " . $board_config['avatar_max_width'] . " pixels, a height no greater than " . $board_config['avatar_max_height'] . " pixels and a file size no more than " . (round($board_config['avatar_filesize'] / 1024)) . " kB.";
$lang['Upload_Avatar_file'] = "Upload Avatar from your machine";
$lang['Upload_Avatar_URL'] = "Upload Avatar from a URL";
$lang['Upload_Avatar_URL_explain'] = "Enter the URL of the location containing the Avatar image, it will be copied to this site.";
$lang['Pick_local_Avatar'] = "Select Avatar from the gallery";
$lang['Link_remote_Avatar'] = "Link to off-site Avatar";
$lang['Link_remote_Avatar_explain'] = "Enter the URL of the location containing the Avatar image you wish to link to.";
$lang['Avatar_URL'] = "URL of Avatar Image";
$lang['Select_from_gallery'] = "Select Avatar from gallery";
$lang['Avatar_gallery'] = "Show gallery";

$lang['Delete_Image'] = "Delete Image";
$lang['Current_Image'] = "Current Image";

$lang['Notify_on_privmsg'] = "Notify on Private Message";
$lang['Hide_user'] = "Hide your online status";

$lang['Profile_updated'] = "Your profile has been updated<br /><br />" . $lang['Click_index'];

$lang['Password_mismatch'] = "The passwords you entered did not match";
$lang['Invalid_username'] = "The username you requested has been taken or disallowed";
$lang['Fields_empty'] = "You must fill in the required fields";
$lang['Avatar_filetype'] = "The avatar filetype must be .jpg, .gif or .png";
$lang['Avatar_filesize'] = "The avatar image file size must more than 0 kB and less than " . round($board_config['avatar_filesize'] / 1024) . " kB";
$lang['Avatar_imagesize'] = "The avatar must be less than " . $board_config['avatar_max_width'] . " pixels wide and " . $board_config['avatar_max_height'] . " pixels high";

$lang['Account_added'] = "Thank you for registering, your account has been created. You may now login with your username and password";
$lang['Account_inactive'] = "Your account has been created. However, this forum requires account activation, an activation key has been sent to the email address you provided. Pease check your email for further information";
$lang['Account_active'] = "Your account has now been activated. Thank you for registering";
$lang['Reactivate'] = "Reactivate your account!";

$lang['Welcome_subject'] = "Welcome to " . $board_config['sitename'] . " Forums";

$lang['COPPA'] = "Your account has been created but has to be approved, please check your email for details.";
$lang['Welcome_COPPA'] = "Your account has been created, however in complance with the COPPA act you must print out this page and have you parent or guardian mail it to: <br />" . $lang['Mailing_address'] . "<br />Or fax it to: <br />" . $lang['Fax_info'] . "<br /> Once this information has been received your account will be activated by the administrator and you will receive an email notification.";

//
// Memberslist
//
$lang['Select_sort_method'] = "Select sort method";
$lang['Sort'] = "Sort";
$lang['Top_Ten'] = "Top Ten Posters";
$lang['Ascending'] = "Ascending";
$lang['Descending'] = "Descending";
$lang['Order'] = "Order";

//
// Usergroups
//
$lang['Group_member_details'] = "Group Membership Details";
$lang['Group_member_join'] = "Join a Group";

$lang['Group_Information'] = "Group Information";
$lang['Group_name'] = "Group name";
$lang['Group_description'] = "Group description";
$lang['Group_membership'] = "Group membership";

$lang['Current_memberships'] = "Current memberships";
$lang['Non_member_groups'] = "Non-member groups";
$lang['Memberships_pending'] = "Memberships pending";

$lang['Join_group'] = "Join Group";
$lang['No_group_members'] = "This group has no members";

$lang['This_open_group'] = "This is an open group, click to request membership";
$lang['This_closed_group'] = "This is a closed group, no more users accepted";
$lang['Member_this_group'] = "You are a member of this group";
$lang['Are_group_moderator'] = "You are the group moderator";
$lang['None'] = "None";

$lang['Subscribe'] = "Subscribe";
$lang['Unsubscribe'] = "Unsubscribe";
$lang['View_Information'] = "View Information";

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
$lang['Online_explain'] = "This data is based on users active over the past five minutes";
$lang['No_users_browsing'] = "There are no users currently browsing this forum";
$lang['Location'] = "Location";
$lang['Last_updated'] = "Last Updated";

$lang['Forum_index'] = "Forum index";
$lang['Logging_on'] = "Logging on";
$lang['Posting_message'] = "Posting a message";
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

$lang['You_been_banned'] = "You have been banned from this forum<br />Please contact the webmaster or board administrator for more information";
$lang['No_topics_post_one'] = "There are no posts in this forum<br />Click on the <b>Post New Topic</b> link on this page to post one";
$lang['Board_disable'] = "Sorry but this board is currently unavailable, please try again later";

$lang['General_Error'] = "General Error";
$lang['Critical_Error'] = "Critical Error";
$lang['An_error_occured'] = "An Error Occured";
$lang['A_critical_error'] = "A Critical Error Occured";

$lang['Error_login'] = "Login Failed<br />You have specified an incorrect/inactive username or invalid password, please go back and try again";

$lang['Not_Moderator'] = "You are not a moderator of this forum";
$lang['Not_Authorised'] = "Not Authorised";

//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderator Control Panel";
$lang['Mod_CP_explain'] = "Using the form below you can perform mass moderation operations on this forum. You can lock, unlock, move or delete any number of topics";
$lang['Select'] = "Select";
$lang['Delete'] = "Delete";
$lang['Move'] = "Move";
$lang['Lock'] = "Lock";
$lang['Unlock'] = "Unlock";
$lang['Topics_Removed'] = "The selected topics have been successfully removed from the database.";
$lang['Topics_Locked'] = "The selected topics have been locked";
$lang['Topics_Moved'] = "The selected topics have been moved";
$lang['Topics_Unlocked'] = "The selected topics have been unlocked";
$lang['Return_to_modcp'] = "to return to the moderator control panel";
$lang['Confirm_delete_topic'] = "Are you sure you want to remove the selected topic/s?";
$lang['Confirm_lock_topic'] = "Are you sure you want to lock the selected topic/s?";
$lang['Confirm_unlock_topic'] = "Are you sure you want to unlock the selected topic/s?";
$lang['Confirm_move_topic'] = "Are you sure you want to move the selected topic/s?";
$lang['Split_Topic'] = "Split Topic Control Panel";
$lang['Split_Topic_explain'] = "Using the form below you can split a topic in two, either by selecting the posts individually or by splitting at a selected post";
$lang['Split_title'] = "New topic title";
$lang['Split_forum'] = "Forum for new topic";
$lang['Split_posts'] = "Split selected posts";
$lang['Split_after'] = "Split from selected post";
$lang['Topic_split'] = "The selected topic has been split successfully";
$lang['Too_many_error'] = "You have selected too many posts. You can only select one post to split a topic after!";
$lang['New_forum'] = "New forum";

//
// Timezones ... for display on each page
//
$lang['All_times'] = "All times are"; // This is followed by GMT and the timezone offset

$lang['-12'] = "GMT - 12" . " " . $lang['Hours'];
$lang['-11'] = "GMT - 11" . " " . $lang['Hours'];
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9" . " " . $lang['Hours'];
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4" . " " . $lang['Hours'];
$lang['-3.5'] = "GMT - 3.5" . " " . $lang['Hours'];
$lang['-3'] = "GMT - 3" . " " . $lang['Hours'];
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1" . " " . $lang['Hour'];
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT + 3" . " " . $lang['Hours'];
$lang['3.5'] = "GMT + 3.5" . " " . $lang['Hours'];
$lang['4'] = "GMT + 4" . " " . $lang['Hours'];
$lang['4.5'] = "GMT + 4.5" . " " . $lang['Hours'];
$lang['5'] = "GMT + 5" . " " . $lang['Hours'];
$lang['5.5'] = "GMT + 5.5" . " " . $lang['Hours'];
$lang['6'] = "GMT + 6" . " " . $lang['Hours'];
$lang['7'] = "GMT + 7" . " " . $lang['Hours'];
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9" . " " . $lang['Hours'];
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11" . " " . $lang['Hours'];
$lang['12'] = "GMT + 12" . " " . $lang['Hours'];

//
// Main Admin section/s
//

// Index
$lang['Not_admin'] = "You are not authorised to administer this board";
$lang['Welcome_phpBB'] = "Welcome to phpBB";
$lang['Admin_intro'] = "Thank you for choosing phpBB as your forum solution. This screen will give you a quick overview of all the various statistics of your board. You can get back to this page by clicking on the <u>Admin Index</u> link in the left pane. To return to the index of your board, click the phpBB logo also in the left pane. The other links on the left hand side of this screen will allow you to control every aspect of your forum experience, each screen will have instructions on how to use the tools.";
$lang['Forum_stats'] = "Forum Statistics";

$lang['Statistic'] = "Statistic";
$lang['Value'] = "Value";
$lang['Number_posts'] = "Number of posts";
$lang['Posts_per_day'] = "Posts per day";
$lang['Number_topics'] = "Number of topics";
$lang['Topics_per_day'] = "Topics per day";
$lang['Number_users'] = "Number of users";
$lang['Users_per_day'] = "Users per day";
$lang['Board_started'] = "Board started";
$lang['Avatar_dir_size'] = "Avatar directory size";
$lang['Database_size'] = "Database size";
$lang['Not_available'] = "Not available";

// DB Utils
$lang['Database_Utilities'] = "Database Utilities";
$lang['Restore'] = "Restore";
$lang['Backup'] = "Backup";
$lang['Restore_explain'] = "This will perform a full restore of all phpBB tables from a saved file. If your server supports it you may upload a gzip compressed text file and it will automatically be decompressed. <b>WARNING</b> This will overwrite any existing data. The restore may take a long time to process please do not move from this page till it is complete.";
$lang['Backup_explain'] = "Here you can backup all your phpBB related data. If you have any additional custom tables in the same database with phpBB that you would like to back up as well please enter their names seperated by commas in the Additional Tables textbox below. If your server supports it you may also gzip compress the file to reduce its size before download.";
$lang['Backup_options'] = "Backup options";
$lang['Start_backup'] = "Start Backup";
$lang['Full_backup'] = "Full backup";
$lang['Structure_backup'] = "Structure Only backup";
$lang['Data_backup'] = "Data only backup";
$lang['Additional_tables'] = "Additional tables";
$lang['Gzip_compress'] = "Gzip compress file";
$lang['Select_file'] = "Select a file";
$lang['Start_Restore'] = "Start Restore";
$lang['Restore_success'] = "The Database has been successfully restored.<br /><br />Your board should be back to the state it was when the backup was made.";
$lang['Backup_download'] = "Your download will start shortly please wait till it begins";
$lang['Backups_not_supported'] = "Sorry but database backups are not currently supported for your database system";

$lang['Restore_Error_uploading'] = "Error in uploading the backup file";
$lang['Restore_Error_filename'] = "Filename problem, please try an alternative file";
$lang['Restore_Error_decompress'] = "Cannot decompress a gzip file, please upload a plain text version";
$lang['Restore_Error_no_file'] = "No file was uploaded";

// Auth pages
$lang['Administrator'] = "Administrator";
$lang['User'] = "User";
$lang['Group'] = "Group";
$lang['Forum'] = "Forum";
$lang['Select_a'] = "Select a"; // followed by on the entries above
$lang['Auth_Control'] = "Authorisation Control"; // preceeded by one of the above options
$lang['Look_up'] = "Look up"; // preceeded by one of the above options

$lang['Group_auth_explain'] = "Here you can alter the permissions and moderator status assigned to each user group. Do not forget when changing group permissions that individual user permissions may still allow the user entry to forums, etc. You will be warned if this is the case.";
$lang['User_auth_explain'] = "Here you can alter the permissions and moderator status assigned to each individual user. Do not forget when changing user permissions that group permissions may still allow the user entry to forums, etc. You will be warned if this is the case.";
$lang['Forum_auth_explain'] = "Here you can alter the authorisation levels of each forum. You will have both a simple and advanced method for doing this, advanced offers greater control of each forum operation. Remember that changing the permission level of forums will affect which users can carry out the various operations within them.";

$lang['Simple_mode'] = "Simple Mode";
$lang['Advanced_mode'] = "Advanced Mode";
$lang['Moderator_status'] = "Moderator status";

$lang['Allowed_Access'] = "Allowed Access";
$lang['Disallowed_Access'] = "Disallowed Access";
$lang['Is_Moderator'] = "Is Moderator";
$lang['Not_Moderator'] = "Not Moderator";

$lang['Conflict_warning'] = "Authorisation Conflict Warning";
$lang['Conflict_message_userauth'] = "This user still has access/moderator rights to this forum via group membership. You may want to alter the group authorisation or remove this user the group to fully prevent them having access/moderator rights. The groups granting rights are noted below.";
$lang['Conflict_message_groupauth'] = "The following user/s still have access/moderator rights to this forum via their user auth settings. You may want to alter the user authorisation/s to fully prevent them having access/moderator rights. The users granted rights are noted below.";

$lang['has_moderator_status'] = "has moderator status on";
$lang['has_access_status'] = "has access status to";
$lang['grants_access_status'] = "grants access status to";
$lang['grants_moderator_status'] = "grants moderator status to";
$lang['for_this_user'] = "for this user";

$lang['Submit_changes'] = "Submit changes";
$lang['Reset_changes'] = "Reset changes";

$lang['Public'] = "Public";
$lang['Private'] = "Private";
$lang['Registered'] = "Registered";
$lang['Administrators'] = "Administrators";
$lang['Hidden'] = "Hidden";

$lang['View'] = "View";
$lang['Read'] = "Read";
$lang['Post'] = "Post";
$lang['Reply'] = "Reply";
$lang['Edit'] = "Edit";
$lang['Delete'] = "Delete";
$lang['Sticky'] = "Sticky";
$lang['Announce'] = "Announce";

$lang['Permissions'] = "Permissions";
$lang['Simple_Permission'] = "Simple Permission";

$lang['This_user_is'] = "This user is a"; // followed by User/Administrator and then next line
$lang['and_belongs_groups'] = "and belongs to the following groups"; // followed by list of groups

$lang['Group_has_members'] = "This group has the following members";

$lang['return_group_auth_admin'] = "to return to the group permissions panel";
$lang['return_user_auth_admin'] = "to return to the user permissions panel";


// Banning
$lang['Ban_control'] = "Ban Control";
$lang['Ban_explain'] = "Here you can control the banning of users. You can achieve this by banning either or both of a specific user or an individual or range of IP addresses or hostnames. These methods prevent a user from even reaching the index page of your board. To prevent a user from registering under a different username you can also specify a banned email address. Please note that banning an email address alone will not prevent that user from being able to logon or post to your board, you should use one of the first two methods to achieve this.";
$lang['Ban_explain_warn'] = "Please note that entering a range of IP addresses results in all the addresses between the start and end being added to the banlist. Attempts will be made to minimise the number of addresses added to the database by introducing wildcards automatically where appropriate. If you really must enter a range try to keep it small or better yet state specific addresses.";

$lang['Ban_username'] = "Ban one or more specific users";
$lang['Ban_username_explain'] = "You can ban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser";
$lang['Ban_IP'] = "Ban one or more IP addresses or hostnames";
$lang['IP_hostname'] = "IP addresses or hostnames";
$lang['Ban_IP_explain'] = "To specify several different IP's or hostnames separate them with commas. To specify a range of IP addresses separate the start and end with a hyphen (-), to specify a wildcard use *";
$lang['Ban_email'] = "Ban one or more email addresses";
$lang['Ban_email_explain'] = "To specify more than one email address separate them with commas. To specify a wildcard username use *, for example *@hotmail.com";

$lang['Unban_username'] = "Un-ban one more specific users";
$lang['Unban_username_explain'] = "You can unban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser";
$lang['Unban_IP'] = "Un-ban one or more IP addresses";
$lang['Unban_IP_explain'] = "You can unban multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser";
$lang['Unban_email'] = "Un-ban one or more email addresses";
$lang['Unban_email_explain'] = "You can unban multiple email addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser";

$lang['No_banned_users'] = "No banned users";
$lang['No_banned_ip'] = "No banned IP addresses";
$lang['No_banned_email'] = "No banned email addresses";
$lang['No_unban'] = "Leave list unchanged";

$lang['Ban_update_sucessful'] = "The banlist has been updated sucessfully";


// Configuration
$lang['Config_updated'] = "Forum Configuration Updated Sucessfully";

// Forum Management
$lang['Remove'] = "Remove";
$lang['Action'] = "Action";
$lang['Update_order'] = "Update Order";

// Smiley Management
$lang['smile_remove_err'] = "Error Deleting Smiley!";
$lang['smiley_return'] = "Return to smiley listing";
$lang['smiley_del_success'] = "The smiley was successfully removed!";
$lang['smile_edit_err'] = "Error processing smiley edits";
$lang['smiley_title'] = "Smiles Editing Utility";
$lang['smiley_code'] = "Smiley Code";
$lang['smiley_url'] = "Smiley Image File";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smiley_add_success'] = "The smiley was successfully added!";
$lang['smiley_edit_success'] = "The smiley was successfully updated!";
$lang['smile_load_err'] = "There was an error retrieving the smilies!";
$lang['smile_add'] = "Add a new Smiley";
$lang['smile_desc'] = "Some wonderful text describing the smiley administration here :)";
$lang['smile_instr'] = "There should be some text here with instructions on what the admin should fill in for the smiley fields when editing or adding a smiley.";
$lang['smiley_config'] = "Smiley Configuration";
$lang['Code'] = "Code";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emotion";

// User Management
$lang['User_admin'] = "Administration";
$lang['User_admin_explain'] = "Here you can change your user's information. Do not abuse this power.<br />Deleting users is not provided here, nor is changing admin status. <br />Use the banning and user permission pages respectively.";

//
// End
// -------------------------------------------------

// -------------------------------------------------
// Old format ... _DON'T_add_any_ new entries here!!
//
// Register
$l_mailingaddress =
"
	James Atkinson<br />
	c/o 100World.com Inc.<br />
	512-1529 West 6th Ave.<br />
	Vancouver BC, V6J 1R1<br />
	Canada<br />
";

$l_faxinfo = "
	Mark Fax with:
  ATTN: James Atkinson<br />
	RE: Forum Registration<br />
	<br />
	Fax Number: +1-604-742-1770<br />
";
$l_coppa = "Your account has been created, however in complance with the COPPA act you must print out this page and have you parent or guardian mail it to: <br />$l_mailingaddress<br />Or fax it to: <br />$l_faxinfo<br /> Once this information has been recived your account will be activated by the administrator and you will recive and email notification.";
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

// Editpost
// Newtopic
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

?>
