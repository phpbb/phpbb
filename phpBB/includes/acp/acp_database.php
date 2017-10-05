<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_database
{
	var $db_tools;
	var $u_action;

	function main($id, $mode)
	{
		global $cache, $db, $user, $template, $table_prefix, $request;
		global $phpbb_root_path, $phpbb_container, $phpbb_log;

		$this->db_tools = $phpbb_container->get('dbal.tools');

		$user->add_lang('acp/database');

		$this->tpl_name = 'acp_database';
		$this->page_title = 'ACP_DATABASE';

		$action	= $request->variable('action', '');

		$form_key = 'acp_database';
		add_form_key($form_key);

		$template->assign_vars(array(
			'MODE'	=> $mode
		));

		switch ($mode)
		{
			case 'backup':

				$this->page_title = 'ACP_BACKUP';

				switch ($action)
				{
					case 'download':
						$type	= $request->variable('type', '');
						$table	= array_intersect($this->db_tools->sql_list_tables(), $request->variable('table', array('')));
						$format	= $request->variable('method', '');
						$where	= $request->variable('where', '');

						if (!sizeof($table))
						{
							trigger_error($user->lang['TABLE_SELECT_ERROR'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (!check_form_key($form_key))
						{
							trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$store = $download = $structure = $schema_data = false;

						if ($where == 'store_and_download' || $where == 'store')
						{
							$store = true;
						}

						if ($where == 'store_and_download' || $where == 'download')
						{
							$download = true;
						}

						if ($type == 'full' || $type == 'structure')
						{
							$structure = true;
						}

						if ($type == 'full' || $type == 'data')
						{
							$schema_data = true;
						}

						@set_time_limit(1200);
						@set_time_limit(0);

						$time = time();

						$filename = 'backup_' . $time . '_' . unique_id();

						$extractor = $phpbb_container->get('dbal.extractor');
						$extractor->init_extractor($format, $filename, $time, $download, $store);

						$extractor->write_start($table_prefix);

						foreach ($table as $table_name)
						{
							// Get the table structure
							if ($structure)
							{
								$extractor->write_table($table_name);
							}
							else
							{
								// We might wanna empty out all that junk :D
								switch ($db->get_sql_layer())
								{
									case 'sqlite3':
										$extractor->flush('DELETE FROM ' . $table_name . ";\n");
									break;

									case 'mssql_odbc':
									case 'mssqlnative':
										$extractor->flush('TRUNCATE TABLE ' . $table_name . "GO\n");
									break;

									case 'oracle':
										$extractor->flush('TRUNCATE TABLE ' . $table_name . "/\n");
									break;

									default:
										$extractor->flush('TRUNCATE TABLE ' . $table_name . ";\n");
									break;
								}
							}

							// Data
							if ($schema_data)
							{
								$extractor->write_data($table_name);
							}
						}

						$extractor->write_end();

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_DB_BACKUP');

						if ($download == true)
						{
							exit;
						}

						trigger_error($user->lang['BACKUP_SUCCESS'] . adm_back_link($this->u_action));
					break;

					default:
						$tables = $this->db_tools->sql_list_tables();
						asort($tables);
						foreach ($tables as $table_name)
						{
							if (strlen($table_prefix) === 0 || stripos($table_name, $table_prefix) === 0)
							{
								$template->assign_block_vars('tables', array(
									'TABLE'	=> $table_name
								));
							}
						}
						unset($tables);

						$template->assign_vars(array(
							'U_ACTION'	=> $this->u_action . '&amp;action=download'
						));

						$available_methods = array('gzip' => 'zlib', 'bzip2' => 'bz2');

						foreach ($available_methods as $type => $module)
						{
							if (!@extension_loaded($module))
							{
								continue;
							}

							$template->assign_block_vars('methods', array(
								'TYPE'	=> $type
							));
						}

						$template->assign_block_vars('methods', array(
							'TYPE'	=> 'text'
						));
					break;
				}
			break;

			case 'restore':

				$this->page_title = 'ACP_RESTORE';

				switch ($action)
				{
					case 'submit':
						$delete = $request->variable('delete', '');
						$file = $request->variable('file', '');
						$download = $request->variable('download', '');

						if (!preg_match('#^backup_\d{10,}_[a-z\d]{16}\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches))
						{
							trigger_error($user->lang['BACKUP_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$file_name = $phpbb_root_path . 'store/' . $matches[0];

						if (!file_exists($file_name) || !is_readable($file_name))
						{
							trigger_error($user->lang['BACKUP_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if ($delete)
						{
							if (confirm_box(true))
							{
								unlink($file_name);
								$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_DB_DELETE');
								trigger_error($user->lang['BACKUP_DELETE'] . adm_back_link($this->u_action));
							}
							else
							{
								confirm_box(false, $user->lang['DELETE_SELECTED_BACKUP'], build_hidden_fields(array('delete' => $delete, 'file' => $file)));
							}
						}
						else if ($download || confirm_box(true))
						{
							if ($download)
							{
								$name = $matches[0];

								switch ($matches[1])
								{
									case 'sql':
										$mimetype = 'text/x-sql';
									break;
									case 'sql.bz2':
										$mimetype = 'application/x-bzip2';
									break;
									case 'sql.gz':
										$mimetype = 'application/x-gzip';
									break;
								}

								header('Cache-Control: private, no-cache');
								header("Content-Type: $mimetype; name=\"$name\"");
								header("Content-disposition: attachment; filename=$name");

								@set_time_limit(0);

								$fp = @fopen($file_name, 'rb');

								if ($fp !== false)
								{
									while (!feof($fp))
									{
										echo fread($fp, 8192);
									}
									fclose($fp);
								}

								flush();
								exit;
							}

							switch ($matches[1])
							{
								case 'sql':
									$fp = fopen($file_name, 'rb');
									$read = 'fread';
									$seek = 'fseek';
									$eof = 'feof';
									$close = 'fclose';
									$fgetd = 'fgetd';
								break;

								case 'sql.bz2':
									$fp = bzopen($file_name, 'r');
									$read = 'bzread';
									$seek = '';
									$eof = 'feof';
									$close = 'bzclose';
									$fgetd = 'fgetd_seekless';
								break;

								case 'sql.gz':
									$fp = gzopen($file_name, 'rb');
									$read = 'gzread';
									$seek = 'gzseek';
									$eof = 'gzeof';
									$close = 'gzclose';
									$fgetd = 'fgetd';
								break;
							}

							switch ($db->get_sql_layer())
							{
								case 'mysql':
								case 'mysql4':
								case 'mysqli':
								case 'sqlite3':
									while (($sql = $fgetd($fp, ";\n", $read, $seek, $eof)) !== false)
									{
										$db->sql_query($sql);
									}
								break;

								case 'postgres':
									$delim = ";\n";
									while (($sql = $fgetd($fp, $delim, $read, $seek, $eof)) !== false)
									{
										$query = trim($sql);

										if (substr($query, 0, 13) == 'CREATE DOMAIN')
										{
											list(, , $domain) = explode(' ', $query);
											$sql = "SELECT domain_name
												FROM information_schema.domains
												WHERE domain_name = '$domain';";
											$result = $db->sql_query($sql);
											if (!$db->sql_fetchrow($result))
											{
												$db->sql_query($query);
											}
											$db->sql_freeresult($result);
										}
										else
										{
											$db->sql_query($query);
										}

										if (substr($query, 0, 4) == 'COPY')
										{
											while (($sub = $fgetd($fp, "\n", $read, $seek, $eof)) !== '\.')
											{
												if ($sub === false)
												{
													trigger_error($user->lang['RESTORE_FAILURE'] . adm_back_link($this->u_action), E_USER_WARNING);
												}
												pg_put_line($db->get_db_connect_id(), $sub . "\n");
											}
											pg_put_line($db->get_db_connect_id(), "\\.\n");
											pg_end_copy($db->get_db_connect_id());
										}
									}
								break;

								case 'oracle':
									while (($sql = $fgetd($fp, "/\n", $read, $seek, $eof)) !== false)
									{
										$db->sql_query($sql);
									}
								break;

								case 'mssql_odbc':
								case 'mssqlnative':
									while (($sql = $fgetd($fp, "GO\n", $read, $seek, $eof)) !== false)
									{
										$db->sql_query($sql);
									}
								break;
							}

							$close($fp);

							// Purge the cache due to updated data
							$cache->purge();

							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_DB_RESTORE');
							trigger_error($user->lang['RESTORE_SUCCESS'] . adm_back_link($this->u_action));
							break;
						}
						else if (!$download)
						{
							confirm_box(false, $user->lang['RESTORE_SELECTED_BACKUP'], build_hidden_fields(array('file' => $file)));
						}

					default:
						$methods = array('sql');
						$available_methods = array('sql.gz' => 'zlib', 'sql.bz2' => 'bz2');

						foreach ($available_methods as $type => $module)
						{
							if (!@extension_loaded($module))
							{
								continue;
							}
							$methods[] = $type;
						}

						$dir = $phpbb_root_path . 'store/';
						$dh = @opendir($dir);

						$backup_files = array();

						if ($dh)
						{
							while (($file = readdir($dh)) !== false)
							{
								if (preg_match('#^backup_(\d{10,})_[a-z\d]{16}\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches))
								{
									if (in_array($matches[2], $methods))
									{
										$backup_files[(int) $matches[1]] = $file;
									}
								}
							}
							closedir($dh);
						}

						if (!empty($backup_files))
						{
							krsort($backup_files);

							foreach ($backup_files as $name => $file)
							{
								$template->assign_block_vars('files', array(
									'FILE'		=> $file,
									'NAME'		=> $user->format_date($name, 'd-m-Y H:i:s', true),
									'SUPPORTED'	=> true,
								));
							}
						}

						$template->assign_vars(array(
							'U_ACTION'	=> $this->u_action . '&amp;action=submit'
						));
					break;
				}
			break;
		}
	}
}

// get how much space we allow for a chunk of data, very similar to phpMyAdmin's way of doing things ;-) (hey, we only do this for MySQL anyway :P)
function get_usable_memory()
{
	$val = trim(@ini_get('memory_limit'));

	if (preg_match('/(\\d+)([mkg]?)/i', $val, $regs))
	{
		$memory_limit = (int) $regs[1];
		switch ($regs[2])
		{

			case 'k':
			case 'K':
				$memory_limit *= 1024;
			break;

			case 'm':
			case 'M':
				$memory_limit *= 1048576;
			break;

			case 'g':
			case 'G':
				$memory_limit *= 1073741824;
			break;
		}

		// how much memory PHP requires at the start of export (it is really a little less)
		if ($memory_limit > 6100000)
		{
			$memory_limit -= 6100000;
		}

		// allow us to consume half of the total memory available
		$memory_limit /= 2;
	}
	else
	{
		// set the buffer to 1M if we have no clue how much memory PHP will give us :P
		$memory_limit = 1048576;
	}

	return $memory_limit;
}

function sanitize_data_mssql($text)
{
	$data = preg_split('/[\n\t\r\b\f]/', $text);
	preg_match_all('/[\n\t\r\b\f]/', $text, $matches);

	$val = array();

	foreach ($data as $value)
	{
		if (strlen($value))
		{
			$val[] = "'" . $value . "'";
		}
		if (sizeof($matches[0]))
		{
			$val[] = 'char(' . ord(array_shift($matches[0])) . ')';
		}
	}

	return implode('+', $val);
}

function sanitize_data_oracle($text)
{
//	$data = preg_split('/[\0\n\t\r\b\f\'"\/\\\]/', $text);
//	preg_match_all('/[\0\n\t\r\b\f\'"\/\\\]/', $text, $matches);
	$data = preg_split('/[\0\b\f\'\/]/', $text);
	preg_match_all('/[\0\r\b\f\'\/]/', $text, $matches);

	$val = array();

	foreach ($data as $value)
	{
		if (strlen($value))
		{
			$val[] = "'" . $value . "'";
		}
		if (sizeof($matches[0]))
		{
			$val[] = 'chr(' . ord(array_shift($matches[0])) . ')';
		}
	}

	return implode('||', $val);
}

function sanitize_data_generic($text)
{
	$data = preg_split('/[\n\t\r\b\f]/', $text);
	preg_match_all('/[\n\t\r\b\f]/', $text, $matches);

	$val = array();

	foreach ($data as $value)
	{
		if (strlen($value))
		{
			$val[] = "'" . $value . "'";
		}
		if (sizeof($matches[0]))
		{
			$val[] = "'" . array_shift($matches[0]) . "'";
		}
	}

	return implode('||', $val);
}

// modified from PHP.net
function fgetd(&$fp, $delim, $read, $seek, $eof, $buffer = 8192)
{
	$record = '';
	$delim_len = strlen($delim);

	while (!$eof($fp))
	{
		$pos = strpos($record, $delim);
		if ($pos === false)
		{
			$record .= $read($fp, $buffer);
			if ($eof($fp) && ($pos = strpos($record, $delim)) !== false)
			{
				$seek($fp, $pos + $delim_len - strlen($record), SEEK_CUR);
				return substr($record, 0, $pos);
			}
		}
		else
		{
			$seek($fp, $pos + $delim_len - strlen($record), SEEK_CUR);
			return substr($record, 0, $pos);
		}
	}

	return false;
}

function fgetd_seekless(&$fp, $delim, $read, $seek, $eof, $buffer = 8192)
{
	static $array = array();
	static $record = '';

	if (!sizeof($array))
	{
		while (!$eof($fp))
		{
			if (strpos($record, $delim) !== false)
			{
				$array = explode($delim, $record);
				$record = array_pop($array);
				break;
			}
			else
			{
				$record .= $read($fp, $buffer);
			}
		}
		if ($eof($fp) && strpos($record, $delim) !== false)
		{
			$array = explode($delim, $record);
			$record = array_pop($array);
		}
	}

	if (sizeof($array))
	{
		return array_shift($array);
	}

	return false;
}
