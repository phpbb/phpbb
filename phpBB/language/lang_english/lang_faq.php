<?php
/***************************************************************************
 *                          lang_faq.php [english]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
 
// 
// To add an entry to your FAQ simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your FAQ entries, if you absolutely must then escape them ie. \"something\"
//
// The FAQ items will appear on the FAQ page in the same order they are listed in this file
//
 
  
$faq[] = array("Why can't I login?", "Have you registered? Seriously, you must register in order to login. Have you been banned from the board (a message will be displayed if you have)? If so then you should contact the webmaster or board administrator to find out why. If you have registered and are not banned and you still cannot login then check and double check your username and password. Usually this is the problem, if not then contact the board administrator they may have incorrect configuration settings for the board.");
$faq[] = array("Why do I need to register at all?", "You may not have too, it is up to the administrator of the board as to whether you need to register in order to post messages. However registration will give you access to additional features not available to guest users such as definable avatar images, private messaging, emailing of fellow users, usergroup subscription, etc. It only takes a few moments to register so it is recommended you do so.");
$faq[] = array("Why do I get logged off automatically?", "If you do not check the 'Log me in automatically' box when you login the board will only keep you logged in for a preset time. This prevents misuse of your account by anyone else. To stay logged in check the box during login, this is not recommended if you access the board from a shared computer, eg. library, internet cafe, university cluster, etc.");
$faq[] = array("How do I prevent my username appearing in the online user listings?", "In your profile you will find an option 'Hide your online status', if you switch this 'on' you'll only appear to board admins or to yourself. You will be counted as a hidden user.");
$faq[] = array("I've lost my password!", "Don't panic! While your password cannot be retrieved it can be reset. To do this go to the login page and click <u>I've forgotten my password</u>, follow the instructions and you should be back online in no time");
$faq[] = array("I registered but cannot login!", "Firstly check your are entering the correct username and password. If they are okay then one of two things may have happened. If COPPA support is enabled and you clicked the <u>I am under 13 years old</u> link while registering then you will have to follow the instructions you received. If this is not the case then does your account need activating? Some boards will require all new registrations be activated, either by yourself or by the administrator before you can logon. When you registered it would have told you whether activation was required. If you were sent an email then follow the instructions, if you did not receive the email then are you sure your email address is valid? One reason activation is used is to reduce the possibility of 'roque' users abusing the board anonymously. If you are sure the email address you used is valid then try contacting the board administrator.");
$faq[] = array("I registered in the past but cannot login any more?!", "The most likely reasons for this are; you entered an incorrect username or password (check the email you were sent when you first registered) or the administrator has deleted your account for some reason. If it is the later case then perhaps you did not post anything? It is usual for boards to periodically remove users who have not posted anything so as to reduce the size of the database. Try registering again and get involved in discussions.");
$faq[] = array("--","");

$faq[] = array("How do I change my settings?", "All your settings (if you are registered) are stored in the database. To alter them click the <u>Profile</u> link (generally shown at the top of pages but this may not be the case). This will allow you to change all your settings");
$faq[] = array("The times are not correct!", "The times are almost certainly correct, however what you may be seeing are times displayed in a timezone different from the one you are in. If this is the case you should change your profile setting for the timezone to match your particular area, eg. London, Paris, New York, Sydney, etc. Please note that changing the timezone, like most settings can only be done by registered users. So if you are not registered this is a good 'time' to do so, if you pardon the pun!");
$faq[] = array("I changed the timezone and the time is still wrong!", "If you are sure you have set the timezone correctly and the time is still different the most likely answer is 'daylight savings time' (or 'summer time' as it is known in the UK and other places). The board is not designed to handle the changeovers between standard and daylight time so during summer months the time may be an hour different from the 'real' local time.");
$faq[] = array("My language is not in the list!", "The most likely reasons for this are either the administrator did not install your language or someone has not translated this board into your language. Try asking the board administrator if they can install the language pack you need, if it does not exist then please feel free to create a new translation. More information can be found at the phpBB Group website (see link at bottom of pages)");
$faq[] = array("How do I show an image below my username?", "There may be two images below a username when viewing posts. The first is an image associated with your 'rank', generally these take the form of stars or blocks indicating how many posts you have made or your 'status' on the forums. Below this may be a larger image known as an avatar, this is generally unique or personal to each user. It is up to the board administrator to enable avatars and they have a choice over the way in which avatars can be made available. If you are unable to use avatars then this is the decision of the board admin, you should ask them their reasons (we're sure they'll be good!)");
$faq[] = array("When I click the email link for a user it asks me to login?", "Sorry but only registered users can send email to people via the built-in email form (if the admin has enabled this feature). This is to prevent malicious use of the email system by anonymous users.");
$faq[] = array("--","");

$faq[] = array("How do I post a topic in a forum?", "Easy, click the relevant button on either the forum or topic screens. You may need to register before you can post a message, the facilities available to you are listed at the bottom of the forum and topic screens (the 'You can post new topics, You can vote in polls, etc.' list)");
$faq[] = array("How do I edit or delete a post?", "Unless you are the board admin or forum moderator you can only edit or delete your own posts. You can edit a post (sometimes for only a limited time after it was made) by clicking the 'edit' button for the relevant post.  If someone has already replied to the post you will find a small piece of text output below the post when you return to the topic, this lists the number of times you edited it. This will only appear if no one has replied, it also will not appear if moderators or admins edit the post (they should leave a message saying what they altered and why). Please note that normal users cannot delete a post once someone has replied.");
$faq[] = array("How do I create a poll?", "Creating a poll is easy, when you post a new topic (or edit the first post of a topic, if you have permission) you shoul see a 'Add Poll' form below the main posting box (if you cannot see this then you probably do not have rights to create polls). You should enter a title for the poll and then at least two options (to set an option type in the poll question and click the 'Add option' button. You can also set a time limit for the poll, 0 is an infinite poll. There will be a limit to the number of options you can list, this is set by the board administrator");
$faq[] = array("How do I edit or delete a poll?", "As with posts, polls can only be edited by the original poster, a moderator or board admin. To edit a poll click the first post in the topic (this always has the poll associated with it). If no one has cast a vote then users can delete the poll or edit any poll option, however if people have already placed votes only moderators or admins can edit or delete it. This is to prevent people rigging polls by changing options mid-way through a poll");
$faq[] = array("Why can't I access a forum?", "Some forums may be limited to certain users or groups. To view, read, post, etc. you may need special authorisation, only the forum moderator and board admin can grant this access, you should contact them.");
$faq[] = array("Why can't I vote in polls?", "Only registered users can vote in polls (so as to prevent spoofing of results). If you have registered and still cannot vote then you probably do not have appropriate access rights.");
$faq[] = array("--","");

$faq[] = array("I cannot send private messages!", "There are three reasons for this; you are not registered and/or not logged on, the board administrator has disabled private messaging for the entire board or the board administrator has prevented you from sending messages. If it is the later case you should try asking the administrator why.");
$faq[] = array("I keep getting unwanted private messages!", "In the future we will be adding an ignore list to the private messaging system. For now though if you keep receiving unwanted private messages from someone inform the board admin, they have the power to prevent a user from sending private messages at all.");
$faq[] = array("I have recieved a spamming or abusive email from someone on this board!", "We are sorry to hear that. The email form feature of this board includes safeguards to try and track users who send such posts. You should email the board administrator with a full copy of the email you received, it is very important this include the headers (these list details of the user that sent the email). They can then take action.");
$faq[] = array("--","");

$faq[] = array("What are usergroups?", "Usergroups are a way in which board administrators can group users. Each user can belong to several groups (this differs from most other boards) and each group can be assigned individual access rights. This makes it easy for admins to set up several users as moderators of a forum, or to give them access to a private forum, etc.");
$faq[] = array("How do I join a usergroup?", "To join a usergroup click the usergroup link on the page header (dependent on template design), you can then view all usergroups. Not all groups are 'open access', some are closed and some may even have hidden memberships. If the board is open then you can request to join it by clicking the appropriate button. The user group moderator will need to approve your request, they may ask why you want to join the group. Please do not pester a group moderator if they turn your request down, they will have their reasons.");
$faq[] = array("How do I become a usergroup moderator?", "Usergroups are initially created by the board admin, they also assign a board moderator. If you are interested in creating a usergroup then your first point of contact should be the admin, try dropping them a private message.");
$faq[] = array("--","");


$faq[] = array("Why isn't X feature available?", "This software was written by and licenced through phpBB Group. If you believe a feature needs to be added then please visit the phpbb.com website and see what phpBB Group have to say. Please do not post feature requests to the board at phpbb.com, the Group uses sourceforge to handle tasking of new features. Please read through the forums and see what, if any, our position may already be for a feature and then follow the procedure given there.");
$faq[] = array("Who do I contact about abusive and/or legal matters related to this board?", "You should contact the administrator of this board. If you cannot find who this you should first contact one of the forum moderators and ask them who you should in turn contact. If still get no response you should contact the owner of the domain (do a whois lookup) or, if this is running on a free service (eg. yahoo, free.fr, f2s.com, etc.), the management or abuse department of that service. Please note that phpBB Group has absolutely no control and cannot in any way be held liable over how, where or by whom this board is used. It is absolutely pointless contacting phpBB Group in relation to any legal (cease and desist, liable, defamatory comment, etc.) matter not directly related to the phpbb.com website or the discrete software of phpBB itself. If you do email phpBB Group about any third party use of this software then you should expect a terse response or no response at all.");
$faq[] = array("--","");

?>