<?php

if ( !empty($setmodules) )
{
	if ( !$auth->acl_get('a_styles') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['STYLE']['EDIT_STYLE'] = $filename . "$SID&amp;mode=newstyle";
	$module['STYLE']['EDIT_TEMPLATE'] = $filename . "$SID&amp;mode=edittemplate";
	$module['STYLE']['EDIT_THEME'] = $filename . "$SID&amp;mode=edittheme";
	$module['STYLE']['EDIT_IMAGESET'] = $filename . "$SID&amp;mode=editimageset";

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

//
$mode = (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';




switch ($mode)
{
	case 'editimageset':
		$imgroot = (isset($_POST['imgroot'])) ? $_POST['imgroot']  : $config['default_style'];

		if (isset($_POST['img_addconfig']))
		{
		}
		else if (isset($_POST['img_addlocal']))
		{
		}

		$imageset = array('imageset_path', 'post_new', 'post_locked', 'post_pm', 'reply_new', 'reply_pm', 'reply_locked', 'icon_profile', 'icon_pm', 'icon_delete', 'icon_ip', 'icon_quote', 'icon_search', 'icon_edit', 'icon_email', 'icon_www', 'icon_icq', 'icon_aim', 'icon_yim', 'icon_msnm', 'icon_no_email', 'icon_no_www', 'icon_no_icq', 'icon_no_aim', 'icon_no_yim', 'icon_no_msnm', 'goto_post', 'goto_post_new', 'goto_post_latest', 'goto_post_newest', 'forum', 'forum_new', 'forum_locked', 'sub_forum', 'sub_forum_new', 'folder', 'folder_new', 'folder_hot', 'folder_hot_new', 'folder_locked', 'folder_locked_new', 'folder_sticky', 'folder_sticky_new', 'folder_announce', 'folder_announce_new', 'topic_watch', 'topic_unwatch', 'poll_left', 'poll_center', 'poll_right', 'rating');

		$sql = 'SELECT imageset_name, imageset_path
			FROM ' . STYLES_IMAGE_TABLE . '
			ORDER BY imageset_name';
		$result = $db->sql_query($sql);

		$imgroot_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($imgroot == $row['imageset_path']) ? ' selected="selected"' : '';
			$imgroot_options .= '<option name="' . $row['imageset_path'] . '"' . $selected . '>' . $row['imageset_path'] . '</option>';
		}

		$imgname_options = '';
		$dp = opendir($phpbb_root_path . 'imagesets/' . $imgroot . '/');
		while ($file = readdir($dp))
		{
			if (preg_match('#\.(gif|png|jpg|jpeg)$#', $file) && is_file($phpbb_root_path . 'imagesets/' . $imgroot . '/' . $file))
			{
				$selected = ($imgname == $file) ? ' selected="selected"' : '';
				$imgname_options .= '<option value="' . $file . '"' . $selected . '>' . $file . '</option>';
			}
		}
		closedir($dp);

		// Output page
		adm_page_header($user->lang['Edit_Imageset']);

?>

<form method="post" action="admin_styles.<?php echo $phpEx . $SID; ?>&amp;mode=editimageset">

<h2>Edit Imageset</h2>

<p>Template set: <select name="imgroot"><?php echo $imgroot_options; ?></select>&nbsp; <input class="liteoption" type="submit" name="img_root" value="Select set" /> &nbsp; <input class="liteoption" type="submit" name="create" value="Create new set" /></p>

<p>Here you can create, edit, delete and download imagesets.</p>

<?php

	if (isset($_POST['img_root']))
	{
		$sql = "SELECT *
			FROM " . STYLES_IMAGE_TABLE . "
			WHERE imageset_path LIKE '" . $_POST['imgroot'] . "'";
		$result = $db->sql_query($sql);

		$images = $db->sql_fetchrow($result);

?>
<table class="bg" cellspacing="1" cellpadding="2" border="0" align="center">
	<tr>
		<th height="25">Image</th><th>Graphic</th><th>&nbsp;</th>
	</tr>
<?php

			for($i = 1; $i < count($imageset); $i++)
			{
				$row_class = (!($i%2)) ? 'row1' : 'row2';

				$img = (!empty($images[$imageset[$i]])) ? '<img src=' . $images[$imageset[$i]] . ' />' : '';
				$img = str_replace('"imagesets/', '"../imagesets/', $img);
				$img = str_replace('{LANG}', $user->img_lang, $img);
				$img = str_replace('{RATE}', 3, $img);
?>
	<tr>
		<td class="<?php echo $row_class; ?>" height="25"><span class="gen"><?php echo ucfirst(str_replace('_', ' ', $imageset[$i])); ?></span></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $img; ?></td>
		<td class="<?php echo $row_class; ?>" align="center">&nbsp;<input class="liteoption" type="submit" value="Edit" /></td>
	</tr>
<?php

			}

?>
	<tr>
		<td class="cat" colspan="3" height="28" align="center"> <input class="liteoption" type="submit" name="download" value="Download set" &nbsp; <input class="liteoption" type="submit" name="img_delete" value="Delete set" /> </td>
	</tr>
</table></form>

<?php

	}

		adm_page_footer();

		break;

	case 'edittemplate':

		$tplcols = (isset($_POST['tplcols'])) ? max(60, intval($_POST['tplcols'])) : 90;
		$tplrows = (isset($_POST['tplrows'])) ? max(4, intval($_POST['tplrows'])) : 30;
		$tplname = (isset($_POST['tplname'])) ? $_POST['tplname']  : '';
		$tplroot = (isset($_POST['tplroot'])) ? $_POST['tplroot']  : 'subSilver';

		$str = '';
		if (isset($_POST['tpl_compile']) && !empty($_POST['decompile']))
		{
			$str = "<?php\n" . $template->compile(stripslashes($_POST['decompile'])) . "\n?".">";

			$fp = fopen($phpbb_root_path . 'cache/templates/' . $tplroot . '/' . $tplname . '.html.' . $phpEx, 'w+');
			fwrite ($fp, $str);
			fclose($fp);

			@chmod($phpbb_root_path . 'templates/cache/' . $tplroot . '/' . $tplname . '.html.' . $phpEx, 0644);

			add_log('admin', 'log_template_edit', $tplname, $tplroot);

			exit;
		}
		else if (!empty($tplname) && isset($_POST['tpl_name']))
		{
			$fp = fopen($phpbb_root_path . 'cache/templates/' . $tplroot . '/' . $tplname . '.html.' . $phpEx, 'r');
			while (!feof($fp))
			{
				$str .= fread($fp, 4096);
			}
			@fclose($fp);

			$match_preg = array(
				'#\$this\->_tpl_include\(\'(.*?)\'\);#',
				'#echo \$this->_tpldata\[\'\.\'\]\[0\]\[\'(.*?)\'\];#', 
				'#echo \(\(isset\(\$this\->_tpldata\[\'\.\'\]\[0\]\[\'(.*?)\'\]\)\).*?;#', 
				'#if \(.*?\[\'\.\'\]\[0\]\[\'(.*?)\'\]\) \{ #', 
				'#\$_(.*?)_count.*?;if \(.*?\)\{#', 
			);

			$replace_preg = array(
				'<!-- INCLUDE $1 -->', 
				'{$1}', 
				'{$1}', 
				'<!-- IF \1 -->',
				'<!-- BEGIN \1 -->', 
			);

			$str = preg_replace($match_preg, $replace_preg, $str);
			$str = str_replace('<?php ', '', $str);
			$str = str_replace(' ?>', '', $str);
		}
		else
		{
			$str = (!empty($_POST['decompile'])) ? stripslashes($_POST['decompile']) : '';
		}

		if (isset($_POST['tpl_download']))
		{
			header("Content-Type: text/html; name=\"" . $tplname . ".html\"");
			header("Content-disposition: attachment; filename=" . $tplname . ".html");
			echo $str;
			exit;

		}

		$tplroot_options = get_templates($tplroot);

		$tplname_options = '';
		$dp = @opendir($phpbb_root_path . 'cache/templates/' . $tplroot . '/');
		while ($file = readdir($dp))
		{
			if (strstr($file, '.html.' . $phpEx) && is_file($phpbb_root_path . 'cache/templates/' . $tplroot . '/' . $file))
			{
				$tpl = substr($file, 0, strpos($file, '.'));
				$selected = ($tplname == $tpl) ? ' selected="selected"' : '';
				$tplname_options .= '<option value="' . $tpl . '"' . $selected . '>' . $tpl . '</option>';
			}
		}
		closedir($dp);

		//
		adm_page_header($user->lang['Edit_template']);

?>

<h2><?php echo $user->lang['Edit_template']; ?></h2>

<p><?php echo $user->lang['Edit_template_explain']; ?></p>

<form method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=edittemplate"; ?>">

<p><?php echo $user->lang['Select_template']; ?>: <select name="tplroot"><?php echo $tplroot_options; ?></select>&nbsp; <input class="liteoption" type="submit" name="tpl_root" value="Select" /></p>

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

		adm_page_footer();
		break;













	case 'edittheme':

		$theme_id = (isset($_POST['themeroot'])) ? $_POST['themeroot']  : '';

		if (isset($_POST['update']))
		{
			$sql = "SELECT theme_id, theme_name
				FROM " . STYLES_CSS_TABLE . "
				WHERE theme_id = $theme_id";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$theme_name = $row['theme_name'];

				$css_data = (!empty($_POST['css_data'])) ? htmlentities($_POST['css_data']) : '';
				$css_external = (!empty($_POST['css_data'])) ? $_POST['css_data'] : '';

				$sql = "UPDATE " > STYLES_CSS_TABLE . "
					SET css_data = '$css_data', css_external = '$css_external'
					WHERE theme_id = $theme_id";
				$db->sql_query($sql);

				add_log('admin', 'log_theme_edit', $theme_name);

				message_die(MESSAGE, $user->lang['Success_theme_update']);
			}
		}

		adm_page_header($user->lang['Edit_theme']);

		$sql = "SELECT theme_id, theme_name
			FROM " . STYLES_CSS_TABLE;
		$result = $db->sql_query($sql);

		$theme_options = '';
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$theme_options .= (($theme_options != '') ? ', ' : '') . '<option value="' . $row['theme_id'] . '">' . $row['theme_name'] . '</option>';
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$css_data = '';
		$css_external = '';
		if ($theme_id)
		{
			$sql = "SELECT css_data, css_external
				FROM " . STYLES_CSS_TABLE . "
				WHERE theme_id = $theme_id";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$css_data = preg_replace('/\t{1,}/i', ' ', $row['css_data']);
				$css_external = $row['css_external'];
			}
		}

		$user->lang = array_merge($user->lang, array(
			'SELECT_CLASS'		=> 'Select class', 
			'style_body'		=> 'Body',
			'style_p'			=> 'Paragraphs', 
			'style_th'			=> 'Table Header Cell',
			'style_td'			=> 'Table Data Cell',
			'style_postdetails'	=> 'Post Information',
			'style_postbody'	=> 'Post text', 
			'style_gen'			=> 'General Text', 
			'style_genmed'		=> 'Medium Text', 
			'style_gensmall'	=> 'Small Text',
			'style_copyright'	=> 'Copyright Text', 
			
		));

		$base_classes = array(
			'body',
			'p', 
			'th',
			'td', 
			'postdetails',
			'postbody', 
			'gen', 
			'gensmall', 
			'copyright'
		);
	
		$class_options = '';
		foreach ($base_classes as $class)
		{
			$class_options .= '<option value="' . $class . '">' . $user->lang['style_' . $class] . '</option>';
		}


		$imglist = filelist($phpbb_root_path . 'templates');

		$bg_imglist = '';
		foreach ($imglist as $img)
		{
			$img = substr($img['path'], 1) . (($img['path'] != '') ? '/' : '') . $img['file']; 

//			$selected = ' selected="selected"';
			$bg_imglist .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
		}
		$bg_imglist = '<option value=""' . (($edit_img == '') ? ' selected="selected"' : '') . '>----------</option>' . $bg_imglist;


?>

<script language="javascript" type="text/javascript">
<!--

function swatch(field)
{
	window.open('./swatch.php?form=style&amp;name=' + field, '_swatch', 'HEIGHT=115,resizable=yes,scrollbars=no,WIDTH=636');
	return false;
}

//-->
</script>

<h2><?php echo $user->lang['Edit_theme']; ?></h2>

<p><?php echo $user->lang['Edit_theme_explain']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_CLASS']; ?>: <select name="class"><?php echo $class_options; ?></select>&nbsp; <input class="liteoption" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th>Parameter</th>
				<th>Value</th>
			</tr>
			<tr>
				<td class="row1">Background image:</td>
				<td class="row2"><select name="backgroundimage"><?php echo $bg_imglist ?></select></td>
			</tr>
			<tr>
				<td class="row1">Repeat background:</td>
				<td class="row2"><select name="repeat"><option value="no">No</option><option value="x">Horizontally Only</option><option value="y">Vertically Only</option><option value="yes">Both Directions</option></select></td>
			</tr>
			<tr>
				<td class="row1">Background color:</td>
				<td class="row2"><input class="post" type="text" name="bgcolor" value="" size="6" maxlength="6" /> [ <a href="swatch.php" onclick="swatch('bgcolor');return false" target="_swatch">Web-safe Colour Swatch</a> ]</td>
			</tr>
			<tr>
				<td class="row1">Foreground color:</td>
				<td class="row2"><input class="post" type="text" name="color" value="" size="6" maxlength="6" /> [ <a href="swatch.php" onclick="swatch('color');return false" target="_swatch">Web-safe Colour Swatch</a> ]</td>
			</tr>
			<tr>
				<td class="row1">Font:</td>
				<td class="row2"><input class="post" type="text" name="fontface" value="" size="40" maxlength="255" /></td>
			</tr>
			<tr>
				<td class="row1">Font size:</td>
				<td class="row2"><input class="post" type="text" name="fontsize" value="" size="3" maxlength="3" /> <select name="fontsizescale"><option value="pt">pt</option><option value="px">px</option><option value="em">em</option><option value="%">%</option></select></td>
			</tr>
			<tr>
				<td class="row1">Font Bold:</td>
				<td class="row2"><input type="radio" name="bold" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="bold" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1">Font Italic:</td>
				<td class="row2"><input type="radio" name="italic" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="italic" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1">Font Underline:</td>
				<td class="row2"><input type="radio" name="underline" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="underline" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1">Line spacing:</td>
				<td class="row2"><input class="post" type="text" name="linespacing" value="" size="3" maxlength="3" /> <select name="linespacingscale"><option value="pt">pt</option><option value="px">px</option><option value="em">em</option><option value="%">%</option></select></td>
			</tr>
			<!-- tr>
				<td class="row1" width="40%">Advanced: <br /><span class="gensmall">Enter here any additional CSS parameters and their values. Enter each parameter on a new row and terminate each with semi-colon ;</td>
				<td class="row2"><textarea name="freeform" cols="40" rows="3"></textarea></td>
			</tr -->
			<tr>
				<td class="cat" colspan="2" align="center"><input class="liteoption" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

		adm_page_footer();

		break;









}


//
function get_templates($tplroot = '')
{
	global $db;

	$sql = "SELECT template_name, template_path
		FROM " . STYLES_TPL_TABLE . "
		ORDER BY template_name";
	$result = $db->sql_query($sql);

	$tplroot_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($tplroot == $row['template_path']) ? ' selected="selected"' : '';
		$tplroot_options .= '<option value="' . $row['template_path'] . '"' . $selected . '>' . $row['template_path'] . '</option>';
	}

	return $tplroot_options;
}

?>