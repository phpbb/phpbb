<?php
/***************************************************************************
 *                           lang_faq.php [ English ]
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

$faq = array(
	0 => array(
		0 => '--',
		1 => 'Login and Registration Issues'
	),
	1 => array(
		0 => 'Why can\'t I login?',
		1 => 'Have you registered? Seriously, you must register in order to login. Have you been banned from the board (a message will be displayed if you have)? If so then you should contact the webmaster or board administrator to find out why. If you have registered and are not banned and you still cannot login then check and double check your username and password. Usually this is the problem, if not then contact the board administrator they may have incorrect configuration settings for the board.'
	),
	2 => array(
		0 => 'Why do I need to register at all?',
		1 => 'You may not have too, it is up to the administrator of the board as to whether you need to register in order to post messages. However registration will give you access to additional features not available to guest users such as definable avatar images, private messaging, emailing of fellow users, usergroup subscription, etc. It only takes a few moments to register so it is recommended you do so.'
	),
	3 => array(
		0 => 'Why do I get logged off automatically?',
		1 => 'If you do not check the <i>Log me in automatically</i> box when you login the board will only keep you logged in for a preset time. This prevents misuse of your account by anyone else. To stay logged in check the box during login, this is not recommended if you access the board from a shared computer, e.g. library, internet cafe, university cluster, etc.'
	),
	4 => array(
		0 => 'How do I prevent my username appearing in the online user listings?',
		1 => 'In your profile you will find an option <i>Hide your online status</i>, if you switch this <i>on</i> you\'ll only appear to board administrators or to yourself. You will be counted as a hidden user.'
	),
	5 => array(
		0 => 'I\'ve lost my password!',
		1 => 'Don\'t panic! While your password cannot be retrieved it can be reset. To do this go to the login page and click <u>I\'ve forgotten my password</u>, follow the instructions and you should be back online in no time'
	),
	6 => array(
		0 => 'I registered but cannot login!',
		1 => 'Firstly check your are entering the correct username and password. If they are okay then one of two things may have happened. If COPPA support is enabled and you clicked the <u>I am under 13 years old</u> link while registering then you will have to follow the instructions you received. If this is not the case then does your account need activating? Some boards will require all new registrations be activated, either by yourself or by the administrator before you can logon. When you registered it would have told you whether activation was required. If you were sent an email then follow the instructions, if you did not receive the email then are you sure your email address is valid? One reason activation is used is to reduce the possibility of <i>rouge</i> users abusing the board anonymously. If you are sure the email address you used is valid then try contacting the board administrator.'
	),
	7 => array(
		0 => 'I registered in the past but cannot login any more?!',
		1 => 'The most likely reasons for this are; you entered an incorrect username or password (check the email you were sent when you first registered) or the administrator has deleted your account for some reason. If it is the later case then perhaps you did not post anything? It is usual for boards to periodically remove users who have not posted anything so as to reduce the size of the database. Try registering again and get involved in discussions.'
	),
	8 => array(
		0 => '--',
		1 => 'User Preferences and settings'
	),
	9 => array(
		0 => 'How do I change my settings?',
		1 => 'All your settings (if you are registered) are stored in the database. To alter them click the <u>Profile</u> link (generally shown at the top of pages but this may not be the case). This will allow you to change all your settings'
	),
	10 => array(
		0 => 'The times are not correct!',
		1 => 'The times are almost certainly correct, however what you may be seeing are times displayed in a timezone different from the one you are in. If this is the case you should change your profile setting for the timezone to match your particular area, e.g. London, Paris, New York, Sydney, etc. Please note that changing the timezone, like most settings can only be done by registered users. So if you are not registered this is a good time to do so, if you pardon the pun!'
	),
	11 => array(
		0 => 'I changed the timezone and the time is still wrong!',
		1 => 'If you are sure you have set the timezone correctly and the time is still different the most likely answer is daylight savings time (or summer time as it is known in the UK and other places). The board is not designed to handle the changeovers between standard and daylight time so during summer months the time may be an hour different from the real local time.'
	),
	12 => array(
		0 => 'My language is not in the list!',
		1 => 'The most likely reasons for this are either the administrator did not install your language or someone has not translated this board into your language. Try asking the board administrator if they can install the language pack you need, if it does not exist then please feel free to create a new translation. More information can be found at the phpBB Group website (see link at bottom of pages)'
	),
	13 => array(
		0 => 'How do I show an image below my username?',
		1 => 'There may be two images below a username when viewing posts. The first is an image associated with your rank, generally these take the form of stars or blocks indicating how many posts you have made or your status on the forums. Below this may be a larger image known as an avatar, this is generally unique or personal to each user. It is up to the board administrator to enable avatars and they have a choice over the way in which avatars can be made available. If you are unable to use avatars then this is the decision of the board admin, you should ask them their reasons (we\'re sure they\'ll be good!)'
	),
	14 => array(
		0 => 'How do I change my rank?',
		1 => 'In general you cannot directly change the wording of any rank (ranks appear below your username in topics and on your profile depending on the style used). Most boards use ranks to indicate the number of posts you have made and to identify certain users, e.g. moderators and administrators may have a special rank. Please do not abuse the board by posting unnecessarily just to increase your rank, you will probably find the moderator or administrator will simply lower your post count.'
	),
	15 => array(
		0 => 'When I click the email link for a user it asks me to login?',
		1 => 'Sorry but only registered users can send email to people via the built-in email form (if the admin has enabled this feature). This is to prevent malicious use of the email system by anonymous users.'
	),
	16 => array(
		0 => '--',
		1 => 'Posting Issues'
	),
	17 => array(
		0 => 'How do I post a topic in a forum?',
		1 => 'Easy, click the relevant button on either the forum or topic screens. You may need to register before you can post a message, the facilities available to you are listed at the bottom of the forum and topic screens (the <i>You can post new topics, You can vote in polls, etc.<i> list)'
	),
	18 => array(
		0 => 'How do I edit or delete a post?',
		1 => 'Unless you are the board admin or forum moderator you can only edit or delete your own posts. You can edit a post (sometimes for only a limited time after it was made) by clicking the <i>edit</i> button for the relevant post.  If someone has already replied to the post you will find a small piece of text output below the post when you return to the topic, this lists the number of times you edited it. This will only appear if no one has replied, it also will not appear if moderators or administrators edit the post (they should leave a message saying what they altered and why). Please note that normal users cannot delete a post once someone has replied.'
	),
	19 => array(
		0 => 'How do I add a signature to my post?',
		1 => 'To add a signature to a post you must first create one, this is done via your profile. Once created you can check the <i>Add Signature</i> box on the posting form to add your signature. You can also add a signature by default to all your posts by checking the appropriate radio box in your profile (you can still prevent a signature being added to individual posts by un-checking the add signature box on the posting form)'
	),
	20 => array(
		0 => 'How do I create a poll?',
		1 => 'Creating a poll is easy, when you post a new topic (or edit the first post of a topic, if you have permission) you should see a <i>Add Poll</i> form below the main posting box (if you cannot see this then you probably do not have rights to create polls). You should enter a title for the poll and then at least two options (to set an option type in the poll question and click the <i>Add option</i> button. You can also set a time limit for the poll, 0 is an infinite poll. There will be a limit to the number of options you can list, this is set by the board administrator'
	),
	21 => array(
		0 => 'How do I edit or delete a poll?',
		1 => 'As with posts, polls can only be edited by the original poster, a moderator or board admin. To edit a poll click the first post in the topic (this always has the poll associated with it). If no one has cast a vote then users can delete the poll or edit any poll option, however if people have already placed votes only moderators or administrators can edit or delete it. This is to prevent people rigging polls by changing options mid-way through a poll'
	),
	22 => array(
		0 => 'Why can\'t I access a forum?',
		1 => 'Some forums may be limited to certain users or groups. To view, read, post, etc. you may need special authorisation, only the forum moderator and board admin can grant this access, you should contact them.'
	),
	23 => array(
		0 => 'Why can\'t I vote in polls?',
		1 => 'Only registered users can vote in polls (so as to prevent spoofing of results). If you have registered and still cannot vote then you probably do not have appropriate access rights.'
	),
	24 => array(
		0 => '--',
		1 => 'Formatting and Topic Types'
	),
	25 => array(
		0 => 'What is BBCode?',
		1 => 'BBCode is a special implementation of HTML, whether you can use BBCode is determined by the administrator (you can also disable it on a per post basis from the posting form). BBCode itself is similar in style to HTML, tags are enclosed in square braces [ and ] rather than &lt; and &gt; and it offers greater control over what and how something is displayed. For more information on BBCode see the guide which can be accessed from the posting page.'
	),
	26 => array(
		0 => 'Can I use HTML?',
		1 => 'That depends on whether the administrator allows you too, they have complete control over it. If you are allowed to use it you will probably find only certain tags work. This is a <i>safety</i> feature to prevent people abusing the board by using tags which may destroy the layout or cause other problems. If HTML is enabled you can disable it on a per post basis from the posting form.'
	),
	27 => array(
		0 => 'What are Smileys?',
		1 => 'Smileys, or Emoticons are small graphical images which can be used to express some feeling using a short code, e.g. :) means happy, :( means sad. The full list of emoticons can be seen via the posting form. Try not to overuse smileys though, they can quickly render a post unreadable and a moderator may decide to edit them out or remove the post altogether'
	),
	28 => array(
		0 => 'Can I post Images?',
		1 => 'Images can indeed be shown in your posts. However, there is no facility at present for uploading images directly to this board. Therefore you must link to an image stored on a publicly accessible web server, e.g. http://www.some-unknown-place.net/my-picture.gif. You cannot link to pictures stored on your own PC (unless it is a publicly accessible server) nor images stored behind authentication mechanisms, e.g. hotmail or yahoo mailboxes, password protected sites, etc. To display the image use either the BBCode [img] tag or appropriate HTML (if allowed).'
	),
	29 => array(
		0 => 'What are Announcements?',
		1 => 'Announcements often contain important information and you should read them as soon as possible. Announcements appear at the top of every page in the forum to which they are posted. Whether or not you can post an announcement depends on the permissions required, these are set by the administrator.'
	),
	30 => array(
		0 => 'What are Sticky topics?',
		1 => 'Sticky topics appear below any announcements in viewforum and only on the first page. They are often quite important so you should read them where possible. As with announcements the board administrator determines what permissions are required to post sticky topics in each forum.'
	),
	31 => array(
		0 => 'What are Locked topics?',
		1 => 'Locked topics are set this way by either the forum moderator or board administrator. You cannot reply to locked topics and any poll it contained is automatically ended. Topics may be locked for many reasons.'
	),
	32 => array(
		0 => '--',
		1 => 'User Levels and Groups'
	),
	33 => array(
		0 => 'What are Administrators?',
		1 => 'Administrators are people assigned the highest level of control over the entire board. These people can control all facets of board operation which includes setting permissions, banning users, creating usergroups or moderators, etc. They also have full moderator capabilities in all the forums.'
	),
	34 => array(
		0 => 'What are Moderators?',
		1 => 'Moderators are individuals (or groups of individuals) whose job it is to look after the running of the forums from day to day. They have the power to edit or delete posts and lock, unlock, move, delete and split topics in the forum they moderate. Generally moderators are there to prevent people going <i>off-topic<i> or posting abusive or offensive material.'
	),
	35 => array(
		0 => 'What are Usergroups?',
		1 => 'Usergroups are a way in which board administrators can group users. Each user can belong to several groups (this differs from most other boards) and each group can be assigned individual access rights. This makes it easy for administrators to set up several users as moderators of a forum, or to give them access to a private forum, etc.'
	),
	36 => array(
		0 => 'How do I join a Usergroup?',
		1 => 'To join a usergroup click the usergroup link on the page header (dependent on template design), you can then view all usergroups. Not all groups are <i>open access</i>, some are closed and some may even have hidden memberships. If the board is open then you can request to join it by clicking the appropriate button. The user group moderator will need to approve your request, they may ask why you want to join the group. Please do not pester a group moderator if they turn your request down, they will have their reasons.'
	),
	37 => array(
		0 => 'How do I become a Usergroup Moderator?',
		1 => 'Usergroups are initially created by the board admin, they also assign a board moderator. If you are interested in creating a usergroup then your first point of contact should be the admin, try dropping them a private message.'
	),
	38 => array(
		0 => '--',
		1 => 'Private Messaging'
	),
	39 => array(
		0 => 'I cannot send private messages!',
		1 => 'There are three reasons for this; you are not registered and/or not logged on, the board administrator has disabled private messaging for the entire board or the board administrator has prevented you from sending messages. If it is the later case you should try asking the administrator why.'
	),
	40 => array(
		0 => 'I keep getting unwanted private messages!',
		1 => 'In the future we will be adding an ignore list to the private messaging system. For now though if you keep receiving unwanted private messages from someone inform the board admin, they have the power to prevent a user from sending private messages at all.'
	),
	41 => array(
		0 => 'I have received a spamming or abusive email from someone on this board!',
		1 => 'We are sorry to hear that. The email form feature of this board includes safeguards to try and track users who send such posts. You should email the board administrator with a full copy of the email you received, it is very important this include the headers (these list details of the user that sent the email). They can then take action.'
	),
	42 => array(
		0 => '--',
		1 => 'phpBB 2 Issues'
	),
	43 => array(
		0 => 'Who wrote this bulletin board?',
		1 => 'This software (in its unmodified form) is produced, released and is copyright  <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. It is made available under the GNU General Public Licence and may be freely distributed, see link for more details'
	),
	44 => array(
		0 => 'Why isn\'t X feature available?',
		1 => 'This software was written by and licensed through phpBB Group. If you believe a feature needs to be added then please visit the phpbb.com website and see what phpBB Group have to say. Please do not post feature requests to the board at phpbb.com, the Group uses sourceforge to handle tasking of new features. Please read through the forums and see what, if any, our position may already be for a feature and then follow the procedure given there.'
	),
	45 => array(
		0 => 'Who do I contact about abusive and/or legal matters related to this board?',
		1 => 'You should contact the administrator of this board. If you cannot find who this you should first contact one of the forum moderators and ask them who you should in turn contact. If still get no response you should contact the owner of the domain (do a whois lookup) or, if this is running on a free service (e.g. yahoo, free.fr, f2s.com, etc.), the management or abuse department of that service. Please note that phpBB Group has absolutely no control and cannot in any way be held liable over how, where or by whom this board is used. It is absolutely pointless contacting phpBB Group in relation to any legal (cease and desist, liable, defamatory comment, etc.) matter not directly related to the phpbb.com website or the discrete software of phpBB itself. If you do email phpBB Group about any third party use of this software then you should expect a terse response or no response at all.'
	)
);
?>