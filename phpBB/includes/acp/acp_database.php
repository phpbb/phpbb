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
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		
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

						$filename = 'backup_' . time();

						// We set up the info needed for our on-the-fly creation :D
						switch ($format)
						{
							case 'text':
								$ext = '.sql';
								$open = 'fopen';
								$write = 'fwrite';
								$close = 'fclose';
								$oper = '';
								$mimetype = 'text/x-sql';
							break;
							case 'bzip2':
								$ext = '.sql.bz2';
								$open = 'bzopen';
								$write = 'bzwrite';
								$close = 'bzclose';
								$oper = 'bzcompress';
								$mimetype = 'application/x-bzip2';
							break;
							case 'gzip':
								$ext = '.sql.gz';
								$open = 'gzopen';
								$write = 'gzwrite';
								$close = 'gzclose';
								$oper = 'gzencode';
								$mimetype = 'application/x-gzip';
							break;
						}

						// We write the file to "store" first (and then compress the file) to not use too much
						// memory. The server process can be easily killed by storing too much data at once.

						
						if ($store == true)
						{
							$file = $phpbb_root_path . 'store/' . $filename . $ext;

							$fp = $open($file, 'w');

							if (!$fp)
							{
								trigger_error('Unable to write temporary file to storage folder');
							}
						}

						if ($download == true)
						{
							$name = $filename . $ext;
							header('Pragma: no-cache');
							header("Content-Type: $mimetype; name=\"$name\"");
							header("Content-disposition: attachment; filename=$name");
						}

						// All of the generated queries go here
						$sql_data = '';
						$sql_data .= "#\n";
						$sql_data .= "# phpBB Backup Script\n";
						$sql_data .= "# Dump of tables for $table_prefix\n";
						$sql_data .= "# DATE : " .  gmdate("d-m-Y H:i:s", $filename) . " GMT\n";
						$sql_data .= "#\n";

						switch (SQL_LAYER)
						{
							case 'sqlite':
								$sql_data .= "BEGIN TRANSACTION;\n";
								$sqlite_version = sqlite_libversion();
							break;

							case 'postgres':
								$sql_data .= "BEGIN;\n";
							break;

							case 'mssql':
							case 'mssql_odbc':
								$sql_data .= "BEGIN TRANSACTION\n";
								$sql_data .= "GO\n";
							break;
						}

						foreach ($table as $table_name)
						{
							// Get the table structure
							if ($structure)
							{
								switch (SQL_LAYER)
								{
									case 'mysqli':
									case 'mysql4':
									case 'mysql':
										$sql_data .= '# Table: ' . $table_name . "\n";
										$sql_data .= "DROP TABLE IF EXISTS $table_name;\n";
									break;
									
									case 'oracle':
										$sql_data .= '# Table: ' . $table_name . "\n";
										$sql_data .= "DROP TABLE $table_name;\n";
										$sql_data .= '\\' . "\n";
									break;

									case 'sqlite':
										$sql_data .= '# Table: ' . $table_name . "\n";
										if (version_compare($sqlite_version, '3.0') == -1)
										{
											$sql_data .= "DROP TABLE $table_name;\n";
										}
										else
										{
											$sql_data .= "DROP TABLE IF EXISTS $table_name;\n";
										}
									break;

									case 'postgres':
									case 'firebird':
										$sql_data .= '# Table: ' . $table_name . "\n";
										$sql_data .= "DROP TABLE $table_name;\n";
									break;

									case 'mssql':
									case 'mssql_odbc':
										$sql_data .= '# Table: ' . $table_name . "\n";
										$sql_data .= "IF OBJECT_ID(N'$table_name', N'U') IS NOT NULL\n";
										$sql_data .= "DROP TABLE $table_name;\n";
										$sql_data .= "GO\n";
									break;
								}
								$sql_data .= $this->get_table_structure($table_name);
							}
							// We might wanna empty out all that junk :D
							else
							{
								switch (SQL_LAYER)
								{
									case 'mysqli':
									case 'mysql4':
									case 'mysql':
									case 'mssql':
									case 'mssql_odbc':
									case 'oracle':
									case 'postgres':
									case 'firebird':
										$sql_data .= 'TRUNCATE TABLE ' . $table_name . ";\n";
									break;
									
									case 'sqlite':
										$sql_data .= 'DELETE FROM ' . $table_name . ";\n";
									break;
								}
							}
							// Now write the data for the first time. :)
							if ($store == true)
							{
								$write($fp, $sql_data);
							}

							if ($download == true)
							{
								if (!empty($oper))
								{
									echo $oper($sql_data);
								}
								else
								{
									echo $sql_data;
								}
							}

							$sql_data = '';

							// Data
							if ($schema_data)
							{
								$sql_data .= "\n";

								switch (SQL_LAYER)
								{
									case 'mysqli':

										$sql = "SELECT *
											FROM $table_name";
										$result = mysqli_query($db->db_connect_id, $sql, MYSQLI_USE_RESULT);
										if ($result != false)
										{
											$fields_cnt = mysqli_num_fields($result);

											// Get field information
											$field = mysqli_fetch_fields($result);
											$field_set = array();

											for ($j = 0; $j < $fields_cnt; $j++)
											{
												$field_set[$j] = $field[$j]->name;
											}

											$search			= array('\\', "'", "\x00", "\x0a", "\x0d", "\x1a");
											$replace		= array('\\\\\\\\', "''", '\0', '\n', '\r', '\Z');
											$fields			= implode(', ', $field_set);
											$values			= array();
											$schema_insert	= 'INSERT INTO ' . $table_name . ' (' . $fields . ') VALUES (';

											while ($row = mysqli_fetch_row($result))
											{
												for ($j = 0; $j < $fields_cnt; $j++)
												{
													if (!isset($row[$j]) || is_null($row[$j]))
													{
														$values[$j] = 'NULL';
													}
													else if (($field[$j]->flags & 32768) && !($field[$j]->flags & 1024))
													{
														$values[$j] = $row[$j];
													}
													else
													{
														$values[$j] = "'" . str_replace($search, $replace, $row[$j]) . "'";
													}
												}
												$sql_data .= $schema_insert . implode(', ', $values) . ");\n";

												if ($store == true)
												{
													$write($fp, $sql_data);
												}

												if ($download == true)
												{
													if (!empty($oper))
													{
														echo $oper($sql_data);
													}
													else
													{
														echo $sql_data;
													}
												}
												$sql_data = '';

												$values	= array();
											}
											mysqli_free_result($result);
										}
									break;

									case 'mysql4':
									case 'mysql':
	
										$sql = "SELECT *
											FROM $table_name";
										$result = mysql_unbuffered_query($sql, $db->db_connect_id);

										if ($result != false)
										{
											$fields_cnt = mysql_num_fields($result);

											// Get field information
											$field = array();
											for ($i = 0; $i < $fields_cnt; $i++) 
											{
												$field[$i] = mysql_fetch_field($result, $i);
											}
											$field_set = array();
											
											for ($j = 0; $j < $fields_cnt; $j++)
											{
												$field_set[$j] = $field[$j]->name;
											}

											$search			= array('\\', "'", "\x00", "\x0a", "\x0d", "\x1a");
											$replace		= array('\\\\\\\\', "''", '\0', '\n', '\r', '\Z');
											$fields			= implode(', ', $field_set);
											$schema_insert	= 'INSERT INTO ' . $table_name . ' (' . $fields . ') VALUES (';

											while ($row = mysql_fetch_row($result))
											{
												$values = array();

												for ($j = 0; $j < $fields_cnt; $j++)
												{
													if (!isset($row[$j]) || is_null($row[$j]))
													{
														$values[$j] = 'NULL';
													}
													else if ($field[$j]->numeric && ($field[$j]->type !== 'timestamp'))
													{
														$values[$j] = $row[$j];
													}
													else
													{
														$values[$j] = "'" . str_replace($search, $replace, $row[$j]) . "'";
													}
												}
												$sql_data .= $schema_insert . implode(', ', $values) . ");\n";

												if ($store == true)
												{
													$write($fp, $sql_data);
												}

												if ($download == true)
												{
													if (!empty($oper))
													{
														echo $oper($sql_data);
													}
													else
													{
														echo $sql_data;
													}
												}
												$sql_data = '';
											}
											mysql_free_result($result);
										}
									break;
	
									case 'sqlite':
										// This is *not* my fault. The PHP guys forgot a call to finalize when they wrote this function. This forces all the tables to stay locked...
										// They finally fixed it in 5.1.3 but 5.1.2 and under still have this so instead, we go and grab the column types by smashing open the sqlite_master table
										// and grope around for things that remind us of datatypes...
										if (version_compare(phpversion(), '5.1.3', '>='))
										{
											$col_types = sqlite_fetch_column_types($db->db_connect_id, $table_name);
										}
										else
										{
											$sql = "SELECT sql
												FROM sqlite_master 
												WHERE type = 'table' 
													AND name = '" . $table_name . "'";
											$table_data = sqlite_single_query($db->db_connect_id, $sql);
											$table_data = preg_replace('#CREATE\s+TABLE\s+"?' . $table_name . '"?#i', '', $table_data);
											$table_data = trim($table_data);

											preg_match('#\((.*)\)#s', $table_data, $matches);

											$column_list = array();
											$table_cols = explode(',', trim($matches[1]));
											foreach($table_cols as $declaration)
											{
												$entities = preg_split('#\s+#', trim($declaration));
												$column_name = preg_replace('/"?([^"]+)"?/', '\1', $entities[0]);

												// Hit a primary key, those are not what we need :D
												if (empty($entities[1]))
												{
													continue;
												}
												$col_types[$column_name] = $entities[1];
											}
										}

										// Unbueffered query and the foreach make this ultra fast, we wait for nothing.
										$sql = "SELECT *
											FROM $table_name";
										$result = sqlite_unbuffered_query($db->db_connect_id, $sql);
										$rows = sqlite_fetch_all($result, SQLITE_ASSOC);

										foreach ($rows as $row)
										{
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
											$sql_data .= 'INSERT INTO ' . $table_name . ' (' . implode(', ', $names) . ') VALUES ('. implode(', ', $data) .");\n";

											if ($store == true)
											{
												$write($fp, $sql_data);
											}

											if ($download == true)
											{
												if (!empty($oper))
												{
													echo $oper($sql_data);
												}
												else
												{
													echo $sql_data;
												}
											}
											$sql_data = '';

										}
										$db->sql_freeresult($result);
									break;

									case 'postgres':

										$ary_type = $ary_name = array();
										
										// Grab all of the data from current table.
										$sql = "SELECT *
											FROM $table_name";
										$result = $db->sql_query($sql);

										$i_num_fields = pg_num_fields($result);
										$seq = '';

										for ($i = 0; $i < $i_num_fields; $i++)
										{
											$ary_type[$i] = pg_field_type($result, $i);
											$ary_name[$i] = pg_field_name($result, $i);


											$sql = "SELECT pg_get_expr(d.adbin, d.adrelid) as rowdefault
												FROM pg_attrdef d, pg_class c
												WHERE (c.relname = '{$table_name}')
													AND (c.oid = d.adrelid)
													AND d.adnum = " . strval($i + 1);
											$result2 = $db->sql_query($sql);
											if ($row = $db->sql_fetchrow($result2))
											{
												// Determine if we must reset the sequences
												if (strpos($row['rowdefault'], "nextval('") === 0)
												{
													$seq .= "SELECT SETVAL('{$table_name}_seq',(select case when max({$ary_name[$i]})>0 then max({$ary_name[$i]})+1 else 1 end FROM {$table_name}));\n";
												}
											}
										}

										while ($row = $db->sql_fetchrow($result))
										{
											$schema_vals = $schema_fields = array();

											// Build the SQL statement to recreate the data.
											for ($i = 0; $i < $i_num_fields; $i++)
											{
												$str_val = $row[$ary_name[$i]];

												if (preg_match('#char|text|bool#i', $ary_type[$i]))
												{
													$str_quote = "'";
													$str_empty = '';
													$str_val = addslashes($str_val);
												}
												else if (preg_match('#date|timestamp#i', $ary_type[$i]))
												{
													if (empty($str_val))
													{
														$str_quote = '';
													}
													else
													{
														$str_quote = "'";
													}
												}
												else
												{
													$str_quote = '';
													$str_empty = 'NULL';
												}

												if (empty($str_val) && $str_val !== '0')
												{
													$str_val = $str_empty;
												}

												$schema_vals[$i] = $str_quote . $str_val . $str_quote;
												$schema_fields[$i] = $ary_name[$i];
											}

											// Take the ordered fields and their associated data and build it
											// into a valid sql statement to recreate that field in the data.
											$sql_data .= "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ");\n";

											if ($store == true)
											{
												$write($fp, $sql_data);
											}

											if ($download == true)
											{
												if (!empty($oper))
												{
													echo $oper($sql_data);
												}
												else
												{
													echo $sql_data;
												}
											}

											$sql_data = '';

										}
										$db->sql_freeresult($result);

										// Write out the sequence statements
										if ($store == true)
										{
											$write($fp, $seq);
										}

										if ($download == true)
										{
											if (!empty($oper))
											{
												echo $oper($seq);
											}
											else
											{
												echo $seq;
											}
										}
										$seq = '';
									break;

									case 'mssql_odbc':
										$ary_type = $ary_name = array();
										$ident_set = false;
										
										// Grab all of the data from current table.
										$sql = "SELECT *
											FROM $table_name";
										$result = $db->sql_query($sql);

										$retrieved_data = odbc_num_rows($result);

										if ($retrieved_data)
										{
											$sql = "SELECT 1 as has_identity
												FROM INFORMATION_SCHEMA.COLUMNS
												WHERE COLUMNPROPERTY(object_id('$table_name'), COLUMN_NAME, 'IsIdentity') = 1";
											$result2 = $db->sql_query($sql);
											$row2 = $db->sql_fetchrow($result2);
											if (!empty($row2['has_identity']))
											{
												$sql_data .= "\nSET IDENTITY_INSERT $table_name ON\nGO\n";
												$ident_set = true;
											}
											$db->sql_freeresult($result2);
										}

										$i_num_fields = odbc_num_fields($result);

										for ($i = 0; $i < $i_num_fields; $i++)
										{
											$ary_type[$i] = odbc_field_type($result, $i);
											$ary_name[$i] = odbc_field_name($result, $i);
										}

										while ($row = $db->sql_fetchrow($result))
										{
											$schema_vals = $schema_fields = array();

											// Build the SQL statement to recreate the data.
											for ($i = 0; $i < $i_num_fields; $i++)
											{
												$str_val = $row[$ary_name[$i]];

												if (preg_match('#char|text|bool#i', $ary_type[$i]))
												{
													$str_quote = "'";
													$str_empty = '';
													$str_val = addslashes($str_val);
												}
												else if (preg_match('#date|timestamp#i', $ary_type[$i]))
												{
													if (empty($str_val))
													{
														$str_quote = '';
													}
													else
													{
														$str_quote = "'";
													}
												}
												else
												{
													$str_quote = '';
													$str_empty = 'NULL';
												}

												if (empty($str_val) && $str_val !== '0' && !(is_int($str_val) || is_float($str_val)))
												{
													$str_val = $str_empty;
												}

												$schema_vals[$i] = $str_quote . $str_val . $str_quote;
												$schema_fields[$i] = $ary_name[$i];
											}

											// Take the ordered fields and their associated data and build it
											// into a valid sql statement to recreate that field in the data.
											$sql_data .= "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ");\n";

											if ($store == true)
											{
												$write($fp, $sql_data);
											}

											if ($download == true)
											{
												if (!empty($oper))
												{
													echo $oper($sql_data);
												}
												else
												{
													echo $sql_data;
												}
											}

											$sql_data = '';

										}
										$db->sql_freeresult($result);

										if ($retrieved_data)
										{
											$sql_data = "\nGO\n";
											if ($ident_set)
											{
												$sql_data .= "\nSET IDENTITY_INSERT $table_name OFF\nGO\n";
											}
										}
									break;

									case 'mssql':
										$ary_type = $ary_name = array();
										$ident_set = false;
										
										// Grab all of the data from current table.
										$sql = "SELECT *
											FROM $table_name";
										$result = $db->sql_query($sql);

										$retrieved_data = mssql_num_rows($result);

										$i_num_fields = mssql_num_fields($result);

										for ($i = 0; $i < $i_num_fields; $i++)
										{
											$ary_type[$i] = mssql_field_type($result, $i);
											$ary_name[$i] = mssql_field_name($result, $i);
										}

										if ($retrieved_data)
										{
											$sql = "SELECT 1 as has_identity
												FROM INFORMATION_SCHEMA.COLUMNS
												WHERE COLUMNPROPERTY(object_id('$table_name'), COLUMN_NAME, 'IsIdentity') = 1";
											$result2 = $db->sql_query($sql);
											$row2 = $db->sql_fetchrow($result2);
											if (!empty($row2['has_identity']))
											{
												$sql_data .= "\nSET IDENTITY_INSERT $table_name ON\nGO\n";
												$ident_set = true;
											}
											$db->sql_freeresult($result2);
										}

										while ($row = $db->sql_fetchrow($result))
										{
											$schema_vals = $schema_fields = array();

											// Build the SQL statement to recreate the data.
											for ($i = 0; $i < $i_num_fields; $i++)
											{
												$str_val = $row[$ary_name[$i]];

												if (preg_match('#char|text|bool#i', $ary_type[$i]))
												{
													$str_quote = "'";
													$str_empty = '';
													$str_val = addslashes($str_val);
												}
												else if (preg_match('#date|timestamp#i', $ary_type[$i]))
												{
													if (empty($str_val))
													{
														$str_quote = '';
													}
													else
													{
														$str_quote = "'";
													}
												}
												else
												{
													$str_quote = '';
													$str_empty = 'NULL';
												}

												if (empty($str_val) && $str_val !== '0' && !(is_int($str_val) || is_float($str_val)))
												{
													$str_val = $str_empty;
												}

												$schema_vals[$i] = $str_quote . $str_val . $str_quote;
												$schema_fields[$i] = $ary_name[$i];
											}

											// Take the ordered fields and their associated data and build it
											// into a valid sql statement to recreate that field in the data.
											$sql_data .= "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ");\n";

											if ($store == true)
											{
												$write($fp, $sql_data);
											}

											if ($download == true)
											{
												if (!empty($oper))
												{
													echo $oper($sql_data);
												}
												else
												{
													echo $sql_data;
												}
											}

											$sql_data = '';

										}
										$db->sql_freeresult($result);

										if ($retrieved_data)
										{
											$sql_data = "\nGO\n";
											if ($ident_set)
											{
												$sql_data .= "\nSET IDENTITY_INSERT $table_name OFF\nGO\n";
											}
										}
									break;

									case 'firebird':

										$ary_type = $ary_name = array();
										
										// Grab all of the data from current table.
										$sql = "SELECT *
											FROM $table_name";
										$result = $db->sql_query($sql);

										$i_num_fields = ibase_num_fields($result);

										for ($i = 0; $i < $i_num_fields; $i++)
										{
											$info = ibase_field_info($result, $i);
											$ary_type[$i] = $info['type'];
											$ary_name[$i] = $info['name'];
										}

										while ($row = $db->sql_fetchrow($result))
										{
											$schema_vals = $schema_fields = array();

											// Build the SQL statement to recreate the data.
											for ($i = 0; $i < $i_num_fields; $i++)
											{
												$str_val = $row[strtolower($ary_name[$i])];

												if (preg_match('#char|text|bool#i', $ary_type[$i]))
												{
													$str_quote = "'";
													$str_empty = '';
													$str_val = addslashes($str_val);
												}
												else if (preg_match('#date|timestamp#i', $ary_type[$i]))
												{
													if (empty($str_val))
													{
														$str_quote = '';
													}
													else
													{
														$str_quote = "'";
													}
												}
												else
												{
													$str_quote = '';
													$str_empty = 'NULL';
												}

												if (empty($str_val) && $str_val !== '0')
												{
													$str_val = $str_empty;
												}

												$schema_vals[$i] = $str_quote . $str_val . $str_quote;
												$schema_fields[$i] = "'" . $ary_name[$i] . "'";
											}

											// Take the ordered fields and their associated data and build it
											// into a valid sql statement to recreate that field in the data.
											$sql_data .= "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ");\n";

											if ($store == true)
											{
												$write($fp, $sql_data);
											}

											if ($download == true)
											{
												if (!empty($oper))
												{
													echo $oper($sql_data);
												}
												else
												{
													echo $sql_data;
												}
											}

											$sql_data = '';

										}
										$db->sql_freeresult($result);
									break;

									case 'oracle':
										$ary_type = $ary_name = array();
										
										// Grab all of the data from current table.
										$sql = "SELECT *
											FROM $table_name";
										$result = $db->sql_query($sql);

										$i_num_fields = ocinumcols($result);

										for ($i = 0; $i < $i_num_fields; $i++)
										{
											$ary_type[$i] = ocicolumntype($result, $i);
											$ary_name[$i] = ocicolumnname($result, $i);
										}

										while ($row = $db->sql_fetchrow($result))
										{
											$schema_vals = $schema_fields = array();

											// Build the SQL statement to recreate the data.
											for ($i = 0; $i < $i_num_fields; $i++)
											{
												$str_val = $row[$ary_name[$i]];

												if (preg_match('#char|text|bool#i', $ary_type[$i]))
												{
													$str_quote = "'";
													$str_empty = '';
													$str_val = addslashes($str_val);
												}
												else if (preg_match('#date|timestamp#i', $ary_type[$i]))
												{
													if (empty($str_val))
													{
														$str_quote = '';
													}
													else
													{
														$str_quote = "'";
													}
												}
												else
												{
													$str_quote = '';
													$str_empty = 'NULL';
												}

												if (empty($str_val) && $str_val !== '0')
												{
													$str_val = $str_empty;
												}

												$schema_vals[$i] = $str_quote . $str_val . $str_quote;
												$schema_fields[$i] = '"' . $ary_name[$i] . "'";
											}

											// Take the ordered fields and their associated data and build it
											// into a valid sql statement to recreate that field in the data.
											$sql_data .= "INSERT INTO $table_name (" . implode(', ', $schema_fields) . ') VALUES (' . implode(', ', $schema_vals) . ");\n";

											if ($store == true)
											{
												$write($fp, $sql_data);
											}

											if ($download == true)
											{
												if (!empty($oper))
												{
													echo $oper($sql_data);
												}
												else
												{
													echo $sql_data;
												}
											}

											$sql_data = '';

										}
										$db->sql_freeresult($result);
									break;
								}
							}
						}

						switch (SQL_LAYER)
						{
							case 'sqlite':
							case 'postgres':
								$sql_data .= "COMMIT;";
							break;

							case 'mssql':
							case 'mssql_odbc':
								$sql_data .= "COMMIT\nGO";
							break;
						}
						
						if ($store == true)
						{
							$write($fp, $sql_data);
							$close($fp);
						}

						if ($download == true)
						{
							if (!empty($oper))
							{
								echo $oper($sql_data);
							}
							else
							{
								echo $sql_data;
							}
							exit;
						}

						unset($sql_data);

						add_log('admin', 'LOG_DB_BACKUP');
						trigger_error($user->lang['BACKUP_SUCCESS']);
					break;

					default:
						$tables = array();
						switch (SQL_LAYER)
						{
							case 'sqlite':
								$sql = "SELECT name
									FROM sqlite_master
									WHERE type='table'
									ORDER BY name";
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									if (strpos($row['name'], $table_prefix) === 0)
									{
										$tables[] = $row['name'];
									}
								}
								$db->sql_freeresult($result);
							break;
							
							case 'mysqli':
							case 'mysql4':
							case 'mysql':
								$sql = "SHOW TABLES
									LIKE '{$table_prefix}%'";
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									$tables[] = current($row);
								}
								$db->sql_freeresult($result);
							break;

							case 'postgres':
								$sql = 'SELECT relname
									FROM pg_stat_user_tables
									ORDER BY relname';
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									if (strpos($row['relname'], $table_prefix) === 0)
									{
										$tables[] = $row['relname'];
									}
								}
								$db->sql_freeresult($result);
							break;

							case 'mssql':
							case 'mssql_odbc':
								$sql = "SELECT TABLE_NAME
									FROM INFORMATION_SCHEMA.TABLES
									WHERE TABLE_TYPE = 'BASE TABLE'
									ORDER BY TABLE_NAME";
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									if (strpos($row['TABLE_NAME'], $table_prefix) === 0)
									{
										$tables[] = $row['TABLE_NAME'];
									}
								}
								$db->sql_freeresult($result);
							break;

							case 'firebird':
								$sql = 'SELECT RDB$RELATION_NAME as TABLE_NAME
									FROM RDB$RELATIONS
									WHERE RDB$SYSTEM_FLAG=0
										AND RDB$VIEW_BLR IS NULL';
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									if (stripos($row['table_name'], $table_prefix) === 0)
									{
										$tables[] = $row['table_name'];
									}
								}
								$db->sql_freeresult($result);
							break;

							case 'oracle':
								$sql = 'SELECT TNAME as table_name
									FROM TAB';
								$result = $db->sql_query($sql);
								while ($row = $db->sql_fetchrow($result))
								{
									if (stripos($row['table_name'], $table_prefix) === 0)
									{
										$tables[] = $row['table_name'];
									}
								}
								$db->sql_freeresult($result);
							break;
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
				switch ($action)
				{
					case 'submit':
						$delete = request_var('delete', '');
						$file = request_var('file', '');

						preg_match('#^(backup_\d{10,})\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches);
						$file_name = $phpbb_root_path . 'store/' . $matches[0];

						if (!(file_exists($file_name) && is_readable($file_name)))
						{
							trigger_error($user->lang['BACKUP_INVALID']);
						}

						if ($delete)
						{
							unlink($file_name);
							trigger_error($user->lang['BACKUP_DELETE']);
						}

						$data = file_get_contents($file_name);

						switch ($matches[2])
						{
							case 'sql.bz2':
								$data = bzdecompress($data);
							break;
							case 'sql.gz':
								$data = gzinflate(substr($data, 10));
							break;
						}

						$download = request_var('download', '');

						if ($download)
						{
							$name = $matches[0];

							switch ($matches[2])
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

							header('Pragma: no-cache');
							header("Content-Type: $mimetype; name=\"$name\"");
							header("Content-disposition: attachment; filename=$name");
							echo $data;
							die;
						}

						if (!empty($data))
						{
							// Strip out sql comments...
							remove_remarks($data);

							// SQLite gets improved performance when you shove all of these disk write queries at once :D
							if (SQL_LAYER == 'sqlite')
							{
								$db->sql_query($data);
							}
							else
							{
								switch (SQL_LAYER)
								{
									case 'firebird':
										$delim = ';;';
									break;

									case 'mysql':
									case 'mysql4':
									case 'mysqli':
									case 'postgres':
										$delim = ';';
									break;

									case 'oracle':
										$delim = '/';
									break;

									case 'mssql':
									case 'mssql-odbc':
										$delim = 'GO';
									break;
								}
								$pieces = split_sql_file($data, $delim);

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
						}
						add_log('admin', 'LOG_DB_RESTORE');
						trigger_error($user->lang['RESTORE_SUCCESS']);
					break;

					default:
						$selected = false;
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
						$dh = opendir($dir);
						while (($file = readdir($dh)) !== false)
						{
							if (preg_match('#^backup_(\d{10,})\.(sql(?:\.(?:gz|bz2))?)$#', $file, $matches))
							{
								$supported = in_array($matches[2], $methods);

								if ($supported == 'true')
								{
									$template->assign_block_vars('files', array(
										'FILE'		=> $file,
										'NAME'		=> gmdate("d-m-Y H:i:s", $matches[1]),
										'SUPPORTED'	=> $supported
									));
									$selected = true;
								}
							}
						}
						closedir($dh);

						if ($selected === true)
						{
							$template->assign_var('EXISTS', true);
						}

						$template->assign_vars(array(
							'U_ACTION'	=> $this->u_action . '&amp;action=submit'
						));
					break;
				}
			break;
		}
	}

	/**
	* Return table structure
	*/
	function get_table_structure($table_name)
	{
		global $db, $domains_created;

		$sql_data = '';

		switch (SQL_LAYER)
		{
			case 'mysqli':
			case 'mysql4':
			case 'mysql':

				$sql_data .= "CREATE TABLE $table_name(\n";
				$rows = array();

				$sql = "SHOW FIELDS
					FROM $table_name";
				$result = $db->sql_query($sql);

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
				$db->sql_freeresult($result);

				$sql = "SHOW KEYS
					FROM $table_name";

				$result = $db->sql_query($sql);

				$index = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$kname = $row['Key_name'];

					if ($kname != 'PRIMARY' && $row['Non_unique'] == 0)
					{
						$kname = "UNIQUE|$kname";
					}

					$index[$kname][] = $row['Column_name'];
				}
				$db->sql_freeresult($result);

				foreach ($index as $key => $columns)
				{
					$line = '   ';

					if ($key == 'PRIMARY')
					{
						$line .= 'PRIMARY KEY (' . implode(', ', $columns) . ')';
					}
					else if (strpos($key, 'UNIQUE') === 0)
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

			break;

			case 'sqlite':

				$sql = "SELECT sql
					FROM sqlite_master 
					WHERE type = 'table' 
						AND name = '" . $db->sql_escape($table_name) . "'
					ORDER BY type DESC, name;";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				// Create Table
				$sql_data .= $row['sql'] . "\n";

				$result = $db->sql_query("PRAGMA index_list('" . $db->sql_escape($table_name) . "');");

				$ar = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$ar[] = $row;
				}
				$db->sql_freeresult($result);
				
				foreach ($ar as $value)
				{
					if (strpos($value['name'], 'autoindex') !== false)
					{
						continue;
					}

					$result = $db->sql_query("PRAGMA index_info('" . $db->sql_escape($value['name']) . "');");

					$fields = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$fields[] = $row['name'];
					}
					$db->sql_freeresult($result);

					$sql_data .= 'CREATE ' . ($value['unique'] ? 'UNIQUE ' : '') . 'INDEX ' . $value['name'] . ' on ' . $table_name . ' (' . implode(', ', $fields) . ");\n";
				}

				$sql_data .= "\n";
			break;

			case 'postgres':
				if (empty($domains_created))
				{
					$domains_created = array();
				}

				$sql = "SELECT a.domain_name, a.data_type, a.character_maximum_length, a.domain_default
					FROM INFORMATION_SCHEMA.domains a, INFORMATION_SCHEMA.column_domain_usage b
					WHERE a.domain_name = b.domain_name
						AND b.table_name = '{$table_name}'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					if (empty($domains_created[$row['domain_name']]))
					{
						$domains_created[$row['domain_name']] = true;
						$sql_data .= "CREATE DOMAIN {$row['domain_name']} as {$row['data_type']}";
						if (!empty($row['character_maximum_length']))
						{
							$sql_data .= '(' . $row['character_maximum_length'] . ')';
						}
						$sql_data .= ' NOT NULL';
						if (!empty($row['domain_default']))
						{
							$sql_data .= 'DEFAULT ' . $row['domain_default'];
						}
						$sql_data .= ";\n";
					}
				}

				// PGSQL does not "tightly" bind sequences and tables, we must guess...
				$sql = "SELECT relname
					FROM pg_class
					WHERE relkind = 'S'
						AND relname = '{$table_name}_seq'";
				$result = $db->sql_query($sql);
				// We don't even care about storing the results. We already know the answer if we get rows back.
				if ($db->sql_fetchrow($result))
				{
					$sql_data .= "DROP SEQUENCE {$table_name}_seq;\n";
					$sql_data .= "CREATE SEQUENCE {$table_name}_seq;\n";
				}
				$db->sql_freeresult($result);
			
				$field_query = "SELECT a.attnum, a.attname as field, t.typname as type, a.attlen as length, a.atttypmod as lengthvar, a.attnotnull as notnull
					FROM pg_class c, pg_attribute a, pg_type t
					WHERE c.relname = '" . $db->sql_escape($table_name) . "'
						AND a.attnum > 0
						AND a.attrelid = c.oid
						AND a.atttypid = t.oid
					ORDER BY a.attnum";
				$result = $db->sql_query($field_query);

				$sql_data .= "CREATE TABLE $table_name(\n";
				$lines = array();
				while ($row = $db->sql_fetchrow($result))
				{
					// Get the data from the table
					$sql_get_default = "SELECT pg_get_expr(d.adbin, d.adrelid) as rowdefault
						FROM pg_attrdef d, pg_class c
						WHERE (c.relname = '" . $db->sql_escape($table_name) . "')
							AND (c.oid = d.adrelid)
							AND d.adnum = " . $row['attnum'];
					$def_res = $db->sql_query($sql_get_default);

					if (!$def_res)
					{
						unset($row['rowdefault']);
					}
					else
					{
						$row['rowdefault'] = $db->sql_fetchfield('rowdefault', 0, $def_res);
					}
					$db->sql_freeresult($def_res);

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
				$db->sql_freeresult($result);


				// Get the listing of primary keys.
				$sql_pri_keys = "SELECT ic.relname as index_name, bc.relname as tab_name, ta.attname as column_name, i.indisunique as unique_key, i.indisprimary as primary_key
					FROM pg_class bc, pg_class ic, pg_index i, pg_attribute ta, pg_attribute ia
					WHERE (bc.oid = i.indrelid)
						AND (ic.oid = i.indexrelid)
						AND (ia.attrelid = i.indexrelid)
						AND	(ta.attrelid = bc.oid)
						AND (bc.relname = '" . $db->sql_escape($table_name) . "')
						AND (ta.attrelid = i.indrelid)
						AND (ta.attnum = i.indkey[ia.attnum-1])
					ORDER BY index_name, tab_name, column_name";

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
				$db->sql_freeresult($result);

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
						AND bc.relname = '" . $db->sql_escape($table_name) . "'
						AND NOT EXISTS (
							SELECT *
								FROM pg_constraint as c, pg_inherits as i
								WHERE i.inhrelid = pg_constraint.conrelid
									AND c.conname = pg_constraint.conname
									AND c.consrc = pg_constraint.consrc
									AND c.conrelid = i.inhparent
						)";
				$result = $db->sql_query($sql_checks);

				// Add the constraints to the sql file.
				while ($row = $db->sql_fetchrow($result))
				{
					if (!is_null($row['consrc']))
					{
						$lines[] = '  CONSTRAINT ' . $row['index_name'] . ' CHECK ' . $row['consrc'];
					}
				}
				$db->sql_freeresult($result);

				$sql_data .= implode(", \n", $lines);
				$sql_data .= "\n);\n";

				if (!empty($index_create))
				{
					$sql_data .= implode("\n", $index_create) . "\n\n";
				}
			break;

			case 'mssql':
			case 'mssql_odbc':
				$sql_data .= "\nCREATE TABLE [$table_name] (\n";
				$rows = array();

				$text_flag = false;

				$sql = "SELECT COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMNPROPERTY(object_id(TABLE_NAME), COLUMN_NAME, 'IsIdentity') as IS_IDENTITY
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_NAME = '$table_name'";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$line = "\t[{$row['COLUMN_NAME']}] [{$row['DATA_TYPE']}]";

					if ($row['DATA_TYPE'] == 'text')
					{
						$text_flag = true;
					}

					if ($row['IS_IDENTITY'])
					{
						$line .= ' IDENTITY (1 , 1)';
					}

					if ($row['CHARACTER_MAXIMUM_LENGTH'] && $row['DATA_TYPE'] !== 'text')
					{
						$line .= ' (' . $row['CHARACTER_MAXIMUM_LENGTH'] . ')';
					}

					if ($row['IS_NULLABLE'] == 'YES')
					{
						$line .= ' NULL';
					}
					else
					{
						$line .= ' NOT NULL';
					}

					if ($row['COLUMN_DEFAULT'])
					{
						$line .= ' DEFAULT ' . $row['COLUMN_DEFAULT'];
					}

					$rows[] = $line;
				}
				$db->sql_freeresult($result);

				$sql_data .= implode(",\n", $rows);
				$sql_data .= "\n) ON [PRIMARY]";

				if ($text_flag)
				{
					$sql_data .= " TEXTIMAGE_ON [PRIMARY]";
				}

				$sql_data .= "\nGO\n\n";
				$rows = array();

				$sql = "SELECT CONSTRAINT_NAME, COLUMN_NAME
					FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
					WHERE TABLE_NAME = '$table_name'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					if (!sizeof($rows))
					{
						$sql_data .= "ALTER TABLE [$table_name] WITH NOCHECK ADD\n";
						$sql_data .= "\tCONSTRAINT [{$row['CONSTRAINT_NAME']}] PRIMARY KEY  CLUSTERED \n\t(\n";
					}
					$rows[] = "\t\t[{$row['COLUMN_NAME']}]";
				}
				if (sizeof($rows))
				{
					$sql_data .= implode(",\n", $rows);
					$sql_data .= "\n\t)  ON [PRIMARY] \nGO\n";
				}
				$db->sql_freeresult($result);

				$index = array();
				$sql = "EXEC sp_statistics '$table_name'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['TYPE'] == 3)
					{
						$index[$row['INDEX_NAME']][] = '[' . $row['COLUMN_NAME'] . ']';
					}
				}
				$db->sql_freeresult($result);

				foreach ($index as $index_name => $column_name)
				{
					$index[$index_name] = implode(', ', $index[$index_name]);
				}

				foreach ($index as $index_name => $columns)
				{
					$sql_data .= "\nCREATE  INDEX [$index_name] ON [$table_name]($columns) ON [PRIMARY]\nGO\n";
				}
			break;

			case 'firebird':

				$data_types = array(7 => 'SMALLINT', 8 => 'INTEGER', 10 => 'FLOAT', 12 => 'DATE', 13 => 'TIME', 14 => 'CHARACTER', 27 => 'DOUBLE PRECISION', 35 => 'TIMESTAMP', 37 => 'VARCHAR', 40 => 'CSTRING', 261 => 'BLOB', 701 => 'DECIMAL', 702 => 'NUMERIC');

				$sql_data .= "\nCREATE TABLE $table_name (\n";

				$sql  = 'SELECT DISTINCT R.RDB$FIELD_NAME as FNAME, R.RDB$NULL_FLAG as NFLAG, R.RDB$DEFAULT_SOURCE as DSOURCE, F.RDB$FIELD_TYPE as FTYPE, F.RDB$FIELD_SUB_TYPE as STYPE, F.RDB$FIELD_LENGTH as FLEN
					FROM RDB$RELATION_FIELDS R
					JOIN RDB$FIELDS F ON R.RDB$FIELD_SOURCE=F.RDB$FIELD_NAME
					LEFT JOIN RDB$FIELD_DIMENSIONS D ON R.RDB$FIELD_SOURCE = D.RDB$FIELD_NAME
					WHERE F.RDB$SYSTEM_FLAG = 0
						AND R.RDB$RELATION_NAME = \''. $table_name . '\'
					ORDER BY R.RDB$FIELD_POSITION';
				$result = $db->sql_query($sql);

				$rows = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$line = "\t" . '"' . $row['fname'] . '" ' . $data_types[$row['ftype']];

					if ($row['ftype'] == 261 && $row['stype'] == 1)
					{
						$line .= ' SUB_TYPE TEXT';
					}

					if ($row['ftype'] == 37 || $row['ftype'] == 14)
					{
						$line .= ' (' . $row['flen'] . ')';
					}

					if (!empty($row['dsource']))
					{
						$line .= ' ' . $row['dsource'];
					}

					if (!empty($row['nflag']))
					{
						$line .= ' NOT NULL';
					}
					$rows[] = $line;
				}
				$db->sql_freeresult($result);

				$sql_data .= implode(",\n", $rows);
				$sql_data .= "\n);;\n";
				$keys = array();

				$sql  = 'SELECT I.RDB$FIELD_NAME as NAME
					FROM RDB$RELATION_CONSTRAINTS RC, RDB$INDEX_SEGMENTS I, RDB$INDICES IDX
					WHERE (I.RDB$INDEX_NAME = RC.RDB$INDEX_NAME)
						AND (IDX.RDB$INDEX_NAME = RC.RDB$INDEX_NAME)
						AND (RC.RDB$RELATION_NAME = \''. $table_name . '\')
					ORDER BY I.RDB$FIELD_POSITION';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$keys[] = $row['name'];
				}

				if (sizeof($keys))
				{
					$sql_data .= "\nALTER TABLE $table_name ADD PRIMARY KEY (" . implode(', ', $keys) . ');;';
				}

				$db->sql_freeresult($result);

				$sql = 'SELECT I.RDB$INDEX_NAME as INAME, I.RDB$UNIQUE_FLAG as UFLAG, S.RDB$FIELD_NAME as FNAME
					FROM RDB$INDICES I JOIN RDB$INDEX_SEGMENTS S ON S.RDB$INDEX_NAME=I.RDB$INDEX_NAME
					WHERE (I.RDB$SYSTEM_FLAG IS NULL  OR  I.RDB$SYSTEM_FLAG=0)
						AND I.RDB$FOREIGN_KEY IS NULL
						AND I.RDB$RELATION_NAME = \''. $table_name . '\'
						AND I.RDB$INDEX_NAME NOT STARTING WITH \'RDB$\'
					ORDER BY S.RDB$FIELD_POSITION';
				$result = $db->sql_query($sql);

				$index = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$index[$row['iname']]['unique'] = !empty($row['uflag']);
					$index[$row['iname']]['values'][] = $row['fname'];
				}

				foreach ($index as $index_name => $data)
				{
					$sql_data .= "\nCREATE ";
					if ($data['unique'])
					{
						$sql_data .= 'UNIQUE ';
					}
					$sql_data .= "INDEX $index_name ON $table_name(" . implode(', ', $data['values']) . ");;";
				}
				$sql_data .= "\n";

				$db->sql_freeresult($result);

				$sql = 'SELECT D1.RDB$DEPENDENT_NAME as DNAME, D1.RDB$FIELD_NAME as FNAME, D1.RDB$DEPENDENT_TYPE, R1.RDB$RELATION_NAME
					FROM RDB$DEPENDENCIES D1
					LEFT JOIN RDB$RELATIONS R1 ON ((D1.RDB$DEPENDENT_NAME = R1.RDB$RELATION_NAME) AND (NOT (R1.RDB$VIEW_BLR IS NULL)))
					WHERE (D1.RDB$DEPENDED_ON_TYPE = 0)
						AND (D1.RDB$DEPENDENT_TYPE <> 3)
						AND (D1.RDB$DEPENDED_ON_NAME = \'' . $table_name . '\')
					UNION SELECT DISTINCT F2.RDB$RELATION_NAME, D2.RDB$FIELD_NAME, D2.RDB$DEPENDENT_TYPE, R2.RDB$RELATION_NAME FROM RDB$DEPENDENCIES D2, RDB$RELATION_FIELDS F2
					LEFT JOIN RDB$RELATIONS R2 ON ((F2.RDB$RELATION_NAME = R2.RDB$RELATION_NAME) AND (NOT (R2.RDB$VIEW_BLR IS NULL)))
					WHERE (D2.RDB$DEPENDENT_TYPE = 3)
						AND (D2.RDB$DEPENDENT_NAME = F2.RDB$FIELD_SOURCE)
						AND (D2.RDB$DEPENDED_ON_NAME = \'' . $table_name . '\')
					ORDER BY 1, 2';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'SELECT T1.RDB$DEPENDED_ON_NAME as GEN, T1.RDB$FIELD_NAME, T1.RDB$DEPENDED_ON_TYPE
						FROM RDB$DEPENDENCIES T1
						WHERE (T1.RDB$DEPENDENT_NAME = \'' . $row['dname'] . '\')
							AND (T1.RDB$DEPENDENT_TYPE = 2 AND T1.RDB$DEPENDED_ON_TYPE = 14)
						UNION ALL SELECT DISTINCT D.RDB$DEPENDED_ON_NAME, D.RDB$FIELD_NAME, D.RDB$DEPENDED_ON_TYPE
						FROM RDB$DEPENDENCIES D, RDB$RELATION_FIELDS F
						WHERE (D.RDB$DEPENDENT_TYPE = 3)
							AND (D.RDB$DEPENDENT_NAME = F.RDB$FIELD_SOURCE)
							AND (F.RDB$RELATION_NAME = \'' . $row['dname'] . '\')
						ORDER BY 1,2';
					$result2 = $db->sql_query($sql);
					$row2 = $db->sql_fetchrow($result2);
					$db->sql_freeresult($result2);
					$gen_name = $row2['gen'];

					$sql_data .= "\nCREATE GENERATOR " . $gen_name . ";;";
					$sql_data .= "\nSET GENERATOR  " . $gen_name . " TO 0;;\n";
					$sql_data .= "\nCREATE TRIGGER {$row['dname']} FOR $table_name";
					$sql_data .= "\nBEFORE INSERT\nAS\nBEGIN";
					$sql_data .= "\n  NEW.{$row['fname']} = GEN_ID(" . $gen_name . ", 1);";
					$sql_data .= "\nEND;;\n";
				}

				$db->sql_freeresult($result);
			break;

			case 'oracle':
				$sql_data .= "\nCREATE TABLE $table_name (\n";

				$sql  = "SELECT COLUMN_NAME, DATA_TYPE, DATA_PRECISION, DATA_LENGTH, NULLABLE, DATA_DEFAULT
					FROM ALL_TAB_COLS
					WHERE table_name = '{$table_name}'";
				$result = $db->sql_query($sql);

				$rows = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$line = '  "' . $row['column_name'] . '" ' . $row['data_type'];

					if ($row['data_type'] !== 'CLOB')
					{
						if ($row['data_type'] !== 'VARCHAR2')
						{
							$line .= '(' . $row['data_precision'] . ')';
						}
						else
						{
							$line .= '(' . $row['data_length'] . ')';
						}
					}

					if (!empty($row['data_default']))
					{
						$line .= ' DEFAULT ' . $row['data_default'];
					}

					if ($row['nullable'] == 'N')
					{
						$line .= ' NOT NULL';
					}
					$rows[] = $line;
				}
				$db->sql_freeresult($result);

				$sql = "SELECT A.CONSTRAINT_NAME, A.COLUMN_NAME
					FROM USER_CONS_COLUMNS A, USER_CONSTRAINTS B
					WHERE A.CONSTRAINT_NAME = B.CONSTRAINT_NAME
						AND B.CONSTRAINT_TYPE = 'P'
						AND A.TABLE_NAME = '{$table_name}'";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$rows[] = "  CONSTRAINT {$row['constraint_name']} PRIMARY KEY ({$row['column_name']})";
				}
				$db->sql_freeresult($result);

				$sql = "SELECT A.CONSTRAINT_NAME, A.COLUMN_NAME
					FROM USER_CONS_COLUMNS A, USER_CONSTRAINTS B
					WHERE A.CONSTRAINT_NAME = B.CONSTRAINT_NAME
						AND B.CONSTRAINT_TYPE = 'U'
						AND A.TABLE_NAME = '{$table_name}'";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$rows[] = "  CONSTRAINT {$row['constraint_name']} UNIQUE ({$row['column_name']})";
				}
				$db->sql_freeresult($result);

				$sql_data .= implode(",\n", $rows);
				$sql_data .= "\n)\n\\";

				$sql = "SELECT A.REFERENCED_NAME
					FROM USER_DEPENDENCIES A, USER_TRIGGERS B
					WHERE A.REFERENCED_TYPE = 'SEQUENCE'
						AND A.NAME = B.TRIGGER_NAME
						AND B. TABLE_NAME = '{$table_name}'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_data .= "\nCREATE SEQUENCE {$row['referenced_name']}\\\n";
				}
				$db->sql_freeresult($result);

				$sql = "SELECT DESCRIPTION, WHEN_CLAUSE, TRIGGER_BODY
					FROM USER_TRIGGERS
					WHERE TABLE_NAME = '{$table_name}'";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_data .= "\nCREATE OR REPLACE TRIGGER {$row['description']}WHEN ({$row['when_clause']})\n{$row['trigger_body']}\\";
				}
				$db->sql_freeresult($result);

				$sql = "SELECT A.INDEX_NAME, B.COLUMN_NAME
					FROM USER_INDEXES A, USER_IND_COLUMNS B
					WHERE A.UNIQUENESS = 'NONUNIQUE'
						AND A.INDEX_NAME = B.INDEX_NAME
						AND B.TABLE_NAME = '{$table_name}'";
				$result = $db->sql_query($sql);

				$index = array();

				while ($row = $db->sql_fetchrow($result))
				{
					$index[$row['index_name']][] = $row['column_name'];
				}

				foreach ($index as $index_name => $column_names)
				{
					$sql_data .= "\nCREATE INDEX $index_name ON $table_name(" . implode(', ', $column_names) . ")\n\\";
				}
				$db->sql_freeresult($result);

			break;
		}

		return $sql_data;
	}
}

?>