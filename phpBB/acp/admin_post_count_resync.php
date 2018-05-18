<?php
/***************************************************************************
 *                            admin_post_count_resync.php
 *                              -------------------
 *     begin                : Fri Sep 06 2002
 *     copyright            : (C) 2002 Adam Alkins
 *     email                : phpbb@rasadam.com
 *	   $Id: admin_post_count_resync.php,v 1.1 2010/10/10 15:05:22 orynider Exp $: 
 *    
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
 
@define('IN_PHPBB', 1);
if( !empty($setmodules) )
{
	$filename = basename(__FILE__);
	$module['Users']['Resync_Post_counts'] = append_sid($filename);
	return;
}

//
// Load default header
//
$phpbb_root_path = "../";
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);

//
// Langauge File
//
include($phpbb_root_path.'language/lang_' . $board_config['default_lang'] . '/lang_pcount_resync.'.$phpEx);

// Allowing 5 minutes for script to run so it won't timeout on large boards. Checks are
// in place to make sure nothing can loop the script
@set_time_limit(300);

// get the post count for the user
function get_post_count($user_id, $arguments,$mode)
{
	global $db;
	
	if($arguments == '')
	{
		if($mode != 'simple')
		{
			return 0;
		}
	}
	
	$sql = "SELECT COUNT(*) as numrows 
		FROM " . POSTS_TABLE . "
			WHERE poster_id = $user_id".$arguments;

	$result = $db->sql_query($sql);
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Could not obtain the new post count", "", __LINE__, __FILE__, $sql);
	}

	$post_count = $db->sql_fetchrow($result);
	
	return $post_count['numrows'];
}

// Set Post Count	
function set_post_count($user_id,$post_count)
{
	global $db;

	$sql = "UPDATE " . USERS_TABLE . "
		SET user_posts = $post_count 
			WHERE user_id = $user_id";
		
	$result = $db->sql_query($sql);
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Could not update post count", "", __LINE__, __FILE__, $sql);
	}	
}

unset($mode);
// see if the mode is valid
if(!isset($_GET['mode'])||($_GET['mode']!='simple'&&$_GET['mode']!='advanced'))
{
	$mode = 'simple';
}
else
{
	$mode = $_GET['mode'];
}

// if button wasn't pressed
if(!isset($_POST['submit']))
{
	switch($mode)
	{
		case 'advanced':
			// query will get forums in the order they are on the index
			$sql = "SELECT f.forum_id,f.forum_name, c.cat_order 
				FROM ". FORUMS_TABLE ." as f, ". CATEGORIES_TABLE ." as c
					WHERE f.cat_id = c.cat_id
						ORDER BY c.cat_order ASC, f.forum_order ASC";
			
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Could not get list of forums", "", __LINE__, __FILE__, $sql);
			}
			
			$forum_rows = $db->sql_fetchrowset($result);
			$num_rows = $db->sql_numrows($result);

			$template->set_filenames(array(
			    "body" => "admin/admin_pcount_resync_adv.tpl")
			);
			
			for( $i = 0; $i < $num_rows; $i++ )
			{
				// determine the css class
				if( ($i % 2) == 1 )
				{
					$row_class = '1';
				}
				else
				{
					$row_class = '2';
				}
				
				// clean forum name
				$forum_rows[$i]['forum_name'] = stripslashes($forum_rows[$i]['forum_name']);
				
				
				// assign block values
				$template->assign_block_vars("forums", array(
					"FORUM_ID" => $forum_rows[$i]['forum_id'],
					"FORUM_NAME" => $forum_rows[$i]['forum_name'],
					"ROW_CLASS" => $row_class)
				); 		
			}

			$template->assign_vars(array(
				"L_PAGE_TITLE" => $lang['Resync_page_title'],
				"L_PAGE_DESC" => $lang['Resync_page_desc_adv'],
				"L_BATCH_MODE" => $lang['Resync_batch_mode'],
				"L_BATCH_NUMBER" => $lang['Resync_batch_number'],
				"L_BATCH_AMOUNT" => $lang['Resync_batch_amount'],
				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],
				"L_FORUM" => $lang['Forum'],
				"L_RESYNCQ" => $lang['Resync_question'],
				"L_USER_SELECT" => $lang['Select_a_User'],
				"L_USER_FIND" => $lang['Find_username'],
				"L_CHECK_ALL" => $lang['Resync_check_all'],
				"L_MODE_CHANGE" => $lang['Simple_mode'],
				"L_DO_RESYNC" => $lang['Resync_do'],
				"L_RESET" => $lang['Reset'],
			
				"S_RESYNC_ACTION" => append_sid("admin_post_count_resync.$phpEx?mode=advanced"),
				"S_FIND_USERNAME" => append_sid($phpbb_root_path."search.php?mode=searchuser"),
				
				"U_MODE_CHANGE" => append_sid("admin_post_count_resync.$phpEx?mode=simple"))
			);
			
			break;
		case 'simple':
			$template->set_filenames(array(
			    "body" => "admin/admin_pcount_resync_simple.tpl")
			);

			$template->assign_vars(array(
				"L_PAGE_TITLE" => $lang['Resync_page_title'],
				"L_PAGE_DESC" => $lang['Resync_page_desc_simple'],
				"L_BATCH_MODE" => $lang['Resync_batch_mode'],
				"L_YES" => $lang['Yes'],
				"L_NO" => $lang['No'],
				"L_BATCH_NUMBER" => $lang['Resync_batch_number'],
				"L_BATCH_AMOUNT" => $lang['Resync_batch_amount'],
				"L_MODE_CHANGE" => $lang['Advanced_mode'],
				"L_DO_RESYNC" => $lang['Resync_do'],
				"L_RESET" => $lang['Reset'],
			
				"S_RESYNC_ACTION" => append_sid("admin_post_count_resync.$phpEx?mode=simple"),

				"U_MODE_CHANGE" => append_sid("admin_post_count_resync.$phpEx?mode=advanced"))
			);
	}
}
else
{
	unset($batch);
	// make sure the batch value is valid, set batch to off if not
	if(!isset($_POST['batch'])||($_POST['batch']!=1&&$_POST['batch']!=0))
	{
		$batch = 0;
	}
	else
	{
		$batch = $_POST['batch'];
	}
	
	$where = '';
	if($mode == 'advanced')
	{
		if($_POST['username']==''&&$_POST['resync_all']!=1)
		{
			message_die(GENERAL_MESSAGE, $lang['Resync_invalid']);
		}
		
		// get list of forums
		$sql = "SELECT forum_id
			FROM ". FORUMS_TABLE ."
				ORDER BY forum_id ASC";
		
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Could not get list of forums", "", __LINE__, __FILE__, $sql);
		}
		
		$forum_rows = $db->sql_fetchrowset($result);
		$num_rows = $db->sql_numrows($result);

		$j = 0;
		
		// Cheap fix for bug that caused sql error because of the brackets with 1 forum
		// Alert thanks to admin@thewiz.co.il
		// 0 = not opened (brackets)
		// 1 = opened
		// 2 = closed
		$k = 0;
		
		for( $i = 0; $i < $num_rows; $i++ )
		{
			// check to see which forums need to be included in the resync
			if( $_POST['forum_'.$forum_rows[$i]['forum_id']]==1 )
			{
				if($j == 0)
				{
					$where .= ' AND ( forum_id = '.$forum_rows[$i]['forum_id'];
					$j = 1;
					$k = 1;					
				}
				else if($i == ($num_rows - 1))
				{
					$where .= ' OR forum_id = '.$forum_rows[$i]['forum_id'].' )';
					$k = 2;
				}
				else
				{
					$where .= ' OR forum_id = '.$forum_rows[$i]['forum_id'];
				}
			}
			
			
		}

		// bracket wasn't closed?
		if( $k == 1)
		{
			$where .= ' )';
		}
	}
	
	// if running in batch mode (batch mode works for only more than one user resync)
	if( ($mode=='simple'||$_POST['resync_all']==1)&&$batch==1 )
	{
		unset($batch_number);
		unset($batch_amount);
		unset($batch_point);
		
		$_POST['batch_number'] = intval($_POST['batch_number']);
		
		// batch number must start at 1 or more	
		if( $_POST['batch_number'] >= 1 )
		{
			$batch_number = $_POST['batch_number'];
		}
		else
		{
			$batch_number = 1;
		}

		$_POST['batch_amount'] = intval($_POST['batch_amount']);
		
		// minimum amount of resyncs per batch is 1
		if( $_POST['batch_amount'] >= 1 )
		{
			$batch_amount = $_POST['batch_amount'];
		}
		else
		{
			$batch_amount = 50;
		}
		
		// calculate the point for the first value of the limit
		$batch_point = $batch_number * $batch_amount;
			
		unset($batch_run);
		
		// batch run will be used to keep the loop running
		$batch_run = 1;
		
		
		// NOTE: Not using template engine because values must be displayed dynamically.
		// On large forums, this script could fill up on memory and crash. By doing this
		// people can resume the batch mode to finish.
				
		echo '<p align="center"><h1>'.$lang['Resync_batch_mode'].'</h1></p><br />';
		
		while( $batch_run == 1 )
		{
			echo $lang['Resync_batch_number'].' ['.$batch_number.']';
			
			// get the user ids for this batch
			$sql = "SELECT user_id
				FROM ". USERS_TABLE ."
					ORDER BY user_id ASC
						LIMIT $batch_point,$batch_amount";
			
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Could not get user Information", "", __LINE__, __FILE__, $sql);
			}
			
			$user_rows = $db->sql_fetchrowset($result);
			$num_rows = $db->sql_numrows($result);
			
			for( $i = 0; $i < $num_rows; $i++ )
			{
				// get the post count
				$post_count = get_post_count($user_rows[$i]['user_id'],$where, $mode);
				// set the new value
				set_post_count($user_rows[$i]['user_id'],$post_count);
			}
			
			// if the num rows were less than the amount to get meaning we are done
			if($num_rows != $batch_amount)
			{
				$batch_run = 0;
				
				echo ' - <strong>'.$lang['Resync_finished'].'</strong><br /><br />';
			}
			else
			{
				echo ' - <strong>'.$lang['Resync_finished'].'</strong><br />';
				$batch_number++;
				$batch_point += $batch_amount;
			}
		}
		
		echo '<p align="center"><h2>'.$lang['Resync_completed'].'</h2></p>';
		die;					
	}
	
	// if we need to resync all users not in batch mode
	if( $mode=='simple'||$_POST['resync_all']==1 )
	{	
		// get list of users
		$sql = "SELECT user_id
			FROM ". USERS_TABLE ."
				ORDER BY user_id ASC";

		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Could not get user Information", "", __LINE__, __FILE__, $sql);
		}
		
		$user_rows = $db->sql_fetchrowset($result);
		$num_rows = $db->sql_numrows($result);
		
		for( $i = 0; $i < $num_rows; $i++ )
		{
			// get post count
			$post_count = get_post_count($user_rows[$i]['user_id'],$where,$mode);
			// set it
			set_post_count($user_rows[$i]['user_id'],$post_count);			
		}
	}
	else
	{
		// validate $username
		$username = ( !empty($_POST['username']) ) ? trim(strip_tags( $_POST['username'] ) ) : '';
		
		// query for username
		$sql = "SELECT user_id
			FROM ". USERS_TABLE ."
				WHERE username = '".$username."'";

		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(GENERAL_ERROR, "Could not get user Information", "", __LINE__, __FILE__, $sql);
		}
		
		$user_row = $db->sql_fetchrow($result);
		$num_rows = $db->sql_numrows($result);
		
		// if it doesn't exist, kill the script
		if($num_rows == 0)
		{
			message_die(GENERAL_MESSAGE,$lang['No_such_user'].$lang['Resync_redirect']);
		}
		
		// get the count
		$post_count = get_post_count($user_row['user_id'],$where, $mode);
		// set it
		set_post_count($user_row['user_id'],$post_count);
	}
	
	$redirect_message = sprintf($lang['Resync_redirect'], append_sid("admin_post_count_resync.php?mode=".$_GET['mode']), append_sid("index.php?pane=right"));
	
	// completed message
	message_die(GENERAL_MESSAGE,$lang['Resync_completed'].$redirect_message);
}

// Spit out the page.
$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>