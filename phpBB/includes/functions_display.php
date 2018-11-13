<?php
/**
*
* @package phpBB2
* @version $Id: functions_display.php,v 1.11 2013/06/28 15:37:22 orynider Exp $
* @copyright (c) 2002-2008 MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
* @link http://mxpcms.sourceforge.net/
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

//
// Fixes for phpBB2
// all funtions mx_ prefixed
// $config -> $board_config
// $cache -> $cache
// $user -> $user
// $auth -> $auth
// append_sid -> append_sid
// get_username_string -> get_username_string
//

/**
* Display Forums
*/
function display_forums($root_data = '', $display_moderators = true, $return_moderators = false)
{
	global $db, $auth, $user, $template;
	global $phpbb_root_path, $phpEx, $board_config;
	global $request;

	// UPI2DB - BEGIN
	$mark_always_read = request_var('always_read', '');
	$mark_forum_id = request_var('forum_id', 0);

	$viewcat = (!empty($_GET[POST_CAT_URL]) ? intval($_GET[POST_CAT_URL]) : -1);
	$viewcat = (($viewcat <= 0) ? -1 : $viewcat);
	$viewcatkey = ($viewcat < 0) ? 'Root' : POST_CAT_URL . $viewcat;

	$mark_read = request_var('mark', '');

	//
	// Handle marking posts
	//
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
	//
	// End handle marking posts
	//

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
			
			'L_FORUM'			=> $user->lang['Forum'],
			// Begin Simple Subforums MOD
			'L_SUBFORUMS'	=> $user->lang['Subforums'],
			// End Simple Subforums MOD		
			'L_TOPICS'			=> $user->lang['Topics'],
			'L_REPLIES'		=> $user->lang['Replies'],
			'L_VIEWS'			=> $user->lang['Views'],
			'L_POSTS'			=> $user->lang['Posts'],
			'L_LASTPOST'		=> $user->lang['Last_Post'], 
			'L_NO_NEW_POSTS'			=> $user->lang['No_new_posts'],
			'L_NEW_POSTS'				=> $user->lang['New_posts'],
			'L_NO_NEW_POSTS_LOCKED'	=> $user->lang['No_new_posts_locked'], 
			'L_NEW_POSTS_LOCKED'			=> $user->lang['New_posts_locked'], 
			'L_ONLINE_EXPLAIN'				=> $user->lang['Online_explain'], 

			'L_MODERATOR'					=> $user->lang['Moderators'], 
			'L_FORUM_LOCKED'				=> $user->lang['Forum_is_locked'],
			'L_MARK_FORUMS_READ'		=> $user->lang['Mark_all_forums'],
			
			'U_TEAM'				=> ($user->data['user_id'] != ANONYMOUS) ? '' : append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=team'),
			'U_TERMS_USE'		=> append_sid("{$phpbb_root_path}profile.$phpEx?mode=terms"),		
			'U_CANONICAL'		=> generate_board_url() . '/' . append_sid("index.$phpEx"),			
			'U_MARK_FORUMS'	=> ($user->data['user_id'] != ANONYMOUS) ? append_sid("{$phpbb_root_path}index.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;mark=forums&amp;mark_time=' . time()) : '', 
			'U_MARK_READ'		=> append_sid("index.$phpEx?mark=forums"))
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
									'S_IS_CAT'	=> ($forum_data[$j]['cat_id'] = 0),
									'S_NO_CAT'	=> (count($category_rows) == 0),								
									'FORUM_ID'	=> $forum_data[$j]['forum_id'],
									'ROW_COLOR' => '#' . $row_color,
									'ROW_CLASS' => $row_class,
									
									'FORUM_FOLDER_IMG' => $folder_image,
	 								'FORUM_FOLDER_IMG_SRC' 		=> $user->img($folder_image, '', '27', '', 'src'),			
									'FORUM_FOLDER_IMG_FULL_TAG' 	=> $user->img($folder_image, '', '27', '', 'full_tag'),	
									'FORUM_FOLDER_IMG_HTML' 		=> $user->img($folder_image, '', '27', '', 'html'),				

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
								
								// Begin Simple Subforums MOD
								// Empty category
								if ($forum_data[$j]['forum_parent'] == $forum_data[$j]['forum_id'] && $forum_data[$j]['forum_type'] == FORUM_CAT)
								{
									$template->assign_block_vars('forumrow', array(
										'S_IS_CAT'	=> ($forum_data[$j]['cat_id'] = 0),
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
									'S_IS_CAT'	=> ($forum_data[$j]['cat_id'] = 0),
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
	
	$forum_rows = $subforums = $forum_ids = $forum_ids_moderator = $forum_moderators = $active_forum_ary = array();
	$parent_id = $visible_forums = 0;
	$sql_from = '';

	// Mark forums read?
	$mark_read = request_var('mark', '');

	if ($mark_read == 'all')
	{
		$mark_read = '';
	}

	if (!$root_data)
	{
		if ($mark_read == 'forums')
		{
			$mark_read = 'all';
		}

		$root_data = array('forum_id' => 0);
		$sql_where = '';
	}
	else
	{
		$sql_where = 'left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'];
	}

	// Display list of active topics for this category?
	$show_active = (isset($root_data['forum_flags']) && ($root_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS)) ? true : false;

	$sql_array = array(
		'SELECT'	=> 'f.*',
		'FROM'		=> array(
			FORUMS_TABLE		=> 'f'
		),
		'LEFT_JOIN'	=> array(),
	);

	if ($board_config['load_db_lastread'] && $user->data['is_registered'])
	{
		$sql_array['LEFT_JOIN'][] = array('FROM' => array(FORUMS_TRACK_TABLE => 'ft'), 'ON' => 'ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id');
		$sql_array['SELECT'] .= ', ft.mark_time';
	}
	else if ($board_config['load_anon_lastread'] || $user->data['is_registered'])
	{
		$tracking_topics = (isset($_COOKIE[$board_config['cookie_name'] . '_track'])) ? ((STRIP) ? stripslashes($_COOKIE[$board_config['cookie_name'] . '_track']) : $_COOKIE[$board_config['cookie_name'] . '_track']) : '';
		$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

		if (!$user->data['is_registered'])
		{
			$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $board_config['board_startdate']) : 0;
		}
	}

	if ($show_active)
	{
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(FORUMS_ACCESS_TABLE => 'fa'),
			'ON'	=> "fa.forum_id = f.forum_id AND fa.session_id = '" . $db->sql_escape($user->session_id) . "'"
		);

		$sql_array['SELECT'] .= ', fa.user_id';
	}

	$sql = $db->sql_build_query('SELECT', array(
		'SELECT'	=> $sql_array['SELECT'],
		'FROM'		=> $sql_array['FROM'],
		'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],

		'WHERE'		=> $sql_where,

		'ORDER_BY'	=> 'f.cat_id, f.forum_order',
	));

	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forums information', '', __LINE__, __FILE__, $sql);
	}

	$forum_tracking_info = array();
	$branch_root_id = $root_data['forum_id'];
	
	// Find which forums are visible for this user
	$is_auth_ary = array();
	$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $user->data, $root_data);
	
	while ($row = $db->sql_fetchrow($result))
	{
		$forum_id = $row['forum_id'];
		$row['forum_type'] = FORUM_CAT;
		
		// Mark forums read?
		if ($mark_read == 'forums' || $mark_read == 'all')
		{
			if ($is_auth_ary[$forum_id]['auth_view'])
			{
				$forum_ids[] = $forum_id;
				continue;
			}
		}

		// Category with no members
		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			continue;
		}

		// Skip branch
		if (isset($right_id))
		{
			if ($row['left_id'] < $right_id)
			{
				continue;
			}
			unset($right_id);
		}

		if (!$is_auth_ary[$forum_id]['auth_view'])
		{
			// if the user does not have permissions to list this forum, skip everything until next branch
			$right_id = $row['right_id'];
			continue;
		}

		$forum_ids[] = $forum_id;

		if ($board_config['load_db_lastread'] && $user->data['is_registered'])
		{
			$forum_tracking_info[$forum_id] = (!empty($row['mark_time'])) ? $row['mark_time'] : $user->data['user_lastmark'];
		}
		else if ($board_config['load_anon_lastread'] || $user->data['is_registered'])
		{
			if (!$user->data['is_registered'])
			{
				$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $board_config['board_startdate']) : 0;
			}
			$forum_tracking_info[$forum_id] = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $board_config['board_startdate']) : $user->data['user_lastmark'];
		}

		$row['forum_topics'] = ($is_auth_ary[$forum_id]['auth_mod']) ? $row['forum_topics_real'] : $row['forum_topics'];

		// Display active topics from this forum?
		if ($show_active && $row['forum_type'] == FORUM_POST && $auth->acl_get('f_read', $forum_id) && ($row['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS))
		{
			if (!isset($active_forum_ary['forum_topics']))
			{
				$active_forum_ary['forum_topics'] = 0;
			}

			if (!isset($active_forum_ary['forum_posts']))
			{
				$active_forum_ary['forum_posts'] = 0;
			}

			$active_forum_ary['forum_id'][]		= $forum_id;
			$active_forum_ary['enable_icons'][]	= $row['enable_icons'];
			$active_forum_ary['forum_topics']	+= $row['forum_topics'];
			$active_forum_ary['forum_posts']	+= $row['forum_posts'];

			// If this is a passworded forum we do not show active topics from it if the user is not authorised to view it...
			if ($row['forum_password'] && $row['user_id'] != $user->data['user_id'])
			{
				$active_forum_ary['exclude_forum_id'][] = $forum_id;
			}
		}

		//
		if ($row['parent_id'] == $root_data['forum_id'] || $row['parent_id'] == $branch_root_id)
		{
			if ($row['forum_type'] != FORUM_CAT)
			{
				$forum_ids_moderator[] = (int) $forum_id;
			}

			// Direct child of current branch
			$parent_id = $forum_id;
			$forum_rows[$forum_id] = $row;

			if ($row['forum_type'] == FORUM_CAT && $row['parent_id'] == $root_data['forum_id'])
			{
				$branch_root_id = $forum_id;
			}
			$forum_rows[$parent_id]['forum_id_last_post'] = $row['forum_id'];
			$forum_rows[$parent_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
		}
		else if ($row['forum_type'] != FORUM_CAT)
		{
			$subforums[$parent_id][$forum_id]['display'] = ($row['display_on_index']) ? true : false;
			$subforums[$parent_id][$forum_id]['name'] = $row['forum_name'];
			$subforums[$parent_id][$forum_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];

			$forum_rows[$parent_id]['forum_topics'] += $row['forum_topics'];

			// Do not list redirects in LINK Forums as Posts.
			if ($row['forum_type'] != FORUM_LINK)
			{
				$forum_rows[$parent_id]['forum_posts'] += $row['forum_posts'];
			}

			if ($row['forum_last_post_time'] > $forum_rows[$parent_id]['forum_last_post_time'])
			{
				$forum_rows[$parent_id]['forum_last_post_id'] = $row['forum_last_post_id'];
				$forum_rows[$parent_id]['forum_last_post_subject'] = $row['forum_last_post_subject'];
				$forum_rows[$parent_id]['forum_last_post_time'] = $row['forum_last_post_time'];
				$forum_rows[$parent_id]['forum_last_poster_id'] = $row['forum_last_poster_id'];
				$forum_rows[$parent_id]['forum_last_poster_name'] = $row['forum_last_poster_name'];
				$forum_rows[$parent_id]['forum_last_poster_colour'] = $row['forum_last_poster_colour'];
				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
			}
		}
	}
	$db->sql_freeresult($result);

	// Handle marking posts
	if ($mark_read == 'forums' || $mark_read == 'all')
	{
		$redirect = build_url('mark');

		if ($mark_read == 'all')
		{
			markread('all');

			$message = sprintf($user->lang['RETURN_INDEX'], '<a href="' . $redirect . '">', '</a>');
		}
		else
		{
			markread('topics', $forum_ids);

			$message = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect . '">', '</a>');
		}

		meta_refresh(3, $redirect);
		trigger_error($user->lang['FORUMS_MARKED'] . '<br /><br />' . $message);
	}

	// Grab moderators ... if necessary
	if ($display_moderators)
	{
		if ($return_moderators)
		{
			$forum_ids_moderator[] = $root_data['forum_id'];
		}
		get_moderators($forum_moderators, $forum_ids_moderator);
	}

	// Used to tell whatever we have to create a dummy category or not.
	$last_catless = true;
	foreach ($forum_rows as $row)
	{
		// Empty category
		if ($row['parent_id'] == $root_data['forum_id'] && $row['forum_type'] == FORUM_CAT)
		{
			$template->assign_block_vars('forumrow', array(
				'S_IS_CAT'				=> true,
				'FORUM_ID'				=> $row['forum_id'],
				'FORUM_NAME'			=> $row['forum_name'],
				'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
				'FORUM_FOLDER_IMG'		=> 'FORUM_FOLDER_IMG',
				'FORUM_FOLDER_IMG_SRC'	=> 'FORUM_FOLDER_IMG_SRC',
				'FORUM_IMAGE'			=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="' . $user->lang['FORUM_CAT'] . '" />' : '',
				'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? $phpbb_root_path . $row['forum_image'] : '',
				'U_VIEWFORUM'			=> append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $row['forum_id']))
			);

			continue;
		}

		$visible_forums++;
		$forum_id = $row['forum_id'];

		$forum_unread = (isset($forum_tracking_info[$forum_id]) && $row['orig_forum_last_post_time'] > $forum_tracking_info[$forum_id]) ? true : false;

		$folder_image = $folder_alt = $l_subforums = '';
		$subforums_list = array();

		// Generate list of subforums if we need to
		if (isset($subforums[$forum_id]))
		{
			foreach ($subforums[$forum_id] as $subforum_id => $subforum_row)
			{
				$subforum_unread = (isset($forum_tracking_info[$subforum_id]) && $subforum_row['orig_forum_last_post_time'] > $forum_tracking_info[$subforum_id]) ? true : false;

				if ($subforum_row['display'] && $subforum_row['name'])
				{
					$subforums_list[] = array(
						'link'		=> append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $subforum_id),
						'name'		=> $subforum_row['name'],
						'unread'	=> $subforum_unread,
					);
				}
				else
				{
					unset($subforums[$forum_id][$subforum_id]);
				}

				// If one subforum is unread the forum gets unread too...
				if ($subforum_unread)
				{
					$forum_unread = true;
				}
			}

			$l_subforums = (sizeof($subforums[$forum_id]) == 1) ? $user->lang['SUBFORUM'] . ': ' : $user->lang['SUBFORUMS'] . ': ';
			$folder_image = ($forum_unread) ? 'forum_unread_subforum' : 'forum_read_subforum';
		}
		else
		{
			switch ($row['forum_type'])
			{
				case FORUM_POST:
					$folder_image = ($forum_unread) ? 'forum_unread' : 'forum_read';
				break;

				case FORUM_LINK:
					$folder_image = 'forum_link';
				break;
			}
		}

		// Which folder should we display?
		if ($row['forum_status'] == ITEM_LOCKED)
		{
			$folder_image = ($forum_unread) ? 'forum_unread_locked' : 'forum_read_locked';
			$folder_alt = 'FORUM_LOCKED';
		}
		else
		{
			$folder_alt = ($forum_unread) ? 'NEW_POSTS' : 'NO_NEW_POSTS';
		}

		// Create last post link information, if appropriate
		if ($row['forum_last_post_id'])
		{
			$last_post_subject = $row['forum_last_post_subject'];
			$last_post_time = $user->format_date($row['forum_last_post_time']);
			$last_post_url = append_sid(PHPBB_URL . "viewtopic.$phpEx", 'f=' . $row['forum_id_last_post'] . '&amp;p=' . $row['forum_last_post_id']) . '#p' . $row['forum_last_post_id'];
		}
		else
		{
			$last_post_subject = $last_post_time = $last_post_url = '';
		}

		// Output moderator listing ... if applicable
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($forum_moderators[$forum_id]))
		{
			$l_moderator = (sizeof($forum_moderators[$forum_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $forum_moderators[$forum_id]);
		}

		$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
		$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? $row['forum_posts'] : '';

		$s_subforums_list = array();
		foreach ($subforums_list as $subforum)
		{
			$s_subforums_list[] = '<a href="' . $subforum['link'] . '" class="subforum ' . (($subforum['unread']) ? 'unread' : 'read') . '">' . $subforum['name'] . '</a>';
		}
		$s_subforums_list = (string) implode(', ', $s_subforums_list);
		$catless = ($row['parent_id'] == $root_data['forum_id']) ? true : false;

		if ($row['forum_type'] != FORUM_LINK)
		{
			$u_viewforum = append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $row['forum_id']);
		}
		else
		{
			// If the forum is a link and we count redirects we need to visit it
			// If the forum is having a password or no read access we do not expose the link, but instead handle it in viewforum
			if (($row['forum_flags'] & FORUM_FLAG_LINK_TRACK) || $row['forum_password'] || !$auth->acl_get('f_read', $forum_id))
			{
				$u_viewforum = append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $row['forum_id']);
			}
			else
			{
				$u_viewforum = $row['forum_link'];
			}
		}

		//
		//This was someting that MX-Publisher template sytsem didn't supported so S_LAST_CAT was introduced fom S_NO_CAT
		//The template shold be changed allso ;)
		//

		$template->assign_block_vars('forumrow', array(
			'S_IS_CAT'			=> false,
			'S_LAST_CAT'		=> $last_catless,
			'S_NO_CAT'			=> $catless,
			'S_IS_LINK'			=> ($row['forum_type'] == FORUM_LINK) ? true : false,
			'S_UNREAD_FORUM'	=> $forum_unread,
			'S_LOCKED_FORUM'	=> ($row['forum_status'] == ITEM_LOCKED) ? true : false,
			'S_SUBFORUMS'		=> (sizeof($subforums_list)) ? true : false,

			'FORUM_ID'				=> $row['forum_id'],
			'FORUM_NAME'			=> $row['forum_name'],
			'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
			'TOPICS'				=> $row['forum_topics'],
			$l_post_click_count		=> $post_click_count,
			'FORUM_FOLDER_IMG'		=> $user->img($folder_image, $folder_alt),
			'FORUM_FOLDER_IMG_SRC'	=> $user->img($folder_image, $folder_alt, false, '', 'src'),
			'FORUM_IMAGE'			=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="' . $user->lang[$folder_alt] . '" />' : '',
			'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? $phpbb_root_path . $row['forum_image'] : '',
			'LAST_POST_SUBJECT'		=> censor_text($last_post_subject),
			'LAST_POST_TIME'		=> $last_post_time,
			'LAST_POSTER'			=> get_username_string('username', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_COLOUR'	=> get_username_string('colour', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_FULL'		=> get_username_string('full', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'MODERATORS'			=> $moderators_list,
			'SUBFORUMS'				=> $s_subforums_list,

			'L_SUBFORUM_STR'		=> $l_subforums,
			'L_FORUM_FOLDER_ALT'	=> $folder_alt,
			'L_MODERATOR_STR'		=> $l_moderator,

			'U_VIEWFORUM'		=> $u_viewforum,
			'U_LAST_POSTER'		=> get_username_string('profile', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'U_LAST_POST'		=> $last_post_url)
		);

		// Assign subforums loop for style authors
		foreach ($subforums_list as $subforum)
		{
			$template->assign_block_vars('forumrow.subforum', array(
				'U_SUBFORUM'	=> $subforum['link'],
				'SUBFORUM_NAME'	=> $subforum['name'],
				'S_UNREAD'		=> $subforum['unread'])
			);
		}

		$last_catless = $catless;
	}
	
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
				
				'FORUM_FOLDER_IMG' => $subforums_list[$i]['folder_image'], 

				'FORUM_NAME' => $forum_data['forum_name'],
				'FORUM_DESC' => $forum_data['forum_desc'],
				'FORUM_DESC_HTML' => htmlspecialchars(preg_replace('@<[\/\!]*?[^<>]*?>@si', '', $forum_data['forum_desc'])),
				
				'POSTS' => $forum_data['forum_posts'],
				'TOPICS' => $forum_data['forum_topics'],
				
				'LAST_POST' => $subforums_list[$i]['last_post'],
				'LAST_POST_SUB' => $subforums_list[$i]['last_post_sub'],
				'LAST_TOPIC' => $topic_data['topic_title'], //$forum_data['topic_title'],
				
				'MODERATORS' => $subforums_list[$i]['moderator_list'],
				'PARENT' => $forum_data['forum_parent'],
				'ID' => $forum_data['forum_id'],
				'UNREAD' => intval($subforums_list[$i]['unread_topics']),
		
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
	$template->assign_vars(array(
		'U_MARK_FORUMS'		=> ($user->data['is_registered'] || $board_config['load_anon_lastread']) ? append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $root_data['forum_id'] . '&amp;mark=forums') : '',
		'S_HAS_SUBFORUM'	=> ($visible_forums) ? true : false,
		'L_SUBFORUM'		=> ($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'],
		'LAST_POST_IMG'		=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'))
	);

	if ($return_moderators)
	{
		return array($active_forum_ary, $forum_moderators);
	}

	return array($active_forum_ary, array());
}

/**
* Create forum rules for given forum
*/
function generate_forum_rules(&$forum_data)
{
	if (!$forum_data['forum_rules'] && !$forum_data['forum_rules_link'])
	{
		return;
	}

	global $template, $phpbb_root_path, $root_path, $phpEx;

	if ($forum_data['forum_rules'])
	{
		$forum_data['forum_rules'] = mx_generate_text_for_display($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options']);
	}

	$template->assign_vars(array(
		'S_FORUM_RULES'	=> true,
		'U_FORUM_RULES'	=> $forum_data['forum_rules_link'],
		'FORUM_RULES'	=> $forum_data['forum_rules'])
	);
}

/**
* Create forum navigation links for given forum, create parent
* list if currently null, assign basic forum info to template
*/
function generate_forum_nav(&$forum_data)
{
	global $db, $user, $template, $auth;
	global $phpEx, $phpbb_root_path, $root_path;

	if (!$auth->acl_get('f_list', $forum_data['forum_id']))
	{
		return;
	}

	// Get forum parents
	$forum_parents = mx_get_forum_parents($forum_data);
	
	// Build navigation links
	if (!empty($forum_parents))
	{
		foreach ($forum_parents as $parent_forum_id => $parent_data)
		{
			list($parent_name, $parent_type) = array_values($parent_data);

			// Skip this parent if the user does not have the permission to view it
			if (!$auth->acl_get('f_list', $parent_forum_id))
			{
				continue;
			}

			$template->assign_block_vars('navlinks', array(
				'S_IS_CAT'		=> ($parent_type == FORUM_CAT) ? true : false,
				'S_IS_LINK'		=> ($parent_type == FORUM_LINK) ? true : false,
				'S_IS_POST'		=> ($parent_type == FORUM_POST) ? true : false,
				'FORUM_NAME'	=> $parent_name,
				'FORUM_ID'		=> $parent_forum_id,
				'U_VIEW_FORUM'	=> append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $parent_forum_id))
			);
		}
	}

	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> ($forum_data['forum_type'] == FORUM_CAT) ? true : false,
		'S_IS_LINK'		=> ($forum_data['forum_type'] == FORUM_LINK) ? true : false,
		'S_IS_POST'		=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_ID'		=> $forum_data['forum_id'],
		'U_VIEW_FORUM'	=> append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $forum_data['forum_id']))
	);

	$template->assign_vars(array(
		'FORUM_ID' 		=> $forum_data['forum_id'],
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_DESC'	=> generate_text_for_display($forum_data['forum_desc'], $forum_data['forum_desc_uid'], $forum_data['forum_desc_bitfield'], $forum_data['forum_desc_options']))
	);

	return;
}

/**
* Returns forum parents as an array. Get them from forum_data if available, or update the database otherwise
*/
function get_forum_parents(&$forum_data)
{
	global $db;

	$forum_parents = array();

	if ($forum_data['parent_id'] > 0)
	{
		if ($forum_data['forum_parents'] == '')
		{
			$sql = 'SELECT forum_id, forum_name, forum_type
				FROM ' . FORUMS_TABLE . '
				WHERE left_id < ' . $forum_data['left_id'] . '
					AND right_id > ' . $forum_data['right_id'] . '
				ORDER BY left_id ASC';
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not query forums information', '', __LINE__, __FILE__, $sql);
			}
			
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_parents[$row['forum_id']] = array($row['forum_name'], (int) $row['forum_type']);
			}
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not query forums information', '', __LINE__, __FILE__, $sql);
			}

			$forum_data['forum_parents'] = serialize($forum_parents);

			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET forum_parents = '" . $db->sql_escape($forum_data['forum_parents']) . "'
				WHERE parent_id = " . $forum_data['parent_id'];
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Could not update forums information', '', __LINE__, __FILE__, $sql);
				}
		}
		else
		{
			$forum_parents = unserialize($forum_data['forum_parents']);
		}
	}

	return $forum_parents;
}

/**
* Generate topic pagination
*/
function topic_generate_pagination($replies, $url)
{
	global $board_config, $user;

	// Make sure $per_page is a valid value
	$per_page = ($board_config['posts_per_page'] <= 0) ? 1 : $board_config['posts_per_page'];

	if (($replies + 1) > $per_page)
	{
		$total_pages = ceil(($replies + 1) / $per_page);
		$pagination = '';

		$times = 1;
		for ($j = 0; $j < $replies + 1; $j += $per_page)
		{
			$pagination .= '<a href="' . $url . '&amp;start=' . $j . '">' . $times . '</a>';
			if ($times == 1 && $total_pages > 5)
			{
				$pagination .= ' ... ';

				// Display the last three pages
				$times = $total_pages - 3;
				$j += ($total_pages - 4) * $per_page;
			}
			else if ($times < $total_pages)
			{
				$pagination .= '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';
			}
			$times++;
		}
	}
	else
	{
		$pagination = '';
	}

	return $pagination;
}

/**
* Obtain list of moderators of each forum
*/
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $board_config, $template, $db, $phpbb_root_path, $phpEx;

	// Have we disabled the display of moderators? If so, then return
	// from whence we came ...
	if (!$board_config['load_moderators'])
	{
		return;
	}

	$forum_sql = '';

	if ($forum_id !== false)
	{
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}

		// If we don't have a forum then we can't have a moderator
		if (!sizeof($forum_id))
		{
			return;
		}

		$forum_sql = 'AND m.' . $db->sql_in_set('forum_id', $forum_id);
	}
	
	if (!$this->db->sql_field_exists('group_colour', GROUPS_TABLE) || !$this->db->sql_field_exists('user_colour', USERS_TABLE))
	{
		$sql_array = array(
			'SELECT'	=> 'm.*, u.user_id as user_colour, g.group_id as group_colour, g.group_type',
			'FROM'		=> array(
				MODERATOR_CACHE_TABLE	=> 'm',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'm.user_id = u.user_id',
				),
				array(
					'FROM'	=> array(GROUPS_TABLE => 'g'),
					'ON'	=> 'm.group_id = g.group_id',
				),
			),
			'WHERE'		=> "m.display_on_index = 1 $forum_sql",
		);
	}
	else
	{
		$sql_array = array(
			'SELECT'	=> 'm.*, u.user_colour, g.group_colour, g.group_type',
			'FROM'		=> array(
				MODERATOR_CACHE_TABLE	=> 'm',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'm.user_id = u.user_id',
				),
				array(
					'FROM'	=> array(GROUPS_TABLE => 'g'),
					'ON'	=> 'm.group_id = g.group_id',
				),
			),
			'WHERE'		=> "m.display_on_index = 1 $forum_sql",
		);
	}


	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!empty($row['user_id']))
		{
			$forum_moderators[$row['forum_id']][] = mx_get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		}
		else
		{
			$forum_moderators[$row['forum_id']][] = '<a' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . ' href="' . append_sid(PHPBB_URL . "memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</a>';
		}
	}
	$db->sql_freeresult($result);

	return;
}

/**
* User authorisation levels output
*
* @param	string	$mode			Can be forum or topic. Not in use at the moment.
* @param	int		$forum_id		The current forum the user is in.
* @param	int		$forum_status	The forums status bit.
*/
function gen_forum_auth_level($mode, $forum_id, $forum_status)
{
	global $template, $auth, $user, $board_config;

	$locked = ($forum_status == ITEM_LOCKED && !$auth->acl_get('m_edit', $forum_id)) ? true : false;

	$rules = array(
		($auth->acl_get('f_post', $forum_id) && !$locked) ? $user->lang['RULES_POST_CAN'] : $user->lang['RULES_POST_CANNOT'],
		($auth->acl_get('f_reply', $forum_id) && !$locked) ? $user->lang['RULES_REPLY_CAN'] : $user->lang['RULES_REPLY_CANNOT'],
		($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $forum_id) && !$locked) ? $user->lang['RULES_EDIT_CAN'] : $user->lang['RULES_EDIT_CANNOT'],
		($user->data['is_registered'] && $auth->acl_gets('f_delete', 'm_delete', $forum_id) && !$locked) ? $user->lang['RULES_DELETE_CAN'] : $user->lang['RULES_DELETE_CANNOT'],
	);

	if ($board_config['allow_attachments'])
	{
		$rules[] = ($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && !$locked) ? $user->lang['RULES_ATTACH_CAN'] : $user->lang['RULES_ATTACH_CANNOT'];
	}

	foreach ($rules as $rule)
	{
		$template->assign_block_vars('rules', array('RULE' => $rule));
	}

	return;
}

/**
* Generate topic status
*/
function topic_status(&$topic_row, $replies, $unread_topic, &$folder_img, &$folder_alt, &$topic_type)
{
	global $user, $board_config;

	$folder = $folder_new = '';

	if ($topic_row['topic_status'] == ITEM_MOVED)
	{
		$topic_type = $user->lang['VIEW_TOPIC_MOVED'];
		$folder_img = 'topic_moved';
		$folder_alt = 'TOPIC_MOVED';
	}
	else
	{
		switch ($topic_row['topic_type'])
		{
			case POST_GLOBAL:
				$topic_type = $user->lang['VIEW_TOPIC_GLOBAL'];
				$folder = 'global_read';
				$folder_new = 'global_unread';
			break;

			case POST_ANNOUNCE:
				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
				$folder = 'announce_read';
				$folder_new = 'announce_unread';
			break;

			case POST_STICKY:
				$topic_type = $user->lang['VIEW_TOPIC_STICKY'];
				$folder = 'sticky_read';
				$folder_new = 'sticky_unread';
			break;

			default:
				$topic_type = '';
				$folder = 'topic_read';
				$folder_new = 'topic_unread';

				if ($board_config['hot_threshold'] && $replies >= $board_config['hot_threshold'] && $topic_row['topic_status'] != ITEM_LOCKED)
				{
					$folder .= '_hot';
					$folder_new .= '_hot';
				}
			break;
		}

		if ($topic_row['topic_status'] == ITEM_LOCKED)
		{
			$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
			$folder .= '_locked';
			$folder_new .= '_locked';
		}


		$folder_img = ($unread_topic) ? $folder_new : $folder;
		$folder_alt = ($unread_topic) ? 'NEW_POSTS' : (($topic_row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_NEW_POSTS');

		// Posted image?
		if (!empty($topic_row['topic_posted']) && $topic_row['topic_posted'])
		{
			$folder_img .= '_mine';
		}
	}

	if ($topic_row['poll_start'] && $topic_row['topic_status'] != ITEM_MOVED)
	{
		$topic_type = $user->lang['VIEW_TOPIC_POLL'];
	}
}

/**
* Assign/Build custom bbcodes for display in screens supporting using of bbcodes
* The custom bbcodes buttons will be placed within the template block 'custom_codes'
*/
function display_custom_bbcodes()
{
	global $db, $template;

	// Start counting from 22 for the bbcode ids (every bbcode takes two ids - opening/closing)
	$num_predefined_bbcodes = 22;

	$sql = 'SELECT bbcode_id, bbcode_tag, bbcode_helpline
		FROM ' . BBCODES_TABLE . '
		WHERE display_on_posting = 1
		ORDER BY bbcode_tag';
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query bbcode information', '', __LINE__, __FILE__, $sql);
	}

	$i = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('custom_tags', array(
			'BBCODE_NAME'		=> "'[{$row['bbcode_tag']}]', '[/" . str_replace('=', '', $row['bbcode_tag']) . "]'",
			'BBCODE_ID'			=> $num_predefined_bbcodes + ($i * 2),
			'BBCODE_TAG'		=> $row['bbcode_tag'],
			'BBCODE_HELPLINE'	=> str_replace(array('&amp;', '&quot;', "'", '&lt;', '&gt;'), array('\&', '\"', '\\\'', '<', '>'), $row['bbcode_helpline']))
		);

		$i++;
	}
	$db->sql_freeresult($result);
}

/**
* Display reasons
*/
function display_reasons($reason_id = 0)
{
	global $db, $user, $template;

	$sql = 'SELECT *
		FROM ' . REPORTS_REASONS_TABLE . '
		ORDER BY reason_order ASC';
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query forum reasort information', '', __LINE__, __FILE__, $sql);
	}
	
	while ($row = $db->sql_fetchrow($result))
	{
		// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
		if (isset($user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])]) && isset($user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])]))
		{
			$row['reason_description'] = $user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])];
			$row['reason_title'] = $user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])];
		}
		
		$template->assign_block_vars('reason', array(
			'ID'			=> $row['reason_id'],
			'TITLE'			=> $row['reason_title'],
			'DESCRIPTION'	=> $row['reason_description'],
			'S_SELECTED'	=> ($row['reason_id'] == $reason_id) ? true : false)
		);
	}
	$db->sql_freeresult($result);
}

/**
* Display user activity (action forum/topic)
*/
function display_user_activity(&$userdata)
{
	global $auth, $template, $db, $user;
	global $phpbb_root_path, $phpEx;

	// Do not display user activity for users having more than 5000 posts...
	if ($userdata['user_posts'] > 5000)
	{
		return;
	}

	$forum_ary = array();

	// Do not include those forums the user is not having read access to...
	$forum_read_ary = $auth->acl_getf('!f_read');

	foreach ($forum_read_ary as $forum_id => $not_allowed)
	{
		if ($not_allowed['f_read'])
		{
			$forum_ary[] = (int) $forum_id;
		}
	}

	$forum_ary = array_unique($forum_ary);
	$forum_sql = (sizeof($forum_ary)) ? 'AND ' . $db->sql_in_set('forum_id', $forum_ary, true) : '';

	// Obtain active forum
	$sql = 'SELECT forum_id, COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . '
		WHERE poster_id = ' . $userdata['user_id'] . "
			AND post_postcount = 1
			$forum_sql
		GROUP BY forum_id
		ORDER BY num_posts DESC";
	$result = $db->sql_query_limit($sql, 1);
	$active_f_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!empty($active_f_row))
	{
		$sql = 'SELECT forum_name
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $active_f_row['forum_id'];
		$result = $db->sql_query($sql, 3600);
		$active_f_row['forum_name'] = (string) $db->sql_fetchfield('forum_name');
		$db->sql_freeresult($result);
	}

	// Obtain active topic
	$sql = 'SELECT topic_id, COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . '
		WHERE poster_id = ' . $userdata['user_id'] . "
			AND post_postcount = 1
			$forum_sql
		GROUP BY topic_id
		ORDER BY num_posts DESC";
	$result = $db->sql_query_limit($sql, 1);
	$active_t_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!empty($active_t_row))
	{
		$sql = 'SELECT topic_title
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $active_t_row['topic_id'];
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not query topic information', '', __LINE__, __FILE__, $sql);
		}
		$active_t_row['topic_title'] = (string) $db->sql_fetchfield('topic_title');
		$db->sql_freeresult($result);
	}

	$userdata['active_t_row'] = $active_t_row;
	$userdata['active_f_row'] = $active_f_row;

	$active_f_name = $active_f_id = $active_f_count = $active_f_pct = '';
	if (!empty($active_f_row['num_posts']))
	{
		$active_f_name = $active_f_row['forum_name'];
		$active_f_id = $active_f_row['forum_id'];
		$active_f_count = $active_f_row['num_posts'];
		$active_f_pct = ($userdata['user_posts']) ? ($active_f_count / $userdata['user_posts']) * 100 : 0;
	}

	$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
	if (!empty($active_t_row['num_posts']))
	{
		$active_t_name = $active_t_row['topic_title'];
		$active_t_id = $active_t_row['topic_id'];
		$active_t_count = $active_t_row['num_posts'];
		$active_t_pct = ($userdata['user_posts']) ? ($active_t_count / $userdata['user_posts']) * 100 : 0;
	}

	$l_active_pct = ($userdata['user_id'] != ANONYMOUS && $userdata['user_id'] == $user->data['user_id']) ? $user->lang['POST_PCT_ACTIVE_OWN'] : $user->lang['POST_PCT_ACTIVE'];

	$template->assign_vars(array(
		'ACTIVE_FORUM'			=> $active_f_name,
		'ACTIVE_FORUM_POSTS'	=> ($active_f_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_f_count),
		'ACTIVE_FORUM_PCT'		=> sprintf($l_active_pct, $active_f_pct),
		'ACTIVE_TOPIC'			=> censor_text($active_t_name),
		'ACTIVE_TOPIC_POSTS'	=> ($active_t_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_t_count),
		'ACTIVE_TOPIC_PCT'		=> sprintf($l_active_pct, $active_t_pct),
		'U_ACTIVE_FORUM'		=> append_sid(PHPBB_URL . "viewforum.$phpEx", 'f=' . $active_f_id),
		'U_ACTIVE_TOPIC'		=> append_sid(PHPBB_URL . "viewtopic.$phpEx", 't=' . $active_t_id),
		'S_SHOW_ACTIVITY'		=> true)
	);
}

/**
* Topic and forum watching common code
*/
function watch_topic_forum_old($mode, &$s_watching, &$s_watching_img, $user_id, $forum_id, $topic_id, $notify_status = 'unset', $start = 0)
{
	global $template, $db, $user, $phpEx, $start, $phpbb_root_path;

	$table_sql = ($mode == 'forum') ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ($mode == 'forum') ? 'forum_id' : 'topic_id';
	$match_id = ($mode == 'forum') ? $forum_id : $topic_id;

	$u_url = ($mode == 'forum') ? 'f' : 'f=' . $forum_id . '&amp;t';

	// Is user watching this thread?
	if ($user_id != ANONYMOUS)
	{
		$can_watch = true;

		if ($notify_status == 'unset')
		{
			$sql = "SELECT notify_status
				FROM $table_sql
				WHERE $where_sql = $match_id
					AND user_id = $user_id";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not query user notify status', '', __LINE__, __FILE__, $sql);
			}

			$notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
			$db->sql_freeresult($result);
		}

		if (!is_null($notify_status))
		{
			if (isset($_GET['unwatch']))
			{
				if ($_GET['unwatch'] == $mode)
				{
					$is_watching = 0;

					$sql = 'DELETE FROM ' . $table_sql . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Could not update unwatch notify status', '', __LINE__, __FILE__, $sql);
					}
				}

				$redirect_url = append_sid(PHPBB_URL . "view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");

				meta_refresh(3, $redirect_url);

				$message = $user->lang['NOT_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				$is_watching = true;

				if ($notify_status)
				{
					$sql = 'UPDATE ' . $table_sql . "
						SET notify_status = 0
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Could not update watching notify status', '', __LINE__, __FILE__, $sql);
					}
				}
			}
		}
		else
		{
			if (isset($_GET['watch']))
			{
				if ($_GET['watch'] == $mode)
				{
					$is_watching = true;

					$sql = 'INSERT INTO ' . $table_sql . " (user_id, $where_sql, notify_status)
						VALUES ($user_id, $match_id, 0)";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Could not innsert user watching status', '', __LINE__, __FILE__, $sql);
					}
				}

				$redirect_url = append_sid(PHPBB_URL . "view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
				meta_refresh(3, $redirect_url);

				$message = $user->lang['ARE_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				$is_watching = 0;
			}
		}
	}
	else
	{
		if (isset($_GET['unwatch']) && $_GET['unwatch'] == $mode)
		{
			login_box();
		}
		else
		{
			$can_watch = 0;
			$is_watching = 0;
		}
	}

	if ($can_watch)
	{
		$s_watching['link'] = append_sid(PHPBB_URL . "view$mode.$phpEx", "$u_url=$match_id&amp;" . (($is_watching) ? 'unwatch' : 'watch') . "=$mode&amp;start=$start");
		$s_watching['title'] = $user->lang[(($is_watching) ? 'STOP' : 'START') . '_WATCHING_' . strtoupper($mode)];
		$s_watching['is_watching'] = $is_watching;
	}

	return;
}

/**
* Topic and forum watching common code
*/
function watch_topic_forum($mode, &$s_watching, $user_id, $forum_id, $topic_id, $notify_status = 'unset', $start = 0)
{
	global $template, $db, $user, $phpEx, $start, $phpbb_root_path;

	$table_sql = ($mode == 'forum') ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ($mode == 'forum') ? 'forum_id' : 'topic_id';
	$match_id = ($mode == 'forum') ? $forum_id : $topic_id;

	$u_url = ($mode == 'forum') ? 'f' : 'f=' . $forum_id . '&amp;t';

	// Is user watching this thread?
	if ($user_id != ANONYMOUS)
	{
		$can_watch = true;

		if ($notify_status == 'unset')
		{
			$sql = "SELECT notify_status
				FROM $table_sql
				WHERE $where_sql = $match_id
					AND user_id = $user_id";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not query user notify status', '', __LINE__, __FILE__, $sql);
			}
			
			$notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
			$db->sql_freeresult($result);
		}

		if (!is_null($notify_status) && $notify_status !== '')
		{
			if (isset($_GET['unwatch']))
			{
				if ($_GET['unwatch'] == $mode)
				{
					$is_watching = 0;

					$sql = 'DELETE FROM ' . $table_sql . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Could not query unwatch status', '', __LINE__, __FILE__, $sql);
					}
				}

				$redirect_url = append_sid(PHPBB_URL . "view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");

				meta_refresh(3, $redirect_url);

				$message = $user->lang['NOT_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				$is_watching = true;

				if ($notify_status)
				{
					$sql = 'UPDATE ' . $table_sql . "
						SET notify_status = 0
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Could not query user notify status', '', __LINE__, __FILE__, $sql);
					}
				}
			}
		}
		else
		{
			if (isset($_GET['watch']))
			{
				if ($_GET['watch'] == $mode)
				{
					$is_watching = true;

					$sql = 'INSERT INTO ' . $table_sql . " (user_id, $where_sql, notify_status)
						VALUES ($user_id, $match_id, 0)";
					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Could not query user notify status', '', __LINE__, __FILE__, $sql);
					}
				}

				$redirect_url = append_sid(PHPBB_URL . "view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
				meta_refresh(3, $redirect_url);

				$message = $user->lang['ARE_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				$is_watching = 0;
			}
		}
	}
	else
	{
		if (isset($_GET['unwatch']) && $_GET['unwatch'] == $mode)
		{
			login_box();
		}
		else
		{
			$can_watch = 0;
			$is_watching = 0;
		}
	}

	if ($can_watch)
	{
		$s_watching['link'] = append_sid(PHPBB_URL . "view$mode.$phpEx", "$u_url=$match_id&amp;" . (($is_watching) ? 'unwatch' : 'watch') . "=$mode&amp;start=$start");
		$s_watching['title'] = $user->lang[(($is_watching) ? 'STOP' : 'START') . '_WATCHING_' . strtoupper($mode)];
		$s_watching['is_watching'] = $is_watching;
	}

	return;
}

/**
* Get user rank title and image
*
* @param int $user_rank the current stored users rank id
* @param int $user_posts the users number of posts
* @param string &$rank_title the rank title will be stored here after execution
* @param string &$rank_img the rank image as full img tag is stored here after execution
* @param string &$rank_img_src the rank image source is stored here after execution
*
*/
function get_user_rank($user_rank, $user_posts, &$rank_title, &$rank_img, &$rank_img_src)
{
	global $ranks, $board_config;

	if (empty($ranks))
	{
		global $backend;
		$ranks = $backend->obtain_ranks();
	}

	if (!empty($user_rank))
	{
		$rank_title = (isset($ranks['special'][$user_rank]['rank_title'])) ? $ranks['special'][$user_rank]['rank_title'] : '';
		$rank_img = (!empty($ranks['special'][$user_rank]['rank_image'])) ? '<img src="' . $board_config['ranks_path'] . '/' . $ranks['special'][$user_rank]['rank_image'] . '" alt="' . $ranks['special'][$user_rank]['rank_title'] . '" title="' . $ranks['special'][$user_rank]['rank_title'] . '" />' : '';
		$rank_img_src = (!empty($ranks['special'][$user_rank]['rank_image'])) ? $board_config['ranks_path'] . '/' . $ranks['special'][$user_rank]['rank_image'] : '';
	}
	else
	{
		if (!empty($ranks['normal']))
		{
			foreach ($ranks['normal'] as $rank)
			{
				if ($user_posts >= $rank['rank_min'])
				{
					$rank_title = $rank['rank_title'];
					$rank_img = (!empty($rank['rank_image'])) ? '<img src="' . $board_config['ranks_path'] . '/' . $rank['rank_image'] . '" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" />' : '';
					$rank_img_src = (!empty($rank['rank_image'])) ? $board_config['ranks_path'] . '/' . $rank['rank_image'] : '';
					break;
				}
			}
		}
	}
}

/**
* Get user avatar
*
* @param string $avatar Users assigned avatar name
* @param int $avatar_type Type of avatar
* @param string $avatar_width Width of users avatar
* @param string $avatar_height Height of users avatar
* @param string $alt Optional language string for alt tag within image, can be a language key or text
*
* @return string Avatar image
*/
function get_user_avatar($avatar, $avatar_type, $avatar_width, $avatar_height, $alt = 'USER_AVATAR')
{
	global $user, $board_config, $phpbb_root_path, $phpEx;

	if (empty($avatar) || !$avatar_type)
	{
		return '';
	}

	$avatar_img = '';

	switch ($avatar_type)
	{
		case AVATAR_UPLOAD:
			$avatar_img = $phpbb_root_path . "download.$phpEx?avatar=";
		break;

		case AVATAR_GALLERY:
			$avatar_img = $phpbb_root_path . $board_config['avatar_gallery_path'] . '/';
		break;
	}

	$avatar_img .= $avatar;
	return '<img src="' . $avatar_img . '" width="' . $avatar_width . '" height="' . $avatar_height . '" alt="' . ((!empty($user->lang[$alt])) ? $user->lang[$alt] : $alt) . '" />';
}

?>