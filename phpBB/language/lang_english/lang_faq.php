<?php
/***************************************************************************
 *                                  faq-english.php
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
 // DO NOT forget the ; at the end of the line.
 // Do not put double quotes (") in your FAQ entries, or if you do escape them ie: \"something\"
 //
 // The FAQ items will appear on the FAQ page in the same order they are listed in this file
 //
 
  
 $faq[] = array("Why do email addresses look like 'name at domain.com'?", "They look this way in order to reduce the possibility of email address gathering programs from finding your email address. These 'bots' look for email addresses in web pages and store them where they are often used to send you spam email. In order to send email to someone you will need to remove the ' at ' in the address and replace it with usual '@'");
 $faq[] = array("Why do I get logged off automatically?", "If you do not check the 'Log me in automatically' box when you login the board will only keep you logged in for a preset time. This prevents misuse of your account by anyone else. To stay logged in check the box during login, this is not recommended if you access the board from a shared computer, eg. library, internet cafe, university cluster, etc.");
 $faq[] = array("Why can't I upload an avatar image?", "An avatar is a small image generally unique to a user displayed next to their posts. It is up to the board administrator to enable avatars (and the way in which avatars can be set). If you are unable to use avatars then this is the decision of the board admin, you should ask them their reasons (we're sure they'll be good!)");
 $faq[] = array("Why am I showing up more than once in the online listing?", "You shouldn't! phpBB 2.0 includes controls to prevent a user being logged in twice from different machines. However, if the connection between your machine and the system serving this board is interrupted mid-transmission it may possibly corrupt the cookie data used to identify you. In this case the session management code will interpret you as a 'returning user' (if you checked the automatic login box) and create a new session. If you suspect this is not the problem you should contact the board administrator as soon as possible.");
 $faq[] = array("Why can't I login?", "Have you registered?! Seriously, you must register in order to login. Have you been banned from the board (a message will be displayed if you have)? If so then you should contact the webmaster or board administrator to find out why. If you have registered and are not banned and you still cannot login then check and double check your username and password. Usually this is the problem, if not then contact the board administrator they may have incorrect configuration settings for the board.");
 $faq[] = array("I keep getting unwanted private messages!", "In the future we will be adding an ignore list to the private messaging system. For now though if you keep receiving unwanted private messages from someone inform the board admin, they have the power to prevent a user from sending private messages at all.");
 $faq[] = array("How do I become a usergroup moderator?", "Usergroups are initially created by the board admin, they also assign a board moderator. If you are interested in creating a usergroup then your first point of contact should be the admin.");
 $faq[] = array("How do I join a usergroup?", "To join a usergroup click the usergroup link on the page header (dependent on template design), you can then view all usergroups and request to join one. The board administrator will need to approve your request, they may ask why you want to join the group. Please don't pester a group moderator if they turn your request down, they'll have their reasons.");
 $faq[] = array("Why can't I access X forum?", "Some groups may be limited to certain users or groups. To view, read, post, etc. you may need special authorisation, only the forum moderator and board admin can grant this access, you should contact them.");
 $faq[] = array("How do I prevent my username appearing in the online user listings?", "In your profile you will find an option 'Hide your online status', if you switch this 'on' you'll only appear to board admins.");
 ?>
 