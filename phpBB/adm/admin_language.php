<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
if (!empty($setmodules))
{
	if (!$auth->acl_get('a_server'))
	{
		return;
	}

	$module['LANGUAGE']['LANGUAGE_PACKS'] = basename(__FILE__) . "$SID&amp;mode=manage";

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.'.$phpEx);

// Do we have general permissions?
if (!$auth->acl_get('a_server'))
{
	trigger_error('NO_ADMIN');
}

// Check and set some common vars
$mode		= request_var('mode', '');
$confirm	= (isset($_POST['confirm'])) ? true : false;
$cancel		= (isset($_POST['cancel'])) ? true : false;
$action		= request_var('action', '');

$action		= (isset($_POST['update_details'])) ? 'update_details' : $action;
$action		= (isset($_POST['download_file'])) ? 'download_file' : $action;
$action		= (isset($_POST['submit_file'])) ? 'submit_file' : $action;
$action		= (isset($_POST['remove_store'])) ? 'details' : $action;

$lang_id = request_var('id', 0);
$cur_file = request_var('cur_file', 'common');

if (is_array($cur_file))
{
	list($cur_file, ) = array_keys($cur_file);
}

$cur_file = (strpos($cur_file, 'email/') !== false) ? 'email/' . basename($cur_file) : basename($cur_file) . '.' . $phpEx;
$safe_mode	= (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on') ? true : false;

$language_files = array('common', 'groups', 'mcp', 'memberlist', 'posting', 'search', 'ucp', 'viewforum', 'viewtopic', 'admin', 'help_bbcode', 'help_faq');

$language_file_header = '<?php
/** 
*
* {FILENAME} [{LANG_NAME}]
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @author {CHANGED} - {AUTHOR}
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// \'Page %s of %s\' you can (and should) write \'Page %1$s of %2$s\', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. \'Message %d\' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., \'Click %sHERE%s\' is fine

/**
*/
';

if (!$mode)
{
	trigger_error('NO_MODE');
}

switch ($action)
{
	case 'update_details':

		if (!$lang_id)
		{
			trigger_error('NO_LANGUAGE_PACK_DEFINED');
		}

		$sql = 'SELECT * FROM ' . LANG_TABLE . "
			WHERE lang_id = $lang_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$sql_ary['lang_english_name'] = request_var('lang_english_name', $row['lang_english_name']);
		$sql_ary['lang_local_name'] = request_var('lang_local_name', $row['lang_local_name']);
		$sql_ary['lang_author'] = request_var('lang_author', $row['lang_author']);

		$db->sql_query('UPDATE ' . LANG_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE lang_id = ' . $lang_id);
			
		add_log('admin', 'LOG_UPDATE_LANG_DETAILS', $sql_ary['lang_english_name']);

		trigger_error('LANGUAGE_DETAILS_UPDATED');
		break;

	case 'submit_file':
	case 'download_file':

		if (!$lang_id)
		{
			trigger_error('NO_LANGUAGE_PACK_DEFINED');
		}

		if (!$cur_file)
		{
			trigger_error('NO_FILE_SELECTED');
		}

		$sql = 'SELECT * FROM ' . LANG_TABLE . "
			WHERE lang_id = $lang_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$safe_mode)
		{
			$mkdir_ary = array('language', 'language/' . $row['lang_iso']);
			if (strpos($cur_file, 'email/') !== false)
			{
				$mkdir_ary[] = 'language/' . $row['lang_iso'] . '/email';
			}
		
			foreach ($mkdir_ary as $dir)
			{
				$dir = $phpbb_root_path . 'store/' . $dir;
	
				if (!is_dir($dir))
				{
					if (!@mkdir($dir, 0777))
					{
						trigger_error("Could not create directory $dir");
					}
					@chmod($dir, 0777);
				}
			}
		}

		$filename = get_filename($row['lang_iso'], $cur_file, true);
		$fp = fopen($filename, 'wb');

		if (strpos($cur_file, 'email/') !== false)
		{
			fwrite($fp, (STRIP) ? stripslashes($_POST['entry']) : $_POST['entry']);
		}
		else if (strpos($cur_file, 'help_') === 0)
		{
			$header = str_replace(array('{FILENAME}', '{LANG_NAME}', '{CHANGED}', '{AUTHOR}'), array($cur_file, $row['lang_english_name'], date('Y-m-d', time()), $row['lang_author']), $language_file_header);
			$header .= '$help = array(' . "\n";
			fwrite($fp, $header);

			foreach ($_POST['entry'] as $key => $value)
			{
				if (!is_array($value))
				{
				}
				else
				{
					$entry = "\tarray(\n";
				
					foreach ($value as $_key => $_value)
					{
						$_value = (STRIP) ? stripslashes($_value) : $_value;
						$entry .= "\t\t" . (int) $_key . "\t=> '" . str_replace("'", "\\'", $_value) . "',\n";
					}
					
					$entry .= "\t),\n";
				}
								
				fwrite($fp, $entry);
			}	

			$footer = ");\n\n?>";
			fwrite($fp, $footer);	
		}
		else
		{
			$header = str_replace(array('{FILENAME}', '{LANG_NAME}', '{CHANGED}', '{AUTHOR}'), array($cur_file, $row['lang_english_name'], date('Y-m-d', time()), $row['lang_author']), $language_file_header);
			$header .= '
/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang += array(
';
			fwrite($fp, $header);

			foreach ($_POST['entry'] as $key => $value)
			{
				if (!is_array($value))
				{
					$value = (STRIP) ? stripslashes($value) : $value;
					$entry = "\t'" . $key . "'\t=> '" . str_replace("'", "\\'", $value) . "',\n";
				}
				else
				{
					$entry = "\n\t'" . $key . "'\t=> array(\n";
				
					foreach ($value as $_key => $_value)
					{
						$_value = (STRIP) ? stripslashes($_value) : $_value;
						$entry .= "\t\t'" . $_key . "'\t=> '" . str_replace("'", "\\'", $_value) . "',\n";
					}
					
					$entry .= "\t),\n\n";
				}
								
				fwrite($fp, $entry);
			}	

			$footer = ");\n\n?>";
			fwrite($fp, $footer);	
		}

		fclose($fp);

		if ($action == 'download_file')
		{
			$name = basename($cur_file);

			header('Pragma: no-cache');
			header('Content-Type: application/octetstream; name="' . $name . '"');
			header('Content-disposition: attachment; filename=' . $name);

			$fp = fopen($filename, 'rb');
			while ($buffer = fread($fp, 1024))
			{
				echo $buffer;
			}
			fclose($fp);
			
			exit;
		}

		$action = 'details';

	case 'details':
		adm_page_header($user->lang['LANGUAGE_PACK_DETAILS']);

		if (!$lang_id)
		{
			trigger_error('NO_LANGUAGE_PACK_DEFINED');
		}
		
		$sql = 'SELECT * FROM ' . LANG_TABLE . '
			WHERE lang_id = ' . $lang_id;
		$result = $db->sql_query($sql);
		$lang_entries = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$lang_iso = $lang_entries['lang_iso'];
		$missing_vars = $missing_files = array();

		$email_templates = filelist($phpbb_root_path . 'language/' . $config['default_lang'], 'email', 'txt');
		$email_templates = $email_templates['email/'];
		
		if (!in_array(str_replace(".{$phpEx}", '', $cur_file), $language_files) && !in_array(basename($cur_file), $email_templates))
		{
			trigger_error('WRONG_LANGUAGE_FILE');
		}

		if (isset($_POST['remove_store']))
		{
			if (!$safe_mode)
			{
				@unlink(get_filename($lang_iso, $cur_file));
			}
			else
			{
				@unlink(get_filename($lang_iso, $cur_file, true));
			}
		}

?>
		<h1><?php echo $user->lang['LANGUAGE_PACK_DETAILS']; ?></h1>

		<form method="post" action="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$lang_id"; ?>">
		<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
		<tr>
			<th colspan="2"><?php echo $lang_entries['lang_local_name']; ?></th>
		</tr>
		<tr>
			<td class="row1"><b><?php echo $user->lang['LANG_ENGLISH_NAME']; ?>: </b></td>
			<td class="row2"><input type="text" class="text" name="lang_english_name" value="<?php echo $lang_entries['lang_english_name']; ?>" /></td>
		</tr>
		<tr>
			<td class="row1"><b><?php echo $user->lang['LANG_LOCAL_NAME']; ?>: </b></td>
			<td class="row2"><input type="text" class="text" name="lang_local_name" value="<?php echo $lang_entries['lang_local_name']; ?>" /></td>
		</tr>
		<tr>
			<td class="row1"><b><?php echo $user->lang['LANG_ISO_CODE']; ?>: </b></td>
			<td class="row2"><?php echo $lang_entries['lang_iso']; ?></td>
		</tr>
		<tr>
			<td class="row1"><b><?php echo $user->lang['LANG_AUTHOR']; ?>: </b></td>
			<td class="row2"><input type="text" class="text" name="lang_author" value="<?php echo $lang_entries['lang_author']; ?>" /></td>
		</tr>
		<tr>
			<td class="cat" colspan="2" align="right"><input type="submit" name="update_details" class="btnmain" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
		</tr>
		</table>
		<br /><br />
		</form>
<?php

		// If current lang is different from the default lang, then first try to grab missing/additional vars
		if ($lang_iso != $config['default_lang'])
		{
			$is_missing_var = false;

			foreach ($language_files as $file)
			{
				if (file_exists(get_filename($lang_iso, "$file.$phpEx")))
				{
					$missing_vars["$file.$phpEx"] = compare_language_files($config['default_lang'], $lang_iso, $file);

					if (sizeof($missing_vars["$file.$phpEx"]))
					{
						$is_missing_var = true;
					}
					else
					{
						unset($missing_vars["$file.$phpEx"]);
					}
				}
				else
				{
					$missing_files[] = get_filename($lang_iso, "$file.$phpEx");
				}
			}
		
			// More missing files... for example email templates?
			foreach ($email_templates as $file)
			{
				if (!file_exists(get_filename($lang_iso, "email/$file")))
				{
					$missing_files[] = get_filename($lang_iso, "email/$file");
				}
			}

			if (sizeof($missing_files))
			{
?>
				<h1><?php echo sprintf($user->lang['THOSE_MISSING_LANG_FILES'], $lang_entries['lang_local_name']); ?></h1>

				<p><b style="color: red;"><?php echo implode('<br />', $missing_files); ?></b></p>

				<br /><br />
<?php
			}

			if ($is_missing_var)
			{
?>
				<h1><?php echo $user->lang['MISSING_LANG_VARIABLES']; ?></h1>

				<p><?php echo sprintf($user->lang['THOSE_MISSING_LANG_VARIABLES'], $lang_entries['lang_local_name']); ?></p>
				
				<form method="post" action="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$lang_id"; ?>">
				<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
				<tr>
					<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_KEY']; ?></th>
					<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_VARIABLE']; ?></th>
				</tr>
<?php
				foreach ($missing_vars as $file => $vars)
				{
?>
					<tr>
						<td class="row3" colspan="2"><b><?php echo $file; ?></b></td>
					</tr>
<?php
					print_language_entries($vars, '', false);
?>
					<tr>
						<td class="cat" align="right" colspan="2"><input type="submit" name="cur_file[<?php echo str_replace(".{$phpEx}", '', $file); ?>]" value="<?php echo $user->lang['SELECT']; ?>" class="btnmain" /></td>
					</tr>
<?php
				}
?>
				</table>
				<br /><br />
				</form>
<?php
			}
		}

		$s_lang_options = '<option class="sep">' . $user->lang['LANGUAGE_FILES'] . '</option>';
		foreach ($language_files as $file)
		{
			if (strpos($file, 'help_') === 0)
			{
				continue;
			}

			$prefix = (file_exists(get_filename($lang_iso, $file . '.' . $phpEx, true))) ? '* ' : '';

			$selected = ($cur_file == $file . '.' . $phpEx) ? ' selected="selected"' : '';
			$s_lang_options .= '<option value="' . $file . '"' . $selected . '>' . $prefix . $file . '.' . $phpEx . '</option>';
		}
		
		$s_lang_options .= '<option class="sep">' . $user->lang['HELP_FILES'] . '</option>';
		foreach ($language_files as $file)
		{
			if (strpos($file, 'help_') !== 0)
			{
				continue;
			}

			$prefix = (file_exists(get_filename($lang_iso, $file . '.' . $phpEx, true))) ? '* ' : '';

			$selected = ($cur_file == $file . '.' . $phpEx) ? ' selected="selected"' : '';
			$s_lang_options .= '<option value="' . $file . '"' . $selected . '>' . $prefix . $file . '.' . $phpEx . '</option>';
		}

		$s_lang_options .= '<option class="sep">' . $user->lang['EMAIL_TEMPLATES'] . '</option>';
		foreach ($email_templates as $file)
		{
			$prefix = (file_exists(get_filename($lang_iso, "email/{$file}", true))) ? '* ' : '';

			$selected = ($cur_file == 'email/' . $file) ? ' selected="selected"' : '';
			$s_lang_options .= '<option value="email/' . $file . '"' . $selected . '>' . $prefix . $file . '</option>';
		}

		// Get Language Entries - if saved within store folder, we take this one (with the option to remove it)
		$lang = array();
		$is_email_file = (strpos($cur_file, 'email/') !== false) ? true : false;
		$is_help_file = (strpos($cur_file, 'help_') === 0) ? true : false;
		$file_from_store = (file_exists(get_filename($lang_iso, $cur_file, true))) ? true : false;

		if (!$file_from_store && !file_exists(get_filename($lang_iso, $cur_file)))
		{
			$print_message = sprintf($user->lang['MISSING_LANGUAGE_FILE'], $cur_file);
		}
		else
		{
			if ($is_email_file)
			{
				$lang = implode('', file(get_filename($lang_iso, $cur_file, $file_from_store)));
			}
			else
			{
				include(get_filename($lang_iso, $cur_file, $file_from_store));

				if ($is_help_file)
				{
					$lang = $help;
					unset($help);
				}
			}
			$print_message = $cur_file;
		}

		// Normal language pack entries
?>
		<a name="entries"></a>
		<h1><?php echo $user->lang['LANGUAGE_ENTRIES']; ?></h1>

		<p><?php echo $user->lang['LANGUAGE_ENTRIES_EXPLAIN']; ?></p>

		<form method="post" action="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$lang_id#entries"; ?>">
		<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
		<tr>
			<td align="left"><?php	
				if ($file_from_store) {
			?> <input type="submit" name="remove_store" value="<?php echo $user->lang['REMOVE_FROM_STORAGE_FOLDER']; ?>" class="btnmain" /> <?php
				}	
			?>
			<td align="right"><select name="cur_file"><?php echo $s_lang_options; ?></select>&nbsp;<input type="submit" class="btnmain" name="change" value="<?php echo $user->lang['SELECT']; ?>" /></td>
		</tr>
		</table>
		<br />
		<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
<?php
		if ($is_email_file)
		{
?>
			<tr>
				<th colspan="2"><?php echo $user->lang['FILE_CONTENTS']; ?></th>
			</tr>
<?php
		}
		else
		{
?>
			<tr>
				<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_KEY']; ?></th>
				<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_VARIABLE']; ?></th>
			</tr>
<?php
		}
?>
		<tr>
			<td class="row3" align="left"><b><?php echo $print_message . (($file_from_store) ? '<br /><b style="color:red;">' . $user->lang['FILE_FROM_STORAGE'] . '</b>' : ''); ?></b></td>
			<td class="row3" align="right"><input type="submit" name="download_file" class="btnlite" value="<?php echo $user->lang['SUBMIT_AND_DOWNLOAD']; ?>" />&nbsp;&nbsp;<input type="submit" name="submit_file" class="btnmain" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
		</tr>
<?php
		if (!$is_email_file)
		{
			$function = ($is_help_file) ? 'print_help_entries' : 'print_language_entries';
			if (isset($missing_vars[$cur_file]) && sizeof($missing_vars[$cur_file]))
			{
				$function($missing_vars[$cur_file], '* ');
			}
			$function($lang);
		}
		else
		{
?>
			<tr>
				<td class="row1" colspan="2" align="center"><textarea name="entry" cols="80" rows="20" class="post" style="width:90%"><?php echo $lang; ?></textarea></td>
			</tr>
<?php
		}
?>
		<tr>
			<td class="cat" colspan="2" align="right"><input type="submit" name="download_file" class="btnlite" value="<?php echo $user->lang['SUBMIT_AND_DOWNLOAD']; ?>" />&nbsp;&nbsp;<input type="submit" name="submit_file" class="btnmain" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
		</tr>
		</table>
		<br /><br />
		</form>
<?php

		break;
	
	case 'delete':
	
		if (!$lang_id)
		{
			trigger_error('NO_LANGUAGE_PACK_DEFINED');
		}
		
		$sql = 'SELECT * FROM ' . LANG_TABLE . '
			WHERE lang_id = ' . $lang_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row['lang_iso'] == $config['default_lang'])
		{
			trigger_error('NO_REMOVE_DEFAULT_LANG');
		}

		$db->sql_query('DELETE FROM ' . LANG_TABLE . ' WHERE lang_id = ' . $lang_id);
		$sql = 'UPDATE ' . USERS_TABLE . " 
			SET user_lang = '{$config['default_lang']}'
			WHERE user_lang = '{$row['lang_iso']}'";
		$db->sql_query($sql);
			
		add_log('admin', 'LOG_DELETE_LANGUAGE_PACK', $row['lang_english_name']);
		
		trigger_error(sprintf($user->lang['LANGUAGE_PACK_DELETED'], $row['lang_english_name']));
	
		break;
	
	case 'install':
		$lang_iso = request_var('iso', '');
		$lang_iso = basename($lang_iso);

		if (!$lang_iso || !file_exists("{$phpbb_root_path}language/$lang_iso/iso.txt"))
		{
			trigger_error('LANGUAGE_PACK_NOT_EXIST');
		}

		$file = file("{$phpbb_root_path}language/$lang_iso/iso.txt");
		$lang_pack = array();

		$lang_pack = array(
			'iso'		=> htmlspecialchars($lang_iso),
			'name'		=> trim(htmlspecialchars($file[0])),
			'local_name'=> trim(htmlspecialchars($file[1])),
			'author'	=> trim(htmlspecialchars($file[2]))
		);
		unset($file);

		$sql = 'SELECT lang_iso FROM ' . LANG_TABLE . "
			WHERE lang_iso = '" . $db->sql_escape($lang_iso) . "'";
		$result = $db->sql_query($sql);
		if ($row = $db->sql_fetchrow($result))
		{
			trigger_error('LANGUAGE_PACK_ALREADY_INSTALLED');
		}
		$db->sql_freeresult($result);

		if (!$lang_pack['name'] || !$lang_pack['local_name'])
		{
			trigger_error('INVALID_LANGUAGE_PACK');
		}
		
		// Add language pack
		$sql_ary = array(
			'lang_iso'			=> $lang_pack['iso'],
			'lang_dir'			=> $lang_pack['iso'],
			'lang_english_name'	=> $lang_pack['name'],
			'lang_local_name'	=> $lang_pack['local_name'],
			'lang_author'		=> $lang_pack['author']
		);

		$db->sql_query('INSERT INTO ' . LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
		
		add_log('admin', 'LOG_LANGUAGE_PACK_INSTALLED', $lang_pack['name']);
		
		trigger_error(sprintf($user->lang['LANGUAGE_PACK_INSTALLED'], $lang_pack['name']));

		break;

	case 'download':
		
		if (!$lang_id)
		{
			trigger_error('NO_LANGUAGE_PACK_DEFINED');
		}

		$sql = 'SELECT * FROM ' . LANG_TABLE . '
			WHERE lang_id = ' . $lang_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$use_method = request_var('use_method', '');
		$methods = array('tar');

		foreach (array('tar.gz' => 'zlib', 'tar.bz2' => 'bz2', 'zip' => 'zlib') as $type => $module)
		{
			if (!@extension_loaded($module))
			{
				break;
			}
			$methods[] = $type;
		}

		if (!in_array($use_method, $methods))
		{
			$use_method = '';
		}

		// Let the user decide in which format he wants to have the pack
		if (!$use_method)
		{
			adm_page_header($user->lang['SELECT_DOWNLOAD_FORMAT']);

?>
			<h1><?php echo $user->lang['SELECT_DOWNLOAD_FORMAT']; ?></h1>
			
			<form method="post" action="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;id=$lang_id"; ?>">
			<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['DOWNLOAD_AS']; ?></td>
			</tr>
			<tr>
				<td class="row1" align="center">
<?php
					foreach ($methods as $method)
					{
						echo '<input type="radio" value="' . $method . '" name="use_method" />&nbsp;' . $method . '&nbsp;';
					}
?>
				</td>
			</tr>
			<tr>
				<td class="cat" align="right"><input type="submit" class="btnmain" value="<?php echo $user->lang['DOWNLOAD']; ?>" name="download" /></td>
			</tr>
			</table>
			</form>
			<br /><br />
<?php
			adm_page_footer();
			exit;
		}

		include($phpbb_root_path . 'includes/functions_compress.'.$phpEx);

		if ($use_method == 'zip')
		{
			$compress = new compress_zip('w', $phpbb_root_path . 'store/lang_pack_' . $row['lang_iso'] . '.' . $use_method);
		}
		else
		{
			$compress = new compress_tar('w', $phpbb_root_path . 'store/lang_pack_' . $row['lang_iso'] . '.' . $use_method, $use_method);
		}
		
		$email_templates = filelist($phpbb_root_path . 'language/' . $row['lang_iso'], 'email', 'txt');
		$email_templates = $email_templates['email/'];
		
		// Go through all language files, we want to write those within the storage folder first
		$src_path = 'language/' . $row['lang_iso'] . '/';
		foreach ($language_files as $file)
		{
			if (file_exists(get_filename($row['lang_iso'], $file . '.' . $phpEx, 'store')))
			{
				if ($safe_mode)
				{
					$compress->add_custom_file('store/langfile_' . $row['lang_iso'] . '_' . $file . '.' . $phpEx, $src_path . $file . '.' . $phpEx);
				}
				else
				{
					$compress->add_file('store/' . $src_path . $file . '.' . $phpEx, 'store/');
				}
			}
			else
			{
				$compress->add_file($src_path . $file . '.' . $phpEx);
			}
		}

		foreach ($email_templates as $file)
		{
			if (file_exists(get_filename($row['lang_iso'], 'email/' . $file, 'store')))
			{
				if ($safe_mode)
				{
					$compress->add_custom_file('store/langfile_' . $row['lang_iso'] . '_email_' . $file, $src_path . 'email/' . $file);
				}
				else
				{
					$compress->add_file('store/' . $src_path . 'email/' . $file, 'store/');
				}
			}
			else
			{
				$compress->add_file($src_path . 'email/' . $file);
			}
		}

		// Write ISO File
		$iso_src = strtr($row['lang_english_name'], array_flip(get_html_translation_table(HTML_ENTITIES))) . "\n";
		$iso_src .= strtr($row['lang_local_name'], array_flip(get_html_translation_table(HTML_ENTITIES))) . "\n";
		$iso_src .= strtr($row['lang_author'], array_flip(get_html_translation_table(HTML_ENTITIES)));
		$compress->add_data($iso_src, 'language/' . $row['lang_iso'] . '/iso.txt');

		// index.html files
		$compress->add_data('', 'language/' . $row['lang_iso'] . '/index.html');
		$compress->add_data('', 'language/' . $row['lang_iso'] . '/email/index.html');
		$compress->close();

		$compress->download('lang_pack_' . $row['lang_iso']);
		@unlink($phpbb_root_path . 'store/lang_pack_' . $row['lang_iso'] . '.' . $use_method);
		exit;

		break;

	default:
		// Output list of language packs
		adm_page_header($user->lang['LANGUAGE_PACKS']);
?>
<h1><?php echo $user->lang['LANGUAGE_PACKS']; ?></h1>

<p><?php echo $user->lang['LANGUAGE_PACKS_EXPLAIN']; ?></p>

<table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
<tr>
	<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_PACK_NAME']; ?></th>
	<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_PACK_LOCALNAME']; ?></th>
	<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_PACK_ISO']; ?></th>
	<th nowrap="nowrap"><?php echo $user->lang['LANGUAGE_PACK_USED_BY']; ?></th>
	<th nowrap="nowrap"><?php echo $user->lang['OPTIONS']; ?></th>
</tr>
<tr>
	<td class="row3" colspan="5"><b><?php echo $user->lang['INSTALLED_LANGUAGE_PACKS']; ?></b></td>
</tr>
<?php

	$sql = 'SELECT user_lang, COUNT(user_lang) AS lang_count
		FROM ' . USERS_TABLE . ' 
		GROUP BY user_lang';
	$result = $db->sql_query($sql);

	$lang_count = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$lang_count[$row['user_lang']] = $row['lang_count'];
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT *  
		FROM ' . LANG_TABLE;
	$result = $db->sql_query($sql);

	$installed = array();
	$row_class = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$installed[] = $row['lang_iso'];
		$row_class = ($row_class != 'row1') ? 'row1' : 'row2';
		$tagstyle = ($row['lang_iso'] == $config['default_lang']) ? '*' : '';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" width="100%" nowrap="nowrap"><a href="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=details&amp;id=" . $row['lang_id']; ?>"><?php echo $row['lang_english_name']; ?></a> <?php echo $tagstyle; ?></td>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap"><?php echo $row['lang_local_name']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><b><?php echo $row['lang_iso']; ?></b></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo (isset($lang_count[$row['lang_iso']])) ? $lang_count[$row['lang_iso']] : '0'; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=download&amp;id=" . $row['lang_id']; ?>"><?php echo $user->lang['DOWNLOAD']; ?></a>&nbsp;|&nbsp;<a href="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;id=" . $row['lang_id']; ?>"><?php echo $user->lang['DELETE']; ?></a></td>
	</tr>
<?php

	}
	$db->sql_freeresult($result);

?>
	<tr>
		<td class="row3" colspan="5"><b><?php echo $user->lang['UNINSTALLED_LANGUAGE_PACKS']; ?></b></td>
	</tr>
<?php

	$new_ary = $iso = array();
	$dp = opendir("{$phpbb_root_path}language");
	while ($file = readdir($dp))
	{
		if ($file{0} != '.' && file_exists("{$phpbb_root_path}language/$file/iso.txt"))
		{
			if (!in_array($file, $installed))
			{
				if ($iso = file("{$phpbb_root_path}language/$file/iso.txt"))
				{
					if (sizeof($iso) == 3)
					{					
						$new_ary[$file] = array(
							'iso'		=> $file,
							'name'		=> trim($iso[0]),
							'local_name'=> trim($iso[1]),
							'author'	=> trim($iso[2])
						);
					}
				}
			}
		}
	}
	unset($installed);
	@closedir($dp);

	if (sizeof($new_ary))
	{
		$row_class = '';
		foreach ($new_ary as $iso => $lang_ary)
		{
			$row_class = ($row_class != 'row1') ? 'row1' : 'row2';

?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $lang_ary['name']; ?></td>
		<td class="<?php echo $row_class; ?>"><?php echo $lang_ary['local_name']; ?></td>
		<td class="<?php echo $row_class; ?>"><b><?php echo $lang_ary['iso']; ?></b></td>
		<td class="<?php echo $row_class; ?>" colspan="2" align="center"><a href="<?php echo "admin_language.$phpEx$SID&amp;mode=$mode&amp;action=install&amp;iso=" . urlencode($lang_ary['iso']); ?>"><?php echo $user->lang['INSTALL']; ?></a></td>
	</tr>
<?php

		}
	}
	else
	{

?>
	<tr>
		<td class="row1" colspan="5" align="center"><?php echo $user->lang['NO_UNINSTALLED_LANGUAGE_PACKS']; ?></td>
	</tr>
<?php

	}
	unset($new_ary);
?>
</table>
<br /><br />
<?php
	adm_page_footer();

}

exit;


//
// FUNCTIONS

/**
* Compare two language files
*/
function compare_language_files($source_lang, $dest_lang, $file_var)
{
	global $phpbb_root_path, $phpEx;

	$return_ary = array();

	$lang = array();
	include("{$phpbb_root_path}language/{$source_lang}/{$file_var}.{$phpEx}");
	$lang_entry_src = $lang;

	$lang = array();
	if (file_exists(get_filename($dest_lang, $file_var . '.' . $phpEx, true)))
	{
		include(get_filename($dest_lang, $file_var . '.' . $phpEx, true));
	}
	else
	{
		include(get_filename($dest_lang, $file_var . '.' . $phpEx));
	}
	$lang_entry_dst = $lang;

	unset($lang);

	$diff_array_keys = array_diff(array_keys($lang_entry_src), array_keys($lang_entry_dst));
	unset($lang_entry_dst);

	foreach ($diff_array_keys as $key)
	{
		$return_ary[$key] = $lang_entry_src[$key];
	}

	unset($lang_entry_src);

	return $return_ary;
}

/**
* Print language entries
*/
function print_language_entries(&$lang_ary, $key_prefix = '', $input_field = true)
{
	foreach ($lang_ary as $key => $value)
	{
?>
		<tr>
			<td class="row1" width="10%" nowrap="nowrap"><?php echo $key_prefix; ?><b><?php echo $key; ?></b></td>
			<td class="row2">
<?php
		if (is_array($value))
		{
?>
			&nbsp;</td>
		</tr>
<?php
			foreach ($value as $_key => $_value)
			{
?>
		<tr>
			<td class="row1" width="10%" nowrap="nowrap"><?php echo $key_prefix; ?><b><?php echo $key . ' :: ' . $_key; ?></b></td>
			<td class="row2"><?php if ($input_field) { ?><input type="text" class="text" name="entry[<?php echo $key; ?>][<?php echo $_key; ?>]" value="<?php echo htmlspecialchars($_value); ?>" style="width:99%" /><?php } else { ?><b><?php echo htmlspecialchars($_value); ?></b><?php } ?></td>
		</tr>
<?php
			}
		}
		else
		{
?>
			<?php if ($input_field) { ?><input type="text" class="post" name="entry[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($value); ?>" style="width:99%" /><?php } else { ?><b><?php echo htmlspecialchars($value); ?></b><?php } ?></td>
		</tr>
<?php
		}
	}
}

/**
* Print help entries
*/
function print_help_entries(&$lang_ary, $key_prefix = '', $text_field = true)
{
	foreach ($lang_ary as $key => $value)
	{
?>
		<tr>
			<td class="row1" width="10%" nowrap="nowrap"><?php echo $key_prefix; ?><b><?php echo $key; ?></b></td>
			<td class="row2">
<?php
		if (is_array($value))
		{
?>
			&nbsp;</td>
		</tr>
<?php
			foreach ($value as $_key => $_value)
			{
?>
		<tr>
			<td class="row1" width="10%" nowrap="nowrap"><?php echo $key_prefix; ?><b><?php echo $key . ' :: ' . $_key; ?></b></td>
			<td class="row2"><?php if ($text_field) { ?><textarea class="post" name="entry[<?php echo $key; ?>][<?php echo $_key; ?>]" cols="80" rows="5" class="post" style="width:90%"><?php echo htmlspecialchars($_value); ?></textarea><?php } else { ?><b><?php echo htmlspecialchars($_value); ?></b><?php } ?></td>
		</tr>
<?php
			}
		}
		else
		{
?>
			<?php if ($text_field) { ?><textarea type="text" class="post" name="entry[<?php echo $key; ?>]" cols="80" rows="5" style="width:90%"><?php echo htmlspecialchars($value); ?></textarea><?php } else { ?><b><?php echo htmlspecialchars($value); ?></b><?php } ?></td>
		</tr>
<?php
		}
	}
}

/**
* Get filename/location of language/help/email file
*/
function get_filename($lang_iso, $file, $check_store = false)
{
	global $phpbb_root_path, $safe_mode;
	
	if ($check_store && $safe_mode)
	{
		return "{$phpbb_root_path}store/langfile_{$lang_iso}_" . ((strpos($file, 'email/') !== false) ? str_replace('email/', 'email_', $file) : $file);
	}
	else if ($check_store)
	{
		return $phpbb_root_path . 'store/language/' . $lang_iso . '/' . $file;
	}
	else
	{
		return $phpbb_root_path . 'language/' . $lang_iso . '/' . $file;
	}
}

?>