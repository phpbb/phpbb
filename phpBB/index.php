<?php
/***************************************************************************
 *                                index.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: index.php,v 1.1 2010/10/10 15:01:18 orynider Exp $
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

/**
* @ignore
*/
define("IN_INDEX", true);

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
define('PHP_EXT', $phpEx);
include($phpbb_root_path . 'common.' . $phpEx);


//
// Start session management
//
$userdata = $user->session_pagestart($user_ip, PAGE_SEARCH);
$user->set_lang($user->lang, $user->help, 'common');
$lang = &$user->lang;
//$user->_init_userprefs($user->data);
init_userprefs($user->data);
//
// End session management
//


// UPI2DB - BEGIN
$mark_always_read = request_var('always_read', '');
$mark_forum_id = request_var('forum_id', 0);

$viewcat = (!empty($_GET[POST_CAT_URL]) ? intval($_GET[POST_CAT_URL]) : -1);
$viewcat = (($viewcat <= 0) ? -1 : $viewcat);
$viewcatkey = ($viewcat < 0) ? 'Root' : POST_CAT_URL . $viewcat;

$mark_read = request_var('mark', '');

// Handle marking posts
if( $mark_read == 'forums' )
{
	if( $userdata['session_logged_in'] )
	{
		setcookie($board_config['cookie_name'] . '_f_all', time(), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
	}

	$template->assign_vars(array(
		"META" => '<meta http-equiv="refresh" content="3;url='  .append_sid("index.$phpEx") . '">')
	);

	$message = $lang['Forums_marked_read'] . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . append_sid("index.$phpEx") . '">', '</a> ');

	message_die(GENERAL_MESSAGE, $message);
}
// End handle marking posts



$tracking_topics = ( isset($_COOKIE[$board_config['cookie_name'] . '_t']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . "_t"]) : array();
$tracking_forums = ( isset($_COOKIE[$board_config['cookie_name'] . '_f']) ) ? unserialize($_COOKIE[$board_config['cookie_name'] . "_f"]) : array();

//
// If you don't use these stats on your index you may want to consider
// removing them
//
$total_posts = get_db_stat('postcount');
$total_users = get_db_stat('usercount');
$newest_userdata = get_db_stat('newestuser');
$newest_user = $newest_userdata['username'];
$newest_uid = $newest_userdata['user_id'];

if( $total_posts == 0 )
{
	$l_total_post_s = $lang['Posted_articles_zero_total'];
}
else if( $total_posts == 1 )
{
	$l_total_post_s = $lang['Posted_article_total'];
}
else
{
	$l_total_post_s = $lang['Posted_articles_total'];
}

if( $total_users == 0 )
{
	$l_total_user_s = $lang['Registered_users_zero_total'];
}
else if( $total_users == 1 )
{
	$l_total_user_s = $lang['Registered_user_total'];
}
else
{
	$l_total_user_s = $lang['Registered_users_total'];
}


$order_legend = 'group_name';
// Grab group details for legend display
if ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
{
	$sql = 'SELECT g.*, g.group_id as group_colour
		FROM ' . GROUPS_TABLE . ' g
		WHERE g.group_id > 0
		ORDER BY ' . $order_legend . ' ASC';
}
else
{
	$sql = 'SELECT g.*, g.group_id as group_colour
		FROM ' . GROUPS_TABLE . ' g
		LEFT JOIN ' . USER_GROUP_TABLE . ' ug
			ON (
				g.group_id = ug.group_id
				AND ug.user_id = ' . $user->data['user_id'] . '
				AND ug.user_pending = 0
			)
		WHERE g.group_id > 0
			AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $user->data['user_id'] . ')
		ORDER BY g.' . $order_legend . ' ASC';
}
$result = $db->sql_query($sql);

$legend = array();

while ($row = $db->sql_fetchrow($result))
{
	$colour_text = ($row['group_colour']) ? ' style="color:#FFA' . $row['group_colour'] . '4F"' : '';
	$group_name = $row['group_name'];

	if ($row['group_name'] == 'BOTS' || ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile')))
	{
		$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
	}
	else
	{
		$legend[] = '<a' . $colour_text . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
	}
}
$db->sql_freeresult($result);

$legend = implode(', ', $legend);


//
// Start page proper
//
$sql = "SELECT c.cat_id, c.cat_title, c.cat_order
	FROM " . CATEGORIES_TABLE . " c 
	ORDER BY c.cat_order";
if( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not query categories list', '', __LINE__, __FILE__, $sql);
}

$category_rows = array();
while ($row = $db->sql_fetchrow($result))
{
	$category_rows[] = $row;
}
$db->sql_freeresult($result);

// Begin Simple Subforums MOD
$subforums_list = array();
// End Simple Subforums MOD
$birthdays = $birthday_list = array();

if( ( $total_categories = count($category_rows) ) )
{
	//
	// Define appropriate SQL
	//
	switch(SQL_LAYER)
	{
		case 'postgresql':
			$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id 
				FROM " . FORUMS_TABLE . " f, " . POSTS_TABLE . " p, " . USERS_TABLE . " u
				WHERE p.post_id = f.forum_last_post_id 
					AND u.user_id = p.poster_id  
					UNION (
						SELECT f.*, NULL, NULL, NULL, NULL
						FROM " . FORUMS_TABLE . " f
						WHERE NOT EXISTS (
							SELECT p.post_time
							FROM " . POSTS_TABLE . " p
							WHERE p.post_id = f.forum_last_post_id  
						)
					)
					ORDER BY cat_id, forum_order";
		break;

		case 'oracle':
			$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id 
				FROM " . FORUMS_TABLE . " f, " . POSTS_TABLE . " p, " . USERS_TABLE . " u
				WHERE p.post_id = f.forum_last_post_id(+)
					AND u.user_id = p.poster_id(+)
				ORDER BY f.cat_id, f.forum_order";
		break;

		default:
			$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id
				FROM (( " . FORUMS_TABLE . " f
				LEFT JOIN " . POSTS_TABLE . " p ON p.post_id = f.forum_last_post_id )
				LEFT JOIN " . USERS_TABLE . " u ON u.user_id = p.poster_id )
				ORDER BY f.cat_id, f.forum_order";
		break;
	}
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forums information', '', __LINE__, __FILE__, $sql);
	}

	$forum_data = array();
	while( $row = $db->sql_fetchrow($result) )
	{
		$forum_data[] = $row;
	}
	$db->sql_freeresult($result);
	if ( !($total_forums = count($forum_data)) )
	{
		message_die(GENERAL_MESSAGE, $lang['No_forums']);
	}
	
	//
	// Obtain a list of topic ids which contain
	// posts made since user last visited
	//
	if ($userdata['session_logged_in'])
	{
		// 60 days limit
		if ($userdata['user_lastvisit'] < (time() - 5184000))
		{
			$userdata['user_lastvisit'] = time() - 5184000;
		}

		$sql = "SELECT t.forum_id, t.topic_id, p.post_time 
			FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p 
			WHERE p.post_id = t.topic_last_post_id 
				AND p.post_time > " . $userdata['user_lastvisit'] . " 
				AND t.topic_moved_id = 0"; 
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not query new topic information', '', __LINE__, __FILE__, $sql);
		}

		$new_topic_data = array();
		while( $topic_data = $db->sql_fetchrow($result) )
		{
			$new_topic_data[$topic_data['forum_id']][$topic_data['topic_id']] = $topic_data['post_time'];
		}
		$db->sql_freeresult($result);
	}

	//
	// Obtain list of moderators of each forum
	// First users, then groups ... broken into two queries
	//
	$sql = "SELECT aa.forum_id, u.user_id, u.username 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u
		WHERE aa.auth_mod = " . TRUE . " 
			AND g.group_single_user = 1 
			AND ug.group_id = aa.group_id 
			AND g.group_id = aa.group_id 
			AND u.user_id = ug.user_id 
		GROUP BY u.user_id, u.username, aa.forum_id 
		ORDER BY aa.forum_id, u.user_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
	}

	$forum_moderators = array();
	while( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '">' . $row['username'] . '</a>';
	}
	$db->sql_freeresult($result);

	$sql = "SELECT aa.forum_id, g.group_id, g.group_name 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g 
		WHERE aa.auth_mod = " . TRUE . " 
			AND g.group_single_user = 0 
			AND g.group_type <> " . GROUP_HIDDEN . "
			AND ug.group_id = aa.group_id 
			AND g.group_id = aa.group_id 
		GROUP BY g.group_id, g.group_name, aa.forum_id 
		ORDER BY aa.forum_id, g.group_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forum moderator information', '', __LINE__, __FILE__, $sql);
	}	

	while( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = '<a href="' . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=" . $row['group_id']) . '">' . $row['group_name'] . '</a>';
	}
	$db->sql_freeresult($result);

	//
	// Find which forums are visible for this user
	//
	$is_auth_ary = array();
	$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata, $forum_data);
	
	// Generate birthday list if required ...
	$show_birthdays = ($auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'));

	$template->assign_block_vars_array('birthdays', $birthdays);	
			
	//
	// Start output of page
	//
	define('SHOW_ONLINE', true);
	$page_title = $lang['Index'];
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	$template->set_filenames(array(
		'body' => 'index_body.tpl')
	);
	
	$template->assign_vars(array(
		'L_STATISTICS' 		=> $user->lang['Statistics'],
		
		'L_LEGEND' 			=> $user->lang['Legend'],			
		'LEGEND'			=> $legend,	
		
		'TOTAL_POSTS' 		=> sprintf($l_total_post_s, $total_posts),
		'TOTAL_USERS' 		=> sprintf($l_total_user_s, $total_users),
		'NEWEST_USER' 		=> sprintf($lang['Newest_user'], '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$newest_uid") . '">', $newest_user, '</a>'), 

		'FORUM_IMG' 		=> $images['forum'],
		'FORUM_NEW_IMG' 	=> $images['forum_new'],
		'FORUM_LOCKED_IMG' 	=> $images['forum_locked'],
		
		
		'BIRTHDAY_LIST'	=> (empty($birthday_list)) ? '' : implode($user->lang['COMMA_SEPARATOR'], $birthday_list),		
		'S_DISPLAY_BIRTHDAY_LIST'	=> $show_birthdays,		
		
		'S_IS_LINK'		=> false,		
		
		'L_FORUM' 			=> $user->lang['Forum'],
		// Begin Simple Subforums MOD
		'L_SUBFORUMS' 		=> $user->lang['Subforums'],
		// End Simple Subforums MOD		
		'L_TOPICS' 			=> $user->lang['Topics'],
		'L_REPLIES' 		=> $user->lang['Replies'],
		'L_VIEWS' 			=> $user->lang['Views'],
		'L_POSTS' 			=> $user->lang['Posts'],
		'L_LASTPOST' 		=> $user->lang['Last_Post'], 
		'L_NO_NEW_POSTS' 	=> $user->lang['No_new_posts'],
		'L_NEW_POSTS' 		=> $user->lang['New_posts'],
		'L_NO_NEW_POSTS_LOCKED' => $user->lang['No_new_posts_locked'], 
		'L_NEW_POSTS_LOCKED' => $user->lang['New_posts_locked'], 
		'L_ONLINE_EXPLAIN' 	=> $user->lang['Online_explain'], 

		'L_MODERATOR' 		=> $user->lang['Moderators'], 
		'L_FORUM_LOCKED' 	=> $user->lang['Forum_is_locked'],
		'L_MARK_FORUMS_READ' => $user->lang['Mark_all_forums'],
		
		'U_TEAM'			=> ($user->data['user_id'] != ANONYMOUS) ? '' : append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=team'),
		'U_TERMS_USE'		=> append_sid("{$phpbb_root_path}profile.$phpEx?mode=terms"),		
		'U_CANONICAL' 		=> generate_board_url() . '/' . append_sid("index.$phpEx"),			
		'U_MARK_FORUMS'		=> ($userdata['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}index.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;mark=forums&amp;mark_time=' . time()) : '', 
		'U_MARK_READ' 		=> append_sid("index.$phpEx?mark=forums"))
	);

	//
	// Let's decide which categories we should display
	//
	$display_categories = array();

	for ($i = 0; $i < $total_forums; $i++ )
	{
		if ($is_auth_ary[$forum_data[$i]['forum_id']]['auth_view'])
		{
			$display_categories[$forum_data[$i]['cat_id']] = true;
		}
	}

	//
	// Okay, let's build the index
	//
	for($i = 0; $i < $total_categories; $i++)
	{
		$cat_id = $category_rows[$i]['cat_id'];

		//
		// Yes, we should, so first dump out the category
		// title, then, if appropriate the forum list
		//
		if (isset($display_categories[$cat_id]) && $display_categories[$cat_id])
		{
			$template->assign_block_vars('catrow', array(
				'CAT_ID' => $cat_id,
				'CAT_DESC' => $category_rows[$i]['cat_title'],
				'U_VIEWCAT' => append_sid("index.$phpEx?" . POST_CAT_URL . "=$cat_id"))
			);

			if ( $viewcat == $cat_id || $viewcat == -1 )
			{
				for($j = 0; $j < $total_forums; $j++)
				{
					if ( $forum_data[$j]['cat_id'] == $cat_id )
					{
						$forum_id = $forum_data[$j]['forum_id'];

						if ( $is_auth_ary[$forum_id]['auth_view'] )
						{
							if ( $forum_data[$j]['forum_status'] == FORUM_LOCKED )
							{
								$folder_image = $images['forum_locked']; 
								$folder_alt = $lang['Forum_locked'];
								// Begin Simple Subforums MOD
								$unread_topics = false;
								$folder_images = array(
									'default'	=> $folder_image,
									'new'		=> $images['forum_locked'],
									'sub'		=> ( isset($images['forums_locked']) ) ? $images['forums_locked'] : $images['forum_locked'],
									'subnew'	=> ( isset($images['forums_locked']) ) ? $images['forums_locked'] : $images['forum_locked'],
									'subalt'	=> $lang['Forum_locked'],
									'subaltnew'	=> $lang['Forum_locked'],
								);
								// End Simple Subforums MOD								
							}
							else
							{
								$unread_topics = false;
								if ( $userdata['session_logged_in'] )
								{
									if ( !empty($new_topic_data[$forum_id]) )
									{
										$forum_last_post_time = 0;

										while( list($check_topic_id, $check_post_time) = @each($new_topic_data[$forum_id]) )
										{
											if ( empty($tracking_topics[$check_topic_id]) )
											{
												$unread_topics = true;
												$forum_last_post_time = max($check_post_time, $forum_last_post_time);

											}
											else
											{
												if ( $tracking_topics[$check_topic_id] < $check_post_time )
												{
													$unread_topics = true;
													$forum_last_post_time = max($check_post_time, $forum_last_post_time);
												}
											}
										}

										if ( !empty($tracking_forums[$forum_id]) )
										{
											if ( $tracking_forums[$forum_id] > $forum_last_post_time )
											{
												$unread_topics = false;
											}
										}

										if ( isset($_COOKIE[$board_config['cookie_name'] . '_f_all']) )
										{
											if ( $_COOKIE[$board_config['cookie_name'] . '_f_all'] > $forum_last_post_time )
											{
												$unread_topics = false;
											}
										}

									}
								}

								$folder_image = ( $unread_topics ) ? $images['forum_new'] : $images['forum']; 
								$folder_alt = ( $unread_topics ) ? $lang['New_posts'] : $lang['No_new_posts'];
								
								// Begin Simple Subforums MOD
								$folder_images = array(
									'default'	=> $folder_image,
									'new'		=> $images['forum_new'],
									'sub'		=> ( isset($images['forums']) ) ? $images['forums'] : $images['forum'],
									'subnew'	=> ( isset($images['forums_new']) ) ? $images['forums_new'] : $images['forum_new'],
									'subalt'	=> $lang['No_new_posts'],
									'subaltnew'	=> $lang['New_posts'],
								);
								// End Simple Subforums MOD								
							}

							$posts = $forum_data[$j]['forum_posts'];
							$topics = $forum_data[$j]['forum_topics'];

							if ( $forum_data[$j]['forum_last_post_id'] )
							{
								$last_post_time = create_date($board_config['default_dateformat'], $forum_data[$j]['post_time'], $board_config['board_timezone']);

								$last_post = $lang['Posted_on_date'] . '&nbsp;' . $last_post_time . '<br />';

								$last_post .= ( $forum_data[$j]['user_id'] == ANONYMOUS ) ? ( !empty($forum_data[$j]['post_username']) ? $forum_data[$j]['post_username'] . ' ' : $lang['Guest'] . ' ' ) : $lang['Post_by_author'] . '&nbsp;<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '='  . $forum_data[$j]['user_id']) . '">' . $forum_data[$j]['username'] . '</a> ';
								
								$last_post .= '<a href="' . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . '=' . $forum_data[$j]['forum_last_post_id']) . '#' . $forum_data[$j]['forum_last_post_id'] . '"><img src="' . $images['icon_latest_reply'] . '" alt="' . $lang['View_latest_post'] . '" title="' . $lang['View_latest_post'] . '" /></a>';

								// Begin Simple Subforums MOD
								$last_post_sub = '<a href="' . append_sid("viewtopic.$phpEx?"  . POST_POST_URL . '=' . $forum_data[$j]['forum_last_post_id']) . '#' . $forum_data[$j]['forum_last_post_id'] . '"><img src="' . ($unread_topics ? $images['icon_newest_reply'] : $images['icon_latest_reply']) . '" border="0" alt="' . $lang['View_latest_post'] . '" title="' . $lang['View_latest_post'] . '" /></a>';
								$last_post_time = $forum_data[$j]['post_time'];
								// End Simple Subforums MOD								
							}
							else
							{
								$last_post = $lang['No_Posts'];
								// Begin Simple Subforums MOD
								$last_post_sub = '<img src="' . $images['icon_minipost'] . '" border="0" alt="' . $lang['No_Posts'] . '" title="' . $lang['No_Posts'] . '" />';
								$last_post_time = 0;
								// End Simple Subforums MOD								
							}

							if (isset($forum_moderators[$forum_id]) && (count($forum_moderators[$forum_id]) > 0))
							{
								$l_moderators = ( count($forum_moderators[$forum_id]) == 1 ) ? $lang['Moderator'] : $lang['Moderators'];
								$moderator_list = implode(', ', $forum_moderators[$forum_id]);
							}
							else
							{
								$l_moderators = '&nbsp;';
								$moderator_list = '';
							}

							$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
							$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
							
							$template->assign_block_vars('catrow.forumrow',	array(
								'S_IS_CAT'	=> (count($category_rows) > 0),
								'S_NO_CAT'	=> (count($category_rows) == 0),								
								'FORUM_ID'	=> $forum_data[$j]['forum_id'],							
								'ROW_COLOR' => '#' . $row_color,
								'ROW_CLASS' => $row_class,
								
								'FORUM_FOLDER_IMG' 			=> $folder_image,
								'FORUM_FOLDER_IMG_SRC' 		=> $user->img($folder_image, '', '27', '', 'src'),			
								'FORUM_FOLDER_IMG_FULL_TAG' => $user->img($folder_image, '', '27', '', 'full_tag'),	
								'FORUM_FOLDER_IMG_HTML' 	=> $user->img($folder_image, '', '27', '', 'html'),				
											
								'FORUM_NAME' => $forum_data[$j]['forum_name'],
								'FORUM_DESC' => $forum_data[$j]['forum_desc'],
								'POSTS' => $forum_data[$j]['forum_posts'],
								'TOPICS' => $forum_data[$j]['forum_topics'],
								'LAST_POST' => $last_post,
								'MODERATORS' => $moderator_list,

								'L_MODERATOR' => $l_moderators, 
								'L_FORUM_FOLDER_ALT' => $folder_alt, 
								// Begin Simple Subforums MOD
								'FORUM_FOLDERS' => serialize($folder_images),
								'S_HAS_SUBFORUM' => ( (intval($forum_data[$j]['forum_parent'])) ? true : false ),
								'PARENT' => $forum_data[$j]['forum_parent'],
								'ID' => $forum_data[$j]['forum_id'],
								'UNREAD' => intval($unread_topics),
								'TOTAL_UNREAD' => intval($unread_topics),
								'TOTAL_POSTS' => $forum_data[$j]['forum_posts'],
								'TOTAL_TOPICS' => $forum_data[$j]['forum_topics'],
								'LAST_POST_FORUM' => $last_post,
								'LAST_POST_TIME' => $last_post_time,
								'LAST_POST_TIME_FORUM' => $last_post_time,
								// End Simple Subforums MOD								

								'U_VIEWFORUM' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))		
							);
							
							//Begin  assign template vars for simple phpBB3 templates 
							// Empty category
							if ($forum_data[$j]['forum_parent'] == $forum_data[$j]['forum_id'] && $forum_data[$j]['forum_type'] == FORUM_CAT)
							{
								$template->assign_block_vars('forumrow', array(
									'S_IS_CAT'	=> (count($category_rows) > 0),
									'S_NO_CAT'	=> (count($category_rows) == 0),								
									'FORUM_ID'	=> $forum_data[$j]['forum_id'],							
									'ROW_COLOR' => '#' . $row_color,
									'ROW_CLASS' => $row_class,
									
									'FORUM_ID'				=> $forum_data[$j]['forum_id'],
									'FORUM_NAME'			=> $forum_data[$j]['forum_name'],
									'FORUM_DESC'			=> generate_text_for_display($forum_data[$j]['forum_desc'], $forum_data[$j]['forum_desc_uid'], $forum_data[$j]['forum_desc_bitfield'], $forum_data[$j]['forum_desc_options']),
									
									'FORUM_FOLDER_IMG' 			=> $folder_image,
									'FORUM_FOLDER_IMG_SRC' 		=> $user->img($folder_image, '', '27', '', 'src'),			
									'FORUM_FOLDER_IMG_FULL_TAG' => $user->img($folder_image, '', '27', '', 'full_tag'),	
									'FORUM_FOLDER_IMG_HTML' 	=> $user->img($folder_image, '', '27', '', 'html'),				
							
									'FORUM_IMAGE'			=> ($forum_data[$j]['forum_image']) ? '<img src="' . $phpbb_root_path . $forum_data[$j]['forum_image'] . '" alt="' . $user->lang['FORUM_CAT'] . '" />' : '',
									'FORUM_IMAGE_SRC'		=> ($forum_data[$j]['forum_image']) ? $phpbb_root_path . $forum_data[$j]['forum_image'] : '',
									'U_VIEWFORUM'			=> append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $forum_data[$j]['forum_id']))
								);
								continue;
							}							
							
							$template->assign_block_vars('forumrow',	array(
								'S_IS_CAT'	=> (count($category_rows) > 0),
								'S_NO_CAT'	=> (count($category_rows) == 0),								
							
								'ROW_COLOR' => '#' . $row_color,
								'ROW_CLASS' => $row_class,
								
								'FORUM_ID'	=> $forum_data[$j]['forum_id'],							
								'S_ROW_COUNT'	=> $j,
								
								'FORUM_FOLDER_IMG' 			=> $folder_image,
								'FORUM_FOLDER_IMG_SRC' 		=> $user->img($folder_image, '', '27', '', 'src'),			
								'FORUM_FOLDER_IMG_FULL_TAG' => $user->img($folder_image, '', '27', '', 'full_tag'),	
								'FORUM_FOLDER_IMG_HTML' 	=> $user->img($folder_image, '', '27', '', 'html'),				
											
								'FORUM_NAME' => $forum_data[$j]['forum_name'],
								'FORUM_DESC' => $forum_data[$j]['forum_desc'],
								'POSTS' => $forum_data[$j]['forum_posts'],
								'TOPICS' => $forum_data[$j]['forum_topics'],
								'LAST_POST' => $last_post,
								'MODERATORS' => $moderator_list,

								'L_MODERATOR' => $l_moderators, 
								'L_FORUM_FOLDER_ALT' => $folder_alt, 
								// Begin Simple Subforums MOD
								'FORUM_FOLDERS' => serialize($folder_images),
								'S_HAS_SUBFORUM' => ( (intval($forum_data[$j]['forum_parent'])) ? true : false ),
								'PARENT' => $forum_data[$j]['forum_parent'],
								'ID' => $forum_data[$j]['forum_id'],
								'DEFINE' => $forum_data[$j]['forum_id'],
								'UNREAD' => intval($unread_topics),
								'TOTAL_UNREAD' => intval($unread_topics),
								'TOTAL_POSTS' => $forum_data[$j]['forum_posts'],
								'TOTAL_TOPICS' => $forum_data[$j]['forum_topics'],
								'LAST_POST_FORUM' => $last_post,
								'LAST_POST_TIME' => $last_post_time,
								'LAST_POST_TIME_FORUM' => $last_post_time,
								// End Simple Subforums MOD								

								'U_VIEWFORUM' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=".$forum_id))		
							);							
							//End  assign template vars for simple phpBB3 templates
							
							// Begin Simple Subforums MOD
							if( $forum_data[$j]['forum_parent'] )
							{
								$subforums_list[] = array(
									'forum_data'	=> $forum_data[$j],
									'folder_image'	=> $folder_image,
									'last_post'		=> $last_post,
									'last_post_sub'	=> $last_post_sub,
									'moderator_list'	=> $moderator_list,
									'unread_topics'	=> $unread_topics,
									'l_moderators'	=> $l_moderators,
									'folder_alt'	=> $folder_alt,
									'last_post_time' => $last_post_time,
									'desc'			=> $forum_data[$j]['forum_desc'],
									);
							}
							// End Simple Subforums MOD							
						}
					}
				}
			}
		}
	} // for ... categories

}// if ... total_categories
else
{
	message_die(GENERAL_MESSAGE, $lang['No_forums']);
}

// Begin Simple Subforums MOD
unset($data);
unset($item);
unset($cat_item);
unset($row_item);

for( $i = 0; $i < count($subforums_list); $i++ )
{
	$forum_data = $subforums_list[$i]['forum_data'];
	$parent_id = $forum_data['forum_parent'];
	
	// Find parent item
	if( isset($template->_tpldata['catrow.']) )
	{
		$data = &$template->_tpldata['catrow.'];
		$count = count($data);
		for( $j = 0; $j < $count; $j++)
		{
			$cat_item = &$data[$j];
			$row_item = &$cat_item['forumrow.'];
			$count2 = count($row_item);
			for( $k = 0; $k < $count2; $k++)
			{
				if( $row_item[$k]['ID'] == $parent_id )
				{
					$item = &$row_item[$k];
					break;
				}
			}
			if( isset($item) )
			{
				break;
			}
		}
	}
	
	if( isset($item) )
	{
		if( isset($item['sub.']) )
		{
			$num = count($item['sub.']);
			$data = &$item['sub.'];
		}
		else
		{
			$num = 0;
			$item[] = 'sub.';
			$data = &$item['sub.'];
		}
		
		// Append new entry
		$data[] = array(
			'NUM' => $num,
			
			'FORUM_FOLDER_IMG' 	=> $subforums_list[$i]['folder_image'], 

			'FORUM_NAME' 		=> $forum_data['forum_name'],
			'FORUM_DESC' 		=> $forum_data['forum_desc'],
			'FORUM_DESC_HTML' 	=> htmlspecialchars(preg_replace('@<[\/\!]*?[^<>]*?>@si', '', $forum_data['forum_desc'])),
			
			'POSTS' 		=> $forum_data['forum_posts'],
			'TOPICS' 		=> $forum_data['forum_topics'],
			
			'LAST_POST' 	=> $subforums_list[$i]['last_post'],
			'LAST_POST_SUB' => $subforums_list[$i]['last_post_sub'],
			'LAST_TOPIC' 	=> $topic_data['topic_title'], //topic_title
			
			'MODERATORS' 	=> $subforums_list[$i]['moderator_list'],
			'PARENT' 		=> $forum_data['forum_parent'],
			'ID' 			=> $forum_data['forum_id'],
			'UNREAD' 		=> intval($subforums_list[$i]['unread_topics']),
	
			'L_MODERATOR' => $subforums_list[$i]['l_moderators'], 
			'L_FORUM_FOLDER_ALT' => $subforums_list[$i]['folder_alt'], 
	
			'U_VIEWFORUM' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . '=' . $forum_data['forum_id'])
		);
		
		$item['HAS_SUBFORUMS'] ++;
		$item['DEFINE'] = &$item['HAS_SUBFORUMS'];
		$item['TOTAL_UNREAD'] += intval($subforums_list[$i]['unread_topics']);
		// Change folder image
		$images = unserialize($item['FORUM_FOLDERS']);
		$item['FORUM_FOLDER_IMG'] = $item['TOTAL_UNREAD'] ? $images['subnew'] : $images['sub'];
		$item['L_FORUM_FOLDER_ALT'] = $item['TOTAL_UNREAD'] ? $images['subaltnew'] : $images['subalt'];
		// Check last post
		if( $item['LAST_POST_TIME'] < $subforums_list[$i]['last_post_time'] )
		{
			$item['LAST_POST'] = $subforums_list[$i]['last_post'];
			$item['LAST_POST_TIME'] = $subforums_list[$i]['last_post_time'];
		}
		if( !$item['LAST_POST_TIME_FORUM'] )
		{
			$item['LAST_POST_FORUM'] = $item['LAST_POST'];
		}
		// Add topics/posts
		$item['TOTAL_POSTS'] += $forum_data['forum_posts'];
		$item['TOTAL_TOPICS'] += $forum_data['forum_topics'];
	}
	unset($item);
	unset($data);
	unset($cat_item);
	unset($row_item);
}
// End Simple Subforums MOD

//
// Generate the page
//
$template->pparse('body');

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>