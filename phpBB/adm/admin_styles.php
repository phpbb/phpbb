<?php

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_styles'))
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['STYLE']['EDIT_STYLE'] = $filename . "$SID&amp;mode=styles";
	$module['STYLE']['EDIT_TEMPLATE'] = $filename . "$SID&amp;mode=templates";
	$module['STYLE']['EDIT_THEME'] = $filename . "$SID&amp;mode=themes";
	$module['STYLE']['EDIT_IMAGESET'] = $filename . "$SID&amp;mode=imagesets";

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
$action = (isset($_REQUEST['action'])) ? htmlspecialchars($_REQUEST['action']) : '';




switch ($mode)
{

	case 'styles':

		$style_id = (isset($_REQUEST['id'])) ? $_REQUEST['id']  : '';

		switch ($action)
		{
			case 'preview':
				break;

			case 'edit':

				if (isset($_POST['update']))
				{
				}

				if ($style_id)
				{
					$sql = 'SELECT * 
						FROM ' . STYLES_TABLE . "
						WHERE style_id = $style_id";
					$result = $db->sql_query($sql);

					if ($style_data = $db->sql_fetchrow($result))
					{
					}
					$db->sql_freeresult($result);
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
						$selected = ($row[$field . '_id'] == $style_data[$field . '_id']) ? ' selected="selected"' : '';
						${$field . '_options'} .= '<option value="' . $row[$field . '_id'] . '"' . $selected . '>' . $row[$field . '_name'] . '</option>';
					}
					$db->sql_freeresult($result);
				}


				// Output the page
				adm_page_header($user->lang['EDIT_STYLE']);

?>

<h1><?php echo $user->lang['EDIT_STYLE']; ?></h1>

<p><?php echo $user->lang['EDIT_STYLE_EXPLAIN']; ?></p>

<p>Selected Style: <b><?php echo $style_data['style_name']; ?></b></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$style_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<td class="row1">Style Name</td>
		<td class="row2"><input class="post" type="text" name="style_name" maxlength="255" size="40" /></td>
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
		<td class="row1">Active:</td>
		<td class="row2"></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="submit" name="preview" value="<?php echo $user->lang['PREVIEW']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
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

		adm_page_header($user->lang['EDIT_STYLE']);

?>
<h1><?php echo $user->lang['EDIT_STYLE']; ?></h1>

<p><?php echo $user->lang['EDIT_STYLE_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Style name</th>
		<th>&nbsp;</th>
	</tr>
<?php

		$sql = 'SELECT style_id, style_name
			FROM ' . STYLES_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;id=" . $row['style_id']; ?>"><?php echo $row['style_name']; ?></a></td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=deactivate&amp;id=" . $row['style_id']; ?>">Deactivate</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;id=" . $row['style_id']; ?>">Delete</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=export&amp;id=" . $row['style_id']; ?>">Export</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=preview&amp;id=" . $row['style_id']; ?>">Preview</a>&nbsp;</td>
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
		<td class="cat" colspan="2">&nbsp;</td>
	</tr>
</table></form>
<?php 

		break;


	case 'imagesets':
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

<h1>Edit Imageset</h1>

<p>Template set: <select name="imgroot"><?php echo $imgroot_options; ?></select>&nbsp; <input class="btnlite" type="submit" name="img_root" value="Select set" /> &nbsp; <input class="btnlite" type="submit" name="create" value="Create new set" /></p>

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
		<td class="<?php echo $row_class; ?>" align="center">&nbsp;<input class="btnlite" type="submit" value="Edit" /></td>
	</tr>
<?php

			}

?>
	<tr>
		<td class="cat" colspan="3" height="28" align="center"> <input class="btnlite" type="submit" name="download" value="Download set" &nbsp; <input class="btnlite" type="submit" name="img_delete" value="Delete set" /> </td>
	</tr>
</table></form>

<?php

	}

		adm_page_footer();

		break;





	case 'templates':

		$template_id = (isset($_REQUEST['id'])) ? $_REQUEST['id']  : '';

		switch ($action)
		{
			case 'preview':

				break;

			case 'edit':

				$tplcols = (isset($_POST['tplcols'])) ? max(76, intval($_POST['tplcols'])) : 76;
				$tplrows = (isset($_POST['tplrows'])) ? max(30, intval($_POST['tplrows'])) : 30;
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

//				$tplroot_options = get_templates($tplroot);

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

<h1><?php echo $user->lang['Edit_template']; ?></h1>

<p><?php echo $user->lang['Edit_template_explain']; ?></p>

<form method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=edit"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="0" border="0" align="center">
	<tr>
		<td class="cat"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td>&nbsp;Template: <select name="tplname"><?php echo $tplname_options; ?></select>&nbsp; <input class="btnlite" type="submit" name="tpl_name" value="Select" /></td>

				<td align="right">Columns: <input type="text" name="tplcols" size="3" maxlength="3" value="<?php echo $tplcols; ?>" /> &nbsp;Rows: <input type="text" name="tplrows" size="3" maxlength="3" value="<?php echo $tplrows; ?>" />&nbsp; <input class="btnlite" type="submit" name="tpl_layout" value="Update" />&nbsp;</td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><textarea class="edit" style="background-color:#DEE3E7" cols="<?php echo $tplcols; ?>" rows="<?php echo $tplrows; ?>" name="decompile"><?php echo htmlentities($str); ?></textarea></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" height="28" align="center"><input class="btnlite" type="submit" name="tpl_compile" value="Recompile" /> &nbsp; <input class="btnlite" type="submit" name="tpl_download" value="Download" /> &nbsp; <input class="btnlite" type="reset" value="Undo" /></td>
	</tr>
</table></form>

<?php

				adm_page_footer();
				break;


				break;

			case 'delete':
				break;

			case 'export':
				break;
		}

		adm_page_header($user->lang['EDIT_TEMPLATE']);

?>
<h1><?php echo $user->lang['Edit_template']; ?></h1>

<p><?php echo $user->lang['Edit_template_explain']; ?></p>

<form name="templates" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Template name</th>
		<th>&nbsp;</th>
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
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=bbcode&amp;id=" . $row['template_id']; ?>">BBCode</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=delete&amp;id=" . $row['template_id']; ?>">Delete</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=export&amp;id=" . $row['template_id']; ?>">Export</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=preview&amp;id=" . $row['template_id']; ?>">Preview</a>&nbsp;</td>
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
		<td class="cat" colspan="2">&nbsp;</td>
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

				$txtcols = (isset($_POST['txtcols'])) ? max(40, intval($_POST['txtcols'])) : 76;
				$txtrows = (isset($_POST['txtrows'])) ? max(5, intval($_POST['txtrows'])) : 10;
				$showcss = (!empty($_POST['showcss'])) ? true : ((!empty($_POST['hidecss'])) ? false : ((!empty($_GET['showcss'])) ? true : false));

				// List of default classes, categorised
				$base_classes = array(
					'text'	=> array(
						'body',  'p',  'a',  'h1',  'h2',  'h3',  'tabletitle',  'cattitle',  'topictitle',  'topicauthor',  'topicdetails',  'postdetails',  'postbody',  'posthilit', 'postauthor',  'genmed',  'gensmall',  'copyright',
					),
					'tables'	=> array(
						'table',  'th', 'cat',  'catdiv',  'td',  'row1',  'row2',  'row3',  'spacer',  'hr', 
					),
					'forms'		=> array(
						'form',  'input',  'select',  'textarea',  'post',  'btnlite', 'btnmain', 'btnbbcode',
					), 
					'bbcode'	=> array(
						'b', 'u', 'i', 'color', 'size', 'code', 'quote', 'flash', 'syntaxbg',  'syntaxcomment', 'syntaxdefault', 'syntaxhtml', 'syntaxkeyword', 'syntaxstring',
					), 
					'custom'	=> array(),
				);


				// We want to submit the updates
				if (isset($_POST['update']))
				{
				}

				
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
					if (!($fp = fopen($phpbb_root_path . 'styles/themes/' . $css_external, 'rb')))
					{
						die("ERROR");
					}
					$stylesheet = fread($fp, filesize($phpbb_root_path . 'styles/themes/' . $css_external));
					fclose($fp);
					$stylesheet = str_replace(array("\t", "\n"), " ", $stylesheet);


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
					// Here we pull out the appropriate class entry then proceed to pull it apart,
					// setting appropriate variables to their respective values. We only match
					// certain css elements, the rest are "hidden" and can be accessed by exposing
					// the raw css
					if (preg_match('#^.*?' . $class . ' {(.*?)}#m', $stylesheet, $matches))
					{
						$css_element = &$matches[1];

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

						foreach ($match_elements as $type => $match_ary)
						{
							foreach ($match_ary as $match)
							{
								$$match = '';
								$var = str_replace('-', '', $match);

								if (preg_match('# ' . $match . ': (.*?);#s', $css_element, $matches))
								{
									switch ($type)
									{
										case 'colors':
											$$var = trim($matches[1]);
											break;

										case 'sizes':
											if (preg_match('#(.*?)(px|%|em|pt)#', $matches[1], $matches))
											{
												${$var . 'units'} = trim($matches[2]);
											}
											$$var = trim($matches[1]);
											break;

										case 'images':
											if (preg_match('#url\(\'(.*?)\'\)#', $matches[1], $matches))
											{
												$$var = trim($matches[1]);
											}
											$$var = str_replace('./', $theme_data['theme_name'] . '/', $$var);
											break;

										case 'repeat':
											$$var = trim($matches[1]);
											break;

										default:
											$$var = trim($matches[1]);
									}
								}
							}
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
						$class_options .= '<option value="' . $class_name . '"' . $selected . '>' . (($category == 'custom') ? $class_name : $user->lang['style_' . $class_name]) . '</option>';
					}
				}


				// Grab list of potential images for class backgrounds
				$imglist = filelist($phpbb_root_path . 'styles/themes');

				$bg_imglist = '';
				foreach ($imglist as $img)
				{
					$img = substr($img['path'], 1) . (($img['path'] != '') ? '/' : '') . $img['file']; 

					$selected = (preg_match('#' . preg_quote($img) . '#', $backgroundimage)) ? ' selected="selected"' : '';
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
	window.open('./swatch.php?form=style&amp;name=' + field, '_swatch', 'HEIGHT=115,resizable=yes,scrollbars=no,WIDTH=636');
	return false;
}

//-->
</script>


<h1><?php echo $user->lang['EDIT_THEME']; ?></h1>

<p><?php echo $user->lang['EDIT_THEME_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$theme_id&amp;showcss=$showcss"; ?>"><table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_CLASS']; ?>: <select name="classname" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $class_options; ?></select>&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

				if ($showcss)
				{

?>
			<tr>
				<td class="cat" colspan="2" align="right">Columns: <input class="post" type="text" name="txtcols" size="3" maxlength="3" value="<?php echo $txtcols; ?>" /> &nbsp;Rows: <input class="post" type="text" name="txtrows" size="3" maxlength="3" value="<?php echo $txtrows; ?>" />&nbsp; <input class="btnlite" type="submit" name="showcss" value="Update" />&nbsp;</td>
			</tr>
			<tr>
				<th colspan="2">Raw CSS</th>
			</tr>
			<tr>
				<td class="row1"><b>Theme name:</b></td>
				<td class="row2"><input class="post" type="text" name="theme_name" value="<?php echo $theme_name; ?>" maxlength="30" size="25" /></td>
			</tr>
			<tr>
				<td class="row1" colspan="2" align="center"><textarea class="post" name="rawcss" rows="<?php echo $txtrows; ?>" cols="<?php echo $txtcols; ?>"><?php echo trim(str_replace('; ', ";\n", $css_element)); ?></textarea></td>
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
			<tr>
				<td class="row1"><b>Theme name:</b></td>
				<td class="row2"><input class="post" type="text" name="theme_name" value="<?php echo $theme_name; ?>" maxlength="30" size="25" /></td>
			</tr>
			<tr>
				<td class="cat" colspan="2"><b>Background</b></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Color:</b> <br /><span class="gensmall">This is a hex-triplet of the form RRGGBB<br /><a href="swatch.php" onclick="swatch('bgcolor');return false" target="_swatch">Web-safe Colour Swatch</a></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="0" border="0"><tr><td><input class="post" type="text" name="backgroundcolor" value="<?php echo $backgroundcolor; ?>" size="8" maxlength="14" /></td><td>&nbsp;</td><td style="border:solid 1px black; background-color: <?php echo $backgroundcolor; ?>"><img src="../images/spacer.gif" width="45" height="15" alt="" /></td></tr></table></td>
			</tr>
			<tr>
				<td class="row1"><b>Image:</b></td>
				<td class="row2"><select name="backgroundimage"><?php echo $bg_imglist ?></select></td>
			</tr>
			<tr>
				<td class="row1"><b>Repeat background:</b></td>
				<td class="row2"><select name="backgroundrepeat"><?php

					foreach (array('' => '------', 'none' => 'No', 'repeat-x' => 'Horizontally Only', 'repeat-y' => 'Vertically Only', 'both' => 'Both Directions') as $cssvalue => $cssrepeat)
					{
						echo '<option value="' . $cssvalue . '"' . (($backgroundrepeat == $cssvalue) ? ' selected="selected"' : '') . '>' . $cssrepeat . '</option>';
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
				<td class="row2"><input class="post" type="text" name="fontfamily" value="<?php echo $fontfamily; ?>" size="40" maxlength="255" /></td>
			</tr>
			<tr>
				<td class="row1"><b>Size:</b></td>
				<td class="row2"><input class="post" type="text" name="fontsize" value="<?php echo $fontsize; ?>" size="3" maxlength="3" /> <select name="fontsizeunits"><?php

					foreach (array('pt', 'px', 'em', '%') as $units)
					{
						echo '<option value="' . $units . '"' . (($fontsizeunits == $units) ? ' selected="selected"' : '') . '>' . $units . '</option>';
					}
	
?></select></td>
			</tr>
			<tr>
				<td class="row1"><b>Bold:</b></td>
				<td class="row2"><input type="radio" name="fontweight" value="bold"<?php echo (!empty($fontweight) && $fontweight == 'bold') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="bold" value="normal"<?php echo (empty($fontweight) || $fontweight == 'normal') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b>Italic:</b></td>
				<td class="row2"><input type="radio" name="fontstyle" value="italic"<?php echo (!empty($fontstyle) && $fontstyle == 'italic') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="italic" value=""<?php echo (empty($fontstyle) || $fontstyle == 'normal') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b>Underline:</b></td>
				<td class="row2"><input type="radio" name="textdecoration" value="underlined"<?php echo (!empty($textdecoration) && $textdecoration == 'underline') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="underline" value="none"<?php echo (empty($textdecoration) || $textdecoration != 'underline') ? ' checked="checked"' : ''; ?>/> <?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b>Line spacing:</b></td>
				<td class="row2"><input class="post" type="text" name="lineheight" value="<?php echo $lineheight; ?>" size="3" maxlength="3" /> <select name="lineheightunits"><?php

					foreach (array('pt', 'px', 'em', '%') as $units)
					{
						echo '<option value="' . $units . '"' . (($lineheightunits == $units) ? ' selected="selected"' : '') . '>' . $units . '</option>';
					}
	
?></select></td>
			</tr>
<?php

				}

?>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="submit" name="preview" value="<?php echo $user->lang['PREVIEW']; ?>" />&nbsp;&nbsp;<?php
									
				if ($showcss)
				{

?><input class="btnlite" type="submit" name="hidecss" value="<?php echo $user->lang['HIDE_RAW_CSS']; ?>" /><?php

				}
				else
				{

?><input class="btnlite" type="submit" name="showcss" value="<?php echo $user->lang['SHOW_RAW_CSS']; ?>" /><?php

				}

?>&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
		</table>
		

		<h1>Custom Class</h1>

		<p>You can add additional classes to this theme if you wish. You must provide the actual CSS class name below, it must be the same as that you have or will use in your template. Please remember that class names may contain only alphanumeric characters, periods (.), colons (:) and number/hash/pound (#). The new class will be added to the Custom Class category in the select box above.</p>

		<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2">Add Custom Class</td>
			</tr>
			<tr>
				<td class="row1" width="40%">CSS class name:</td>
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
		adm_page_header($user->lang['EDIT_THEME']);

?>
<h1><?php echo $user->lang['THEMES']; ?></h1>

<p><?php echo $user->lang['THEMES_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>Theme name</th>
		<th>&nbsp;</th>
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
		<td class="<?php echo $row_class; ?>" width="100%"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=edit&amp;id=" . $row['theme_id']; ?>"><?php echo $row['theme_name']; ?></a></td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=recreate&amp;id=" . $row['theme_id']; ?>">Recache</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=delete&amp;id=" . $row['theme_id']; ?>">Delete</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=export&amp;id=" . $row['theme_id']; ?>">Export</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=preview&amp;id=" . $row['theme_id']; ?>">Preview</a>&nbsp;</td>
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
		<td class="cat" colspan="2" align="right">Create new theme: <input class="post" type="text" name="theme_name" value="" maxlength="30" size="25" /> <input class="btnmain" type="submit" name="newtheme" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
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