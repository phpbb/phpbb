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
$lang['Category'] = "Category";
$lang['Topic'] = "Topic";
$lang['Reply'] = "Reply";
$lang['Replies'] = "Replies";
$lang['Views'] = "Views";
$lang['Post'] = "Post";
$lang['Posted'] = "Posted";
$lang['Username'] = "Username";
$lang['Password'] = "Password";
$lang['Email'] = "Email";
$lang['Poster'] = "Poster";
$lang['Author'] = "Author";
$lang['Time'] = "Time";
$lang['Hours'] = "Hours";

$lang['1_Day'] = "1 Day";
$lang['7_Days'] = "7 Days";
$lang['2_Weeks'] = "2 Weeks";
$lang['1_Month'] = "1 Month";
$lang['3_Months'] = "3 Months";
$lang['6_Months'] = "6 Months";
$lang['1_Year'] = "1 Year";

$lang['Go'] = "Go";
$lang['Jump_to'] = "Jump to";
$lang['Submit'] = "Submit";
$lang['Reset'] = "Reset";
$lang['Cancel'] = "Cancel";
$lang['Spellcheck'] = "Spellcheck";
$lang['Yes'] = "Yes";
$lang['No'] = "No";
$lang['Enabled'] = "Enabled";
$lang['Disabled'] = "Disabled";
$lang['Error'] = "Error";
$lang['Success'] = "Success";

$lang['Next'] = "Next";
$lang['Previous'] = "Previous";
$lang['Goto_page'] = "Goto page";
$lang['Joined'] = "Joined";
$lang['IP_Address'] = "IP Address";

$lang['Select_forum'] = "Select a forum";
$lang['View_latest_post'] = "View latest post";
$lang['Page_of'] = "Page <b>%d</b> of <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Number";
$lang['AIM'] = "AIM Address";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Post_new_topic'] = "Post new topic";
$lang['Reply_to_topic'] = "Reply to topic";
$lang['Reply_with_quote'] = "Reply with quote";

$lang['Admin_panel'] = "Go to Administration Panel";


//
// Global Header strings
//
$lang['Registered_users'] = "Registered Users:";
$lang['Online_users_total'] = "In total there are %d users online :: ";
$lang['Online_user_total'] = "In total there is %d user online :: ";
$lang['Reg_users_total'] = "%d Registered, ";
$lang['Reg_user_total'] = "%d Registered, ";
$lang['Hidden_users_total'] = "%d Hidden and ";
$lang['Hidden_user_total'] = "%d Hidden and ";
$lang['Guest_users_total'] = "%d Guests";
$lang['Guest_user_total'] = "%d Guests";

$lang['You_last_visit'] = "You last visited on %s"; // %s replaced by date/time
$lang['Search_your_posts'] = "View your posts";
$lang['Search_unanswered'] = "View unanswered posts";
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


//
// Stats block text
//
$lang['Posted_article_total'] = "Our users have posted a total of <b>%d</b> article"; // Number of posts
$lang['Posted_articles_total'] = "Our users have posted a total of <b>%d</b> articles"; // Number of posts
$lang['Registered_user_total'] = "We have <b>%d</b> registered user"; // # registered users
$lang['Registered_users_total'] = "We have <b>%d</b> registered users"; // # registered users
$lang['Newest_user'] = "The newest registered user is <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "No new posts since your last visit";
$lang['No_new_posts'] = "No new posts";
$lang['New_posts'] = "New posts";
$lang['New_post'] = "New post";
$lang['No_new_posts_hot'] = "No new posts [ Popular ]";
$lang['New_posts_hot'] = "New posts [ Popular ]";
$lang['No_new_posts_locked'] = "No new posts [ Locked ]";
$lang['New_posts_locked'] = "New posts [ Locked ]";
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

$lang['Mark_all_forums'] = "Mark all forums read";
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

$lang['Mark_all_topics'] = "Mark all topics read";
$lang['Topics_marked_read'] = "The topics for this forum have now been marked read";

$lang['Rules_post_can'] = "You <b>can</b> post new topics in this forum";
$lang['Rules_post_cannot'] = "You <b>cannot</b> post new topics in this forum";
$lang['Rules_reply_can'] = "You <b>can</b> reply to topics in this forum";
$lang['Rules_reply_cannot'] = "You <b>cannot</b> reply to topics in this forum";
$lang['Rules_edit_can'] = "You <b>can</b> edit your posts in this forum";
$lang['Rules_edit_cannot'] = "You <b>cannot</b> edit your posts in this forum";
$lang['Rules_delete_can'] = "You <b>can</b> delete your posts in this forum";
$lang['Rules_delete_cannot'] = "You <b>cannot</b> delete your posts in this forum";
$lang['Rules_vote_can'] = "You <b>can</b> vote in polls in this forum";
$lang['Rules_vote_cannot'] = "You <b>cannot</b> vote in polls in this forum";
$lang['Rules_moderate'] = "You <b>can</b> %smoderate this forum%s"; // %s replaced by a href links, do not remove! 

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

$lang['Read_profile'] = "Read profile users profile"; // Followed by username of poster
$lang['Send_email'] = "Send email to user"; // Followed by username of poster
$lang['Visit_website'] = "Visit posters website";
$lang['ICQ_status'] = "ICQ Status";
$lang['Edit_delete_post'] = "Edit/Delete this post";
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
$lang['Options'] = "Options";

$lang['Post_Announcement'] = "Announcement";
$lang['Post_Sticky'] = "Sticky";
$lang['Post_Normal'] = "Normal";

$lang['Confirm'] = "Confirm";
$lang['Confirm_delete'] = "Are you sure you want to delete this post?";
$lang['Confirm_delete_poll'] = "Are you sure you want to delete this poll?";
$lang['Submit_post'] = "Submit Post";
$lang['Preview'] = "Preview";
$lang['Spellcheck'] = "Spellcheck";
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

$lang['Disable_HTML_post'] = "Disable HTML in this post";
$lang['Disable_BBCode_post'] = "Disable BBCode in this post";
$lang['Disable_Smilies_post'] = "Disable Smilies in this post";

$lang['HTML_is_ON'] = "HTML is <u>ON</u>";
$lang['HTML_is_OFF'] = "HTML is <u>OFF</u>";
$lang['BBCode_is_ON'] = "BBCode is <u>ON</u>";
$lang['BBCode_is_OFF'] = "BBCode is <u>OFF</u>";
$lang['Smilies_are_ON'] = "Smilies are <u>ON</u>";
$lang['Smilies_are_OFF'] = "Smilies are <u>OFF</u>";

$lang['Attach_signature'] = "Attach signature (signatures can be changed in profile)";
$lang['Notify'] = "Notify me when a reply is posted";
$lang['Delete_post'] = "Delete this post";

$lang['wrote'] = "wrote"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Quote"; // comes before bbcode quote output.
$lang['Code'] = "Code"; // comes before bbcode code output.

$lang['Stored'] = "Your message has been entered successfully";
$lang['Deleted'] = "Your message has been deleted successfully";
$lang['Poll_delete'] = "Your poll has been deleted successfully";
$lang['Vote_cast'] = "Your vote has been cast";

$lang['Click_return_topic'] = "Click %sHere%s to return to the topic"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Click %sHere%s to try again";
$lang['Click_return_forum'] = "Click %sHere%s to return to the forum";
$lang['Click_view_message'] = "Click %sHere%s to view your message";
$lang['Click_return_modcp'] = "Click %sHere%s to return to the Moderator Control Panel";
$lang['Click_return_group'] = "Click %sHere%s to return to group information";

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
$lang['Outbox'] = "Outbox";
$lang['Savedbox'] = "Saved box";
$lang['Sentbox'] = "Sent box";
$lang['Flag'] = "Flag";
$lang['Subject'] = "Subject";
$lang['From'] = "From";
$lang['To'] = "To";
$lang['Date'] = "Date";
$lang['Mark'] = "Mark";
$lang['Sent'] = "Sent";
$lang['Saved'] = "Saved";
$lang['Delete_marked'] = "Delete Marked";
$lang['Delete_all'] = "Delete All";
$lang['Save_marked'] = "Save Marked"; 
$lang['Save_message'] = "Save Message";
$lang['Delete_message'] = "Delete Message";

$lang['Display_messages'] = "Display messages from previous"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "All Messages";

$lang['No_messages_folder'] = "You have no messages in this folder";

$lang['PM_disabled'] = "Private messaging has been disabled on this board";
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

$lang['Mark_all'] = "Mark all";
$lang['Unmark_all'] = "Unmark all";

$lang['Inbox_size'] = "Your Inbox is %d%% full"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Your Sentbox is %d%% full"; 
$lang['Savebox_size'] = "Your Savebox is %d%% full"; 


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Viewing profile :: %s"; // %s is username 
$lang['About_user'] = "All about %s";

$lang['Preferences'] = "Preferences";
$lang['Items_required'] = "Items marked with a * are required unless stated otherwise";
$lang['Registration_info'] = "Registration Information";
$lang['Profile_info'] = "Profile Information";
$lang['Profile_info_warn'] = "This information will be publicly viewable";
$lang['Avatar_panel'] = "Avatar control panel";
$lang['Avatar_gallery'] = "Avatar gallery";

$lang['Website'] = "Website";
$lang['Location'] = "Location";
$lang['Contact'] = "Contact";
$lang['Email_address'] = "Email address";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Send private message";
$lang['Hidden_email'] = "[ Hidden ]";
$lang['Search_user_posts'] = "Search for posts by this user";
$lang['Interests'] = "Interests";
$lang['Occupation'] = "Occupation"; 
$lang['Poster_rank'] = "Poster rank";

$lang['Total_posts'] = "Total posts";
$lang['User_post_pct_stats'] = "%d%% of total"; // 15% of total
$lang['User_post_day_stats'] = "%.2f posts per day"; // 1.5 posts per day
$lang['Search_user_posts'] = "Find all posts by %s"; // Find all posts by username

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
$lang['Signature_explain'] = "This is a block of text that can be added to posts you make. There is a %d character limit";
$lang['Public_view_email'] = "Always show my Email Address";

$lang['Current_password'] = "Current password";
$lang['New_password'] = "New password";
$lang['Confirm_password'] = "Confirm password";
$lang['password_if_changed'] = "You only need to supply a password if you want to change it";
$lang['password_confirm_if_changed'] = "You only need to confirm your password if you changed it above";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Displays a small graphic image below your details in posts. Only one image can be displayed at a time, its width can be no greater than %d pixels, a height no greater than %d pixels and a file size no more than %dkB."; $lang['Upload_Avatar_file'] = "Upload Avatar from your machine";
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

$lang['Send_email_msg'] = "Send an email message";
$lang['No_user_specified'] = "No user was specified";
$lang['User_prevent_email'] = "This user does not wish to receive email. Try sending them a private message";
$lang['User_not_exist'] = "That user does not exist";
$lang['CC_email'] = "Send a copy of this email to yourself";
$lang['Email_message_desc'] = "This message will be sent as plain text, do not include any HTML or BBCode. The return address for this message will be set to your email address.";
$lang['Flood_email_limit'] = "You cannot send another email at this time, try again later";
$lang['Recipient'] = "Recipient";
$lang['Email_sent'] = "The email has been sent";
$lang['Send_email'] = "Send email";
$lang['Empty_subject_email'] = "You must specify a subject for the email";
$lang['Empty_message_email'] = "You must enter a message to be emailed";

//
// Memberslist
//
$lang['Select_sort_method'] = "Select sort method";
$lang['Sort'] = "Sort";
$lang['Sort_Top_Ten'] = "Top Ten Posters";
$lang['Sort_Joined'] = "Joined Date";
$lang['Sort_Username'] = "Username";
$lang['Sort_Location'] = "Location";
$lang['Sort_Posts'] = "Total posts";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Website";
$lang['Sort_Ascending'] = "Ascending";
$lang['Sort_Descending'] = "Descending";
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

$lang['Group_type'] = "Group type";
$lang['Group_open'] = "Open group";
$lang['Group_closed'] = "Closed group";
$lang['Group_hidden'] = "Hidden group";

$lang['Current_memberships'] = "Current memberships";
$lang['Non_member_groups'] = "Non-member groups";
$lang['Memberships_pending'] = "Memberships pending";

$lang['No_groups_exist'] = "No Groups Exist";
$lang['Group_not_exist'] = "That user group does not exist";

$lang['Join_group'] = "Join Group";
$lang['No_group_members'] = "This group has no members";
$lang['Group_hidden_members'] = "This group is hidden, you cannot view its membership";
$lang['No_pending_group_members'] = "This group has no pending members";
$lang["Group_joined"] = "You have successfully subscribed to this group<br />You will be notifed when your subscription is approved by the group moderator";
$lang['Group_request'] = "A request to join your group has been made";
$lang['Group_approved'] = "Your request has been approved";
$lang['Group_added'] = "You have been added to this usergroup"; 
$lang['Already_member_group'] = "You are already a member of this group";
$lang['User_is_member_group'] = "User is already a member of this group";
$lang['Group_type_updated'] = "Successfully updated group type";

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

$lang['Login_to_join'] = "Login to join or manage group memberships";
$lang['This_open_group'] = "This is an open group, click to request membership";
$lang['This_closed_group'] = "This is a closed group, no more users accepted";
$lang['This_hidden_group'] = "This is a hidden group, automatic user addition is not allowed";
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
$lang['Reg_users_online'] = "There are %d Registered and ";
$lang['Hidden_users_online'] = "%d Hidden users online";
$lang['Guest_users_online'] = "There are %d Guest users online";
$lang['Guest_user_online'] = "There is %d Guest user online";
$lang['Online_explain'] = "This data is based on users active over the past five minutes";
$lang['No_users_browsing'] = "There are no users currently browsing this forum";
$lang['Forum_Location'] = "Forum Location";
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

$lang['Error_login'] = "You have specified an incorrect or inactive username or an invalid password";

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
$lang['Leave_shadow_topic'] = "Leave shadow topic in old forum.";
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

$lang['-12'] = "GMT - 12 Hours";
$lang['-11'] = "GMT - 11 Hours";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Hours";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 Hours";
$lang['-3.5'] = "GMT - 3.5 Hours";
$lang['-3'] = "GMT - 3 Hours";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 Hours";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT + 3 Hours";
$lang['3.5'] = "GMT + 3.5 Hours";
$lang['4'] = "GMT + 4 Hours";
$lang['4.5'] = "GMT + 4.5 Hours";
$lang['5'] = "GMT + 5 Hours";
$lang['5.5'] = "GMT + 5.5 Hours";
$lang['6'] = "GMT + 6 Hours";
$lang['7'] = "GMT + 7 Hours";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 Hours";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 Hours";
$lang['12'] = "GMT + 12 Hours";


//
// That's all Folks!
// -------------------------------------------------

?>