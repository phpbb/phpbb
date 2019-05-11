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

namespace phpbb\acp\controller;

class database
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \phpbb\db\extractor\extractor_interface */
	protected $extractor;

	/** @var \phpbb\filesystem\temp */
	protected $filesystem_temp;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\storage\storage backup */
	protected $storage;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string Table prefix */
	protected $table_prefix;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface		$cache				Cache object
	 * @param \phpbb\db\driver\driver_interface			$db					Database object
	 * @param \phpbb\db\tools\tools_interface			$db_tools			Database tools object
	 * @param \phpbb\db\extractor\extractor_interface	$extractor			Database extractor object
	 * @param \phpbb\filesystem\temp					$filesystem_temp	Temporary filesystem object
	 * @param \phpbb\language\language					$lang				Language object
	 * @param \phpbb\log\log							$log				Log object
	 * @param \phpbb\request\request					$request			Request object
	 * @param \phpbb\storage\storage					$storage			Storage object: backup
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\user								$user				User object
	 * @param string									$table_prefix		Table prefix
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\db\tools\tools_interface $db_tools,
		\phpbb\db\extractor\extractor_interface $extractor,
		\phpbb\filesystem\temp $filesystem_temp,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\storage\storage $storage,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$table_prefix
	)
	{
		$this->cache			= $cache;
		$this->db				= $db;
		$this->db_tools			= $db_tools;
		$this->extractor		= $extractor;
		$this->filesystem_temp	= $filesystem_temp;
		$this->lang				= $lang;
		$this->log				= $log;
		$this->request			= $request;
		$this->storage			= $storage;
		$this->template			= $template;
		$this->user				= $user;

		$this->table_prefix		= $table_prefix;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('acp/database');

		$this->tpl_name = 'acp_database';
		$this->page_title = 'ACP_DATABASE';

		$action	= $this->request->variable('action', '');

		$form_key = 'acp_database';
		add_form_key($form_key);

		$this->template->assign_var('MODE', $mode);

		switch ($mode)
		{
			case 'backup':
				$this->page_title = 'ACP_BACKUP';

				switch ($action)
				{
					case 'download':
						$type	= $this->request->variable('type', '');
						$table	= array_intersect($this->db_tools->sql_list_tables(), $this->request->variable('table', ['']));
						$format	= $this->request->variable('method', '');
						$where	= $this->request->variable('where', '');

						if (empty($table))
						{
							trigger_error($this->lang->lang('TABLE_SELECT_ERROR') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if (!check_form_key($form_key))
						{
							trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$store = $download = $structure = $schema_data = false;

						if ($where === 'store_and_download' || $where === 'store')
						{
							$store = true;
						}

						if ($where === 'store_and_download' || $where === 'download')
						{
							$download = true;
						}

						if ($type === 'full' || $type === 'structure')
						{
							$structure = true;
						}

						if ($type === 'full' || $type === 'data')
						{
							$schema_data = true;
						}

						@set_time_limit(1200);
						@set_time_limit(0);

						$time = time();

						$filename = 'backup_' . $time . '_' . unique_id();

						try
						{
							$this->extractor->init_extractor($format, $filename, $time, $download, $store);

							$this->extractor->write_start($this->table_prefix);

							foreach ($table as $table_name)
							{
								// Get the table structure
								if ($structure)
								{
									// Add table structure to the backup
									// This method also add a "drop the table if exists" after trying to write the table structure
									$this->extractor->write_table($table_name);
								}
								else
								{
									// Add command to empty table before write data on it
									switch ($this->db->get_sql_layer())
									{
										case 'sqlite3':
											$this->extractor->flush('DELETE FROM ' . $table_name . ";\n");
										break;

										case 'mssql_odbc':
										case 'mssqlnative':
											$this->extractor->flush('TRUNCATE TABLE ' . $table_name . "GO\n");
										break;

										case 'oracle':
											$this->extractor->flush('TRUNCATE TABLE ' . $table_name . "/\n");
										break;

										default:
											$this->extractor->flush('TRUNCATE TABLE ' . $table_name . ";\n");
										break;
									}
								}

								// Write schema data if it exists
								if ($schema_data)
								{
									$this->extractor->write_data($table_name);
								}
							}

							$this->extractor->write_end();
						}
						catch (\phpbb\exception\runtime_exception $e)
						{
							trigger_error($e->getMessage(), E_USER_ERROR);
						}

						try
						{
							if ($store)
							{
								$ext = '';

								// Get file name
								switch ($format)
								{
									case 'text':
										$ext = '.sql';
									break;
									case 'bzip2':
										$ext = '.sql.gz2';
									break;
									case 'gzip':
										$ext = '.sql.gz';
									break;
								}

								$file = $filename . $ext;

								// Copy to storage using streams
								$temp_dir = $this->filesystem_temp->get_dir();

								$fp = fopen($temp_dir . '/' . $file, 'rb');

								if ($fp === false)
								{
									throw new \phpbb\exception\runtime_exception('CANNOT_OPEN_FILE');
								}

								$this->storage->write_stream($file, $fp);

								if (is_resource($fp))
								{
									fclose($fp);
								}

								// Remove file from tmp
								@unlink($temp_dir . '/' . $file);

								// Save to database
								$sql = "INSERT INTO " . $this->table_prefix . "backups (filename)
									VALUES ('$file');";
								$this->db->sql_query($sql);
							}
						}
						catch (\phpbb\exception\runtime_exception $e)
						{
							trigger_error($e->getMessage(), E_USER_ERROR);
						}

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DB_BACKUP');

						if ($download == true)
						{
							exit;
						}

						trigger_error($this->lang->lang('BACKUP_SUCCESS') . adm_back_link($this->u_action));
					break;

					default:
						$tables = $this->db_tools->sql_list_tables();
						asort($tables);
						foreach ($tables as $table_name)
						{
							if (strlen($this->table_prefix) === 0 || stripos($table_name, $this->table_prefix) === 0)
							{
								$this->template->assign_block_vars('tables', ['TABLE' => $table_name]);
							}
						}
						unset($tables);

						$this->template->assign_var('U_ACTION', $this->u_action . '&amp;action=download');

						$available_methods = ['gzip' => 'zlib', 'bzip2' => 'bz2'];

						foreach ($available_methods as $type => $module)
						{
							if (!@extension_loaded($module))
							{
								continue;
							}

							$this->template->assign_block_vars('methods', ['TYPE' => $type]);
						}

						$this->template->assign_block_vars('methods', ['TYPE' => 'text']);
					break;
				}
			break;

			case 'restore':
				$this->page_title = 'ACP_RESTORE';

				switch ($action)
				{
					case 'submit':
						$delete = $this->request->variable('delete', '');
						$file = $this->request->variable('file', '');
						$download = $this->request->variable('download', '');

						if (!preg_match('#^backup_\d{10,}_(?:[a-z\d]{16}|[a-z\d]{32})\.(sql(?:\.(?:gz|bz2))?)$#i', $file, $matches))
						{
							trigger_error($this->lang->lang('BACKUP_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$file_name = $matches[0];

						if (!$this->storage->exists($file_name))
						{
							trigger_error($this->lang->lang('BACKUP_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						if ($delete)
						{
							if (confirm_box(true))
							{
								try
								{
									// Delete from storage
									$this->storage->delete($file_name);

									// Add log entry
									$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DB_DELETE');

									// Remove from database
									$sql = "DELETE FROM " . $this->table_prefix . "backups
										WHERE filename = '" . $this->db->sql_escape($file_name) . "';";
									$this->db->sql_query($sql);
								}
								catch (\Exception $e)
								{
									trigger_error($this->lang->lang('BACKUP_ERROR') . adm_back_link($this->u_action), E_USER_WARNING);
								}

								trigger_error($this->lang->lang('BACKUP_DELETE') . adm_back_link($this->u_action));
							}
							else
							{
								confirm_box(false, $this->lang->lang('DELETE_SELECTED_BACKUP'), build_hidden_fields(['delete' => $delete, 'file' => $file]));
							}
						}
						else if ($download || confirm_box(true))
						{
							if ($download)
							{
								$mime_type = '';
								$name = $matches[0];

								switch ($matches[1])
								{
									case 'sql':
										$mime_type = 'text/x-sql';
									break;
									case 'sql.bz2':
										$mime_type = 'application/x-bzip2';
									break;
									case 'sql.gz':
										$mime_type = 'application/x-gzip';
									break;
								}

								header('Cache-Control: private, no-cache');
								header("Content-Type: $mime_type; name=\"$name\"");
								header("Content-disposition: attachment; filename=$name");

								@set_time_limit(0);

								try
								{
									$fp = $this->storage->read_stream($file_name);

									while (!feof($fp))
									{
										echo fread($fp, 8192);
									}
									fclose($fp);
								}
								catch (\phpbb\storage\exception\exception $e)
								{
									// If open fails, just finish
								}

								flush();
								exit;
							}

							// Copy file to temp folder to decompress it
							$temp_file_name = $this->filesystem_temp->get_dir() . '/' . $file_name;

							try
							{
								$stream = $this->storage->read_stream($file_name);
								$fp = fopen($temp_file_name, 'w+b');

								stream_copy_to_stream($stream, $fp);

								fclose($fp);
								fclose($stream);
							}
							catch (\phpbb\storage\exception\exception $e)
							{
								trigger_error($this->lang->lang('RESTORE_DOWNLOAD_FAIL') . adm_back_link($this->u_action));
							}

							switch ($matches[1])
							{
								case 'sql':
									$fp = fopen($temp_file_name, 'rb');
									$read = 'fread';
									$seek = 'fseek';
									$eof = 'feof';
									$close = 'fclose';
									$fgetd = 'fgetd';
								break;

								case 'sql.bz2':
									$fp = bzopen($temp_file_name, 'r');
									$read = 'bzread';
									$seek = '';
									$eof = 'feof';
									$close = 'bzclose';
									$fgetd = 'fgetd_seekless';
								break;

								case 'sql.gz':
									$fp = gzopen($temp_file_name, 'rb');
									$read = 'gzread';
									$seek = 'gzseek';
									$eof = 'gzeof';
									$close = 'gzclose';
									$fgetd = 'fgetd';
								break;
							}

							switch ($this->db->get_sql_layer())
							{
								case 'mysql':
								case 'mysql4':
								case 'mysqli':
								case 'sqlite3':
									while (($sql = $fgetd($fp, ";\n", $read, $seek, $eof)) !== false)
									{
										$this->db->sql_query($sql);
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
											$result = $this->db->sql_query($sql);
											if (!$this->db->sql_fetchrow($result))
											{
												$this->db->sql_query($query);
											}
											$this->db->sql_freeresult($result);
										}
										else
										{
											$this->db->sql_query($query);
										}

										if (substr($query, 0, 4) == 'COPY')
										{
											while (($sub = $fgetd($fp, "\n", $read, $seek, $eof)) !== '\.')
											{
												if ($sub === false)
												{
													trigger_error($this->lang->lang('RESTORE_FAILURE') . adm_back_link($this->u_action), E_USER_WARNING);
												}
												pg_put_line($this->db->get_db_connect_id(), $sub . "\n");
											}
											pg_put_line($this->db->get_db_connect_id(), "\\.\n");
											pg_end_copy($this->db->get_db_connect_id());
										}
									}
								break;

								case 'oracle':
									while (($sql = $fgetd($fp, "/\n", $read, $seek, $eof)) !== false)
									{
										$this->db->sql_query($sql);
									}
								break;

								case 'mssql_odbc':
								case 'mssqlnative':
									while (($sql = $fgetd($fp, "GO\n", $read, $seek, $eof)) !== false)
									{
										$this->db->sql_query($sql);
									}
								break;
							}

							$close($fp);

							@unlink($temp_file_name);

							// Purge the cache due to updated data
							$this->cache->purge();

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DB_RESTORE');

							trigger_error($this->lang->lang('RESTORE_SUCCESS') . adm_back_link($this->u_action));
						}
						else if (!$download)
						{
							confirm_box(false, $this->lang->lang('RESTORE_SELECTED_BACKUP'), build_hidden_fields(['file' => $file]));
						}
					break;

					default:
						$methods = ['sql'];
						$available_methods = ['sql.gz' => 'zlib', 'sql.bz2' => 'bz2'];

						foreach ($available_methods as $type => $module)
						{
							if (!@extension_loaded($module))
							{
								continue;
							}
							$methods[] = $type;
						}

						$sql = 'SELECT filename
							FROM ' . BACKUPS_TABLE;
						$result = $this->db->sql_query($sql);

						$backup_files = [];

						while ($row = $this->db->sql_fetchrow($result))
						{
							if (preg_match('#^backup_(\d{10,})_(?:[a-z\d]{16}|[a-z\d]{32})\.(sql(?:\.(?:gz|bz2))?)$#i', $row['filename'], $matches))
							{
								if (in_array($matches[2], $methods))
								{
									$backup_files[(int) $matches[1]] = $row['filename'];
								}
							}
						}

						$this->db->sql_freeresult($result);

						if (!empty($backup_files))
						{
							krsort($backup_files);

							foreach ($backup_files as $name => $file)
							{
								$this->template->assign_block_vars('files', [
									'FILE'		=> $file,
									'NAME'		=> $this->user->format_date($name, 'd-m-Y H:i:s', true),
									'SUPPORTED'	=> true,
								]);
							}
						}

						$this->template->assign_var('U_ACTION', $this->u_action . '&amp;action=submit');
					break;
				}
			break;
		}

		// @todo return $this->helper->render('acp_database.html', $this->lang->lang('ACP_' . strtoupper($mode));
	}
}

/**
 * Get how much space we allow for a chunk of data.
 * Very similar to phpMyAdmin's way of doing things
 * (We only do this for MySQL anyway)
 *
 * @see \phpbb\db\extractor\mssql_extractor
 *
 * @return int
 */
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

/**
 * Sanitize data (Microsoft SQL).
 *
 * @see \phpbb\db\extractor\mssql_extractor
 *
 * @param string	$text
 * @return string
 */
function sanitize_data_mssql($text)
{
	$data = preg_split('/[\n\t\r\b\f]/', $text);
	preg_match_all('/[\n\t\r\b\f]/', $text, $matches);

	$val = [];

	foreach ($data as $value)
	{
		if (strlen($value))
		{
			$val[] = "'" . $value . "'";
		}
		if (!empty($matches[0]))
		{
			$val[] = 'char(' . ord(array_shift($matches[0])) . ')';
		}
	}

	return implode('+', $val);
}

/**
 * Sanitize data (Oracle)
 *
 * @see \phpbb\db\extractor\oracle_extractor
 *
 * @param string	$text
 * @return string
 */
function sanitize_data_oracle($text)
{
//	$data = preg_split('/[\0\n\t\r\b\f\'"\/\\\]/', $text);
//	preg_match_all('/[\0\n\t\r\b\f\'"\/\\\]/', $text, $matches);
	$data = preg_split('/[\0\b\f\'\/]/', $text);
	preg_match_all('/[\0\r\b\f\'\/]/', $text, $matches);

	$val = [];

	foreach ($data as $value)
	{
		if (strlen($value))
		{
			$val[] = "'" . $value . "'";
		}
		if (!empty($matches[0]))
		{
			$val[] = 'chr(' . ord(array_shift($matches[0])) . ')';
		}
	}

	return implode('||', $val);
}

/**
 * Sanitize data.
 *
 * @see \phpbb\db\extractor\sqlite3_extractor
 *
 * @param string	$text
 * @return string
 */
function sanitize_data_generic($text)
{
	$data = preg_split('/[\n\t\r\b\f]/', $text);
	preg_match_all('/[\n\t\r\b\f]/', $text, $matches);

	$val = [];

	foreach ($data as $value)
	{
		if (strlen($value))
		{
			$val[] = "'" . $value . "'";
		}
		if (!empty($matches[0]))
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
	static $array = [];
	static $record = '';

	if (empty($array))
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

	if (!empty($array))
	{
		return array_shift($array);
	}

	return false;
}
