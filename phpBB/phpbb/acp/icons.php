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

namespace phpbb\acp;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @todo [smilies] check regular expressions for special char replacements (stored specialchared in db)
 */
class icons
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var ContainerInterface */
	protected $container;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var array phpBB tables */
	protected $tables;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\config\config					$config			Config object
	 * @param ContainerInterface					$container		Service container object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\pagination						$pagination		Pagination object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param string								$root_path		phpBB root path
	 * @param array									$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		ContainerInterface $container,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $lang,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$root_path,
		$tables
	)
	{
		$this->cache		= $cache;
		$this->config		= $config;
		$this->container	= $container;
		$this->db			= $db;
		$this->lang			= $lang;
		$this->pagination	= $pagination;
		$this->request		= $request;
		$this->template		= $template;

		$this->root_path	= $root_path;
		$this->tables		= $tables;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('acp/posting');

		// Set up general vars
		$icon_id = $this->request->variable('id', 0);
		$action = $this->request->variable('action', '');
		$action = $this->request->is_set_post('add') ? 'add' : $action;
		$action = $this->request->is_set_post('edit') ? 'edit' : $action;
		$action = $this->request->is_set_post('import') ? 'import' : $action;

		$form_key = 'acp_icons';
		add_form_key($form_key);

		$mode = ($mode === 'smilies') ? 'smilies' : 'icons';

		// What are we working on?
		switch ($mode)
		{
			case 'smilies':
				$lang		= 'SMILIES';
				$table		= $this->tables['smilies'];
				$fields		= 'smiley';
				$img_path	= $this->config['smilies_path'];
			break;

			case 'icons':
				$lang		= 'ICONS';
				$table		= $this->tables['icons'];
				$fields		= 'icons';
				$img_path	= $this->config['icons_path'];
			break;

			default:
				$lang		= '';
				$table		= '';
				$fields		= '';
				$img_path	= '';
			break;
		}

		$this->page_title = 'ACP_' . $lang;
		$this->tpl_name = 'acp_icons';

		// Clear some arrays
		$_images = $_paks = [];
		$notice = '';

		// Grab file list of paks and images
		if ($action === 'edit' || $action === 'add' || $action === 'import')
		{
			$img_list = filelist($this->root_path . $img_path, '');

			foreach ($img_list as $path => $img_ary)
			{
				if (empty($img_ary))
				{
					continue;
				}

				asort($img_ary, SORT_STRING);

				foreach ($img_ary as $img)
				{
					$img_size = getimagesize($this->root_path . $img_path . '/' . $path . $img);

					if (!$img_size[0] || !$img_size[1] || strlen($img) > 255)
					{
						continue;
					}

					// adjust the width and height to be lower than 128px while preserving the aspect ratio (for icons)
					if ($mode === 'icons')
					{
						if ($img_size[0] > 127 && $img_size[0] > $img_size[1])
						{
							$img_size[1] = (int) ($img_size[1] * (127 / $img_size[0]));
							$img_size[0] = 127;
						}
						else if ($img_size[1] > 127)
						{
							$img_size[0] = (int) ($img_size[0] * (127 / $img_size[1]));
							$img_size[1] = 127;
						}
					}

					$_images[$path . $img]['file'] = $path . $img;
					$_images[$path . $img]['width'] = $img_size[0];
					$_images[$path . $img]['height'] = $img_size[1];
				}
			}
			unset($img_list);

			if ($dir = @opendir($this->root_path . $img_path))
			{
				while (($file = readdir($dir)) !== false)
				{
					if (is_file($this->root_path . $img_path . '/' . $file) && preg_match('#\.pak$#i', $file))
					{
						$_paks[] = $file;
					}
				}
				closedir($dir);

				if (!empty($_paks))
				{
					asort($_paks, SORT_STRING);
				}
			}
		}

		// What shall we do today? Oops, I believe that's trademarked ...
		switch ($action)
		{
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'edit':
				unset($_images);
				$_images = [];
			// no break;

			case 'add':
				$smilies = $default_row = [];
				$smiley_options = $order_list = $add_order_list = '';

				if ($action === 'add' && $mode === 'smilies')
				{
					$sql = 'SELECT *
						FROM ' . $this->tables['smilies'] . '
						ORDER BY smiley_order';
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						if (empty($smilies[$row['smiley_url']]))
						{
							$smilies[$row['smiley_url']] = $row;
						}
					}
					$this->db->sql_freeresult($result);

					if (!empty($smilies))
					{
						foreach ($smilies as $row)
						{
							$selected = false;

							if (!$smiley_options)
							{
								$selected = true;
								$default_row = $row;
							}
							$smiley_options .= '<option value="' . $row['smiley_url'] . '"' . (($selected) ? ' selected="selected"' : '') . '>' . $row['smiley_url'] . '</option>';

							$this->template->assign_block_vars('smile', [
								'SMILEY_URL'	=> addslashes($row['smiley_url']),
								'CODE'			=> addslashes($row['code']),
								'EMOTION'		=> addslashes($row['emotion']),
								'WIDTH'			=> $row['smiley_width'],
								'HEIGHT'		=> $row['smiley_height'],
								'ORDER'			=> $row['smiley_order'] + 1,
							]);
						}
					}
				}

				$sql = "SELECT *
					FROM $table
					ORDER BY {$fields}_order " . (($icon_id || $action === 'add') ? 'DESC' : 'ASC');
				$result = $this->db->sql_query($sql);

				$data = [];
				$after = false;
				$order_lists = ['', ''];
				$add_order_lists = ['', ''];
				$display_count = 0;

				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($action === 'add')
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
						if ($action === 'edit' && !$icon_id)
						{
							$data[$row[$fields . '_url']] = $row;
						}

						$selected = '';
						if (!empty($after))
						{
							$selected = ' selected="selected"';
							$after = false;
						}
						if ($row['display_on_posting'])
						{
							$display_count++;
						}
						$after_txt = ($mode === 'smilies') ? $row['code'] : $row['icons_url'];
						$order_lists[$row['display_on_posting']] = '<option value="' . ($row[$fields . '_order'] + 1) . '"' . $selected . '>' . $this->lang->lang('AFTER_' . $lang, ' -&gt; ' . $after_txt) . '</option>' . $order_lists[$row['display_on_posting']];

						if (!empty($default_row))
						{
							$add_order_lists[$row['display_on_posting']] = '<option value="' . ($row[$fields . '_order'] + 1) . '"' . (($row[$fields . '_id'] == $default_row['smiley_id']) ? ' selected="selected"' : '') . '>' . $this->lang->lang('AFTER_' . $lang, ' -&gt; ' . $after_txt) . '</option>' . $add_order_lists[$row['display_on_posting']];
						}
					}
				}
				$this->db->sql_freeresult($result);

				$order_list = '<option value="1"' . ((!isset($after)) ? ' selected="selected"' : '') . '>' . $this->lang->lang('FIRST') . '</option>';
				$add_order_list = '<option value="1">' . $this->lang->lang('FIRST') . '</option>';

				if ($action === 'add')
				{
					$data = $_images;
				}

				$colspan = ($mode === 'smilies') ? 7 : 6;
				$colspan += $icon_id ? 1 : 0;
				$colspan += ($action === 'add') ? 2 : 0;

				$this->template->assign_vars([
					'S_EDIT'		=> true,
					'S_SMILIES'		=> ($mode === 'smilies') ? true : false,
					'S_ADD'			=> ($action === 'add') ? true : false,

					'S_ORDER_LIST_DISPLAY'			=> $order_list . $order_lists[1],
					'S_ORDER_LIST_UNDISPLAY'		=> $order_list . $order_lists[0],
					'S_ORDER_LIST_DISPLAY_COUNT'	=> $display_count + 1,

					'L_TITLE'		=> $this->lang->lang('ACP_' . $lang),
					'L_EXPLAIN'		=> $this->lang->lang('ACP_' . $lang . '_EXPLAIN'),
					'L_CONFIG'		=> $this->lang->lang($lang . '_CONFIG'),
					'L_URL'			=> $this->lang->lang($lang . '_URL'),
					'L_LOCATION'	=> $this->lang->lang($lang . '_LOCATION'),
					'L_WIDTH'		=> $this->lang->lang($lang . '_WIDTH'),
					'L_HEIGHT'		=> $this->lang->lang($lang . '_HEIGHT'),
					'L_ORDER'		=> $this->lang->lang($lang . '_ORDER'),
					'L_NO_ICONS'	=> $this->lang->lang('NO_' . $lang . '_' . strtoupper($action)),

					'COLSPAN'		=> $colspan,
					'ID'			=> $icon_id,

					'U_BACK'		=> $this->u_action,
					'U_ACTION'		=> $this->u_action . '&amp;action=' . (($action === 'add') ? 'create' : 'modify'),
				]);

				foreach ($data as $img => $img_row)
				{
					$this->template->assign_block_vars('items', [
						'IMG'		=> $img,
						'A_IMG'		=> addslashes($img),
						'IMG_SRC'	=> $this->root_path . $img_path . '/' . $img,

						'CODE'		=> ($mode === 'smilies' && isset($img_row['code'])) ? $img_row['code'] : '',
						'EMOTION'	=> ($mode === 'smilies' && isset($img_row['emotion'])) ? $img_row['emotion'] : '',

						'S_ID'				=> (isset($img_row[$fields . '_id'])) ? true : false,
						'ID'				=> (isset($img_row[$fields . '_id'])) ? $img_row[$fields . '_id'] : 0,
						'WIDTH'				=> (!empty($img_row[$fields .'_width'])) ? $img_row[$fields .'_width'] : $img_row['width'],
						'HEIGHT'			=> (!empty($img_row[$fields .'_height'])) ? $img_row[$fields .'_height'] : $img_row['height'],
						'TEXT_ALT'		    => ($mode === 'icons' && !empty($img_row['icons_alt'])) ? $img_row['icons_alt'] : $img,
						'ALT'			    => ($mode === 'icons' && !empty($img_row['icons_alt'])) ? $img_row['icons_alt'] : '',
						'POSTING_CHECKED'	=> (!empty($img_row['display_on_posting']) || $action === 'add') ? ' checked="checked"' : '',
					]);
				}

				// Ok, another row for adding an addition code for a pre-existing image...
				if ($action === 'add' && $mode === 'smilies' && !empty($smilies))
				{
					$this->template->assign_vars([
						'S_ADD_CODE'		=> true,

						'S_IMG_OPTIONS'		=> $smiley_options,

						'S_ADD_ORDER_LIST_DISPLAY'		=> $add_order_list . $add_order_lists[1],
						'S_ADD_ORDER_LIST_UNDISPLAY'	=> $add_order_list . $add_order_lists[0],

						'IMG_SRC'			=> $this->root_path . $img_path . '/' . $default_row['smiley_url'],
						'IMG_PATH'			=> $img_path,

						'CODE'				=> $default_row['code'],
						'EMOTION'			=> $default_row['emotion'],

						'WIDTH'				=> $default_row['smiley_width'],
						'HEIGHT'			=> $default_row['smiley_height'],
					]);
				}

				return;

			break;

			case 'create':
			case 'modify':

				if (!check_form_key($form_key))
				{
					trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Get items to create/modify
				$images = $this->request->is_set_post('image') ? array_keys($this->request->variable('image', ['' => 0])) : [];

				// Now really get the items
				$image_id		= $this->request->is_set_post('id') ? $this->request->variable('id', ['' => 0]) : [];
				$image_order	= $this->request->is_set_post('order') ? $this->request->variable('order', ['' => 0]) : [];
				$image_width	= $this->request->is_set_post('width') ? $this->request->variable('width', ['' => 0]) : [];
				$image_height	= $this->request->is_set_post('height') ? $this->request->variable('height', ['' => 0]) : [];
				$image_add		= $this->request->is_set_post('add_image') ? $this->request->variable('add_img', ['' => 0]) : [];
				$image_emotion	= $this->request->variable('emotion', ['' => ''], true);
				$image_code		= $this->request->variable('code', ['' => ''], true);
				$image_alt		= $this->request->is_set_post('alt') ? $this->request->variable('alt', ['' => ''], true) : [];
				$image_display_on_posting = $this->request->is_set_post('display_on_posting') ? $this->request->variable('display_on_posting', ['' => 0]) : [];

				// Ok, add the relevant bits if we are adding new codes to existing emoticons...
				if ($this->request->variable('add_additional_code', false, false, \phpbb\request\request_interface::POST))
				{
					$add_image			= $this->request->variable('add_image', '');
					$add_code			= $this->request->variable('add_code', '', true);
					$add_emotion		= $this->request->variable('add_emotion', '', true);

					if ($add_image && $add_emotion && $add_code)
					{
						$images[] = $add_image;
						$image_add[$add_image] = true;

						$image_code[$add_image] = $add_code;
						$image_emotion[$add_image] = $add_emotion;
						$image_width[$add_image] = $this->request->variable('add_width', 0);
						$image_height[$add_image] = $this->request->variable('add_height', 0);

						if ($this->request->variable('add_display_on_posting', false, false, \phpbb\request\request_interface::POST))
						{
							$image_display_on_posting[$add_image] = 1;
						}

						$image_order[$add_image] = $this->request->variable('add_order', 0);
					}
				}

				if ($mode === 'smilies' && $action === 'create')
				{
					$smiley_count = (int) $this->db->get_row_count($table);

					$addable_smileys_count = count($images);
					foreach ($images as $image)
					{
						if (!isset($image_add[$image]))
						{
							--$addable_smileys_count;
						}
					}

					if ($smiley_count + $addable_smileys_count > SMILEY_LIMIT)
					{
						trigger_error($this->lang->lang('TOO_MANY_SMILIES', SMILEY_LIMIT) . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}

				$icons_updated = 0;
				$errors = [];
				foreach ($images as $image)
				{
					if ($mode === 'smilies' && ($image_emotion[$image] == '' || $image_code[$image] == ''))
					{
						$errors[$image] = 'SMILIE_NO_' . (($image_emotion[$image] == '') ? 'EMOTION' : 'CODE');
					}
					else if ($action === 'create' && !isset($image_add[$image]))
					{
						// skip images where add wasn't checked
					}
					else if (!file_exists($this->root_path . $img_path . '/' . $image))
					{
						$errors[$image] = 'SMILIE_NO_FILE';
					}
					else
					{
						if ($image_width[$image] == 0 || $image_height[$image] == 0)
						{
							$img_size = getimagesize($this->root_path . $img_path . '/' . $image);
							$image_width[$image] = $img_size[0];
							$image_height[$image] = $img_size[1];
						}

						// Adjust image width/height for icons
						if ($mode === 'icons')
						{
							if ($image_width[$image] > 127 && $image_width[$image] > $image_height[$image])
							{
								$image_height[$image] = (int) ($image_height[$image] * (127 / $image_width[$image]));
								$image_width[$image] = 127;
							}
							else if ($image_height[$image] > 127)
							{
								$image_width[$image] = (int) ($image_width[$image] * (127 / $image_height[$image]));
								$image_height[$image] = 127;
							}
						}

						$img_sql = [
							$fields . '_url'		=> $image,
							$fields . '_width'		=> $image_width[$image],
							$fields . '_height'		=> $image_height[$image],
							'display_on_posting'	=> (isset($image_display_on_posting[$image])) ? 1 : 0,
						];

						if ($mode === 'smilies')
						{
							$img_sql = array_merge($img_sql, [
									'emotion'	=> $image_emotion[$image],
									'code'		=> $image_code[$image]]
							);
						}

						if ($mode === 'icons')
						{
							$img_sql = array_merge($img_sql, [
									'icons_alt'	=> $image_alt[$image]]
							);
						}

						// Image_order holds the 'new' order value
						if (!empty($image_order[$image]))
						{
							$img_sql = array_merge($img_sql, [
									$fields . '_order'	=>	$image_order[$image]]
							);

							// Since we always add 'after' an item, we just need to increase all following + the current by one
							$sql = "UPDATE $table
								SET {$fields}_order = {$fields}_order + 1
								WHERE {$fields}_order >= {$image_order[$image]}";
							$this->db->sql_query($sql);

							// If we adjust the order, we need to adjust all other orders too - they became inaccurate...
							foreach ($image_order as $_image => $_order)
							{
								if ($_image == $image)
								{
									continue;
								}

								if ($_order >= $image_order[$image])
								{
									$image_order[$_image]++;
								}
							}
						}

						if ($action === 'modify'  && !empty($image_id[$image]))
						{
							$sql = "UPDATE $table
								SET " . $this->db->sql_build_array('UPDATE', $img_sql) . "
								WHERE {$fields}_id = " . $image_id[$image];
							$this->db->sql_query($sql);
							$icons_updated++;
						}
						else if ($action !== 'modify')
						{
							$sql = "INSERT INTO $table " . $this->db->sql_build_array('INSERT', $img_sql);
							$this->db->sql_query($sql);
							$icons_updated++;
						}

					}
				}

				$this->cache->destroy('_icons');
				$this->cache->destroy('sql', $table);
				$this->container->get('text_formatter.cache')->invalidate();

				$level = ($icons_updated) ? E_USER_NOTICE : E_USER_WARNING;
				$errormsgs = '';
				foreach ($errors as $img => $error)
				{
					$errormsgs .= '<br />' . $this->lang->lang($error, $img);
				}
				if ($action === 'modify')
				{
					trigger_error($this->lang->lang($lang . '_EDITED', $icons_updated) . $errormsgs . adm_back_link($this->u_action), $level);
				}
				else
				{
					trigger_error($this->lang->lang($lang . '_ADDED', $icons_updated) . $errormsgs . adm_back_link($this->u_action), $level);
				}

			break;

			case 'import':

				$pak = $this->request->variable('pak', '');
				$current = $this->request->variable('current', '');

				if ($pak != '')
				{
					$order = 0;

					if (!check_form_key($form_key))
					{
						trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (!($pak_ary = @file($this->root_path . $img_path . '/' . $pak)))
					{
						trigger_error($this->lang->lang('PAK_FILE_NOT_READABLE') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Make sure the pak_ary is valid
					foreach ($pak_ary as $pak_entry)
					{
						if (preg_match_all("#'(.*?)', ?#", $pak_entry, $data))
						{
							if ((count($data[1]) != 4 && $mode === 'icons') ||
								((count($data[1]) != 6 || (empty($data[1][4]) || empty($data[1][5]))) && $mode === 'smilies' ))
							{
								trigger_error($this->lang->lang('WRONG_PAK_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
							}
						}
						else
						{
							trigger_error($this->lang->lang('WRONG_PAK_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}

					// The user has already selected a smilies_pak file
					if ($current === 'delete')
					{
						switch ($this->db->get_sql_layer())
						{
							case 'sqlite3':
								$this->db->sql_query('DELETE FROM ' . $table);
							break;

							default:
								$this->db->sql_query('TRUNCATE TABLE ' . $table);
							break;
						}

						switch ($mode)
						{
							case 'smilies':
							break;

							case 'icons':
								// Reset all icon_ids
								$this->db->sql_query('UPDATE ' . $this->tables['topics'] . ' SET icon_id = 0');
								$this->db->sql_query('UPDATE ' . $this->tables['posts'] . ' SET icon_id = 0');
							break;
						}
					}
					else
					{
						$cur_img = [];

						$field_sql = ($mode === 'smilies') ? 'code' : 'icons_url';

						$sql = "SELECT $field_sql
							FROM $table";
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							++$order;
							$cur_img[$row[$field_sql]] = 1;
						}
						$this->db->sql_freeresult($result);
					}

					if ($mode === 'smilies')
					{
						$smiley_count = (int) $this->db->get_row_count($table);
						if ($smiley_count + count($pak_ary) > SMILEY_LIMIT)
						{
							trigger_error($this->lang->lang('TOO_MANY_SMILIES', SMILEY_LIMIT) . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}

					foreach ($pak_ary as $pak_entry)
					{
						$data = [];
						if (preg_match_all("#'(.*?)', ?#", $pak_entry, $data))
						{
							if ((count($data[1]) !== 4 && $mode === 'icons') ||
								(count($data[1]) !== 6 && $mode === 'smilies'))
							{
								trigger_error($this->lang->lang('WRONG_PAK_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
							}

							$code = '';
							$emotion = '';

							// Strip slashes here because slashes got added before... (on export)
							$img = stripslashes($data[1][0]);
							$width = stripslashes($data[1][1]);
							$height = stripslashes($data[1][2]);
							$display_on_posting = stripslashes($data[1][3]);

							if (isset($data[1][4]) && isset($data[1][5]))
							{
								$emotion = stripslashes($data[1][4]);
								$code = stripslashes($data[1][5]);
							}

							if ($current == 'replace' &&
								(($mode === 'smilies' && !empty($cur_img[$code])) ||
									($mode === 'icons' && !empty($cur_img[$img]))))
							{
								$replace_sql = ($mode === 'smilies') ? $code : $img;
								$sql = [
									$fields . '_url'		=> $img,
									$fields . '_height'		=> (int) $height,
									$fields . '_width'		=> (int) $width,
									'display_on_posting'	=> (int) $display_on_posting,
								];

								if ($mode === 'smilies')
								{
									$sql = array_merge($sql, ['emotion' => $emotion]);
								}

								$sql = "UPDATE $table SET " . $this->db->sql_build_array('UPDATE', $sql) . "
									WHERE $field_sql = '" . $this->db->sql_escape($replace_sql) . "'";
								$this->db->sql_query($sql);
							}
							else
							{
								++$order;

								$sql = [
									$fields . '_url'	=> $img,
									$fields . '_height'	=> (int) $height,
									$fields . '_width'	=> (int) $width,
									$fields . '_order'	=> (int) $order,
									'display_on_posting'=> (int) $display_on_posting,
								];

								if ($mode === 'smilies')
								{
									$sql = array_merge($sql, [
										'code'				=> $code,
										'emotion'			=> $emotion,
									]);
								}
								$this->db->sql_query("INSERT INTO $table " . $this->db->sql_build_array('INSERT', $sql));
							}
						}
					}

					$this->cache->destroy('_icons');
					$this->cache->destroy('sql', $table);
					$this->container->get('text_formatter.cache')->invalidate();

					trigger_error($this->lang->lang($lang . '_IMPORT_SUCCESS') . adm_back_link($this->u_action));
				}
				else
				{
					$pak_options = '';

					foreach ($_paks as $pak)
					{
						$pak_options .= '<option value="' . $pak . '">' . htmlspecialchars($pak) . '</option>';
					}

					$this->template->assign_vars([
						'S_CHOOSE_PAK'		=> true,
						'S_PAK_OPTIONS'		=> $pak_options,

						'L_TITLE'			=> $this->lang->lang('ACP_' . $lang),
						'L_EXPLAIN'			=> $this->lang->lang('ACP_' . $lang . '_EXPLAIN'),
						'L_NO_PAK_OPTIONS'	=> $this->lang->lang('NO_' . $lang . '_PAK'),
						'L_CURRENT'			=> $this->lang->lang('CURRENT_' . $lang),
						'L_CURRENT_EXPLAIN'	=> $this->lang->lang('CURRENT_' . $lang . '_EXPLAIN'),
						'L_IMPORT_SUBMIT'	=> $this->lang->lang('IMPORT_' . $lang),

						'U_BACK'		=> $this->u_action,
						'U_ACTION'		=> $this->u_action . '&amp;action=import',
					]);
				}
			break;

			case 'export':
				$this->page_title = 'EXPORT_' . $lang;
				$this->tpl_name = 'message_body';

				$this->template->assign_vars([
					'MESSAGE_TITLE'		=> $this->lang->lang('EXPORT_' . $lang),
					'MESSAGE_TEXT'		=> $this->lang->lang('EXPORT_' . $lang . '_EXPLAIN', '<a href="' . $this->u_action . '&amp;action=send&amp;hash=' . generate_link_hash('acp_icons') . '">', '</a>'),

					'S_USER_NOTICE'		=> true,
				]);

				return;
			break;

			case 'send':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_icons'))
				{
					trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$pak = '';

				$sql = "SELECT *
					FROM $table
					ORDER BY {$fields}_order";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$pak .= "'" . addslashes($row[$fields . '_url']) . "', ";
					$pak .= "'" . addslashes($row[$fields . '_width']) . "', ";
					$pak .= "'" . addslashes($row[$fields . '_height']) . "', ";
					$pak .= "'" . addslashes($row['display_on_posting']) . "', ";

					if ($mode === 'smilies')
					{
						$pak .= "'" . addslashes($row['emotion']) . "', ";
						$pak .= "'" . addslashes($row['code']) . "', ";
					}

					$pak .= "\n";
				}
				$this->db->sql_freeresult($result);

				if ($pak != '')
				{
					garbage_collection();

					header('Cache-Control: public');

					// Send out the Headers
					header('Content-Type: text/x-delimtext; name="' . $mode . '.pak"');
					header('Content-Disposition: inline; filename="' . $mode . '.pak"');
					echo $pak;

					flush();
					exit;
				}
				else
				{
					trigger_error($this->lang->lang('NO_' . strtoupper($fields) . '_EXPORT') . adm_back_link($this->u_action), E_USER_WARNING);
				}

			break;

			case 'delete':
				if (confirm_box(true))
				{
					$sql = "DELETE FROM $table
						WHERE {$fields}_id = $icon_id";
					$this->db->sql_query($sql);

					switch ($mode)
					{
						case 'smilies':
						break;

						case 'icons':
							// Reset appropriate icon_ids
							$this->db->sql_query('UPDATE ' . $this->tables['topics'] . "
								SET icon_id = 0
								WHERE icon_id = $icon_id");

							$this->db->sql_query('UPDATE ' . $this->tables['posts'] . "
								SET icon_id = 0
								WHERE icon_id = $icon_id");
						break;
					}

					$notice = $this->lang->lang($lang . '_DELETED');

					$this->cache->destroy('_icons');
					$this->cache->destroy('sql', $table);
					$this->container->get('text_formatter.cache')->invalidate();

					if ($this->request->is_ajax())
					{
						$json_response = new \phpbb\json_response;
						$json_response->send([
							'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
							'MESSAGE_TEXT'	=> $notice,
							'REFRESH_DATA'	=> [
								'time'	=> 3
							],
						]);
					}
				}
				else
				{
					confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'id'		=> $icon_id,
						'action'	=> 'delete',
					]));
				}

			break;

			case 'move_up':
			case 'move_down':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_icons'))
				{
					trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Get current order id...
				$sql = "SELECT {$fields}_order as current_order
					FROM $table
					WHERE {$fields}_id = $icon_id";
				$result = $this->db->sql_query($sql);
				$current_order = (int) $this->db->sql_fetchfield('current_order');
				$this->db->sql_freeresult($result);

				if ($current_order == 0 && $action === 'move_up')
				{
					break;
				}

				// on move_down, switch position with next order_id...
				// on move_up, switch position with previous order_id...
				$switch_order_id = ($action === 'move_down') ? $current_order + 1 : $current_order - 1;

				//
				$sql = "UPDATE $table
					SET {$fields}_order = $current_order
					WHERE {$fields}_order = $switch_order_id
						AND {$fields}_id <> $icon_id";
				$this->db->sql_query($sql);
				$move_executed = (bool) $this->db->sql_affectedrows();

				// Only update the other entry too if the previous entry got updated
				if ($move_executed)
				{
					$sql = "UPDATE $table
						SET {$fields}_order = $switch_order_id
						WHERE {$fields}_order = $current_order
							AND {$fields}_id = $icon_id";
					$this->db->sql_query($sql);
				}

				$this->cache->destroy('_icons');
				$this->cache->destroy('sql', $table);
				$this->container->get('text_formatter.cache')->invalidate();

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send(['success'	=> $move_executed]);
				}
			break;
		}

		// By default, check that image_order is valid and fix it if necessary
		$sql = "SELECT {$fields}_id AS order_id, {$fields}_order AS fields_order
			FROM $table
			ORDER BY display_on_posting DESC, {$fields}_order";
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				++$order;
				if ($row['fields_order'] != $order)
				{
					$this->db->sql_query("UPDATE $table
						SET {$fields}_order = $order
						WHERE {$fields}_id = " . $row['order_id']);
				}
			}
			while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'L_TITLE'			=> $this->lang->lang('ACP_' . $lang),
			'L_EXPLAIN'			=> $this->lang->lang('ACP_' . $lang . '_EXPLAIN'),
			'L_IMPORT'			=> $this->lang->lang('IMPORT_' . $lang),
			'L_EXPORT'			=> $this->lang->lang('EXPORT_' . $lang),
			'L_NOT_DISPLAYED'	=> $this->lang->lang($lang . '_NOT_DISPLAYED'),
			'L_ICON_ADD'		=> $this->lang->lang('ADD_' . $lang),
			'L_ICON_EDIT'		=> $this->lang->lang('EDIT_' . $lang),

			'NOTICE'			=> $notice,
			'COLSPAN'			=> ($mode === 'smilies') ? 5 : 3,

			'S_SMILIES'			=> ($mode === 'smilies') ? true : false,

			'U_ACTION'			=> $this->u_action,
			'U_IMPORT'			=> $this->u_action . '&amp;action=import',
			'U_EXPORT'			=> $this->u_action . '&amp;action=export',
		]);

		$pagination_start = $this->request->variable('start', 0);
		$spacer = false;

		$item_count = (int) $this->db->get_row_count($table);

		$sql = "SELECT *
			FROM $table
			ORDER BY {$fields}_order ASC";
		$result = $this->db->sql_query_limit($sql, $this->config['smilies_per_page'], $pagination_start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$alt_text = ($mode === 'smilies') ? $row['code'] : (($mode === 'icons' && !empty($row['icons_alt'])) ? $row['icons_alt'] : $row['icons_url']);

			$this->template->assign_block_vars('items', [
				'S_SPACER'		=> (!$spacer && !$row['display_on_posting']) ? true : false,
				'ALT_TEXT'		=> $alt_text,
				'IMG_SRC'		=> $this->root_path . $img_path . '/' . $row[$fields . '_url'],
				'WIDTH'			=> $row[$fields . '_width'],
				'HEIGHT'		=> $row[$fields . '_height'],
				'CODE'			=> isset($row['code']) ? $row['code'] : '',
				'EMOTION'		=> isset($row['emotion']) ? $row['emotion'] : '',

				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $row[$fields . '_id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $row[$fields . '_id'],
				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $row[$fields . '_id'] . '&amp;start=' . $pagination_start . '&amp;hash=' . generate_link_hash('acp_icons'),
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $row[$fields . '_id'] . '&amp;start=' . $pagination_start . '&amp;hash=' . generate_link_hash('acp_icons'),
			]);

			if (!$spacer && !$row['display_on_posting'])
			{
				$spacer = true;
			}
		}
		$this->db->sql_freeresult($result);

		$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $item_count, $this->config['smilies_per_page'], $pagination_start);
	}
}
