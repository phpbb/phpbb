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
* @todo [smilies] check regular expressions for special char replacements (stored specialchared in db)
*/
class acp_icons
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\finder */
	protected $finder;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var ContainerBuilder */
	protected $phpbb_container;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\textformatter\cache_interface */
	protected $text_formatter_cache;

	/** @var string */
	protected $action;

	/** @var string */
	protected $fields;

	/** @var string */
	protected $icon_id;

	/** @var string */
	protected $id;

	/** @var string */
	protected $img_path;

	/** @var string */
	protected $lang;

	/** @var string */
	protected $mode;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $table;

	/** @var string */
	public $u_action;

	/**
	 * Constructor
	 *
	 * @param p_master $module Module object
	 * @access public
	 */
	public function __construct($module)
	{
		global $phpbb_container;
		$this->phpbb_container = $phpbb_container;
		$this->ext_manager = $this->phpbb_container->get('ext.manager');

		$this->cache = $this->phpbb_container->get('cache.driver');
		$this->config = $this->phpbb_container->get('config');
		$this->db = $this->phpbb_container->get('dbal.conn');
		$this->finder = $this->ext_manager->get_finder();
		$this->language = $this->phpbb_container->get('language');
		$this->pagination = $this->phpbb_container->get('pagination');
		$this->request = $this->phpbb_container->get('request');
		$this->template = $this->phpbb_container->get('template');
		$this->text_formatter_cache = $this->phpbb_container->get('text_formatter.cache');
		$this->phpbb_root_path = $this->phpbb_container->getParameter('core.root_path');
		$this->php_ext = $this->phpbb_container->getParameter('core.php_ext');

		$this->id = preg_replace("#^{$module->p_class}_#", '', $module->p_name);
		$this->mode = ($module->p_mode == 'smilies') ? 'smilies' : 'icons';
	}

	/**
	 * The main ACP module method
	 *
	 * @param string $id       Module id
	 * @param string $mode     Module mode (smilies|icons)
	 * @return null
	 */
	public function main($id, $mode)
	{
		$this->init();
		$this->language->add_lang('acp/posting');

		$this->tpl_name = 'acp_icons';
		$this->page_title = 'ACP_' . $this->lang;

		// What shall we do today? Oops, I believe that's trademarked ...
		switch ($this->action)
		{
			case 'add':
			case 'edit':
				$this->add_edit();
				return;
			break;

			case 'create':
			case 'modify':
				$this->create_modify();
			break;

			case 'import':
				$this->import();
			break;

			case 'export':
				$this->export();
				return;
			break;

			case 'send':
				$this->send();
			break;

			case 'delete':
				$this->delete();
			break;

			case 'move_up':
			case 'move_down':
				$this->move();
			break;
		}

		// By default, check that image_order is valid and fix it if necessary
		$this->check_image_order();

		$this->template->assign_vars(array(
			'COLSPAN'			=> ($this->mode == 'smilies') ? 5 : 3,

			'L_TITLE'			=> $this->language->lang('ACP_' . $this->lang),
			'L_EXPLAIN'			=> $this->language->lang('ACP_' . $this->lang . '_EXPLAIN'),
			'L_IMPORT'			=> $this->language->lang('IMPORT_' . $this->lang),
			'L_EXPORT'			=> $this->language->lang('EXPORT_' . $this->lang),
			'L_NOT_DISPLAYED'	=> $this->language->lang($this->lang . '_NOT_DISPLAYED'),
			'L_ICON_ADD'		=> $this->language->lang('ADD_' . $this->lang),
			'L_ICON_EDIT'		=> $this->language->lang('EDIT_' . $this->lang),

			'S_SMILIES'			=> ($this->mode == 'smilies') ? true : false,

			'U_ACTION'			=> $this->u_action,
			'U_IMPORT'			=> $this->u_action . '&amp;action=import',
			'U_EXPORT'			=> $this->u_action . '&amp;action=export',
			)
		);

		$pagination_start = $this->request->variable('start', 0);
		$item_count = $this->item_count();
		$items = $this->get_items_data();

		$this->template->assign_block_vars_array('items', $items);
		$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $item_count, $this->config['smilies_per_page'], $pagination_start);
	}

	/**
	 * Add or edit smilies / topic icons entries
	 *
	 * @return null
	 */
	public function add_edit()
	{
		$_images = ($this->action == 'edit') ? array() : $this->get_imglist();

		$smilies = $default_row = array();
		$smiley_options = $order_list = $add_order_list = '';

		if ($this->action == 'add' && $this->mode == 'smilies')
		{
			$sql = 'SELECT *
				FROM ' . SMILIES_TABLE . '
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

			if (sizeof($smilies))
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

					$this->template->assign_block_vars('smile', array(
						'SMILEY_URL'	=> addslashes($row['smiley_url']),
						'CODE'			=> addslashes($row['code']),
						'EMOTION'		=> addslashes($row['emotion']),
						'WIDTH'			=> $row['smiley_width'],
						'HEIGHT'		=> $row['smiley_height'],
						'ORDER'			=> $row['smiley_order'] + 1,
					));
				}
			}
		}

		$sql = "SELECT *
			FROM {$this->table}
			ORDER BY {$this->fields}_order " . (($this->icon_id || $this->action == 'add') ? 'DESC' : 'ASC');
		$result = $this->db->sql_query($sql);

		$data = array();
		$after = false;
		$display = 0;
		$order_lists = array('', '');
		$add_order_lists = array('', '');
		$display_count = 0;

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($this->action == 'add')
			{
				unset($_images[$row[$this->fields . '_url']]);
			}

			if ($row[$this->fields . '_id'] == $this->icon_id)
			{
				$after = true;
				$display = $row['display_on_posting'];
				$data[$row[$this->fields . '_url']] = $row;
			}
			else
			{
				if ($this->action == 'edit' && !$this->icon_id)
				{
					$data[$row[$this->fields . '_url']] = $row;
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
				$after_txt = ($this->mode == 'smilies') ? $row['code'] : $row['icons_url'];
				$order_lists[$row['display_on_posting']] = '<option value="' . ($row[$this->fields . '_order'] + 1) . '"' . $selected . '>' . $this->language->lang('AFTER_' . $this->lang, ' -&gt; ' . $after_txt) . '</option>' . $order_lists[$row['display_on_posting']];

				if (!empty($default_row))
				{
					$add_order_lists[$row['display_on_posting']] = '<option value="' . ($row[$this->fields . '_order'] + 1) . '"' . (($row[$this->fields . '_id'] == $default_row['smiley_id']) ? ' selected="selected"' : '') . '>' . $this->language->lang('AFTER_' . $this->lang, ' -&gt; ' . $after_txt) . '</option>' . $add_order_lists[$row['display_on_posting']];
				}
			}
		}
		$this->db->sql_freeresult($result);

		$order_list = '<option value="1"' . ((!isset($after)) ? ' selected="selected"' : '') . '>' . $this->language->lang('FIRST') . '</option>';
		$add_order_list = '<option value="1">' . $this->language->lang('FIRST') . '</option>';

		if ($this->action == 'add')
		{
			$data = $_images;
		}

		$colspan = (($this->mode == 'smilies') ? 7 : 6);
		$colspan += ($this->icon_id) ? 1 : 0;
		$colspan += ($this->action == 'add') ? 2 : 0;

		$this->template->assign_vars(array(
			'S_EDIT'		=> true,
			'S_SMILIES'		=> ($this->mode == 'smilies') ? true : false,
			'S_ADD'			=> ($this->action == 'add') ? true : false,

			'S_ORDER_LIST_DISPLAY'		=> $order_list . $order_lists[1],
			'S_ORDER_LIST_UNDISPLAY'	=> $order_list . $order_lists[0],
			'S_ORDER_LIST_DISPLAY_COUNT'	=> $display_count + 1,

			'L_TITLE'		=> $this->language->lang('ACP_' . $this->lang),
			'L_EXPLAIN'		=> $this->language->lang('ACP_' . $this->lang . '_EXPLAIN'),
			'L_CONFIG'		=> $this->language->lang($this->lang . '_CONFIG'),
			'L_URL'			=> $this->language->lang($this->lang . '_URL'),
			'L_LOCATION'	=> $this->language->lang($this->lang . '_LOCATION'),
			'L_WIDTH'		=> $this->language->lang($this->lang . '_WIDTH'),
			'L_HEIGHT'		=> $this->language->lang($this->lang . '_HEIGHT'),
			'L_ORDER'		=> $this->language->lang($this->lang . '_ORDER'),
			'L_NO_ICONS'	=> $this->language->lang('NO_' . $this->lang . '_' . strtoupper($this->action)),

			'COLSPAN'		=> $colspan,
			'ID'			=> $this->icon_id,

			'U_BACK'		=> $this->u_action,
			'U_ACTION'		=> $this->u_action . '&amp;action=' . (($this->action == 'add') ? 'create' : 'modify'),
		));

		foreach ($data as $img => $img_row)
		{
			$this->template->assign_block_vars('items', array(
				'IMG'		=> $img,
				'A_IMG'		=> addslashes($img),
				'IMG_SRC'	=> $this->phpbb_root_path . $img,

				'CODE'		=> ($this->mode == 'smilies' && isset($img_row['code'])) ? $img_row['code'] : '',
				'EMOTION'	=> ($this->mode == 'smilies' && isset($img_row['emotion'])) ? $img_row['emotion'] : '',

				'S_ID'				=> (isset($img_row[$this->fields . '_id'])) ? true : false,
				'ID'				=> (isset($img_row[$this->fields . '_id'])) ? $img_row[$this->fields . '_id'] : 0,
				'WIDTH'				=> (!empty($img_row[$this->fields .'_width'])) ? $img_row[$this->fields .'_width'] : $img_row['width'],
				'HEIGHT'			=> (!empty($img_row[$this->fields .'_height'])) ? $img_row[$this->fields .'_height'] : $img_row['height'],
				'TEXT_ALT'		    => ($this->mode == 'icons' && !empty($img_row['icons_alt'])) ? $img_row['icons_alt'] : $img,
				'ALT'			    => ($this->mode == 'icons' && !empty($img_row['icons_alt'])) ? $img_row['icons_alt'] : '',
				'POSTING_CHECKED'	=> (!empty($img_row['display_on_posting']) || $this->action == 'add') ? ' checked="checked"' : '',
			));
		}

		// Ok, another row for adding an addition code for a pre-existing image...
		if ($this->action == 'add' && $this->mode == 'smilies' && sizeof($smilies))
		{
			$this->template->assign_vars(array(
				'S_ADD_CODE'		=> true,

				'S_IMG_OPTIONS'		=> $smiley_options,

				'S_ADD_ORDER_LIST_DISPLAY'		=> $add_order_list . $add_order_lists[1],
				'S_ADD_ORDER_LIST_UNDISPLAY'	=> $add_order_list . $add_order_lists[0],

				'IMG_SRC'			=> $this->phpbb_root_path . $default_row['smiley_url'],
				'IMG_PATH'			=> $this->img_path,

				'CODE'				=> $default_row['code'],
				'EMOTION'			=> $default_row['emotion'],

				'WIDTH'				=> $default_row['smiley_width'],
				'HEIGHT'			=> $default_row['smiley_height'],
			));
		}

		return;
	}

	/**
	 * Check if image_order is valid and fix it if necessary
	 *
	 * @return null
	 */
	public function check_image_order()
	{
		$sql = "SELECT {$this->fields}_id AS order_id, {$this->fields}_order AS fields_order
			FROM {$this->table}
			ORDER BY display_on_posting DESC, {$this->fields}_order";
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				++$order;
				if ($row['fields_order'] != $order)
				{
					$this->db->sql_query("UPDATE {$this->table}
						SET {$this->fields}_order = $order
						WHERE {$this->fields}_id = " . $row['order_id']);
				}
			}
			while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Create or modify smilies / topic icons entries
	 *
	 * @return null
	 */
	public function create_modify()
	{
		// Get items to create/modify
		$images = ($this->request->is_set_post('image')) ? array_keys($this->request->variable('image', array('' => 0))) : array();

		// Now really get the items
		$image_id		= ($this->request->is_set_post('id')) ? $this->request->variable('id', array('' => 0)) : array();
		$image_order	= ($this->request->is_set_post('order')) ? $this->request->variable('order', array('' => 0)) : array();
		$image_width	= ($this->request->is_set_post('width')) ? $this->request->variable('width', array('' => 0)) : array();
		$image_height	= ($this->request->is_set_post('height')) ? $this->request->variable('height', array('' => 0)) : array();
		$image_add		= ($this->request->is_set_post('add_img')) ? $this->request->variable('add_img', array('' => 0)) : array();
		$image_emotion	= $this->request->variable('emotion', array('' => ''), true);
		$image_code		= $this->request->variable('code', array('' => ''), true);
		$image_alt		= ($this->request->is_set_post('alt')) ? $this->request->variable('alt', array('' => ''), true) : array();
		$image_display_on_posting = ($this->request->is_set_post('display_on_posting')) ? $this->request->variable('display_on_posting', array('' => 0)) : array();

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

		if ($this->mode == 'smilies' && $this->action == 'create')
		{
			$smiley_count = $this->item_count();

			$addable_smileys_count = sizeof($images);
			foreach ($images as $image)
			{
				if (!isset($image_add[$image]))
				{
					--$addable_smileys_count;
				}
			}

			if ($smiley_count + $addable_smileys_count > SMILEY_LIMIT)
			{
				trigger_error($this->language->lang('TOO_MANY_SMILIES', SMILEY_LIMIT) . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		$icons_updated = 0;
		$errors = array();
		foreach ($images as $image)
		{
			if ($this->mode == 'smilies' && ($image_emotion[$image] == '' || $image_code[$image] == ''))
			{
				$errors[$image] = 'SMILIE_NO_' . (($image_emotion[$image] == '') ? 'EMOTION' : 'CODE');
			}
			else if ($this->action == 'create' && !isset($image_add[$image]))
			{
				// skip images where add wasn't checked
			}
			else if (!file_exists($this->phpbb_root_path . $image))
			{
				$errors[$image] = 'SMILIE_NO_FILE';
			}
			else
			{
				if ($image_width[$image] == 0 || $image_height[$image] == 0)
				{
					$img_size = getimagesize($this->phpbb_root_path . $image);
					$image_width[$image] = $img_size[0];
					$image_height[$image] = $img_size[1];
				}

				// Adjust image width/height for icons
				if ($this->mode == 'icons')
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

				$img_sql = array(
					$this->fields . '_url'		=> $image,
					$this->fields . '_width'		=> $image_width[$image],
					$this->fields . '_height'		=> $image_height[$image],
					'display_on_posting'	=> (isset($image_display_on_posting[$image])) ? 1 : 0,
				);

				if ($this->mode == 'smilies')
				{
					$img_sql = array_merge($img_sql, array(
						'emotion'	=> $image_emotion[$image],
						'code'		=> $image_code[$image])
					);
				}

				if ($this->mode == 'icons')
				{
					$img_sql = array_merge($img_sql, array(
						'icons_alt'	=> $image_alt[$image])
					);
				}

				// Image_order holds the 'new' order value
				if (!empty($image_order[$image]))
				{
					$img_sql = array_merge($img_sql, array(
						$this->fields . '_order'	=>	$image_order[$image])
					);

					// Since we always add 'after' an item, we just need to increase all following + the current by one
					$sql = "UPDATE {$this->table}
						SET {$this->fields}_order = {$this->fields}_order + 1
						WHERE {$this->fields}_order >= {$image_order[$image]}";
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

				if ($this->action == 'modify'  && !empty($image_id[$image]))
				{
					$sql = "UPDATE {$this->table}
						SET " . $this->db->sql_build_array('UPDATE', $img_sql) . "
						WHERE {$this->fields}_id = " . $image_id[$image];
					$this->db->sql_query($sql);
					$icons_updated++;
				}
				else if ($this->action !== 'modify')
				{
					$sql = "INSERT INTO {$this->table} " . $this->db->sql_build_array('INSERT', $img_sql);
					$this->db->sql_query($sql);
					$icons_updated++;
				}

			}
		}

		$this->cache->destroy('_icons');
		$this->cache->destroy('sql', $this->table);
		$this->text_formatter_cache->invalidate();

		$level = ($icons_updated) ? E_USER_NOTICE : E_USER_WARNING;
		$errormsgs = '';
		foreach ($errors as $img => $error)
		{
			$errormsgs .= '<br />' . $this->language->lang($error, $img);
		}
		if ($this->action == 'modify')
		{
			trigger_error($this->language->lang($this->lang . '_EDITED', $icons_updated) . $errormsgs . adm_back_link($this->u_action), $level);
		}
		else
		{
			trigger_error($this->language->lang($this->lang . '_ADDED', $icons_updated) . $errormsgs . adm_back_link($this->u_action), $level);
		}
	}

	/**
	 * Delete smilies / topic icons from the database
	 *
	 * @return array $packs    Array with the image packs paths
	 */
	public function delete()
	{
		if (confirm_box(true))
		{
			$sql = "DELETE FROM {$this->table}
				WHERE {$this->fields}_id = {$this->icon_id}";
			$this->db->sql_query($sql);

			switch ($this->mode)
			{
				case 'smilies':
				break;

				case 'icons':
					// Reset appropriate icon_ids
					$this->db->sql_query('UPDATE ' . TOPICS_TABLE . "
						SET icon_id = 0
						WHERE icon_id = {$this->icon_id}");

					$this->db->sql_query('UPDATE ' . POSTS_TABLE . "
						SET icon_id = 0
						WHERE icon_id = {$this->icon_id}");
				break;
			}

			$notice = $this->language->lang($this->lang . '_DELETED');
			$this->template->assign_vars(array(
				'NOTICE'			=> $notice,
			));

			$this->cache->destroy('_icons');
			$this->cache->destroy('sql', $this->table);
			$this->text_formatter_cache->invalidate();

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
					'MESSAGE_TEXT'	=> $notice,
					'REFRESH_DATA'	=> array(
						'time'	=> 3
					)
				));
			}
		}
		else
		{
			confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields(array(
				'i'			=> $this->id,
				'mode'		=> $this->mode,
				'id'		=> $this->icon_id,
				'action'	=> 'delete',
			)));
		}
	}

	/**
	 * Display the smilies / topic icons pack export page
	 *
	 * @return null
	 */
	public function export()
	{
		$this->page_title = 'EXPORT_' . $this->lang;
		$this->tpl_name = 'message_body';

		$this->template->assign_vars(array(
			'MESSAGE_TITLE'		=> $this->language->lang('EXPORT_' . $this->lang),
			'MESSAGE_TEXT'		=> $this->language->lang('EXPORT_' . $this->lang . '_EXPLAIN', '<a href="' . $this->u_action . '&amp;action=send">', '</a>'),

			'S_USER_NOTICE'		=> true,
			)
		);
	}

	/**
	 * Get the list of smilies / topic icons
	 *
	 * @return array $images   Array with the image paths
	 */
	public function get_imglist()
	{
		$imglist = $images = array();

		$imglist = array_keys($this->finder
			->extension_directory("/{$this->mode}")
			->core_path("{$this->img_path}/")
			->find()
		);
		if (!empty($imglist))
		{
			asort($imglist, SORT_STRING);

			foreach ($imglist as $img)
			{
				$img_size = getimagesize($this->phpbb_root_path . $img);

				if (!$img_size[0] || !$img_size[1] || strlen($img) > 255)
				{
					continue;
				}

				// adjust the width and height to be lower than 128px while perserving the aspect ratio (for icons)
				if ($this->mode == 'icons')
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

				$images[$img]['file'] = $img;
				$images[$img]['width'] = $img_size[0];
				$images[$img]['height'] = $img_size[1];
			}
			unset($imglist);

			return $images;
		}
	}

	/**
	 * Get smilies / topic icons packs
	 *
	 * @return array $packs    Array with the image packs paths
	 */
	public function get_imgpacks()
	{
		$packs = array();

		$packs = array_keys($this->finder
			->extension_directory("/{$this->mode}")
			->core_path("{$this->img_path}/")
			->suffix('.pak')
			->find()
		);

		if (!empty($packs))
		{
			asort($packs, SORT_STRING);
		}

		return $packs;
	}

	/**
	 * Get items data to assign to the template block
	 *
	 * @return array $items Array with items data
	 */
	public function get_items_data()
	{
		$pagination_start = $this->request->variable('start', 0);
		$items = array();
		$spacer = false;

		$sql = "SELECT *
			FROM {$this->table}
			ORDER BY {$this->fields}_order ASC";
		$result = $this->db->sql_query_limit($sql, $this->config['smilies_per_page'], $pagination_start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$alt_text = ($this->mode == 'smilies') ? $row['code'] : (($this->mode == 'icons' && !empty($row['icons_alt'])) ? $row['icons_alt'] : $row['icons_url']);

			$items[] = array(
				'S_SPACER'		=> (!$spacer && !$row['display_on_posting']) ? true : false,
				'ALT_TEXT'		=> $alt_text,
				'IMG_SRC'		=> $this->phpbb_root_path . $row[$this->fields . '_url'],
				'WIDTH'			=> $row[$this->fields . '_width'],
				'HEIGHT'		=> $row[$this->fields . '_height'],
				'CODE'			=> (isset($row['code'])) ? $row['code'] : '',
				'EMOTION'		=> (isset($row['emotion'])) ? $row['emotion'] : '',
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $row[$this->fields . '_id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $row[$this->fields . '_id'],
				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $row[$this->fields . '_id'] . '&amp;start=' . $pagination_start,
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $row[$this->fields . '_id'] . '&amp;start=' . $pagination_start,
			);

			if (!$spacer && !$row['display_on_posting'])
			{
				$spacer = true;
			}
		}
		$this->db->sql_freeresult($result);

		return $items;
	}

	/**
	 * Setting initial variable values
	 *
	 * @return null
	 */
	public function init()
	{
		// Set up general vars
		$this->action = $this->request->variable('action', '');
		$this->action = $this->request->is_set_post('add') ? 'add' : $this->action;
		$this->action = $this->request->is_set_post('edit') ? 'edit' : $this->action;
		$this->action = $this->request->is_set_post('import') ? 'import' : $this->action;

		$this->icon_id = $this->request->variable('id', 0);

		$this->table = ($this->mode == 'smilies') ? SMILIES_TABLE : ICONS_TABLE;
		$this->lang = strtoupper($this->mode);
		$this->fields = ($this->mode == 'smilies') ? 'smiley' : 'icons';
		$this->img_path = $this->config["{$this->mode}_path"];
	}

	/**
	 * Import icon packs
	 *
	 * @return null
	 */
	public function import()
	{
		$_paks = $this->get_imgpacks();
		$pak = $this->request->variable('pak', '');
		$current = $this->request->variable('current', '');

		if ($pak != '')
		{
			$order = 0;

			if (!($pak_ary = @file($this->phpbb_root_path . '/' . $pak)))
			{
				trigger_error($this->language->lang('PAK_FILE_NOT_READABLE') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Make sure the pak_ary is valid
			foreach ($pak_ary as $pak_entry)
			{
				if (preg_match_all("#'(.*?)', ?#", $pak_entry, $data))
				{
					if ((sizeof($data[1]) != 4 && $this->mode == 'icons') ||
						((sizeof($data[1]) != 6 || (empty($data[1][4]) || empty($data[1][5]))) && $this->mode == 'smilies' ))
					{
						trigger_error($this->language->lang('WRONG_PAK_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				else
				{
					trigger_error($this->language->lang('WRONG_PAK_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			// The user has already selected a smilies_pak file
			if ($current == 'delete')
			{
				switch ($this->db->get_sql_layer())
				{
					case 'sqlite':
					case 'sqlite3':
						$this->db->sql_query('DELETE FROM ' . $this->table);
					break;

					default:
						$this->db->sql_query('TRUNCATE TABLE ' . $this->table);
					break;
				}

				switch ($this->mode)
				{
					case 'smilies':
					break;

					case 'icons':
						// Reset all icon_ids
						$this->db->sql_query('UPDATE ' . TOPICS_TABLE . ' SET icon_id = 0');
						$this->db->sql_query('UPDATE ' . POSTS_TABLE . ' SET icon_id = 0');
					break;
				}
			}
			else
			{
				$cur_img = array();

				$field_sql = ($this->mode == 'smilies') ? 'code' : 'icons_url';

				$sql = "SELECT $field_sql
					FROM {$this->table}";
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					++$order;
					$cur_img[$row[$field_sql]] = 1;
				}
				$this->db->sql_freeresult($result);
			}

			if ($this->mode == 'smilies')
			{
				$smiley_count = $this->item_count();
				if ($smiley_count + sizeof($pak_ary) > SMILEY_LIMIT)
				{
					trigger_error($this->language->lang('TOO_MANY_SMILIES', SMILEY_LIMIT) . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			foreach ($pak_ary as $pak_entry)
			{
				$data = array();
				if (preg_match_all("#'(.*?)', ?#", $pak_entry, $data))
				{
					if ((sizeof($data[1]) != 4 && $this->mode == 'icons') ||
						(sizeof($data[1]) != 6 && $this->mode == 'smilies'))
					{
						trigger_error($this->language->lang('WRONG_PAK_TYPE') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Stripslash here because it got addslashed before... (on export)
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
						(($this->mode == 'smilies' && !empty($cur_img[$code])) ||
						($this->mode == 'icons' && !empty($cur_img[$img]))))
					{
						$replace_sql = ($this->mode == 'smilies') ? $code : $img;
						$sql = array(
							$this->fields . '_url'		=> $img,
							$this->fields . '_height'		=> (int) $height,
							$this->fields . '_width'		=> (int) $width,
							'display_on_posting'	=> (int) $display_on_posting,
						);

						if ($this->mode == 'smilies')
						{
							$sql = array_merge($sql, array(
								'emotion'				=> $emotion,
							));
						}

						$sql = "UPDATE {$this->table} SET " . $this->db->sql_build_array('UPDATE', $sql) . "
							WHERE $field_sql = '" . $this->db->sql_escape($replace_sql) . "'";
						$this->db->sql_query($sql);
					}
					else
					{
						++$order;

						$sql = array(
							$this->fields . '_url'	=> $img,
							$this->fields . '_height'	=> (int) $height,
							$this->fields . '_width'	=> (int) $width,
							$this->fields . '_order'	=> (int) $order,
							'display_on_posting'=> (int) $display_on_posting,
						);

						if ($this->mode == 'smilies')
						{
							$sql = array_merge($sql, array(
								'code'				=> $code,
								'emotion'			=> $emotion,
							));
						}
						$this->db->sql_query("INSERT INTO {$this->table} " . $this->db->sql_build_array('INSERT', $sql));
					}
				}
			}

			$this->cache->destroy('_icons');
			$this->cache->destroy('sql', $this->table);
			$this->text_formatter_cache->invalidate();

			trigger_error($this->language->lang($this->lang . '_IMPORT_SUCCESS') . adm_back_link($this->u_action));
		}
		else
		{
			$pak_options = '';
			foreach ($_paks as $pak)
			{
				$pak_options .= '<option value="' . $pak . '">' . htmlspecialchars($pak) . '</option>';
			}

			$this->template->assign_vars(array(
				'S_CHOOSE_PAK'		=> true,
				'S_PAK_OPTIONS'		=> $pak_options,

				'L_TITLE'			=> $this->language->lang('ACP_' . $this->lang),
				'L_EXPLAIN'			=> $this->language->lang('ACP_' . $this->lang . '_EXPLAIN'),
				'L_NO_PAK_OPTIONS'	=> $this->language->lang('NO_' . $this->lang . '_PAK'),
				'L_CURRENT'			=> $this->language->lang('CURRENT_' . $this->lang),
				'L_CURRENT_EXPLAIN'	=> $this->language->lang('CURRENT_' . $this->lang . '_EXPLAIN'),
				'L_IMPORT_SUBMIT'	=> $this->language->lang('IMPORT_' . $this->lang),

				'U_BACK'		=> $this->u_action,
				'U_ACTION'		=> $this->u_action . '&amp;action=import',
				)
			);
		}
	}

	/**
	 * Returns the count of smilies or icons in the database
	 *
	 * @return int number of items
	 */
	/* private */ function item_count()
	{
		$sql = "SELECT COUNT(*) AS item_count
			FROM {$this->table}";
		$result = $this->db->sql_query($sql);
		$item_count = (int) $this->db->sql_fetchfield('item_count');
		$this->db->sql_freeresult($result);

		return $item_count;
	}

	/**
	 * Move items up and down
	 *
	 * @return null
	 */
	public function move()
	{
		// Get current order id...
		$sql = "SELECT {$this->fields}_order as current_order
			FROM {$this->table}
			WHERE {$this->fields}_id = {$this->icon_id}";
		$result = $this->db->sql_query($sql);
		$current_order = (int) $this->db->sql_fetchfield('current_order');
		$this->db->sql_freeresult($result);

		if ($current_order == 0 && $this->action == 'move_up')
		{
			return;
		}

		// on move_down, switch position with next order_id...
		// on move_up, switch position with previous order_id...
		$switch_order_id = ($this->action == 'move_down') ? $current_order + 1 : $current_order - 1;

		//
		$sql = "UPDATE {$this->table}
			SET {$this->fields}_order = $current_order
			WHERE {$this->fields}_order = $switch_order_id
				AND {$this->fields}_id <> {$this->icon_id}";
		$this->db->sql_query($sql);
		$move_executed = (bool) $this->db->sql_affectedrows();

		// Only update the other entry too if the previous entry got updated
		if ($move_executed)
		{
			$sql = "UPDATE {$this->table}
				SET {$this->fields}_order = $switch_order_id
				WHERE {$this->fields}_order = $current_order
					AND {$this->fields}_id = {$this->icon_id}";
			$this->db->sql_query($sql);
		}

		$this->cache->destroy('_icons');
		$this->cache->destroy('sql', $this->table);
		$this->text_formatter_cache->invalidate();

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'success'	=> $move_executed,
			));
		}
	}

	/**
	 * Send icons pack configuration content to the browser
	 *
	 * @return null
	 */
	public function send()
	{
		$sql = "SELECT *
			FROM {$this->table}
			ORDER BY {$this->fields}_order";
		$result = $this->db->sql_query($sql);

		$pak = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$pak .= "'" . addslashes($row[$this->fields . '_url']) . "', ";
			$pak .= "'" . addslashes($row[$this->fields . '_width']) . "', ";
			$pak .= "'" . addslashes($row[$this->fields . '_height']) . "', ";
			$pak .= "'" . addslashes($row['display_on_posting']) . "', ";

			if ($this->mode == 'smilies')
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
			header('Content-Type: text/x-delimtext; name="' . $this->mode . '.pak"');
			header('Content-Disposition: inline; filename="' . $this->mode . '.pak"');
			echo $pak;

			flush();
			exit;
		}
		else
		{
			trigger_error($this->language->lang('NO_' . strtoupper($this->fields) . '_EXPORT') . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}
}
