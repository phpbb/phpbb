<?php
/***************************************************************************
 *                              admin_words.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
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

if($setmodules == 1)
{
	$file = basename(__FILE__);
	$module['Styles']['Add_new'] = "$file?mode=addnew";
	$module['Styles']['Create_new'] = "$file?mode=create";
	$module['Styles']['Manage'] = "$file";
	$module['Styles']['Export'] = "$file?mode=export";
	return;
}

//
// Load default header
//
$phpbb_root_dir = "./../";

//
// Check if the user has cancled a confirmation message.
//
$confirm = ( $HTTP_POST_VARS['confirm'] ) ? TRUE : FALSE;
$cancel = ( $HTTP_POST_VARS['cancel'] ) ? TRUE : FALSE;

if($cancel)
{
	header("Location: $PHP_SELF");
}

if(!$HTTP_POST_VARS['send_file'])
{
	require('pagestart.inc');
}

if(isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']) )
{
	$mode = ($HTTP_GET_VARS['mode']) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];
}
else 
{
	$mode = "";
}

switch($mode)
{
	case "addnew":
	$install_to = ($HTTP_GET_VARS['install_to']) ? urldecode($HTTP_GET_VARS['install_to']) : $HTTP_POST_VARS['install_to'];
	$style_name = ($HTTP_GET_VARS['style']) ? urldecode($HTTP_GET_VARS['style']) : $HTTP_POST_VARS['style'];
	
	if(isset($install_to))
	{
		include($phpbb_root_dir . "templates/" . $install_to . "/theme_info.cfg");
		$template_name = $$install_to;
		$found = FALSE; 
		
		for($i = 0; $i < count($template_name) && !$found; $i++)
		{
			if($template_name[$i]['style_name'] == $style_name)
			{
				while(list($key, $val) = each($template_name[$i]))
				{
					$db_fields[] = $key;
					$db_values[] = $val;
				}
			}
		}
				
		$sql = "INSERT INTO " . THEMES_TABLE . " (";
		for($i = 0; $i < count($db_fields); $i++)
		{
			$sql .= $db_fields[$i];
			if($i != (count($db_fields) - 1))
			{
				$sql .= ", ";
			}
			
		}
		$sql .= ") VALUES (";
		for($i = 0; $i < count($db_values); $i++)
		{
			$sql .= "'" . $db_values[$i] . "'";
			if($i != (count($db_values) - 1))
			{
				$sql .= ", ";
			}
		}
		$sql .= ")";
		
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not insert theme data!", "Error", __LINE__, __FILE__, $sql);
		}
		
		message_die(GENERAL_MESSAGE, $lang['Theme_installed'], $lang['Success']);

	}
	else
	{
		
		$installable_themes = array();
		
		if($dir = opendir($phpbb_root_dir . "templates/"))
		{
			while($sub_dir = readdir($dir))
			{
				if($sub_dir != "." && $sub_dir != ".." && $sub_dir != "CVS")
				{
					if(file_exists($phpbb_root_dir . "templates/" . $sub_dir . "/theme_info.cfg"))
					{
						include($phpbb_root_dir . "templates/" . $sub_dir . "/theme_info.cfg");
						
						for($i = 0; $i < count($$sub_dir); $i++)
						{
							$working_data = $$sub_dir;
							
							$style_name = $working_data[$i]['style_name'];
													
							$sql = "SELECT themes_id FROM " . THEMES_TABLE . " WHERE style_name = '$style_name'";
							if(!$result = $db->sql_query($sql))
							{
								message_die(GENREAL_ERROR, "Could not query themes table!", "Error", __LINE__, __FILE__, $sql);
							}
							if(!$db->sql_numrows($result))
							{
								$installable_themes[] = $working_data[$i];
							}
						}
					}
				}
			}
			
			$template->set_filenames(array(
				"body" => "admin/styles_addnew_body.tpl")
			);
			
			$template->assign_vars(array(
				"L_STYLES_TITLE" => $lang['Styles_admin'],
				"L_STYLES_ADD_TEXT" => $lang['Styles_addnew_explain'],
				"L_STYLE" => $lang['Style'],
				"L_TEMPLATE" => $lang['Template'],
				"L_INSTALL" => $lang['Install'],
				"L_ACTION" => $lang['Action'])
			);
				
			for($i = 0; $i < count($installable_themes); $i++)
			{
				$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
				$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
	
				$template->assign_block_vars("styles", array(
					"STYLE_NAME" => $installable_themes[$i]['style_name'],
					"TEMPLATE_NAME" => $installable_themes[$i]['template_name'],
					"ROW_CLASS" => $row_class,
					"ROW_COLOR" => $row_color,
					"U_STYLES_INSTALL" => append_sid("admin_styles.$phpEx?mode=addnew&style=" . urlencode($installable_themes[$i]['style_name']) . "&install_to=" . urlencode($installable_themes[$i]['template_name'])))
				);
			
			}
			$template->pparse("body");
				
		}
		closedir($dir);
	}
		
	
	break;
	
	case "create":
	case "edit":
	
	
	break;
	case "export";
	
	if($HTTP_POST_VARS['export_template'])
	{
		$template_name = $HTTP_POST_VARS['export_template'];
		$sql = "SELECT * FROM " . THEMES_TABLE . " WHERE template_name = '$template_name'";
		
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not get theme data for selected template", "Error", __LINE__, __FILE__, $sql);
		}
		
		$theme_rowset = $db->sql_fetchrowset($result);
		
		if(count($theme_rowset) == 0)
		{
			message_die(GENERAL_MESSAGE, $lang['No_themes'], $lang['Export_themes']);
		}
		
		$theme_data = '<?php'."\n\n";
		$theme_data .= "//\n// phpBB 2.x auto-generated theme config file for $template_name\n// Do not change anything in this file!\n//\n\n";


		for($i = 0; $i < count($theme_rowset); $i++)
		{
			while(list($key, $val) = each($theme_rowset[$i]))
			{
				if(!intval($key) && $key != "0")
				{
					$theme_data .= '$' . $template_name . "[$i][$key] = \"$val\";\n";
				}
			}
			$theme_data .= "\n";
		}
		
		$theme_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!
		
		
		@umask(0111);

		$fp = @fopen($phpbb_root_path . 'templates/' . $template_name . '/theme_info.cfg', 'w');
		if( !$fp )
		{
			//
			// Unable to open the file writeable do something here as an attempt
			// to get around that...
			//
			$s_hidden_fields = '<input type="hidden" name="theme_info" value="' . htmlspecialchars($theme_data) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="send_file" value="1" /><input type="hidden" name="mode" value="export" />';
			
			$download_form = '<form action="'. append_sid("admin_styles.$phpEx") .'" method="POST"><input type="submit" name="submit" value="' . $lang['Download'] . '" />' . $s_hidden_fields;
			$template->set_filenames(array(
				"body" => "message_body.tpl")
			);

			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Export_themes'],
				"MESSAGE_TEXT" => $lang['Download_theme_cfg'] . "<br />" . $download_form)
			);

			$template->pparse('body');
			exit();
		}

		$result = @fputs($fp, $theme_data, strlen($theme_data));
		fclose($fp);
		
		message_die(GENERAL_MESSAGE, $lang['Theme_info_saved'], $lang['Success']);


	}
	else if($HTTP_POST_VARS['send_file'])
	{
		
		header("Content-Type: text/x-delimtext; name=\"theme_info.cfg\"");
		header("Content-disposition: attachment; filename=theme_info.cfg");
		if( get_magic_quotes_gpc() )
		{
			$HTTP_POST_VARS['theme_info'] = stripslashes($HTTP_POST_VARS['theme_info']);
		}
		echo $HTTP_POST_VARS['theme_info'];
	}
	else
	{
		$template->set_filenames(array(
			"body" => "admin/styles_exporter.tpl")
		);
		
		if($dir = opendir($phpbb_root_path . 'templates/'))
		{	
			$s_template_select = '<select name="export_template">';
			while($file = readdir($dir))
			{	
				if($file != "." && $file != ".." && $file != "CVS")
				{
					$s_template_select .= '<option value="' . $file . '">' . $file . "</option>\n";
				}
			}
		}
		else
		{
			message_die(GENERAL_ERROR, $lang['No_template_dir'], $lang['Error'], __LINE__, __FILE__);
		}
		
		$template->assign_vars(array(
			"L_STYLE_EXPORTER" => $lang['Export_themes'],
			"L_EXPORTER_EXPLAIN" => $lang['Export_explain'],
			"S_EXPORTER_ACTION" => append_sid("$PHP_SELF?mode=export"),
			"L_TEMPLATE_SELECT" => $lang['Select_template'],
			"S_TEMPLATE_SELECT" => $s_template_select,
			"L_SUBMIT" => $lang['Submit'])
		);
		
		$template->pparse("body");
		
	}

	break;
	case "delete":
		$style_id = ($HTTP_GET_VARS['style_id']) ? intval($HTTP_GET_VARS['style_id']) : intval($HTTP_POST_VARS['style_id']);
		
		if(!$confirm)
		{
			$hidden_fields = '<input type="hidden" name="mode" value="'.$mode.'" /><input type="hidden" name="style_id" value="'.$style_id.'" />';
			
			//
			// Set template files
			//
			$template->set_filenames(array(
				"confirm" => "confirm_body.tpl")
			);

			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Confirm'],
				"MESSAGE_TEXT" => $lang['Confirm_delete_style'],

				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],

				"S_CONFIRM_ACTION" => append_sid("admin_styles.$phpEx"),
				"S_HIDDEN_FIELDS" => $hidden_fields)
			);

			$template->pparse("confirm");

		}
		else
		{
			//
			// The user has confirmed the delete. Remove the style, the style element names and update any users
			// who might be using this style
			//
			$sql = "DELETE FROM " . THEMES_TABLE . " WHERE themes_id = $style_id";
			if(!$result = $db->sql_query($sql, BEGIN_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not remove style data!", "Error", __LINE__, __FILE__, $sql);
			}
			
			$sql = "DELETE FROM " . THEMES_NAME_TABLE . " WHERE themes_id = $style_id";
	
			// There may not be any theme name data so don't throw an error if the SQL dosan't work
			$db->sql_query($sql);
			
			$sql = "UPDATE " . USERS_TABLE . " SET user_style = " . $board_config['default_style'] . " WHERE user_style = $style_id";
			if(!$result = $db->sql_query($sql, END_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Could not update user style information", "Error", __LINE__, __FILE__, $sql);
			}
			
			message_die(GENERAL_MESSAGE, $lang['Style_removed'], $lang['Success']);
		}
		
		
	break;
	default:
		
		$sql = "SELECT themes_id, template_name, style_name FROM phpbb_themes ORDER BY template_name";
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not get style information!", "Error", __LINE__, __FILE__, $sql);
		}
		
		$style_rowset = $db->sql_fetchrowset($result);
		
		$template->set_filenames(array(
			"body" => "admin/styles_list_body.tpl")
		);

		$template->assign_vars(array("L_STYLES_TITLE" => $lang['Styles_admin'],
			"L_STYLES_TEXT" => $lang['Styles_explain'],
			"L_STYLE" => $lang['Style'],
			"L_TEMPLATE" => $lang['Template'],
			"L_EDIT" => $lang['Edit'],
			"L_DELETE" => $lang['Delete']));
					
		for($i = 0; $i < count($style_rowset); $i++)
		{
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("styles", array("ROW_CLASS" => $row_class,
				"ROW_COLOR" => $row_color,
				"STYLE_NAME" => $style_rowset[$i]['style_name'],
				"TEMPLATE_NAME" => $style_rowset[$i]['template_name'],
				"U_STYLES_EDIT" => append_sid("$PHP_SELF?mode=edit&style_id=" . $style_rowset[$i]['themes_id']),
				"U_STYLES_DELETE" => append_sid("$PHP_SELF?mode=delete&style_id=" . $style_rowset[$i]['themes_id'])));
		}
		
		$template->pparse("body");	
			
	
	break;
}

if(!$HTTP_POST_VARS['send_file'])
{
	include('page_footer_admin.'.$phpEx);
}

?>