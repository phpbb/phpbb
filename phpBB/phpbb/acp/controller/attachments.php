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

class attachments
{
	/** @var \phpbb\attachment\manager */
	protected $attachment_manager;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache_driver;

	/** @var \phpbb\cache\service */
	protected $cache_service;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpBB web path */
	protected $web_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @var array */
	protected $new_config;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\attachment\manager				$attachment_manager	Attachment manager object
	 * @param \phpbb\auth\auth						$auth				Auth object
	 * @param \phpbb\cache\driver\driver_interface	$cache_driver		Cache driver object
	 * @param \phpbb\cache\service					$cache_service		Cache service object
	 * @param \phpbb\config\config					$config				Config object
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \phpbb\event\dispatcher				$dispatcher			Event dispatcher object
	 * @param \phpbb\filesystem\filesystem			$filesystem			Filesystem object
	 * @param \phpbb\acp\helper\controller			$helper				ACP Controller helper object
	 * @param \phpbb\language\language				$language			Language object
	 * @param \phpbb\log\log						$log				Log object
	 * @param \phpbb\pagination						$pagination			Pagination object
	 * @param \phpbb\path_helper					$path_helper		Path helper object
	 * @param \phpbb\request\request				$request			Request object
	 * @param \phpbb\template\template				$template			Template object
	 * @param \phpbb\user							$user				User object
	 * @param string								$root_path			phpBB root path
	 * @param string								$php_ext			php File extension
	 * @param array									$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\attachment\manager $attachment_manager,
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache_driver,
		\phpbb\cache\service $cache_service,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\filesystem\filesystem $filesystem,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\path_helper $path_helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->attachment_manager	= $attachment_manager;
		$this->auth					= $auth;
		$this->cache_driver			= $cache_driver;
		$this->cache_service		= $cache_service;
		$this->config				= $config;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->filesystem			= $filesystem;
		$this->helper				= $helper;
		$this->language				= $language;
		$this->log					= $log;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
		$this->root_path			= $root_path;
		$this->web_path				= $path_helper->update_web_root_path($root_path);
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function main($mode, $page = 1)
	{
		$this->language->add_lang(['posting', 'viewtopic', 'acp/attachments']);

		$errors = $notify = [];
		$submit = $this->request->is_set_post('submit');
		$action = $this->request->variable('action', '');

		switch ($mode)
		{
			case 'attach':
				$l_title = 'ACP_SETTINGS_ATTACHMENT';
				$u_mode = 'acp_settings_attachment';
			break;
			default:
				$l_title = 'ACP_ATTACHMENTS_' . utf8_strtoupper($mode);
				$u_mode = 'acp_settings_' . $mode;
			break;
		}

		add_form_key($u_mode);

		if ($submit && !check_form_key($u_mode))
		{
			return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route($u_mode), E_USER_WARNING);
		}

		$this->template->assign_vars([
			'L_TITLE'			=> $this->language->lang($l_title),
			'L_TITLE_EXPLAIN'	=> $this->language->lang($l_title . '_EXPLAIN'),

			'U_ACTION'			=> $this->helper->get_current_url(),
		]);

		switch ($mode)
		{
			case 'attach':
				if (!function_exists('get_supported_image_types'))
				{
					include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
				}

				$s_assigned_groups = [];

				$sql = 'SELECT group_name, cat_id
					FROM ' . $this->tables['extension_groups'] . '
					WHERE cat_id > 0
					ORDER BY cat_id';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$s_assigned_groups[(int) $row['cat_id']][] = $this->get_extension_group_name($row['group_name']);
				}
				$this->db->sql_freeresult($result);

				$l_legend_cat_images = $this->language->lang('SETTINGS_CAT_IMAGES') . ' [' . $this->language->lang('ASSIGNED_GROUP') . ': ' . ((!empty($s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE])) ? implode($this->language->lang('COMMA_SEPARATOR'), $s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE]) : $this->language->lang('NO_EXT_GROUP')) . ']';

				$display_vars = [
					'title'	=> 'ACP_ATTACHMENT_SETTINGS',
					'vars'	=> [
						'legend1'				=> 'ACP_ATTACHMENT_SETTINGS',

						'img_max_width'			=> ['lang' => 'MAX_IMAGE_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,],
						'img_max_height'		=> ['lang' => 'MAX_IMAGE_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,],
						'img_link_width'		=> ['lang' => 'IMAGE_LINK_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,],
						'img_link_height'		=> ['lang' => 'IMAGE_LINK_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,],

						'allow_attachments'		=> ['lang' => 'ALLOW_ATTACHMENTS', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false],
						'allow_pm_attach'		=> ['lang' => 'ALLOW_PM_ATTACHMENTS', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false],
						'display_order'			=> ['lang' => 'DISPLAY_ORDER', 'validate' => 'bool', 'type' => 'custom', 'function' => [$this, 'display_order'], 'explain' => true],
						'attachment_quota'		=> ['lang' => 'ATTACH_QUOTA', 'validate' => 'string', 'type' => 'custom', 'function' => [$this, 'max_filesize'], 'explain' => true],
						'max_filesize'			=> ['lang' => 'ATTACH_MAX_FILESIZE', 'validate' => 'string', 'type' => 'custom', 'function' => [$this, 'max_filesize'], 'explain' => true],
						'max_filesize_pm'		=> ['lang' => 'ATTACH_MAX_PM_FILESIZE', 'validate' => 'string', 'type' => 'custom', 'function' => [$this, 'max_filesize'], 'explain' => true],
						'max_attachments'		=> ['lang' => 'MAX_ATTACHMENTS', 'validate' => 'int:0:999', 'type' => 'number:0:999', 'explain' => false],
						'max_attachments_pm'	=> ['lang' => 'MAX_ATTACHMENTS_PM', 'validate' => 'int:0:999', 'type' => 'number:0:999', 'explain' => false],
						'secure_downloads'		=> ['lang' => 'SECURE_DOWNLOADS', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
						'secure_allow_deny'		=> ['lang' => 'SECURE_ALLOW_DENY', 'validate' => 'int', 'type' => 'custom', 'function' => [$this, 'select_allow_deny'], 'explain' => true],
						'secure_allow_empty_referer'	=> ['lang' => 'SECURE_EMPTY_REFERRER', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
						'check_attachment_content' 		=> ['lang' => 'CHECK_CONTENT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],

						'legend2'					=> $l_legend_cat_images,
						'img_display_inlined'		=> ['lang' => 'DISPLAY_INLINED', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
						'img_create_thumbnail'		=> ['lang' => 'CREATE_THUMBNAIL', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
						'img_max_thumb_width'		=> ['lang' => 'MAX_THUMB_WIDTH', 'validate' => 'int:0:999999999999999', 'type' => 'number:0:999999999999999', 'explain' => true, 'append' => ' ' . $this->language->lang('PIXEL')],
						'img_min_thumb_filesize'	=> ['lang' => 'MIN_THUMB_FILESIZE', 'validate' => 'int:0:999999999999999', 'type' => 'number:0:999999999999999', 'explain' => true, 'append' => ' ' . $this->language->lang('BYTES')],
						'img_strip_metadata'		=> ['lang' => 'IMAGE_STRIP_METADATA', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
						'img_quality'				=> ['lang' => 'IMAGE_QUALITY', 'validate' => 'int:50:90', 'type' => 'number:50:90', 'explain' => true, 'append' => ' &percnt;'],
						'img_max'					=> ['lang' => 'MAX_IMAGE_SIZE', 'validate' => 'int:0:9999', 'type' => 'dimension:0:9999', 'explain' => true, 'append' => ' ' . $this->language->lang('PIXEL')],
						'img_link'					=> ['lang' => 'IMAGE_LINK_SIZE', 'validate' => 'int:0:9999', 'type' => 'dimension:0:9999', 'explain' => true, 'append' => ' ' . $this->language->lang('PIXEL')],
					],
				];

				/**
				 * Event to add and/or modify acp_attachment configurations
				 *
				 * @event core.acp_attachments_config_edit_add
				 * @var array	display_vars	Array of config values to display and process
				 * @var string	mode			Mode of the config page we are displaying
				 * @var boolean	submit			Do we display the form or process the submission
				 * @since 3.1.11-RC1
				 */
				$vars = ['display_vars', 'mode', 'submit'];
				extract($this->dispatcher->trigger_event('core.acp_attachments_config_edit_add', compact($vars)));

				$this->new_config = $this->config;
				$cfg_array = $this->request->is_set('config') ? $this->request->variable('config', ['' => '']) : $this->new_config;
				$errors = [];

				// We validate the complete config if wished
				validate_config_vars($display_vars['vars'], $cfg_array, $errors);

				// Do not write values if there is an error
				if (!empty($errors))
				{
					$submit = false;
				}

				// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
				foreach ($display_vars['vars'] as $config_name => $null)
				{
					if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
					{
						continue;
					}

					$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

					if (in_array($config_name, ['attachment_quota', 'max_filesize', 'max_filesize_pm']))
					{
						$size_var = $this->request->variable($config_name, '');
						$this->new_config[$config_name] = $config_value = $size_var === 'kb' ? round($config_value * 1024) : ($size_var === 'mb' ? round($config_value * 1048576) : $config_value);
					}

					if ($submit)
					{
						$this->config->set($config_name, $config_value);
					}
				}

				if ($this->request->is_set_post('securesubmit') || $this->request->is_set_post('unsecuresubmit'))
				{
					return $this->perform_site_list();
				}

				if ($submit)
				{
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_ATTACH');

					if (empty($errors))
					{
						return $this->helper->message_back('CONFIG_UPDATED', $u_mode);
					}
				}

				$this->template->assign_var('S_ATTACHMENT_SETTINGS', true);

				// Secure Download Options - Same procedure as with banning
				$allow_deny = $this->new_config['secure_allow_deny'] ? 'ALLOWED' : 'DISALLOWED';

				$sql = 'SELECT *
					FROM ' . $this->tables['sitelist'];
				$result = $this->db->sql_query($sql);

				$defined_ips = '';
				$ips = [];

				while ($row = $this->db->sql_fetchrow($result))
				{
					$value = ($row['site_ip']) ? $row['site_ip'] : $row['site_hostname'];
					if ($value)
					{
						$defined_ips .= '<option' . ($row['ip_exclude'] ? ' class="sep"' : '') . ' value="' . $row['site_id'] . '">' . $value . '</option>';
						$ips[$row['site_id']] = $value;
					}
				}
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_WARNING'				=> !empty($errors),
					'WARNING_MSG'			=> implode('<br />', $errors),

					'DEFINED_IPS'			=> $defined_ips,
					'S_DEFINED_IPS'			=> $defined_ips !== '',
					'S_SECURE_DOWNLOADS'	=> $this->new_config['secure_downloads'],

					'L_SECURE_TITLE'		=> $this->language->lang('DEFINE_' . $allow_deny . '_IPS'),
					'L_IP_EXCLUDE'			=> $this->language->lang('EXCLUDE_FROM_' . $allow_deny . '_IP'),
					'L_REMOVE_IPS'			=> $this->language->lang('REMOVE_' . $allow_deny . '_IPS'),
				]);

				// Output relevant options
				foreach ($display_vars['vars'] as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}

					if (strpos($config_key, 'legend') !== false)
					{
						$this->template->assign_block_vars('options', [
							'S_LEGEND'		=> true,
							'LEGEND'		=> $this->language->lang($vars),
						]);

						continue;
					}

					$type = explode(':', $vars['type']);

					$l_explain = '';
					if ($vars['explain'] && isset($vars['lang_explain']))
					{
						$l_explain = $this->language->lang($vars['lang_explain']);
					}
					else if ($vars['explain'])
					{
						$l_explain = $this->language->is_set($vars['lang'] . '_EXPLAIN') ? $this->language->lang($vars['lang'] . '_EXPLAIN') : '';
					}

					$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);
					if (empty($content))
					{
						continue;
					}

					$this->template->assign_block_vars('options', [
						'KEY'			=> $config_key,
						'TITLE'			=> $this->language->lang($vars['lang']),
						'S_EXPLAIN'		=> $vars['explain'],
						'TITLE_EXPLAIN'	=> $l_explain,
						'CONTENT'		=> $content,
					]);

					unset($display_vars['vars'][$config_key]);
				}
			break;

			case 'extensions':
				if ($submit || $this->request->is_set_post('add_extension_check'))
				{
					if ($submit)
					{
						// Change Extensions ?
						$extension_change_list	= $this->request->variable('extension_change_list', [0]);
						$group_select_list		= $this->request->variable('group_select', [0]);

						// Generate correct Change List
						$extensions = [];

						for ($i = 0, $size = count($extension_change_list); $i < $size; $i++)
						{
							$extensions[$extension_change_list[$i]]['group_id'] = $group_select_list[$i];
						}

						$sql = 'SELECT *
							FROM ' . $this->tables['extensions'] . '
							ORDER BY extension_id';
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							if ($row['group_id'] != $extensions[$row['extension_id']]['group_id'])
							{
								$sql = 'UPDATE ' . $this->tables['extensions'] . '
									SET group_id = ' . (int) $extensions[$row['extension_id']]['group_id'] . '
									WHERE extension_id = ' . (int) $row['extension_id'];
								$this->db->sql_query($sql);

								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACH_EXT_UPDATE', false, [$row['extension']]);
							}
						}
						$this->db->sql_freeresult($result);

						// Delete Extension?
						$extension_id_list = $this->request->variable('extension_id_list', [0]);

						if (!empty($extension_id_list))
						{
							$extension_list = '';

							$sql = 'SELECT extension
								FROM ' . $this->tables['extensions'] . '
								WHERE ' . $this->db->sql_in_set('extension_id', $extension_id_list);
							$result = $this->db->sql_query($sql);
							while ($row = $this->db->sql_fetchrow($result))
							{
								$extension_list .= $extension_list === '' ? $row['extension'] : ', ' . $row['extension'];
							}
							$this->db->sql_freeresult($result);

							$sql = 'DELETE
								FROM ' . $this->tables['extensions'] . '
								WHERE ' . $this->db->sql_in_set('extension_id', $extension_id_list);
							$this->db->sql_query($sql);

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACH_EXT_DEL', false, [$extension_list]);
						}
					}

					// Add Extension?
					$add_extension			= strtolower($this->request->variable('add_extension', ''));
					$add_extension_group	= $this->request->variable('add_group_select', 0);
					$add					= $this->request->is_set_post('add_extension_check');

					if ($add_extension && $add)
					{
						if (empty($errors))
						{
							$sql = 'SELECT extension_id
								FROM ' . $this->tables['extensions'] . "
								WHERE extension = '" . $this->db->sql_escape($add_extension) . "'";
							$result = $this->db->sql_query($sql);
							if ($row = $this->db->sql_fetchrow($result))
							{
								$errors[] = $this->language->lang('EXTENSION_EXIST', $add_extension);
							}
							$this->db->sql_freeresult($result);

							if (empty($errors))
							{
								$sql_ary = [
									'group_id'	=>	$add_extension_group,
									'extension'	=>	$add_extension,
								];

								$sql = 'INSERT INTO ' . $this->tables['extensions'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
								$this->db->sql_query($sql);

								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACH_EXT_ADD', false, [$add_extension]);
							}
						}
					}

					if (empty($errors))
					{
						$notify[] = $this->language->lang('EXTENSIONS_UPDATED');
					}

					$this->cache_driver->destroy('_extensions');
				}

				$this->template->assign_vars([
					'S_EXTENSIONS'			=> true,
					'ADD_EXTENSION'			=> isset($add_extension) ? $add_extension : '',
					'GROUP_SELECT_OPTIONS'	=> $this->request->is_set_post('add_extension_check') ? $this->group_select('add_group_select', $add_extension_group, 'extension_group') : $this->group_select('add_group_select', false, 'extension_group'),
				]);

				$sql = 'SELECT *
					FROM ' . $this->tables['extensions'] . '
					ORDER BY group_id, extension';
				$result = $this->db->sql_query($sql);

				if ($row = $this->db->sql_fetchrow($result))
				{
					$old_group_id = $row['group_id'];
					do
					{
						$s_spacer = false;

						$current_group_id = $row['group_id'];
						if ($old_group_id != $current_group_id)
						{
							$s_spacer = true;
							$old_group_id = $current_group_id;
						}

						$this->template->assign_block_vars('extensions', [
							'S_SPACER'		=> $s_spacer,
							'EXTENSION_ID'	=> $row['extension_id'],
							'EXTENSION'		=> $row['extension'],
							'GROUP_OPTIONS'	=> $this->group_select('group_select[]', $row['group_id']),
						]);
					}
					while ($row = $this->db->sql_fetchrow($result));
				}
				$this->db->sql_freeresult($result);
			break;

			case 'ext_groups':
				$this->template->assign_var('S_EXTENSION_GROUPS', true);

				if ($submit)
				{
					$action = $this->request->variable('action', '');
					$group_id = $this->request->variable('g', 0);

					if ($action !== 'add' && $action !== 'edit')
					{
						return trigger_error($this->language->lang('NO_MODE') . $this->helper->adm_back_route($u_mode), E_USER_ERROR);
					}

					if (!$group_id && $action === 'edit')
					{
						return trigger_error($this->language->lang('NO_EXT_GROUP_SPECIFIED') . $this->helper->adm_back_route($u_mode), E_USER_WARNING);
					}

					if ($group_id)
					{
						$sql = 'SELECT *
							FROM ' . $this->tables['extension_groups'] . '
							WHERE group_id = ' . (int) $group_id;
						$result = $this->db->sql_query($sql);
						$ext_row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if ($ext_row === false)
						{
							return trigger_error($this->language->lang('NO_EXT_GROUP_SPECIFIED') . $this->helper->adm_back_route($u_mode), E_USER_WARNING);
						}
					}
					else
					{
						$ext_row = [];
					}

					$group_name = $this->request->variable('group_name', '', true);
					$new_group_name = $action === 'add' ? $group_name : ($ext_row['group_name'] != $group_name ? $group_name : '');

					if (!$group_name)
					{
						$errors[] = $this->language->lang('NO_EXT_GROUP_NAME');
					}

					// Check New Group Name
					if ($new_group_name)
					{
						$sql = 'SELECT group_id
							FROM ' . $this->tables['extension_groups'] . '
							WHERE ' . $this->db->sql_lower_text('group_name') . " = '" . $this->db->sql_escape(utf8_strtolower($new_group_name)) . "'";
						if ($group_id)
						{
							$sql .= ' AND group_id <> ' . $group_id;
						}
						$result = $this->db->sql_query($sql);

						if ($this->db->sql_fetchrow($result))
						{
							$errors[] = $this->language->lang('EXTENSION_GROUP_EXIST', $new_group_name);
						}
						$this->db->sql_freeresult($result);
					}

					if (empty($errors))
					{
						// Ok, build the update/insert array
						$upload_icon	= $this->request->variable('upload_icon', 'no_image');
						$size_select	= $this->request->variable('size_select', 'b');
						$forum_select	= $this->request->variable('forum_select', false);
						$allowed_forums	= $this->request->variable('allowed_forums', [0]);
						$allow_in_pm	= $this->request->is_set_post('allow_in_pm');
						$max_filesize	= $this->request->variable('max_filesize', 0);
						$max_filesize	= $size_select === 'kb' ? round($max_filesize * 1024) : ($size_select === 'mb' ? round($max_filesize * 1048576) : $max_filesize);
						$allow_group	= $this->request->is_set_post('allow_group');

						if ($max_filesize === (int) $this->config['max_filesize'])
						{
							$max_filesize = 0;
						}

						if (empty($allowed_forums))
						{
							$forum_select = false;
						}

						$group_ary = [
							'group_name'	=> $group_name,
							'cat_id'		=> $this->request->variable('special_category', ATTACHMENT_CATEGORY_NONE),
							'allow_group'	=> $allow_group ? 1 : 0,
							'upload_icon'	=> $upload_icon === 'no_image' ? '' : $upload_icon,
							'max_filesize'	=> $max_filesize,
							'allowed_forums'=> $forum_select ? serialize($allowed_forums) : '',
							'allow_in_pm'	=> $allow_in_pm ? 1 : 0,
						];

						$sql = $action === 'add' ? 'INSERT INTO ' . $this->tables['extension_groups'] . ' ' : 'UPDATE ' . $this->tables['extension_groups'] . ' SET ';
						$sql .= $this->db->sql_build_array(($action === 'add' ? 'INSERT' : 'UPDATE'), $group_ary);
						$sql .= $action === 'edit' ? ' WHERE group_id = ' . (int) $group_id : '';

						$this->db->sql_query($sql);

						if ($action === 'add')
						{
							$group_id = $this->db->sql_nextid();
						}

						$group_name = $this->get_extension_group_name($group_name);
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACH_EXTGROUP_' . strtoupper($action), false, [$group_name]);
					}

					$extension_list = $this->request->variable('extensions', [0]);

					if ($action === 'edit' && !empty($extension_list))
					{
						$sql = 'UPDATE ' . $this->tables['extensions'] . '
							SET group_id = 0
							WHERE group_id = ' . (int) $group_id;
						$this->db->sql_query($sql);
					}

					if (!empty($extension_list))
					{
						$sql = 'UPDATE ' . $this->tables['extensions'] . '
							SET group_id = ' . (int) $group_id . '
							WHERE ' . $this->db->sql_in_set('extension_id', $extension_list);
						$this->db->sql_query($sql);
					}

					$this->cache_driver->destroy('_extensions');

					if (empty($errors))
					{
						$notify[] = $this->language->lang('SUCCESS_EXTENSION_GROUP_' . strtoupper($action));
					}
				}

				$cat_lang = [
					ATTACHMENT_CATEGORY_NONE	=> $this->language->lang('NO_FILE_CAT'),
					ATTACHMENT_CATEGORY_IMAGE	=> $this->language->lang('CAT_IMAGES'),
				];

				$group_id = $this->request->variable('g', 0);
				$action = $this->request->is_set_post('add') ? 'add' : $action;

				$ext_group_row = [];
				$forum_ids = [];

				switch ($action)
				{
					case 'delete':
						if (confirm_box(true))
						{
							$sql = 'SELECT group_name
								FROM ' . $this->tables['extension_groups'] . '
								WHERE group_id = ' . (int) $group_id;
							$result = $this->db->sql_query($sql);
							$group_name = (string) $this->db->sql_fetchfield('group_name');
							$this->db->sql_freeresult($result);

							$sql = 'DELETE
								FROM ' . $this->tables['extension_groups'] . '
								WHERE group_id = ' . (int) $group_id;
							$this->db->sql_query($sql);

							// Set corresponding Extensions to a pending Group
							$sql = 'UPDATE ' . $this->tables['extensions'] . '
								SET group_id = 0
								WHERE group_id = ' . (int) $group_id;
							$this->db->sql_query($sql);

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACH_EXTGROUP_DEL', false, [$group_name]);

							$this->cache_driver->destroy('_extensions');

							return $this->helper->message_back('EXTENSION_GROUP_DELETED', $u_mode);
						}
						else
						{
							confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
								'mode'		=> $mode,
								'action'	=> $action,
								'group_id'	=> $group_id,
							]));

							return redirect($u_mode);
						}
					break;

					case 'edit':
						if (!$group_id)
						{
							return trigger_error($this->language->lang('NO_EXT_GROUP_SPECIFIED') . $this->helper->adm_back_route($u_mode), E_USER_WARNING);
						}

						$sql = 'SELECT *
							FROM ' . $this->tables['extension_groups'] . '
							WHERE group_id = ' . (int) $group_id;
						$result = $this->db->sql_query($sql);
						$ext_group_row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$forum_ids = !$ext_group_row['allowed_forums'] ? [] : unserialize(trim($ext_group_row['allowed_forums']));
					// no break;

					case 'add':
						if ($action === 'add')
						{
							$ext_group_row = [
								'group_name'	=> $this->request->variable('group_name', '', true),
								'cat_id'		=> 0,
								'allow_group'	=> 1,
								'allow_in_pm'	=> 1,
								'upload_icon'	=> '',
								'max_filesize'	=> 0,
							];
						}

						$sql = 'SELECT *
							FROM ' . $this->tables['extensions'] . '
							WHERE group_id = ' . (int) $group_id . '
								OR group_id = 0
							ORDER BY extension';
						$result = $this->db->sql_query($sql);
						$extensions = $this->db->sql_fetchrowset($result);
						$this->db->sql_freeresult($result);

						if ($ext_group_row['max_filesize'] == 0)
						{
							$ext_group_row['max_filesize'] = (int) $this->config['max_filesize'];
						}

						$max_filesize = get_formatted_filesize($ext_group_row['max_filesize'], false, ['mb', 'kb', 'b']);
						$size_format = $max_filesize['si_identifier'];
						$ext_group_row['max_filesize'] = $max_filesize['value'];

						$filename_list = '';
						$no_image_select = false;

						$img_path = $this->config['upload_icons_path'];
						$img_list = filelist($this->root_path . $img_path);

						if (!empty($img_list['']))
						{
							$img_list = array_values($img_list);
							$img_list = $img_list[0];

							foreach ($img_list as $key => $img)
							{
								if (!$ext_group_row['upload_icon'])
								{
									$no_image_select = true;
									$selected = '';
								}
								else
								{
									$selected = $ext_group_row['upload_icon'] == $img ? ' selected="selected"' : '';
								}

								if (strlen($img) > 255)
								{
									continue;
								}

								$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . htmlspecialchars($img) . '</option>';
							}
						}

						$i = 0;
						$assigned_extensions = '';
						foreach ($extensions as $num => $row)
						{
							if ($row['group_id'] == $group_id && $group_id)
							{
								$assigned_extensions .= $i ? ', ' . $row['extension'] : $row['extension'];
								$i++;
							}
						}

						$s_extension_options = '';
						foreach ($extensions as $row)
						{
							$s_extension_options .= '<option' . (!$row['group_id'] ? ' class="disabled"' : '') . ' value="' . $row['extension_id'] . '"' . (($row['group_id'] == $group_id && $group_id) ? ' selected="selected"' : '') . '>' . $row['extension'] . '</option>';
						}

						$this->template->assign_vars([
							'ACTION'				=> $action,
							'ALLOW_GROUP'			=> $ext_group_row['allow_group'],
							'ALLOW_IN_PM'			=> $ext_group_row['allow_in_pm'],
							'ASSIGNED_EXTENSIONS'	=> $assigned_extensions,
							'GROUP_ID'				=> $group_id,
							'GROUP_NAME'			=> $ext_group_row['group_name'],
							'EXTGROUP_FILESIZE'		=> $ext_group_row['max_filesize'],
							'IMG_PATH'				=> $img_path,
							'UPLOAD_ICON_SRC'		=> $this->web_path . $img_path . '/' . $ext_group_row['upload_icon'],

							'L_LEGEND'				=> $this->language->lang(strtoupper($action) . '_EXTENSION_GROUP'),

							'S_CATEGORY_SELECT'			=> $this->category_select('special_category', $group_id, 'category'),
							'S_EDIT_GROUP'				=> true,
							'S_EXT_GROUP_SIZE_OPTIONS'	=> size_select_options($size_format),
							'S_EXTENSION_OPTIONS'		=> $s_extension_options,
							'S_FILENAME_LIST'			=> $filename_list,
							'S_FORUM_ID_OPTIONS'		=> make_forum_select($forum_ids),
							'S_FORUM_IDS'				=> !empty($forum_ids),
							'S_NO_IMAGE'				=> $no_image_select,

							'U_BACK'			=> $this->helper->route('acp_attachments_ext_groups'),
							'U_EXTENSIONS'		=> $this->helper->route('acp_attachments_extensions'),
						]);
					break;
				}

				$old_allow_group = $old_allow_pm = 1;

				$sql = 'SELECT *
					FROM ' . $this->tables['extension_groups'] . '
					ORDER BY allow_group DESC, allow_in_pm DESC, group_name';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$s_add_spacer = ($old_allow_group != $row['allow_group'] || $old_allow_pm != $row['allow_in_pm']) ? true : false;

					$this->template->assign_block_vars('groups', [
						'GROUP_NAME'	=> $this->get_extension_group_name($row['group_name']),
						'CATEGORY'		=> $cat_lang[$row['cat_id']],

						'S_ADD_SPACER'		=> $s_add_spacer,
						'S_ALLOWED_IN_PM'	=> (bool) $row['allow_in_pm'],
						'S_GROUP_ALLOWED'	=> (bool) $row['allow_group'],

						'U_DELETE'		=> $this->helper->route('acp_attachments_ext_groups', ['action' => 'delete', 'g' => $row['group_id']]),
						'U_EDIT'		=> $this->helper->route('acp_attachments_ext_groups', ['action' => 'edit', 'g' => $row['group_id']]),
					]);

					$old_allow_group = $row['allow_group'];
					$old_allow_pm = $row['allow_in_pm'];
				}
				$this->db->sql_freeresult($result);
			break;

			case 'orphan':
				if ($submit)
				{
					$delete_files = $this->request->is_set_post('delete') ? array_keys($this->request->variable('delete', ['' => 0])) : [];
					$add_files = $this->request->is_set_post('add') ? array_keys($this->request->variable('add', ['' => 0])) : [];
					$post_ids = $this->request->variable('post_id', ['' => 0]);

					if (!empty($delete_files))
					{
						$sql = 'SELECT *
							FROM ' . $this->tables['attachments'] . '
							WHERE ' . $this->db->sql_in_set('attach_id', $delete_files) . '
								AND is_orphan = 1';
						$result = $this->db->sql_query($sql);

						$delete_files = [];
						while ($row = $this->db->sql_fetchrow($result))
						{
							$this->attachment_manager->unlink($row['physical_filename'], 'file');

							if ($row['thumbnail'])
							{
								$this->attachment_manager->unlink($row['physical_filename'], 'thumbnail');
							}

							$delete_files[$row['attach_id']] = $row['real_filename'];
						}
						$this->db->sql_freeresult($result);
					}

					if (!empty($delete_files))
					{
						$sql = 'DELETE FROM ' . $this->tables['attachments'] . '
							WHERE ' . $this->db->sql_in_set('attach_id', array_keys($delete_files));
						$this->db->sql_query($sql);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACH_ORPHAN_DEL', false, [implode(', ', $delete_files)]);
						$notify[] = $this->language->lang('LOG_ATTACH_ORPHAN_DEL', implode($this->language->lang('COMMA_SEPARATOR'), $delete_files));
					}

					$upload_list = [];
					foreach ($add_files as $attach_id)
					{
						if (!isset($delete_files[$attach_id]) && !empty($post_ids[$attach_id]))
						{
							$upload_list[$attach_id] = $post_ids[$attach_id];
						}
					}
					unset($add_files);

					if (!empty($upload_list))
					{
						$this->template->assign_var('S_UPLOADING_FILES', true);

						$forum_names = [];

						$sql = 'SELECT forum_id, forum_name
							FROM ' . $this->tables['forums'];
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$forum_names[(int) $row['forum_id']] = $row['forum_name'];
						}
						$this->db->sql_freeresult($result);

						$post_info = [];

						$sql = 'SELECT forum_id, topic_id, post_id, poster_id
							FROM ' . $this->tables['posts'] . '
							WHERE ' . $this->db->sql_in_set('post_id', $upload_list);
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$post_info[(int) $row['post_id']] = $row;
						}
						$this->db->sql_freeresult($result);

						$files_added = $space_taken = 0;

						// Select those attachments we want to change...
						$sql = 'SELECT *
							FROM ' . $this->tables['attachments'] . '
							WHERE ' . $this->db->sql_in_set('attach_id', array_keys($upload_list)) . '
								AND is_orphan = 1';
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$post_row = $post_info[$upload_list[$row['attach_id']]];

							$s_denied = (bool) !$this->auth->acl_get('f_attach', (int) $post_row['forum_id']);

							$this->template->assign_block_vars('upload', [
								'FILE_INFO'		=> $this->language->lang('UPLOADING_FILE_TO', $row['real_filename'], $post_row['post_id']),
								'L_DENIED'		=> $s_denied ? $this->language->lang('UPLOAD_DENIED_FORUM', $forum_names[$row['forum_id']]) : '',
								'S_DENIED'		=> $s_denied,
							]);

							if ($s_denied)
							{
								continue;
							}

							// Adjust attachment entry
							$sql_ary = [
								'in_message'	=> 0,
								'is_orphan'		=> 0,
								'poster_id'		=> (int) $post_row['poster_id'],
								'post_msg_id'	=> (int) $post_row['post_id'],
								'topic_id'		=> (int) $post_row['topic_id'],
							];

							$sql = 'UPDATE ' . $this->tables['attachments'] . '
								SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE attach_id = ' . (int) $row['attach_id'];
							$this->db->sql_query($sql);

							$sql = 'UPDATE ' . $this->tables['posts'] . '
								SET post_attachment = 1
								WHERE post_id = ' . (int) $post_row['post_id'];
							$this->db->sql_query($sql);

							$sql = 'UPDATE ' . $this->tables['topics'] . '
								SET topic_attachment = 1
								WHERE topic_id = ' . (int) $post_row['topic_id'];
							$this->db->sql_query($sql);

							$space_taken += $row['filesize'];
							$files_added++;

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACH_FILEUPLOAD', false, [$post_row['post_id'], $row['real_filename']]);
						}
						$this->db->sql_freeresult($result);

						if ($files_added)
						{
							$this->config->increment('upload_dir_size', $space_taken, false);
							$this->config->increment('num_files', $files_added, false);
						}
					}
				}

				$this->template->assign_var('S_ORPHAN', true);

				$attachments_per_page = (int) $this->config['topics_per_page'];

				// Get total number or orphans older than 3 hours
				$sql = 'SELECT COUNT(attach_id) as num_files, SUM(filesize) as total_size
					FROM ' . $this->tables['attachments'] . '
					WHERE is_orphan = 1
						AND filetime < ' . (time() - (3 * 60 * 60));
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$num_files = (int) $row['num_files'];
				$total_size = (int) $row['total_size'];

				$start = ($page - 1) * $attachments_per_page;
				$start = $this->pagination->validate_start($start, $attachments_per_page, $num_files);

				// Just get the files with is_orphan set and older than 3 hours
				$sql = 'SELECT *
					FROM ' . $this->tables['attachments'] . '
					WHERE is_orphan = 1
						AND filetime < ' . (time() - (3 * 60 * 60)) . '
					ORDER BY filetime DESC';
				$result = $this->db->sql_query_limit($sql, $attachments_per_page, $start);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('orphan', [
						'ATTACH_ID'			=> (int) $row['attach_id'],
						'FILESIZE'			=> get_formatted_filesize($row['filesize']),
						'FILETIME'			=> $this->user->format_date($row['filetime']),
						'PHYSICAL_FILENAME'	=> utf8_basename($row['physical_filename']),
						'REAL_FILENAME'		=> utf8_basename($row['real_filename']),
						'POST_IDS'			=> !empty($post_ids[$row['attach_id']]) ? $post_ids[$row['attach_id']] : '',

						'U_FILE'			=> append_sid($this->root_path . 'download/file.' . $this->php_ext, 'mode=view&amp;id=' . $row['attach_id']),
					]);
				}
				$this->db->sql_freeresult($result);

				$this->pagination->generate_template_pagination([
					'routes' => ['acp_attachments_orphaned', 'acp_attachments_orphaned_pagination'],
				], 'pagination', 'page', $num_files, $attachments_per_page, $start);

				$this->template->assign_vars([
					'TOTAL_FILES'		=> $num_files,
					'TOTAL_SIZE'		=> get_formatted_filesize($total_size),
				]);
			break;

			case 'manage':
				if ($submit)
				{
					$delete_files = $this->request->is_set_post('delete') ? array_keys($this->request->variable('delete', ['' => 0])) : [];

					if (!empty($delete_files))
					{
						// Select those attachments we want to delete...
						$sql = 'SELECT real_filename
							FROM ' . $this->tables['attachments'] . '
							WHERE ' . $this->db->sql_in_set('attach_id', $delete_files) . '
								AND is_orphan = 0';
						$result = $this->db->sql_query($sql);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$deleted_filenames[] = $row['real_filename'];
						}
						$this->db->sql_freeresult($result);

						if ($num_deleted = $this->attachment_manager->delete('attach', $delete_files))
						{
							if (count($delete_files) != $num_deleted)
							{
								$errors[] = $this->language->lang('FILES_GONE');
							}

							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ATTACHMENTS_DELETED', false, [implode(', ', $deleted_filenames)]);
							$notify[] = $this->language->lang('LOG_ATTACHMENTS_DELETED', implode($this->language->lang('COMMA_SEPARATOR'), $deleted_filenames));
						}
						else
						{
							$errors[] = $this->language->lang('NO_FILES_TO_DELETE');
						}
					}
				}

				if ($action === 'stats')
				{
					$this->handle_stats_resync();
				}

				$stats_error = $this->check_stats_accuracy();

				if ($stats_error)
				{
					$errors[] = $stats_error;
				}

				$this->template->assign_var('S_MANAGE', true);

				// Sort keys
				$sort_days	= $this->request->variable('st', 0);
				$sort_key	= $this->request->variable('sk', 't');
				$sort_dir	= $this->request->variable('sd', 'd');

				// Sorting
				$limit_days = [0 => $this->language->lang('ALL_ENTRIES'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];
				$sort_by_text = ['f' => $this->language->lang('FILENAME'), 't' => $this->language->lang('FILEDATE'), 's' => $this->language->lang('FILESIZE'), 'x' => $this->language->lang('EXTENSION'), 'd' => $this->language->lang('DOWNLOADS'), 'p' => $this->language->lang('ATTACH_POST_TYPE'), 'u' => $this->language->lang('AUTHOR')];
				$sort_by_sql = ['f' => 'a.real_filename', 't' => 'a.filetime', 's' => 'a.filesize', 'x' => 'a.extension', 'd' => 'a.download_count', 'p' => 'a.in_message', 'u' => 'u.username'];

				$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

				$min_filetime = $sort_days ? (time() - ($sort_days * 86400)) : '';
				$limit_filetime = $min_filetime ? " AND a.filetime >= $min_filetime " : '';

				$attachments_per_page = (int) $this->config['topics_per_page'];

				$start = ($page - 1) * $attachments_per_page;
				$start = $sort_days && $this->request->is_set_post('sort') ? 0 : $start;

				$stats = $this->get_attachment_stats($limit_filetime);
				$num_files = $stats['num_files'];
				$total_size = $stats['upload_dir_size'];

				// Make sure $start is set to the last page if it exceeds the amount
				$start = $this->pagination->validate_start($start, $attachments_per_page, $num_files);

				// If the user is trying to reach the second half of the attachments list, fetch it starting from the end
				$store_reverse = false;
				$sql_limit = $attachments_per_page;

				if ($start > $num_files / 2)
				{
					$store_reverse = true;

					// Select the sort order. Add time sort anchor for non-time sorting cases
					$sql_sort_anchor = $sort_key !== 't' ? ', a.filetime ' . ($sort_dir === 'd' ? 'ASC' : 'DESC') : '';
					$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . ($sort_dir === 'd' ? 'ASC' : 'DESC') . $sql_sort_anchor;
					$sql_limit = $this->pagination->reverse_limit($start, $sql_limit, $num_files);
					$sql_start = $this->pagination->reverse_start($start, $sql_limit, $num_files);
				}
				else
				{
					// Select the sort order. Add time sort anchor for non-time sorting cases
					$sql_sort_anchor = $sort_key !== 't' ? ', a.filetime ' . ($sort_dir === 'd' ? 'DESC' : 'ASC') : '';
					$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . ($sort_dir === 'd' ? 'DESC' : 'ASC') . $sql_sort_anchor;
					$sql_start = $start;
				}

				$attachments_list = [];

				// Just get the files
				$sql = 'SELECT a.*, u.username, u.user_colour, t.topic_title
					FROM ' . $this->tables['attachments'] . ' a
					LEFT JOIN ' . $this->tables['users'] . ' u
						ON (u.user_id = a.poster_id)
					LEFT JOIN ' . $this->tables['topics'] . " t
						ON (a.topic_id = t.topic_id)
					WHERE a.is_orphan = 0
						$limit_filetime
					ORDER BY $sql_sort_order";
				$result = $this->db->sql_query_limit($sql, $sql_limit, $sql_start);

				$i = $store_reverse ? $sql_limit - 1 : 0;

				// Store increment value in a variable to save some conditional calls
				$i_increment = $store_reverse ? -1 : 1;
				while ($attachment_row = $this->db->sql_fetchrow($result))
				{
					$attachments_list[$i] = $attachment_row;
					$i = $i + $i_increment;
				}
				$this->db->sql_freeresult($result);

				parse_str($u_sort_param, $pagination_sort_params);
				$this->pagination->generate_template_pagination([
					'routes' => ['acp_attachments_manage', 'acp_attachments_manage_pagination'],
					'params' => $pagination_sort_params,
				], 'pagination', 'page', $num_files, $attachments_per_page, $start);

				$this->template->assign_vars([
					'TOTAL_FILES'		=> $num_files,
					'TOTAL_SIZE'		=> get_formatted_filesize($total_size),

					'S_LIMIT_DAYS'		=> $s_limit_days,
					'S_SORT_KEY'		=> $s_sort_key,
					'S_SORT_DIR'		=> $s_sort_dir,
				]);

				// Grab extensions
				$extensions = $this->cache_service->obtain_attach_extensions(true);

				for ($i = 0, $end = count($attachments_list); $i < $end; ++$i)
				{
					$row = $attachments_list[$i];

					$row['extension'] = strtolower(trim((string) $row['extension']));
					$comment = $row['attach_comment'] && !$row['in_message'] ? str_replace(["\n", "\r"], ['<br />', "\n"], $row['attach_comment']) : '';
					$display_cat = isset($extensions[$row['extension']]['display_cat']) ? $extensions[$row['extension']]['display_cat'] : ATTACHMENT_CATEGORY_NONE;
					$l_downloaded_viewed = $display_cat == ATTACHMENT_CATEGORY_NONE ? 'DOWNLOAD_COUNTS' : 'VIEWED_COUNTS';

					$this->template->assign_block_vars('attachments', [
						'ATTACH_ID'			=> (int) $row['attach_id'],
						'ATTACHMENT_POSTER'	=> get_username_string('full', (int) $row['poster_id'], (string) $row['username'], (string) $row['user_colour'], (string) $row['username']),
						'COMMENT'			=> $comment,
						'EXT_GROUP_NAME'	=> !empty($extensions[$row['extension']]['group_name']) ? $this->get_extension_group_name($extensions[$row['extension']]['group_name']) : '',
						'FILESIZE'			=> get_formatted_filesize((int) $row['filesize']),
						'FILETIME'			=> $this->user->format_date((int) $row['filetime']),
						'REAL_FILENAME'		=> utf8_basename((string) $row['real_filename']),
						'TOPIC_TITLE'		=> !$row['in_message'] ? (string) $row['topic_title'] : '',

						'L_DOWNLOAD_COUNT'	=> $this->language->lang($l_downloaded_viewed, (int) $row['download_count']),

						'S_IN_MESSAGE'		=> (bool) $row['in_message'],

						'U_VIEW_TOPIC'		=> append_sid("{$this->root_path}viewtopic.$this->php_ext", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . "#p{$row['post_msg_id']}",
						'U_FILE'			=> append_sid($this->root_path . 'download/file.' . $this->php_ext, 'mode=view&amp;id=' . $row['attach_id']),
					]);
				}
			break;
		}

		if (!empty($errors))
		{
			$this->template->assign_vars([
				'S_WARNING'		=> true,
				'WARNING_MSG'	=> implode('<br />', $errors),
			]);
		}

		if (!empty($notify))
		{
			$this->template->assign_vars([
				'S_NOTIFY'		=> true,
				'NOTIFY_MSG'	=> implode('<br />', $notify),
			]);
		}

		return $this->helper->render('acp_attachments.html', $this->language->lang($l_title));
	}

	/**
	 * Get attachment file count and size of upload directory.
	 *
	 * @param string	$limit	Additional limit for WHERE clause to filter stats by
	 * @return array			Returns array with stats: num_files and upload_dir_size
	 */
	public function get_attachment_stats($limit = '')
	{
		$sql = 'SELECT COUNT(a.attach_id) AS num_files, SUM(a.filesize) AS upload_dir_size
			FROM ' . $this->tables['attachments'] . ' a
			WHERE a.is_orphan = 0' . $limit;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return [
			'num_files'			=> (int) $row['num_files'],
			'upload_dir_size'	=> (float) $row['upload_dir_size'],
		];
	}

	/**
	 * Set config attachment stat values.
	 *
	 * @param array		$stats	Array of config key => value pairs to set.
	 * @return void
	 */
	public function set_attachment_stats(array $stats)
	{
		foreach ($stats as $key => $value)
		{
			$this->config->set($key, $value, true);
		}
	}

	/**
	 * Check accuracy of attachment statistics.
	 *
	 * @return bool|string		false on success,
	 * 							string on failure with the error message
	 */
	public function check_stats_accuracy()
	{
		// Get fresh stats.
		$stats = $this->get_attachment_stats();

		// Get current files stats
		$num_files = (int) $this->config['num_files'];
		$total_size = (float) $this->config['upload_dir_size'];

		if (($num_files != $stats['num_files']) || ($total_size != $stats['upload_dir_size']))
		{
			$u_resync = $this->helper->route('acp_attachments_manage', ['action' => 'stats']);

			return $this->language->lang(
				'FILES_STATS_WRONG',
				(int) $stats['num_files'],
				get_formatted_filesize($stats['upload_dir_size']),
				'<a href="' . $u_resync . '">',
				'</a>'
			);
		}

		return false;
	}

	/**
	 * Handle stats resync.
	 *
	 * @return void
	 */
	public function handle_stats_resync()
	{
		if (confirm_box(true))
		{
			$this->set_attachment_stats($this->get_attachment_stats());

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESYNC_FILES_STATS');
		}
		else
		{
			confirm_box(false, $this->language->lang('RESYNC_FILES_STATS_CONFIRM'), build_hidden_fields([
				'mode'		=> 'manage',
				'action'	=> 'stats',
			]));

			redirect($this->helper->route('acp_settings_manage'));
		}
	}

	/**
	 * Build <select> for category items.
	 *
	 * @html
	 *
	 * @param string	$select_name	The <select> name attribute
	 * @param int|false	$group_id		The selected group identifier
	 * @param string	$key			The <select> id attribute
	 * @return string					The <select> HTML element
	 */
	protected function category_select($select_name, $group_id = false, $key = '')
	{
		$types = [
			ATTACHMENT_CATEGORY_NONE	=> $this->language->lang('NO_FILE_CAT'),
			ATTACHMENT_CATEGORY_IMAGE	=> $this->language->lang('CAT_IMAGES'),
		];

		if ($group_id)
		{
			$sql = 'SELECT cat_id
				FROM ' . $this->tables['extension_groups'] . '
				WHERE group_id = ' . (int) $group_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$cat_type = $row !== false ? (int) $row['cat_id'] : ATTACHMENT_CATEGORY_NONE;
		}
		else
		{
			$cat_type = ATTACHMENT_CATEGORY_NONE;
		}

		$group_select = '<select name="' . $select_name . '"' . ($key ? ' id="' . $key . '"' : '') . '>';

		foreach ($types as $type => $mode)
		{
			$selected = ($type == $cat_type) ? ' selected="selected"' : '';
			$group_select .= '<option value="' . $type . '"' . $selected . '>' . $mode . '</option>';
		}

		$group_select .= '</select>';

		return $group_select;
	}

	/**
	 * Build <select> for extension group items.
	 *
	 * @html
	 *
	 * @param string	$select_name		The <select> name attribute
	 * @param int|bool	$default_group		The selected group identifier
	 * @param string	$key				The <select> id attribute
	 * @return string						The <select> HTML element
	 */
	protected function group_select($select_name, $default_group = false, $key = '')
	{
		$group_select = '<select name="' . $select_name . '"' . ($key ? ' id="' . $key . '"' : '') . '>';

		$group_name = [];

		$sql = 'SELECT group_id, group_name
			FROM ' . $this->tables['extension_groups'] . '
			ORDER BY group_name';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_name[] = [
				'group_id'		=> (int) $row['group_id'],
				'group_name'	=> $this->get_extension_group_name($row['group_name']),
			];
		}
		$this->db->sql_freeresult($result);

		$group_name[] = [
			'group_id'		=> 0,
			'group_name'	=> $this->language->lang('NOT_ASSIGNED'),
		];

		for ($i = 0, $groups_size = count($group_name); $i < $groups_size; $i++)
		{
			if ($default_group === false)
			{
				$selected = $i === 0 ? ' selected="selected"' : '';
			}
			else
			{
				$selected = $group_name[$i]['group_id'] == $default_group ? ' selected="selected"' : '';
			}

			$group_select .= '<option value="' . $group_name[$i]['group_id'] . '"' . $selected . '>' . $group_name[$i]['group_name'] . '</option>';
		}

		$group_select .= '</select>';

		return $group_select;
	}

	/**
	 * Perform operations on sites for external linking.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function perform_site_list()
	{
		if ($this->request->is_set('securesubmit'))
		{
			// Grab the list of entries
			$ips = $this->request->variable('ips', '');
			$ip_list = array_unique(explode("\n", $ips));
			$ip_list_log = implode(', ', $ip_list);

			$ip_exclude = (int) $this->request->variable('ipexclude', false, false, \phpbb\request\request_interface::POST);

			$ip_list = [];
			$host_list = [];

			foreach ($ip_list as $item)
			{
				if (preg_match('#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#', trim($item), $ip_range_explode))
				{
					// Don't ask about all this, just don't ask ... !
					$ip_1_counter = $ip_range_explode[1];
					$ip_1_end = $ip_range_explode[5];

					while ($ip_1_counter <= $ip_1_end)
					{
						$ip_2_counter = ($ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[2] : 0;
						$ip_2_end = ($ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[6];

						if ($ip_2_counter == 0 && $ip_2_end == 254)
						{
							$ip_2_counter = 256;

							$ip_list[] = "'$ip_1_counter.*'";
						}

						while ($ip_2_counter <= $ip_2_end)
						{
							$ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
							$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

							if ($ip_3_counter == 0 && $ip_3_end == 254)
							{
								$ip_3_counter = 256;

								$ip_list[] = "'$ip_1_counter.$ip_2_counter.*'";
							}

							while ($ip_3_counter <= $ip_3_end)
							{
								$ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
								$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

								if ($ip_4_counter == 0 && $ip_4_end == 254)
								{
									$ip_4_counter = 256;

									$ip_list[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.*'";
								}

								while ($ip_4_counter <= $ip_4_end)
								{
									$ip_list[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter'";
									$ip_4_counter++;
								}
								$ip_3_counter++;
							}
							$ip_2_counter++;
						}
						$ip_1_counter++;
					}
				}
				else if (preg_match('#^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$#', trim($item)) || preg_match('#^[a-f0-9:]+\*?$#i', trim($item)))
				{
					$ip_list[] = "'" . trim($item) . "'";
				}
				else if (preg_match('#^([\w\-_]\.?){2,}$#is', trim($item)))
				{
					$host_list[] = "'" . trim($item) . "'";
				}
				else if (preg_match("#^([a-z0-9\-\*\._/]+?)$#is", trim($item)))
				{
					$host_list[] = "'" . trim($item) . "'";
				}
			}

			$sql = 'SELECT site_ip, site_hostname
				FROM ' . $this->tables['sitelist'] . "
				WHERE ip_exclude = $ip_exclude";
			$result = $this->db->sql_query($sql);

			if ($row = $this->db->sql_fetchrow($result))
			{
				$ip_list_tmp = [];
				$host_list_tmp = [];

				do
				{
					if ($row['site_ip'])
					{
						if (strlen($row['site_ip']) > 40)
						{
							continue;
						}

						$ip_list_tmp[] = "'" . $row['site_ip'] . "'";
					}
					else if ($row['site_hostname'])
					{
						if (strlen($row['site_hostname']) > 255)
						{
							continue;
						}

						$host_list_tmp[] = "'" . $row['site_hostname'] . "'";
					}
					// break;
				}
				while ($row = $this->db->sql_fetchrow($result));

				$ip_list = array_unique(array_diff($ip_list, $ip_list_tmp));
				$host_list = array_unique(array_diff($host_list, $host_list_tmp));

				unset($ip_list_tmp);
				unset($host_list_tmp);
			}
			$this->db->sql_freeresult($result);

			if (!empty($ip_list))
			{
				foreach ($ip_list as $ip_entry)
				{
					$sql = 'INSERT INTO ' . $this->tables['sitelist'] . $this->db->sql_build_array('INSERT', [
						'site_ip'		=> $ip_entry,
						'ip_exclude'	=> $ip_exclude,
					]);
					$this->db->sql_query($sql);
				}
			}

			if (!empty($host_list))
			{
				foreach ($host_list as $host_entry)
				{
					$sql = 'INSERT INTO ' . $this->tables['sitelist'] . $this->db->sql_build_array('INSERT', [
						'site_hostname'	=> $host_entry,
						'ip_exclude'	=> $ip_exclude,
					]);
					$this->db->sql_query($sql);
				}
			}

			if (!empty($ip_list_log))
			{
				// Update log
				$log_entry = $ip_exclude ? 'LOG_DOWNLOAD_EXCLUDE_IP' : 'LOG_DOWNLOAD_IP';
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_entry, false, [$ip_list_log]);
			}

			return $this->helper->message_back('SECURE_DOWNLOAD_UPDATE_SUCCESS', 'acp_settings_attachment');
		}
		else // if ($this->request->is_set_post('unsecuresubmit'))
		{
			$unip_sql = $this->request->variable('unip', [0]);

			if (!empty($unip_sql))
			{
				$l_unip_list = '';

				// Grab details of ips for logging information later
				$sql = 'SELECT site_ip, site_hostname
					FROM ' . $this->tables['sitelist'] . '
					WHERE ' . $this->db->sql_in_set('site_id', $unip_sql);
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$l_unip_list .= (($l_unip_list != '') ? ', ' : '') . (($row['site_ip']) ? $row['site_ip'] : $row['site_hostname']);
				}
				$this->db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . $this->tables['sitelist'] . '
					WHERE ' . $this->db->sql_in_set('site_id', $unip_sql);
				$this->db->sql_query($sql);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DOWNLOAD_REMOVE_IP', false, [$l_unip_list]);
			}

			return $this->helper->message_back('SECURE_DOWNLOAD_UPDATE_SUCCESS', 'acp_settings_attachment');
		}
	}

	/**
	 * Write display_order config field.
	 *
	 * @html
	 *
	 * @param int		$value		The config value
	 * @param string	$key		The config name (used as the element's identifier)
	 * @return string				The <label> and <input> elements
	 */
	public function display_order($value, $key = '')
	{
		return h_radio('config[display_order]', [0 => 'DESCENDING', 1 => 'ASCENDING'], $value, $key);
	}

	/**
	 * Adjust all three max_filesize config vars for display.
	 *
	 * @html
	 *
	 * @param int		$value		The config value
	 * @param string	$key		The config name (used as the element's identifier)
	 * @return string				The <input> and <select> elements
	 */
	public function max_filesize($value, $key = '')
	{
		// Determine size var and adjust the value accordingly
		$filesize = get_formatted_filesize($value, false, ['mb', 'kb', 'b']);
		$size_var = $filesize['si_identifier'];
		$value = $filesize['value'];

		// size and maxlength must not be specified for input of type number
		return '<input type="number" id="' . $key . '" min="0" max="999999999999999" step="any" name="config[' . $key . ']" value="' . $value . '" /> <select name="' . $key . '">' . size_select_options($size_var) . '</select>';
	}

	/**
	 * Write secure_allow_deny config field.
	 *
	 * @html
	 *
	 * @param int		$value		The config value
	 * @param string	$key		The config name (used as the element's identifier)
	 * @return string				The <label> and <input> elements
	 */
	public function select_allow_deny($value, $key = '')
	{
		$radio_ary = [1 => 'ORDER_ALLOW_DENY', 0 => 'ORDER_DENY_ALLOW'];

		return h_radio('config[' . $key . ']', $radio_ary, $value, $key);
	}

	/**
	 * Try and get a localised extension group name.
	 *
	 * @param string	$group_name	The extension group name
	 * @return string				The possibly localised extension group name
	 */
	public function get_extension_group_name($group_name)
	{
		$extension_group_name = 'EXT_GROUP_' . $group_name;

		return $this->language->is_set($extension_group_name) ? $this->language->lang($extension_group_name) : $group_name;
	}
}
