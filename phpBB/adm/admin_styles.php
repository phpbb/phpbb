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
	$action = '';
	if (isset($_POST['add']))
	{
		$action = 'add';
	}
	else if (isset($_POST['preview']))
	{
		$action = 'preview';
	}
}

$error = array();

$safe_mode = (@ini_get('safe_mode') && @strtolower(ini_get('safe_mode')) == 'on') ? true : false;

// Generate list of archive types inc. regexp | match
$archive_types = $archive_preg = '';
foreach (array('zip' => 'zlib', 'tar' => '', 'tar.gz' => 'zlib', 'tar.bz2' => 'bz2') as $type => $module)
{
	if ($module && !extension_loaded($module))
	{
		break;
	}
	$archive_types .= (($archive_types != '') ? ', ' : '') . "<u>.$type</u>";
	$archive_preg .= (($archive_preg != '') ? '|' : '') . '\.' . preg_quote($type);
}



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
				if ($style_id)
				{
					$sql = 'SELECT s.style_id, s.style_name, s.style_copyright, t.template_id, t.template_name, t.template_path, t.template_copyright, c.theme_id, c.theme_name, c.theme_path, c.theme_copyright, c.css_storedb, c.css_data, i.*
						FROM ' . STYLES_TABLE . ' s, ' . STYLES_TPL_TABLE . ' t, ' . STYLES_CSS_TABLE . ' c, ' . STYLES_IMAGE_TABLE . " i 
						WHERE s.style_id = $style_id
							AND t.template_id = s.template_id 
							AND c.theme_id = s.theme_id 
							AND i.imageset_id = s.imageset_id";
					$result = $db->sql_query($sql);

					if (!($row = ($db->sql_fetchrow($result))))
					{
						trigger_error($user->lang['NO_STYLE']);
					}
					$db->sql_freeresult($result);

					$var_ary = array('style_id', 'style_name', 'style_copyright', 'template_id', 'template_name', 'template_path', 'template_copyright', 'theme_id', 'theme_name', 'theme_path', 'theme_copyright', 'css_storedb', 'css_data', 'imageset_id', 'imageset_name', 'imageset_path', 'imageset_copyright');
					foreach ($var_ary as $var)
					{
						$$var = (!empty($row[$var])) ? $row[$var] : '';
						unset($row[$var]);
					}

					$style_cfg  = addslashes($style_name) . "\n";
					$style_cfg .= addslashes($style_copyright) . "\n";
					$style_cfg .= addslashes($config['version']) . "\n";

					$template_cfg  = addslashes($template_name) . "\n";
					$template_cfg .= addslashes($template_copyright) . "\n";
					$template_cfg .= addslashes($config['version']) . "\n";

					$theme_cfg  = addslashes($theme_name) . "\n";
					$theme_cfg .= addslashes($theme_copyright) . "\n";
					$theme_cfg .= addslashes($config['version']) . "\n";

					$imageset_cfg  = addslashes($imageset_name) . "\n";
					$imageset_cfg .= addslashes($imageset_copyright) . "\n";
					$imageset_cfg .= addslashes($config['version']) . "\n";
					
					foreach (array_keys($row) as $key)
					{
						$imageset_cfg .= $key . '||' . str_replace("styles/$imageset_path/imageset/", '{PATH}', $row[$key]) . "\n";
						unset($row[$key]);
					}

					$files = array(
						array(
							'src'		=> "styles/$template_path/template/", 
							'prefix-'	=> "styles/$template_path/", 
							'prefix+'	=> false, 
							'exclude'	=> 'template.cfg'), 
						array(
							'src'		=> "styles/$theme_path/theme/", 
							'prefix-'	=> "styles/$theme_path/", 
							'prefix+'	=> false, 
							'exclude'	=> ($css_storedb) ? 'stylesheet.cfg,theme.cfg' : 'theme.cfg'
						), 
						array(
							'src'		=> "styles/$imageset_path/imageset/", 
							'prefix-'	=> "styles/$imageset_path/", 
							'prefix+'	=> false, 
							'exclude'	=> 'imageset.cfg'
						),
					);

					$data = array(
						array(
							'src'		=> trim($style_cfg), 
							'prefix'	=> 'style.cfg'
						), 
						array(
							'src'		=> trim($theme_cfg), 
							'prefix'	=> 'theme/theme.cfg'
						), 
						array(
							'src'		=> trim($template_cfg), 
							'prefix'	=> 'template/template.cfg'
						), 
						array(
							'src'		=> trim($imageset_cfg), 
							'prefix'	=> 'imageset/imageset.cfg'
						), 
					);

					if ($css_storedb)
					{
						$data += array(
							array(
								'src' => $css_data, 
								'prefix' => 'theme/stylesheet.css'
							),
						);
					}

					unset($css_data);
					unset($style_cfg);
					unset($theme_cfg);
					unset($template_cfg);
					unset($imageset_cfg);

					export('style', $style_id, $style_name, str_replace(' ', '_', $style_name), $files, $data);
				}
				break;

			case 'add':
			case 'edit':
			case 'install':

				if (isset($_POST['update']))
				{
					if ($action == 'install')
					{
						$style_path = htmlspecialchars($_POST['style_path']);
						if (!($cfg = file("{$phpbb_root_path}styles/$style_path/style.cfg")))
						{
							trigger_error($user->lang['NO_STYLE']);
						}

						$style_name = trim($cfg[0]);
						$style_copyright = trim($cfg[1]);

						// Imageset
						if (!($cfg = file("{$phpbb_root_path}styles/$style_path/imageset/imageset.cfg")))
						{
							trigger_error($user->lang['NO_IMAGESET']);
						}
						$imageset_name = trim($cfg[0]);
						$imageset_copyright = trim($cfg[1]);

						$sql = 'SELECT imageset_id, imageset_name 
							FROM ' . STYLES_IMAGE_TABLE . " 
							WHERE imageset_name = '" . $db->sql_escape($imageset_name) . "'";
						$result = $db->sql_query($sql);

						// If an entry already exists we need not install this one
						if (!($row = $db->sql_fetchrow($result)))
						{
							if (empty($imageset_name))
							{
								$error[] = $user->lang['IMAGESET_ERR_STYLE_NAME'];
							}

							if (strlen($imageset_name) > 30)
							{
								$error[] = $user->lang['IMAGESET_ERR_NAME_LONG'];
							}

							if (!preg_match('#^[a-z0-9_\-\+\. ]+$#i', $imageset_name))
							{
								$error[] = $user->lang['IMAGESET_ERR_NAME_CHARS'];
							}

							if (strlen($imageset_copyright) > 60)
							{
								$error[] = $user->lang['IMAGESET_ERR_COPY_LONG'];
							}

							if (!sizeof($error))
							{
								$sql_img_ary = array();

								for ($i = 3; $i < sizeof($cfg); $i++)
								{
									$tmp = explode('||', $cfg[$i]);
									$sql_img_ary[$tmp[0]] = str_replace('{PATH}', "styles/$style_path/imageset/", trim($tmp[1]));
								}

								$sql_ary = array(
									'imageset_name'			=> $imageset_name,
									'imageset_copyright'	=> $imageset_copyright, 
									'imageset_path'			=> str_replace(' ', '_', $style_path), 
								);
								$sql_ary += $sql_img_ary;

								$sql = 'INSERT INTO ' . STYLES_IMAGE_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
								$db->sql_query($sql);
								$imageset_id = $db->sql_nextid();

								add_log('admin', 'LOG_ADD_IMAGESET', $imageset_name);
							}
						}
						else
						{
							$imageset_id = $row['imageset_id'];
						}
						$db->sql_freeresult($result);
						unset($cfg);

						// Theme
						if (!($cfg = file("{$phpbb_root_path}styles/$style_path/theme/theme.cfg")))
						{
							trigger_error($user->lang['NO_THEME']);
						}
						$theme_name = trim($cfg[0]);
						$theme_copyright = trim($cfg[1]);

						$sql = 'SELECT theme_id, theme_name 
							FROM ' . STYLES_CSS_TABLE . " 
							WHERE theme_name = '" . $db->sql_escape($theme_name) . "'";
						$result = $db->sql_query($sql);

						// If an entry already exists we need not install this one
						if (!($row = $db->sql_fetchrow($result)))
						{
							if (empty($theme_name))
							{
								$error[] = $user->lang['THEME_ERR_STYLE_NAME'];
							}

							if (strlen($theme_name) > 30)
							{
								$error[] = $user->lang['THEME_ERR_NAME_LONG'];
							}

							if (!preg_match('#^[a-z0-9_\-\+\. ]+$#i', $theme_name))
							{
								$error[] = $user->lang['THEME_ERR_NAME_CHARS'];
							}

							if (strlen($theme_copyright) > 60)
							{
								$error[] = $user->lang['THEME_ERR_COPY_LONG'];
							}

							if (!sizeof($error))
							{
								$css_storedb = 0;
								$css_data = '';
								if (!is_writeable("{$phpbb_root_path}styles/$style_path/theme/stylesheet.css"))
								{
									$css_data = implode('', file("{$phpbb_root_path}styles/$style_path/theme/stylesheet.css"));
									$css_storedb = 1;
								}

								$sql_ary = array(
									'theme_name'		=> $theme_name,
									'theme_copyright'	=> $theme_copyright, 
									'theme_path'		=> str_replace(' ', '_', $style_path), 
									'css_storedb'		=> $css_storedb, 
									'css_data'			=> $css_data, 
								);

								$sql = 'INSERT INTO ' . STYLES_CSS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
								$db->sql_query($sql);
								$theme_id = $db->sql_nextid();

								$log = ($css_storedb) ? 'LOG_ADD_THEME_DB' : 'LOG_ADD_THEME_FS';
								add_log('admin', $log, $theme_name);
							}
						}
						else
						{
							$theme_id = $row['theme_id'];
						}
						$db->sql_freeresult($result);
						unset($cfg);


						// Template
						if (!($cfg = file("{$phpbb_root_path}styles/$style_path/template/template.cfg")))
						{
							trigger_error($user->lang['NO_TEMPLATE']);
						}
						$template_name = trim($cfg[0]);
						$template_copyright = trim($cfg[1]);

						$sql = 'SELECT template_id, template_name 
							FROM ' . STYLES_TPL_TABLE . " 
							WHERE template_name = '" . $db->sql_escape($template_name) . "'";
						$result = $db->sql_query($sql);

						// If an entry already exists we need not install this one
						if (!($row = $db->sql_fetchrow($result)))
						{
							if (empty($template_name))
							{
								$error[] = $user->lang['TPL_ERR_STYLE_NAME'];
							}

							if (strlen($template_name) > 30)
							{
								$error[] = $user->lang['TPL_ERR_NAME_LONG'];
							}

							if (!preg_match('#^[a-z0-9_\-\+\. ]+$#i', $template_name))
							{
								$error[] = $user->lang['TPL_ERR_NAME_CHARS'];
							}

							if (strlen($template_copyright) > 60)
							{
								$error[] = $user->lang['TPL_ERR_COPY_LONG'];
							}

							if (!sizeof($error))
							{
								$sql_ary = array(
									'template_name'		=> $template_name,
									'template_copyright'	=> $template_copyright, 
									'template_path'		=> str_replace(' ', '_', $style_path), 
								);

								$sql = 'INSERT INTO ' . STYLES_TPL_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
								$db->sql_query($sql);
								$template_id = $db->sql_nextid();

								$log = 'LOG_ADD_TPL_FS';
								add_log('admin', $log, $template_name);
							}
						}
						else
						{
							$template_id = $row['template_id'];
						}
						$db->sql_freeresult($result);
						unset($cfg);
					}
					else
					{
						$style_name = (isset($_POST['style_name'])) ? stripslashes(htmlspecialchars($_POST['style_name'])) : '';
						$style_copyright = (isset($_POST['style_copyright'])) ? stripslashes(htmlspecialchars($_POST['style_copyright'])) : '';

						$template_id = (!empty($_POST['template_id'])) ? intval($_POST['template_id']) : 0;
						$theme_id = (!empty($_POST['theme_id'])) ? intval($_POST['theme_id']) : 0;
						$imageset_id = (!empty($_POST['imageset_id'])) ? intval($_POST['imageset_id']) : 0;
					}

					$style_active = (!empty($_POST['style_active'])) ? 1 : 0;
					$style_default = (!empty($_POST['default_style'])) ? 1 : 0;

					if (empty($style_name))
					{
						$error[] = $user->lang['STYLE_ERR_STYLE_NAME'];
					}

					if (strlen($style_name) > 30)
					{
						$error[] = $user->lang['STYLE_ERR_NAME_LONG'];
					}

					if (!preg_match('#^[a-z0-9_\-\+\. ]+$#i', $style_name))
					{
						$error[] = $user->lang['STYLE_ERR_NAME_CHARS'];
					}

					if (strlen($style_copyright) > 60)
					{
						$error[] = $user->lang['STYLE_ERR_COPY_LONG'];
					}

					if (!$template_id || !$theme_id || !$imageset_id)
					{
						$error[] = $user->lang['STYLE_ERR_NO_IDS'];
					}

					$sql_where = ($action == 'add' || $action == 'install') ? "WHERE style_name = '" . $db->sql_escape($style_name) . "'" : "WHERE style_id <> $style_id AND style_name = '" . $db->sql_escape($style_name) . "'";
					$sql = 'SELECT style_name 
						FROM ' . STYLES_TABLE . " 
						$sql_where";
					$result = $db->sql_query($sql);

					if ($row = $db->sql_fetchrow($result))
					{
						$error[] = $user->lang['STYLE_ERR_NAME_EXIST'];
					}
					$db->sql_freeresult($result);

					if (!sizeof($error))
					{
						$sql_ary = array(
							'style_name'		=> $style_name, 
							'style_copyright'	=> $style_copyright, 
							'template_id'		=> $template_id, 
							'theme_id'			=> $theme_id, 
							'imageset_id'		=> $imageset_id, 
						);

						$sql = ($action == 'add' || $action == 'install') ? 'INSERT INTO ' . STYLES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary) : 'UPDATE ' . STYLES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE style_id = $style_id";
						$db->sql_query($sql);

						if ($action == 'add' || $action == 'install')
						{
							$style_id = $db->sql_nextid();
						}

						if ($style_default)
						{
							set_config('default_style', $style_id);
						}

						$log = ($action == 'add' || $action == 'install') ? 'LOG_ADD_STYLE' : 'LOG_EDIT_STYLE';
						add_log('admin', $log, $style_name);

						$message = ($action == 'add' || $action == 'install') ? 'STYLED_ADDED' : 'STYLE_EDITED';
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
							trigger_error($user->lang['NO_STYLE']);
						}
						$db->sql_freeresult($result);

						$style_default = ($config['default_style'] == $style_id) ? 1 : 0;
					}
					else
					{
						if ($action == 'install' && !empty($_GET['name']))
						{
							$style_path = htmlspecialchars($_GET['name']);
							if (!file_exists("{$phpbb_root_path}styles/$style_path/style.cfg"))
							{
								trigger_error($user->lang['NO_STYLE']);
							}

							if (!($cfg = file("{$phpbb_root_path}styles/$style_path/style.cfg")))
							{
								trigger_error($user->lang['NO_STYLE']);
							}

							$style_version = preg_replace('#^2\.([0-9]+?)\.([0-9]+?).*?$#', '\1.\2', trim($cfg[2]));
							$phpbbversion = preg_replace('#^2\.([0-9]+?)\.([0-9]+?).*?$#', '\1.\2', $config['version']);

							if ($style_version != $phpbbversion)
							{
								$error[] = $user->lang['STYLE_VERSION_DIFF'];
							}

							$style_name = trim($cfg[0]);
							$style_copyright = trim($cfg[1]);

							if (!($cfg = file("{$phpbb_root_path}styles/$style_path/imageset/imageset.cfg")))
							{
								trigger_error($user->lang['NO_IMAGESET']);
							}
							$imageset_name = trim($cfg[0]);
							unset($cfg);

							if (!($cfg = file("{$phpbb_root_path}styles/$style_path/theme/theme.cfg")))
							{
								trigger_error($user->lang['NO_THEME']);
							}
							$theme_name = trim($cfg[0]);
							unset($cfg);

							if (!($cfg = file("{$phpbb_root_path}styles/$style_path/template/template.cfg")))
							{
								trigger_error($user->lang['NO_TEMPLATE']);
							}
							$template_name = trim($cfg[0]);
							unset($cfg);

							$style_active = 1;
							$s_hidden_fields = '<input type="hidden" name="style_path" value="' . $style_path . '" />';
						}
						else
						{
							$style_name = (isset($_POST['style_name'])) ? stripslashes(htmlspecialchars($_POST['style_name'])) : '';
							$style_copyright = '';
							$style_active = 1;
							$template_id = $theme_id = $imageset_id = $style_default = 0;
						}
					}
				}

				if ($action != 'install')
				{
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
				}

				$active_yes = ($style_active) ? ' checked="checked"' : '';
				$active_no = (!$style_active) ? ' checked="checked"' : '';
				$style_default_yes = ($style_default) ? ' checked="checked"' : '';
				$style_default_no = (!$style_default) ? ' checked="checked"' : '';


				// Output the page
				adm_page_header($user->lang['EDIT_STYLE']);

?>

<h1><?php echo $user->lang['EDIT_STYLE']; ?></h1>

<p><?php echo $user->lang['EDIT_STYLE_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$style_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['EDIT_STYLE']; ?></th>
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
		<td class="row1"><b><?php echo $user->lang['STYLE_NAME']; ?>:</b></td>
		<td class="row2"><input class="post" type="text" name="style_name" maxlength="30" size="30" value="<?php echo $style_name; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['STYLE_COPYRIGHT']; ?>:</b></td>
		<td class="row2"><?php
	
				echo ($action == 'edit' || $action == 'install') ? '<b>' . $style_copyright . '</b>' : '<input class="post" type="text" name="style_copyright" maxlength="60" size="30" value="' . $style_copyright . '" />';

?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['STYLE_TEMPLATE']; ?>:</b></td>
		<td class="row2"><?php
	
		echo ($action == 'install') ? "<b>$template_name</b>" : '<select name="template_id">' . $template_options . '</select>';

?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['STYLE_THEME']; ?>:</b></td>
		<td class="row2"><?php
	
		echo ($action == 'install') ? "<b>$theme_name</b>" : '<select name="theme_id">' . $theme_options . '</select>';

?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['STYLE_IMAGESET']; ?>:</b></td>
		<td class="row2"><?php
	
		echo ($action == 'install') ? "<b>$imageset_name</b>" : '<select name="imageset_id">' . $imageset_options . '</select>';

?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['STYLE_ACTIVE']; ?>:</b></td>
		<td class="row2"><input type="radio" name="style_active" value="1"<?php echo $active_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="style_active" value="0"<?php echo $active_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

				if ($style_id != $config['default_style'])
				{

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['STYLE_DEFAULT']; ?>:</b></td>
		<td class="row2"><input type="radio" name="default_style" value="1"<?php echo $style_default_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="default_style" value="0"<?php echo $style_default_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<!-- input class="btnlite" type="submit" name="preview" value="<?php echo $user->lang['PREVIEW']; ?>" />&nbsp;&nbsp;--><input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
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

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th nowrap="nowrap"><?php echo $user->lang['STYLE_NAME']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['STYLE_USED_BY']; ?></th>
		<th nowrap="nowrap" colspan="4"><?php echo $user->lang['OPTIONS']; ?></th>
	</tr>
	<tr>
		<td class="row3" colspan="6"><b><?php echo $user->lang['INSTALLED_STYLE']; ?></b></td>
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

		$installed = array();
		$basis_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = strtolower($row['style_name']);
			$basis_options .= '<option value="' . $row['style_id'] . '">' . $row['style_name'] . '</option>';

			$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

			$stylevis = (!$row['style_active']) ? 'activate' : 'deactivate';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;id=" . $row['style_id']; ?>"><?php echo $row['style_name']; ?></a><?php echo ($config['default_style'] == $row['style_id']) ? ' *' : ''; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo (!empty($style_count[$row['style_id']])) ? $style_count[$row['style_id']] : '0'; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$stylevis&amp;id=" . $row['style_id']; ?>"><?php echo $user->lang['STYLE_' . strtoupper($stylevis)]; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;id=" . $row['style_id']; ?>"><?php echo $user->lang['DELETE']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=export&amp;id=" . $row['style_id']; ?>"><?php echo $user->lang['EXPORT']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "{$phpbb_root_path}index.$phpEx$SID&amp;style=" . $row['style_id']; ?>" target="_stylepreview"><?php echo $user->lang['PREVIEW']; ?></a>&nbsp;</td>
	</tr>
<?php

		}
		$db->sql_freeresult($result);

?>
	<tr>
		<td class="row3" colspan="6"><b><?php echo $user->lang['UNINSTALLED_STYLE']; ?></b></td>
	</tr>
<?php

	$new_ary = $cfg = array();
	$dp = opendir("{$phpbb_root_path}styles/");
	while ($file = readdir($dp))
	{
		if ($file{0} != '.' && file_exists("{$phpbb_root_path}styles/$file/style.cfg"))
		{
			if ($cfg = file("{$phpbb_root_path}styles/$file/style.cfg"))
			{
				$name = trim($cfg[0]);
				if (!in_array(strtolower($name), $installed))
				{
					$new_ary[$i]['path'] = $file;
					$new_ary[$i]['name'] = $name;
				}
			}
		}
	}
	unset($installed);
	@closedir($dp);

	if (sizeof($new_ary))
	{
		foreach ($new_ary as $key => $cfg)
		{

?>
	<tr>
		<td class="row1"><?php echo $cfg['name']; ?></td>
		<td class="row1" colspan="5" align="center"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=install&amp;name=" . urlencode($cfg['path']); ?>"><?php echo $user->lang['INSTALL']; ?></a></td>
	</tr>
<?php

		}
	}
	else
	{

?>
	<tr>
		<td class="row1" colspan="6" align="center"><?php echo $user->lang['NO_UNINSTALLED_STYLE']; ?></td>
	</tr>
<?php

	}
	unset($new_ary);

?>
<tr>
		<td class="cat" colspan="6" align="right"><?php echo $user->lang['CREATE_STYLE']; ?>: <input class="post" type="text" name="style_name" value="" maxlength="30" size="25" /> <input class="btnmain" type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table></form>
<?php 

		adm_page_footer();
		break;

	// IMAGESETS
	case 'imagesets':
		$imageset_id = (isset($_REQUEST['id'])) ? intval($_REQUEST['id'])  : 0;

		switch ($action)
		{
			case 'export':
				if ($imageset_id)
				{
					$sql = 'SELECT * 
						FROM ' . STYLES_IMAGE_TABLE . "
						WHERE imageset_id = $imageset_id";
					$result = $db->sql_query($sql);

					if (!($row = ($db->sql_fetchrow($result))))
					{
						trigger_error($user->lang['NO_IMAGESET']);
					}
					$db->sql_freeresult($result);

					$imageset_name = $row['imageset_name'];
					$imageset_path = $row['imageset_path'];
					$imageset_copyright = $row['imageset_copyright'];
					unset($row['imageset_name']);
					unset($row['imageset_path']);
					unset($row['imageset_copyright']);
					unset($row['imageset_id']);

					$cfg  = addslashes($imageset_name) . "\n";
					$cfg .= addslashes($imageset_copyright) . "\n";
					$cfg .= addslashes($config['version']) . "\n";
					
					foreach (array_keys($row) as $key)
					{
						$cfg.= $key . '||' . str_replace("styles/$imageset_path/imageset/", '{PATH}', $row[$key]) . "\n";
						unset($row[$key]);
					}

					$files = array(array('src' => "styles/$imageset_path/imageset/", 'prefix-' => "styles/$imageset_path/", 'prefix+' => false, 'exclude' => 'imageset.cfg'));
					$data = array(array('src' => trim($cfg), 'prefix' => "imageset/imageset.cfg"));

					export('imageset', $imageset_id, $imageset_name, $imageset_path, $files, $data);
				}
				break;

			case 'delete':
				if ($imageset_id)
				{
					$sql = 'SELECT imageset_id, imageset_name, imageset_path  
						FROM ' . STYLES_IMAGE_TABLE . "
						WHERE imageset_id = $imageset_id";
					$result = $db->sql_query($sql);

					if (!(extract($db->sql_fetchrow($result))))
					{
						trigger_error($user->lang['NO_IMAGESET']);
					}
					$db->sql_freeresult($result);

					remove('imageset', $imageset_id, $imageset_name, $imageset_path);
				}
				break;

			case 'add':
			case 'details':
			case 'install':
				details('imageset', $mode, $action, $imageset_id);
				exit;
				break;

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
								$test_ary[] = preg_replace('#^"styles/' . $imageset_path . '/imageset/(\{LANG\}/)?(.*?)".*$#', '\2', $$img);
							}
						}
					}

					$dp = @opendir("{$phpbb_root_path}styles/$imageset_path/imageset/");
					while ($file = readdir($dp))
					{
						if (is_file("{$phpbb_root_path}styles/$imageset_path/imageset/$file"))
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
				$imagesetlist = filelist("{$phpbb_root_path}styles/$imageset_path/imageset");

				$imagesetlist_options = '';
				foreach ($imagesetlist as $path => $img_ary)
				{
					foreach ($img_ary as $img)
					{
						$img = substr($path, 1) . (($path != '') ? '/' : '') . $img; 

						$selected = (preg_match('#' . preg_quote($img) . '$#', $background_image)) ? ' selected="selected"' : '';
						$imagesetlist_options .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
					}
				}
				$imagesetlist_options = '<option value=""' . (($edit_img == '') ? ' selected="selected"' : '') . '>' . $user->lang['NONE'] . '</option>' . $imagesetlist_options;
				unset($imagesetlist);


				adm_page_header($user->lang['EDIT_IMAGESET']);

?>

<h1><?php echo $user->lang['EDIT_IMAGESET']; ?></h1>

<p><?php echo $user->lang['EDIT_IMAGESET_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;id=$imageset_id&amp;action=$action"; ?>"><table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_CLASS']; ?>: <select name="imgname" onchange="this.form.submit(); "><?php echo $img_options; ?></select>&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2"><?php echo $user->lang['EDIT_IMAGESET']; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="2" align="center"><?php echo (!empty($$imgname)) ? '<img src=' . str_replace('"styles/', '"../styles/', str_replace('{LANG}', $user->img_lang, $$imgname)) . ' vspace="5" />' : ''; ?></td>
			</tr>
			<tr>
				<th width="40%"><?php echo $user->lang['IMAGE_PARAMETER']; ?></th>
				<th><?php echo $user->lang['IMAGE_VALUE']; ?></th>
			</tr>
			<tr>
				<td class="row1" width="40%"><b><?php echo $user->lang['IMAGE']; ?>:</b></td>
				<td class="row2"><select name="imgpath"><?php echo $imagesetlist_options; ?></select></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b><?php echo $user->lang['DIMENSIONS']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['DIMENSIONS_EXPLAIN']; ?></span></td>
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

		// Front page
		front('imageset', array('details', 'delete', 'export'));
		break;


	// TEMPLATES
	case 'templates':
		$template_id = (isset($_REQUEST['id'])) ? $_REQUEST['id']  : false;

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

		// Lights, Camera ...
		switch ($action)
		{
			case 'preview':
				break;

			case 'refresh':
				if ($template_id)
				{
					$sql = 'SELECT template_path, template_storedb
						FROM ' . STYLES_TPL_TABLE . " 
						WHERE template_id = $template_id";
					$result = $db->sql_query($sql);

					if (!extract($db->sql_fetchrow($result)))
					{
						trigger_error($user->lang['NO_TEMPLATE']);
					}
					$db->sql_freeresult($result);

					if ($template_storedb && file_exists("{$phpbb_root_path}styles/$template_path/template/"))
					{
						$filelist = array('/' => array());

						$sql = 'SELECT template_filename, template_mtime 
							FROM ' . STYLES_TPLDATA_TABLE . "
							WHERE template_id = $template_id";
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							if (@filemtime("{$phpbb_root_path}styles/$template_path/template/" . $row['template_filename']) > $row['template_mtime'])
							{
								$filelist['/'][] = $row['template_filename'];
							}
						}
						$db->sql_freeresult($result);

						store_templates('update', $template_id, $template_path, $filelist);
						unset($filelist);
					}
				}
				break;

			case 'delete':
				if ($template_id)
				{
					$sql = 'SELECT template_id, template_name, template_path, template_storedb  
						FROM ' . STYLES_TPL_TABLE . "
						WHERE template_id = $template_id";
					$result = $db->sql_query($sql);

					if (!(extract($db->sql_fetchrow($result))))
					{
						trigger_error($user->lang['NO_TEMPLATE']);
					}
					$db->sql_freeresult($result);

					if ($template_storedb)
					{
						$sql = 'DELETE FROM ' . STYLES_TPLDATA_TABLE . " 
							WHERE template_id = $template_id";
						$db->sql_query($sql);
					}

					remove('template', $template_id, $template_name, $template_path, $template_storedb);
				}
				break;

			case 'export':
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

					$cfg  = addslashes($template_name) . "\n";
					$cfg .= addslashes($template_copyright) . "\n";
					$cfg .= addslashes($config['version']) . "\n";
					$cfg .= addslashes($bbcode_bitfield) . "\n";

					if ($template_storedb)
					{
						// TODO
						// eek
					}
					else
					{
						$files = array(array('src' => "styles/$template_path/template/", 'prefix-' =>  "styles/$template_path/", 'prefix+' => false, 'exclude' => 'template.cfg'));
						$data = array(array('src' => trim($cfg), 'prefix' => 'template/template.cfg'));
					}

					export('template', $template_id, $template_name, $template_path, $files, $data);
				}
				break;

			case 'cache':
				$source = (!empty($_GET['source'])) ? htmlspecialchars($_GET['source']) : '';
				$file_ary = (!empty($_POST['delete'])) ? array_map('htmlspecialchars', $_POST['delete']) : '';

				$sql = 'SELECT * 
					FROM ' . STYLES_TPL_TABLE . "
					WHERE template_id = $template_id";
				$result = $db->sql_query($sql);

				if (!(extract($db->sql_fetchrow($result))))
				{
					trigger_error($user->lang['NO_TEMPLATE']);
				}
				$db->sql_freeresult($result);

				$cache_prefix = "tpl_$template_path";

				// User wants to delete one or more files ... 
				if ($_POST['update'] && $file_ary)
				{
					foreach ($file_ary as $file)
					{
						$file = "{$phpbb_root_path}cache/{$cache_prefix}_$file.html.$phpEx";
						if (file_exists($file) && is_file($file))
						{
							@unlink($file);
						}
					}
					unset($file_ary);

					add_log('admin', 'LOG_CLEAR_TPLCACHE', $template_name);
					trigger_error($user->lang['TEMPLATE_CACHE_CLEARED']);
				}

				// Someone wants to see the cached source ... so we'll highlight it, 
				// add line numbers and indent it appropriately. This could be nasty
				// on larger source files ...
				if ($source && file_exists("{$phpbb_root_path}cache/{$cache_prefix}_$source.html.$phpEx"))
				{

					adm_page_header($user->lang['TEMPLATE_CACHE']);

?>

<h1><?php echo $_GET['source']; ?></h1>

<?php

					$marker = time();
					$code = implode("$marker", file("{$phpbb_root_path}cache/{$cache_prefix}_$source.html.$phpEx"));

					$conf = array('highlight.bg', 'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string');
					foreach ($conf as $ini_var)
					{
						ini_set($ini_var, str_replace('highlight.', 'syntax', $ini_var));
					}

					ob_start();
					highlight_string($code);
					$code = ob_get_contents();
					ob_end_clean();

					$str_from = array('<font color="syntax', '</font>', '<code>', '</code>','[', ']', '.');
					$str_to = array('<span class="syntax', '</span>', '', '', '&#91;', '&#93;', '&#46;');

					if ($remove_tags)
					{
						$str_from[] = '<span class="syntaxdefault">&lt;?php&nbsp;</span>';
						$str_to[] = '';
						$str_from[] = '<span class="syntaxdefault">&lt;?php&nbsp;';
						$str_to[] = '<span class="syntaxdefault">';
						$str_from[] = '<span class="syntaxdefault">?&gt;</span>';
						$str_to[] = '';
					}

					$code = str_replace($str_from, $str_to, $code);
					$code = preg_replace('#^(<span class="[a-z_]+">)\n?(.*?)\n?(</span>)$#is', '\1\2\3', $code);
					$code = explode("$marker", $code);

?>

<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
<?php 
	
					$i = $j = 1;
					$length = strlen(sizeof($code));
					$indent = str_repeat('&nbsp;', $length);
					foreach ($code as $key => $line)
					{

?>
	<tr valign="top">
		<td class="sourcenum" align="right"><?php echo $i; ?>&nbsp;&nbsp;</td>
		<td class="source"><?php

						echo $indent . $line;
						$i++;
						if (strlen($i) > $j)
						{
							$indent = substr($indent, 0, -6);
							$j++;
						}
						unset($code[$key]);

?></td>
	</tr>
<?php

					}

?>
</table>

<br clear="all" />

<?php

					adm_page_footer();
				}

				if ($template_storedb)
				{
					$sql = 'SELECT template_filename, template_mtime 
						FROM ' . STYLES_TPLDATA_TABLE . " 
						WHERE template_id = $template_id";
					$result = $db->sql_query($sql);

					$filemtime = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$filemtime[$row['template_filename']] = $row['template_mtime'];
					}
					$db->sql_freeresult($result);
				}

				// Open the cache directory and grab a list of the relevant cached templates.
				// We also grab some other details such as when the compiled template was
				// created, when the original template was modified and the cached filesize
				if (!($dp = @opendir("{$phpbb_root_path}cache")))
				{
					trigger_error($user->lang['ERR_TPLCACHE_READ']);
				}

				$tplcache_ary = array();
				while ($file = readdir($dp))
				{
					if (is_file($phpbb_root_path . 'cache/' . $file) && strstr($file, $cache_prefix))
					{
						$filename = preg_replace('#^' . $cache_prefix . '_(.*?)\.html\.' . $phpEx . '$#i', '\1', $file);
						$tplcache_ary[$filename]['cache'] = filemtime("{$phpbb_root_path}cache/$file");
						$tplcache_ary[$filename]['size'] = filesize("{$phpbb_root_path}cache/$file");
						$tplcache_ary[$filename]['src'] = (!$template_storedb) ? filemtime("{$phpbb_root_path}styles/$template_path/template/$filename.html") : $filemtime[$filename . '.html'] ;
					}
				}
				closedir($dp);


				// Output the page
				adm_page_header($user->lang['TEMPLATE_CACHE']);

?>

<script language="Javascript" type="text/javascript">
<!--
function marklist(match, status)
{
	len = eval('document.' + match + '.length');
	for (i = 0; i < len; i++)
	{
		eval('document.' + match + '.elements[i].checked = ' + status);
	}
}

function viewsource(url)
{
	window.open(url, '_source', 'HEIGHT=550,resizable=yes,scrollbars=yes,WIDTH=750');
	return false;
}

//-->
</script>

<h1><?php echo $user->lang['TEMPLATE_CACHE']; ?></h1>

<p><?php echo $user->lang['TEMPLATE_CACHE_EXPLAIN']; ?></p>

<form name="tplcache" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$template_id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th nowrap="nowrap"><?php echo $user->lang['CACHE_FILENAME']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['CACHE_FILESIZE']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['CACHE_CACHED']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['CACHE_MODIFIED']; ?></th>
		<th width="1%"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

				if (sizeof($tplcache_ary))
				{
					foreach ($tplcache_ary as $filename => $times_ary)
					{
						$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$template_id&amp;source=$filename"; ?>" onclick="viewsource('<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$template_id&amp;source=$filename"; ?>');return false"><?php echo $filename; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo sprintf('%.1f KB', $times_ary['size'] / 1024); ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo $user->format_date($times_ary['cache']); ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo $user->format_date($times_ary['src']); ?></td>
		<td class="<?php echo $row_class; ?>" width="1%" align="center"><input type="checkbox" name="delete[]" value="<?php echo $filename; ?>" /></td>
	</tr>
<?php

					}
				}
				else
				{

?>
	<tr>
		<td class="row1" colspan="5" align="center"><?php echo $user->lang['NO_CACHED_TPL_FILES']; ?></td>
	</tr>
<?php

				}

?>
	<tr>
		<td class="cat" colspan="5" align="right"><input class="btnlite" type="submit" name="update" value="<?php echo $user->lang['DELETE_MARKED']; ?>" /></td>
	</tr>
</table>

<table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><b><span class="gensmall"><a href="javascript:marklist('tplcache', true);" class="gensmall"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('tplcache', false);" class="gensmall"><?php echo $user->lang['UNMARK_ALL']; ?></a></span></b></td>
	</tr>
</table></form>
<?php

				adm_page_footer();
				break;

			case 'add':
			case 'details':
			case 'install':
				details('template', $mode, $action, $template_id);
				exit;
				break;

			case 'edit':
				$tplcols = (isset($_POST['tplcols'])) ? max(20, intval($_POST['tplcols'])) : 80;
				$tplrows = (isset($_POST['tplrows'])) ? max(5, intval($_POST['tplrows'])) : 20;
				$tplname = (isset($_POST['tplname'])) ? $_POST['tplname']  : '';
				$tpldata = (!empty($_POST['tpldata'])) ? stripslashes($_POST['tpldata']) : ''; // NB : STRIPSLASHED!

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

					// User wants to submit data ...
					if (!empty($_POST['update']))
					{
						// Where is the template stored?
						if (!$template_storedb && is_writeable("{$phpbb_root_path}styles/$template_path/template/$tplname"))
						{
							// Grab template data
							if (!($fp = fopen("{$phpbb_root_path}styles/$template_path/template/$tplname", 'wb')))
							{
								trigger_error($user->lang['NO_TEMPLATE']);
							}
							$stylesheet = fwrite($fp, stripslashes($stylesheet));
							fclose($fp);
						}
						else
						{
							$db->sql_transaction('begin');

							if (!$template_storedb)
							{
								// We change the path to one relative to the root rather than the theme folder
								$sql = 'UPDATE ' . STYLES_TPL_TABLE . ' 
									SET template_storedb = 1 
									WHERE template_id = ' . $template_id;
								$db->sql_query($sql);

								$filelist = filelist("{$phpbb_root_path}styles/$template_path/template");
								$filelist = array('/template' => $filelist['']);
								store_templates('insert', $template_id, $template_path, $filelist);
							}

							$sql = 'UPDATE ' . STYLES_TPLDATA_TABLE . " 
								SET template_data = '" . $db->sql_escape($tpldata) . "', template_mtime = " . time() . " 
								WHERE template_id = $template_id 
									AND template_filename = '" . $db->sql_escape($tplname) . "'";
							$db->sql_query($sql);

							$db->sql_transaction('commit');
						}

						@unlink("{$phpbb_root_path}cache/tpl_{$template_name}_$tplname.$phpEx");

						$error[] = $user->lang['TEMPLATE_UPDATED'];
						add_log('admin', 'LOG_EDIT_TEMPLATE', $template_name, $tplname);
					}

					$test_ary = array();
					foreach ($tpllist as $category => $tpl_ary)
					{
						$test_ary = array_merge($test_ary, $tpl_ary);
					}

					if (!$template_storedb)
					{
						$dp = @opendir("{$phpbb_root_path}styles/$template_path/template");
						while ($file = readdir($dp))
						{
							if (!strstr($file, 'bbcode.') && strstr($file, '.html') && !in_array($file, $test_ary) &&  is_file("{$phpbb_root_path}styles/$template_path/template/$file"))
							{
								$tpllist['custom'][] = $file;
							}
						}
						closedir($dp);
						unset($matches);
						unset($test_ary);

						if ($tplname && !$tpldata)
						{
							if (!($fp = fopen("{$phpbb_root_path}styles/$template_path/template/$tplname", 'r')))
							{
								trigger_error($user->lang['NO_TEMPLATE']);
							}
							$tpldata = fread($fp, filesize("{$phpbb_root_path}styles/$template_path/template/$tplname"));
							fclose($fp);
						}

					}
					else
					{
						$sql = 'SELECT * 
							FROM ' . STYLES_TPLDATA_TABLE . " 
							WHERE template_id = $template_id";
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							if (!strstr($row['template_filename'], 'bbcode.') && !in_array($row['template_filename'], $test_ary))
							{
								$tpllist['custom'][] = $row['template_filename'];
							}

							if ($row['template_filename'] == $tplname && !$tpldata)
							{
								$tpldata = $row['template_data'];
							}
						}
						$db->sql_freeresult($result);
					}

					// List of included templates
					if ($tplname)
					{
						preg_match_all('#<!\-\- INCLUDE (.*?) \-\->#', $tpldata, $included_tpls);
						$included_tpls = $included_tpls[1];
					}
				}
				unset($test_ary);

				// Generate list of template options
				$tpl_options = '';
				ksort($tpllist);
				foreach ($tpllist as $category => $tpl_ary)
				{
					sort($tpl_ary);
					$tpl_options .= '<option class="sep">' . $category . '</option>';

					foreach ($tpl_ary as $tpl_file)
					{
						$selected = ($tpl_file == $tplname) ? ' selected="selected"' : '';
						$tpl_options .= '<option value="' . $tpl_file . '"' . $selected . '>' . (($category == 'custom') ? $tpl_file : $tpl_file) . '</option>';
					}
				}


				// Output page
				adm_page_header($user->lang['EDIT_TEMPLATE']);

?>

<h1><?php echo $user->lang['EDIT_TEMPLATE']; ?></h1>

<p><?php echo $user->lang['EDIT_TEMPLATE_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;id=$template_id&amp;action=$action"; ?>"><table cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_TEMPLATE']; ?>: <select name="tplname" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $tpl_options; ?></select>&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat"><?php echo $user->lang['TEXT_COLUMNS']; ?>: <input class="post" type="text" name="tplcols" size="3" maxlength="3" value="<?php echo $tplcols; ?>" /> &nbsp;<?php echo $user->lang['TEXT_ROWS']; ?>: <input class="post" type="text" name="tplrows" size="3" maxlength="3" value="<?php echo $tplrows; ?>" />&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['UPDATE']; ?>" /></td>
			</tr>
			<tr>
				<th><?php echo $user->lang['RAW_HTML']; ?></th>
			</tr>
<?php

				if (sizeof($error))
				{


?>
			<tr>
				<td class="row3" align="center"><?php echo implode('<br />', $error); ?></td>
			</tr>
<?php

				}

?>
			<tr>
				<td class="row2" align="center"><textarea class="post" style="font-family:'Courier New', monospace;font-size:9pt;line-height:125%;" cols="<?php echo $tplcols; ?>" rows="<?php echo $tplrows; ?>" name="tpldata"><?php echo htmlentities($tpldata); ?></textarea></td>
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
		}


		// Front page
		front('template', array('cache', 'details', 'refresh', 'export', 'delete', 'preview'));
		break;


	// THEMES
	case 'themes':
		$theme_id = (isset($_REQUEST['id'])) ? intval($_REQUEST['id'])  : false;

		switch ($action)
		{
			case 'preview':
				break;

			case 'add':
			case 'details':
			case 'install':
				details('theme', $mode, $action, $theme_id);
				exit;
				break;

			case 'edit':
				// General parameters
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
					'images'	=> 'url(\'./%s\')',
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
					

					// Where is the CSS stored?
					if (!$theme_storedb)
					{
						if (!($fp = fopen("{$phpbb_root_path}styles/$theme_path/theme/stylesheet.css", 'rb')))
						{
							trigger_error($user->lang['NO_THEME']);
						}
						$stylesheet = fread($fp, filesize("{$phpbb_root_path}styles/$theme_path/theme/stylesheet.css"));
						fclose($fp);
					}
					else
					{
						$stylesheet = &$theme_data;
					}


					// Pull out list of "custom" tags
					if (preg_match_all('#([a-z\.:]+?) {.*?}#si', $stylesheet, $matches))
					{
						$test_ary = array();
						foreach ($base_classes as $category => $class_ary)
						{
							$test_ary += $class_ary;
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

						// Where is the CSS stored?
						if (!$storedb && is_writeable("{$phpbb_root_path}styles/$theme_path/theme/stylesheet.css"))
						{
							// Grab template data
							if (!($fp = fopen("{$phpbb_root_path}styles/$theme_path/theme/stylesheet.css", 'wb')))
							{
								trigger_error($user->lang['NO_THEME']);
							}
							$stylesheet = fwrite($fp, $stylesheet);
							fclose($fp);
						}
						else
						{
							// We change the path to one relative to the root rather than the theme folder
							$sql_ary = array(
								'theme_storedb'		=> 1,
								'theme_data'		=> str_replace('./', "styles/$theme_path/theme/", $stylesheet),
							);
							$sql = 'UPDATE ' . STYLES_CSS_TABLE . ' 
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
								WHERE theme_id = ' . $theme_id;
							$db->sql_query($sql);
						}

						$error[] = $user->lang['THEME_UPDATED'];
						add_log('admin', 'LOG_EDIT_THEME', $theme_name);
					}


					// I guess really this needs some basic examples, pulled from subSilver
					// to demonstrate the default classes. Other, custom classes can just use
					// the div/span and some text? This is gonna get nasty :(
					if (!empty($_POST['preview']))
					{
						// Temp, just to get this out of the way
						theme_preview($theme_path, $stylesheet, $class, $css_element);
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
					$class_options .= '<option class="sep">' . $user->lang['CSS_CAT_' . strtoupper($category)] . '</option>';
					foreach ($class_ary as $class_name)
					{
						$selected = ($class_name == $class) ? ' selected="selected"' : '';
						$class_options .= '<option value="' . $class_name . '"' . $selected . '>' . (($category == 'custom') ? $class_name : $user->lang['CSS_' . str_replace('.', '', strtoupper($class_name))]) . '</option>';
					}
				}


				// Grab list of potential images for class backgrounds
				$imglist = filelist("{$phpbb_root_path}styles/$theme_path/theme");

				$bg_imglist = '';
				foreach ($imglist as $path => $img_ary)
				{
					foreach ($img_ary as $img)
					{
						$img = substr($path, 1) . (($path != '') ? '/' : '') . $img; 

						$selected = (preg_match('#' . preg_quote($img) . '$#', $background_image)) ? ' selected="selected"' : '';
						$bg_imglist .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>';
					}
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

<h3><?php echo $user->lang['SHOW_RAW_CSS_NOTE']; ?></h3>

<p><?php echo $user->lang['SHOW_RAW_CSS_EXPLAIN']; ?></p>
<?php

				}

?>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$theme_id&amp;showcss=$showcss"; ?>" onsubmit="return csspreview()"><table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_CLASS']; ?>: <select name="classname" onchange="if (this.options[this.selectedIndex].value != ''){ csspreview(); this.form.submit(); }"><?php echo $class_options; ?></select>&nbsp; <input class="btnlite" type="submit" value="<?php echo $user->lang['SELECT']; ?>" tabindex="100" /></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
<?php

				if ($showcss)
				{

?>
			<tr>
				<th colspan="2"><?php echo $user->lang['RAW_CSS']; ?></th>
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
				<th><?php echo $user->lang['CSS_PARAMETER']; ?></th>
				<th><?php echo $user->lang['CSS_VALUE']; ?></th>
			</tr>
<?php

					if (sizeof($error) && !empty($_POST['update']))
					{
						echo '<tr><td class="row3" colspan="2" align="center"><span class="gen" style="color:green" align="center">' . implode('<br />', $error) . '</span></td></tr>';
					}

?>
			<tr>
				<td class="row3" colspan="2"><b><?php echo $user->lang['BACKGROUND']; ?></b></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b><?php echo $user->lang['BACKGROUND_COLOUR']; ?>:</b> <br /><span class="gensmall"><?php echo $user->lang['COLOUR_EXPLAIN']; ?></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="0" border="0"><tr><td><input class="post" type="text" name="background_color" value="<?php echo $background_color; ?>" size="8" maxlength="14"  onchange="document.all.stylebgcolor.bgColor=this.form.background_color.value" /></td><td>&nbsp;</td><td bgcolor="<?php echo $background_color; ?>" id="stylebgcolor" style="border:solid 1px black;"><img src="../images/spacer.gif" width="45" height="15" alt="" /></td><td class="gensmall"> &nbsp; [ <a href="swatch.php" onclick="swatch('background_color');return false" target="_swatch"><?php echo $user->lang['COLOUR_SWATCH']; ?></a> ]</td></tr></table></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['BACKGROUND_IMAGE']; ?>:</b></td>
				<td class="row2"><select name="background_image"><?php echo $bg_imglist ?></select></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['BACKGROUND_REPEAT']; ?>:</b></td>
				<td class="row2"><select name="background_repeat"><?php

					foreach (array('' => '------', 'none' => $user->lang['REPEAT_NO'], 'repeat-x' => $user->lang['REPEAT_X'], 'repeat-y' => $user->lang['REPEAT_Y'], 'both' => $user->lang['REPEAT_ALL']) as $cssvalue => $cssrepeat)
					{
						echo '<option value="' . $cssvalue . '"' . (($background_repeat == $cssvalue) ? ' selected="selected"' : '') . '>' . $cssrepeat . '</option>';
					}
	
?></select></td>
			</tr>


			<tr>
				<td class="row3" colspan="2"><b><?php echo $user->lang['FOREGROUND']; ?></b></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b><?php echo $user->lang['FONT_COLOUR']; ?>:</b> <br /><span class="gensmall"><?php echo $user->lang['COLOUR_EXPLAIN']; ?> <a href="swatch.php" onclick="swatch('color');return false" target="_swatch"><?php echo $user->lang['COLOUR_SWATCH']; ?></a></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="0" border="0"><tr><td><input class="post" type="text" name="color" value="<?php echo $color; ?>" size="8" maxlength="14" onchange="document.all.stylecolor.bgColor=this.form.color.value" /></td><td>&nbsp;</td><td bgcolor="<?php echo $color; ?>" id="stylecolor" style="border:solid 1px black;"><img src="../images/spacer.gif" width="45" height="15" alt="" /></td><td class="gensmall"> &nbsp; [ <a href="swatch.php" onclick="swatch('color');return false" target="_swatch"><?php echo $user->lang['COLOUR_SWATCH']; ?></a> ]</td></tr></table></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b><?php echo $user->lang['FONT_FACE']; ?>:</b> <br /><span class="gensmall"><?php echo $user->lang['FONT_FACE_EXPLAIN']; ?></span></td>
				<td class="row2"><input class="post" type="text" name="font_family" value="<?php echo $font_family; ?>" size="40" maxlength="255" /></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['FONT_SIZE']; ?>:</b></td>
				<td class="row2"><input class="post" type="text" name="font_size" value="<?php echo $font_size; ?>" size="3" maxlength="3" /> <select name="font_size_units"><?php

					foreach (array('pt', 'px', 'em', '%') as $units)
					{
						echo '<option value="' . $units . '"' . (($font_size_units == $units) ? ' selected="selected"' : '') . '>' . $units . '</option>';
					}
	
?></select></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['BOLD']; ?>:</b></td>
				<td class="row2"><input type="radio" name="font_weight" value="bold"<?php echo (!empty($font_weight) && $font_weight == 'bold') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="font_weight" value="normal"<?php echo (!empty($font_weight) && $font_weight == 'normal') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['NO']; ?> &nbsp; <input type="radio" name="font_weight" value=""<?php echo (empty($font_weight)) ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['UNSET']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['ITALIC']; ?>:</b></td>
				<td class="row2"><input type="radio" name="font_style" value="italic"<?php echo (!empty($font_style) && $font_style == 'italic') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="font_style" value="normal"<?php echo (!empty($font_style) && $font_style == 'normal') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['NO']; ?> &nbsp; <input type="radio" name="font_style" value=""<?php echo (empty($font_style)) ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['UNSET']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['UNDERLINE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="text_decoration" value="underline"<?php echo (!empty($text_decoration) && $text_decoration == 'underline') ? ' checked="checked"' : ''; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="text_decoration" value="none"<?php echo (!empty($text_decoration) && $text_decoration == 'none') ? ' checked="checked"' : ''; ?>/> <?php echo $user->lang['NO']; ?> &nbsp; <input type="radio" name="text_decoration" value=""<?php echo (empty($text_decoration)) ? ' checked="checked"' : ''; ?>/> <?php echo $user->lang['UNSET']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['LINE_SPACING']; ?>:</b></td>
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
									
				echo ($showcss) ? '<input class="btnlite" type="submit" name="hidecss" value="' . $user->lang['HIDE_RAW_CSS'] . '" />' : '<input class="btnlite" type="submit" name="showcss" value="' . $user->lang['SHOW_RAW_CSS'] . '" />';
				echo $s_hidden_fields; 
				
?></td>
			</tr>
		</table></td>
	</tr>
</table>

<h1><?php echo $user->lang['CUSTOM_CLASS']; ?></h1>

<p><?php echo $user->lang['CUSTOM_CLASS_EXPLAIN']; ?></p>

<table width="95%" cellspacing="1" cellpadding="1" border="0" align="center">
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2"><?php echo $user->lang['CUSTOM_CLASS']; ?></td>
			</tr>
			<tr>
				<td class="row1" width="40%"><b><?php echo $user->lang['CSS_CLASS_NAME']; ?>:</b></td>
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
				if ($theme_id)
				{
					$sql = 'SELECT theme_id, theme_name, theme_path, theme_storedb
						FROM ' . STYLES_CSS_TABLE . "
						WHERE theme_id = $theme_id";
					$result = $db->sql_query($sql);

					if (!(extract($db->sql_fetchrow($result))))
					{
						trigger_error($user->lang['NO_THEME']);
					}
					$db->sql_freeresult($result);

					remove('theme', $theme_id, $theme_name, $theme_path, $theme_storedb);
				}
				break;

			case 'export':
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

					$cfg  = addslashes($theme_name) . "\n";
					$cfg .= addslashes($theme_copyright) . "\n";
					$cfg .= addslashes($config['version']) . "\n";

					if ($css_storedb)
					{
						$files = array(array('src' => "styles/$theme_path/theme/", 'prefix-' => "styles/$theme_path/", 'prefix+' => false, 'exclude' => "$theme_path.css,theme.cfg"));
						$data = array(array('src' => $css_data, 'prefix' => "theme/$theme_path.css"), array('src' => trim($cfg), 'prefix' => "theme/theme.cfg"));
					}
					else
					{
						$files = array(array('src' => "styles/$theme_path/theme/", 'prefix-' => "styles/$theme_path/", 'prefix+' => false, 'exclude' => "theme.cfg"));
						$data = array(array('src' => trim($cfg), 'prefix' => "theme/theme.cfg"));
					}
					unset($css_data);

					export('theme', $theme_id, $theme_name, $theme_path, $files, $data);
				}
				break;
		}

		// Front page
		front('theme', array('details', 'refresh', 'export', 'delete', 'preview'));
		break;
}


exit;


// ---------
// FUNCTIONS
//
function front($type, $options)
{
	global $phpbb_root_path, $phpEx, $SID, $config, $db, $user, $mode;

	switch ($type)
	{
		case 'template':
			$table = STYLES_TPL_TABLE;
			break;

		case 'theme':
			$table = STYLES_CSS_TABLE;
			break;

		case 'imageset':
			$table = STYLES_IMAGE_TABLE;
			break;
	}

	$l_prefix = strtoupper($type);

	// Output list of themes
	adm_page_header($user->lang[$l_prefix . 'S']);

?>
<h1><?php echo $user->lang[$l_prefix . 'S']; ?></h1>

<p><?php echo $user->lang[$l_prefix . 'S_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang[$l_prefix . '_NAME']; ?></th>
		<th colspan="<?php echo sizeof($options); ?>"><?php echo $user->lang['OPTIONS']; ?></th>
	</tr>
	<tr>
		<td class="row3" colspan="<?php echo sizeof($options) + 1; ?>"><b><?php echo $user->lang['INSTALLED_' . $l_prefix]; ?></b></td>
	</tr>
<?php

	$sql = "SELECT {$type}_id, {$type}_name, {$type}_path 
		FROM $table";
	$result = $db->sql_query($sql);

	$installed = array();
	$basis_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$installed[] = $row[$type . '_name'];
		$basis_options .= '<option value="' . $row[$type . '_id'] . '">' . $row[$type . '_name'] . '</option>';

		$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=edit&amp;id=" . $row[$type . '_id']; ?>"><?php echo $row[$type . '_name']; ?></a></td>
<?php

		foreach ($options as $option)
		{

?>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$option&amp;id=" . $row[$type . '_id']; ?>"><?php echo $user->lang[strtoupper($option)]; ?></a>&nbsp;</td>
<?php

		}

?>
	</tr>
<?php

	}
	$db->sql_freeresult($result);

?>
	<tr>
		<td class="row3" colspan="<?php echo sizeof($options) + 1; ?>"><b><?php echo $user->lang['UNINSTALLED_' . $l_prefix]; ?></b></td>
	</tr>
<?php

	$new_ary = $cfg = array();
	$dp = opendir("{$phpbb_root_path}styles");
	while ($file = readdir($dp))
	{
		if ($file{0} != '.' && file_exists("{$phpbb_root_path}styles/$file/$type/$type.cfg"))
		{
			if ($cfg = file("{$phpbb_root_path}styles/$file/$type/$type.cfg"))
			{
				$name = trim($cfg[0]);
				if (!in_array($name, $installed))
				{
					$new_ary[$i]['path'] = $file;
					$new_ary[$i]['name'] = $name;
					$i++;
				}
			}
		}
	}
	unset($installed);
	@closedir($dp);

	if (sizeof($new_ary))
	{
		foreach ($new_ary as $key => $cfg)
		{
			$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $cfg['name']; ?></td>
		<td class="<?php echo $row_class; ?>" colspan="<?php echo sizeof($options); ?>" align="center"><a href="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=install&amp;name=" . urlencode($cfg['path']); ?>"><?php echo $user->lang['INSTALL']; ?></a></td>
	</tr>
<?php

		}
	}
	else
	{

?>
	<tr>
		<td class="row1" colspan="<?php echo sizeof($options) + 1; ?>" align="center"><?php echo $user->lang['NO_UNINSTALLED_' . $l_prefix]; ?></td>
	</tr>
<?php

	}
	unset($new_ary);

?>
	<tr>
		<td class="cat" colspan="<?php echo sizeof($options) + 1; ?>" align="right"><?php echo $user->lang['CREATE_' . $l_prefix]; ?>: <input class="post" type="text" name="<?php echo $type; ?>_name" value="" maxlength="30" size="25" /> <input class="btnmain" type="submit" name="add" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table></form>

<?php

	adm_page_footer();

}

function remove($type, $id, $name, $path, $storedb = false)
{
	global $phpbb_root_path, $SID, $config, $db, $user, $mode, $action;

	switch ($type)
	{
		case 'template':
			$table = STYLES_TPL_TABLE;
			break;

		case 'theme':
			$table = STYLES_CSS_TABLE;
			break;

		case 'imageset':
			$table = STYLES_IMAGE_TABLE;
			break;
	}

	$l_prefix = strtoupper($type);

	$sql = "SELECT {$type}_id, {$type}_name 
		FROM $table  
		WHERE {$type}_id <> $id 
		ORDER BY {$type}_id";
	$result = $db->sql_query($sql);

	$options = '';
	if (!($row = $db->sql_fetchrow($result)))
	{
		trigger_error($user->lang['ONLY_' . $l_prefix]);
	}

	do
	{
		$options .= '<option value="' . $row[$type . '_id'] . '">' . $row[$type . '_name'] . '</option>';
	}
	while ($row = $db->sql_fetchrow($result));

	if (isset($_POST['update']))
	{
		$sql = "DELETE FROM $table 
			WHERE {$type}_id = $id";
		$db->sql_query($sql);

		$sql = "UPDATE $table 
			SET {$type}_id = " . intval($_POST['newid']) . "
			WHERE {$type}_id = $id";
		$db->sql_query($sql);

		$onfs = 0;
		if (!empty($_POST['deletefs']) && is_writeable("{$phpbb_root_path}styles/$path/{$type}"))
		{
			$filelist = filelist("{$phpbb_root_path}styles/$path/{$type}", '', '*');
			krsort($filelist);

			foreach ($filelist as $subpath => $file_ary)
			{
				$subpath = "{$phpbb_root_path}styles/$path/{$type}$subpath";
				foreach ($file_ary as $file)
				{
/*					if (!@unlink("$subpath/$file"))
					{
						$onfs = 1;
					}*/
				}

/*				if (!@rmdir($subpath))
				{
					$onfs = 1;
				}*/
			}
		}
		else
		{
			$onfs = (file_exists("{$phpbb_root_path}styles/$path/{$type}") && !is_writeable("{$phpbb_root_path}styles/$path/{$type}")) ? 1 : 0;
		}

		add_log('admin', 'LOG_DELETE_' . $l_prefix, $name);
		$message = ($onfs) ? $l_prefix . '_DELETED_FS' : $l_prefix . '_DELETED';
		trigger_error($user->lang[$message]);
	}

	// Output list of themes
	adm_page_header($user->lang['DELETE_' . $l_prefix]);

?>
<h1><?php echo $user->lang['DELETE_' . $l_prefix]; ?></h1>

<p><?php echo $user->lang['DELETE_' . $l_prefix . '_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['DELETE_' . $l_prefix]; ?></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang[$l_prefix . '_NAME']; ?>:</b></td>
		<td class="row2"><b><?php echo $name; ?></b></td>
	</tr>
<?php

	if (is_writeable("{$phpbb_root_path}styles/$path/{$type}") && !$storedb)
	{

?>
	<tr>
		<td class="row1" width="40%"><b>Delete from filesystem:</b></td>
		<td class="row2"><input type="radio" name="deletefs" value="1" /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="deletefs" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['REPLACE_' . $l_prefix]; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['REPLACE_' . $l_prefix . '_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="newid"><?php echo $options; ?></select></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['DELETE']; ?>"; />&nbsp;&nbsp;<input class="btnlite" type="submit" name="cancel" value="<?php echo $user->lang['CANCEL']; ?>"; /></td>
	</tr>
</table></form>
<?php

	adm_page_footer();

}

function details($type, $mode, $action, $id)
{
	global $phpbb_root_path, $phpEx, $SID, $config, $db, $user;
	global $archive_preg, $safe_mode;

	switch ($type)
	{
		case 'theme':
			$table = STYLES_CSS_TABLE;
			break;

		case 'template':
			$table = STYLES_TPL_TABLE;
			break;

		case 'imageset':
			$table = STYLES_IMAGE_TABLE;
			break;
	}

	$l_type = strtoupper($type);
	$l_prefix = ($action == 'add') ? 'ADD' : (($action == 'details') ? 'EDIT_DETAILS' : 'INSTALL');

	// Do we want to edit an existing theme or are we creating a new theme
	// or submitting an existing one?
	if (empty($_POST['update']))
	{
		if ($id) 
		{
			$sql = "SELECT * 
				FROM $table 
				WHERE {$type}_id = $id";
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_' . $l_type]);
			}
			$db->sql_freeresult($result);

			$name = (!empty($row[$type . '_name'])) ? $row[$type . '_name'] : '';
			$copyright = (!empty($row[$type . '_copyright'])) ? $row[$type . '_copyright'] : '';
			$path = (!empty($row[$type . '_path'])) ? $row[$l_type . '_path'] : '';
			$storedb = (!empty($row[$type . '_storedb'])) ? $type[$type . '_storedb'] : '';
			$data = (!empty($row[$type . '_data'])) ? $row[$type . '_data'] : '';
			unset($row);

			$s_hidden_fields = '';
		}
		else if ($action == 'install' && empty($_POST['update']))
		{
			if (empty($_GET['name']))
			{
				trigger_error($user->lang[$l_type . '_ERR_NO_NAME']);
			}

			$path = trim(htmlspecialchars($_GET['name']));
			if (!file_exists("{$phpbb_root_path}styles/$path/$type/$type.cfg"))
			{
				trigger_error($user->lang['NO_' . $l_type]);
			}

			if (!($cfg = file("{$phpbb_root_path}styles/$path/$type/$type.cfg")))
			{
				trigger_error($user->lang['NO_' . $l_type]);
			}

			$name = trim($cfg[0]);
			$copyright = trim($ecfg[1]);
			$version = preg_replace('#^2\.([0-9]+?)\.([0-9]+?).*?$#', '\1.\2', trim($cfg[2]));
			$phpbbversion = preg_replace('#^2\.([0-9]+?)\.([0-9]+?).*?$#', '\1.\2', $config['version']);

			if ($version != $phpbbversion)
			{
				$error[] = $user->lang[$l_type . '_VERSION_DIFF'];
			}

			$s_hidden_fields = '<input type="hidden" name="path" value="' . $path . '" /><input type="hidden" name="copyright" value="' . $copyright . '" />';
		}
		else
		{
			$name = (!empty($_POST['name'])) ? htmlspecialchars(stripslashes($_POST['name'])) : '';
			$copyright = (!empty($_POST['copyright'])) ? htmlspecialchars(stripslashes($_POST['copyright'])) : '';
			$storedb = (!empty($_POST['storedb'])) ? 1 : (($safe_mode) ? 1 : 0);
			$basis = (!empty($_POST['basis'])) ? intval($_POST['basis']) : false;
			$path = (!empty($_POST['path'])) ? htmlspecialchars(stripslashes($_POST['path'])) : '';

			$s_hidden_fields = (!empty($_POST['basis'])) ? '<input type="hidden" name="basis" value="' . $basis . '" />' : '';
			if ($path)
			{
				$s_hidden_fields .= '<input type="hidden" name="path" value="' . $path . '" /><input type="hidden" name="copyright" value="' . $copyright . '" />';
			}
		}
	}

	// Do the update thang
	if (isset($_POST['update']))
	{
		if (!$safe_mode && $action == 'add' && is_writeable("{$phpbb_root_path}styles") && (!empty($_FILES['upload_file']['name']) || !empty($_POST['import_file'])))
		{
			if (!empty($_FILES['upload_file']['name']))
			{
				$realname = htmlspecialchars($_FILES['upload_file']['name']);
				$filename = htmlspecialchars($_FILES['upload_file']['tmp_name']);

				if (!is_uploaded_file($filename))
				{
					trigger_error("$filename was not uploaded");
				}
			}
			else
			{
				$realname = htmlspecialchars($_POST['import_file']);
				$filename = "{$phpbb_root_path}store/$realname";
			}

			if (!preg_match('#(' . $archive_preg . ')$#i', $realname, $match))
			{
				$error[] = sprintf($user->lang['UPLOAD_WRONG_TYPE'], $archive_types);
			}
			$path = preg_replace('#^(.*?)' . preg_quote($match[0]) . '$#', '\1', $realname);

			// Attempt to extract the files to a temporary directory in store
			$tmp_path = $phpbb_root_path . 'store/tmp_' . substr(uniqid(''), 0, 10) . '/';
			if (!@mkdir($tmp_path))
			{
				trigger_error("Cannot create $tmp_path");
			}

			include($phpbb_root_path . 'includes/functions_compress.'.$phpEx);

			switch ($match[0])
			{
				case '.zip':
					$zip = new compress_zip('r', $filename);
					break;
				default:
					$zip = new compress_tar('r', $filename, $match[0]);
			}
			$zip->extract($tmp_path);
			$zip->close();

			$filelist = filelist($tmp_path, '', '*');

			if (!in_array($type . '.cfg', $filelist['/' . $type]))
			{
				trigger_error($user->lang[$l_type . '_ERR_NOT_' . $l_type]);
			}

			$cfg = file("$tmp_path$type/$type.cfg");
			if ($name == '')
			{
				$name	= trim($cfg[0]);
			}
			$copyright	= trim($cfg[1]);
			$version	= trim($cfg[2]);
			unset($cfg);
		}
		else if ($action == 'install') 
		{
			$path		= (!empty($_POST['path'])) ? htmlspecialchars($_POST['path']) : '';

			if (!($cfg = file("{$phpbb_root_path}styles/$path/$type/$type.cfg")))
			{
				trigger_error($user->lang['NO_' . $l_type]);
			}

			$name		= (!empty($_POST['name'])) ? htmlspecialchars($_POST['name']) : trim($cfg[0]);
			$storedb	= (!empty($_POST['storedb'])) ? 1 : 0;
			$copyright	= trim($cfg[1]);
			$version	= trim($cfg[2]);

			unset($cfg);
		}
		else
		{
			$name		= (!empty($_POST['name'])) ? htmlspecialchars($_POST['name']) : '';
			$copyright	= (!empty($_POST['copyright'])) ? htmlspecialchars($_POST['copyright']) : '';
			$storedb	= (!empty($_POST['storedb'])) ? 1 : 0;
		}

		$sql_where = ($action == 'add' || $action == 'install') ? "WHERE {$type}_name = '" . $db->sql_escape($name) . "'" : "WHERE {$type}_id <> $id AND {$type}_name = '" . $db->sql_escape($name) . "'";
		$sql = "SELECT {$type}_name 
			FROM $table 
			$sql_where";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$error[] = $user->lang[$l_type . '_ERR_NAME_EXIST'];
		}
		$db->sql_freeresult($result);
		unset($row);

		if (empty($name))
		{
			$error[] = $user->lang[$l_type . '_ERR_STYLE_NAME'];
		}

		if (strlen($name) > 30)
		{
			$error[] = $user->lang[$l_type . '_ERR_NAME_LONG'];
		}

		if (!preg_match('#^[a-z0-9_\-\+\. ]+$#i', $name))
		{
			$error[] = $user->lang[$l_type . '_ERR_NAME_CHARS'];
		}

		if (strlen($copyright) > 60)
		{
			$error[] = $user->lang[$l_type . '_ERR_COPY_LONG'];
		}

		if (!sizeof($error))
		{
			$css_data = '';
			if ($action == 'install')
			{
				switch ($type)
				{
					case 'theme':
						if (!is_writeable("{$phpbb_root_path}styles/$path/$type/stylesheet.css"))
						{
							$storedb = 1;
							if (!($css_data = implode('', file("{$phpbb_root_path}styles/$path/$type/stylesheet.css"))))
							{
								trigger_error($user->lang['NO_THEME']);
							}
						}
						break;

					case 'template':
						$filelist = filelist("{$phpbb_root_path}styles/$path/$type", '', 'html');
						foreach ($filelist as $pathfile => $file_ary)
						{
							foreach ($file_ary as $file)
							{
								if (!is_writeable("{$phpbb_root_path}styles/$path/$type$pathfile/$file"))
								{
									$storedb = 1;
									break;
								}
							}
						}
						break;

					case 'imageset':
						if (!($cfg = file("{$phpbb_root_path}styles/$path/$type/imageset.cfg")))
						{
							trigger_error($user->lang['NO_IMAGESET']);
						}

						for ($i = 3; $i < sizeof($cfg); $i++)
						{
							$tmp = explode('||', $cfg[$i]);
							$sql_img_ary[$tmp[0]] = str_replace('{PATH}', "styles/$path/$type/", trim($tmp[1]));
						}
						unset($cfg);
						break;
				}
			}
			
			
			if ($action == 'add' && !$safe_mode && is_writeable("{$phpbb_root_path}styles"))
			{
				if (file_exists("{$phpbb_root_path}styles/$path/$type/"))
				{
					for ($i = 1; $i < 100; $i++)
					{
						if (!file_exists("$phpbb_root_path/styles/{$path}_{$i}/$type/"))
						{
							$path .= "_$i";
							break;
						}
					}
				}

				umask(0);
				if (@mkdir("{$phpbb_root_path}styles/$path", 0777))
				{
					@chmod("{$phpbb_root_path}styles/$path", 0777);
				}

				if (@mkdir("{$phpbb_root_path}styles/$path/$type", 0777))
				{
					if (!@chmod("{$phpbb_root_path}styles/$path/$type", 0777))
					{
						$storedb = 1;
					}
				}

				if (!empty($_FILES['upload_file']) || !empty($_POST['import_file']))
				{
					// TODO
					// mkdir, rmdir and rename error catching
					ksort($filelist);
					foreach ($filelist as $filepath => $file_ary)
					{
						if ($path && !file_exists("{$phpbb_root_path}styles/$path$filepath"))
						{
							@mkdir("{$phpbb_root_path}styles/$path$filepath", 0777);
						}
						
						foreach ($file_ary as $file)
						{
							@rename("$tmp_path$filepath/$file", "{$phpbb_root_path}styles/$path$filepath/$file");
						}

						if ($filepath && file_exists("$tmp_path$filepath"))
						{
							@rmdir("$tmp_path$filepath");
						}
					}

					@rmdir("$tmp_path/$type");
					@rmdir($tmp_path);
				}
				else if ($basis && !$storedb && !$safe_mode)
				{
					switch ($type)
					{
						case 'theme':
							$sql = "SELECT theme_name, theme_path, theme_storedb, css_data  
								FROM $table 
								WHERE theme_id = $basis";
							$result = $db->sql_query($sql);

							if ($row = $db->sql_fetchrow($result))
							{
								$css_data = ($row['storedb']) ? $row['css_data'] : implode('', file($phpbb_root_path . 'styles/' . $row['theme_path'] . "/$type/stylesheet.css"));

								if (!$storedb && ($fp = @fopen("{$phpbb_root_path}styles/$path/$type/stylesheet.css", 'wb')))
								{
									$storedb = (@fwrite($fp, $css_data)) ? 0 : 1;

									if (!$storedb)
									{
										unset($css_data);

										// Get a list of all files and folders in the basis themes folder
										$filelist = filelist($phpbb_root_path . 'styles/' . $row['theme_path'] . '/' . $type, '', '*');

										// Copy every file bar the original stylesheet
										foreach ($filelist as $filepath => $file_ary)
										{
											foreach ($file_ary as $file)
											{
												if ($file == 'stylesheet.css')
												{
													continue;
												}

												if (!file_exists("{$phpbb_root_path}styles/$path/$type/$filepath"))
												{
													@mkdir("{$phpbb_root_path}styles/$path/$type/$filepath");
												}
												@copy("{$phpbb_root_path}styles/" . $row['theme_path'] . "/$type/$filepath/$file", "{$phpbb_root_path}styles/$path/$type/$filepath/$file");
											}
										}
										unset($filelist);
									}
								}
								else
								{
									$storedb = 1;
								}
								@fclose($fp);
							}
							$db->sql_freeresult($result);
							break;

						case 'template':
							$sql = "SELECT theme_name, theme_path, theme_storedb, css_data  
								FROM $table 
								WHERE theme_id = $basis";
							$result = $db->sql_query($sql);

							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);
							if (!$row)
							{
								$error = $user->lang['NO_TEMPLATE'];
								break;
							}

							if (!is_writeable("{$phpbb_root_path}styles/$path/$type"))
							{
								$storedb = 1;
							}

							if ($row['template_storedb'])
							{
							}
							break;
						
						case 'imageset':
							$sql = "SELECT imageset_name, imageset_path  
								FROM $table  
								WHERE imageset_id = $basis";
							$result = $db->sql_query($sql);

							if ($row = $db->sql_fetchrow($result))
							{
								// Get a list of all files and folders
								$filelist = filelist("{$phpbb_root_path}styles/" . $row['imageset_path'] . '/imageset/', '', '*');

								// Copy every file
								foreach ($filelist as $pathfile => $file_ary)
								{
									foreach ($file_ary as $file)
									{
										if (!file_exists("{$phpbb_root_path}styles/$path/$type/$pathfile"))
										{
											@mkdir("{$phpbb_root_path}styles/$path/$type/$pathfile");
										}
										@copy("{$phpbb_root_path}styles/" . $row['imageset_path'] . "/$type$pathfile/$file", "{$phpbb_root_path}styles/$path/$type$pathfile/$file");
									}
								}
								unset($filelist);
							}
							$db->sql_freeresult($result);
							break;
					}
				}
			}

			// Build relevant array ... we need to do this in bits depending on the
			// action and type
			$sql_ary = array(
				$type . '_name'		=> $name,
			);

			if ($action == 'add' || $action == 'install')
			{
				$sql_ary += array(
					$type . '_copyright'=> $copyright, 
					$type . '_path'		=> $path, 
				);

				switch ($type)
				{
					case 'theme':
						$sql_ary += array(
							$type . '_mtime'	=> ($storedb) ? filemtime("{$phpbb_root_path}styles/$path/$type/stylesheet.css") : 0, 
							$type . '_storedb'	=> $storedb, 
							$type . '_data'		=> ($storedb) ? $css_data : '',
						);
						break;
					case 'template':
						$sql_ary += array(
							$type . '_storedb'	=> $storedb, 
						);
						break;
				}
			}

			if ($action == 'details' && $mode != 'imagesets')
			{
				$sql = "SELECT {$type}_path, {$type}_storedb" . (($type == 'theme') ? ', theme_data' : '') . " 
					FROM $table 
					WHERE {$type}_id = $id";
				$result = $db->sql_query($sql);

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row[$type . '_storedb'] != $storedb)
				{
					$path = $row[$type . '_path'];

					switch ($type)
					{
						case 'theme':
							$css_data = implode('', file("{$phpbb_root_path}styles/$path/$type/stylesheet.css"));
							if (!$storedb && !$safe_mode && is_writeable("{$phpbb_root_path}styles/$path/theme/stylesheet.css"))
							{
								$storedb = 1;
								if ($fp = @fopen("{$phpbb_root_path}styles/$path/$type/stylesheet.css", 'wb'))
								{
									$storedb = (@fwrite($fp, str_replace("styles/$path/theme/", './', $css_data))) ? 0 : 1;
								}
								fclose($fp);
							}
							$css_data = str_replace('./', "styles/$path/theme/", $css_data);

							$sql_ary += array(
								$type . '_mtime'	=> ($storedb) ? filemtime("{$phpbb_root_path}styles/$path/$type/stylesheet.css") : 0, 
								$type . '_storedb'	=> $storedb, 
								$type . '_data'		=> ($storedb) ? $css_data : '',
							);
							break;
						
						case 'template':
							$filelist = filelist("{$phpbb_root_path}styles/$path/template", '', 'html');

							if (!$storedb && !$safe_mode && is_writeable("{$phpbb_root_path}styles/$path/template"))
							{
								$sql = 'SELECT * 
									FROM ' . STYLES_TPLDATA_TABLE . " 
									WHERE template_id = $id";
								$result = $db->sql_query($sql);

								while ($row = $db->sql_fetchrow($result))
								{
									if (!($fp = fopen("{$phpbb_root_path}styles/$path/template/" . $row['template_filename'], 'wb')))
									{
										$storedb = 1;
										break;
									}

									if (!fwrite($fp, $row['template_data']))
									{
										$storedb = 1;
										break;
									}

									fclose($fp);

								}
								$db->sql_freeresult($result);

								if (!$storedb)
								{
									$sql = 'DELETE FROM ' . STYLES_TPLDATA_TABLE . " 
										WHERE template_id = $id";
									$db->sql_query($sql);
								}
							}

							$sql_ary += array(
								$type . '_storedb'	=> $storedb, 
							);
							break;
					}
				}
			}

			$db->sql_transaction('begin');

			$sql = ($action == 'add' || $action == 'install') ? "INSERT INTO $table " . $db->sql_build_array('INSERT', $sql_ary) : "UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE {$type}_id = $id";
			$db->sql_query($sql);

			if ($type == 'template' && $storedb)
			{
				$filelist = array('/template' => $filelist['']);

				if (!$id)
				{
					$id = $db->sql_nextid();
				}

				store_templates('insert', $id, $path, $filelist);
			}

			$db->sql_transaction('commit');

			$message = ($action == 'add' || $action == 'install') ? (($storedb) ? $l_prefix . '_DB_ADDED' : $l_prefix . '_FS_ADDED') : $l_prefix . '_DETAILS_UPDATE';
			$log = ($action == 'add' || $action == 'install') ? (($storedb) ? 'LOG_ADD_' . $l_prefix . '_DB' : 'LOG_ADD_' . $l_prefix . '_FS') : 'LOG_EDIT_' . $l_prefix . '_DETAILS';

			add_log('admin', $log, $_name);
			trigger_error($user->lang[$message]);
		}

		
		// If we were uploading or importing and an error occured we delete
		// the temporary files
		if (!empty($_FILES['upload_file']) || !empty($_POST['import_file']))
		{
			// TODO
			// mkdir, rmdir and rename error catching
			ksort($filelist);
			foreach ($filelist as $filepath => $file_ary)
			{
				foreach ($file_ary as $file)
				{
					@unlink("$tmp_path$filepath/$file");
				}

				if ($filepath && file_exists("$tmp_path$filepath"))
				{
					@rmdir("$tmp_path$filepath");
				}
			}

			@rmdir("$tmp_path/$type");
			@rmdir($tmp_path);
		}
	}


	$storedb_no = (!$storedb) ? ' checked="checked"' : '';
	$storedb_yes = ($storedb) ? ' checked="checked"' : '';


	// Output the page
	adm_page_header($user->lang[$l_prefix . '_' . $l_type]);

?>

<h1><?php echo $user->lang[$l_prefix . '_' . $l_type]; ?></h1>

<p><?php echo $user->lang[$l_prefix . '_' . $l_type . '_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$id"; ?>"<?php echo (!$safe_mode && is_writeable("{$phpbb_root_path}styles")) ? ' enctype="multipart/form-data"' : ''; ?>><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang[$l_prefix . '_' . $l_type]; ?></th>
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
		<td class="row1" width="40%"><b><?php echo $user->lang[$l_type . '_NAME']; ?>:</b></td>
		<td class="row2"><input class="post" type="text" name="name" value="<?php echo $name; ?>" maxlength="30" size="25" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['COPYRIGHT']; ?>:</b></td>
		<td class="row2"><?php
	
	echo ($action == 'add') ? '<input class="post" type="text" name="copyright" value="' . $copyright . '" maxlength="30" size="25" />' : "<b>$copyright</b>";

?></td>
	</tr>
<?php

	if ($type != 'imageset')
	{

?>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang[$l_type . '_LOCATION']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang[$l_type . '_LOCATION_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="storedb" value="0"<?php echo $storedb_no; ?> /> <?php echo $user->lang['STORE_FILESYSTEM']; ?>&nbsp;&nbsp;<input type="radio" name="storedb" value="1"<?php echo $storedb_yes; ?> /> <?php echo $user->lang['STORE_DATABASE']; ?></td>
	</tr>
<?php

	}

	// Import, upload and basis options
	if ($action == 'add' && !$safe_mode && is_writeable("{$phpbb_root_path}styles"))
	{
		$store_options = '';
		$dp = opendir("{$phpbb_root_path}store");
		while ($file = readdir($dp))
		{
			if ($file{0} != '.' && preg_match('#(' . $archive_preg . ')$#i', $file))
			{
				$store_options .= "<option value=\"$file\">$file</option>";
			}
		}
		closedir($dp);

		$store_options = '<option value="">' . $user->lang['NO_IMPORT'] . '</option>' . $store_options;

		$sql = "SELECT {$type}_id, {$type}_name 
			FROM $table";
		$result = $db->sql_query($sql);

		$basis_options = '<option value="">' . $user->lang['NO_BASIS'] . '</option>';
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = (!empty($_POST['basis']) && $_POST['basis'] == $row[$type . '_id']) ? ' selected="selected"' : '';
			$basis_options .= '<option value="' . $row[$type . '_id'] . '"' . $selected . '>' . $row[$type . '_name'] . '</option>';
		}
		$db->sql_freeresult($result);

?>
	<tr>
		<th colspan="2"><?php echo $user->lang[$l_type . '_BASIS']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang[$l_type . '_EXISTING_BASIS']; ?>:</b></td>
		<td class="row2"><select name="import_file"><?php echo $basis_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang[$l_type . '_UPLOAD_BASIS']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['ALLOWED_FILETYPES']; ?>: <?php echo $archive_types; ?></span></td>
		<td class="row2"><input class="post" type="file" name="upload_file" /><input type="hidden" name="MAX_FILE_SIZE" value="1048576" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang[$l_type . '_IMPORT_BASIS']; ?>:</b></td>
		<td class="row2"><select name="import_file"><?php echo $store_options; ?></select></td>
	</tr>
<?php

	}	

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /><?php echo $s_hidden_fields; ?></td>
	</tr>
</table></form>
<?php

	adm_page_footer();

}

function store_templates($mode, $id, $path, $filelist)
{
	global $phpbb_root_path, $phpEx, $db;

	$includes = array();
	foreach ($filelist as $pathfile => $file_ary)
	{
		foreach ($file_ary as $file)
		{
			if (!($fp = fopen("{$phpbb_root_path}styles/$path$pathfile/$file", 'r')))
			{
				trigger_error("Could not open {$phpbb_root_path}styles/$path/$pathfile/$file");
			}
			$template_data = fread($fp, filesize("{$phpbb_root_path}styles/$path$pathfile/$file"));
			fclose($fp);

			if (preg_match_all('#<!-- INCLUDE (.*?\.html) -->#is', $template_data, $matches))
			{
				foreach ($matches[1] as $match)
				{
					$includes[trim($match)][] = $file;
				}
			}
		}
	}

	foreach ($filelist as $pathfile => $file_ary)
	{
		foreach ($file_ary as $file)
		{
			// Skip index.
			if (strpos($file, 'index.') === 0)
			{
				continue;
			}

			// We could do this using extended inserts ... but that could be one
			// heck of a lot of data ...
			$sql_ary = array(
				'template_id'		=> $id,
				'template_filename'	=> $file,
				'template_included'	=> (!empty($includes[$file])) ? implode(':', $includes[$file]) . ':' : '',
				'template_mtime'	=> filemtime("{$phpbb_root_path}styles/$path$pathfile/$file"),
				'template_data'		=> implode('', file("{$phpbb_root_path}styles/$path$pathfile/$file")),
			);

			$sql = ($mode == 'insert') ? 'INSERT INTO ' . STYLES_TPLDATA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary) : 'UPDATE ' . STYLES_TPLDATA_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary) . " WHERE template_id = $id AND template_filename = '" . $db->sql_escape($file) . "'";
			$db->sql_query($sql);
		}
	}
}

function export($type, $id, $name, $path, &$files, &$data)
{
	global $phpbb_root_path, $phpEx, $SID, $config, $user, $mode, $action;

	include($phpbb_root_path . 'includes/functions_compress.'.$phpEx);

	$l_prefix = strtoupper($type);

	if (isset($_POST['update']))
	{
		switch ($_POST['format'])
		{
			case 'tar':
				$ext = 'tar';
				$mimetype = 'x-tar';
				$compress = 'compress_tar';
				break;

			case 'zip':
				if (!extension_loaded('zlib'))
				{
					trigger_error($user->lang['NO_SUPPORT_ZIP']);
				}
				$ext = 'zip';
				$mimetype = 'zip';
				$compress = 'compress_zip';
				break;

			case 'tar.gz':
				if (!extension_loaded('zlib'))
				{
					trigger_error($user->lang['NO_SUPPORT_GZ']);
				}
				$ext = 'tar.gz';
				$mimetype = 'x-gzip';
				$compress = 'compress_tar';
				break;

			case 'tar.bz2':
				if (!extension_loaded('bz2'))
				{
					trigger_error($user->lang['NO_SUPPORT_BZ2']);
				}
				$ext = 'tar.bz2';
				$mimetype = 'x-bzip2';
				$compress = 'compress_tar';
				break;

			default:
				$error[] = $user->lang[$l_prefix . '_ERR_ARCHIVE'];
		}

		if (!sizeof($error))
		{
			if (!($zip = new $compress('w', "{$phpbb_root_path}store/$path.$ext")))
			{
				trigger_error($user->lang['STORE_UNWRITEABLE']);
			}

			if ($files)
			{
				foreach ($files as $file_ary)
				{
					$zip->add_file($file_ary['src'], $file_ary['prefix-'], $file_ary['prefix+'], $file_ary['exclude']);
				}
			}

			if ($data)
			{
				foreach ($data as $data_ary)
				{
					$zip->add_data($data_ary['src'], $data_ary['prefix']);
				}
			}

			$zip->close();

			add_log('admin', 'LOG_EXPORT_' . $l_prefix, $name);

			if (empty($_POST['store']))
			{
				header('Pragma: no-cache');
				header("Content-Type: application/$mimetype; name=\"$path.$ext\"");
				header("Content-disposition: attachment; filename=$path.$ext");

				$fp = fopen("{$phpbb_root_path}store/$path.$ext", 'rb');
				while ($buffer = fread($fp, 1024))
				{
					echo $buffer;
				}
				fclose($fp);
				@unlink("{$phpbb_root_path}store/{$type}_$path.$ext");
				exit;
			}

			trigger_error(sprintf($user->lang[$l_prefix . '_EXPORTED'], "store/$path.$ext"));
		}
	}

	// Output list of themes
	adm_page_header($user->lang[$l_prefix . '_EXPORT']);

?>
<h1><?php echo $user->lang[$l_prefix . '_EXPORT']; ?></h1>

<p><?php echo $user->lang[$l_prefix . '_EXPORT_EXPLAIN']; ?></p>

<form name="style" method="post" action="<?php echo "admin_styles.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$id"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
<tr>
	<th colspan="2"><?php echo $user->lang[$l_prefix . '_EXPORT']; ?></td>
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
	<td class="row1" width="40%"><b><?php echo $user->lang[$l_prefix . '_NAME']; ?>:</b></td>
	<td class="row2"><b><?php echo $name; ?></b></td>
</tr>
<tr>
	<td class="row1" width="40%"><b><?php echo $user->lang['DOWNLOAD_STORE']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['DOWNLOAD_STORE_EXPLAIN']; ?></span></td>
	<td class="row2"><input type="radio" name="store" value="1" checked="checked" /> Store&nbsp;&nbsp;<input type="radio" name="store" value="0" /> Download</td>
</tr>
<tr>
	<td class="row1" width="40%"><b><?php echo $user->lang['ARCHIVE_FORMAT']; ?>:</b></td>
	<td class="row2"><?php

	$compress_types = array('zip' => 'zlib', 'tar' => '', 'tar.gz' => 'zlib', 'tar.bz2' => 'bz2');

	foreach ($compress_types as $type => $module)
	{
		if ($module && !extension_loaded($module))
		{
			break;
		}
		echo '<input type="radio" name="format" value="' . $type . '" /> .' . $type . '&nbsp;&nbsp;';
	}

?></td>
</tr>
<tr>
	<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>"; />&nbsp;&nbsp;<input class="btnlite" type="submit" name="cancel" value="<?php echo $user->lang['CANCEL']; ?>"; /></td>
</tr>
</table></form>
<?php

	adm_page_footer();

}

function theme_preview(&$path, &$stylesheet, &$class, &$css_element)
{
	global $config, $user;

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
		$stylesheet = preg_replace('#^(' . $class . ' {).*?(})#m', '\1 ' . $updated_element . ' \2', $stylesheet);
	}

	echo str_replace('styles/', '../styles/', str_replace('./', "styles/$path/theme/", $stylesheet));

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
	
}
//
// FUNCTIONS
// ---------

?>