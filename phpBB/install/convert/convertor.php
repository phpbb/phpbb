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

namespace phpbb\convert;

use phpbb\install\controller\helper;
use phpbb\template\template;

/**
 * Convertor backend class
 *
 * WARNING: This file did not meant to be present in a production environment, so moving this file to a location which
 * 			is accessible after board installation might lead to security issues.
 */
class convertor
{
	/**
	 * @var helper
	 */
	protected $controller_helper;

	/**
	 * @var \phpbb\filesystem\filesystem
	 */
	protected $filesystem;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param template	$template
	 * @param helper	$controller_helper
	 */
	public function __construct(template $template, helper $controller_helper)
	{
		global $convert, $phpbb_filesystem;

		$this->template = $template;
		$this->filesystem = $phpbb_filesystem;
		$this->controller_helper = $controller_helper;

		$convert = new convert($this);
	}

	/**
	 * The function which does the actual work (or dispatches it to the relevant places)
	 */
	function convert_data($converter)
	{
		global $user, $phpbb_root_path, $phpEx, $db, $lang, $config, $cache, $auth;
		global $convert, $convert_row, $message_parser, $skip_rows, $language;
		global $request, $phpbb_dispatcher;

		$phpbb_config_php_file = new \phpbb\config_php_file($phpbb_root_path, $phpEx);
		extract($phpbb_config_php_file->get_all());

		require_once($phpbb_root_path . 'includes/constants.' . $phpEx);
		require_once($phpbb_root_path . 'includes/functions_convert.' . $phpEx);

		$dbms = $phpbb_config_php_file->convert_30_dbms_to_31($dbms);

		/** @var \phpbb\db\driver\driver_interface $db */
		$db = new $dbms();
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, true);
		unset($dbpasswd);

		// We need to fill the config to let internal functions correctly work
		$config = new \phpbb\config\db($db, new \phpbb\cache\driver\dummy, CONFIG_TABLE);

		// Override a couple of config variables for the duration
		$config['max_quote_depth'] = 0;

		// @todo Need to confirm that max post length in source is <= max post length in destination or there may be interesting formatting issues
		$config['max_post_chars'] = $config['min_post_chars'] = 0;

		// Set up a user as well. We _should_ have enough of a database here at this point to do this
		// and it helps for any core code we call
		$user->session_begin();
		$user->page = $user->extract_current_page($phpbb_root_path);

		$convert->options = array();
		if (isset($config['convert_progress']))
		{
			$convert->options = unserialize($config['convert_progress']);
			$convert->options = array_merge($convert->options, unserialize($config['convert_db_server']), unserialize($config['convert_db_user']), unserialize($config['convert_options']));
		}

		// This information should have already been checked once, but do it again for safety
		if (empty($convert->options) || empty($convert->options['tag']) ||
			!isset($convert->options['dbms']) ||
			!isset($convert->options['dbhost']) ||
			!isset($convert->options['dbport']) ||
			!isset($convert->options['dbuser']) ||
			!isset($convert->options['dbpasswd']) ||
			!isset($convert->options['dbname']) ||
			!isset($convert->options['table_prefix']))
		{
			$this->error($user->lang['NO_CONVERT_SPECIFIED'], __LINE__, __FILE__);
		}

		$this->template->assign_var('S_CONV_IN_PROGRESS', true);

		// Make some short variables accessible, for easier referencing
		$convert->convertor_tag = basename($convert->options['tag']);
		$convert->src_dbms = $convert->options['dbms'];
		$convert->src_dbhost = $convert->options['dbhost'];
		$convert->src_dbport = $convert->options['dbport'];
		$convert->src_dbuser = $convert->options['dbuser'];
		$convert->src_dbpasswd = $convert->options['dbpasswd'];
		$convert->src_dbname = $convert->options['dbname'];
		$convert->src_table_prefix = $convert->options['table_prefix'];

		// initiate database connection to old db if old and new db differ
		global $src_db, $same_db;
		$src_db = $same_db = null;
		if ($convert->src_dbms != $dbms || $convert->src_dbhost != $dbhost || $convert->src_dbport != $dbport || $convert->src_dbname != $dbname || $convert->src_dbuser != $dbuser)
		{
			$dbms = $convert->src_dbms;
			/** @var \phpbb\db\driver\driver $src_db */
			$src_db = new $dbms();
			$src_db->sql_connect($convert->src_dbhost, $convert->src_dbuser, htmlspecialchars_decode($convert->src_dbpasswd), $convert->src_dbname, $convert->src_dbport, false, true);
			$same_db = false;
		}
		else
		{
			$src_db = $db;
			$same_db = true;
		}

		$convert->mysql_convert = false;
		switch ($src_db->sql_layer)
		{
			case 'sqlite3':
				$convert->src_truncate_statement = 'DELETE FROM ';
				break;

			// Thanks MySQL, for silently converting...
			case 'mysql':
			case 'mysql4':
				if (version_compare($src_db->sql_server_info(true, false), '4.1.3', '>='))
				{
					$convert->mysql_convert = true;
				}
				$convert->src_truncate_statement = 'TRUNCATE TABLE ';
				break;

			case 'mysqli':
				$convert->mysql_convert = true;
				$convert->src_truncate_statement = 'TRUNCATE TABLE ';
				break;

			default:
				$convert->src_truncate_statement = 'TRUNCATE TABLE ';
				break;
		}

		if ($convert->mysql_convert && !$same_db)
		{
			$src_db->sql_query("SET NAMES 'binary'");
		}

		switch ($db->get_sql_layer())
		{
			case 'sqlite3':
				$convert->truncate_statement = 'DELETE FROM ';
				break;

			default:
				$convert->truncate_statement = 'TRUNCATE TABLE ';
				break;
		}

		$get_info = false;

		// check security implications of direct inclusion
		if (!file_exists('./convertors/convert_' . $convert->convertor_tag . '.' . $phpEx))
		{
			$this->error($user->lang['CONVERT_NOT_EXIST'], __LINE__, __FILE__);
		}

		if (file_exists('./convertors/functions_' . $convert->convertor_tag . '.' . $phpEx))
		{
			include_once('./convertors/functions_' . $convert->convertor_tag . '.' . $phpEx);
		}

		$get_info = true;
		include('./convertors/convert_' . $convert->convertor_tag . '.' . $phpEx);

		// Map some variables...
		$convert->convertor_data = $convertor_data;
		$convert->tables = $tables;
		$convert->config_schema = $config_schema;

		// Now include the real data
		$get_info = false;
		include('./convertors/convert_' . $convert->convertor_tag . '.' . $phpEx);

		$convert->convertor_data = $convertor_data;
		$convert->tables = $tables;
		$convert->config_schema = $config_schema;
		$convert->convertor = $convertor;

		// The test_file is a file that should be present in the location of the old board.
		if (!file_exists($convert->options['forum_path'] . '/' . $test_file))
		{
			$this->error(sprintf($user->lang['COULD_NOT_FIND_PATH'], $convert->options['forum_path']), __LINE__, __FILE__);
		}

		$search_type = $config['search_type'];

		// For conversions we are a bit less strict and set to a search backend we know exist...
		if (!class_exists($search_type))
		{
			$search_type = '\phpbb\search\fulltext_native';
			$config->set('search_type', $search_type);
		}

		if (!class_exists($search_type))
		{
			trigger_error('NO_SUCH_SEARCH_MODULE');
		}

		$error = false;
		$convert->fulltext_search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

		if ($error)
		{
			trigger_error($error);
		}

		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		$message_parser = new \parse_message();

		$jump = $request->variable('jump', 0);
		$final_jump = $request->variable('final_jump', 0);
		$sync_batch = $request->variable('sync_batch', -1);
		$last_statement = $request->variable('last', 0);

		// We are running sync...
		if ($sync_batch >= 0)
		{
			$this->sync_forums($converter, $sync_batch);
			return;
		}

		if ($jump)
		{
			$this->jump($converter, $jump, $last_statement);
			return;
		}

		if ($final_jump)
		{
			$this->final_jump($final_jump);
			return;
		}

		$current_table = $request->variable('current_table', 0);
		$old_current_table = min(-1, $current_table - 1);
		$skip_rows = $request->variable('skip_rows', 0);

		if (!$current_table && !$skip_rows)
		{
			if (!$request->variable('confirm', false))
			{
				// If avatars / ranks / smilies folders are specified make sure they are writable
				$bad_folders = array();

				$local_paths = array(
					'avatar_path'			=> path($config['avatar_path']),
					'avatar_gallery_path'	=> path($config['avatar_gallery_path']),
					'icons_path'			=> path($config['icons_path']),
					'ranks_path'			=> path($config['ranks_path']),
					'smilies_path'			=> path($config['smilies_path'])
				);

				foreach ($local_paths as $folder => $local_path)
				{
					if (isset($convert->convertor[$folder]))
					{
						if (empty($convert->convertor['test_file']))
						{
							// test_file is mandantory at the moment so this should never be reached, but just in case...
							$this->error($user->lang['DEV_NO_TEST_FILE'], __LINE__, __FILE__);
						}

						if (!$local_path || !$this->filesystem->is_writable($phpbb_root_path . $local_path))
						{
							if (!$local_path)
							{
								$bad_folders[] = sprintf($user->lang['CONFIG_PHPBB_EMPTY'], $folder);
							}
							else
							{
								$bad_folders[] = $local_path;
							}
						}
					}
				}

				if (sizeof($bad_folders))
				{
					$msg = (sizeof($bad_folders) == 1) ? $user->lang['MAKE_FOLDER_WRITABLE'] : $user->lang['MAKE_FOLDERS_WRITABLE'];
					sort($bad_folders);
					$this->error(sprintf($msg, implode('<br />', $bad_folders)), __LINE__, __FILE__, true);

					$this->template->assign_vars(array(
						'L_SUBMIT'	=> $user->lang['INSTALL_TEST'],
						'U_ACTION'	=> $this->controller_helper->route('phpbb_convert_convert', array('converter' => $converter)),
					));
					return;
				}

				// Grab all the tables used in convertor
				$missing_tables = $tables_list = $aliases = array();

				foreach ($convert->convertor['schema'] as $schema)
				{
					// Skip those not used (because of addons/plugins not detected)
					if (!$schema['target'])
					{
						continue;
					}

					foreach ($schema as $key => $val)
					{
						// we're dealing with an array like:
						// array('forum_status',			'forums.forum_status',				'is_item_locked')
						if (is_int($key) && !empty($val[1]))
						{
							$temp_data = $val[1];
							if (!is_array($temp_data))
							{
								$temp_data = array($temp_data);
							}

							foreach ($temp_data as $value)
							{
								if (preg_match('/([a-z0-9_]+)\.([a-z0-9_]+)\)* ?A?S? ?([a-z0-9_]*?)\.?([a-z0-9_]*)$/i', $value, $m))
								{
									$table = $convert->src_table_prefix . $m[1];
									$tables_list[$table] = $table;

									if (!empty($m[3]))
									{
										$aliases[] = $convert->src_table_prefix . $m[3];
									}
								}
							}
						}
						// 'left_join'		=> 'topics LEFT JOIN vote_desc ON topics.topic_id = vote_desc.topic_id AND topics.topic_vote = 1'
						else if ($key == 'left_join')
						{
							// Convert the value if it wasn't an array already.
							if (!is_array($val))
							{
								$val = array($val);
							}

							for ($j = 0, $size = sizeof($val); $j < $size; ++$j)
							{
								if (preg_match('/LEFT JOIN ([a-z0-9_]+) AS ([a-z0-9_]+)/i', $val[$j], $m))
								{
									$table = $convert->src_table_prefix . $m[1];
									$tables_list[$table] = $table;

									if (!empty($m[2]))
									{
										$aliases[] = $convert->src_table_prefix . $m[2];
									}
								}
							}
						}
					}
				}

				// Remove aliased tables from $tables_list
				foreach ($aliases as $alias)
				{
					unset($tables_list[$alias]);
				}

				// Check if the tables that we need exist
				$src_db->sql_return_on_error(true);
				foreach ($tables_list as $table => $null)
				{
					$sql = 'SELECT 1 FROM ' . $table;
					$_result = $src_db->sql_query_limit($sql, 1);

					if (!$_result)
					{
						$missing_tables[] = $table;
					}
					$src_db->sql_freeresult($_result);
				}
				$src_db->sql_return_on_error(false);

				// Throw an error if some tables are missing
				// We used to do some guessing here, but since we have a suggestion of possible values earlier, I don't see it adding anything here to do it again

				if (sizeof($missing_tables) == sizeof($tables_list))
				{
					$this->error($user->lang['NO_TABLES_FOUND'] . ' ' . $user->lang['CHECK_TABLE_PREFIX'], __LINE__, __FILE__);
				}
				else if (sizeof($missing_tables))
				{
					$this->error(sprintf($user->lang['TABLES_MISSING'], implode($user->lang['COMMA_SEPARATOR'], $missing_tables)) . '<br /><br />' . $user->lang['CHECK_TABLE_PREFIX'], __LINE__, __FILE__);
				}

				$url = $this->save_convert_progress($converter, 'confirm=1');
				$msg = $user->lang['PRE_CONVERT_COMPLETE'];

				if ($convert->convertor_data['author_notes'])
				{
					$msg .= '</p><p>' . sprintf($user->lang['AUTHOR_NOTES'], $convert->convertor_data['author_notes']);
				}

				$this->template->assign_vars(array(
					'L_SUBMIT'		=> $user->lang['CONTINUE_CONVERT'],
					'BODY'			=> $msg,
					'U_ACTION'		=> $url,
				));

				return;
			} // if (!$request->variable('confirm', false)))

			$this->template->assign_block_vars('checks', array(
				'S_LEGEND'		=> true,
				'LEGEND'		=> $user->lang['STARTING_CONVERT'],
			));

			// Convert the config table and load the settings of the old board
			if (!empty($convert->config_schema))
			{
				restore_config($convert->config_schema);

				// Override a couple of config variables for the duration
				$config['max_quote_depth'] = 0;

				// @todo Need to confirm that max post length in source is <= max post length in destination or there may be interesting formatting issues
				$config['max_post_chars'] = $config['min_post_chars'] = 0;
			}

			$this->template->assign_block_vars('checks', array(
				'TITLE'		=> $user->lang['CONFIG_CONVERT'],
				'RESULT'	=> $user->lang['DONE'],
			));

			// Now process queries and execute functions that have to be executed prior to the conversion
			if (!empty($convert->convertor['execute_first']))
			{
				// @codingStandardsIgnoreStart
				eval($convert->convertor['execute_first']);
				// @codingStandardsIgnoreEnd
			}

			if (!empty($convert->convertor['query_first']))
			{
				if (!is_array($convert->convertor['query_first']))
				{
					$convert->convertor['query_first'] = array('target', array($convert->convertor['query_first']));
				}
				else if (!is_array($convert->convertor['query_first'][0]))
				{
					$convert->convertor['query_first'] = array(array($convert->convertor['query_first'][0], $convert->convertor['query_first'][1]));
				}

				foreach ($convert->convertor['query_first'] as $query_first)
				{
					if ($query_first[0] == 'src')
					{
						if ($convert->mysql_convert && $same_db)
						{
							$src_db->sql_query("SET NAMES 'binary'");
						}

						$src_db->sql_query($query_first[1]);

						if ($convert->mysql_convert && $same_db)
						{
							$src_db->sql_query("SET NAMES 'utf8'");
						}
					}
					else
					{
						$db->sql_query($query_first[1]);
					}
				}
			}

			$this->template->assign_block_vars('checks', array(
				'TITLE'		=> $user->lang['PREPROCESS_STEP'],
				'RESULT'	=> $user->lang['DONE'],
			));
		} // if (!$current_table && !$skip_rows)

		$this->template->assign_block_vars('checks', array(
			'S_LEGEND'		=> true,
			'LEGEND'		=> $user->lang['FILLING_TABLES'],
		));

		// This loop takes one target table and processes it
		while ($current_table < sizeof($convert->convertor['schema']))
		{
			$schema = $convert->convertor['schema'][$current_table];

			// The target table isn't set, this can be because a module (for example the attachement mod) is taking care of this.
			if (empty($schema['target']))
			{
				$current_table++;
				continue;
			}

			$this->template->assign_block_vars('checks', array(
				'TITLE'	=> sprintf($user->lang['FILLING_TABLE'], $schema['target']),
			));

			// This is only the case when we first start working on the tables.
			if (!$skip_rows)
			{
				// process execute_first and query_first for this table...
				if (!empty($schema['execute_first']))
				{
					// @codingStandardsIgnoreStart
					eval($schema['execute_first']);
					// @codingStandardsIgnoreEnd
				}

				if (!empty($schema['query_first']))
				{
					if (!is_array($schema['query_first']))
					{
						$schema['query_first'] = array('target', array($schema['query_first']));
					}
					else if (!is_array($schema['query_first'][0]))
					{
						$schema['query_first'] = array(array($schema['query_first'][0], $schema['query_first'][1]));
					}

					foreach ($schema['query_first'] as $query_first)
					{
						if ($query_first[0] == 'src')
						{
							if ($convert->mysql_convert && $same_db)
							{
								$src_db->sql_query("SET NAMES 'binary'");
							}
							$src_db->sql_query($query_first[1]);
							if ($convert->mysql_convert && $same_db)
							{
								$src_db->sql_query("SET NAMES 'utf8'");
							}
						}
						else
						{
							$db->sql_query($query_first[1]);
						}
					}
				}

				if (!empty($schema['autoincrement']))
				{
					switch ($db->get_sql_layer())
					{
						case 'postgres':
							$db->sql_query("SELECT SETVAL('" . $schema['target'] . "_seq',(select case when max(" . $schema['autoincrement'] . ")>0 then max(" . $schema['autoincrement'] . ")+1 else 1 end from " . $schema['target'] . '));');
							break;

						case 'oracle':
							$result = $db->sql_query('SELECT MAX(' . $schema['autoincrement'] . ') as max_id FROM ' . $schema['target']);
							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							$largest_id = (int) $row['max_id'];

							if ($largest_id)
							{
								$db->sql_query('DROP SEQUENCE ' . $schema['target'] . '_seq');
								$db->sql_query('CREATE SEQUENCE ' . $schema['target'] . '_seq START WITH ' . ($largest_id + 1));
							}
							break;
					}
				}
			}

			// Process execute_always for this table
			// This is for code which needs to be executed on every pass of this table if
			// it gets split because of time restrictions
			if (!empty($schema['execute_always']))
			{
				// @codingStandardsIgnoreStart
				eval($schema['execute_always']);
				// @codingStandardsIgnoreEnd
			}

			//
			// Set up some variables
			//
			// $waiting_rows	holds rows for multirows insertion (MySQL only)
			// $src_tables		holds unique tables with aliases to select from
			// $src_fields		will quickly refer source fields (or aliases) corresponding to the current index
			// $select_fields	holds the names of the fields to retrieve
			//

			$sql_data = array(
				'source_fields'		=> array(),
				'target_fields'		=> array(),
				'source_tables'		=> array(),
				'select_fields'		=> array(),
			);

			// This statement is building the keys for later insertion.
			$insert_query = $this->build_insert_query($schema, $sql_data, $current_table);

			// If no source table is affected, we skip the table
			if (empty($sql_data['source_tables']))
			{
				$skip_rows = 0;
				$current_table++;
				continue;
			}

			$distinct = (!empty($schema['distinct'])) ? 'DISTINCT ' : '';

			$sql = 'SELECT ' . $distinct . implode(', ', $sql_data['select_fields']) . " \nFROM " . implode(', ', $sql_data['source_tables']);

			// Where
			$sql .= (!empty($schema['where'])) ? "\nWHERE (" . $schema['where'] . ')' : '';

			// Group By
			if (!empty($schema['group_by']))
			{
				$schema['group_by'] = array($schema['group_by']);
				foreach ($sql_data['select_fields'] as $select)
				{
					$alias = strpos(strtolower($select), ' as ');
					$select = ($alias) ? substr($select, 0, $alias) : $select;
					if (!in_array($select, $schema['group_by']))
					{
						$schema['group_by'][] = $select;
					}
				}
			}
			$sql .= (!empty($schema['group_by'])) ? "\nGROUP BY " . implode(', ', $schema['group_by']) : '';

			// Having
			$sql .= (!empty($schema['having'])) ? "\nHAVING " . $schema['having'] : '';

			// Order By
			if (empty($schema['order_by']) && !empty($schema['primary']))
			{
				$schema['order_by'] = $schema['primary'];
			}
			$sql .= (!empty($schema['order_by'])) ? "\nORDER BY " . $schema['order_by'] : '';

			// Counting basically holds the amount of rows processed.
			$counting = -1;
			$batch_time = 0;

			while ($counting === -1 || ($counting >= $convert->batch_size && still_on_time()))
			{
				$old_current_table = $current_table;

				$rows = '';
				$waiting_rows = array();

				if (!empty($batch_time))
				{
					$mtime = explode(' ', microtime());
					$mtime = $mtime[0] + $mtime[1];
					$rows = ceil($counting/($mtime - $batch_time)) . " rows/s ($counting rows) | ";
				}

				$this->template->assign_block_vars('checks', array(
					'TITLE'		=> "skip_rows = $skip_rows",
					'RESULT'	=> $rows . ((defined('DEBUG') && function_exists('memory_get_usage')) ? ceil(memory_get_usage()/1024) . ' ' . $user->lang['KIB'] : ''),
				));

				$mtime = explode(' ', microtime());
				$batch_time = $mtime[0] + $mtime[1];

				if ($convert->mysql_convert && $same_db)
				{
					$src_db->sql_query("SET NAMES 'binary'");
				}

				// Take skip rows into account and only fetch batch_size amount of rows
				$___result = $src_db->sql_query_limit($sql, $convert->batch_size, $skip_rows);

				if ($convert->mysql_convert && $same_db)
				{
					$src_db->sql_query("SET NAMES 'utf8'");
				}

				// This loop processes each row
				$counting = 0;

				$convert->row = $convert_row = array();

				if (!empty($schema['autoincrement']))
				{
					switch ($db->get_sql_layer())
					{
						case 'mssql_odbc':
						case 'mssqlnative':
							$db->sql_query('SET IDENTITY_INSERT ' . $schema['target'] . ' ON');
							break;
					}
				}

				// Now handle the rows until time is over or no more rows to process...
				while ($counting === 0 || still_on_time())
				{
					$convert_row = $src_db->sql_fetchrow($___result);

					if (!$convert_row)
					{
						// move to the next batch or table
						break;
					}

					// With this we are able to always save the last state
					$convert->row = $convert_row;

					// Increment the counting variable, it stores the number of rows we have processed
					$counting++;

					$insert_values = array();

					$sql_flag = $this->process_row($schema, $sql_data, $insert_values);

					if ($sql_flag === true)
					{
						switch ($db->get_sql_layer())
						{
							// If MySQL, we'll wait to have num_wait_rows rows to submit at once
							case 'mysql':
							case 'mysql4':
							case 'mysqli':
								$waiting_rows[] = '(' . implode(', ', $insert_values) . ')';

								if (sizeof($waiting_rows) >= $convert->num_wait_rows)
								{
									$errored = false;

									$db->sql_return_on_error(true);

									if (!$db->sql_query($insert_query . implode(', ', $waiting_rows)))
									{
										$errored = true;
									}
									$db->sql_return_on_error(false);

									if ($errored)
									{
										$db->sql_return_on_error(true);

										// Because it errored out we will try to insert the rows one by one... most of the time this
										// is caused by duplicate entries - but we also do not want to miss one...
										foreach ($waiting_rows as $waiting_sql)
										{
											if (!$db->sql_query($insert_query . $waiting_sql))
											{
												$this->db_error($user->lang['DB_ERR_INSERT'], htmlspecialchars($insert_query . $waiting_sql) . '<br /><br />' . htmlspecialchars(print_r($db->_sql_error(), true)), __LINE__, __FILE__, true);
											}
										}

										$db->sql_return_on_error(false);
									}

									$waiting_rows = array();
								}

								break;

							default:
								$insert_sql = $insert_query . '(' . implode(', ', $insert_values) . ')';

								$db->sql_return_on_error(true);

								if (!$db->sql_query($insert_sql))
								{
									$this->db_error($user->lang['DB_ERR_INSERT'], htmlspecialchars($insert_sql) . '<br /><br />' . htmlspecialchars(print_r($db->_sql_error(), true)), __LINE__, __FILE__, true);
								}
								$db->sql_return_on_error(false);

								$waiting_rows = array();

								break;
						}
					}

					$skip_rows++;
				}
				$src_db->sql_freeresult($___result);

				// We might still have some rows waiting
				if (sizeof($waiting_rows))
				{
					$errored = false;
					$db->sql_return_on_error(true);

					if (!$db->sql_query($insert_query . implode(', ', $waiting_rows)))
					{
						$errored = true;
					}
					$db->sql_return_on_error(false);

					if ($errored)
					{
						$db->sql_return_on_error(true);

						// Because it errored out we will try to insert the rows one by one... most of the time this
						// is caused by duplicate entries - but we also do not want to miss one...
						foreach ($waiting_rows as $waiting_sql)
						{
							$db->sql_query($insert_query . $waiting_sql);
							$this->db_error($user->lang['DB_ERR_INSERT'], htmlspecialchars($insert_query . $waiting_sql) . '<br /><br />' . htmlspecialchars(print_r($db->_sql_error(), true)), __LINE__, __FILE__, true);
						}

						$db->sql_return_on_error(false);
					}

					$waiting_rows = array();
				}

				if (!empty($schema['autoincrement']))
				{
					switch ($db->get_sql_layer())
					{
						case 'mssql_odbc':
						case 'mssqlnative':
							$db->sql_query('SET IDENTITY_INSERT ' . $schema['target'] . ' OFF');
							break;

						case 'postgres':
							$db->sql_query("SELECT SETVAL('" . $schema['target'] . "_seq',(select case when max(" . $schema['autoincrement'] . ")>0 then max(" . $schema['autoincrement'] . ")+1 else 1 end from " . $schema['target'] . '));');
							break;

						case 'oracle':
							$result = $db->sql_query('SELECT MAX(' . $schema['autoincrement'] . ') as max_id FROM ' . $schema['target']);
							$row = $db->sql_fetchrow($result);
							$db->sql_freeresult($result);

							$largest_id = (int) $row['max_id'];

							if ($largest_id)
							{
								$db->sql_query('DROP SEQUENCE ' . $schema['target'] . '_seq');
								$db->sql_query('CREATE SEQUENCE ' . $schema['target'] . '_seq START WITH ' . ($largest_id + 1));
							}
							break;
					}
				}
			}

			// When we reach this point, either the current table has been processed or we're running out of time.
			if (still_on_time() && $counting < $convert->batch_size/* && !defined('DEBUG')*/)
			{
				$skip_rows = 0;
				$current_table++;
			}
			else
			{/*
				if (still_on_time() && $counting < $convert->batch_size)
				{
					$skip_rows = 0;
					$current_table++;
				}*/

				// Looks like we ran out of time.
				$url = $this->save_convert_progress($converter, 'current_table=' . $current_table . '&amp;skip_rows=' . $skip_rows);

				$current_table++;
//				$percentage = ($skip_rows == 0) ? 0 : floor(100 / ($total_rows / $skip_rows));

				$msg = sprintf($user->lang['STEP_PERCENT_COMPLETED'], $current_table, sizeof($convert->convertor['schema']));

				$this->template->assign_vars(array(
					'BODY'			=> $msg,
					'L_SUBMIT'		=> $user->lang['CONTINUE_CONVERT'],
					'U_ACTION'		=> $url,
				));

				$this->meta_refresh($url);
				return;
			}
		}

		// Process execute_last then we'll be done
		$url = $this->save_convert_progress($converter, 'jump=1');

		$this->template->assign_vars(array(
			'L_SUBMIT'		=> $user->lang['FINAL_STEP'],
			'U_ACTION'		=> $url,
		));

		$this->meta_refresh($url);
		return;
	}

	/**
	 * Sync function being executed at the middle, some functions need to be executed after a successful sync.
	 */
	function sync_forums($converter, $sync_batch)
	{
		global $user, $db, $phpbb_root_path, $phpEx, $config, $cache;
		global $convert;

		include_once ($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		$this->template->assign_block_vars('checks', array(
			'S_LEGEND'	=> true,
			'LEGEND'	=> $user->lang['SYNC_TOPICS'],
		));

		$batch_size = $convert->batch_size;

		$sql = 'SELECT MIN(topic_id) as min_value, MAX(topic_id) AS max_value
			FROM ' . TOPICS_TABLE;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Set values of minimum/maximum primary value for this table.
		$primary_min = $row['min_value'];
		$primary_max = $row['max_value'];

		if ($sync_batch == 0)
		{
			$sync_batch = (int) $primary_min;
		}

		if ($sync_batch == 0)
		{
			$sync_batch = 1;
		}

		// Fetch a batch of rows, process and insert them.
		while ($sync_batch <= $primary_max && still_on_time())
		{
			$end = ($sync_batch + $batch_size - 1);

			// Sync all topics in batch mode...
			sync('topic', 'range', 'topic_id BETWEEN ' . $sync_batch . ' AND ' . $end, true, true);

			$this->template->assign_block_vars('checks', array(
				'TITLE'		=> sprintf($user->lang['SYNC_TOPIC_ID'], $sync_batch, ($sync_batch + $batch_size)) . ((defined('DEBUG') && function_exists('memory_get_usage')) ? ' [' . ceil(memory_get_usage()/1024) . ' ' . $user->lang['KIB'] . ']' : ''),
				'RESULT'	=> $user->lang['DONE'],
			));

			$sync_batch += $batch_size;
		}

		if ($sync_batch >= $primary_max)
		{
			$url = $this->save_convert_progress($converter, 'final_jump=1');

			$this->template->assign_vars(array(
				'L_SUBMIT'		=> $user->lang['CONTINUE_CONVERT'],
				'U_ACTION'		=> $url,
			));

			$this->meta_refresh($url);
			return;
		}
		else
		{
			$sync_batch--;
		}

		$url = $this->save_convert_progress($converter, 'sync_batch=' . $sync_batch);

		$this->template->assign_vars(array(
			'L_SUBMIT'		=> $user->lang['CONTINUE_CONVERT'],
			'U_ACTION'		=> $url,
		));

		$this->meta_refresh($url);
		return;
	}

	/**
	 * Save the convertor status
	 */
	function save_convert_progress($convertor_tag, $step)
	{
		global $config, $convert, $language;

		// Save convertor Status
		$config->set('convert_progress', serialize(array(
			'step'			=> $step,
			'table_prefix'	=> $convert->src_table_prefix,
			'tag'			=> $convert->convertor_tag,
		)), false);

		$config->set('convert_db_server', serialize(array(
			'dbms'			=> $convert->src_dbms,
			'dbhost'		=> $convert->src_dbhost,
			'dbport'		=> $convert->src_dbport,
			'dbname'		=> $convert->src_dbname,
		)), false);

		$config->set('convert_db_user', serialize(array(
			'dbuser'		=> $convert->src_dbuser,
			'dbpasswd'		=> $convert->src_dbpasswd,
		)), false);

		return $this->controller_helper->route('phpbb_convert_convert', array('converter' => $convertor_tag)) . '?' . $step;
	}

	/**
	 * Finish conversion, the last function to be called.
	 */
	function finish_conversion()
	{
		global $db, $phpbb_root_path, $phpEx, $convert, $config, $language, $user;
		global $cache, $auth, $phpbb_container, $phpbb_log;

		include_once ($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		$db->sql_query('DELETE FROM ' . CONFIG_TABLE . "
			WHERE config_name = 'convert_progress'
				OR config_name = 'convert_options'
				OR config_name = 'convert_db_server'
				OR config_name = 'convert_db_user'");
		$db->sql_query('DELETE FROM ' . SESSIONS_TABLE);

		@unlink($phpbb_container->getParameter('core.cache_dir') . 'data_global.' . $phpEx);
		phpbb_cache_moderators($db, $cache, $auth);

		// And finally, add a note to the log
		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_INSTALL_CONVERTED', false, array($convert->convertor_data['forum_name'], $config['version']));

		$url = $this->controller_helper->route('phpbb_convert_finish');

		$this->template->assign_vars(array(
			'L_SUBMIT'		=> $user->lang['FINAL_STEP'],
			'U_ACTION'		=> $url,
		));

		$this->meta_refresh($url);
		return;
	}

	/**
	 * This function marks the steps after syncing
	 */
	function final_jump($final_jump)
	{
		global $user, $src_db, $same_db, $db, $phpbb_root_path, $phpEx, $config, $cache;
		global $convert;

		$this->template->assign_block_vars('checks', array(
			'S_LEGEND'	=> true,
			'LEGEND'	=> $user->lang['PROCESS_LAST'],
		));

		if ($final_jump == 1)
		{
			$db->sql_return_on_error(true);

			update_topics_posted();

			$this->template->assign_block_vars('checks', array(
				'TITLE'		=> $user->lang['UPDATE_TOPICS_POSTED'],
				'RESULT'	=> $user->lang['DONE'],
			));

			if ($db->get_sql_error_triggered())
			{
				$this->template->assign_vars(array(
					'S_ERROR_BOX'	=> true,
					'ERROR_TITLE'	=> $user->lang['UPDATE_TOPICS_POSTED'],
					'ERROR_MSG'		=> $user->lang['UPDATE_TOPICS_POSTED_ERR'],
				));
			}
			$db->sql_return_on_error(false);

			$this->finish_conversion();
			return;
		}
	}

	/**
	 * This function marks the steps before syncing (jump=1)
	 */
	function jump($converter, $jump, $last_statement)
	{
		/** @var \phpbb\db\driver\driver_interface $src_db */
		/** @var \phpbb\cache\driver\driver_interface $cache */
		global $user, $src_db, $same_db, $db, $phpbb_root_path, $phpEx, $config, $cache;
		global $convert;

		include_once ($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		$this->template->assign_block_vars('checks', array(
			'S_LEGEND'	=> true,
			'LEGEND'	=> $user->lang['PROCESS_LAST'],
		));

		if ($jump == 1)
		{
			// Execute 'last' statements/queries
			if (!empty($convert->convertor['execute_last']))
			{
				if (!is_array($convert->convertor['execute_last']))
				{
					// @codingStandardsIgnoreStart
					eval($convert->convertor['execute_last']);
					// @codingStandardsIgnoreEnd
				}
				else
				{
					while ($last_statement < sizeof($convert->convertor['execute_last']))
					{
						// @codingStandardsIgnoreStart
						eval($convert->convertor['execute_last'][$last_statement]);
						// @codingStandardsIgnoreEnd

						$this->template->assign_block_vars('checks', array(
							'TITLE'		=> $convert->convertor['execute_last'][$last_statement],
							'RESULT'	=> $user->lang['DONE'],
						));

						$last_statement++;
						$url = $this->save_convert_progress($converter, 'jump=1&amp;last=' . $last_statement);

						$percentage = ($last_statement == 0) ? 0 : floor(100 / (sizeof($convert->convertor['execute_last']) / $last_statement));
						$msg = sprintf($user->lang['STEP_PERCENT_COMPLETED'], $last_statement, sizeof($convert->convertor['execute_last']), $percentage);

						$this->template->assign_vars(array(
							'L_SUBMIT'		=> $user->lang['CONTINUE_LAST'],
							'BODY'			=> $msg,
							'U_ACTION'		=> $url,
						));

						$this->meta_refresh($url);
						return;
					}
				}
			}

			if (!empty($convert->convertor['query_last']))
			{
				if (!is_array($convert->convertor['query_last']))
				{
					$convert->convertor['query_last'] = array('target', array($convert->convertor['query_last']));
				}
				else if (!is_array($convert->convertor['query_last'][0]))
				{
					$convert->convertor['query_last'] = array(array($convert->convertor['query_last'][0], $convert->convertor['query_last'][1]));
				}

				foreach ($convert->convertor['query_last'] as $query_last)
				{
					if ($query_last[0] == 'src')
					{
						if ($convert->mysql_convert && $same_db)
						{
							$src_db->sql_query("SET NAMES 'binary'");
						}

						$src_db->sql_query($query_last[1]);

						if ($convert->mysql_convert && $same_db)
						{
							$src_db->sql_query("SET NAMES 'utf8'");
						}
					}
					else
					{
						$db->sql_query($query_last[1]);
					}
				}
			}

			// Sanity check
			$db->sql_return_on_error(false);
			$src_db->sql_return_on_error(false);

			fix_empty_primary_groups();

			$sql = 'SELECT MIN(user_regdate) AS board_startdate
				FROM ' . USERS_TABLE;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!isset($config['board_startdate']) || ($row['board_startdate'] < $config['board_startdate'] && $row['board_startdate'] > 0))
			{
				$config->set('board_startdate', $row['board_startdate']);
				$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_regdate = ' . $row['board_startdate'] . ' WHERE user_id = ' . ANONYMOUS);
			}

			update_dynamic_config();

			$this->template->assign_block_vars('checks', array(
				'TITLE'		=> $user->lang['CLEAN_VERIFY'],
				'RESULT'	=> $user->lang['DONE'],
			));

			$url = $this->save_convert_progress($converter, 'jump=2');

			$this->template->assign_vars(array(
				'L_SUBMIT'		=> $user->lang['CONTINUE_CONVERT'],
				'U_ACTION'		=> $url,
			));

			$this->meta_refresh($url);
			return;
		}

		if ($jump == 2)
		{
			$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_permissions = ''");

			// TODO: sync() is likely going to bomb out on forums with a considerable amount of topics.
			// TODO: the sync function is able to handle FROM-TO values, we should use them here (batch processing)
			sync('forum', '', '', false, true);
			$cache->destroy('sql', FORUMS_TABLE);

			$this->template->assign_block_vars('checks', array(
				'TITLE'		=> $user->lang['SYNC_FORUMS'],
				'RESULT'	=> $user->lang['DONE'],
			));

			// Continue with synchronizing the forums...
			$url = $this->save_convert_progress($converter, 'sync_batch=0');

			$this->template->assign_vars(array(
				'L_SUBMIT'		=> $user->lang['CONTINUE_CONVERT'],
				'U_ACTION'		=> $url,
			));

			$this->meta_refresh($url);
			return;
		}
	}

	function build_insert_query(&$schema, &$sql_data, $current_table)
	{
		global $db, $user;
		global $convert;

		// Can we use IGNORE with this DBMS?
		$sql_ignore = (strpos($db->get_sql_layer(), 'mysql') === 0 && !defined('DEBUG')) ? 'IGNORE ' : '';
		$insert_query = 'INSERT ' . $sql_ignore . 'INTO ' . $schema['target'] . ' (';

		$aliases = array();

		$sql_data = array(
			'source_fields'		=> array(),
			'target_fields'		=> array(),
			'source_tables'		=> array(),
			'select_fields'		=> array(),
		);

		foreach ($schema as $key => $val)
		{
			// Example: array('group_name',				'extension_groups.group_name',		'htmlspecialchars'),
			if (is_int($key))
			{
				if (!empty($val[0]))
				{
					// Target fields
					$sql_data['target_fields'][$val[0]] = $key;
					$insert_query .= $val[0] . ', ';
				}

				if (!is_array($val[1]))
				{
					$val[1] = array($val[1]);
				}

				foreach ($val[1] as $valkey => $value_1)
				{
					// This should cover about any case:
					//
					// table.field					=> SELECT table.field				FROM table
					// table.field AS alias			=> SELECT table.field	AS alias	FROM table
					// table.field AS table2.alias	=> SELECT table2.field	AS alias	FROM table table2
					// table.field AS table2.field	=> SELECT table2.field				FROM table table2
					//
					if (preg_match('/^([a-z0-9_]+)\.([a-z0-9_]+)( +AS +(([a-z0-9_]+?)\.)?([a-z0-9_]+))?$/i', $value_1, $m))
					{
						// There is 'AS ...' in the field names
						if (!empty($m[3]))
						{
							$value_1 = ($m[2] == $m[6]) ? $m[1] . '.' . $m[2] : $m[1] . '.' . $m[2] . ' AS ' . $m[6];

							// Table alias: store it then replace the source table with it
							if (!empty($m[5]) && $m[5] != $m[1])
							{
								$aliases[$m[5]] = $m[1];
								$value_1 = str_replace($m[1] . '.' . $m[2], $m[5] . '.' . $m[2], $value_1);
							}
						}
						else
						{
							// No table alias
							$sql_data['source_tables'][$m[1]] = (empty($convert->src_table_prefix)) ? $m[1] : $convert->src_table_prefix . $m[1] . ' ' . $m[1];
						}

						$sql_data['select_fields'][$value_1] = $value_1;
						$sql_data['source_fields'][$key][$valkey] = (!empty($m[6])) ? $m[6] : $m[2];
					}
				}
			}
			else if ($key == 'where' || $key == 'group_by' || $key == 'order_by' || $key == 'having')
			{
				if (@preg_match_all('/([a-z0-9_]+)\.([a-z0-9_]+)/i', $val, $m))
				{
					foreach ($m[1] as $value)
					{
						$sql_data['source_tables'][$value] = (empty($convert->src_table_prefix)) ? $value : $convert->src_table_prefix . $value . ' ' . $value;
					}
				}
			}
		}

		// Add the aliases to the list of tables
		foreach ($aliases as $alias => $table)
		{
			$sql_data['source_tables'][$alias] = $convert->src_table_prefix . $table . ' ' . $alias;
		}

		// 'left_join'		=> 'forums LEFT JOIN forum_prune ON forums.forum_id = forum_prune.forum_id',
		if (!empty($schema['left_join']))
		{
			if (!is_array($schema['left_join']))
			{
				$schema['left_join'] = array($schema['left_join']);
			}

			foreach ($schema['left_join'] as $left_join)
			{
				// This won't handle concatened LEFT JOINs
				if (!preg_match('/([a-z0-9_]+) LEFT JOIN ([a-z0-9_]+) A?S? ?([a-z0-9_]*?) ?(ON|USING)(.*)/i', $left_join, $m))
				{
					$this->error(sprintf($user->lang['NOT_UNDERSTAND'], 'LEFT JOIN', $left_join, $current_table, $schema['target']), __LINE__, __FILE__);
				}

				if (!empty($aliases[$m[2]]))
				{
					if (!empty($m[3]))
					{
						$this->error(sprintf($user->lang['NAMING_CONFLICT'], $m[2], $m[3], $schema['left_join']), __LINE__, __FILE__);
					}

					$m[2] = $aliases[$m[2]];
					$m[3] = $m[2];
				}

				$right_table = $convert->src_table_prefix . $m[2];
				if (!empty($m[3]))
				{
					unset($sql_data['source_tables'][$m[3]]);
				}
				else if ($m[2] != $m[1])
				{
					unset($sql_data['source_tables'][$m[2]]);
				}

				if (strpos($sql_data['source_tables'][$m[1]], "\nLEFT JOIN") !== false)
				{
					$sql_data['source_tables'][$m[1]] = '(' . $sql_data['source_tables'][$m[1]] . ")\nLEFT JOIN $right_table";
				}
				else
				{
					$sql_data['source_tables'][$m[1]] .= "\nLEFT JOIN $right_table";
				}

				if (!empty($m[3]))
				{
					unset($sql_data['source_tables'][$m[3]]);
					$sql_data['source_tables'][$m[1]] .= ' AS ' . $m[3];
				}
				else if (!empty($convert->src_table_prefix))
				{
					$sql_data['source_tables'][$m[1]] .= ' AS ' . $m[2];
				}
				$sql_data['source_tables'][$m[1]] .= ' ' . $m[4] . $m[5];
			}
		}

		// Remove ", " from the end of the insert query
		$insert_query = substr($insert_query, 0, -2) . ') VALUES ';

		return $insert_query;
	}

	/**
	 * Function for processing the currently handled row
	 */
	function process_row(&$schema, &$sql_data, &$insert_values)
	{
		global $user, $phpbb_root_path, $phpEx, $db, $lang, $config, $cache;
		global $convert, $convert_row;

		$sql_flag = false;

		foreach ($schema as $key => $fields)
		{
			// We are only interested in the lines with:
			// array('comment', 'attachments_desc.comment', 'htmlspecialchars'),
			if (is_int($key))
			{
				if (!is_array($fields[1]))
				{
					$fields[1] = array($fields[1]);
				}

				$firstkey_set = false;
				$firstkey = 0;

				foreach ($fields[1] as $inner_key => $inner_value)
				{
					if (!$firstkey_set)
					{
						$firstkey = $inner_key;
						$firstkey_set = true;
					}

					$src_field = isset($sql_data['source_fields'][$key][$inner_key]) ? $sql_data['source_fields'][$key][$inner_key] : '';

					if (!empty($src_field))
					{
						$fields[1][$inner_key] = $convert->row[$src_field];
					}
				}

				if (!empty($fields[0]))
				{
					// We have a target field, if we haven't set $sql_flag yet it will be set to TRUE.
					// If a function has already set it to FALSE it won't change it.
					if ($sql_flag === false)
					{
						$sql_flag = true;
					}

					// No function assigned?
					if (empty($fields[2]))
					{
						$value = $fields[1][$firstkey];
					}
					else if (is_array($fields[2]) && !is_callable($fields[2]))
					{
						// Execute complex function/eval/typecast
						$value = $fields[1];

						foreach ($fields[2] as $type => $execution)
						{
							if (strpos($type, 'typecast') === 0)
							{
								if (!is_array($value))
								{
									$value = array($value);
								}
								$value = $value[0];
								settype($value, $execution);
							}
							else if (strpos($type, 'function') === 0)
							{
								if (!is_array($value))
								{
									$value = array($value);
								}

								$value = call_user_func_array($execution, $value);
							}
							else if (strpos($type, 'execute') === 0)
							{
								if (!is_array($value))
								{
									$value = array($value);
								}

								$execution = str_replace('{RESULT}', '$value', $execution);
								$execution = str_replace('{VALUE}', '$value', $execution);
								// @codingStandardsIgnoreStart
								eval($execution);
								// @codingStandardsIgnoreEnd
							}
						}
					}
					else
					{
						$value = call_user_func_array($fields[2], $fields[1]);
					}

					if (is_null($value))
					{
						$value = '';
					}

					$insert_values[] = $db->_sql_validate_value($value);
				}
				else if (!empty($fields[2]))
				{
					if (is_array($fields[2]))
					{
						// Execute complex function/eval/typecast
						$value = '';

						foreach ($fields[2] as $type => $execution)
						{
							if (strpos($type, 'typecast') === 0)
							{
								$value = settype($value, $execution);
							}
							else if (strpos($type, 'function') === 0)
							{
								if (!is_array($value))
								{
									$value = array($value);
								}

								$value = call_user_func_array($execution, $value);
							}
							else if (strpos($type, 'execute') === 0)
							{
								if (!is_array($value))
								{
									$value = array($value);
								}

								$execution = str_replace('{RESULT}', '$value', $execution);
								$execution = str_replace('{VALUE}', '$value', $execution);
								// @codingStandardsIgnoreStart
								eval($execution);
								// @codingStandardsIgnoreEnd
							}
						}
					}
					else
					{
						call_user_func_array($fields[2], $fields[1]);
					}
				}
			}
		}

		return $sql_flag;
	}

	/**
	 * Own meta refresh function to be able to change the global time used
	 */
	function meta_refresh($url)
	{
		global $convert;

		if ($convert->options['refresh'])
		{
			// Because we should not rely on correct settings, we simply use the relative path here directly.
			$this->template->assign_vars(array(
					'S_REFRESH'	=> true,
					'META'		=> '<meta http-equiv="refresh" content="5; url=' . $url . '" />')
			);
		}
	}

	/**
	 * Error handler function
	 *
	 * This function needs to be kept for BC
	 *
	 * @param $error
	 * @param $line
	 * @param $file
	 * @param bool|false $skip
	 */
	public function error($error, $line, $file, $skip = false)
	{
		$this->template->assign_block_vars('errors', array(
			'TITLE'	=> $error,
			'DESCRIPTION' => 'In ' . $file . ' on line ' . $line,
		));
	}

	/**
	 * Database error handler function
	 *
	 * This function needs to be kept for BC
	 *
	 * @param $error
	 * @param $sql
	 * @param $line
	 * @param $file
	 * @param bool|false $skip
	 */
	public function db_error($error, $sql, $line, $file, $skip = false)
	{
		$this->template->assign_block_vars('errors', array(
			'TITLE'	=> $error,
			'DESCRIPTION' => 'In ' . $file . ' on line ' . $line . '<br /><br /><strong>SQL:</strong> ' . $sql,
		));
	}
}
