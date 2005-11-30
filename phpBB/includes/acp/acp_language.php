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
* @package acp
*/
class acp_language
{
	var $main_files;
	var $language_header = '';
	var $lang_header = '';

	var $language_file = '';
	var $language_directory = '';

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;
		global $safe_mode, $file_uploads;

		include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$this->default_variables();

		// Check and set some common vars
		$action		= request_var('action', '');

		$action		= (isset($_POST['update_details'])) ? 'update_details' : $action;
		$action		= (isset($_POST['download_file'])) ? 'download_file' : $action;
		$action		= (isset($_POST['submit_file'])) ? 'submit_file' : $action;
		$action		= (isset($_POST['remove_store'])) ? 'details' : $action;

		$lang_id = request_var('id', 0);
		if (isset($_POST['missing_file']))
		{
			$missing_file = request_var('missing_file', array('' => 0));
			list($_REQUEST['language_file'], ) = array_keys($missing_file);
		}
		
		list($this->language_directory, $this->language_file) = explode('|', request_var('language_file', '|common.' . $phpEx));

		$this->language_directory = basename($this->language_directory);
		$this->language_file = basename($this->language_file);

		$user->add_lang('acp/language');
		$this->tpl_name = 'acp_language';
		$this->page_title = 'ACP_LANGUAGE_PACKS';

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		switch ($action)
		{
			case 'update_details':

				if (!$lang_id)
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($u_action));
				}

				$sql = 'SELECT * FROM ' . LANG_TABLE . "
					WHERE lang_id = $lang_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$sql_ary	= array(
					'lang_english_name'		=> request_var('lang_english_name', $row['lang_english_name']),
					'lang_local_name'		=> request_var('lang_local_name', $row['lang_local_name']),
					'lang_author'			=> request_var('lang_author', $row['lang_author']),
				);

				$db->sql_query('UPDATE ' . LANG_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE lang_id = ' . $lang_id);
					
				add_log('admin', 'LOG_LANGUAGE_PACK_UPDATED', $sql_ary['lang_english_name']);

				trigger_error($user->lang['LANGUAGE_DETAILS_UPDATED'] . adm_back_link($u_action));
			break;

			case 'submit_file':
			case 'download_file':

				if (!$lang_id || !isset($_POST['entry']) || !is_array($_POST['entry']))
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($u_action));
				}

				if (!$this->language_file || (!$this->language_directory && !in_array($this->language_file, $this->main_files)))
				{
					trigger_error($user->lang['NO_FILE_SELECTED'] . adm_back_link($u_action));
				}

				$sql = 'SELECT * FROM ' . LANG_TABLE . "
					WHERE lang_id = $lang_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$safe_mode)
				{
					$mkdir_ary = array('language', 'language/' . $row['lang_iso']);
					
					if ($this->language_directory)
					{
						$mkdir_ary[] = 'language/' . $row['lang_iso'] . '/' . $this->language_directory;
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

				// Get target filename for storage folder
				$filename = $this->get_filename($row['lang_iso'], $this->language_directory, $this->language_file, true, true);
				$fp = fopen($phpbb_root_path . $filename, 'wb');

				if (!$fp)
				{
					trigger_error($user->lang['UNABLE_TO_WRITE_FILE']);
				}

				if ($this->language_directory == 'email')
				{
					// Email Template
					fwrite($fp, (STRIP) ? stripslashes($_POST['entry']) : $_POST['entry']);
				}
				else
				{
					$name = (($this->language_directory) ? $this->language_directory . '_' : '') . $this->language_file;
					$header = str_replace(array('{FILENAME}', '{LANG_NAME}', '{CHANGED}', '{AUTHOR}'), array($name, $row['lang_english_name'], date('Y-m-d', time()), $row['lang_author']), $this->language_file_header);

					if (strpos($this->language_file, 'help_') === 0)
					{
						// Help File
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
					}
					else
					{
						// Language File
						$header .= $this->lang_header;
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
					}

					$footer = ");\n\n?>";
					fwrite($fp, $footer);	
				}

				fclose($fp);

				if ($action == 'download_file')
				{
					header('Pragma: no-cache');
					header('Content-Type: application/octetstream; name="' . $this->language_file . '"');
					header('Content-disposition: attachment; filename=' . $this->language_file);

					$fp = fopen($phpbb_root_path . $filename, 'rb');
					while ($buffer = fread($fp, 1024))
					{
						echo $buffer;
					}
					fclose($fp);
					
					exit;
				}
			
				$action = 'details';

			// no break;

			case 'details':

				if (!$lang_id)
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($u_action));
				}
				
				$this->page_title = 'LANGUAGE_PACK_DETAILS';

				$sql = 'SELECT * FROM ' . LANG_TABLE . '
					WHERE lang_id = ' . $lang_id;
				$result = $db->sql_query($sql);
				$lang_entries = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				
				$lang_iso = $lang_entries['lang_iso'];
				$missing_vars = $missing_files = array();

				// Get email templates
				$email_files = filelist($phpbb_root_path . 'language/' . $config['default_lang'], 'email', 'txt');
				$email_files = $email_files['email/'];

				// Get acp files
				$acp_files = filelist($phpbb_root_path . 'language/' . $config['default_lang'], 'acp', $phpEx);
				$acp_files = $acp_files['acp/'];

				// Get mod files
				$mods_files = filelist($phpbb_root_path . 'language/' . $config['default_lang'], 'mods', $phpEx);
				$mods_files = (isset($mods_files['mods/'])) ? $mods_files['mods/'] : array();

				// Check if our current filename matches the files
				switch ($this->language_directory)
				{
					case 'email':
						if (!in_array($this->language_file, $email_files))
						{
							trigger_error($user->lang['WRONG_LANGUAGE_FILE'] . adm_back_link($u_action . '&amp;action=details&amp;id=' . $lang_id));
						}
					break;

					case 'acp':
						if (!in_array($this->language_file, $acp_files))
						{
							trigger_error($user->lang['WRONG_LANGUAGE_FILE'] . adm_back_link($u_action . '&amp;action=details&amp;id=' . $lang_id));
						}
					break;

					case 'mods':
						if (!in_array($this->language_file, $mods_files))
						{
							trigger_error($user->lang['WRONG_LANGUAGE_FILE'] . adm_back_link($u_action . '&amp;action=details&amp;id=' . $lang_id));
						}
					break;

					default:
						if (!in_array($this->language_file, $this->main_files))
						{
							trigger_error($user->lang['WRONG_LANGUAGE_FILE'] . adm_back_link($u_action . '&amp;action=details&amp;id=' . $lang_id));
						}
				}
				
				if (isset($_POST['remove_store']))
				{
					$store_filename = $this->get_filename($lang_iso, $this->language_directory, $this->language_file, true, true);
					@unlink($phpbb_root_path . $store_filename);
				}

				$template->assign_vars(array(
					'S_DETAILS'			=> true,
					'U_ACTION'			=> $u_action . "&amp;action=details&amp;id=$lang_id",
					'U_BACK'			=> $u_action,
					'LANG_LOCAL_NAME'	=> $lang_entries['lang_local_name'],
					'LANG_ENGLISH_NAME'	=> $lang_entries['lang_english_name'],
					'LANG_ISO'			=> $lang_entries['lang_iso'],
					'LANG_AUTHOR'		=> $lang_entries['lang_author'],
					)
				);

				// If current lang is different from the default lang, then first try to grab missing/additional vars
				if ($lang_iso != $config['default_lang'])
				{
					$is_missing_var = false;

					foreach ($this->main_files as $file)
					{
						if (file_exists($phpbb_root_path . $this->get_filename($lang_iso, '', $file)))
						{
							$missing_vars[$file] = $this->compare_language_files($config['default_lang'], $lang_iso, '', $file);
							
							if (sizeof($missing_vars[$file]))
							{
								$is_missing_var = true;
							}
						}
						else
						{
							$missing_files[] = $this->get_filename($lang_iso, '', $file);
						}
					}

					// Now go through acp/mods directories
					foreach ($acp_files as $file)
					{
						if (file_exists($phpbb_root_path . $this->get_filename($lang_iso, 'acp', $file)))
						{
							$missing_vars['acp/' . $file] = $this->compare_language_files($config['default_lang'], $lang_iso, 'acp', $file);
							
							if (sizeof($missing_vars['acp/' . $file]))
							{
								$is_missing_var = true;
							}
						}
						else
						{
							$missing_files[] = $this->get_filename($lang_iso, 'acp', $file);
						}
					}

					if (sizeof($mods_files))
					{
						foreach ($mods_files as $file)
						{
							if (file_exists($phpbb_root_path . $this->get_filename($lang_iso, 'mods', $file)))
							{
								$missing_vars['mods/' . $file] = $this->compare_language_files($config['default_lang'], $lang_iso, 'mods', $file);
								
								if (sizeof($missing_vars['mods/' . $file]))
								{
									$is_missing_var = true;
								}
							}
							else
							{
								$missing_files[] = $this->get_filename($lang_iso, 'mods', $file);
							}
						}
					}
				
					// More missing files... for example email templates?
					foreach ($email_files as $file)
					{
						if (!file_exists($phpbb_root_path . $this->get_filename($lang_iso, 'email', $file)))
						{
							$missing_files[] = $this->get_filename($lang_iso, 'email', $file);
						}
					}

					if (sizeof($missing_files))
					{
						$template->assign_vars(array(
							'S_MISSING_FILES'		=> true,
							'L_MISSING_FILES'		=> sprintf($user->lang['THOSE_MISSING_LANG_FILES'], $lang_entries['lang_local_name']),
							'MISSING_FILES'			=> implode('<br />', $missing_files))
						);		
					}

					if ($is_missing_var)
					{
						$template->assign_vars(array(
							'S_MISSING_VARS'			=> true,
							'L_MISSING_VARS_EXPLAIN'	=> sprintf($user->lang['THOSE_MISSING_LANG_VARIABLES'], $lang_entries['lang_local_name']),
							'U_MISSING_ACTION'			=> $u_action . "&amp;action=$action&amp;id=$lang_id")
						);						

						foreach ($missing_vars as $file => $vars)
						{
							if (!sizeof($vars))
							{
								continue;
							}

							$template->assign_block_vars('missing', array(
								'FILE'			=> $file,
								'TPL'			=> $this->print_language_entries($vars, '', false),
								'KEY'			=> (strpos($file, '/') === false) ? '|' . $file : str_replace('/', '|', $file))
							);
						}
					}
				}

				// Main language files
				$s_lang_options = '<option value="|common.' . $phpEx . '" class="sep">' . $user->lang['LANGUAGE_FILES'] . '</option>';
				foreach ($this->main_files as $file)
				{
					if (strpos($file, 'help_') === 0)
					{
						continue;
					}

					$prefix = (file_exists($phpbb_root_path . $this->get_filename($lang_iso, '', $file, true, true))) ? '* ' : '';

					$selected = (!$this->language_directory && $this->language_file == $file) ? ' selected="selected"' : '';
					$s_lang_options .= '<option value="|' . $file . '"' . $selected . '>' . $prefix . $file . '</option>';
				}
				
				// Help Files
				$s_lang_options .= '<option value="|common.' . $phpEx . '" class="sep">' . $user->lang['HELP_FILES'] . '</option>';
				foreach ($this->main_files as $file)
				{
					if (strpos($file, 'help_') !== 0)
					{
						continue;
					}

					$prefix = (file_exists($phpbb_root_path . $this->get_filename($lang_iso, '', $file, true, true))) ? '* ' : '';

					$selected = (!$this->language_directory && $this->language_file == $file) ? ' selected="selected"' : '';
					$s_lang_options .= '<option value="|' . $file . '"' . $selected . '>' . $prefix . $file . '</option>';
				}

				// Now every other language directory
				$check_files = array('email', 'acp', 'mods');

				foreach ($check_files as $check)
				{
					if (!sizeof(${$check . '_files'}))
					{
						continue;
					}

					$s_lang_options .= '<option value="|common.' . $phpEx . '" class="sep">' . $user->lang[strtoupper($check) . '_FILES'] . '</option>';
					
					foreach (${$check . '_files'} as $file)
					{
						$prefix = (file_exists($phpbb_root_path . $this->get_filename($lang_iso, $check, $file, true, true))) ? '* ' : '';

						$selected = ($this->language_directory == $check && $this->language_file == $file) ? ' selected="selected"' : '';
						$s_lang_options .= '<option value="' . $check . '|' . $file . '"' . $selected . '>' . $prefix . $file . '</option>';
					}
				}

				// Get Language Entries - if saved within store folder, we take this one (with the option to remove it)
				$lang = array();

				$is_email_file = ($this->language_directory == 'email') ? true : false;
				$is_help_file = (strpos($this->language_file, 'help_') === 0) ? true : false;

				$file_from_store = (file_exists($phpbb_root_path . $this->get_filename($lang_iso, $this->language_directory, $this->language_file, true, true))) ? true : false;
				$no_store_filename = $this->get_filename($lang_iso, $this->language_directory, $this->language_file);

				if (!$file_from_store && !file_exists($phpbb_root_path . $no_store_filename))
				{
					$print_message = sprintf($user->lang['MISSING_LANGUAGE_FILE'], $no_store_filename);
				}
				else
				{
					if ($is_email_file)
					{
						$lang = implode('', file($phpbb_root_path . $this->get_filename($lang_iso, $this->language_directory, $this->language_file, $file_from_store)));
					}
					else
					{
						include($phpbb_root_path . $this->get_filename($lang_iso, $this->language_directory, $this->language_file, $file_from_store));

						if ($is_help_file)
						{
							$lang = $help;
							unset($help);
						}
					}
					$print_message = (($this->language_directory) ? $this->language_directory . '/' : '') . $this->language_file;
				}

				// Normal language pack entries
				$template->assign_vars(array(
					'U_ENTRY_ACTION'		=> $u_action . "&amp;action=details&amp;id=$lang_id#entries",
					'S_EMAIL_FILE'			=> $is_email_file,
					'S_FROM_STORE'			=> $file_from_store,
					'S_LANG_OPTIONS'		=> $s_lang_options,
					'PRINT_MESSAGE'			=> $print_message,
					)
				);
				
				if (!$is_email_file)
				{
					$method = ($is_help_file) ? 'print_help_entries' : 'print_language_entries';
					$tpl = '';
					$name = (($this->language_directory) ? $this->language_directory . '/' : '') . $this->language_file;

					if (isset($missing_vars[$name]) && sizeof($missing_vars[$name]))
					{
						$tpl .= $this->$method($missing_vars[$name], '* ');
					}
					
					$tpl .= $this->$method($lang);

					$template->assign_var('TPL', $tpl);
					unset($tpl);
				}
				else
				{
					$template->assign_vars(array(
						'LANG'		=> $lang)
					);
					unset($lang);
				}

				return;
			
			break;

			case 'delete':
			
				if (!$lang_id)
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($u_action));
				}
				
				$sql = 'SELECT * FROM ' . LANG_TABLE . '
					WHERE lang_id = ' . $lang_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row['lang_iso'] == $config['default_lang'])
				{
					trigger_error($user->lang['NO_REMOVE_DEFAULT_LANG'] . adm_back_link($u_action));
				}

				$db->sql_query('DELETE FROM ' . LANG_TABLE . ' WHERE lang_id = ' . $lang_id);

				$sql = 'UPDATE ' . USERS_TABLE . " 
					SET user_lang = '{$config['default_lang']}'
					WHERE user_lang = '{$row['lang_iso']}'";
				$db->sql_query($sql);
					
				add_log('admin', 'LOG_LANGUAGE_PACK_DELETED', $row['lang_english_name']);
				
				trigger_error(sprintf($user->lang['LANGUAGE_PACK_DELETED'], $row['lang_english_name']) . adm_back_link($u_action));
			break;

			case 'install':
				$lang_iso = request_var('iso', '');
				$lang_iso = basename($lang_iso);

				if (!$lang_iso || !file_exists("{$phpbb_root_path}language/$lang_iso/iso.txt"))
				{
					trigger_error($user->lang['LANGUAGE_PACK_NOT_EXIST'] . adm_back_link($u_action));
				}

				$file = file("{$phpbb_root_path}language/$lang_iso/iso.txt");

				$lang_pack = array(
					'iso'		=> $lang_iso,
					'name'		=> trim(htmlspecialchars(stripslashes($file[0]))),
					'local_name'=> trim(htmlspecialchars(stripslashes($file[1]))),
					'author'	=> trim(htmlspecialchars(stripslashes($file[2])))
				);
				unset($file);

				$sql = 'SELECT lang_iso FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $db->sql_escape($lang_iso) . "'";
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					trigger_error($user->lang['LANGUAGE_PACK_ALREADY_INSTALLED'] . adm_back_link($u_action));
				}
				$db->sql_freeresult($result);

				if (!$lang_pack['name'] || !$lang_pack['local_name'])
				{
					trigger_error($user->lang['INVALID_LANGUAGE_PACK'] . adm_back_link($u_action));
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
				
				trigger_error(sprintf($user->lang['LANGUAGE_PACK_INSTALLED'], $lang_pack['name']) . adm_back_link($u_action));

			break;

			case 'download':
		
				if (!$lang_id)
				{
					trigger_error($user->lang['NO_LANG_ID'] . adm_back_link($u_action));
				}

				$sql = 'SELECT * FROM ' . LANG_TABLE . '
					WHERE lang_id = ' . $lang_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$use_method = request_var('use_method', '');
				$methods = array('tar');

				$available_methods = array('tar.gz' => 'zlib', 'tar.bz2' => 'bz2', 'zip' => 'zlib');
				foreach ($available_methods as $type => $module)
				{
					if (!@extension_loaded($module))
					{
						continue;
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
					$this->page_title = 'SELECT_DOWNLOAD_FORMAT';

					$radio_buttons = '';
					foreach ($methods as $method)
					{
						$radio_buttons .= '<input type="radio"' . ((!$radio_buttons) ? ' id="use_method"' : '') . ' value="' . $method . '" name="use_method" />&nbsp;' . $method . '&nbsp;';
					}

					$template->assign_vars(array(
						'S_SELECT_METHOD'		=> true,
						'U_BACK'				=> $u_action,
						'U_ACTION'				=> $u_action . "&amp;action=$action&amp;id=$lang_id",
						'RADIO_BUTTONS'			=> $radio_buttons)
					);
				
					return;
				}

				include_once($phpbb_root_path . 'includes/functions_compress.' . $phpEx);

				if ($use_method == 'zip')
				{
					$compress = new compress_zip('w', $phpbb_root_path . 'store/lang_' . $row['lang_iso'] . '.' . $use_method);
				}
				else
				{
					$compress = new compress_tar('w', $phpbb_root_path . 'store/lang_' . $row['lang_iso'] . '.' . $use_method, $use_method);
				}

				// Get email templates
				$email_templates = filelist($phpbb_root_path . 'language/' . $row['lang_iso'], 'email', 'txt');
				$email_templates = $email_templates['email/'];

				// Get acp files
				$acp_files = filelist($phpbb_root_path . 'language/' . $row['lang_iso'], 'acp', $phpEx);
				$acp_files = $acp_files['acp/'];

				// Get mod files
				$mod_files = filelist($phpbb_root_path . 'language/' . $row['lang_iso'], 'mods', $phpEx);
				$mod_files = (isset($mod_files['mods/'])) ? $mod_files['mods/'] : array();

				// Add main files
				$this->add_to_archive($compress, $this->main_files, $row['lang_iso']);

				// Write files in folders
				$this->add_to_archive($compress, $email_templates, $row['lang_iso'], 'email');
				$this->add_to_archive($compress, $acp_files, $row['lang_iso'], 'acp');
				$this->add_to_archive($compress, $mod_files, $row['lang_iso'], 'mods');

				// Write ISO File
				$iso_src = html_entity_decode($row['lang_english_name']) . "\n";
				$iso_src .= html_entity_decode($row['lang_local_name']) . "\n";
				$iso_src .= html_entity_decode($row['lang_author']);
				$compress->add_data($iso_src, 'language/' . $row['lang_iso'] . '/iso.txt');

				// index.html files
				$compress->add_data('', 'language/' . $row['lang_iso'] . '/index.html');
				$compress->add_data('', 'language/' . $row['lang_iso'] . '/email/index.html');
				$compress->add_data('', 'language/' . $row['lang_iso'] . '/acp/index.html');
				
				if (sizeof($mod_files))
				{
					$compress->add_data('', 'language/' . $row['lang_iso'] . '/mods/index.html');
				}

				$compress->close();

				$compress->download('lang_' . $row['lang_iso']);
				@unlink($phpbb_root_path . 'store/lang_' . $row['lang_iso'] . '.' . $use_method);

				exit;

			break;
		}

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

		while ($row = $db->sql_fetchrow($result))
		{
			$installed[] = $row['lang_iso'];
			$tagstyle = ($row['lang_iso'] == $config['default_lang']) ? '*' : '';

			$template->assign_block_vars('lang', array(
					'U_DETAILS'			=> $u_action . "&amp;action=details&amp;id={$row['lang_id']}",
					'U_DOWNLOAD'		=> $u_action . "&amp;action=download&amp;id={$row['lang_id']}",
					'U_DELETE'			=> $u_action . "&amp;action=delete&amp;id={$row['lang_id']}",

					'ENGLISH_NAME'		=> $row['lang_english_name'],
					'TAG'				=> $tagstyle,
					'LOCAL_NAME'		=> $row['lang_local_name'],
					'ISO'				=> $row['lang_iso'],
					'USED_BY'			=> (isset($lang_count[$row['lang_iso']])) ? $lang_count[$row['lang_iso']] : 0,
					
				)
			);
		}
		$db->sql_freeresult($result);

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
			foreach ($new_ary as $iso => $lang_ary)
			{
				$template->assign_block_vars('notinst', array(
					'ISO'			=> $lang_ary['iso'],
					'LOCAL_NAME'	=> $lang_ary['local_name'],
					'NAME'			=> $lang_ary['name'],
					'U_INSTALL'		=> $u_action . '&amp;action=install&amp;iso=' . urlencode($lang_ary['iso']))
				);
			}
		}
	
		unset($new_ary);
	}


	/**
	* Set default language variables/header
	*/
	function default_variables()
	{
		global $phpEx;

		$this->language_file_header = '<?php
/** 
*
* {FILENAME} [{LANG_NAME}]
*
* @package language
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
';

		$this->lang_header = '

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang += array(
';

		// Language files in language root directory
		$this->main_files = array("common.$phpEx", "groups.$phpEx", "mcp.$phpEx", "memberlist.$phpEx", "posting.$phpEx", "search.$phpEx", "ucp.$phpEx", "viewforum.$phpEx", "viewtopic.$phpEx", "help_bbcode.$phpEx", "help_faq.$phpEx");
	
	}

	/**
	* Get filename/location of language file
	*/
	function get_filename($lang_iso, $directory, $filename, $check_store = false, $only_return_filename = false)
	{
		global $phpbb_root_path, $safe_mode;
		
		$check_filename = "language/$lang_iso/" . (($directory) ? $directory . '/' : '') . $filename;

		if ($check_store)
		{
			$check_store_filename = ($safe_mode) ? "store/langfile_{$lang_iso}" . (($directory) ? '_' . $directory : '') . "_{$filename}" : "store/language/$lang_iso/" . (($directory) ? $directory . '/' : '') . $filename;

			if (!$only_return_filename && file_exists($phpbb_root_path . $check_store_filename))
			{
				return $check_store_filename;
			}
			else if ($only_return_filename)
			{
				return $check_store_filename;
			}
		}

		return $check_filename;
	}

	/**
	* Add files to archive
	*/
	function add_to_archive(&$compress, $filelist, $lang_iso, $directory = '')
	{
		global $phpbb_root_path;

		foreach ($filelist as $file)
		{
			// Get source filename
			$source = $this->get_filename($lang_iso, $directory, $file, true);
			$destination = 'language/' . $lang_iso . '/' . (($directory) ? $directory . '/' : '') . $file;

			// Add file to archive
			$compress->add_custom_file($phpbb_root_path . $source, $destination);
		}
	}

	/**
	* Print language entries
	*/
	function print_language_entries(&$lang_ary, $key_prefix = '', $input_field = true)
	{
		$tpl = '';

		foreach ($lang_ary as $key => $value)
		{
			if (is_array($value))
			{
				$tpl .= '
				<tr>
					<td class="row3" colspan="2">' . $key_prefix . '<b>' . $key . '</b></td>
				</tr>';

				foreach ($value as $_key => $_value)
				{
					$tpl .= '
						<tr>
							<td class="row1" style="width: 10%; white-space: nowrap;">' . $key_prefix . '<b>' . $_key . '</b></td>
							<td class="row2">';
					
					if ($input_field)
					{
						$tpl .= '<input type="text" name="entry[' . $key . '][' . $_key . ']" value="' . htmlspecialchars($_value) . '" style="width: 99%" />';
					}
					else
					{
						$tpl .= '<b>' . htmlspecialchars($_value) . '</b>';
					}
					
					$tpl .= '</td>
						</tr>';
				}

				$tpl .= '
				<tr>
					<td class="spacer" colspan="2">&nbsp;</td>
				</tr>';
			}
			else
			{
				$tpl .= '
				<tr>
					<td class="row1" style="width: 10%; white-space: nowrap;">' . $key_prefix . '<b>' . $key . '</b></td>
					<td class="row2">';

				if ($input_field)
				{
					$tpl .= '<input type="text" name="entry[' . $key . ']" value="' . htmlspecialchars($value) . '" style="width: 99%" />';
				}
				else
				{
					$tpl .= '<b>' . htmlspecialchars($value) . '</b>';
				}
				
				$tpl .= '</td>
					</tr>';
			}
		}

		return $tpl;
	}

	/**
	* Print help entries
	*/
	function print_help_entries(&$lang_ary, $key_prefix = '', $text_field = true)
	{
		$tpl = '';
		
		foreach ($lang_ary as $key => $value)
		{
			if (is_array($value))
			{
				$tpl .= '
				<tr>
					<td class="row3" colspan="2">' . $key_prefix . '<b>' . $key . '</b></td>
				</tr>';

				foreach ($value as $_key => $_value)
				{
					$tpl .= '
						<tr>
							<td class="row1" style="width: 10%; white-space: nowrap;">' . $key_prefix . '<b>' . $_key . '</b></td>
							<td class="row2">';
					
					if ($text_field)
					{
						$tpl .= '<textarea name="entry[' . $key . '][' . $_key . ']" cols="80" rows="5" style="width: 90%;">' . htmlspecialchars($_value) . '</textarea>';
					}
					else
					{
						$tpl .= '<b>' . htmlspecialchars($_value) . '</b>';
					}
					
					$tpl .= '</td>
						</tr>';
				}

				$tpl .= '
				<tr>
					<td class="spacer" colspan="2">&nbsp;</td>
				</tr>';
			}
			else
			{
				$tpl .= '
				<tr>
					<td class="row1" style="width: 10%; white-space: nowrap;">' . $key_prefix . '<b>' . $key . '</b></td>
					<td class="row2">';

				if ($text_field)
				{
					$tpl .= '<textarea name="entry[' . $key . ']" cols="80" rows="5" style="width: 90%;">' . htmlspecialchars($value) . '</textarea>';
				}
				else
				{
					$tpl .= '<b>' . htmlspecialchars($value) . '</b>';
				}

				$tpl .= '</td>
					</tr>';
			}
		}
		
		return $tpl;
	}

	/**
	* Compare two language files
	*/
	function compare_language_files($source_lang, $dest_lang, $directory, $file)
	{
		global $phpbb_root_path, $phpEx;

		$return_ary = array();

		$lang = array();
		include("{$phpbb_root_path}language/{$source_lang}/" . (($directory) ? $directory . '/' : '') . $file);
		$lang_entry_src = $lang;

		$lang = array();

		if (!file_exists($phpbb_root_path . $this->get_filename($dest_lang, $directory, $file, true)))
		{
			return array();
		}

		include($phpbb_root_path . $this->get_filename($dest_lang, $directory, $file, true));

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
}

/**
* @package module_install
*/
class acp_language_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_language',
			'title'		=> 'ACP_LANGUAGE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'lang_packs'		=> array('title' => 'ACP_LANGUAGE_PACKS', 'auth' => 'acl_a_server'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>