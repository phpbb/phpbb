<?php

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_styles'))
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['STYLE']['MANAGE_STYLE'] = $filename . "$SID&amp;mode=styles";
	$module['STYLE']['MANAGE_TEMPLATE'] = $filename . "$SID&amp;mode=templates";
	$module['STYLE']['MANAGE_THEME'] = $filename . "$SID&amp;mode=themes";
	$module['STYLE']['MANAGE_IMAGESET'] = $filename . "$SID&amp;mode=imagesets";

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Do we have styles admin permissions?
if (!$auth->acl_get('a_styles'))
{
	trigger_error($user->lang['NO_ADMIN']);
}


// Get and set some vars
$mode = (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';

if (isset($_REQUEST['action']))
{
	$action = htmlspecialchars($_REQUEST['action']);
}
else
{
	if (isset($_POST['add']))
	{
		$action = 'add';
	}
	else if (isset($_POST['preview']))
	{
		$action = 'preview';
	}
	else
	{
		$action = '';
	}
}

$error = array();


// What shall we do today then?
switch ($mode)
{

	case 'styles':

		$style_id = (isset($_REQUEST['id'])) ? intval($_REQUEST['id'])  : '';

		switch ($action)
		{
			case 'activate':
			case 'deactivate':
				// TODO ... reset user_styles if their style is deactivated
				$sql = 'UPDATE ' . STYLES_TABLE . '
					SET style_active = ' . (($action == 'activate') ? 1 : 0) . ' 
					WHERE style_id = ' . $style_id;
				$db->sql_query($sql);
				break;

			case 'delete':
				break;

			case 'export':
				break;

			case 'add':
			case 'edit':

				if (isset($_POST['update']))
				{
					$style_name = (isset($_POST['style_name'])) ? stripslashes(htmlspecialchars($_POST['style_name'])) : '';
					$style_copyright = (isset($_POST['style_copyright'])) ? stripslashes(htmlspecialchars($_POST['style_copyright'])) : '';

					$style_active = (!empty($_POST['style_active'])) ? 1 : 0;

					$template_id = (!empty($_POST['template_id'])) ? intval($_POST['template_id']) : 0;
					$theme_id = (!empty($_POST['theme_id'])) ? intval($_POST['theme_id']) : 0;
					$imageset_id = (!empty($_POST['imageset_id'])) ? intval($_POST['imageset_id']) : 0;

					if (empty($style_name))
					{
						$error[] = $user->lang['STYLE_ERR_STYLE_NAME'];
					}

					if (strlen($style_name) > 30)
					{
						$error[] = $user->lang['STYLE_ERR_NAME_LONG'];
					}

					if (strlen($style_copyright) > 60)
					{
						$error[] = $user->lang['STYLE_ERR_COPY_LONG'];
					}

					if (!$template_id || !$theme_id || !$imageset_id)
					{
						$error[] = $user->lang['STYLE_ERR_NO_IDS'];
					}

					if ($action == 'add')
					{
						$sql = 'SELECT style_name 
							FROM ' . STYLES_TABLE . " 
							WHERE style_name = '" . $db->sql_escape($style_name) . "'";
						$result = $db->sql_query($sql);

						if ($row = $db->sql_fetchrow($result))
						{
							$error[] = $user->lang['STYLE_ERR_NAME_EXIST'];
						}
						$db->sql_freeresult($result);
					}

					if (!sizeof($error))
					{
						$sql_ary = array(
							'style_name'		=> $style_name, 
							'style_copyright'	=> $style_copyright, 
							'template_id'		=> $template_id, 
							'theme_id'			=> $theme_id, 
							'imageset_id'		=> $imageset_id, 
						);

						$sql = ($action == 'add') ? 'INSERT INTO ' . STYLES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary) : 'UPDATE ' . STYLES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE style_id = $style_id";
						$db->sql_query($sql);

						$log = ($action == 'add') ? 'LOG_ADD_STYLE' : 'LOG_EDIT_STYLE';
						add_log('admin', $log, addslashes($style_name));

						$message = ($action == 'add') ? 'STYLED_ADDED' : 'STYLE_EDITED';
						trigger_error($user->lang[$message]);
					}
				}

				if (!sizeof($error))
				{
					if ($style_id)
					{
						$sql = 'SELECT * 
							FROM ' . STYLES_TABLE . "
							WHERE style_id = $style_id";
						$result = $db->sql_query($sql);

						if (!extract($db->sql_fetchrow($result)))
						{
							die("ERROR");
						}
						$db->sql_freeresult($result);
					}
					else
					{
						$style_name = (isset($_POST['style_name'])) ? stripslashes(htmlspecialchars($_POST['style_name'])) : '';
						$style_copyright = '';
						$style_active = 1;
						$template_id = $theme_id = $imageset_id = 0;
					}
				}

				$style_options = array();
				$field_ary = array(STYLES_CSS_TABLE => 'theme', STYLES_TPL_TABLE => 'template', STYLES_IMAGE_TABLE => 'imageset');
				foreach ($field_ary as $table => $field)
				{
					$sql = "SELECT {$field}_id, {$field}_name
						FROM $table 
						ORDER BY {$field}_id";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$selected = ($row[$field . '_id'] == ${$field . '_id'}) ? ' selected="selected"' : '';
						${$field . '_options'} .= '<option value="' . $row[$field . '_id'] . '"' . $selected . '>' . $row[$field . '_name'] . '</option>';
					}
					$db->sql_freeresult($result);
				}


				// Output the page
				adm_page_header($user->lang['EDIT_STYLE']);

?>

<h1><?php echo $user->lang['EDIT_STYLE']; ?></h1>

<p><?php echo $user->lang['EDIT_STYLE_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$style_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2">Edit Style</th>
	</tr>
<?php

				if (sizeof($error))
				{

?>
	<tr>
		<td colspan="2" class="row3" align="center"><span style="color:red"><?php echo implode('<br />', $error); ?></span></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="row1">Style Name</td>
		<td class="row2"><input class="post" type="text" name="style_name" maxlength="30" size="30" value="<?php echo $style_name; ?>" /></td>
	</tr>
	<tr>
		<td class="row1">Style Copyright</td>
		<td class="row2"><?php
	
				echo ($action == 'edit') ? '<b>' . $style_copyright . '</b>' : '<input class="post" type="text" name="style_copyright" maxlength="60" size="30" value="' . $style_copyright . '" />';

?></td>
	</tr>
	<tr>
		<td class="row1">Template set:</td>
		<td class="row2"><select name="template_id"><?php echo $template_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1">Theme set:</td>
		<td class="row2"><select name="theme_id"><?php echo $theme_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1">Imageset:</td>
		<td class="row2"><select name="imageset_id"><?php echo $imageset_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1">Status:</td>
		<td class="row2"><input type="radio" name="style_active" value="0"<?php echo (!$style_active) ? ' checked="checked"' : ''; ?> /> Inactive &nbsp; <input type="radio" name="style_active" value="1"<?php echo ($style_active) ? ' checked="checked"' : ''; ?> /> Active</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<!-- input class="btnlite" type="submit" name="preview" value="<?php echo $user->lang['PREVIEW']; ?>" />&nbsp;&nbsp;--><input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

				adm_page_footer();
				break;
		}

		
		adm_page_header($user->lang['MANAGE_STYLE']);

?>
<h1><?php echo $user->lang['MANAGE_STYLE']; ?></h1>

<p><?php echo $user->lang['MANAGE_STYLE_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th nowrap="nowrap">Style name</th>
		<th nowrap="nowrap">Used by</th>
		<th nowrap="nowrap" colspan="4">&nbsp;</th>
	</tr>
<?php

		$sql = 'SELECT user_style, COUNT(user_style) AS style_count
			FROM ' . USERS_TABLE . ' 
			GROUP BY user_style';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$style_count[$row['user_style']] = $row['style_count'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT style_id, style_name, style_active 
			FROM ' . STYLES_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

				$stylevis = (!$row['style_active']) ? 'activate' : 'deactivate';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;id=" . $row['style_id']; ?>"><?php echo $row['style_name']; ?></a><?php echo ($config['default_style'] == $row['style_id']) ? ' *' : ''; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo (!empty($style_count[$row['style_id']])) ? $style_count[$row['style_id']] : '0'; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$stylevis&amp;id=" . $row['style_id']; ?>"><?php echo $user->lang['STYLE_' . strtoupper($stylevis)]; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;id=" . $row['style_id']; ?>">Delete</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=export&amp;id=" . $row['style_id']; ?>">Export</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "{$phpbb_root_path}index.$phpEx$SID&amp;style=" . $row['style_id']; ?>" target="_stylepreview">Preview</a>&nbsp;</td>
	</tr>
<?php

			}
			while ($row = $db->sql_fetchrow($result));
		}
		else
		{
		}
		$db->sql_freeresult($result);


?>
	<tr>
		<td class="cat" colspan="6" align="right">Create new style: <input class="post" type="text" name="style_name" value="" maxlength="30" size="25" /> <input class="btnmain" type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table></form>
<?php 

		break;








	case 'imagesets':
		$imageset_id = (isset($_REQUEST['id'])) ? intval($_REQUEST['id'])  : 0;

		switch ($action)
		{
			case 'edit':

				$imgname = (!empty($_POST['imgname'])) ? htmlspecialchars($imgname) : '';

				if ($imageset_id)
				{
					$sql = 'SELECT * 
						FROM ' . STYLES_IMAGE_TABLE . "
						WHERE imageset_id = $imageset_id";
					$result = $db->sql_query($sql);

					if (!extract($db->sql_fetchrow($result)))
					{
						trigger_error($user->lang['NO_IMAGESET']);
					}

					do {
						extract($db->sql_fetchrow($result));
					}
					while ($row = $db->sql_fetchrow($result));
					$db->sql_freeresult($result);


					$imglist = array(
						'buttons'	=> array(
							'btn_post', 'btn_post_pm', 'btn_reply', 'btn_reply_pm', 'btn_locked', 'btn_profile', 'btn_pm', 'btn_delete', 'btn_ip', 'btn_quote', 'btn_search', 'btn_edit', 'btn_report', 'btn_email', 'btn_www', 'btn_icq', 'btn_aim', 'btn_yim', 'btn_msnm', 'btn_jabber', 'btn_online', 'btn_offline', 'btn_topic_watch', 'btn_topic_unwatch',
						),
						'icons'		=> array(
							'icon_unapproved', 'icon_reported', 'icon_attach', 'icon_post', 'icon_post_new', 'icon_post_latest', 'icon_post_newest',),
						'forums'		=> array(
							'forum', 'forum_new', 'forum_locked', 'forum_link', 'sub_forum', 'sub_forum_new',),
						'folders'	=> array(
							'folder', 'folder_posted', 'folder_new', 'folder_new_posted', 'folder_hot', 'folder_hot_posted', 'folder_hot_new', 'folder_hot_new_posted', 'folder_locked', 'folder_locked_posted', 'folder_locked_new', 'folder_locked_new_posted', 'folder_sticky', 'folder_sticky_posted', 'folder_sticky_new', 'folder_sticky_new_posted', 'folder_announce', 'folder_announce_posted', 'folder_announce_new', 'folder_announce_new_posted',),
						'polls'		=> array(
							'poll_left', 'poll_center', 'poll_right',), 
						'custom'	=> array(), 
					);


					$test_ary = array();
					foreach ($imglist as $category => $img_ary)
					{
						foreach ($img_ary as $img)
						{
							if (!empty($$img))
							{
								$test_ary[] = preg_replace('#^"styles/imagesets/' . $imageset_path . '/(\{LANG\}/)?(.*?)".*$#', '\2', $$img);
							}
						}
					}

					$dp = @opendir($phpbb_root_path . 'styles/imagesets/' . $imageset_path);
					while ($file = readdir($dp))
					{
						if (is_file($phpbb_root_path . 'styles/imagesets/' . $imageset_path . '/' . $file))
						{
							if (!in_array($file, $test_ary))
							{
								$imglist['custom'][] = $file;
							}
						}
					}
					closedir($dp);
					unset($matches);
					unset($test_ary);

					$imgwidth = (preg_match('#width="([0-9]+?)"#i', $$imgname, $matches)) ? $matches[1] : 0;
					$imgheight = (preg_match('#height="([0-9]+?)"#i', $$imgname, $matches)) ? $matches[1] : 0;
				}


				// Generate list of image options
				$img_options = '';
				foreach ($imglist as $category => $img_ary)
				{
					$img_options .= '<option class="sep">' . $category . '</option>';
					foreach ($img_ary as $img)
					{
						$selected = ($img == $imgname) ? ' selected="selected"' : '';
						$img_options .= '<option value="' . $img . '"' . $selected . '>' . (($category == 'custom') ? $img : $img) . '</option>';
					}
				}

				// Grab list of potential images
				$imagesetlist = filelist($phpbb_root_path . 'styles/imagesets/' . $imageset_path);

				$imagesetlist_options = '';
				foreach ($imagesetlist as $img)
				{
					$img = substr($img['path'], 1) . (($img['path'] != '') ? '/' : '') . $img['file']; 

					$selected = (preg_match('#' . preg_quote($img) . '$#', $background_image)) ? ' selected="selected"' : '';
					$imagesetlist_options .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
				}
				$imagesetlist_options = '<option value=""' . (($edit_img == '') ? ' selected="selected"' : '') . '>' . $user->lang['NONE'] . '</option>' . $imagesetlist_options;
				unset($imagesetlist);


				adm_page_header($user->lang['EDIT_IMAGESET']);

?>

<h1><?php echo $user->lang['EDIT_IMAGESET']; ?></h1>

<p><?php echo $user->lang['EDIT_IMAGESET_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;id=$imageset_id&amp;action=$action"; ?>"><table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<!-- tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th>Parameter</th>
				<th>Value</th>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Theme name:</b></td>
				<td class="row2"><input class="post" type="text" name="theme_name" value="<?php echo $theme_name; ?>" maxlength="30" size="25" /></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Copyright:</b></td>
				<td class="row2"><input class="post" type="text" name="theme_copyright" value="<?php echo $theme_copyright; ?>" maxlength="30" size="25" /></td>
			</tr>
		</table>
		
		<br clear="all" /><br /></td>
	</tr -->
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_CLASS']; ?>: <select name="imgname" onchange="this.form.submit(); "><?php echo $img_options; ?></select>&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2">Preview</th>
			</tr>
			<tr>
				<td class="row1" colspan="2" align="center"><?php echo (!empty($$imgname)) ? '<img src=' . str_replace('"styles/', '"../styles/', str_replace('{LANG}', $user->img_lang, $$imgname)) . ' vspace="5" />' : ''; ?></td>
			</tr>
			<tr>
				<th width="40%">Parameter</th>
				<th>Value</th>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Image:</b></td>
				<td class="row2"><select name="imgpath"><?php echo $imagesetlist_options; ?></select></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Dimensions:</b><br /><span class="gensmall">Dimensions are optional, set to zero to ignore.</span></td>
				<td class="row2"><input class="post" type="text" name="imgwidth" maxlength="4" size="2" value="<?php echo (!empty($imgwidth)) ? $imgwidth : '0'; ?>" /> X <input class="post" type="text" name="imgheight" maxlength="4" size="2" value="<?php echo (!empty($imgheight)) ? $imgheight : '0'; ?>" /></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnmain" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

				adm_page_footer();
				break;
		}


		adm_page_header($user->lang['MANAGE_IMAGESET']);

?>
<h1><?php echo $user->lang['MANAGE_IMAGESET']; ?></h1>

<p><?php echo $user->lang['MANAGE_IMAGESET_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Imageset name</th>
		<th colspan="2">&nbsp;</th>
	</tr>
<?php

		$sql = 'SELECT imageset_id, imageset_name
			FROM ' . STYLES_IMAGE_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;id=" . $row['imageset_id']; ?>"><?php echo $row['imageset_name']; ?></a></td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;id=" . $row['imageset_id']; ?>">Delete</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=export&amp;id=" . $row['imageset_id']; ?>">Export</a>&nbsp;</td>
	</tr>
<?php

			}
			while ($row = $db->sql_fetchrow($result));
		}
		else
		{
		}
		$db->sql_freeresult($result);


?>
	<tr>
		<td class="cat" colspan="3">&nbsp;</td>
	</tr>
</table></form>
<?php 

		break;







	case 'templates':

		switch ($action)
		{
			case 'preview':
				break;

			case 'edit':

				$template_id = (isset($_REQUEST['id'])) ? $_REQUEST['id']  : false;
				$tplcols = (isset($_POST['tplcols'])) ? max(20, intval($_POST['tplcols'])) : 76;
				$tplrows = (isset($_POST['tplrows'])) ? max(5, intval($_POST['tplrows'])) : 20;
				$tplname = (isset($_POST['tplname'])) ? $_POST['tplname']  : '';


				$tpllist = array(
					'misc'		=> array(
						'confirm_body.html', 'faq_body.html', 'index_body.html',  'message_body.html', 'viewonline_body.html', 
					),
					'includes'	=> array(
						'overall_footer.html', 'overall_header.html', 'simple_footer.html', 'simple_header.html', 'searchbox.html', 'jumpbox.html',
					), 
					'forum'		=> array(
						'viewforum_body.html', 'viewforum_subforum.html', 
					),
					'topic'		=> array(
						'viewtopic_attach_body.html', 'viewtopic_body.html', 'viewtopic_print.html',
					),
					'group'		=> array(
						'gcp_body.html', 'gcp_pending_info.html', 'gcp_user_body.html', 
					),
					'user'		=> array(
						'ucp_agreement.html', 'ucp_footer.html', 'ucp_header.html', 'ucp_main.html', 'ucp_pm_body.html', 'ucp_pm_popup.html', 'ucp_pm_preview.html', 'ucp_pm_read.html', 'ucp_prefs.html', 'ucp_profile.html', 'ucp_register.html', 'ucp_remind.html', 
					),
					'profile'	=> array(
						'memberlist_body.html', 'memberlist_email.html', 'memberlist_im.html', 'memberlist_view.html', 
					), 
					'mod'		=> array(
						'mcp_forum.html', 'mcp_foruminfo.html', 'mcp_front.html', 'mcp_header.html', 'mcp_jumpbox.html', 'mcp_move.html', 'mcp_post.html', 'mcp_queue.html', 'mcp_reports.html', 'mcp_topic.html', 'mcp_viewlogs.html', 'report_body.html', 
					),
					'search'	=> array(
						'search_body.html', 'search_results_posts.html', 'search_results_topics.html', 
					),
					'posting'	=> array(
						'posting_attach_body.html', 'posting_body.html', 'posting_poll_body.html', 'posting_preview.html', 'posting_smilies.html', 'posting_topic_review.html', 
					),
					'login'		=> array(
						'login_body.html', 'login_forum.html', 
					), 
					'custom'	=> array(), 
				);

				$tpldata = '';
				if ($template_id)
				{
					$sql = 'SELECT * 
						FROM ' . STYLES_TPL_TABLE . "
						WHERE template_id = $template_id";
					$result = $db->sql_query($sql);

					if (!(extract($db->sql_fetchrow($result))))
					{
						trigger_error($user->lang['NO_TEMPLATE']);
					}
					$db->sql_freeresult($result);


					$test_ary = array();
					foreach ($tpllist as $category => $tpl_ary)
					{
						$test_ary = array_merge($test_ary, $tpl_ary);
					}

					$dp = @opendir($phpbb_root_path . 'styles/templates/' . $template_path);
					while ($file = readdir($dp))
					{
						if (!strstr($file, 'bbcode.') && strstr($file, '.html') && is_file($phpbb_root_path . 'styles/templates/' . $template_path . '/' . $file))
						{
							if (!in_array($file, $test_ary))
							{
								$tpllist['custom'][] = $file;
							}
						}
					}
					closedir($dp);
					unset($matches);
					unset($test_ary);

					if ($tplname)
					{
						$fp = fopen($phpbb_root_path . 'styles/templates/' . $template_path . '/' . $tplname, 'r');// . '.html'
						while (!feof($fp))
						{
							$tpldata .= fread($fp, 4096);
						}
						@fclose($fp);

						preg_match_all('#<!\-\- INCLUDE (.*?) \-\->#', $tpldata, $included_tpls);
						$included_tpls = $included_tpls[1];
					}
				}

/*				if (isset($_POST['tpl_download']))
				{
					header("Content-Type: text/html; name=\"" . $tplname . ".html\"");
					header("Content-disposition: attachment; filename=" . $tplname . ".html");
					echo $str;
					exit;

				}
*/


				// Generate list of template options
				$tpl_options = '';
				ksort($tpllist);
				foreach ($tpllist as $category => $tpl_ary)
				{
					if (sizeof($tpl_ary))
					{
						sort($tpl_ary);
						$tpl_options .= '<option class="sep">' . $category . '</option>';

						foreach ($tpl_ary as $tpl_file)
						{
							$selected = ($tpl_file == $tplname) ? ' selected="selected"' : '';
							$tpl_options .= '<option value="' . $tpl_file . '"' . $selected . '>' . (($category == 'custom') ? $tpl_file : $tpl_file) . '</option>';
						}
					}
				}


				$tplname_options = '';
				$dp = @opendir($phpbb_root_path . 'styles/templates/' . $template_path);
				while ($file = readdir($dp))
				{
					if (strstr($file, '.html') && is_file($phpbb_root_path . 'styles/templates/' . $template_path . '/' . $file))
					{
						$tpl = substr($file, 0, strpos($file, '.'));
						$selected = ($tplname == $tpl) ? ' selected="selected"' : '';
						$tplname_options .= '<option value="' . $tpl . '"' . $selected . '>' . $tpl . '</option>';
					}
				}
				closedir($dp);



				//
				adm_page_header($user->lang['EDIT_TEMPLATE']);

?>

<h1><?php echo $user->lang['EDIT_TEMPLATE']; ?></h1>

<p><?php echo $user->lang['EDIT_TEMPLATE_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;id=$template_id&amp;action=$action"; ?>"><table cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_TEMPLATE']; ?>: <select name="tplname" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $tpl_options; ?></select>&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat">Columns: <input class="post" type="text" name="tplcols" size="3" maxlength="3" value="<?php echo $tplcols; ?>" /> &nbsp;Rows: <input class="post" type="text" name="tplrows" size="3" maxlength="3" value="<?php echo $tplrows; ?>" />&nbsp; <input class="btnlite" type="submit" value="Update" /></td>
			</tr>
			<tr>
				<th>Raw HTML</th>
			</tr>
			<tr>
				<td class="row2" align="center"><textarea class="post" style="font-family:'Courier New', monospace;font-size:10pt;line-height:125%;" cols="<?php echo $tplcols; ?>" rows="<?php echo $tplrows; ?>" name="decompile"><?php echo htmlentities($tpldata); ?></textarea></td>
			</tr>
			<tr>
				<td class="cat" align="center"><input class="btnlite" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

				adm_page_footer();
				break;

			case 'delete':
				break;

			case 'export':
				break;
		}

		adm_page_header($user->lang['MANAGE_TEMPLATE']);

?>
<h1><?php echo $user->lang['MANAGE_TEMPLATE']; ?></h1>

<p><?php echo $user->lang['MANAGE_TEMPLATE_EXPLAIN']; ?></p>

<form name="templates" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2">Template name</th>
		<th colspan="3">&nbsp;</th>
	</tr>
<?php

		$sql = 'SELECT template_id, template_name 
			FROM ' . STYLES_TPL_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=edit&amp;id=" . $row['template_id']; ?>"><?php echo $row['template_name']; ?></a></td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=bbcode&amp;id=" . $row['template_id']; ?>">BBCode</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=delete&amp;id=" . $row['template_id']; ?>">Delete</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=export&amp;id=" . $row['template_id']; ?>">Export</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=preview&amp;id=" . $row['template_id']; ?>">Preview</a>&nbsp;</td>
	</tr>
<?php

			}
			while ($row = $db->sql_fetchrow($result));
		}
		else
		{
		}
		$db->sql_freeresult($result);


?>
	<tr>
		<td class="cat" colspan="5" align="right">&nbsp;</td>
	</tr>
</table></form>

<?php

		adm_page_footer();

		break;











	case 'themes':

		switch ($action)
		{
			case 'preview':
				break;

			case 'edit':

				// General parameters
				$theme_id = (isset($_REQUEST['id'])) ? $_REQUEST['id']  : '';
				$class = (isset($_POST['classname'])) ? htmlspecialchars($_POST['classname']) : '';

				$txtcols = (isset($_POST['txtcols'])) ? max(20, intval($_POST['txtcols'])) : 76;
				$txtrows = (isset($_POST['txtrows'])) ? max(5, intval($_POST['txtrows'])) : 10;
				$showcss = (!empty($_POST['showcss'])) ? true : ((!empty($_POST['hidecss'])) ? false : ((!empty($_GET['showcss'])) ? true : false));

				// List of default classes, categorised
				$base_classes = array(
					'text'	=> array(
						'body',  'p',  'h1',  'h2',  'h3',  '.tabletitle',  '.cattitle',  '.topictitle',  '.topicauthor',  '.topicdetails',  '.postdetails',  '.postbody',  '.posthilit', '.postauthor',  '.mainmenu', '.nav', '.genmed',  '.gensmall',  '.copyright',
					),
					'tables'	=> array(
						'table',  'th', '.cat',  '.catdiv',  'td',  '.row1',  '.row2',  '.row3',  '.spacer',  'hr', 
					),
					'forms'		=> array(
						'form',  'input',  'select',  '.textarea',  '.post',  '.btnlite', '.btnmain', '.btnbbcode',
					), 
					'bbcode'	=> array(
						'.b', '.u', '.i', '.color', '.size', '.code', '.quote', 'flash', '.syntaxbg',  '.syntaxcomment', '.syntaxdefault', '.syntaxhtml', '.syntaxkeyword', '.syntaxstring',
					), 
					'custom'	=> array(),
				);

				// We categorise the elements which comprise the css class so that we set 
				// any appropriate additional data, e.g. sizes require the scale type to be set, 
				// images require the relevant image be pulled and selected in the dropdown, etc.
				$match_elements = array(
					'colors'	=> array('background-color', 'color',),
					'sizes'		=> array('font-size', 'line-height',),
					'images'	=> array('background-image',),
					'repeat'	=> array('background-repeat',),
					'other'		=> array('font-weight', 'font-family', 'font-style', 'text-decoration',),
				);

				// Used in an sprintf statement to generate appropriate output for rawcss mode
				$map_elements = array(
					'colors'	=> '%s',
					'sizes'		=> '%d%s',
					'images'	=> 'url(\'./../%s\')',
					'repeat'	=> '%s',
					'other'		=> '%s',
				);


				$s_hidden_fields = '';

				// Do we want to edit an existing theme?
				if ($theme_id)
				{
					$sql = 'SELECT * 
						FROM ' . STYLES_CSS_TABLE . "
						WHERE theme_id = $theme_id";
					$result = $db->sql_query($sql);

					if (!(extract($db->sql_fetchrow($result))))
					{
						trigger_error($user->lang['NO_THEME']);
					}
					$db->sql_freeresult($result);
					

					// Grab template data
					if (!($fp = fopen("{$phpbb_root_path}styles/themes/$theme_path/$theme_name.css", 'rb')))
					{
						die("ERROR");
					}
					$stylesheet = fread($fp, filesize("{$phpbb_root_path}styles/themes/$theme_path/$theme_name.css"));
					fclose($fp);


					// Pull out list of "custom" tags
					if (preg_match_all('#([a-z\.:]+?) {.*?}#si', $stylesheet, $matches))
					{
						$test_ary = array();
						foreach ($base_classes as $category => $class_ary)
						{
							$test_ary = array_merge($test_ary, $class_ary);
						}

						$matches = preg_replace('#^\.#', '', $matches[1]);
						foreach ($matches as $value)
						{
							if (!in_array($value, $test_ary))
							{
								$base_classes['custom'][] = $value;
							}
						}
						unset($matches);
						unset($test_ary);
					}				
				}


				// Do we have a class set? If so, we need to extract and set the relevant data
				if (!empty($class))
				{
					// We must generate the relevant data ... what we need depends on whether
					// we are looking @ the rawcss or the simplified settings and whether we
					// have just selected a class. We must also cope with switching between 
					// simple and rawcss mode
					$css_element = array();
					if (!empty($_POST['rawcss']) && (!empty($_POST['hidecss']) || !empty($_POST['preview']) || !empty($_POST['update'])))
					{
						$css_element = preg_replace("#;[\r\n]*#s", "\n", stripslashes($_POST['rawcss']));
						$css_element = explode("\n", $css_element);
					}
					else if (($showcss && !empty($_POST['showcss'])) || !empty($_POST['preview']) || !empty($_POST['update']))
					{
						if (!empty($_POST['cssother']))
						{
							$css_element = explode('; ', stripslashes($_POST['cssother']));
						}

						foreach ($match_elements as $type => $match_ary)
						{
							foreach ($match_ary as $match)
							{
								$var = str_replace('-', '_', $match);
								if (!empty($_POST[$var]))
								{
									$css_element[] = str_replace('_', '-', $var) . ': ' . (($type == 'sizes') ? sprintf($map_elements[$type], stripslashes($_POST[$var]), $_POST[$var . '_units']) : sprintf($map_elements[$type], stripslashes($_POST[$var])));
								}
							}
						}
					}
					else if (preg_match('#^' . $class . ' {(.*?)}#m', $stylesheet, $matches))
					{
						$css_element = explode('; ', ltrim(substr($matches[1], 0, -2)));
					}


					// User wants to submit data ...
					if (!empty($_POST['update']))
					{
						$updated_element = implode('; ', $css_element) . ';';
						if (preg_match('#^' . $class . ' {(.*?)}#m', $stylesheet))
						{
							$stylesheet = preg_replace('#^(' . $class . ' {).*?(})#m', '\1 ' . $updated_element . ' \2', $stylesheet);
						}
						else
						{
							$stylesheet .= '';
						}

						if (!($fp = fopen($phpbb_root_path . 'styles/themes/' . $css_external, 'wb')))
						{
							die("ERROR");
						}
						$stylesheet = fwrite($fp, $stylesheet);
						fclose($fp);

						$error[] = $user->lang['THEME_UPDATED'];

						add_log('admin', 'LOG_EDIT_THEME', $theme_name);
					}


					// I guess really this needs some basic examples, pulled from subSilver
					// to demonstrate the default classes. Other, custom classes can just use
					// the div/span and some text? This is gonna get nasty :(
					if (!empty($_POST['preview']))
					{
						$output = '<span class="' . str_replace('.', '', $class). '">%s</span>';
						
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="<?php echo $user->lang['LTR']; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $user->lang['ENCODING']; ?>">
<meta http-equiv="Content-Style-Type" content="text/css">
<style type="text/css">
<!--
<?php

						$updated_element = implode('; ', $css_element) . ';';
						if (preg_match('#^' . $class . ' {(.*?)}#m', $stylesheet))
						{
							echo $stylesheet = str_replace("url('./../", "url('./../styles/themes/", preg_replace('#^(' . $class . ' {).*?(})#m', '\1 ' . $updated_element . ' \2', $stylesheet));
						}
						else
						{
							echo str_replace("url('./../", "url('./../styles/themes/", $stylesheet);
						}
?>
//-->
</style>
</head>
<body>

<table width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr align="center" valign="middle">
		<td height="100" width="33%"><h1>h1</h1></td>
		<td height="100" width="33%"><h2>h2</h2></td>
		<td height="100" width="33%"><h3>h3</h3></td>
	</tr>
	<tr align="center">
		<td colspan="3" height="30"><a class="mainmenu" href="">mainmenu</a></td>
	</tr>
	<tr>
		<td colspan="3" height="50">&nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr>
		<td align="left" valign="bottom"><a class="titles" href="">titles</a>
	</tr>
</table>

<table width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="nav" width="10" align="left" valign="middle"><a href="">navlink</a></td>
	</tr>
</table>

<table class="tablebg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="3">th</th>
	</tr>
	<tr>
		<td class="cat" width="40%"><span class="cattitle">cattitle / cat</span></td>
		<td class="catdiv" colspan="2">catdiv</td>
	</tr>
	<tr>
		<td class="row1" width="40%"><a class="topictitle" href="">topictitle / row1</a></td>
		<td class="row2"><span class="topicauthor">topicauthor / row2</span></td>
		<td class="row1"><span class="topicdetails">topicdetails / row1</span></td>
	</tr>
	<tr>
		<td class="row3" colspan="3">row3</td>
	</tr>
	<tr>
		<td class="spacer" colspan="3">spacer</td>
	</tr>
	<tr>
		<td class="row1"><span class="postauthor">postauthor / row1</span></td>
		<td class="row2"><span class="postdetails">postdetails / row2</span></td>
		<td class="row1"><span class="postbody">postbody / row1 <span class="posthilit">posthilit</span></span></td>
	</tr>
</table>

<br /><hr width="95%" />

<table width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr align="center">
		<td><span class="gen">gen</span></td>
		<td><span class="genmed">genmed</span></td>
		<td><span class="gensmall">gensmall</span></td>
	</tr>
	<tr align="center">
		<td colspan="3"><span class="copyright">copyright <a href="">phpBB</a></span></td>
	</tr>
</table>

<hr width="95%" /><br />

<form><table width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr align="center">
		<td><input class="btnmain" type="submit" value="input / btnmain" /></td>
		<td><input class="btnlite" type="submit" value="input / btnlite" /></td>
		<td><input class="btnbbcode" type="submit" value="input / btnbbcode" /></td>
	</tr>
	<tr align="center">
		<td colspan="3"><input class="post" type="text" value="input / post" /></td>
	</tr>
	<tr align="center">
		<td colspan="3"><select class="post"><option>select</option></select></td>
	</tr>
	<tr align="center">
		<td colspan="3"><textarea class="post">textarea / post</textarea></td>
	</tr>
</table></form>

<hr width="95%" /><br />

<table class="tablebg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="row2" align="center"><span class="postbody">postbody / <b>bold</b> <i>italic</i> <u>underline</u></span></td>
	</tr>
	<tr>
		<td class="row2"><table width="90%" cellspacing="1" cellpadding="3" border="0" align="center">
			<tr>
				<td class="quote"><b>A_N_Other wrote:</b><hr />quote</td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="row2"><table width="90%" cellspacing="1" cellpadding="3" border="0" align="center">
			<tr> 
				<td><b class="genmed">Code:</b></td>
			</tr>
			<tr>
				<td class="code">10 Print "hello"<br />20 Goto 10</td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="row2"><table width="90%" cellspacing="1" cellpadding="3" border="0" align="center">
			<tr> 
				<td><b class="genmed">PHP:</b></td>
			</tr>
			<tr>
				<td class="code"><span class="syntaxbg"><span class="syntaxcomment">// syntaxcomment</span><br /><span class="syntaxdefault">?&gt;</span><br />&lt;<span class="syntaxhtml">HTML</span>&gt;<br /><span class="syntaxdefault">&lt;?php</span><br /><span class="syntaxkeyword">echo </span> <span class="syntaxdefault">$this = </span><span class="syntaxstring">"HELLO"</span><span class="syntaxdefault">;</span></span></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

</body>
</html>
<?php
	
						exit;


					}


					// Here we pull out the appropriate class entry then proceed to pull it apart,
					// setting appropriate variables to their respective values. We only match
					// certain css elements, the rest are "hidden" and can be accessed by exposing
					// the raw css
					if (!$showcss)
					{
						foreach ($match_elements as $type => $match_ary)
						{
							foreach ($match_ary as $match)
							{
								$var = str_replace('-', '_', $match);
								$$var = '';

								if (sizeof($css_element))
								{
									foreach ($css_element as $key => $element)
									{
										if (preg_match('#^' . preg_quote($match, '#') . ': (.*?)$#', $element, $matches))
										{
											switch ($type)
											{
												case 'sizes':
													if (preg_match('#(.*?)(px|%|em|pt)#', $matches[1], $matches))
													{
														${$var . '_units'} = trim($matches[2]);
													}
													$$var = trim($matches[1]);
													break;

												case 'images':
													if (preg_match('#url\(\'(.*?)\'\)#', $matches[1], $matches))
													{
														$$var = trim($matches[1]);
														$$var = str_replace('./', $theme_name . '/', $$var);
													}
													break;

												default:
													$$var = trim($matches[1]);
											}

											// Remove this element from array
											unset($css_element[$key]);
											break;
										}
									}
								}
							}
						}

						// Any remaining elements must be custom data so we save that
						// in a hidden field
						if (sizeof($css_element))
						{
							$s_hidden_fields .= '<input type="hidden" name="cssother" value="' . addslashes(implode('; ', $css_element)) . '" />';
						}
					}
				}
				// End of class element variable setting

				// Generate list of class options
				$class_options = '';
				foreach ($base_classes as $category => $class_ary)
				{
					$class_options .= '<option class="sep">' . $user->lang['style_cat_' . $category] . '</option>';
					foreach ($class_ary as $class_name)
					{
						$selected = ($class_name == $class) ? ' selected="selected"' : '';
						$class_options .= '<option value="' . $class_name . '"' . $selected . '>' . (($category == 'custom') ? $class_name : $user->lang['style_' . str_replace('.', '', $class_name)]) . '</option>';
					}
				}


				// Grab list of potential images for class backgrounds
				$imglist = filelist($phpbb_root_path . 'styles/themes');

				$bg_imglist = '';
				foreach ($imglist as $img)
				{
					$img = substr($img['path'], 1) . (($img['path'] != '') ? '/' : '') . $img['file']; 

					$selected = (preg_match('#' . preg_quote($img) . '$#', $background_image)) ? ' selected="selected"' : '';
					$bg_imglist .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
				}
				$bg_imglist = '<option value=""' . (($edit_img == '') ? ' selected="selected"' : '') . '>' . $user->lang['NONE'] . '</option>' . $bg_imglist;
				unset($imglist);


				// Output the page
				adm_page_header($user->lang['EDIT_THEME']);

?>

<script language="javascript" type="text/javascript">
<!--

function swatch(field)
{
	window.open('./swatch.<?php echo "$phpEx?form=style&name="; ?>' + field, '_swatch', 'HEIGHT=115,resizable=yes,scrollbars=no,WIDTH=636');
	return false;
}

function csspreview()
{
	if (document.myvar == 'preview')
	{
		window.open('', '_preview', 'HEIGHT=400,resizable=yes,scrollbars=yes,WIDTH=500');
		document.forms['style'].target =  '_preview';
	}
	else
	{
		document.forms['style'].target =  '_self';
	}
	document.myvar='';

	return true;
}

//-->
</script>

<h1><?php echo $user->lang['EDIT_THEME']; ?></h1>

<p><?php echo $user->lang['EDIT_THEME_EXPLAIN']; ?></p>

<?php 

				if ($showcss)
				{

?>

<h3>Note</h3>

<p><?php echo $user->lang['SHOW_RAW_CSS_EXPLAIN']; ?></p>
<?php

				}

?>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$theme_id&amp;showcss=$showcss"; ?>" onsubmit="return csspreview()"><table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<!-- tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th>Parameter</th>
				<th>Value</th>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Theme name:</b></td>
				<td class="row2"><input class="post" type="text" name="theme_name" value="<?php echo $theme_name; ?>" maxlength="30" size="25" /></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Copyright:</b></td>
				<td class="row2"><input class="post" type="text" name="theme_copyright" value="<?php echo $theme_copyright; ?>" maxlength="30" size="25" /></td>
			</tr>
		</table>
		
		<br clear="all" /><br /></td>
	</tr -->
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_CLASS']; ?>: <select name="classname" onchange="if (this.options[this.selectedIndex].value != ''){ csspreview(); this.form.submit(); }"><?php echo $class_options; ?></select>&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

				if ($showcss)
				{

?>
			<!-- tr>
				<td class="cat" colspan="2">Columns: <input class="post" type="text" name="txtcols" size="3" maxlength="3" value="<?php echo $txtcols; ?>" /> &nbsp;Rows: <input class="post" type="text" name="txtrows" size="3" maxlength="3" value="<?php echo $txtrows; ?>" />&nbsp; <input class="btnlite" type="submit" value="Update" /></td>
			</tr -->
			<tr>
				<th colspan="2">Raw CSS</th>
			</tr>
<?php

				if (sizeof($error) && !empty($_POST['update']))
				{
					echo '<tr><td class="row3" colspan="2" align="center"><span class="gen" style="color:green" align="center">' . implode('<br />', $error) . '</span></td></tr>';
				}

?>
			<tr>
				<td class="row2" colspan="2" align="center"><textarea class="post" style="font-family:'Courier New', monospace;font-size:10pt;line-height:125%;" name="rawcss" rows="<?php echo $txtrows; ?>" cols="<?php echo $txtcols; ?>"><?php echo (sizeof($css_element)) ? implode(";\n", $css_element) . ';' : ''; ?></textarea></td>
			</tr>

<?php

				}
				else
				{

?>
			<tr>
				<th>Parameter</th>
				<th>Value</th>
			</tr>
<?php

				if (sizeof($error) && !empty($_POST['update']))
				{
					echo '<tr><td class="row3" colspan="2" align="center"><span class="gen" style="color:green" align="center">' . implode('<br />', $error) . '</span></td></tr>';
				}

?>
			<tr>
				<td class="cat" colspan="2"><b>Background</b></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Color:</b> <br /><span class="gensmall">This is a hex-triplet of the form RRGGBB<br /><a href="swatch.php" onclick="swatch('background_color');return false" target="_swatch">Web-safe Colour Swatch</a></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="0" border="0"><tr><td><input class="post" type="text" name="background_color" value="<?php echo $background_color; ?>" size="8" maxlength="14"  onchange="document.all.stylebgcolor.bgColor=this.form.background_color.value" /></td><td>&nbsp;</td><td bgcolor="<?php echo $background_color; ?>" id="stylebgcolor" style="border:solid 1px black;"><img src="../images/spacer.gif" width="45" height="15" alt="" /></td></tr></table></td>
			</tr>
			<tr>
				<td class="row1"><b>Image:</b></td>
				<td class="row2"><select name="background_image"><?php echo $bg_imglist ?></select></td>
			</tr>
			<tr>
				<td class="row1"><b>Repeat background:</b></td>
				<td class="row2"><select name="background_repeat"><?php

					foreach (array('' => '------', 'none' => 'No', 'repeat-x' => 'Horizontally Only', 'repeat-y' => 'Vertically Only', 'both' => 'Both Directions') as $cssvalue => $cssrepeat)
					{
						echo '<option value="' . $cssvalue . '"' . (($background_repeat == $cssvalue) ? ' selected="selected"' : '') . '>' . $cssrepeat . '</option>';
					}
	
?></select></td>
			</tr>


			<tr>
				<td class="cat" colspan="2"><b>Foreground</b></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Color:</b> <br /><span class="gensmall">This is a hex-triplet of the form RRGGBB<br /><a href="swatch.php" onclick="swatch('color');return false" target="_swatch">Web-safe Colour Swatch</a></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="0" border="0"><tr><td><input class="post" type="text" name="color" value="<?php echo $color; ?>" size="8" maxlength="14" onchange="document.all.stylecolor.bgColor=this.form.color.value" /></td><td>&nbsp;</td><td bgcolor="<?php echo $color; ?>" id="stylecolor" style="border:solid 1px black;"><img src="../images/spacer.gif" width="45" height="15" alt="" /></td></tr></table></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Font:</b> <br /><span class="gensmall">You can specify multiple fonts seperated by commas</span></td>
				<td class="row2"><input class="post" type="text" name="font_family" value="<?php echo $font_family; ?>" size="40" maxlength="255" /></td>
			</tr>
			<tr>
				<td class="row1"><b>Size:</b></td>
				<td class="row2"><input class="post" type="text" name="font_size" value="<?php echo $font_size; ?>" size="3" maxlength="3" /> <select name="font_size_units"><?php

					foreach (array('pt', 'px', 'em', '%') as $units)
					{
						echo '<option value="' . $units . '"' . (($font_size_units == $units) ? ' selected="selected"' : '') . '>' . $units . '</option>';
					}
	
?></select></td>
			</tr>
			<tr>
				<td class="row1"><b>Bold:</b></td>
				<td class="row2"><input type="radio" name="font_weight" value="bold"<?php echo (!empty($font_weight) && $font_weight == 'bold') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="font_weight" value="normal"<?php echo (!empty($font_weight) && $font_weight == 'normal') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['NO']; ?> &nbsp; <input type="radio" name="font_weight" value=""<?php echo (empty($font_weight)) ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['UNSET']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b>Italic:</b></td>
				<td class="row2"><input type="radio" name="font_style" value="italic"<?php echo (!empty($font_style) && $font_style == 'italic') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="font_style" value="normal"<?php echo (!empty($font_style) && $font_style == 'normal') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['NO']; ?> &nbsp; <input type="radio" name="font_style" value=""<?php echo (empty($font_style)) ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['UNSET']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b>Underline:</b></td>
				<td class="row2"><input type="radio" name="text_decoration" value="underline"<?php echo (!empty($text_decoration) && $text_decoration == 'underline') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="text_decoration" value="none"<?php echo (!empty($text_decoration) && $text_decoration == 'none') ? ' checked="checked"' : ''; ?>/> <?php echo $user->lang['NO']; ?> &nbsp; <input type="radio" name="text_decoration" value=""<?php echo (empty($text_decoration)) ? ' checked="checked"' : ''; ?>/> <?php echo $user->lang['UNSET']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b>Line spacing:</b></td>
				<td class="row2"><input class="post" type="text" name="line_height" value="<?php echo $line_height; ?>" size="3" maxlength="3" /> <select name="line_height_units"><?php

					foreach (array('pt', 'px', 'em', '%') as $units)
					{
						echo '<option value="' . $units . '"' . (($line_height_units == $units) ? ' selected="selected"' : '') . '>' . $units . '</option>';
					}
	
?></select></td>
			</tr>
<?php

				}

?>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>"; />&nbsp;&nbsp;<input class="btnlite" type="submit" name="preview" value="<?php echo $user->lang['PREVIEW']; ?>" onclick="document.myvar='preview';" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" />&nbsp;&nbsp;<?php
									
				if ($showcss)
				{

?><input class="btnlite" type="submit" name="hidecss" value="<?php echo $user->lang['HIDE_RAW_CSS']; ?>" /><?php

				}
				else
				{

?><input class="btnlite" type="submit" name="showcss" value="<?php echo $user->lang['SHOW_RAW_CSS']; ?>" /><?php

				}

?><?php echo $s_hidden_fields; ?></td>
			</tr>
		</table></td>
	</tr>
</table>

<h1>Custom Class</h1>

<p>You can add additional classes to this theme if you wish. You must provide the actual CSS class name below, it must be the same as that you have or will use in your template. Please remember that class names may contain only alphanumeric characters, periods (.), colons (:) and number/hash/pound (#). The new class will be added to the Custom Class category in the select box above.</p>

<table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2">Add Custom Class</td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>CSS class name:</b></td>
				<td class="row2"><input class="post" type="text" name="customclass" value="" maxlength="15" size="15" /></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="addclass" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
		</table>
	
		</td>
	</tr>
</table></form>
<?php

				adm_page_footer();
				break;

			case 'delete':
				break;

			case 'export':
				break;
		}


		// Output list of themes
		adm_page_header($user->lang['THEMES']);

?>
<h1><?php echo $user->lang['THEMES']; ?></h1>

<p><?php echo $user->lang['THEMES_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Theme name</th>
		<th colspan="3">&nbsp;</th>
	</tr>
<?php

		$sql = 'SELECT theme_id, theme_name
			FROM ' . STYLES_CSS_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%"><?php 
			
//				echo (is_writeable($phpbb_root_path . 'styles/themes/' . $row['css_external'])) ? sprintf('%s%s%s', "<a href=\"admin_styles.$phpEx$SID&amp;mode=themes&amp;action=edit&amp;id=" . $row['theme_id'] . '">', $row['theme_name'], '</a>') : $row['theme_name'];

				echo sprintf('%s%s%s', "<a href=\"admin_styles.$phpEx$SID&amp;mode=themes&amp;action=edit&amp;id=" . $row['theme_id'] . '">', $row['theme_name'], '</a>');

?></td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=delete&amp;id=" . $row['theme_id']; ?>">Delete</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=export&amp;id=" . $row['theme_id']; ?>">Export</a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=preview&amp;id=" . $row['theme_id']; ?>">Preview</a>&nbsp;</td>
	</tr>
<?php

			}
			while ($row = $db->sql_fetchrow($result));
		}
		else
		{
		}
		$db->sql_freeresult($result);


?>
	<tr>
		<td class="cat" colspan="4" align="right">Create new theme: <input class="post" type="text" name="theme_name" value="" maxlength="30" size="25" /> <input class="btnmain" type="submit" name="newtheme" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table></form>

<?php

		adm_page_footer();

		break;






}


// ---------
// FUNCTIONS
//
class template_admin extends template
{
	function compile_cache_clear($template = false)
	{
		global $phpbb_root_path;

		$template_list = array();

		if (!$template)
		{
			$dp = opendir($phpbb_root_path . $this->cache_root);
			while ($dir = readdir($dp)) 
			{
				$template_dir = $phpbb_root_path . $this->cache_root . $dir;
				if (!is_file($template_dir) && !is_link($template_dir) && $dir != '.' && $dir != '..')
				{
					array_push($template_list, $dir);
				}
			}
			closedir($dp);
		}
		else
		{
			array_push($template_list, $template);
		}

		foreach ($template_list as $template)
		{
			$dp = opendir($phpbb_root_path . $this->cache_root . $template);
			while ($file = readdir($dp))
			{
				unlink($phpbb_root_path . $this->cache_root . $file);
			}
			closedir($dp);
		}

		return;
	}

	function compile_cache_show($template)
	{
		global $phpbb_root_path;

		$template_cache = array();

		$template_dir = $phpbb_root_path . $this->cache_root . $template;
		$dp = opendir($template_dir);
		while ($file = readdir($dp))
		{
			if (preg_match('#\.html$#i', $file) && is_file($template_dir . '/' . $file))
			{
				array_push($template_cache, $file);
			}
		}
		closedir($dp);

		return;
	}
}
//
// FUNCTIONS
// ---------

?>