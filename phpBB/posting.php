<?php
/***************************************************************************
 *                                 posting.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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
 *
 ***************************************************************************/
include('extension.inc');
include('common.'.$phpEx);

if($submit && !$preview)
{
   switch($mode)
     {
      case 'newtopic':
	echo "Dave likes to submit<br>";

	break;
      case 'reply':

	break;
      case 'editpost':

	break;
     }
}
else
{

   switch($mode)
     {
      case 'newtopic':
			if(!isset($forum_id))
			{
				error_die(GENERAL_ERROR, "Sorry, no there is no such forum");
			}

			$pagetype = "newtopic";
			$page_title = " $l_postnew";
			$sql = "SELECT forum_name, forum_access FROM ".FORUMS_TABLE." WHERE forum_id = '$forum_id'";
			if(!$result = $db->sql_query($sql))
			{
				error_die(SQL_QUERY, "Could not obtain forum/forum access information.", __LINE__, __FILE__);
			}
			$forum_info = $db->sql_fetchrowset($result);
			$forum_name = stripslashes($forum_info[0]["forum_name"]);
			$forum_access = $forum_info[0]["forum_access"];

			if($forum_access == ANONALLOWED)
			{
				$about_posting = "$l_anonusers $l_inthisforum $l_anonhint";
			}
			if($forum_access == REGONLY)
			{
				$about_posting = "$l_regusers $l_inthisforum";
			}
			if($forum_access == MODONLY)
			{
				$about_posting = "$l_modusers $l_inthisforum";
			}

			include('includes/page_header.'.$phpEx);
			if($user_logged_in)
			{
				$username_input = $userdata["username"];
				$password_input = "";
			}
			else
			{
				if(!isset($username))
				{
					$username = $userdata["username"];
				}
				$username_input = '<input type="text" name="username" value="'.$username.'" size="25" maxlength="50">';
				$password_input = '<input type="password" name="password" size="25" maxlenght="40">';
			}
			$subject_input = '<input type="text" name="subject" value="'.$subject.'" size="50" maxlenght="255">';
			$message_input = '<textarea name="message" rows="10" cols="35" wrap="virtual">'.$message.'</textarea>';
			if($allow_html)
			{
				$html_status = $l_htmlis . " " . $l_on;
				$html_toggle = '<input type="checkbox" name="disable_html" ';
				if($disable_html)
				{
					$html_toggle .= 'checked';
				}
				$html_toggle .= "> $l_disable $l_html $l_onthispost";
			}
			else
			{
				$html_status = $l_htmlis . " " . $l_off;
			}
			if($allow_bbcode)
			{
				$bbcode_status = $l_bbcodeis . " " . $l_on;
				$bbcode_toggle = '<input type="checkbox" name="disable_bbcode" ';
				if($disable_bbcode)
				{
					$bbcode_toggle .= "checked";
				}
				$bbcode_toggle .= "> $l_disable $l_bbcode $l_onthispost";
			}
			else
			{
				$bbcode_status = $l_bbcodeis . " " . $l_off;
			}

			$smile_toggle = '<input type="checkbox" name="disable_smile" ';
			if($disable_smile)
			{
				$smile_toggle .= "checked";
			}
			$smile_toggle .= "> $l_disable $l_smilies $l_onthispost";

			$sig_toggle = '<input type="checkbox" name="attach_sig" ';
			if($attach_sig || $userdata["attach_sig"] == 1)
			{
				$sig_toggle .= "checked";
			}
			$sig_toggle .= "> $l_attachsig";

			$notify_toggle = '<input type="checkbox" name="notify" ';
			if($notify || $userdata["always_notify"] == 1)
			{
				$notify_toggle .= "checked";
			}
			$notify_toggle .= "> $l_notify";


			$template->assign_vars(array("L_ABOUTPOST" => $l_aboutpost,
											"L_SUBJECT" => $l_subject,
											"L_MESSAGEBODY" => $l_body,
											"L_OPTIONS" => $l_options,
											"L_PREVIEW" => $l_preview,
											"L_SUBMIT" => $l_submit,
											"L_CANCEL" => $l_cancelpost,
											"MODE" => $mode,
											"ABOUT_POSTING" => $about_posting,
											"USERNAME_INPUT" => $username_input,
											"PASSWORD_INPUT" => $password_input,
											"SUBJECT_INPUT" => $subject_input,
											"MESSAGE_INPUT" => $message_input,
											"HTML_STATUS" => $html_status,
											"HTML_TOGGLE" => $html_toggle,
											"SMILE_TOGGLE" => $smile_toggle,
											"SIG_TOGGLE" => $sig_toggle,
											"NOTIFY_TOGGLE" => $notify_toggle,
											"BBCODE_TOGGLE" => $bbcode_toggle,
											"BBCODE_STATUS" => $bbcode_status));
			$template->pparse("body");
			include('includes/page_tail.'.$phpEx);
	break;
      case 'reply':

	break;
      case 'editpost':

	break;
     }
}


?>
