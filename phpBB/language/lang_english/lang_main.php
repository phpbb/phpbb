<?php
/***************************************************************************
 *                            lang_main.php [English]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
// The format of this file is:
//
// ---> $lang['message'] = "text";
//
// You should also try to set a locale and a character
// encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may
// not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

setlocale(LC_ALL, "en");
$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Forums'] = "Forums";
$lang['Category'] = "Category";
$lang['Categories'] = "Categories";
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
$lang['Enabled'] = "Enabled";
$lang['Next'] = "Next";
$lang['Previous'] = "Previous";
$lang['Goto_page'] = "Goto page";
$lang['Page'] = "Page"; // Followed by the current page number then 'of x' where x is total pages
$lang['Pages'] = "Pages";
$lang['IP_Address'] = "IP Address";

$lang['View_latest_post'] = "View latest post";
$lang['Page_of'] = "Page <b>%d</b> of <b>%d</b>"; // Replaces with: Page 1 of 2 for example
$lang['Page'] = "Page"; // Followed by the current page number then 'of x' where x is total pages
$lang['Pages'] = "Pages";
$lang['of'] = "of"; // See Page above

$lang['Submit'] = "Submit";
$lang['Reset'] = "Reset";
$lang['Cancel'] = "Cancel";
$lang['Yes'] = "Yes";
$lang['No'] = "No";
$lang['Go'] = "Go";
$lang['Joined'] = "Joined";

$lang['Admin_panel'] = "Go to Administration Panel";

$lang['ICQ'] = "ICQ Number";
$lang['AIM'] = "AIM Address";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Error'] = "Error";

$lang['Jump_to'] = "Jump to";
$lang['Select_forum'] = "Select a forum";

$lang['Success'] = "Success";
$lang['Private_messaging'] = "Send a Private Message";


//
// Global Header strings
//
$lang['Registered_users'] = "Registered Users:";
$lang['Online_users'] = "In total there are %d users online :: %d Registered, %d Hidden and %d Guests";
$lang['Online_user'] = "In total there is %d user online :: %d Registered, %d Hidden and %d Guests";

$lang['You_last_visit'] = "You last visited on";
$lang['Add'] = "Add";
$lang['Register'] = "Register";
$lang['Profile'] = "Profile";
$lang['Edit_profile'] = "Edit your profile";
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
$lang['Posted_total'] = "Our users have posted a total of <b>%d</b> articles"; // Number of posts
$lang['Registered_user_total'] = "We have <b>%d</b> registered user"; // # registered users
$lang['Registered_users_total'] = "We have <b>%d</b> registered users"; // # registered users
$lang['Newest_user'] = "The newest registered user is <b>%s%s%s</b>"; // username

$lang['No_new_posts'] = "No new posts";
$lang['New_posts'] = "New posts";
$lang['New_post'] = "New post";
$lang['No_new_posts_hot'] = "No new posts [ Popular ]";
$lang['New_posts_hot'] = "New posts [ Popular ]";
$lang['Topic_is_locked'] = "Topic is locked";
$lang['Forum_is_locked'] = "Forum is locked";


//
// Login
//
$lang['Login'] = "Login";
$lang['Logout'] = "Logout";
$lang['Forgotten_password'] = "I forgot my password";
$lang['Log_me_in'] = "Log me on automatically each visit";


//
// Index page
//
$lang['No_Posts'] = "No Posts";
$lang['Forum_Index'] = "Forum Index";
$lang['No_forums'] = "This board has no forums";

$lang['Private_Message'] = "Private Message";
$lang['Private_Messages'] = "Private Messages";
$lang['Who_is_Online'] = "Who is Online";

$lang['Forums_marked_read'] = "All forums have been marked read";


//
// Viewforum
//
$lang['View_forum'] = "View Forum";

$lang['Forum_not_exist'] = "The forum you selected does not exist";
$lang['Reached_on_error'] = "You have reached this page in error";

$lang['Display_topics'] = "Display topics from previous";
$lang['All_Topics'] = "All Topics";
$lang['Topic_Announcement'] = "<b>Announcement:</b>";
$lang['Topic_Sticky'] = "<b>Sticky:</b>";
$lang['Topic_Moved'] = "<b>Moved:</b>";
$lang['Topic_Poll'] = "<b>[ Poll ]</b>";

$lang['View_newest_posts'] = "View posts since your last visit";
$lang['Topics_marked_read'] = "The topics for this forum have now been marked read";

$lang['Rules_post_can'] = "You <b>can</b> post new topics in this forum";
$lang['Rules_post_cannot'] = "You <b>cannot</b> post new topics in this forum";
$lang['Rules_reply_can'] = "You <b>can</b> reply to topics in this forum";
$lang['Rules_reply_cannot'] = "You <b>cannot</b> reply to topics in this forum";
$lang['Rules_edit_can'] = "You <b>can</b> edit your posts in this forum";
$lang['Rules_edit_cannot'] = "You <b>cannot</b> edit your posts in this forum";
$lang['Rules_delete_can'] = "You <b>can</b> delete posts in this forum";
$lang['Rules_delete_cannot'] = "You <b>cannot</b> delete posts in this forum";
$lang['Rules_vote_can'] = "You <b>can</b> vote in polls in this forum";
$lang['Rules_vote_cannot'] = "You <b>cannot</b> vote in polls in this forum";
$lang['Rules_moderate'] = "You <b>can</b> %smoderate this forum%s"; // %s replaced by a href 

//
// Viewtopic
//
$lang['View_topic'] = "View topic";

$lang['Guest'] = 'Guest';
$lang['Post_subject'] = "Post subject";
$lang['View_next_topic'] = "View next topic";
$lang['View_previous_topic'] = "View previous topic";
$lang['Submit_vote'] = "Submit Vote";
$lang['View_results'] = "View Results";

$lang['No_newer_topics'] = "There are no newer topics in this forum";
$lang['No_older_topics'] = "There are no older topics in this forum";
$lang['Topic_post_not_exist'] = "The topic or post you requested does not exist";
$lang['No_posts_topic'] = "No posts exist for this topic";

$lang['Display_posts'] = "Display posts from previous";
$lang['All_Posts'] = "All Posts";
$lang['Newest_First'] = "Newest First";
$lang['Oldest_First'] = "Oldest First";

$lang['Return_to_top'] = "Return to top";

$lang['Read_profile'] = "Read profile of"; // Followed by username of poster
$lang['Send_email'] = "Send email to "; // Followed by username of poster
$lang['Visit_website'] = "Visit posters website";
$lang['ICQ_status'] = "ICQ Status";
$lang['Edit_delete_post'] = "Edit/Delete this post";
$lang['Reply_with_quote'] = "Reply with quote";
$lang['View_IP'] = "View IP of poster";
$lang['Delete_post'] = "Delete this post";

$lang['Edited_time_total'] = "Last edited by %s on %s, edited %d time in total"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Last edited by %s on %s, edited %d times in total"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Lock this topic";
$lang['Unlock_topic'] = "Unlock this topic";
$lang['Move_topic'] = "Move this topic";
$lang['Delete_topic'] = "Delete this topic";
$lang['Split_topic'] = "Split this topic";

$lang['Stop_watching_topic'] = "Stop watching this topic";
$lang['Start_watching_topic'] = "Watch this topic for replies";
$lang['No_longer_watching'] = "You are no longer watching this topic";
$lang['You_are_watching'] = "You are now watching this topic";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Message body";
$lang['Topic_review'] = "Topic review";

$lang['No_post_mode'] = "No post mode specified";

$lang['Post_a_new_topic'] = "Post a new topic";
$lang['Post_new_topic_in'] = "Post new topic in:"; // Followed by forum name
$lang['Post_a_reply'] = "Post a reply";
$lang['Post_topic_as'] = "Post topic as";
$lang['Edit_Post'] = "Edit post";
$lang['Post_Normal'] = "Normal";
$lang['Post_Global_Announcement'] = "Global Announcement";
$lang['Post_Announcement'] = "Announcement";
$lang['Post_Sticky'] = "Sticky";
$lang['Options'] = "Options";

$lang['Confirm'] = "Confirm";
$lang['Confirm_delete'] = "Are you sure you want to delete this post?";
$lang['Confirm_delete_poll'] = "Are you sure you want to delete this poll?";
$lang['Submit_post'] = "Submit Post";
$lang['Preview'] = "Preview";
$lang['Cancel_post'] = "Cancel post";

$lang['Flood_Error'] = "You cannot make another post so soon after your last, please try again in a short while";
$lang['Empty_subject'] = "You must specifiy a subject when posting a new topic";
$lang['Empty_message'] = "You must enter a message when posting";
$lang['Announce_and_sticky'] = "You cannot post a topic that is both an announcement and a sticky topic";
$lang['Forum_locked'] = "This forum is locked you cannot post, reply to or edit topics";
$lang['Topic_locked'] = "This topic is locked you cannot edit posts or make replies";
$lang['No_post_id'] = "You must select a post to edit";
$lang['No_topic_id'] = "You must select a topic to reply to";
$lang['No_valid_mode'] = "You can only post, reply edit or quote messages, please return and try again";
$lang['No_such_post'] = "There is no such post, please return and try again";
$lang['Edit_own_posts'] = "Sorry but you can only edit your own posts";
$lang['Delete_own_posts'] = "Sorry but you can only delete your own posts";
$lang['Cannot_delete_replied'] = "Sorry but you may not delete posts that have been replied to";
$lang['Cannot_delete_poll'] = "Sorry but you cannot delete an active poll";
$lang['Empty_poll_title'] = "You must enter a title for your poll";
$lang['To_few_poll_options'] = "You must enter at least two poll options";
$lang['To_many_poll_options'] = "You have tried to enter too many poll options";
$lang['Post_has_no_poll'] = "This post has no poll";

$lang['Add_poll'] = "Add a Poll";
$lang['Add_poll_explain'] = "If you do not want to add a poll to your topic leave the fields blank";
$lang['Poll_question'] = "Poll question";
$lang['Poll_option'] = "Poll option";
$lang['Add_option'] = "Add option";
$lang['Update'] = "Update";
$lang['Delete'] = "Delete";
$lang['Poll_for'] = "Run poll for";
$lang['Poll_for_explain'] = "[ Enter 0 or leave blank for a never ending poll ]";
$lang['Delete_poll'] = "Delete Poll";

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

$lang['wrote'] = "wrote"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Quote"; // comes before bbcode quote output.
$lang['Code'] = "Code"; // comes before bbcode code output.

$lang['Stored'] = "Your message has been entered successfully";
$lang['Deleted'] = "Your message has been deleted successfully";
$lang['Poll_delete'] = "Your poll has been deleted successfully";
$lang['Vote_cast'] = "Your vote has been cast";
$lang['Click'] = "Click"; // Followed by here and then either return to topic or view message
$lang['Here'] = "Here";
$lang['to_return_forum'] = "to return to the forum";
$lang['to_view_message'] = "to view your message";
$lang['to_return_topic'] = "to return to the topic";

$lang['Click_return_topic'] = "Click %sHere%s to return to the topic";
$lang['Click_return_forum'] = "Click %sHere%s to return to the forum";
$lang['Click_view_message'] = "Click %sHere%s to view your message";

$lang['Topic_reply_notification'] = "Topic Reply Notification";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Private Messaging";

$lang['Login_check_pm'] = "Login to check your private messages";
$lang['New_pms'] = "You have %d new messages"; // You have 2 new messages
$lang['New_pm'] = "You have %d new message"; // You have 1 new message
$lang['No_new_pm'] = "You have no new messages";

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

$lang['Cannot_send_privmsg'] = "Sorry but the administrator has prevented you from sending private messages";
$lang['No_to_user'] = "You must specify a username to send this message";
$lang['No_such_user'] = "Sorry but no such user exists";

$lang['Message_sent'] = "Your message has been sent";

$lang['Click_return_inbox'] = "Click %sHere%s to return to your Inbox";
$lang['Click_return_index'] = "Click %sHere%s to return to the Index";

$lang['Re'] = "Re"; // Re as in 'Response to'

$lang['Send_a_new_message'] = "Send a new private message";
$lang['Send_a_reply'] = "Reply to a private message";
$lang['Edit_message'] = "Edit private message";

$lang['Notification_subject'] = "New Private Message has arrived";

$lang['Find_username'] = "Find a username";
$lang['Find'] = "Find";
$lang['No_match'] = "No matches found";

$lang['No_post_id'] = "No post ID was specified";
$lang['No_such_folder'] = "No such folder exists";
$lang['No_folder'] = "No folder specified";

$lang['Savedbox'] = "Saved box";
$lang['Sentbox'] = "Sent box";

$lang['Mark_all'] = "Mark all";
$lang['Unmark_all'] = "Unmark all";

$lang['Box_size'] = "Your %s is %d%% full"; // eg. Your Inbox is 50% full
//$lang['Box_size'] = "Your {BOXNAME} is {BOXSIZE}% full"; // eg. Your Inbox is 50% full


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
$lang['Avatar_gallery'] = "Avatar gallery";

$lang['Website'] = "Website";
$lang['From'] = "From";
$lang['Contact'] = "Contact";
$lang['Email_address'] = "Email address";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Send private message";
$lang['Hidden_email'] = "[ Hidden ]";
$lang['Search_user_posts'] = "Search for posts by this user";
$lang['Interests'] = "Interests";
$lang['Occupation'] = "Occupation"; 
$lang['Poster_rank'] = "Poster rank";

$lang['posts_per_day'] = "posts per day";
$lang['of_total'] = "of total"; // follows percentage of total posts

$lang['No_user_id_specified'] = "Sorry but that user does not exist";
$lang['Wrong_Profile'] = "You cannot modify a profile that is not your own.";
$lang['Bad_username'] = "The username you choose has been taken or is disallowed by the administrator.";
$lang['Sorry_banned_or_taken_email'] = "Sorry but the email address you gave has either been banned, is already registered to another user or is invalid. Please try an alternative address, if that is also banned you should contact the board administrator for advice";
$lang['Only_one_avatar'] = "Only one type of avatar can be specified";
$lang['File_no_data'] = "The file at the URL you gave contains no data";
$lang['No_connection_URL'] = "A connection could not be made to the URL you gave";
$lang['Incomplete_URL'] = "The URL you entered is incomplete";
$lang['Wrong_remote_avatar_format'] = "The URL of the remote avatar is not valid";

$lang['Always_smile'] = "Always enable Smilies";
$lang['Always_html'] = "Always allow HTML";
$lang['Always_bbcode'] = "Always allow BBCode";
$lang['Always_add_sig'] = "Always attach my signature";
$lang['Always_notify'] = "Always notify me of replies";
$lang['Always_notify_explain'] = "Sends an email when someone replies to a topic you have posted in. This can be changed whenever you post";

$lang['Board_style'] = "Board Style";
$lang['Board_lang'] = "Board Language";
$lang['No_themes'] = "No Themes In database";
$lang['Timezone'] = "Timezone";
$lang['Date_format'] = "Date format";
$lang['Date_format_explain'] = "The syntax used is identical to the PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> function";
$lang['Signature'] = "Signature";
$lang['Signature_explain'] = "This is a block of text that can be added to posts you make. There is a 255 character limit";
$lang['Public_view_email'] = "Always show my Email Address";

$lang['Current_password'] = "Current password";
$lang['New_password'] = "New password";
$lang['Confirm_password'] = "Confirm password";
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
$lang['View_avatar_gallery'] = "Show gallery";

$lang['Select_avatar'] = "Select avatar";
$lang['Return_profile'] = "Cancel avatar";
$lang['Select_category'] = "Select category";

$lang['Delete_Image'] = "Delete Image";
$lang['Current_Image'] = "Current Image";

$lang['Notify_on_privmsg'] = "Notify on Private Message";
$lang['Hide_user'] = "Hide your online status";

$lang['Profile_updated'] = "Your profile has been updated";
$lang['Profile_updated_inactive'] = "Your profile has been updated, however you have changed vital details thus your account is now inactive. Check your email to find out how to reactivate your account, or if admin activation is require wait for the administrator to reactivate your account";

$lang['to_return_index'] = "to return to the index";

$lang['Password_mismatch'] = "The passwords you entered did not match";
$lang['Current_password_mismatch'] = "The current password you supplied does not match that stored in the database";
$lang['Invalid_username'] = "The username you requested has been taken or disallowed";
$lang['Signature_too_long'] = "Your signature is too long";
$lang['Fields_empty'] = "You must fill in the required fields";
$lang['Avatar_filetype'] = "The avatar filetype must be .jpg, .gif or .png";
$lang['Avatar_filesize'] = "The avatar image file size must be more than 0 kB and less than"; // followed by xx kB, xx being the size
$lang['kB'] = "kB";
$lang['Avatar_imagesize'] = "The avatar must be less than " . $board_config['avatar_max_width'] . " pixels wide and " . $board_config['avatar_max_height'] . " pixels high"; 

$lang['Welcome_subject'] = "Welcome to " . $board_config['sitename'] . " Forums";
$lang['New_account_subject'] = "New user account";
$lang['Account_activated_subject'] = "Account Activated";

$lang['Account_added'] = "Thank you for registering, your account has been created. You may now login with your username and password";
$lang['Account_inactive'] = "Your account has been created. However, this forum requires account activation, an activation key has been sent to the email address you provided. Pease check your email for further information";
$lang['Account_inactive_admin'] = "Your account has been created. However, this forum requires account activation by the administrator. An email has been sent to them and you will be informed when your account has been activated";
$lang['Account_active'] = "Your account has now been activated. Thank you for registering";
$lang['Account_active_admin'] = "The account has now been activated";
$lang['Reactivate'] = "Reactivate your account!";
$lang['COPPA'] = "Your account has been created but has to be approved, please check your email for details.";
$lang['Welcome_COPPA'] = "Your account has been created, however in complance with the COPPA act you must print out this page and have you parent or guardian mail it to: <br />" . $lang['Mailing_address'] . "<br />Or fax it to: <br />" . $lang['Fax_info'] . "<br /> Once this information has been received your account will be activated by the administrator and you will receive an email notification.";

$lang['Wrong_activation'] = "The activation key you supplied does not match any in the database";
$lang['Send_password'] = "Send me a new password"; 
$lang['Password_updated'] = "A new password has been created, please check your email for details on how to activate it";
$lang['No_email_match'] = "The email address you supplied does not match the one listed for that username";
$lang['New_password_activation'] = "New password activation";
$lang['Password_activated'] = "Your account has been re-activated. To logon please use the password supplied in the email you received";


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
// Group control panel
//
$lang['Group_Control_Panel'] = "Group Control Panel";
$lang['Group_member_details'] = "Group Membership Details";
$lang['Group_member_join'] = "Join a Group";

$lang['Group_Information'] = "Group Information";
$lang['Group_name'] = "Group name";
$lang['Group_description'] = "Group description";
$lang['Group_membership'] = "Group membership";
$lang['Group_Members'] = "Group Members";
$lang['Group_Moderator'] = "Group Moderator";
$lang['Pending_members'] = "Pending Members";

$lang['Current_memberships'] = "Current memberships";
$lang['Non_member_groups'] = "Non-member groups";
$lang['Memberships_pending'] = "Memberships pending";

$lang['Join_group'] = "Join Group";
$lang['No_group_members'] = "This group has no members";
$lang['No_pending_group_members'] = "This group has no pending members";
$lang["Group_joined"] = "You have successfully subscribed to this group<br />You will be notifed when your subscription is approved by the group moderator";
$lang['Group_request'] = "A request to join your group has been made";
$lang['Group_approved'] = "Your request has been approved";
$lang['Group_added'] = "You have been added to this usergroup";

$lang['Could_not_add_user'] = "The user you selected does not exist";

$lang['Confirm_unsub'] = "Are you sure you want to unsubscribe from this group?";
$lang['Confirm_unsub_pending'] = "Your subscription to this group has not yet been approved, are you sure you want to unsubscribe?";

$lang['Unsub_success'] = "You have been unsubscribed from this group.";

$lang['Approve_selected'] = "Approve Selected";
$lang['Deny_selected'] = "Deny Selected";
$lang['Not_logged_in'] = "You must be logged in to join a group.";
$lang['Remove_selected'] = "Remove Selected";
$lang['Add_member'] = "Add Member";
$lang['Not_group_moderator'] = "You are not this groups moderator therefor you cannot preform that action.";

$lang['This_open_group'] = "This is an open group, click to request membership";
$lang['This_closed_group'] = "This is a closed group, no more users accepted";
$lang['Member_this_group'] = "You are a member of this group";
$lang['Pending_this_group'] = "Your membership of this group is pending";
$lang['Are_group_moderator'] = "You are the group moderator";
$lang['None'] = "None";

$lang['Subscribe'] = "Subscribe";
$lang['Unsubscribe'] = "Unsubscribe";
$lang['View_Information'] = "View Information";

//
// Search
//
$lang['Search_query'] = "Search Query";
$lang['Search_options'] = "Search Options";
$lang['Search_keywords'] = "Search for Keywords";
$lang['Search_keywords_explain'] = "You can use <u>AND</u> to define words which must be in the results, <u>OR</u> to define words which may be in the result and <u>NOT</u> to define words which should not be in the result. Use * as a wildcard for partial matches. To define a phrase enclose it within &quot;&quot;";
$lang['Search_author'] = "Search for Author";
$lang['Search_author_explain'] = "Use * as a wildcard for partial matches";
$lang['Search_for_any'] = "Search for any terms or use query as entered";
$lang['Search_for_all'] = "Search for all terms";
$lang['Search_author'] = "Search for Author";
$lang['Return_first'] = "Return first"; // followed by xxx characters
$lang['characters_posts'] = "characters of posts";
$lang['Search_previous'] = "Search previous"; // followed by days, weeks, months, year, all
$lang['Sort_by'] = "Sort by";
$lang['Sort_Ascending'] = "Sort Ascending";
$lang['Sort_Decending'] = "Sort Descending";
$lang['Display_results'] = "Display results as";
$lang['All'] = "All";
$lang['No_search_match'] = "No topics or posts met your search criteria";
$lang['found'] = "found"; // this precedes the number of matches found and follows Search
$lang['match'] = "Match"; // this and the following entry proceed the number of matches found
$lang['matches'] = "Matches";
$lang['Search_new'] = "View posts since last visit";
$lang['Close_window'] = "Close Window";

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
$lang['Reg_users_online'] = "There are %d Registered and %d Hidden users online";
$lang['Guest_users_online'] = "There are %d Guest users online";
$lang['Guest_user_online'] = "There is %d Guest user online";
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

$lang['Error_login'] = "Login Failed<br /><br />You have specified an incorrect or inactive username or an invalid password";

$lang['Not_Moderator'] = "You are not a moderator of this forum";
$lang['Not_Authorised'] = "Not Authorised";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderator Control Panel";
$lang['Mod_CP_explain'] = "Using the form below you can perform mass moderation operations on this forum. You can lock, unlock, move or delete any number of topics. If this forum is defined as private in some way you can also modify which users can and cannot access it.";
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
$lang['Move_to_forum'] = "Move to forum";
$lang['Split_Topic'] = "Split Topic Control Panel";
$lang['Split_Topic_explain'] = "Using the form below you can split a topic in two, either by selecting the posts individually or by splitting at a selected post";
$lang['Split_title'] = "New topic title";
$lang['Split_forum'] = "Forum for new topic";
$lang['Split_posts'] = "Split selected posts";
$lang['Split_after'] = "Split from selected post";
$lang['Topic_split'] = "The selected topic has been split successfully";
$lang['Too_many_error'] = "You have selected too many posts. You can only select one post to split a topic after!";
$lang['New_forum'] = "New forum";
$lang['None_selected'] = "You have no selected any topics to preform this operation on. Please go back and select at least one.";
$lang['This_posts_IP'] = "IP for this post";
$lang['Other_IP_this_user'] = "Other IP's this user has posted from";
$lang['Users_this_IP'] = "Users posting from this IP";
$lang['IP_info'] = "IP Information";
$lang['Lookup_IP'] = "Look up IP";


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


// --------------------
// Main Admin section/s
// --------------------

//
// Index
//
$lang['Admin'] = "Administration";
$lang['Not_admin'] = "You are not authorised to administer this board";
$lang['Welcome_phpBB'] = "Welcome to phpBB";
$lang['Admin_intro'] = "Thank you for choosing phpBB as your forum solution. This screen will give you a quick overview of all the various statistics of your board. You can get back to this page by clicking on the <u>Admin Index</u> link in the left pane. To return to the index of your board, click the phpBB logo also in the left pane. The other links on the left hand side of this screen will allow you to control every aspect of your forum experience, each screen will have instructions on how to use the tools.";
$lang['Forum_stats'] = "Forum Statistics";
$lang['Admin_Index'] = "Admin Index";
$lang['Preview_forum'] = "Preview Forum";

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
$lang['Gzip_compression'] ="Gzip compression";
$lang['Not_available'] = "Not available";


//
// DB Utils
//
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


//
// Auth pages
//
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
$lang['Vote'] = "Vote";
$lang['Pollcreate'] = "Poll create";

$lang['Permissions'] = "Permissions";
$lang['Simple_Permission'] = "Simple Permission";

$lang['This_user_is'] = "This user is a"; // followed by User/Administrator and then next line
$lang['and_belongs_groups'] = "and belongs to the following groups"; // followed by list of groups

$lang['Group_has_members'] = "This group has the following members";

$lang['Forum_auth_updated'] = "Forum permissions updated";
$lang['User_auth_updated'] = "User permissions updated";
$lang['Group_auth_updated'] = "Group permissions updated";
$lang['return_forum_auth_admin'] = "to return to the forum permissions panel";
$lang['return_group_auth_admin'] = "to return to the group permissions panel";
$lang['return_user_auth_admin'] = "to return to the user permissions panel";


//
// Banning
//
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


//
// Configuration
//
$lang['General_Config'] = "General Configuration";
$lang['Config_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.";
$lang['General_settings'] = "General Board Settings";
$lang['Site_name'] = "Site name";
$lang['Acct_activation'] = "Enable account activation";


//
// Forum Management
//
$lang['Forum_admin'] = "Forum Administration";
$lang['Forum_admin_explain'] = "From this panel you can add, delete, edit, re-order and re-synchronise categories and forums";
$lang['Edit_forum'] = "Edit forum";
$lang['Create_forum'] = "Create new forum";
$lang['Create_category'] = "Create new category";
$lang['Remove'] = "Remove";
$lang['Action'] = "Action";
$lang['Update_order'] = "Update Order";
$lang['Config_updated'] = "Forum Configuration Updated Sucessfully";
$lang['Edit'] = "Edit";
$lang['Delete'] = "Delete";
$lang['Move_up'] = "Move up";
$lang['Move_down'] = "Move down";
$lang['Resync'] = "Resync";
$lang['No_mode'] = "No mode was set";
$lang['Forum_edit_delete_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side";

//
// Smiley Management
//
$lang['smiley_return'] = "Return to smiley listing";
$lang['smiley_del_success'] = "The smiley was successfully removed";
$lang['smiley_title'] = "Smiles Editing Utility";
$lang['smiley_code'] = "Smiley Code";
$lang['smiley_url'] = "Smiley Image File";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smiley_add_success'] = "The smiley was successfully added";
$lang['smiley_edit_success'] = "The smiley was successfully updated";
$lang['smile_add'] = "Add a new Smiley";
$lang['smile_desc'] = "From this page you can add, remove and edit the emoticons or smileys your users can use in their posts and private messages.";
$lang['smiley_config'] = "Smiley Configuration";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emotion";


//
// User Management
//
$lang['User_admin'] = "Administration";
$lang['User_admin_explain'] = "Here you can change your user's information and certain specific options. To modify the users permissions please use the user and group permissions system.";
$lang['User_delete'] = "Delete this user";
$lang['User_delete_explain'] = "Click here to delete this user, this cannot be undone.";
$lang['User_deleted'] = "User was successfully deleted.";
$lang['User_status'] = "User is active";
$lang['User_allowpm'] = "Can send Private Messages";
$lang['User_allowavatar'] = "Can display avatar";
$lang['Admin_avatar_explain'] = "Here you can see and delete the user's current avatar.";
$lang['User_special'] = "Special admin-only fields";
$lang['User_special_explain'] = "These fields are not able to be modified by the users.  Here you can set their status and other options that are not given to users.";


//
// Group Management
//
$lang['Group_admin_explain'] = "From this panel you can administer all your usergroups, you can; delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description";
$lang['Error_updating_groups'] = "There was an error while updating the groups";
$lang['Updated_group'] = "The group was successfully updated";
$lang['Added_new_group'] = "The new group was successfully created";
$lang['Deleted_group'] = "The group was successfully deleted";
$lang['New_group'] = "Create new group";
$lang['Edit_group'] = "Edit group";
$lang['group_name'] = "Group name";
$lang['group_description'] = "Group description";
$lang['group_moderator'] = "Group moderator";
$lang['group_status'] = "Group status";
$lang['group_open'] = "Open group";
$lang['group_closed'] = "Closed group";
$lang['group_delete'] = "Delete group";
$lang['group_delete_check'] = "Delete this group";
$lang['submit_group_changes'] = "Submit Changes";
$lang['reset_group_changes'] = "Reset Changes";
$lang['No_group_name'] = "You must specify a name for this group";
$lang['No_group_moderator'] = "You must specify a moderator for this group";
$lang['No_group_mode'] = "You must specify a mode for this group, open or closed";
$lang['delete_group_moderator'] = "Delete the old group moderator?";
$lang['delete_moderator_explain'] = "If you're changing the group moderator, check this box to remove the old moderator from the group.  Otherwise, do not check it, and the user will become a regular member of the group.";



//
// Prune Administration
//
$lang['Forum_Prune'] = "Forum Prune";
$lang['Forum_Prune_explain'] = "This will delete any topic which has not been posted to within the number of days you select. If you do not enter a number then all topics will be deleted. It will not remove topics in which polls are still running nor will it remove announcements. You will need to remove these topics manually.";
$lang['Do_Prune'] = "Do Prune";
$lang['All_Forums'] = "All Forums";
$lang['prune_days'] = "Remove topics that have not been posted to in";
$lang['Prune_topics_not_posted'] = "Prune topics that haven't been posted to in the last";
$lang['prune_freq'] = 'Check for topic age every';
$lang['Set_prune_data'] = "You have turned on auto-prune for this forum but did not set a frequency or number of days to prune. Please go back and do so";
$lang['Topics_pruned'] = "Topics pruned";
$lang['Posts_pruned'] = "Posts pruned";
$lang['Prune_success'] = "Pruning of forums was successful";


//
// Word censor
//
$lang['Word_censor'] = "Word Censor";
$lang['Word'] = "Word";
$lang['Replacement'] = "Replacement";
$lang['Add_new_word'] = "Add new word";
$lang['Update_word'] = "Update word censor";
$lang['Words_title'] = "Word Censors";
$lang['Words_explain'] = "From this control panel you can add, edit, and remove words that will be automatically censored on your forums. Wildcards (*) are accepted in the word field! (i.e.: *test*, test*, *test, and test are all valid)";
$lang['Must_enter_word'] = "You must enter a word and it's replacement!";
$lang['No_word_selected'] = "No word selected for editing";
$lang['Word_updated'] = "The selected word censor has been successfully updated";
$lang['Word_added'] = "The word censor has been successfully added";
$lang['Word_removed'] = "The selected word censor has been successfully removed";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Here you can email a message to either all of your users, or all users of a specific group.  To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all receptients.  If you are emailing a large group of people, please be patient after submiting and DO NOT stop the page halfway through.  It is normal for amass emailing to take a long time.";
$lang['Compose'] = "Compose";


//
// Install Process
//
$lang['Welcome_install'] = "Welcome to phpBB 2 Installation";
$lang['Initial_config'] = "Basic Configuration";
$lang['DB_config'] = "Database Configuration";
$lang['Admin_config'] = "Admin Configuration";
$lang['Installer_Error'] = "An error has occured during installation";
$lang['Previous_Install'] = "A previous installation has been detected";
$lang['Inst_Step_0'] = "Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist";
$lang['Start_Install'] = "Start Install";
$lang['Default_lang'] = "Default board language";
$lang['DB_Host'] = "Database Server Hostname";
$lang['DB_Name'] = "Your Database Name";
$lang['Database'] = "Your Database";
$lang['Install_lang'] = "Choose Language for Installation";
$lang['dbms'] = "Database Type";
$lang['Inst_Step_1'] = "Your database tables have been created and filled with some basic default data.  Please enter your chosen phpBB Admin Username and Password.";
$lang['Create_User'] = "Create User";
$lang['Inst_Step_2'] = "Your admin username has been created.  At this point your basic installation is complete. You will now be taken to a screen which will allow you to administer your new installation. Please be sure to check the General Configuration details and make any required changes. Thank you for choosing phpBB 2.";
$lang['Finish_Install'] = "Finish Installation";
$lang['Install_db_error'] = "An error occured trying to update the database";
$lang['ODBC_Instructs'] = "Someone please write some odbc instructions in the \$lang['ODBC_Instructs'] variable!";
$lang['Table_Prefix'] = "Prefix for tables in database";
$lang['Unwriteable_config'] = "Your config file is unwriteable at present. A copy of the config file will be downloaded to your when you click the button below. You should upload this file to the same directory as phpBB 2. Once this is done you should log in using the administrator name and password you provided on the previous form and visit the admin control centre (a link will appear at the bottom of each screen once logged in) to check the general configuration. Thank you for choosing phpBB 2.";
$lang['Download_config'] = "Download Config";
$lang['ftp_choose'] = "Choose Download Method";
$lang['Attempt_ftp'] = "Attempt to ftp config file into place:";
$lang['Send_file'] = "Just send the file to me and I'll ftp it manually:";
$lang['ftp_option'] = "<br />Since the ftp extensions are loaded in php you may will also be given
the option of first trying to automatically ftp the config file into place.";
$lang['ftp_instructs'] = "You have chosen to attempt to ftp the file to your phpBB installation automagically.  Please enter the information below to facilitate this process. Note that the FTP Path should be the exact path via ftp to your phpBB2 installation as if you were ftping to it.";
$lang['ftp_path'] = "FTP Path to phpBB2:";
$lang['ftp_username'] = "Your FTP Username:";
$lang['ftp_password'] = "Your FTP Password:";
$lang['Transfer_config'] = "Start Transfer";
$lang['ftp_info'] = "Enter Your FTP Information";

//
// Ranks admin
//
$lang['Must_select_rank'] = "Sorry, you didn't select a rank.  Please go back and try again.";
$lang['No_assigned_rank'] = "No special rank assigned";
$lang['Ranks_title'] = "Rank Administration";
$lang['Ranks_explain'] = "Here you can add, edit, view, and delete ranks. This is also a place to create custom ranks";
$lang['Rank_title'] = "Rank Title";
$lang['Rank_special'] = "Is special rank";
$lang['Rank_minimum'] = "Minimum Posts";
$lang['Rank_maximum'] = "Maximum Posts";
$lang['Rank_updated'] = "The rank was successfully updated";
$lang['Rank_added'] = "The rank was successfully added";
$lang['Rank_removed'] = "The rank was successfully deleted";
$lang['Add_new_rank'] = "Add new rank";
$lang['Rank_image'] = "Rank Image";
$lang['Rank_image_explain'] = "This is the place to set a custom image for everyone in the rank. You can specify either a relative or absolute path to the image";
$lang['return_rank_admin'] = "to return to rank admin";

//
// Disallow Username Admin
//
$lang['disallowed_deleted'] = "The disallowed username has successfully been removed";
$lang['disallowed_already'] = "The username you are trying to disallow has already been disallowed, or a user currently exists that this would disallow";
$lang['disallow_successful'] = "The disallowed username has successfully been added";
$lang['Disallow_control'] = "Username Disallow Control";
$lang['disallow_instructs'] = "Here you can control usernames which will not be allowed to be used.  Disallowed usernames are allowed to contain a wildcard character of '*'.  Please note that you will not be allowed to specify a username to disallow if that username has already been registered.  You must first delete that username, and then disallow it.";
$lang['del_disallow'] = "Remove a Disallowed Username";
$lang['del_disallow_explain'] = "You can remove a disallowed username by selecting the username from this list and clicking submit";
$lang['add_disallow'] = "Add a disallowed username";
$lang['add_disallow_explain'] = "You can disallow a username using the wildcard character '*' to match any character";
$lang['no_disallowed'] = "No Disallowed Usernames";

//
// Styles Admin
//
$lang['Styles_admin'] = "Styles Administration";
$lang['Styles_explain'] = "Using this facility you can add, remove and manage styles (templates and themes) available to your users.";
$lang['Styles_addnew_explain'] = "The following list contains all the themes that are available for the templates you currently have. The items on this list HAVE NOT yet been installed into the phpBB database. To install a theme simply click the 'install' link beside a selected entry";
$lang['Style'] = "Style";
$lang['Template'] = "Template";
$lang['Install'] = "Install";
$lang['Confirm_delete_style'] = "Are you sure you want to delete this style?";
$lang['Style_removed'] = "The selected style has been removed from the database. To fully remove this style from your system you must delete the appropriate directory from your templates directory.";
$lang['Theme_installed'] = "The selected theme has been installed successfully";
$lang['Export_themes'] = "Export Themes";
$lang['Download_theme_cfg'] = "The exporter could not write the theme information file. Click the button below to download this file with your browser. Once you have downloaded it you can transer it to your templates dir and package your template for distribution if you choose.";
$lang['No_themes'] = "The template you selected has no themes attached to it. Click on the 'Create New' link to the left to create one.";
$lang['Download'] = "Download";
$lang['No_template_dir'] = "Could not open template dir, it may be unreadable by the webserver or may not exist";
$lang['Export_explain'] = "In this panel you will be able to export the theme data for a selected template. Select the template from the list below and the script will create the theme configuration file and attempt to save it to the selected template directory. If it cannot save the file itself it will give you the option to download it. In order for the script to save the file you must give write access to the webserver for the selected template dir. For more information on this see the phpBB users guide.";
$lang['Select_template'] = "Select a Template";
$lang['Theme_info_saved'] = "The theme information for the selected template has been saved. You should now return the permissions on the theme_info.cfg and/or selected template directory to READ ONLY.";
//
// That's all Folks!
// -------------------------------------------------

?>
