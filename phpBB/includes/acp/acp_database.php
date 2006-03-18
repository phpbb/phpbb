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
class acp_database
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $table_prefix;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_compress.'.$phpEx);
		
		$user->add_lang('acp/database');

		$this->tpl_name = 'acp_database';
		$this->page_title = 'ACP_DATABASE';

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$template->assign_vars(array(
			'MODE'	=> $mode
		));

		switch ($mode)
		{
			case 'backup':
				switch ($action)
				{
					case 'download':
						$type	= request_var('type', '');
						$table	= request_var('table', array(''));
						$format	= request_var('method', '');
						$where	= request_var('where', '');

						$filename = time();

						// All of the generated queries go here
						$sql_data = '';
						$sql_data .= "#\n";
						$sql_data .= "# phpBB Backup Script\n";
						$sql_data .= "# Dump of tables for $table_prefix\n";
						$sql_data .= "#\n# DATE : " .  gmdate("d-m-Y H:i:s", $filename) . " GMT\n";
						$sql_data .= "#\n";

						switch (SQL_LAYER)
						{
							case 'sqlite':
								$sql_data .= "BEGIN TRANSACTION;\n";
							break;
						}

						// Structure
						if ($type == 'full' || $type == 'structure')
						{
							switch (SQL_LAYER)
							{
								case 'mysqli':
								case 'mysql4':
								case 'mysql':

									foreach ($table as $table_name)
									{
										$row = array();
										$sql_data .= '# Table: ' . $table_name . "\n";
										$sql_data .= "DROP TABLE IF EXISTS $table_name;\n";
										$sql_data .= "CREATE TABLE $table_name(\n";

										$result = $db->sql_query("SHOW FIELDS FROM $table_name");
										while ($row = $db->sql_fetchrow($result))
										{
											$line = '   ' . $row['Field'] . ' ' . $row['Type'];

											if (!is_null($row['Default']))
											{
												$line .= " DEFAULT '{$row['Default']}'";
											}

											if ($row['Null'] != 'YES')
											{
												$line .= ' NOT NULL';
											}

											if ($row['Extra'] != '')
											{
												$line .= ' ' . $row['Extra'];
											}

											$rows[] = $line;
										}

										$result = $db->sql_query("SHOW KEYS FROM $table_name");

										while($row = $db->sql_fetchrow($result))
										{
											$kname = $row['Key_name'];

											if (($kname != 'PRIMARY') && ($row['Non_unique'] == 0))
											{
												$kname = "UNIQUE|$kname";
											}

											if (!is_array($index[$kname]))
											{
												$index[$kname] = array();
											}

											$index[$kname][] = $row['Column_name'];
										}

										$field = array();
										foreach ($index as $key => $columns)
										{
											$line = '   ';

											if ($key == 'PRIMARY')
											{
												$line .= 'PRIMARY KEY (' . implode(', ', $columns) . ')';
											}
											elseif (strpos($key, 'UNIQUE') === 0)
											{
												$line .= 'UNIQUE ' . substr($key, 7) . ' (' . implode(', ', $columns) . ')';
											}
											else
											{
												$line .= "KEY $key (" . implode(', ', $columns) . ')';
											}
											$rows[] = $line;
										}
										$sql_data .= implode(",\n", $rows);
										$sql_data .= "\n);\n\n";
									}
								break;
								case 'sqlite':
									$names	= preg_replace('/\w+/', "'\\0'", implode(', ', $table));
									$sql = "SELECT sql, name FROM sqlite_master WHERE type!='meta' AND name IN ($names) ORDER BY type DESC, name;";
									$result = $db->sql_query($sql);

									while ($row = $db->sql_fetchrow($result))
									{
										$sql_data .= '# Table: ' . $row['name'] . "\n" . $row['sql'] . ";\n";
										$sql_data .= "DROP TABLE IF EXISTS {$row['name']};\n";
										$sql2 = "PRAGMA index_list('{$row['name']}');";
										$result2 = $db->sql_query($sql2);
										$ar = sqlite_fetch_all($result2);

										foreach ($ar as $value)
										{
											if (strpos($value['name'], 'autoindex') !== false)
											{
												continue;
											}
											$result3 = $db->sql_query("PRAGMA index_info('{$value['name']}');");
											$ars = sqlite_fetch_all($result3);

											$fields = array();
											foreach ($ars as $va)
											{
												$fields[] = $va['name'];
											}

											$sql_data .= 'CREATE ' . ($value['unique'] ? 'UNIQUE ' : '') . 'INDEX ' . $value['name'] . ' on ' . $row['name'] . ' (' . implode(', ', $fields) . ");\n";
										}
										$sql_data .= "\n";
									}
								break;
								case 'postgres':
									$get_seq_sql = "SELECT * FROM pg_class WHERE NOT relname ~ 'pg_.*'
										AND relkind = 'S' ORDER BY relname";

									$seq = $db->sql_query($get_seq_sql);

									if ($num_seq = $db->sql_numrows($seq))
									{
										$return_val = "\n# Sequences \n";
										$i_seq = 0;

										while($i_seq < $num_seq)
										{
											$row = $db->sql_fetchrow($seq);
											$sequence = $row['relname'];

											$bool = false;
											foreach($table as $table_name)
											{
												if (strpos($sequence, $table_name) === false)
												{
													continue;
												}
												$bool = true;
												break;
											}

											// Don't create a sequence for tables we don't create
											if (!$bool)
											{
												$i_seq++;
												continue;
											}

											$get_props_sql = "SELECT * FROM $sequence";
											$seq_props = $db->sql_query($get_props_sql);

											if ($db->sql_numrows($seq_props) > 0)
											{
												$row1 = $db->sql_fetchrow($seq_props);
												$row1['last_value'] = 1;

												$return_val .= "DROP SEQUENCE $sequence;\n";
												$return_val .= "CREATE SEQUENCE $sequence START " . $row1['last_value'] . ' INCREMENT ' . $row1['increment_by'] . ' MAXVALUE ' . $row1['max_value'] . ' MINVALUE ' . $row1['min_value'] . ' CACHE ' . $row1['cache_value'] . ";\n";
											}

											$i_seq++;

										}
										$sql_data .= $return_val . "\n";

									}

									foreach ($table as $table_name)
									{
										$field_query = "SELECT a.attnum, a.attname AS field, t.typname as type, a.attlen AS length, a.atttypmod as lengthvar, a.attnotnull as notnull
											FROM pg_class c, pg_attribute a, pg_type t
											WHERE c.relname = '$table_name'
												AND a.attnum > 0
												AND a.attrelid = c.oid
												AND a.atttypid = t.oid
											ORDER BY a.attnum";
										$result = $db->sql_query($field_query);

										$sql_data .= '# Table: ' . $table_name . "\n";
										$sql_data .= "DROP TABLE $table_name;\n";
										$sql_data .= "CREATE TABLE $table_name(\n";
										$lines = array();
										while ($row = $db->sql_fetchrow($result))
										{
											//
											// Get the data from the table
											//
											$sql_get_default = "SELECT d.adsrc AS rowdefault
												FROM pg_attrdef d, pg_class c
												WHERE (c.relname = '$table_name')
													AND (c.oid = d.adrelid)
													AND d.adnum = " . $row['attnum'];
											$def_res = $db->sql_query($sql_get_default);

											if (!$def_res)
											{
												unset($row['rowdefault']);
											}
											else
											{
												$row['rowdefault'] = @pg_fetch_result($def_res, 0, 'rowdefault');
											}

											if ($row['type'] == 'bpchar')
											{
												// Internally stored as bpchar, but isn't accepted in a CREATE TABLE statement.
												$row['type'] = 'char';
											}

											$line = '  ' . $row['field'] . ' ' . $row['type'];

											if (strpos($row['type'], 'char') !== false)
											{
												if ($row['lengthvar'] > 0)
												{
													$line .= '(' . ($row['lengthvar'] - 4) . ')';
												}
											}

											if (strpos($row['type'], 'numeric') !== false)
											{
												$line .= '(';
												$line .= sprintf("%s,%s", (($row['lengthvar'] >> 16) & 0xffff), (($row['lengthvar'] - 4) & 0xffff));
												$line .= ')';
											}

											if (!empty($row['rowdefault']))
											{
												$line .= ' DEFAULT ' . $row['rowdefault'];
											}

											if ($row['notnull'] == 't')
											{
												$line .= ' NOT NULL';
											}
											$lines[] = $line;
										}


										// Get the listing of primary keys.
										$sql_pri_keys = "SELECT ic.relname AS index_name, bc.relname AS tab_name, ta.attname AS column_name, i.indisunique AS unique_key, i.indisprimary AS primary_key
											FROM pg_class bc, pg_class ic, pg_index i, pg_attribute ta, pg_attribute ia
											WHERE (bc.oid = i.indrelid)
												AND (ic.oid = i.indexrelid)
												AND (ia.attrelid = i.indexrelid)
												AND	(ta.attrelid = bc.oid)
												AND (bc.relname = '$table_name')
												AND (ta.attrelid = i.indrelid)
												AND (ta.attnum = i.indkey[ia.attnum-1])
											ORDER BY index_name, tab_name, column_name ";
										$result = $db->sql_query($sql_pri_keys);

										$index_create = $index_rows = $primary_key = array();

										// We do this in two steps. It makes placing the comma easier
										while ($row = $db->sql_fetchrow($result))
										{
											if ($row['primary_key'] == 't')
											{
												$primary_key[] = $row['column_name'];
												$primary_key_name = $row['index_name'];

											}
											else
											{
												// We have to store this all this info because it is possible to have a multi-column key...
												// we can loop through it again and build the statement
												$index_rows[$row['index_name']]['table'] = $table_name;
												$index_rows[$row['index_name']]['unique'] = ($row['unique_key'] == 't') ? true : false;
												$index_rows[$row['index_name']]['column_names'][] = $row['column_name'];
											}
										}

										if (!empty($index_rows))
										{
											foreach ($index_rows as $idx_name => $props)
											{
												$index_create[] = 'CREATE ' . ($props['unique'] ? 'UNIQUE ' : '') . "INDEX $idx_name ON $table_name (" . implode(', ', $props['column_names']) . ");";
											}
										}

										if (!empty($primary_key))
										{
											$lines[] = "  CONSTRAINT $primary_key_name PRIMARY KEY (" . implode(', ', $primary_key) . ")";
										}

										// Generate constraint clauses for CHECK constraints
										$sql_checks = "SELECT conname as index_name, consrc
											FROM pg_constraint, pg_class bc
											WHERE conrelid = bc.oid
												AND bc.relname = '$table_name'
												AND NOT EXISTS (
													SELECT *
														FROM pg_constraint as c, pg_inherits as i
														WHERE i.inhrelid = pg_constraint.conrelid
															AND c.conname = pg_constraint.conname
															AND c.consrc = pg_constraint.consrc
															AND c.conrelid = i.inhparent
												)";
										$result = $db->sql_query($sql_checks);

										if (!$result)
										{
											message_die(GENERAL_ERROR, "Failed in get_table_def (show fields)", "", __LINE__, __FILE__, $sql_checks);
										}

										// Add the constraints to the sql file.
										while ($row = $db->sql_fetchrow($result))
										{
											if (!is_null($row['consrc']))
											{
												$lines[] = '  CONSTRAINT ' . $row['index_name'] . ' CHECK ' . $row['consrc'];
											}
										}

										$sql_data .= implode(", \n", $lines);
										$sql_data .= "\n);\n";

										if (!empty($index_create))
										{
											$sql_data .= implode("\n", $index_create) . "\n\n";
										}
									}
								break;

								default:
									trigger_error('KungFuDeathGrip');
							}
						}

						// Data
						if ($type == 'full' || $type == 'data')
						{
							$sql_data .= "\n";
							switch (SQL_LAYER)
							{
								case 'mysqli':
								case 'mysql4':
								case 'mysql':
									foreach ($table as $name)
									{
										$col_types = array();
										$col = $db->sql_query("SHOW COLUMNS FROM $name");
										while ($row = $db->sql_fetchrow($col))
										{
											$col_types[$row['Field']] = $row['Type'];
										}
										$sql = "SELECT * FROM $name";
										$result = $db->sql_query($sql);

										while ($row = $db->sql_fetchrow($result))
										{
											$sql_data .= 'INSERT INTO ' . $name . ' (';
											$names = $data = array();
											foreach ($row as $row_name => $row_data)
											{
												$names[] = $row_name;

												// Figure out what this data is, escape it properly
												if (is_null($row_data))
												{
													$row_data = 'NULL';
												}
												else if ($row_data == '')
												{
													$row_data = "''";
												}
												else if (strpos($col_types[$row_name], 'text') !== false || strpos($col_types[$row_name], 'char') !== false)
												{
													$row_data = "'" . $row_data . "'";
												}

												$data[] = $row_data;
											}
											$sql_data .= implode(', ', $names) . ') VALUES ('. implode(', ', $data) .");\n";
										}
									}
								break;
								case 'sqlite':
									foreach ($table as $name)
									{
										$col_types = sqlite_fetch_column_types($name, $db->db_connect_id);
										$sql = "SELECT * FROM $name";
										$result = $db->sql_query($sql);

										while ($row = $db->sql_fetchrow($result))
										{
											$sql_data .= 'INSERT INTO ' . $name . ' (';
											$names = $data = array();
											foreach ($row as $row_name => $row_data)
											{
												$names[] = $row_name;

												// Figure out what this data is, escape it properly
												if (is_null($row_data))
												{
													$row_data = 'NULL';
												}
												else if ($row_data == '')
												{
													$row_data = "''";
												}
												else if (strpos($col_types[$row_name], 'text') !== false || strpos($col_types[$row_name], 'char') !== false)
												{
													$row_data = "'" . $row_data . "'";
												}

												$data[] = $row_data;
											}
											$sql_data .= implode(', ', $names) . ') VALUES ('. implode(', ', $data) .");\n";
										}
									}
								break;

								case 'postgres':
									foreach ($table as $name)
									{
										$aryType = $aryName = array();
										// Grab all of the data from current table.
										$sql = "SELECT * FROM {$name}";
										$result = $db->sql_query($sql);

										$i_num_fields = pg_num_fields($result);

										for ($i = 0; $i < $i_num_fields; $i++)
										{
											$aryType[] = pg_field_type($result, $i);
											$aryName[] = pg_field_name($result, $i);
										}

										while ($row = $db->sql_fetchrow($result))
										{
											$schema_vals = $schema_fields = array();
											// Build the SQL statement to recreate the data.
											for ($i = 0; $i < $i_num_fields; $i++)
											{
												$strVal = $row[$aryName[$i]];

												if (preg_match('#char|text|bool#i', $aryType[$i]))
												{
													$strQuote = "'";
													$strEmpty = '';
													$strVal = addslashes($strVal);
												}
												else if (preg_match('#date|timestamp#i', $aryType[$i]))
												{
													if (empty($strVal))
													{
														$strQuote = '';
													}
													else
													{
														$strQuote = "'";
													}
												}
												else
												{
													$strQuote = '';
													$strEmpty = 'NULL';
												}

												if (empty($strVal) && $strVal !== '0')
												{
													$strVal = $strEmpty;
												}

												$schema_vals[] = $strQuote . $strVal . $strQuote;
												$schema_fields[] = $aryName[$i];
											}

											// Take the ordered fields and their associated data and build it
											// into a valid sql statement to recreate that field in the data.
											$sql_data .= "INSERT INTO $name (" . implode(', ', $schema_fields) . ') VALUES(' . implode(', ', $schema_vals) . ");\n";
										}
									}
								break;

								default:
									trigger_error('KungFuDeathGrip');
							}
						}

						switch (SQL_LAYER)
						{
							case 'sqlite':
								$sql_data .= "COMMIT;";
							break;
						}

						// Base file name
						$file = $phpbb_root_path . 'store/' . $filename . $format;

						switch ($format)
						{
							case '.zip':
							case '.tar.bz2':
							case '.tar.gz':
							case '.tar':

								if ($format == '.zip')
								{
									$compress = new compress_zip('w', $file);
								}
								else
								{
									$compress = new compress_tar('w', $file, $format);
								}

								$compress->add_data($sql_data, "$filename.sql");
								$compress->close();
								if ($where == 'download')
								{
									$compress->download($filename);
									exit;
								}
							break;

							case '.sql':

								$handle = @fopen($file, 'a');
								@fwrite($handle, $sql_data);
								@fclose($handle);
								if ($where == 'download')
								{
									$mimetype = 'text/sql';

									header('Pragma: no-cache');
									header("Content-Type: $mimetype; name=\"$filename.sql\"");
									header("Content-disposition: attachment; filename=$filename.sql");

									$fp = fopen("{$phpbb_root_path}store/$filename.sql", 'rb');
									while ($buffer = fread($fp, 1024))
									{
										echo $buffer;
									}
									fclose($fp);
									exit;
								}
						}
						add_log('admin', 'LOG_DB_BACKUP');
						trigger_error($user->lang['BACKUP_SUCCESS']);
					break;

					default:
						$tables = array();
						switch (SQL_LAYER)
						{
							case 'sqlite':
								$sql = "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name";
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									if (strpos($row['name'] . '_', $table_prefix) === 0)
									{
										$tables[] = $row['name'];
									}
								}
							break;
							
							case 'mysqli':
							case 'mysql4':
							case 'mysql':
								$sql = "SHOW TABLES LIKE '{$table_prefix}%'";
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									$tables[] = current($row);
								}
							break;

							case 'postgres':
								$sql = "SELECT relname FROM pg_stat_user_tables ORDER BY relname;";
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									if (strpos($row['relname'] . '_', $table_prefix) === 0)
									{
										$tables[] = $row['relname'];
									}
								}
							break;

							default:
								trigger_error('KungFuDeathGrip');
						}

						foreach ($tables as $table)
						{
							$template->assign_block_vars('tables', array(
								'TABLE'	=> $table
							));
						}

						$template->assign_vars(array(
							'U_ACTION'	=> $this->u_action . '&amp;action=download'
						));
						
						$methods = array('.sql');
						$methods = array_merge($methods, compress::methods());
						foreach ($methods as $type)
						{
							$template->assign_block_vars('methods', array(
								'TYPE'	=> $type
							));
						}
					break;
				}
			break;

			case 'restore':
				switch ($action)
				{
					case 'submit':
						$file = request_var('file', '');
						preg_match('#^(\d{10})\.(sql|zip|tar(?:\.(?:gz|bz2))?)$#', $file, $matches);
						$format = '.' . $matches[2];
						switch ($format)
						{
							case '.zip':
							case '.tar.bz2':
							case '.tar.gz':
							case '.tar':
								if ($format == '.zip')
								{
									$compress = new compress_zip('r', $phpbb_root_path . 'store/' . $file);
								}
								else
								{
									$compress = new compress_tar('r', $phpbb_root_path . 'store/' . $file, $format);
								}

								$compress->extract($phpbb_root_path . 'store/');
								$compress->close();
							break;
						}

						$data = file_get_contents($phpbb_root_path . 'store/' . $matches[1] . '.sql');
						if ($data != '')
						{
							// Strip out sql comments...
							remove_remarks($data);
							$pieces = split_sql_file($data, ';');

							$sql_count = count($pieces);
							for($i = 0; $i < $sql_count; $i++)
							{
								$sql = trim($pieces[$i]);

								if (!empty($sql) && $sql[0] != '#')
								{
									$db->sql_query($sql);
								}
							}
						}
						add_log('admin', 'LOG_DB_RESTORE');
						trigger_error($user->lang['RESTORE_SUCCESS']);
					break;

					default:
						$selected = $stop = false;
						$methods = compress::methods();
						$methods[] = '.sql';

						$dir = $phpbb_root_path . 'store/';
						$dh = opendir($dir);
						while (($file = readdir($dh)) !== false)
						{
							if (preg_match('#^\d{10}\.(sql|zip|tar(?:\.(?:gz|bz2))?)$#', $file, $matches))
							{
								$supported = in_array('.' . $matches[1], $methods);
								if ($supported && !$selected && !$stop)
								{
									$selected = true;
									$stop = true;
								}
								else
								{
									$selected = false;
								}
								$template->assign_block_vars('files', array(
									'FILE'		=> $file,
									'SUPPORTED'	=> $supported,
									'SELECTED'	=> $selected
								));
							}
						}
						closedir($dh);

						$template->assign_vars(array(
							'U_ACTION'	=> $this->u_action . '&amp;action=submit'
						));
					break;
				}
			break;
		}
	}
}

/**
* @package module_install
*/
class acp_database_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_database',
			'title'		=> 'ACP_DATABASE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'backup'	=> array('title' => 'ACP_BACKUP', 'auth' => 'acl_a_server'),
				'restore'	=> array('title' => 'ACP_RESTORE', 'auth' => 'acl_a_server'),
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