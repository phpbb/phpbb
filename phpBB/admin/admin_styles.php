<?php

if ( !empty($setmodules) )
{
	if ( !$auth->get_acl_admin('styles') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Styles']['Edit_Style'] = $filename . "$SID&amp;mode=newstyle";
	$module['Styles']['Edit_Template'] = $filename . "$SID&amp;mode=edittemplate";
	$module['Styles']['Edit_Theme'] = $filename . "$SID&amp;mode=edittheme";
	$module['Styles']['Edit_Imageset'] = $filename . "$SID&amp;mode=editimageset";

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have styles admin permissions?
//
if ( !$auth->get_acl_admin('styles') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

/*
$dp = opendir($phpbb_root_path . 'templates/cache/');
while ( $file = readdir($dp) )
{
	if ( !is_file($phpbb_root_path . 'templates/cache/' . $file) && !is_link($phpbb_root_path . 'templates/cache/' . $file) && $file != '.' && $file != '..' )
	{
		$selected = ( $tplroot == $file ) ? ' selected="selected"' : '';
		$tplroot_options .= '<option name="' . $file . '"' . $selected . '>' . $file . '</option>';
	}
}
closedir($dp);
*/

//
//
//
$mode = ( isset($HTTP_GET_VARS['mode']) ) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];

switch ( $mode )
{
	case 'editimageset':
		$imgroot = ( isset($HTTP_POST_VARS['imgroot']) ) ? $HTTP_POST_VARS['imgroot']  : 'subSilver';

		if ( isset($HTTP_POST_VARS['img_root']) )
		{
			$sql = "SELECT *
				FROM " . STYLES_IMAGE_TABLE . "
				WHERE imageset_path LIKE '" . $HTTP_POST_VARS['imgroot'] . "'";
			$result = $db->sql_query($sql);

			$images = $db->sql_fetchrow($result);
		}
		if ( isset($HTTP_POST_VARS['img_addconfig']) )
		{
		}
		else if ( isset($HTTP_POST_VARS['img_addlocal']) )
		{
		}

		$imageset = array('imageset_path', 'post_new', 'post_locked', 'post_pm', 'reply_new', 'reply_pm', 'reply_locked', 'icon_profile', 'icon_pm', 'icon_delete', 'icon_ip', 'icon_quote', 'icon_search', 'icon_edit', 'icon_email', 'icon_www', 'icon_icq', 'icon_aim', 'icon_yim', 'icon_msnm', 'icon_no_email', 'icon_no_www', 'icon_no_icq', 'icon_no_aim', 'icon_no_yim', 'icon_no_msnm', 'goto_post', 'goto_post_new', 'goto_post_latest', 'goto_post_newest', 'forum', 'forum_new', 'forum_locked', 'folder', 'folder_new', 'folder_hot', 'folder_hot_new', 'folder_locked', 'folder_locked_new', 'folder_sticky', 'folder_sticky_new', 'folder_announce', 'folder_announce_new', 'topic_watch', 'topic_unwatch', 'poll_left', 'poll_center', 'poll_right', 'rating');

		$sql = "SELECT imageset_name, imageset_path
			FROM " . STYLES_IMAGE_TABLE . "
			ORDER BY imageset_name";
		$result = $db->sql_query($sql);

		$imgroot_options = '';
		while ( $row = $db->sql_fetchrow($result) )
		{
			$selected = ( $imgroot == $row['imageset_path'] ) ? ' selected="selected"' : '';
			$imgroot_options .= '<option name="' . $row['imageset_path'] . '"' . $selected . '>' . $row['imageset_path'] . '</option>';
		}

		$imgname_options = '';
		$dp = opendir($phpbb_root_path . 'imagesets/' . $imgroot . '/');
		while ( $file = readdir($dp) )
		{
			if ( preg_match('#\.(gif|png|jpg|jpeg)$#', $file) && is_file($phpbb_root_path . 'imagesets/' . $imgroot . '/' . $file) )
			{
				$selected = ( $imgname == $file ) ? ' selected="selected"' : '';
				$imgname_options .= '<option value="' . $file . '"' . $selected . '>' . $file . '</option>';
			}
		}
		closedir($dp);

		//
		// Output page
		//
		page_header($lang['Styles']);

		echo '<form method="post" action="admin_styles.' . $phpEx . '?mode=editimageset">';

		echo '<h2>Edit Imageset</h2>';

		echo '<p>Template set: <select name="imgroot">' . $imgroot_options . '</select>&nbsp; <input class="liteoption" type="submit" name="img_root" value="Select" /></p>';

		echo '<p>Use this panel to edit or remove imagesets from the database.</p>';

		echo '<table cellspacing="1" cellpadding="2" border="0" align="center" bgcolor="#98AAB1">';
		echo '<tr>';
		echo '<td class="cat" colspan="6" height="28" align="center"><span class="gen">Available images: <select name="imageset">' . $imgname_options . '</select></span></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th height="25">Image</th><th>Source</th><th>Width</th><th>Height</th><th>Border</th><th>&nbsp;</th>';
		echo '</tr>';

		for($i = 0; $i < count($imageset); $i++)
		{
			$class = ( !($i%2) ) ? 'row1' : 'row2';

			echo '<tr>';
			echo '<td class="' . $class . '" height="25"><span class="gen">' . ucfirst(str_replace('_', ' ', $imageset[$i])) . '</span></td>';
			echo '<td class="' . $class . '"><input class="text" type="text" name="src[' . $imageset[$i] . ']" value="' . ( ( !empty($images[$imageset[$i]]) ) ? $images[$imageset[$i]] : '' ) . '" size="20" maxsize="30" /></td>';
			echo '<td class="' . $class . '"><input class="text" type="text" name="width[' . $imageset[$i] . ']" size="3" maxsize="3" /></td>';
			echo '<td class="' . $class . '"><input class="text" type="text" name="height[' . $imageset[$i] . ']" size="3" maxsize="3" /></td>';
			echo '<td class="' . $class . '"><input class="text" type="text" name="border[' . $imageset[$i] . ']" size="2" maxsize="2" /></td>';
			echo '<td class="' . $class . '"><input class="liteoption" type="submit" value="Update" onclick="this.form.' . $imageset[$i] . '.value=this.form.imageset.options[this.form.imageset.selectedIndex].value;return false" />&nbsp;<input class="liteoption" type="submit" value="Clear" onclick="this.form.' . $imageset[$i] . '.value=\'\';return false" />&nbsp;</td>';
			echo '</tr>';
		}

		echo '<td class="cat" colspan="6" height="28" align="center"><input class="liteoption" type="submit" name="img_update" value="Update set" /> &nbsp; <input class="liteoption" type="submit" name="img_delete" value="Delete set" /> &nbsp; <input class="liteoption" type="reset" value="Undo" /></td>';
		echo '</tr>';
		echo '</table>';
		echo '</form>';
		page_footer();

		break;

	case 'edittemplate':

		$tplcols = ( isset($HTTP_POST_VARS['tplcols']) ) ? max(60, intval($HTTP_POST_VARS['tplcols'])) : 90;
		$tplrows = ( isset($HTTP_POST_VARS['tplrows']) ) ? max(4, intval($HTTP_POST_VARS['tplrows'])) : 30;
		$tplname = ( isset($HTTP_POST_VARS['tplname']) ) ? $HTTP_POST_VARS['tplname']  : '';
		$tplroot = ( isset($HTTP_POST_VARS['tplroot']) ) ? $HTTP_POST_VARS['tplroot']  : 'subSilver';

		$str = '';
		if ( isset($HTTP_POST_VARS['tpl_compile']) && !empty($HTTP_POST_VARS['decompile']) )
		{
			$str = "<?php\n" . $template->compile(stripslashes($HTTP_POST_VARS['decompile'])) . "\n?".">";

			$fp = fopen($phpbb_root_path . 'templates/cache/' . $tplroot . '/' . $tplname . '.html.' . $phpEx, 'w+');
			fwrite ($fp, $str);
			fclose($fp);

			@chmod($phpbb_root_path . 'templates/cache/' . $tplroot . '/' . $tplname . '.html.' . $phpEx, 0644);

			add_admin_log('log_template_edit', $tplname, $tplroot);

			exit;
		}
		else if ( !empty($tplname) && isset($HTTP_POST_VARS['tpl_name']) )
		{
			$fp = fopen($phpbb_root_path . 'templates/cache/' . $tplroot . '/' . $tplname . '.html.' . $phpEx, 'r');
			while ( !feof($fp) )
			{
				$str .= fread($fp, 4096);
			}
			@fclose($fp);

			$template->decompile($str);
		}
		else
		{
			$str = ( !empty($HTTP_POST_VARS['decompile']) ) ? stripslashes($HTTP_POST_VARS['decompile']) : '';
		}

		if ( isset($HTTP_POST_VARS['tpl_download']) )
		{
			header("Content-Type: text/html; name=\"" . $tplname . ".html\"");
			header("Content-disposition: attachment; filename=" . $tplname . ".html");
			echo $str;
			exit;

		}

		$tplroot_options = get_templates($tplroot);

		$tplname_options = '';
		$dp = @opendir($phpbb_root_path . 'templates/cache/' . $tplroot . '/');
		while ( $file = readdir($dp) )
		{
			if ( strstr($file, '.html.' . $phpEx) && is_file($phpbb_root_path . 'templates/cache/' . $tplroot . '/' . $file) )
			{
				$tpl = substr($file, 0, strpos($file, '.'));
				$selected = ( $tplname == $tpl ) ? ' selected="selected"' : '';
				$tplname_options .= '<option value="' . $tpl . '"' . $selected . '>' . $tpl . '</option>';
			}
		}
		closedir($dp);

		//
		//
		//
		page_header($lang['Edit_template']);

?>

<h2><?php echo $lang['Edit_template']; ?></h2>

<p><?php echo $lang['Edit_template_explain']; ?></p>

<form method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=edittemplate"; ?>">

<p><?php echo $lang['Select_template']; ?>: <select name="tplroot"><?php echo $tplroot_options; ?></select>&nbsp; <input class="liteoption" type="submit" name="tpl_root" value="Select" /></p>

<table class="bg" width="95%" cellspacing="1" cellpadding="0" border="0" align="center">
	<tr>
		<td class="cat"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td>&nbsp;Template: <select name="tplname"><?php echo $tplname_options; ?></select>&nbsp; <input class="liteoption" type="submit" name="tpl_name" value="Select" /></td>

				<td align="right">Columns: <input type="text" name="tplcols" size="3" maxlength="3" value="<?php echo $tplcols; ?>" /> &nbsp;Rows: <input type="text" name="tplrows" size="3" maxlength="3" value="<?php echo $tplrows; ?>" />&nbsp; <input class="liteoption" type="submit" name="tpl_layout" value="Update" />&nbsp;</td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><textarea class="edit" style="background-color:#DEE3E7" cols="<?php echo $tplcols; ?>" rows="<?php echo $tplrows; ?>" name="decompile"><?php echo htmlentities($str); ?></textarea></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" height="28" align="center"><input class="liteoption" type="submit" name="tpl_compile" value="Recompile" /> &nbsp; <input class="liteoption" type="submit" name="tpl_download" value="Download" /> &nbsp; <input class="liteoption" type="reset" value="Undo" /></td>
	</tr>
</table></form>

<?php

		page_footer();
		break;

	case 'edittheme':

		$theme_id = ( isset($HTTP_POST_VARS['themeroot']) ) ? $HTTP_POST_VARS['themeroot']  : '';

		if ( isset($HTTP_POST_VARS['update']) )
		{
			$sql = "SELECT theme_id, theme_name
				FROM " . STYLES_CSS_TABLE . "
				WHERE theme_id = $theme_id";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result) )
			{
				$theme_name = $row['theme_name'];

				$css_data = ( !empty($HTTP_POST_VARS['css_data']) ) ? htmlentities($HTTP_POST_VARS['css_data']) : '';
				$css_external = ( !empty($HTTP_POST_VARS['css_data']) ) ? $HTTP_POST_VARS['css_data'] : '';

				$sql = "UPDATE " > STYLES_CSS_TABLE . "
					SET css_data = '$css_data', css_external = '$css_external'
					WHERE theme_id = $theme_id";
				$db->sql_query($sql);

				add_admin_log('log_theme_edit', $theme_name);

				message_die(MESSAGE, $lang['Success_theme_update']);
			}
		}

		page_header($lang['Edit_theme']);

		$sql = "SELECT theme_id, theme_name
			FROM " . STYLES_CSS_TABLE;
		$result = $db->sql_query($sql);

		$theme_options = '';
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$theme_options .= ( ( $theme_options != '' ) ? ', ' : '' ) . '<option value="' . $row['theme_id'] . '">' . $row['theme_name'] . '</option>';
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		$css_data = '';
		$css_external = '';
		if ( $theme_id )
		{
			$sql = "SELECT css_data, css_external
				FROM " . STYLES_CSS_TABLE . "
				WHERE theme_id = $theme_id";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result) )
			{
				$css_data = preg_replace('/\t{1,}/i', ' ', $row['css_data']);
				$css_external = $row['css_external'];
			}
		}

?>

<form method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>">

<h2><?php echo $lang['Edit_theme']; ?></h2>

<p><?php echo $lang['Edit_theme_explain']; ?></p>

<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $lang['Select_theme']; ?>: <select name="themeroot"><?php echo $theme_options; ?></select>&nbsp; <input class="liteoption" type="submit" name="tpl_root" value="<?php echo $lang['Select']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['CSS_data']; ?>: <br /><span class="gensmall"><?php echo $lang['CSS_data_explain']; ?></td>
		<td class="row2"><textarea class="edit" cols="65" rows="15" name="css_data"><?php echo htmlentities($css_data); ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['CSS_sheet']; ?>: </td>
		<td class="row2"><input type="text" name="css_external" maxlength="60" size="60" value="<?php echo $css_external; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="liteoption" type="submit" name="update" value="<?php echo $lang['Update']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="reset" value="<?php echo $lang['Reset']; ?>" /></td>
	</tr>
</table></form>

<?php

		page_footer();

		break;
}


//
//
//
function get_templates($tplroot = '')
{
	global $db;

	$sql = "SELECT template_name, template_path
		FROM " . STYLES_TPL_TABLE . "
		ORDER BY template_name";
	$result = $db->sql_query($sql);

	$tplroot_options = '';
	while ( $row = $db->sql_fetchrow($result) )
	{
		$selected = ( $tplroot == $row['template_path'] ) ? ' selected="selected"' : '';
		$tplroot_options .= '<option value="' . $row['template_path'] . '"' . $selected . '>' . $row['template_path'] . '</option>';
	}

	return $tplroot_options;
}

?>