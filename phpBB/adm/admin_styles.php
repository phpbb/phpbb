<?php

if ( !empty($setmodules) )
{
	if ( !$auth->acl_get('a_styles') )
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





	case 'templates':

		$template_id = (isset($_REQUEST['id'])) ? $_REQUEST['id']  : '';

		switch ($action)
		{
			case 'preview':

				break;

			case 'edit':

				$tplcols = (isset($_POST['tplcols'])) ? max(60, intval($_POST['tplcols'])) : 76;
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

<h2><?php echo $user->lang['Edit_template']; ?></h2>

<p><?php echo $user->lang['Edit_template_explain']; ?></p>

<form method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=templates&amp;action=edit"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="0" border="0" align="center">
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


				break;

			case 'delete':
				break;

			case 'export':
				break;
		}

		adm_page_header($user->lang['EDIT_TEMPLATE']);

?>
<h2><?php echo $user->lang['Edit_template']; ?></h2>

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

		$theme_id = (isset($_REQUEST['id'])) ? $_REQUEST['id']  : '';

		switch ($action)
		{
			case 'preview':

				break;

			case 'edit':

				if (isset($_POST['update']))
				{
				}


				$class = (isset($_POST['classname'])) ? htmlspecialchars($_POST['classname']) : '';

				if ($theme_id)
				{
					$sql = 'SELECT * 
						FROM ' . STYLES_CSS_TABLE . "
						WHERE theme_id = $theme_id";
					$result = $db->sql_query($sql);

					if ($theme_data = $db->sql_fetchrow($result))
					{
					}
					$db->sql_freeresult($result);
				}

				$user->lang = array_merge($user->lang, array(
					'SELECT_CLASS'			=> 'Select class', 

					'style_cat_text'		=> 'Text classes',
					'style_body'			=> 'Body',
					'style_p'				=> 'Paragraphs', 
					'style_h1'				=> 'Header 1', 
					'style_h2'				=> 'Header 2', 
					'style_h3'				=> 'Header 3', 

					'style_postdetails'		=> 'Post Information',
					'style_postbody'		=> 'Post Text', 
					'style_postauthor'		=> 'Post Author', 

					'style_topictitle'		=> 'Topic titles', 
					'style_topicauthor'		=> 'Topic Author', 
					'style_topicdetails'	=> 'Topic Details', 

					'style_gen'				=> 'General Text', 
					'style_genmed'			=> 'Medium Text', 
					'style_gensmall'		=> 'Small Text',

					'style_copyright'		=> 'Copyright Text', 


					'style_cat_tables'		=> 'Table classes', 
					'style_cat'				=> 'Category Header Cell', 
					'style_cattitle'		=> 'Category Header Text', 
					'style_th'				=> 'Table Header Cell',
					'style_td'				=> 'Table Data Cell',

					'style_cat_bbcode'		=> 'BBCode classes', 
					'style_b'				=> 'Bold',
					'style_u'				=> 'Underline',
					'style_i'				=> 'Italics',
					'style_color'			=> 'Colour',
					'style_size'			=> 'Size',	
					'style_code'			=> 'Code',
					'style_quote'			=> 'Quote',
					'style_flash'			=> 'Flash',
					'style_syntaxbg'		=> 'Syntax Background', 
					'style_syntaxcomment'	=> 'Syntax Comments',
					'style_syntaxdefault'	=> 'Syntax Default',
					'style_syntaxhtml'		=> 'Syntax HTML',
					'style_syntaxkeyword'	=> 'Syntax Keyword',
					'style_syntaxstring'	=> 'Syntax String',
				
				));

				$base_classes = array(
					'text'		=> array(
						'body',
						'p',
						'h1', 
						'h2', 
						'h3',
						'gen', 
						'genmed', 
						'gensmall', 
						'topictitle', 
						'topicauthor', 
						'topicdetails', 
						'postdetails',
						'postbody', 
						'postauthor', 
						'copyright'
					), 
					'tables'	=> array(
						'th',
						'td', 
						'cat', 
						'cattitle', 
					),
					'bbcode'	=> array(
						'b',
						'u',
						'i',
						'color',
						'size',
						'code',
						'quote',
						'flash',
						'syntaxbg', 
						'syntaxcomment',
						'syntaxdefault',
						'syntaxhtml',
						'syntaxkeyword',
						'syntaxstring',
					)
				);
			
				$class_options = '';
				foreach ($base_classes as $category => $class_ary)
				{
					$class_options .= '<option class="sep">' . $user->lang['style_cat_' . $category] . '</option>';
					foreach ($class_ary as $class_name)
					{
						$selected = ($class_name == $class) ? ' selected="selected"' : '';
						$class_options .= '<option value="' . $class_name . '"' . $selected . '>' . $user->lang['style_' . $class_name] . '</option>';
					}
				}

				if (!empty($class))
				{
					//TEMP
					if (!($fp = fopen($phpbb_root_path . 'templates/' . $theme_data['css_external'], 'rb')))
					{
						die("ERROR");
					}
					$stylesheet = fread($fp, filesize($phpbb_root_path . 'templates/' . $theme_data['css_external']));
					fclose($fp);
					$stylesheet = str_replace(array("\t", "\n"), " ", $stylesheet);


					if (preg_match('#^.*?' . $class . ' {(.*?)}#m', $stylesheet, $matches))
					{
						$stylesheet = &$matches[1];

						$match_elements = array(
							'colors'	=> array('background-color', 'color', 'border-color', 
							),
							'sizes'		=> array('font-size', 'line-height', 'border-width', 
							),
							'images'	=> array('background-image', 
							),
							'repeat'	=> array('background-repeat',
							),
							'other'		=> array('font-weight', 'font-family', 'font-style', 'text-decoration', 'border-style', 
							),
						);

						foreach ($match_elements as $type => $match_ary)
						{
							foreach ($match_ary as $match)
							{
								$$match = '';
								$var = str_replace('-', '', $match);

								if (preg_match('#\b' . $match . ': (.*?);#s', $stylesheet, $matches))
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

				// Grab list of potential images for class backgrounds
				$imglist = filelist($phpbb_root_path . 'templates');

				$bg_imglist = '';
				foreach ($imglist as $img)
				{
					$img = substr($img['path'], 1) . (($img['path'] != '') ? '/' : '') . $img['file']; 

					$selected = (preg_match('#templates/' . preg_quote($img) . '#', $backgroundimage)) ? ' selected="selected"' : '';
					$bg_imglist .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
				}
				$bg_imglist = '<option value=""' . (($edit_img == '') ? ' selected="selected"' : '') . '>' . $user->lang['NONE'] . '</option>' . $bg_imglist;


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


<h2><?php echo $user->lang['Edit_theme']; ?></h2>

<p><?php echo $user->lang['Edit_theme_explain']; ?></p>

<p>Selected Theme: <b><?php echo $theme_data['theme_name']; ?></b></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$theme_id"; ?>"><table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_CLASS']; ?>: <select name="classname" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $class_options; ?></select>&nbsp; <input class="liteoption" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th>Parameter</th>
				<th>Value</th>
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
				<td class="cat" colspan="2"><b>Text</b></td>
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
			<tr>
				<td class="cat" colspan="2"><b>Borders</b></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b>Color:</b> <br /><span class="gensmall">This is a hex-triplet of the form RRGGBB<br /><a href="swatch.php" onclick="swatch('bordercolor');return false" target="_swatch">Web-safe Colour Swatch</a></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="0" border="0"><tr><td><input class="post" type="text" name="bordercolor" value="<?php echo $bordercolor; ?>" size="8" maxlength="14" /></td><td>&nbsp;</td><td style="border:solid 1px black; background-color: <?php echo $bordercolor; ?>"><img src="../images/spacer.gif" width="45" height="15" alt="" /></td></tr></table></td>
			</tr>
			<tr>
				<td class="row1"><b>Width:</b></td>
				<td class="row2"><input class="post" type="text" name="borderwidth" value="<?php echo $borderwidth; ?>" size="2" maxlength="2" /> <select name="borderwidthunits"><?php

			foreach (array('pt', 'px', 'em', '%') as $units)
			{
				echo '<option value="' . $units . '"' . (($borderwidthunits == $units) ? ' selected="selected"' : '') . '>' . $units . '</option>';
			}
	
?></select></td>
			</tr>
			<tr>
				<td class="row1"><b>Style:</b></td>
				<td class="row2"><select name="borderstyle"><?php
	
			foreach (array('' => '------', 'none' => 'none', 'solid' => 'solid', 'dashed' => 'dashed', 'dotted' => 'dotted') as $cssvalue => $cssstyle)
			{
				echo '<option value="' . $cssvalue . '"' . (($borderstyle == $cssvalue) ? ' selected="selected"' : '') . '>' . $cssstyle . '</option>';
			}
	
?></select></td>
			</tr>
			<!-- tr>
				<td class="row1" width="40%">Advanced: <br /><span class="gensmall">Enter here any additional CSS parameters and their values. Enter each parameter on a new row and terminate each with semi-colon ;</td>
				<td class="row2"><textarea name="freeform" cols="40" rows="3"></textarea></td>
			</tr -->
			<tr>
				<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="preview" value="<?php echo $user->lang['PREVIEW']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
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

		adm_page_header($user->lang['EDIT_THEME']);

?>
<h2><?php echo $user->lang['Edit_theme']; ?></h2>

<p><?php echo $user->lang['Edit_theme_explain']; ?></p>

<p>Selected Theme: <b>subSilver</b></p>

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
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=recreate&amp;id=" . $row['theme_id']; ?>">Recreate</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=delete&amp;id=" . $row['theme_id']; ?>">Delete</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=export&amp;id=" . $row['theme_id']; ?>">Export</a> | <a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=themes&amp;action=preview&amp;id=" . $row['theme_id']; ?>">Preview</a>&nbsp;</td>
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






}

?>