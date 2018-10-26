<?php
/***************************************************************************
 *                             admin_bots.php
 *                            -------------------
 *   begin                : Sunday, February 13, 2005
 *   copyright            : (C) 2004 Adam Marcus
 *   email                : adam_marcus@btinternet.com
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

@define('IN_PHPBB', true);

if( !empty($setmodules) )
{
	$filename = basename(__FILE__);
	$module['Site_Promotion']['Manage Bots'] = $filename;
	return;
}

// load default header
$phpbb_root_path = './../';
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);

include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_bot_admin.' . $phpEx);


// errors - mwhahahaha
$bot_errors = "";

// get relevant query data
$submit = ((isset($_POST['submit'])) ? true : false);
if (isset($_GET['action']) || isset($_POST['action']))
{
	$action = (isset($_POST['action'])) ? $_POST['action'] : $_GET['action'];
}
else
{
	$action = '';
}
$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
$mark = (isset($_POST['mark'])) ? $_POST['mark'] : 0;
if (isset($_POST['add'])) $action = 'add';

// editing and marks don't go well together...
if ( ( sizeof($mark) != 1 ) && $action == "edit" ) $action = '';
if ( ((sizeof($mark)) ?  $mark != '' : false ) && $action == "edit" ) 
{
	$id = $mark[0];
	$submit = false;
}


// hmmmmmm what does the user want to do?
switch ($action)
{
	case 'ignore_pending':
	case 'add_pending':
		// get required query data
		$pending_number = $_GET['pending']; 
		$pending_data = $_GET['data']; 

		// get data from table
		$sql = "SELECT pending_" . $pending_data . " 
		FROM " . BOTS_TABLE . " 
		WHERE bot_id = " . $id;

		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Couldn\'t obtain bot data.', '', __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		// seperate data into a list
		$pending_array = explode('|', $row['pending_' . $pending_data]);

		if ($action == 'add_pending')
		{
			$new_data = $pending_array[($pending_number-1)*2];
		}

		array_splice($pending_array,  ($pending_number-1)*2, 2);
		$pending = implode("|", $pending_array);

		// update table
		$sql = "UPDATE " . BOTS_TABLE . " 
		SET pending_" . $pending_data . "='$pending'
		WHERE bot_id = " . $id;

		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Couldn\'t update data in bots table.', '', __LINE__, __FILE__, $sql);
		}

		if ($action == "add_pending")
		{
			// get data from table
			$sql = "SELECT bot_" . $pending_data . " 
			FROM " . BOTS_TABLE . " 
			WHERE bot_id = " . $id;

			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Couldn\'t obtain bot data.', '', __LINE__, __FILE__, $sql);
			}

			$row = $db->sql_fetchrow($result);

			// seperate data into a list
			$pending_array = explode('|', $row['bot_' . $pending_data]);

			// replace delimeter to prevent errors
			$new_data = str_replace("|", "&#124;", $new_data);

			$pending_added = false;

			// are we dealing with an ip or user agent?
			if ($pending_data == "ip")
			{

				// loop through ip's
				for ( $loop = 0; $loop < count($pending_array); $loop++)
				{
					$ip_found = false;

					for ( $limit = 9; $limit <= 15; $limit++ )
   					{
						if (strcmp(substr($pending_array[$loop],0,$limit) , substr($new_data, 0, $limit))!=0)
						{
							if ($ip_found == true)
							{
								$pending_array[$loop] = substr($pending_array[$loop],0,($limit-1));
								$pending_added = true;
							}
						} else {
							$ip_found = true;
						}
					}
				}
			} else {

				// loop through user agent's
				for ( $loop = 0; $loop < count($pending_array); $loop++)
				{
					// which user agent string is shorter?
					$smaller_string = ( ( strlen($pending_array[$loop]) > strlen($new_data) ) ? $new_data : $pending_array[$loop]);
					$larger_string = ( ( strlen($pending_array[$loop]) < strlen($new_data) ) ? $new_data : $pending_array[$loop]);

					// shortest user agent string too short?
					if (strlen($smaller_string) <= 6) continue;

					for ( $limit = strlen($smaller_string); $limit > 6; $limit-- )
   					{
						for ($loop2 = 0; $loop2 < (strlen($smaller_string)-$limit)+1; $loop2++)
						{
							if (strstr($larger_string, substr($smaller_string, $loop2, $limit)))
							{
								$pending_array[$loop] = $smaller_string;
								$pending_added = true;
							}
						}
					}
				}
			}

			// insert new data into array
			if (!$pending_added) $pending_array[] = $new_data;

			$pending = implode("|", $pending_array);

			// update table
			$sql = "UPDATE " . BOTS_TABLE . " 
			SET bot_" . $pending_data . "='$pending'
			WHERE bot_id = " . $id;

			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Couldn\'t update data in bots table.', '', __LINE__, __FILE__, $sql);
			}
		}

		// load bot added template
		$template->set_filenames(array(
			"body" => "admin/bots_added.tpl")
		);

		$template->assign_vars(array(
			"S_BOTS_ACTION" => append_sid("admin_bots.$phpEx"),

			"L_BOTS_TITLE" => ( ($action == 'add_pending') ? $lang['Add'] : $lang['Ignore'] ) . " " . $lang['Bots'],
			"L_BOTS_EXPLAIN" => $lang['Bot_Result_Explain'],

			"L_BOT_OK" => $lang['Ok'],
			"L_BOT_RESULT" => $lang['Result'] . ":",
			"L_BOT_ADDED" => $lang['Bot_Added_Or_Modified'])
		);

		// display the page!
		$template->pparse("body");

		include('./page_footer_admin.'.$phpEx);

		break;
	case 'delete':
		// are we actually deleting something or do people just like clicking links...
		if ($id || $mark)
		{
			$id = ($id) ? " = $id" : ' IN (' . implode(', ', $mark) . ')';

			// do the delete!
			$sql = "DELETE FROM " . BOTS_TABLE . " 
				WHERE bot_id $id";

			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Couldn\'t delete data from bots table.', '', __LINE__, __FILE__, $sql);
			}

		}
		break;

	case 'add':
	case 'edit':
		// get data from table

		// check if data has been submitted?
		if ($submit)
		{
			// get and validate required submitted data - for some reason isset doesn't work here?!
			if ( $_POST['bot_ip'] == '' )
			{
				if ( $_POST['bot_agent'] == '')
				{
					$bot_errors = $lang['Error_No_Agent_Or_Ip'];
				}
			}
			if ( $_SERVER['REMOTE_ADDR'] == $_POST['bot_ip'])
			{
			$bot_errors = $lang['Error_Own_Ip'];
             }
			if ( $_POST['bot_name'] != '' )
			{
				$bot_name = $_POST['bot_name'];
			}
			else
			{
				$bot_errors = $lang['Error_No_Bot_Name'];
			}
			
			if ($action == 'edit')
			{
				$sql = "SELECT bot_name
					FROM " . BOTS_TABLE . "
					WHERE bot_id = $id";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Couldn\'t delete data from bots table.', '', __LINE__, __FILE__, $sql);
				}
				 $row = $db->sql_fetchrow($result);
				 $current_name = $row['bot_name'];
				 $db->sql_freeresult($result);
			  
			   $sql = "SELECT bot_name
					FROM " . BOTS_TABLE . "
					WHERE bot_name = '$bot_name'";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Couldn\'t delete data from bots table.', '', __LINE__, __FILE__, $sql);
				}
				
				$sql_bot_name_check = $db->sql_numrows($result);
				$db->sql_freeresult($result);
		
				if(($sql_bot_name_check > 0) && ($current_name != $bot_name))
				{
					$bot_errors = $lang['Error_Bot_Name_Taken'];
				}
			}
			
			if ($action == 'add')
			{
				 $sql = "SELECT bot_name
					FROM " . BOTS_TABLE . "
					WHERE bot_name = '$bot_name'";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Couldn\'t delete data from bots table.', '', __LINE__, __FILE__, $sql);
				}
				$sql_bot_name_check = $db->sql_numrows($result);
				
				if($sql_bot_name_check > 0)
				{
					$bot_errors = $lang['Error_Bot_Name_Taken'];
				}
			}
			
			if (!$bot_errors)
			{
				$bot_agent = ( ( $_POST['bot_agent'] != '' ) ? $_POST['bot_agent'] : '' );
				$bot_ip = ( ( $_POST['bot_ip'] != '' ) ? $_POST['bot_ip'] : '' );
				$bot_color = $_POST['style'];
				
				// remove spaces from ip
				$bot_ip = str_replace(' ', '', encode_ip($bot_ip));
				
				// are we creating a new bot - or not?
				if ($action == 'add')
				{
					$sql = "INSERT INTO " . BOTS_TABLE . " (bot_name, bot_agent, bot_ip, bot_color)
						  VALUES ('$bot_name', '$bot_agent', '$bot_ip', '$bot_color')";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Couldn\'t insert data into bots table.', '', __LINE__, __FILE__, $sql);
					}
				} 
				else 
				{
					$sql = "UPDATE " . BOTS_TABLE . " 
						  SET bot_name='$bot_name', bot_agent='$bot_agent', bot_ip='$bot_ip', bot_color='$bot_color' 
						  WHERE bot_id = $id";

					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Couldn\'t update data in bots table.', '', __LINE__, __FILE__, $sql);
					}
				}

				// load bot added template
				$template->set_filenames(array(
					"body" => "admin/bots_added.tpl")
				);

				$template->assign_vars(array(
					"S_BOTS_ACTION" => append_sid("admin_bots.$phpEx"),

					"L_BOTS_TITLE" => ( ($action == 'edit') ? $lang['Edit'] : $lang['Add'] ) . " " . $lang['Bots'],
					"L_BOTS_EXPLAIN" => $lang['Bot_Result_Explain'],

					"L_BOT_OK" => $lang['Ok'],
					"L_BOT_RESULT" => $lang['Result'] . ":",
					"L_BOT_ADDED" => $lang['Bot_Settings_Changed'])
				);

				// finish off another wonderful page!
				$template->pparse("body");

				include('./page_footer_admin.'.$phpEx);

				// free the result
				$db->sql_freeresult($result);
			}

		} 

		if (!$submit || $bot_errors)
		{

			// load new template
			$template->set_filenames(array(
				"body" => "admin/bots_add_body.tpl")
			);

			if ($id) 
			{
				// get required bot data
				$sql = "SELECT bot_name, bot_agent, bot_ip, bot_color
				FROM " . BOTS_TABLE . "
				WHERE bot_id = $id";

				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Couldn\'t get data from bots table.', '', __LINE__, __FILE__, $sql);
				}

				$row = $db->sql_fetchrow($result);

				// free the result
				$db->sql_freeresult($result);

				$sql = "SELECT template_name FROM " . THEMES_TABLE;
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Couldn\'t get data from themes table.', '', __LINE__, __FILE__, $sql);
				}

				$loop = 0;
				$bot_style='';
				while ($row2 = $db->sql_fetchrow($result))
				{
					$loop++;
					$bot_style .= "<option " . (($loop == $row['bot_color'])? "selected" : "") . " value='$loop'>" . $row2['template_name'] . "</option>";
				}

				$template->assign_vars(array(
					"BOT_NAME" => $row['bot_name'],
					"BOT_AGENT" => $row['bot_agent'],
					"BOT_IP" => decode_ip($row['bot_ip'])
					)
				);
			}
		}

		if ($bot_errors)
		{
			$template->assign_block_vars('errorrow', array(
				'BOT_ERROR' => $bot_errors)
			);
		}

		if (!$bot_style)
		{
			$sql = "SELECT template_name FROM " . THEMES_TABLE;
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Couldn\'t get data from themes table.', '', __LINE__, __FILE__, $sql);
			}
	
			$loop = 0;
			$bot_style='';
			while ($row2 = $db->sql_fetchrow($result))
			{
				$loop++;
				$bot_style .= "<option value='$loop'>" . $row2['template_name'] . "</option>";
			}
		}

		$template->assign_vars(array(
			"S_BOTS_ACTION" => append_sid("admin_bots.$phpEx") . "&action=" . $action . "&id=" . $id,

			"BOT_STYLE" => $bot_style,

			"L_BOTS_TITLE" => ( ($action == 'edit') ? $lang['Edit'] : $lang['Add'] ) . " " . $lang['Bots'],
			"L_BOTS_EXPLAIN" => $lang['Bot_Edit_Or_Add_Explain'],

			"L_BOT_SUBMIT" => $lang['Submit'],
			"L_BOT_RESET" => $lang['Reset'],

			"L_BOT_NAME" => $lang['Bot_Name'],
			"L_BOT_AGENT" => $lang['Agent_Match'],
			"L_BOT_IP" => $lang['Bot_Ip'],
			"L_BOT_STYLE" => $lang['Bot_Style'],

			"L_BOT_NAME_EXPLAIN" => $lang['Bot_Name_Explain'],
			"L_BOT_AGENT_EXPLAIN" => $lang['Bot_Agent_Explain'],
			"L_BOT_IP_EXPLAIN" => $lang['Bot_Ip_Explain'],
			"L_BOT_STYLE_EXPLAIN" => $lang['Bot_Style_Explain'])
		);

		// write the page! yay!
		$template->pparse("body");

		include('./page_footer_admin.'.$phpEx);

		break;

}

// load default template
$template->set_filenames(array(
	"body" => "admin/bots_body.tpl")
);

// VERY approximately calculate total site pages!
$total_posts = get_db_stat('postcount');
$total_users = get_db_stat('usercount');
$total_topics = get_db_stat('topiccount');

$total_pages = floor($total_topics / $board_config['topics_per_page']);
$total_pages += floor($total_posts / $board_config['posts_per_page']);
$total_pages += $total_users + floor($total_users / 50);
$total_pages = floor($total_pages*1.35);

// get bot table data
$sql = "SELECT bot_id, bot_name, bot_last_visit, bot_visit_counter, bot_visit_counter as bot_pages
FROM " . BOTS_TABLE;

if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Couldn\'t query bots.', '', __LINE__, __FILE__, $sql);
}

// generate table from bot data
while ($row = $db->sql_fetchrow($result))
{

	$row_class = ( ($row_class == $theme['td_class2']) ? $theme['td_class1'] : $theme['td_class2']);
	$bot_last_visits = explode('|', $row['bot_last_visit']);

	if ($bot_last_visits[0] == '')
	{
		$bot_last_visit = $lang['Never'];
	} 
	else 
	{
		$bot_last_visit = "<select>";
		foreach ($bot_last_visits as $visit)
		{
			$bot_last_visit .= "<option>" . date("j M y H:i:s", $visit) . "</option>";
		}
		$bot_last_visit .= "</select>";
	}

	$bot_pages = $row['bot_pages'];

	$percentage = round(($bot_pages / $total_pages)*100);

	$bot_pages .= " (" . (($percentage < 100) ? $percentage : 100)  . "%)";

	$template->assign_block_vars('botrow', array(
		'ROW_NUMBER' => $row['bot_id'],
		'ROW_CLASS' => $row_class,

		'BOT_NAME' => $row['bot_name'],
		'PAGES' => $bot_pages,
		'VISITS' => $row['bot_visit_counter'],
		'bot_last_visit' => $bot_last_visit)
	);

}


// if their are no bots write a friendly, informative message!
if ( $db->sql_numrows($result) == 0 )
{
	$template->assign_block_vars('nobotrow', array(
		'NO_BOTS' => $lang['No_Bots'])
	);
}

// free the result and finish the page!
$db->sql_freeresult($result);

// get bot table data
$sql = "SELECT bot_id, bot_name, bot_agent, bot_ip 
FROM " . BOTS_TABLE;

if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Couldn\'t query bots.', '', __LINE__, __FILE__, $sql);
}

$pending_bots = $db->sql_numrows($result);
$row = $db->sql_fetchrowset($result);

// generate pending table from bot data

// i know its bad practice to have to almost identical statements but what the hey!
$pending_array = explode('|', $row['bot_agent']);
		
for ($loop = 0; $loop < $pending_bots; $loop++)
{
	$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
	$row_class = ( ($row_class == $theme['td_class2']) ? $theme['td_class1'] : $theme['td_class2']);
	
	$template->assign_block_vars('pendingrow', array(
		"ROW_COLOR" 			=> "#" . $row_color,
		"ROW_CLASS" 			=> $row_class,
				
		'ROW_NUMBER' 		=> $row[$loop]['bot_id'],
		'PENDING_NUMBER' 	=> $loop,
		'PENDING_DATA' 		=> "agent",
	
		'BOT_NAME' 	=> $row[$loop]['bot_name'],
		'AGENT' 		=> "<b>" . $row[$loop]['bot_agent'] . "</b>",
		'IP' 				=> "<a href=\"http://network-tools.com/default.asp?host=" . decode_ip($row[$loop]['bot_ip']) . "\" target=\"_phpbbwhois\">" . decode_ip($row[$loop]['bot_ip']) . "</a>")
	);
}

// if their are no pending bots write a friendly, informative message!
if ( !$pending_bots )
{
	$template->assign_block_vars('nopendingrow', array(
		'NO_BOTS' => $lang['No_Pending_Bots'])
	);
}

// free the result and finish the page!
$db->sql_freeresult($result);

$template->assign_vars(array(
	"S_BOTS_ACTION" => append_sid("admin_bots.$phpEx"),

	"L_BOTS_TITLE" => $lang['Manage_Bots'],
	"L_BOTS_EXPLAIN" => $lang['Bot_Explain'],
    "L_SUPPORT" => $lang['L_SUPPORT'],
	"L_BOTS_TITLE_PENDING" => $lang['Pending_Bots'],
	"L_BOTS_EXPLAIN_PENDING" => $lang['Pending_Explain'],

	"L_BOT_IP" => $lang['Bot_Ip'],
	"L_BOT_AGENT" => $lang['Bot_Agent'],
	"L_BOT_NAME" => $lang['Bot_Name'],
	"L_BOT_LAST_VISIT" => $lang['Last_Visit'],
	"L_BOT_VISITS" => $lang['Visits'],
	"L_BOT_PAGES" => $lang['Pages'],
	"L_BOT_OPTIONS" => $lang['Options'],
	"L_BOT_MARK" => $lang['Mark'],
	"L_BOT_IGNORE" => $lang['Ignore'],
	"L_BOT_ADD" => $lang['Add'],

	"L_BOT_SUBMIT" => $lang['Submit'],
	"L_BOT_DELETE" => $lang['Delete'],
	"L_BOT_EDIT" => $lang['Edit'])
);


$template->pparse("body");

include('./page_footer_admin.'.$phpEx);

?>