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
* @todo [smilies] check regular expressions for special char replacements (stored specialchared in db)
*/
class acp_icons
{
	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/posting');

		// Set up general vars
		$action = request_var('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$action = (isset($_POST['edit'])) ? 'edit' : $action;
		$icon_id = request_var('id', 0);

		$this->tpl_name = 'acp_icons';

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		// What are we working on?
		switch ($mode)
		{
			case 'smilies':
				$table = SMILIES_TABLE;
				$lang = 'SMILIES';
				$fields = 'smiley';
				$img_path = $config['smilies_path'];
			break;

			case 'icons':
				$table = ICONS_TABLE;
				$lang = 'ICONS';
				$fields = 'icons';
				$img_path = $config['icons_path'];
			break;
		}

		$this->page_title = 'ACP_' . $lang;

		// Clear some arrays
		$_images = $_paks = array();
		$notice = '';

		// Grab file list of paks and images
		if ($action == 'edit' || $action == 'add' || $action == 'import')
		{
			$imglist = filelist($phpbb_root_path . $img_path, '');
			
			foreach ($imglist as $path => $img_ary)
			{
				foreach ($img_ary as $img)
				{
					$img_size = @getimagesize($phpbb_root_path . $img_path . '/' . $path . $img);

					if (!$img_size[0] || !$img_size[1])
					{
						continue;
					}

					$_images[$path . $img]['file'] = $path . $img;
					$_images[$path . $img]['width'] = $img_size[0];
					$_images[$path . $img]['height'] = $img_size[1];
				}
			}
			unset($imglist);

			$dir = @opendir($phpbb_root_path . $img_path);
			while ($file = @readdir($dir))
			{
				if (is_file($phpbb_root_path . $img_path . '/' . $file) && preg_match('#\.pak$#i', $file))
				{
					$_paks[] = $file;
				}
			}
			@closedir($dir);
		}

		// What shall we do today? Oops, I believe that's trademarked ...
		switch ($action)
		{
			case 'edit':
				unset($_images);
				$_images = array();

			case 'add':

				$order_list = '';

				$sql = "SELECT * 
					FROM $table 
					ORDER BY {$fields}_order " . (($icon_id || $action == 'add') ? 'DESC' : 'ASC');
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						if ($action == 'add')
						{
							unset($_images[$row[$fields . '_url']]);
						}

						if ($row[$fields . '_id'] == $icon_id)
						{
							$after = true;
							$data[$row[$fields . '_url']] = $row;
						}
						else
						{
							if ($action == 'edit' && !$icon_id)
							{
								$data[$row[$fields . '_url']] = $row;
							}
							
							$selected = '';
							if (!empty($after))
							{
								$selected = ' selected="selected"';
								$after = false;
							}

							$after_txt = ($mode == 'smilies') ? $row['code'] : $row['icons_url'];
							$order_list = '<option value="' . ($row[$fields . '_order']) . '"' . $selected . '>' . sprintf($user->lang['AFTER_' . $lang], ' -&gt; ' . htmlspecialchars($after_txt)) . '</option>' . $order_list;
						}
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

				$order_list = '<option value="1"' . ((!isset($after)) ? ' selected="selected"' : '') . '>' . $user->lang['FIRST'] . '</option>' . $order_list;

				if ($action == 'add')
				{
					$data = $_images;
				}

				$colspan = (($mode == 'smilies') ? '7' : '5');
				$colspan += ($icon_id) ? 1 : 0;
				$colspan += ($action == 'add') ? 2 : 0;

				$template->assign_vars(array(
					'S_EDIT'		=> true,
					'S_SMILIES'		=> ($mode == 'smilies') ? true : false,
					'S_ADD'			=> ($action == 'add') ? true : false,
					'S_ORDER_LIST'	=> $order_list,

					'L_TITLE'		=> $user->lang['ACP_' . $lang],
					'L_EXPLAIN'		=> $user->lang['ACP_' . $lang . '_EXPLAIN'],
					'L_CONFIG'		=> $user->lang[$lang . '_CONFIG'],
					'L_URL'			=> $user->lang[$lang . '_URL'],
					'L_LOCATION'	=> $user->lang[$lang . '_LOCATION'],
					'L_WIDTH'		=> $user->lang[$lang . '_WIDTH'],
					'L_HEIGHT'		=> $user->lang[$lang . '_HEIGHT'],
					'L_ORDER'		=> $user->lang[$lang . '_ORDER'],

					'COLSPAN'		=> $colspan,
					'ID'			=> $icon_id,

					'U_BACK'		=> $u_action,
					'U_ACTION'		=> $u_action . '&amp;action=' . (($action == 'add') ? 'create' : 'modify'),
					)
				);

				foreach ($data as $img => $img_row)
				{
					$template->assign_block_vars('items', array(
						'IMG'		=> $img,
						'IMG_SRC'	=> $phpbb_root_path . $img_path . '/' . $img,

						'CODE'		=> ($mode == 'smilies' && isset($img_row['code'])) ? $img_row['code'] : '',
						'EMOTION'	=> ($mode == 'smilies' && isset($img_row['emotion'])) ? $img_row['emotion'] : '',

						'S_ID'				=> (isset($img_row[$fields . '_id'])) ? true : false,
						'ID'				=> (isset($img_row[$fields . '_id'])) ? $img_row[$fields . '_id'] : 0,
						'WIDTH'				=> (!empty($img_row[$fields .'_width'])) ? $img_row[$fields .'_width'] : $img_row['width'],
						'HEIGHT'			=> (!empty($img_row[$fields .'_height'])) ? $img_row[$fields .'_height'] : $img_row['height'],
						'POSTING_CHECKED'	=> (!empty($img_row['display_on_posting']) || $action == 'add') ? ' checked="checked"' : '')
					);
				}

				return;
	
			break;

			case 'create':
			case 'modify':

				// Get items to create/modify
				$images = (isset($_POST['image'])) ? array_keys(request_var('image', array('' => 0))) : array();
				
				// Now really get the items
				$image_id		= (isset($_POST['id'])) ? array_map('intval', $_POST['id']) : array();
				$image_order	= (isset($_POST['order'])) ? array_map('intval', $_POST['order']) : array();
				$image_width	= (isset($_POST['width'])) ? array_map('intval', $_POST['width']) : array();
				$image_height	= (isset($_POST['height'])) ? array_map('intval', $_POST['height']) : array();
				$image_add		= (isset($_POST['add_img'])) ? array_map('intval', $_POST['add_img']) : array();
				$image_emotion	= request_var('emotion', '');
				$image_code		= request_var('code', '');
				$image_display_on_posting = (isset($_POST['display_on_posting'])) ? array_map('intval', $_POST['display_on_posting']) : array();

				foreach ($images as $image)
				{
					if (($mode == 'smilies' && ($image_emotion[$image] == '' || $image_code[$image] == '')) ||
						($action == 'create' && !isset($image_add[$image])))
					{
					}
					else
					{
						if ($image_width[$image] == 0 || $image_height[$image] == 0)
						{
							$img_size = @getimagesize($phpbb_root_path . $img_path . '/' . $image);
							$image_width[$image] = $img_size[0];
							$image_height[$image] = $img_size[1];
						}

						$img_sql = array(
							$fields . '_url'	=>	$image,
							$fields . '_width'	=>	$image_width[$image],
							$fields . '_height'	=>	$image_height[$image],
							'display_on_posting'=>	(isset($image_display_on_posting[$image])) ? 1 : 0,
						);

						if ($mode == 'smilies')
						{
							$img_sql = array_merge($img_sql, array(
								'emotion'	=>	$image_emotion[$image],
								'code'		=>	$image_code[$image])
							);
						}
						
						if (!empty($image_order[$image]))
						{
							$img_sql = array_merge($img_sql, array(
								$fields . '_order'	=>	$image_order[$image] . '.5')
							);
						}

						if ($action == 'modify')
						{
							$sql = "UPDATE $table
								SET " . $db->sql_build_array('UPDATE', $img_sql) . " 
								WHERE {$fields}_id = " . $image_id[$image];
							$db->sql_query($sql);
						}
						else
						{
							$sql = "INSERT INTO $table " . $db->sql_build_array('INSERT', $img_sql);
							$db->sql_query($sql);
						}

						$update = false;

						if ($action == 'modify' && !empty($image_order[$image]))
						{
							$update = true;

							$result = $db->sql_query("SELECT {$fields}_order 
								FROM $table
								WHERE {$fields}_id = " . $image_id[$image]);
							$order_old = $db->sql_fetchfield($fields . '_order', 0, $result);
							$db->sql_freeresult($result);

							if ($order_old == $image_order[$image])
							{
								$update = false;
							}

							if ($order_old > $image_order[$image])
							{
								$sign = '+';
								$where = $fields . '_order >= ' . $image_order[$image] . " AND {$fields}_order < $order_old";
							}
							else if ($order_old < $image_order[$image])
							{
								$sign = '-';
								$where = "{$fields}_order > $order_old AND {$fields}_order < " . $image_order[$image];
								$sql[$fields . '_order'] = $image_order[$image] - 1;
							}
						}

						if ($update)
						{
							$sql = "UPDATE $table
								SET {$fields}_order = {$fields}_order $sign 1
								WHERE $where";
							$db->sql_query($sql);
						}
					}
				}
				
				$cache->destroy('icons');

				if ($action == 'modify')
				{
					trigger_error($user->lang[$lang . '_EDITED'] . adm_back_link($u_action));
				}
				else
				{
					trigger_error($user->lang[$lang . '_ADDED'] . adm_back_link($u_action));
				}

			break;

			case 'import':

				$pak = request_var('pak', '');
				$current = request_var('current', '');

				if ($pak != '')
				{
					$order = 0;

					// The user has already selected a smilies_pak file
					if ($current == 'delete')
					{
						$db->sql_query("TRUNCATE $table");

						switch ($mode)
						{
							case 'smilies':
							break;

							case 'icons':
								// Reset all icon_ids
								$db->sql_query('UPDATE ' . TOPICS_TABLE . ' SET icon_id = 0');
								$db->sql_query('UPDATE ' . POSTS_TABLE . ' SET icon_id = 0');
							break;
						}
					}
					else 
					{
						$cur_img = array();

						$field_sql = ($mode == 'smilies') ? 'code' : 'icons_url';
						$result = $db->sql_query("SELECT $field_sql FROM $table");

						while ($row = $db->sql_fetchrow($result))
						{
							++$order;
							$cur_img[$row[$field_sql]] = 1;
						}
						$db->sql_freeresult($result);
					}

					if (!($pak_ary = @file($phpbb_root_path . $img_path . '/' . $pak)))
					{
						trigger_error($user->lang['PAK_FILE_NOT_READABLE'] . adm_back_link($u_action));
					}

					foreach ($pak_ary as $pak_entry)
					{
						$data = array();
						if (preg_match_all("#'(.*?)', #", $pak_entry, $data))
						{
							if ((sizeof($data[1]) != 3 && $mode == 'icons') || 
								(sizeof($data[1]) != 5 && $mode == 'smilies'))
							{
								trigger_error($user->lang['WRONG_PAK_TYPE'] . adm_back_link($u_action));
							}

							$img = stripslashes($data[1][0]);
							$width = stripslashes($data[1][1]);
							$height = stripslashes($data[1][2]);

							if (isset($data[1][3]) && isset($data[1][4]))
							{
								$emotion = stripslashes($data[1][3]);
								$code = stripslashes($data[1][4]);
							}

							if ($current == 'replace' && 
								(($mode == 'smilies' && !empty($cur_img[$code])) || 
								($mode == 'icons' && !empty($cur_img[$img]))))
							{
								$replace_sql = ($mode == 'smilies') ? $code : $img;
								$sql = array(
									$fields . '_url'	=>	$img,
									$fields . '_height'	=>	(int) $height,
									$fields . '_width'	=>	(int) $width,
								);

								if ($mode == 'smilies')
								{
									$sql = array_merge($sql, array(
										'emotion'	=>	$emotion
									));
								}

								$db->sql_query("UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql) . " 
									WHERE $field_sql = '" . $db->sql_escape($replace_sql) . "'");
							}
							else
							{
								++$order;

								$sql = array(
									$fields . '_url'	=>	$img,
									$fields . '_height'	=>	(int) $height,
									$fields . '_width'	=>	(int) $width,
									$fields . '_order'	=>	(int) $order,
								);

								if ($mode == 'smilies')
								{
									$sql = array_merge($sql, array(
										'code'		=>	$code,
										'emotion'	=>	$emotion
									));
								}
								$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql));
							}
						}
					}

					$cache->destroy('icons');
					trigger_error($user->lang[$lang . '_IMPORT_SUCCESS'] . adm_back_link($u_action));
				}
				else
				{
					$pak_options = '';

					foreach ($_paks as $pak)
					{
						$pak_options .= '<option value="' . $pak . '">' . htmlspecialchars($pak) . '</option>';
					}

					$template->assign_vars(array(
						'S_CHOOSE_PAK'		=> true,
						'S_PAK_OPTIONS'		=> $pak_options,

						'L_TITLE'			=> $user->lang['ACP_' . $lang],
						'L_EXPLAIN'			=> $user->lang['ACP_' . $lang . '_EXPLAIN'],
						'L_IMPORT'			=> $user->lang[$lang . '_IMPORT'],
						'L_NO_PAK_OPTIONS'	=> $user->lang['NO_' . $lang . '_PAK'],
						'L_CURRENT'			=> $user->lang['CURRENT_' . $lang],
						'L_CURRENT_EXPLAIN'	=> $user->lang['CURRENT_' . $lang . '_EXPLAIN'],
						'L_IMPORT_SUBMIT'	=> $user->lang['IMPORT_' . $lang],

						'U_BACK'		=> $u_action,
						'U_ACTION'		=> $u_action . '&amp;action=import',
						)
					);
				}
			break;

			case 'export':

				$this->page_title = 'EXPORT_' . $lang;
				$this->tpl_name = 'message_body';

				$template->assign_vars(array(
					'MESSAGE_TITLE'		=> $user->lang['EXPORT_' . $lang],
					'MESSAGE_TEXT'		=> sprintf($user->lang['EXPORT_' . $lang . '_EXPLAIN'], '<a href="' . $u_action . '&amp;action=send">', '</a>'))
				);

				return;
				
			break;

			case 'send':

				$sql = "SELECT * 
					FROM $table
					ORDER BY {$fields}_order";
				$result = $db->sql_query($sql);

				$pak = '';
				while ($row = $db->sql_fetchrow($result))
				{
					$pak .= "'" . addslashes($row[$fields . '_url']) . "', ";
					$pak .= "'" . addslashes($row[$fields . '_height']) . "', ";
					$pak .= "'" . addslashes($row[$fields . '_width']) . "', ";
					if ($mode == 'smilies')
					{
						$pak .= "'" . addslashes($row['emotion']) . "', ";
						$pak .= "'" . addslashes($row['code']) . "', ";
					}
					$pak .= "\n";
				}
				$db->sql_freeresult($result);

				if ($pak != '')
				{
					$db->sql_close();

					header('Pragma: public');

					// Send out the Headers
					header('Content-Type: text/x-delimtext; name="' . $fields . '.pak"');
					header('Content-Disposition: inline; filename="' . $fields . '.pak"');
					echo $pak;

					flush();
					exit;
				}
				else
				{
					trigger_error($user->lang['NO_' . $fields . '_EXPORT'] . adm_back_link($u_action));
				}

			break;

			case 'delete':

				$db->sql_query("DELETE FROM $table 
					WHERE {$fields}_id = $icon_id");

				switch ($mode)
				{
					case 'smilies':
					break;

					case 'icons':
						// Reset appropriate icon_ids
						$db->sql_query('UPDATE ' . TOPICS_TABLE . " 
							SET icon_id = 0 
							WHERE icon_id = $icon_id");

						$db->sql_query('UPDATE ' . POSTS_TABLE . " 
							SET icon_id = 0 
							WHERE icon_id = $icon_id");

					break;
				}

				$notice = $user->lang[$lang . '_DELETED'];

			break;

			case 'move_up':
			case 'move_down':

				$image_order = intval($_GET['order']);
				$order_total = $image_order * 2 + (($action == 'move_up') ? -1 : 1);

				$sql = 'UPDATE ' . $table . '
					SET ' . $fields . "_order = $order_total - " . $fields . '_order
					WHERE ' . $fields . "_order IN ($image_order, " . (($action == 'move_up') ? $image_order - 1 : $image_order + 1) . ')';
				$db->sql_query($sql);

				$cache->destroy('icons');

			break;
		}

		// By default, check that image_order is valid and fix it if necessary
		$sql = "SELECT {$fields}_id AS order_id, {$fields}_order AS fields_order
			FROM $table
			ORDER BY {$fields}_order";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				++$order;
				if ($row['fields_order'] != $order)
				{
					$db->sql_query("UPDATE $table
						SET {$fields}_order = $order
						WHERE {$fields}_id = " . $row['order_id']);
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['ACP_' . $lang],
			'L_EXPLAIN'			=> $user->lang['ACP_' . $lang . '_EXPLAIN'],
			'L_IMPORT'			=> $user->lang['IMPORT_' . $lang],
			'L_EXPORT'			=> $user->lang['EXPORT_' . $lang],
			'L_NOT_DISPLAYED'	=> $user->lang[$lang . '_NOT_DISPLAYED'],
			'L_ICON_ADD'		=> $user->lang['ADD_' . $lang],
			'L_ICON_EDIT'		=> $user->lang['EDIT_' . $lang],

			'NOTICE'			=> $notice,
			'COLSPAN'			=> ($mode == 'smilies') ? 5 : 3,

			'S_SMILIES'			=> ($mode == 'smilies') ? true : false,

			'U_ACTION'			=> $u_action,
			'U_IMPORT'			=> $u_action . '&amp;action=import',
			'U_EXPORT'			=> $u_action . '&amp;action=export',
			)
		);

		$spacer = false;

		$sql = "SELECT * 
			FROM $table
			ORDER BY display_on_posting DESC, {$fields}_order ASC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$alt_text = ($mode == 'smilies') ? $row['code'] : '';

			$template->assign_block_vars('items', array(
				'S_SPACER'		=> (!$spacer && !$row['display_on_posting']) ? true : false,
				'ALT_TEXT'		=> $alt_text,
				'IMG_SRC'		=> $phpbb_root_path . $img_path . '/' . $row[$fields . '_url'],
				'WIDTH'			=> $row[$fields . '_width'],
				'HEIGHT'		=> $row[$fields . '_height'],
				'CODE'			=> (isset($row['code'])) ? $row['code'] : '',
				'EMOTION'		=> (isset($row['emotion'])) ? $row['emotion'] : '',
				'U_EDIT'		=> $u_action . '&amp;action=edit&amp;id=' . $row[$fields . '_id'],
				'U_DELETE'		=> $u_action . '&amp;action=delete&amp;id=' . $row[$fields . '_id'],
				'U_MOVE_UP'		=> $u_action . '&amp;action=move_up&amp;order=' . $row[$fields . '_order'],
				'U_MOVE_DOWN'	=> $u_action . '&amp;action=move_down&amp;order=' . $row[$fields . '_order'])
			);

			if (!$spacer && !$row['display_on_posting'])
			{
				$spacer = true;
			}
		}
		$db->sql_freeresult($result);

	}
}

/**
* @package module_install
*/
class acp_icons_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_icons',
			'title'		=> 'ACP_ICONS_SMILIES',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'icons'		=> array('title' => 'ACP_ICONS', 'auth' => 'acl_a_icons'),
				'smilies'	=> array('title' => 'ACP_SMILIES', 'auth' => 'acl_a_icons'),
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