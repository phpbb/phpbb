<?php

/***************************************************************************
 *                             lang_bot_admin.php
 *                            --------------------
 *   begin                : Sunday, February 13, 2005
 *   copyright            : (C) 2004 Adam Marcus
 *  
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

$lang['Manage_Bots'] = "Manage Bots";
$lang['Bot_Explain'] = "Bots (also known as crawlers) are automated agents most commonly used to index information on the internet. Very few of these bots support sessions and can therefore fail to index your site correctly. Here you can define the assigning of session ids to these bots to solve this problem.";
$lang['L_SUPPORT'] = '<a href="http://www.searchmebaby.com" target="_blank">Support Forum</a>';
$lang['Pending_Bots'] = "Pending Bots";
$lang['Pending_Explain'] = "Listed below are users that matched some but not all of your bot criteria. In other words the user only matched either the user agent or ip. The mismatched data is the highlighted next to the bot name. You can choose to either add this info which will then appear as part of that bots criteria or ignore it.";

$lang['Bot_Agent'] = "Bot agent";
$lang['Bot_Name'] = "Bot name";
$lang['Bots'] = "bots"; 
$lang['Agent_Match'] = "Agent match";
$lang['Bot_Ip'] = "Bot IP";
$lang['Bot_Style'] = "Bot style";

$lang['Last_Visit'] = "Last visit";
$lang['Visits'] = "Visits";
$lang['Pages'] = "Pages";
$lang['Never'] = "Never";
$lang['Options'] = "Options";
$lang['Result'] = "Result";
$lang['Ok'] = "Ok";
$lang['Mark'] = "Mark";
$lang['Ignore'] = "Ignore";
$lang['Add'] = "Add";

$lang['Submit'] = "Submit";
$lang['Delete'] = "Delete";
$lang['Reset'] = "Reset";
$lang['Edit'] = "Edit";

$lang['ip'] = "ip";
$lang['agent'] = "agent";

$lang['No_Bots'] = "Sorry there are currently no bots in the database!";
$lang['No_Pending_Bots'] = "Sorry there are currently no pending bots in the database!";
$lang['Bot_Added_Or_Modified'] = "Bot information successfully added/ignored."; 
$lang['Bot_Result_Explain'] = "Here you can see the result of your query.";
$lang['Bot_Settings_Changed'] = "Bot settings successfully modified/added.";

$lang['Bot_Edit_Or_Add_Explain'] = "Here you can either add or modify an existing bot entry. You are able to supply either a matching user agent or a range of ip's to use.";
$lang['Bot_Name_Explain'] = "Used for your use only.";
$lang['Bot_Agent_Explain'] = "A matching user agent. Partial matches are allowed. Seperate agents with a single '|'.";
$lang['Bot_Ip_Explain'] = "Partial matches are allowed. Seperate IP addresses with a single '|'.";
$lang['Bot_Style_Explain'] = "Style bot sees when visiting your site.";

$lang['Error_No_Agent_Or_Ip'] = "You have no supplied a vaild user agent or ip.";
$lang['Error_No_Bot_Name'] = "You have not supplied a bot name.";
$lang['Error_Bot_Name_Taken'] = "That bot name is already taken.";
$lang['Error_Own_Ip'] = "You can't use your own Ip as a bot Ip, using your own Ip will get you locked out of the admin cp. If you need to test the script Google search for a spider simulator.";
?>