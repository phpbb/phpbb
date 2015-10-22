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

class acp_attachments
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var ContainerBuilder */
	protected $phpbb_container;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	public $id;
	public $u_action;
	protected $new_config;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $phpbb_container;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$this->id = $id;
		$this->db = $db;
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_container = $phpbb_container;

		$user->add_lang(array('posting', 'viewtopic', 'acp/attachments'));

		$error = $notify = array();
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');

		$form_key = 'acp_attach';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($mode)
		{
			case 'attach':
				$l_title = 'ACP_ATTACHMENT_SETTINGS';
			break;

			case 'extensions':
				$l_title = 'ACP_MANAGE_EXTENSIONS';
			break;

			case 'ext_groups':
				$l_title = 'ACP_EXTENSION_GROUPS';
			break;

			case 'orphan':
				$l_title = 'ACP_ORPHAN_ATTACHMENTS';
			break;

			case 'manage':
				$l_title = 'ACP_MANAGE_ATTACHMENTS';
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		$this->tpl_name = 'acp_attachments';
		$this->page_title = $l_title;

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$l_title],
			'L_TITLE_EXPLAIN'	=> $user->lang[$l_title . '_EXPLAIN'],
			'U_ACTION'			=> $this->u_action)
		);

		switch ($mode)
		{
			case 'attach':

				include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

				$sql = 'SELECT group_name, cat_id
					FROM ' . EXTENSION_GROUPS_TABLE . '
					WHERE cat_id > 0
					ORDER BY cat_id';
				$result = $db->sql_query($sql);

				$s_assigned_groups = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$row['group_name'] = (isset($user->lang['EXT_GROUP_' . $row['group_name']])) ? $user->lang['EXT_GROUP_' . $row['group_name']] : $row['group_name'];
					$s_assigned_groups[$row['cat_id']][] = $row['group_name'];
				}
				$db->sql_freeresult($result);

				$l_legend_cat_images = $user->lang['SETTINGS_CAT_IMAGES'] . ' [' . $user->lang['ASSIGNED_GROUP'] . ': ' . ((!empty($s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE])) ? implode($user->lang['COMMA_SEPARATOR'], $s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE]) : $user->lang['NO_EXT_GROUP']) . ']';

				$display_vars = array(
					'title'	=> 'ACP_ATTACHMENT_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_ATTACHMENT_SETTINGS',

						'img_max_width'			=> array('lang' => 'MAX_IMAGE_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),
						'img_max_height'		=> array('lang' => 'MAX_IMAGE_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),
						'img_link_width'		=> array('lang' => 'IMAGE_LINK_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),
						'img_link_height'		=> array('lang' => 'IMAGE_LINK_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false,),

						'allow_attachments'		=> array('lang' => 'ALLOW_ATTACHMENTS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_attach'		=> array('lang' => 'ALLOW_PM_ATTACHMENTS',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'upload_path'			=> array('lang' => 'UPLOAD_DIR',			'validate' => 'wpath',	'type' => 'text:25:100', 'explain' => true),
						'display_order'			=> array('lang' => 'DISPLAY_ORDER',			'validate' => 'bool',	'type' => 'custom', 'method' => 'display_order', 'explain' => true),
						'attachment_quota'		=> array('lang' => 'ATTACH_QUOTA',			'validate' => 'string',	'type' => 'custom', 'method' => 'max_filesize', 'explain' => true),
						'max_filesize'			=> array('lang' => 'ATTACH_MAX_FILESIZE',	'validate' => 'string',	'type' => 'custom', 'method' => 'max_filesize', 'explain' => true),
						'max_filesize_pm'		=> array('lang' => 'ATTACH_MAX_PM_FILESIZE','validate' => 'string',	'type' => 'custom', 'method' => 'max_filesize', 'explain' => true),
						'max_attachments'		=> array('lang' => 'MAX_ATTACHMENTS',		'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => false),
						'max_attachments_pm'	=> array('lang' => 'MAX_ATTACHMENTS_PM',	'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => false),
						'secure_downloads'		=> array('lang' => 'SECURE_DOWNLOADS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'secure_allow_deny'		=> array('lang' => 'SECURE_ALLOW_DENY',		'validate' => 'int',	'type' => 'custom', 'method' => 'select_allow_deny', 'explain' => true),
						'secure_allow_empty_referer'	=> array('lang' => 'SECURE_EMPTY_REFERRER', 'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'check_attachment_content' 		=> array('lang' => 'CHECK_CONTENT', 'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend2'					=> $l_legend_cat_images,
						'img_display_inlined'		=> array('lang' => 'DISPLAY_INLINED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'img_create_thumbnail'		=> array('lang' => 'CREATE_THUMBNAIL',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'img_max_thumb_width'		=> array('lang' => 'MAX_THUMB_WIDTH',		'validate' => 'int:0:999999999999999',	'type' => 'number:0:999999999999999', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
						'img_min_thumb_filesize'	=> array('lang' => 'MIN_THUMB_FILESIZE',	'validate' => 'int:0:999999999999999',	'type' => 'number:0:999999999999999', 'explain' => true, 'append' => ' ' . $user->lang['BYTES']),
						'img_imagick'				=> array('lang' => 'IMAGICK_PATH',			'validate' => 'path',	'type' => 'text:20:200', 'explain' => true, 'append' => '&nbsp;&nbsp;<span>[ <a href="' . $this->u_action . '&amp;action=imgmagick">' . $user->lang['SEARCH_IMAGICK'] . '</a> ]</span>'),
						'img_max'					=> array('lang' => 'MAX_IMAGE_SIZE',		'validate' => 'int:0:9999',	'type' => 'dimension:0:9999', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
						'img_link'					=> array('lang' => 'IMAGE_LINK_SIZE',		'validate' => 'int:0:9999',	'type' => 'dimension:0:9999', 'explain' => true, 'append' => ' ' . $user->lang['PIXEL']),
					)
				);

				$this->new_config = $config;
				$cfg_array = (isset($_REQUEST['config'])) ? request_var('config', array('' => '')) : $this->new_config;
				$error = array();

				// We validate the complete config if whished
				validate_config_vars($display_vars['vars'], $cfg_array, $error);

				// Do not write values if there is an error
				if (sizeof($error))
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

					if (in_array($config_name, array('attachment_quota', 'max_filesize', 'max_filesize_pm')))
					{
						$size_var = request_var($config_name, '');
						$this->new_config[$config_name] = $config_value = ($size_var == 'kb') ? round($config_value * 1024) : (($size_var == 'mb') ? round($config_value * 1048576) : $config_value);
					}

					if ($submit)
					{
						set_config($config_name, $config_value);
					}
				}

				$this->perform_site_list();

				if ($submit)
				{
					add_log('admin', 'LOG_CONFIG_ATTACH');

					// Check Settings
					$this->test_upload($error, $this->new_config['upload_path'], false);

					if (!sizeof($error))
					{
						trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
					}
				}

				$template->assign_var('S_ATTACHMENT_SETTINGS', true);

				if ($action == 'imgmagick')
				{
					$this->new_config['img_imagick'] = $this->search_imagemagick();
				}

				// We strip eventually manual added convert program, we only want the patch
				if ($this->new_config['img_imagick'])
				{
					// Change path separator
					$this->new_config['img_imagick'] = str_replace('\\', '/', $this->new_config['img_imagick']);
					$this->new_config['img_imagick'] = str_replace(array('convert', '.exe'), array('', ''), $this->new_config['img_imagick']);

					// Check for trailing slash
					if (substr($this->new_config['img_imagick'], -1) !== '/')
					{
						$this->new_config['img_imagick'] .= '/';
					}
				}

				$supported_types = get_supported_image_types();

				// Check Thumbnail Support
				if (!$this->new_config['img_imagick'] && (!isset($supported_types['format']) || !sizeof($supported_types['format'])))
				{
					$this->new_config['img_create_thumbnail'] = 0;
				}

				$template->assign_vars(array(
					'U_SEARCH_IMAGICK'		=> $this->u_action . '&amp;action=imgmagick',
					'S_THUMBNAIL_SUPPORT'	=> (!$this->new_config['img_imagick'] && (!isset($supported_types['format']) || !sizeof($supported_types['format']))) ? false : true)
				);

				// Secure Download Options - Same procedure as with banning
				$allow_deny = ($this->new_config['secure_allow_deny']) ? 'ALLOWED' : 'DISALLOWED';

				$sql = 'SELECT *
					FROM ' . SITELIST_TABLE;
				$result = $db->sql_query($sql);

				$defined_ips = '';
				$ips = array();

				while ($row = $db->sql_fetchrow($result))
				{
					$value = ($row['site_ip']) ? $row['site_ip'] : $row['site_hostname'];
					if ($value)
					{
						$defined_ips .= '<option' . (($row['ip_exclude']) ? ' class="sep"' : '') . ' value="' . $row['site_id'] . '">' . $value . '</option>';
						$ips[$row['site_id']] = $value;
					}
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_SECURE_DOWNLOADS'	=> $this->new_config['secure_downloads'],
					'S_DEFINED_IPS'			=> ($defined_ips != '') ? true : false,
					'S_WARNING'				=> (sizeof($error)) ? true : false,

					'WARNING_MSG'			=> implode('<br />', $error),
					'DEFINED_IPS'			=> $defined_ips,

					'L_SECURE_TITLE'		=> $user->lang['DEFINE_' . $allow_deny . '_IPS'],
					'L_IP_EXCLUDE'			=> $user->lang['EXCLUDE_FROM_' . $allow_deny . '_IP'],
					'L_REMOVE_IPS'			=> $user->lang['REMOVE_' . $allow_deny . '_IPS'])
				);

				// Output relevant options
				foreach ($display_vars['vars'] as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}

					if (strpos($config_key, 'legend') !== false)
					{
						$template->assign_block_vars('options', array(
							'S_LEGEND'		=> true,
							'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
						);

						continue;
					}

					$type = explode(':', $vars['type']);

					$l_explain = '';
					if ($vars['explain'] && isset($vars['lang_explain']))
					{
						$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
					}
					else if ($vars['explain'])
					{
						$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
					}

					$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);
					if (empty($content))
					{
						continue;
					}

					$template->assign_block_vars('options', array(
						'KEY'			=> $config_key,
						'TITLE'			=> $user->lang[$vars['lang']],
						'S_EXPLAIN'		=> $vars['explain'],
						'TITLE_EXPLAIN'	=> $l_explain,
						'CONTENT'		=> $content,
						)
					);

					unset($display_vars['vars'][$config_key]);
				}

			break;

			case 'extensions':

				if ($submit || isset($_POST['add_extension_check']))
				{
					if ($submit)
					{
						// Change Extensions ?
						$extension_change_list	= request_var('extension_change_list', array(0));
						$group_select_list		= request_var('group_select', array(0));

						// Generate correct Change List
						$extensions = array();

						for ($i = 0, $size = sizeof($extension_change_list); $i < $size; $i++)
						{
							$extensions[$extension_change_list[$i]]['group_id'] = $group_select_list[$i];
						}

						$sql = 'SELECT *
							FROM ' . EXTENSIONS_TABLE . '
							ORDER BY extension_id';
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['group_id'] != $extensions[$row['extension_id']]['group_id'])
							{
								$sql = 'UPDATE ' . EXTENSIONS_TABLE . '
									SET group_id = ' . (int) $extensions[$row['extension_id']]['group_id'] . '
									WHERE extension_id = ' . $row['extension_id'];
								$db->sql_query($sql);

								add_log('admin', 'LOG_ATTACH_EXT_UPDATE', $row['extension']);
							}
						}
						$db->sql_freeresult($result);

						// Delete Extension?
						$extension_id_list = request_var('extension_id_list', array(0));

						if (sizeof($extension_id_list))
						{
							$sql = 'SELECT extension
								FROM ' . EXTENSIONS_TABLE . '
								WHERE ' . $db->sql_in_set('extension_id', $extension_id_list);
							$result = $db->sql_query($sql);

							$extension_list = '';
							while ($row = $db->sql_fetchrow($result))
							{
								$extension_list .= ($extension_list == '') ? $row['extension'] : ', ' . $row['extension'];
							}
							$db->sql_freeresult($result);

							$sql = 'DELETE
								FROM ' . EXTENSIONS_TABLE . '
								WHERE ' . $db->sql_in_set('extension_id', $extension_id_list);
							$db->sql_query($sql);

							add_log('admin', 'LOG_ATTACH_EXT_DEL', $extension_list);
						}
					}

					// Add Extension?
					$add_extension			= strtolower(request_var('add_extension', ''));
					$add_extension_group	= request_var('add_group_select', 0);
					$add					= (isset($_POST['add_extension_check'])) ? true : false;

					if ($add_extension && $add)
					{
						if (!sizeof($error))
						{
							$sql = 'SELECT extension_id
								FROM ' . EXTENSIONS_TABLE . "
								WHERE extension = '" . $db->sql_escape($add_extension) . "'";
							$result = $db->sql_query($sql);

							if ($row = $db->sql_fetchrow($result))
							{
								$error[] = sprintf($user->lang['EXTENSION_EXIST'], $add_extension);
							}
							$db->sql_freeresult($result);

							if (!sizeof($error))
							{
								$sql_ary = array(
									'group_id'	=>	$add_extension_group,
									'extension'	=>	$add_extension
								);

								$db->sql_query('INSERT INTO ' . EXTENSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
								add_log('admin', 'LOG_ATTACH_EXT_ADD', $add_extension);
							}
						}
					}

					if (!sizeof($error))
					{
						$notify[] = $user->lang['EXTENSIONS_UPDATED'];
					}

					$cache->destroy('_extensions');
				}

				$template->assign_vars(array(
					'S_EXTENSIONS'			=> true,
					'ADD_EXTENSION'			=> (isset($add_extension)) ? $add_extension : '',
					'GROUP_SELECT_OPTIONS'	=> (isset($_POST['add_extension_check'])) ? $this->group_select('add_group_select', $add_extension_group, 'extension_group') : $this->group_select('add_group_select', false, 'extension_group'))
				);

				$sql = 'SELECT *
					FROM ' . EXTENSIONS_TABLE . '
					ORDER BY group_id, extension';
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
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

						$template->assign_block_vars('extensions', array(
							'S_SPACER'		=> $s_spacer,
							'EXTENSION_ID'	=> $row['extension_id'],
							'EXTENSION'		=> $row['extension'],
							'GROUP_OPTIONS'	=> $this->group_select('group_select[]', $row['group_id']))
						);
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

			break;

			case 'ext_groups':

				$template->assign_var('S_EXTENSION_GROUPS', true);

				if ($submit)
				{
					$action = request_var('action', '');
					$group_id = request_var('g', 0);

					if ($action != 'add' && $action != 'edit')
					{
						trigger_error('NO_MODE', E_USER_ERROR);
					}

					if (!$group_id && $action == 'edit')
					{
						trigger_error($user->lang['NO_EXT_GROUP_SPECIFIED'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if ($group_id)
					{
						$sql = 'SELECT *
							FROM ' . EXTENSION_GROUPS_TABLE . "
							WHERE group_id = $group_id";
						$result = $db->sql_query($sql);
						$ext_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$ext_row)
						{
							trigger_error($user->lang['NO_EXT_GROUP_SPECIFIED'] . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					else
					{
						$ext_row = array();
					}

					$group_name = utf8_normalize_nfc(request_var('group_name', '', true));
					$new_group_name = ($action == 'add') ? $group_name : (($ext_row['group_name'] != $group_name) ? $group_name : '');

					if (!$group_name)
					{
						$error[] = $user->lang['NO_EXT_GROUP_NAME'];
					}

					// Check New Group Name
					if ($new_group_name)
					{
						$sql = 'SELECT group_id
							FROM ' . EXTENSION_GROUPS_TABLE . "
							WHERE LOWER(group_name) = '" . $db->sql_escape(utf8_strtolower($new_group_name)) . "'";
						if ($group_id)
						{
							$sql .= ' AND group_id <> ' . $group_id;
						}
						$result = $db->sql_query($sql);

						if ($db->sql_fetchrow($result))
						{
							$error[] = sprintf($user->lang['EXTENSION_GROUP_EXIST'], $new_group_name);
						}
						$db->sql_freeresult($result);
					}

					if (!sizeof($error))
					{
						// Ok, build the update/insert array
						$upload_icon	= request_var('upload_icon', 'no_image');
						$size_select	= request_var('size_select', 'b');
						$forum_select	= request_var('forum_select', false);
						$allowed_forums	= request_var('allowed_forums', array(0));
						$allow_in_pm	= (isset($_POST['allow_in_pm'])) ? true : false;
						$max_filesize	= request_var('max_filesize', 0);
						$max_filesize	= ($size_select == 'kb') ? round($max_filesize * 1024) : (($size_select == 'mb') ? round($max_filesize * 1048576) : $max_filesize);
						$allow_group	= (isset($_POST['allow_group'])) ? true : false;

						if ($max_filesize == $config['max_filesize'])
						{
							$max_filesize = 0;
						}

						if (!sizeof($allowed_forums))
						{
							$forum_select = false;
						}

						$group_ary = array(
							'group_name'	=> $group_name,
							'cat_id'		=> request_var('special_category', ATTACHMENT_CATEGORY_NONE),
							'allow_group'	=> ($allow_group) ? 1 : 0,
							'upload_icon'	=> ($upload_icon == 'no_image') ? '' : $upload_icon,
							'max_filesize'	=> $max_filesize,
							'allowed_forums'=> ($forum_select) ? serialize($allowed_forums) : '',
							'allow_in_pm'	=> ($allow_in_pm) ? 1 : 0,
						);

						if ($action == 'add')
						{
							$group_ary['download_mode'] = INLINE_LINK;
						}

						$sql = ($action == 'add') ? 'INSERT INTO ' . EXTENSION_GROUPS_TABLE . ' ' : 'UPDATE ' . EXTENSION_GROUPS_TABLE . ' SET ';
						$sql .= $db->sql_build_array((($action == 'add') ? 'INSERT' : 'UPDATE'), $group_ary);
						$sql .= ($action == 'edit') ? " WHERE group_id = $group_id" : '';

						$db->sql_query($sql);

						if ($action == 'add')
						{
							$group_id = $db->sql_nextid();
						}

						$group_name = (isset($user->lang['EXT_GROUP_' . $group_name])) ? $user->lang['EXT_GROUP_' . $group_name] : $group_name;
						add_log('admin', 'LOG_ATTACH_EXTGROUP_' . strtoupper($action), $group_name);
					}

					$extension_list = request_var('extensions', array(0));

					if ($action == 'edit' && sizeof($extension_list))
					{
						$sql = 'UPDATE ' . EXTENSIONS_TABLE . "
							SET group_id = 0
							WHERE group_id = $group_id";
						$db->sql_query($sql);
					}

					if (sizeof($extension_list))
					{
						$sql = 'UPDATE ' . EXTENSIONS_TABLE . "
							SET group_id = $group_id
							WHERE " . $db->sql_in_set('extension_id', $extension_list);
						$db->sql_query($sql);
					}

					$cache->destroy('_extensions');

					if (!sizeof($error))
					{
						$notify[] = $user->lang['SUCCESS_EXTENSION_GROUP_' . strtoupper($action)];
					}
				}

				$cat_lang = array(
					ATTACHMENT_CATEGORY_NONE		=> $user->lang['NO_FILE_CAT'],
					ATTACHMENT_CATEGORY_IMAGE		=> $user->lang['CAT_IMAGES'],
					ATTACHMENT_CATEGORY_WM			=> $user->lang['CAT_WM_FILES'],
					ATTACHMENT_CATEGORY_RM			=> $user->lang['CAT_RM_FILES'],
					ATTACHMENT_CATEGORY_FLASH		=> $user->lang['CAT_FLASH_FILES'],
					ATTACHMENT_CATEGORY_QUICKTIME	=> $user->lang['CAT_QUICKTIME_FILES'],
				);

				$group_id = request_var('g', 0);
				$action = (isset($_POST['add'])) ? 'add' : $action;

				switch ($action)
				{
					case 'delete':

						if (confirm_box(true))
						{
							$sql = 'SELECT group_name
								FROM ' . EXTENSION_GROUPS_TABLE . "
								WHERE group_id = $group_id";
							$result = $db->sql_query($sql);
							$group_name = (string) $db->sql_fetchfield('group_name');
							$db->sql_freeresult($result);

							$sql = 'DELETE
								FROM ' . EXTENSION_GROUPS_TABLE . "
								WHERE group_id = $group_id";
							$db->sql_query($sql);

							// Set corresponding Extensions to a pending Group
							$sql = 'UPDATE ' . EXTENSIONS_TABLE . "
								SET group_id = 0
								WHERE group_id = $group_id";
							$db->sql_query($sql);

							add_log('admin', 'LOG_ATTACH_EXTGROUP_DEL', $group_name);

							$cache->destroy('_extensions');

							trigger_error($user->lang['EXTENSION_GROUP_DELETED'] . adm_back_link($this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'i'			=> $id,
								'mode'		=> $mode,
								'group_id'	=> $group_id,
								'action'	=> 'delete',
							)));
						}

					break;

					case 'edit':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_EXT_GROUP_SPECIFIED'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql = 'SELECT *
							FROM ' . EXTENSION_GROUPS_TABLE . "
							WHERE group_id = $group_id";
						$result = $db->sql_query($sql);
						$ext_group_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						$forum_ids = (!$ext_group_row['allowed_forums']) ? array() : unserialize(trim($ext_group_row['allowed_forums']));

					// no break;

					case 'add':

						if ($action == 'add')
						{
							$ext_group_row = array(
								'group_name'	=> utf8_normalize_nfc(request_var('group_name', '', true)),
								'cat_id'		=> 0,
								'allow_group'	=> 1,
								'allow_in_pm'	=> 1,
								'upload_icon'	=> '',
								'max_filesize'	=> 0,
							);

							$forum_ids = array();
						}

						$extensions = array();

						$sql = 'SELECT *
							FROM ' . EXTENSIONS_TABLE . "
							WHERE group_id = $group_id
								OR group_id = 0
							ORDER BY extension";
						$result = $db->sql_query($sql);
						$extensions = $db->sql_fetchrowset($result);
						$db->sql_freeresult($result);

						if ($ext_group_row['max_filesize'] == 0)
						{
							$ext_group_row['max_filesize'] = (int) $config['max_filesize'];
						}

						$max_filesize = get_formatted_filesize($ext_group_row['max_filesize'], false, array('mb', 'kb', 'b'));
						$size_format = $max_filesize['si_identifier'];
						$ext_group_row['max_filesize'] = $max_filesize['value'];

						$img_path = $config['upload_icons_path'];

						$filename_list = '';
						$no_image_select = false;

						$imglist = filelist($phpbb_root_path . $img_path);

						if (!empty($imglist['']))
						{
							$imglist = array_values($imglist);
							$imglist = $imglist[0];

							foreach ($imglist as $key => $img)
							{
								if (!$ext_group_row['upload_icon'])
								{
									$no_image_select = true;
									$selected = '';
								}
								else
								{
									$selected = ($ext_group_row['upload_icon'] == $img) ? ' selected="selected"' : '';
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
								$assigned_extensions .= ($i) ? ', ' . $row['extension'] : $row['extension'];
								$i++;
							}
						}

						$s_extension_options = '';
						foreach ($extensions as $row)
						{
							$s_extension_options .= '<option' . ((!$row['group_id']) ? ' class="disabled"' : '') . ' value="' . $row['extension_id'] . '"' . (($row['group_id'] == $group_id && $group_id) ? ' selected="selected"' : '') . '>' . $row['extension'] . '</option>';
						}

						$template->assign_vars(array(
							'IMG_PATH'				=> $img_path,
							'ACTION'				=> $action,
							'GROUP_ID'				=> $group_id,
							'GROUP_NAME'			=> $ext_group_row['group_name'],
							'ALLOW_GROUP'			=> $ext_group_row['allow_group'],
							'ALLOW_IN_PM'			=> $ext_group_row['allow_in_pm'],
							'UPLOAD_ICON_SRC'		=> $phpbb_root_path . $img_path . '/' . $ext_group_row['upload_icon'],
							'EXTGROUP_FILESIZE'		=> $ext_group_row['max_filesize'],
							'ASSIGNED_EXTENSIONS'	=> $assigned_extensions,

							'S_CATEGORY_SELECT'			=> $this->category_select('special_category', $group_id, 'category'),
							'S_EXT_GROUP_SIZE_OPTIONS'	=> size_select_options($size_format),
							'S_EXTENSION_OPTIONS'		=> $s_extension_options,
							'S_FILENAME_LIST'			=> $filename_list,
							'S_EDIT_GROUP'				=> true,
							'S_NO_IMAGE'				=> $no_image_select,
							'S_FORUM_IDS'				=> (sizeof($forum_ids)) ? true : false,

							'U_EXTENSIONS'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;mode=extensions"),
							'U_BACK'			=> $this->u_action,

							'L_LEGEND'			=> $user->lang[strtoupper($action) . '_EXTENSION_GROUP'])
						);

						$s_forum_id_options = '';

						/** @todo use in-built function **/

						$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
							FROM ' . FORUMS_TABLE . '
							ORDER BY left_id ASC';
						$result = $db->sql_query($sql, 600);

						$right = $cat_right = $padding_inc = 0;
						$padding = $forum_list = $holding = '';
						$padding_store = array('0' => '');

						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
							{
								// Non-postable forum with no subforums, don't display
								continue;
							}

							if (!$auth->acl_get('f_list', $row['forum_id']))
							{
								// if the user does not have permissions to list this forum skip
								continue;
							}

							if ($row['left_id'] < $right)
							{
								$padding .= '&nbsp; &nbsp;';
								$padding_store[$row['parent_id']] = $padding;
							}
							else if ($row['left_id'] > $right + 1)
							{
								$padding = empty($padding_store[$row['parent_id']]) ? '' : $padding_store[$row['parent_id']];
							}

							$right = $row['right_id'];

							$selected = (in_array($row['forum_id'], $forum_ids)) ? ' selected="selected"' : '';

							if ($row['left_id'] > $cat_right)
							{
								// make sure we don't forget anything
								$s_forum_id_options .= $holding;
								$holding = '';
							}

							if ($row['right_id'] - $row['left_id'] > 1)
							{
								$cat_right = max($cat_right, $row['right_id']);

								$holding .= '<option value="' . $row['forum_id'] . '"' . (($row['forum_type'] == FORUM_POST) ? ' class="sep"' : '') . $selected . '>' . $padding . $row['forum_name'] . '</option>';
							}
							else
							{
								$s_forum_id_options .= $holding . '<option value="' . $row['forum_id'] . '"' . (($row['forum_type'] == FORUM_POST) ? ' class="sep"' : '') . $selected . '>' . $padding . $row['forum_name'] . '</option>';
								$holding = '';
							}
						}

						if ($holding)
						{
							$s_forum_id_options .= $holding;
						}

						$db->sql_freeresult($result);
						unset($padding_store);

						$template->assign_vars(array(
							'S_FORUM_ID_OPTIONS'	=> $s_forum_id_options)
						);

					break;
				}

				$sql = 'SELECT *
					FROM ' . EXTENSION_GROUPS_TABLE . '
					ORDER BY allow_group DESC, allow_in_pm DESC, group_name';
				$result = $db->sql_query($sql);

				$old_allow_group = $old_allow_pm = 1;
				while ($row = $db->sql_fetchrow($result))
				{
					$s_add_spacer = ($old_allow_group != $row['allow_group'] || $old_allow_pm != $row['allow_in_pm']) ? true : false;

					$template->assign_block_vars('groups', array(
						'S_ADD_SPACER'		=> $s_add_spacer,
						'S_ALLOWED_IN_PM'	=> ($row['allow_in_pm']) ? true : false,
						'S_GROUP_ALLOWED'	=> ($row['allow_group']) ? true : false,

						'U_EDIT'		=> $this->u_action . "&amp;action=edit&amp;g={$row['group_id']}",
						'U_DELETE'		=> $this->u_action . "&amp;action=delete&amp;g={$row['group_id']}",

						'GROUP_NAME'	=> (isset($user->lang['EXT_GROUP_' . $row['group_name']])) ? $user->lang['EXT_GROUP_' . $row['group_name']] : $row['group_name'],
						'CATEGORY'		=> $cat_lang[$row['cat_id']],
						)
					);

					$old_allow_group = $row['allow_group'];
					$old_allow_pm = $row['allow_in_pm'];
				}
				$db->sql_freeresult($result);

			break;

			case 'orphan':

				if ($submit)
				{
					$delete_files = (isset($_POST['delete'])) ? array_keys(request_var('delete', array('' => 0))) : array();
					$add_files = (isset($_POST['add'])) ? array_keys(request_var('add', array('' => 0))) : array();
					$post_ids = request_var('post_id', array('' => 0));

					if (sizeof($delete_files))
					{
						$sql = 'SELECT *
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE ' . $db->sql_in_set('attach_id', $delete_files) . '
								AND is_orphan = 1';
						$result = $db->sql_query($sql);

						$delete_files = array();
						while ($row = $db->sql_fetchrow($result))
						{
							phpbb_unlink($row['physical_filename'], 'file');

							if ($row['thumbnail'])
							{
								phpbb_unlink($row['physical_filename'], 'thumbnail');
							}

							$delete_files[$row['attach_id']] = $row['real_filename'];
						}
						$db->sql_freeresult($result);
					}

					if (sizeof($delete_files))
					{
						$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . '
							WHERE ' . $db->sql_in_set('attach_id', array_keys($delete_files));
						$db->sql_query($sql);

						add_log('admin', 'LOG_ATTACH_ORPHAN_DEL', implode(', ', $delete_files));
						$notify[] = sprintf($user->lang['LOG_ATTACH_ORPHAN_DEL'], implode($user->lang['COMMA_SEPARATOR'], $delete_files));
					}

					$upload_list = array();
					foreach ($add_files as $attach_id)
					{
						if (!isset($delete_files[$attach_id]) && !empty($post_ids[$attach_id]))
						{
							$upload_list[$attach_id] = $post_ids[$attach_id];
						}
					}
					unset($add_files);

					if (sizeof($upload_list))
					{
						$template->assign_var('S_UPLOADING_FILES', true);

						$sql = 'SELECT forum_id, forum_name
							FROM ' . FORUMS_TABLE;
						$result = $db->sql_query($sql);

						$forum_names = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$forum_names[$row['forum_id']] = $row['forum_name'];
						}
						$db->sql_freeresult($result);

						$sql = 'SELECT forum_id, topic_id, post_id, poster_id
							FROM ' . POSTS_TABLE . '
							WHERE ' . $db->sql_in_set('post_id', $upload_list);
						$result = $db->sql_query($sql);

						$post_info = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$post_info[$row['post_id']] = $row;
						}
						$db->sql_freeresult($result);

						// Select those attachments we want to change...
						$sql = 'SELECT *
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE ' . $db->sql_in_set('attach_id', array_keys($upload_list)) . '
								AND is_orphan = 1';
						$result = $db->sql_query($sql);

						$files_added = $space_taken = 0;
						while ($row = $db->sql_fetchrow($result))
						{
							$post_row = $post_info[$upload_list[$row['attach_id']]];

							$template->assign_block_vars('upload', array(
								'FILE_INFO'		=> sprintf($user->lang['UPLOADING_FILE_TO'], $row['real_filename'], $post_row['post_id']),
								'S_DENIED'		=> (!$auth->acl_get('f_attach', $post_row['forum_id'])) ? true : false,
								'L_DENIED'		=> (!$auth->acl_get('f_attach', $post_row['forum_id'])) ? sprintf($user->lang['UPLOAD_DENIED_FORUM'], $forum_names[$row['forum_id']]) : '')
							);

							if (!$auth->acl_get('f_attach', $post_row['forum_id']))
							{
								continue;
							}

							// Adjust attachment entry
							$sql_ary = array(
								'in_message'	=> 0,
								'is_orphan'		=> 0,
								'poster_id'		=> $post_row['poster_id'],
								'post_msg_id'	=> $post_row['post_id'],
								'topic_id'		=> $post_row['topic_id'],
							);

							$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE attach_id = ' . $row['attach_id'];
							$db->sql_query($sql);

							$sql = 'UPDATE ' . POSTS_TABLE . '
								SET post_attachment = 1
								WHERE post_id = ' . $post_row['post_id'];
							$db->sql_query($sql);

							$sql = 'UPDATE ' . TOPICS_TABLE . '
								SET topic_attachment = 1
								WHERE topic_id = ' . $post_row['topic_id'];
							$db->sql_query($sql);

							$space_taken += $row['filesize'];
							$files_added++;

							add_log('admin', 'LOG_ATTACH_FILEUPLOAD', $post_row['post_id'], $row['real_filename']);
						}
						$db->sql_freeresult($result);

						if ($files_added)
						{
							set_config_count('upload_dir_size', $space_taken, true);
							set_config_count('num_files', $files_added, true);
						}
					}
				}

				$template->assign_vars(array(
					'S_ORPHAN'		=> true)
				);

				// Just get the files with is_orphan set and older than 3 hours
				$sql = 'SELECT *
					FROM ' . ATTACHMENTS_TABLE . '
					WHERE is_orphan = 1
						AND filetime < ' . (time() - 3*60*60) . '
					ORDER BY filetime DESC';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('orphan', array(
						'FILESIZE'			=> get_formatted_filesize($row['filesize']),
						'FILETIME'			=> $user->format_date($row['filetime']),
						'REAL_FILENAME'		=> utf8_basename($row['real_filename']),
						'PHYSICAL_FILENAME'	=> utf8_basename($row['physical_filename']),
						'ATTACH_ID'			=> $row['attach_id'],
						'POST_IDS'			=> (!empty($post_ids[$row['attach_id']])) ? $post_ids[$row['attach_id']] : '',
						'U_FILE'			=> append_sid($phpbb_root_path . 'download/file.' . $phpEx, 'mode=view&amp;id=' . $row['attach_id']))
					);
				}
				$db->sql_freeresult($result);

			break;

			case 'manage':

				if ($submit)
				{
					$delete_files = (isset($_POST['delete'])) ? array_keys(request_var('delete', array('' => 0))) : array();

					if (sizeof($delete_files))
					{
						// Select those attachments we want to delete...
						$sql = 'SELECT real_filename
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE ' . $db->sql_in_set('attach_id', $delete_files) . '
								AND is_orphan = 0';
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							$deleted_filenames[] = $row['real_filename'];
						}
						$db->sql_freeresult($result);

						if ($num_deleted = delete_attachments('attach', $delete_files))
						{
							if (sizeof($delete_files) != $num_deleted)
							{
								$error[] = $user->lang['FILES_GONE'];
							}
							add_log('admin', 'LOG_ATTACHMENTS_DELETED', implode(', ', $deleted_filenames));
							$notify[] = sprintf($user->lang['LOG_ATTACHMENTS_DELETED'], implode($user->lang['COMMA_SEPARATOR'], $deleted_filenames));
						}
						else
						{
							$error[] = $user->lang['NO_FILES_TO_DELETE'];
						}
					}
				}

				if ($action == 'stats')
				{
					$this->handle_stats_resync();
				}

				$stats_error = $this->check_stats_accuracy();

				if ($stats_error)
				{
					$error[] = $stats_error;
				}

				$template->assign_vars(array(
					'S_MANAGE'		=> true,
				));

				$start		= request_var('start', 0);

				// Sort keys
				$sort_days	= request_var('st', 0);
				$sort_key	= request_var('sk', 't');
				$sort_dir	= request_var('sd', 'd');

				// Sorting
				$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
				$sort_by_text = array('f' => $user->lang['FILENAME'], 't' => $user->lang['FILEDATE'], 's' => $user->lang['FILESIZE'], 'x' => $user->lang['EXTENSION'], 'd' => $user->lang['DOWNLOADS'],'p' => $user->lang['ATTACH_POST_TYPE'], 'u' => $user->lang['AUTHOR']);
				$sort_by_sql = array('f' => 'a.real_filename', 't' => 'a.filetime', 's' => 'a.filesize', 'x' => 'a.extension', 'd' => 'a.download_count', 'p' => 'a.in_message', 'u' => 'u.username');

				$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

				$min_filetime = ($sort_days) ? (time() - ($sort_days * 86400)) : '';
				$limit_filetime = ($min_filetime) ? " AND a.filetime >= $min_filetime " : '';
				$start = ($sort_days && isset($_POST['sort'])) ? 0 : $start;

				$attachments_per_page = (int) $config['topics_per_page'];

				$stats = $this->get_attachment_stats($limit_filetime);
				$num_files = $stats['num_files'];
				$total_size = $stats['upload_dir_size'];

				// Make sure $start is set to the last page if it exceeds the amount
				$pagination = $phpbb_container->get('pagination');
				$start = $pagination->validate_start($start, $attachments_per_page, $num_files);

				// If the user is trying to reach the second half of the attachments list, fetch it starting from the end
				$store_reverse = false;
				$sql_limit = $attachments_per_page;

				if ($start > $num_files / 2)
				{
					$store_reverse = true;

					// Select the sort order. Add time sort anchor for non-time sorting cases
					$sql_sort_anchor = ($sort_key != 't') ? ', a.filetime ' . (($sort_dir == 'd') ? 'ASC' : 'DESC') : '';
					$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'ASC' : 'DESC') . $sql_sort_anchor;
					$sql_limit = $pagination->reverse_limit($start, $sql_limit, $num_files);
					$sql_start = $pagination->reverse_start($start, $sql_limit, $num_files);
				}
				else
				{
					// Select the sort order. Add time sort anchor for non-time sorting cases
					$sql_sort_anchor = ($sort_key != 't') ? ', a.filetime ' . (($sort_dir == 'd') ? 'DESC' : 'ASC') : '';
					$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC') . $sql_sort_anchor;
					$sql_start = $start;
				}

				$attachments_list = array();

				// Just get the files
				$sql = 'SELECT a.*, u.username, u.user_colour, t.topic_title
					FROM ' . ATTACHMENTS_TABLE . ' a
					LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = a.poster_id)
					LEFT JOIN ' . TOPICS_TABLE . " t ON (a.topic_id = t.topic_id)
					WHERE a.is_orphan = 0
						$limit_filetime
					ORDER BY $sql_sort_order";
				$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

				$i = ($store_reverse) ? $sql_limit - 1 : 0;

				// Store increment value in a variable to save some conditional calls
				$i_increment = ($store_reverse) ? -1 : 1;
				while ($attachment_row = $db->sql_fetchrow($result))
				{
					$attachments_list[$i] = $attachment_row;
					$i = $i + $i_increment;
				}
				$db->sql_freeresult($result);

				$base_url = $this->u_action . "&amp;$u_sort_param";
				$pagination->generate_template_pagination($base_url, 'pagination', 'start', $num_files, $attachments_per_page, $start);

				$template->assign_vars(array(
					'TOTAL_FILES'		=> $num_files,
					'TOTAL_SIZE'		=> get_formatted_filesize($total_size),

					'S_LIMIT_DAYS'		=> $s_limit_days,
					'S_SORT_KEY'		=> $s_sort_key,
					'S_SORT_DIR'		=> $s_sort_dir)
				);

				// Grab extensions
				$extensions = $cache->obtain_attach_extensions(true);

				for ($i = 0, $end = sizeof($attachments_list); $i < $end; ++$i)
				{
					$row = $attachments_list[$i];

					$row['extension'] = strtolower(trim((string) $row['extension']));
					$comment = ($row['attach_comment'] && !$row['in_message']) ? str_replace(array("\n", "\r"), array('<br />', "\n"), $row['attach_comment']) : '';
					$display_cat = $extensions[$row['extension']]['display_cat'];
					$l_downloaded_viewed = ($display_cat == ATTACHMENT_CATEGORY_NONE) ? 'DOWNLOAD_COUNTS' : 'VIEWED_COUNTS';

					$template->assign_block_vars('attachments', array(
						'ATTACHMENT_POSTER'	=> get_username_string('full', (int) $row['poster_id'], (string) $row['username'], (string) $row['user_colour'], (string) $row['username']),
						'FILESIZE'			=> get_formatted_filesize((int) $row['filesize']),
						'FILETIME'			=> $user->format_date((int) $row['filetime']),
						'REAL_FILENAME'		=> (!$row['in_message']) ? utf8_basename((string) $row['real_filename']) : '',
						'PHYSICAL_FILENAME'	=> utf8_basename((string) $row['physical_filename']),
						'EXT_GROUP_NAME'	=> (!empty($extensions[$row['extension']]['group_name'])) ? $user->lang['EXT_GROUP_' . $extensions[$row['extension']]['group_name']] : '',
						'COMMENT'			=> $comment,
						'TOPIC_TITLE'		=> (!$row['in_message']) ? (string) $row['topic_title'] : '',
						'ATTACH_ID'			=> (int) $row['attach_id'],
						'POST_ID'			=> (int) $row['post_msg_id'],
						'TOPIC_ID'			=> (int) $row['topic_id'],
						'POST_IDS'			=> (!empty($post_ids[$row['attach_id']])) ? (int) $post_ids[$row['attach_id']] : '',

						'L_DOWNLOAD_COUNT'	=> $user->lang($l_downloaded_viewed, (int) $row['download_count']),

						'S_IN_MESSAGE'		=> (bool) $row['in_message'],

						'U_VIEW_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . "#p{$row['post_msg_id']}",
						'U_FILE'			=> append_sid($phpbb_root_path . 'download/file.' . $phpEx, 'mode=view&amp;id=' . $row['attach_id']))
					);
				}

			break;
		}

		if (sizeof($error))
		{
			$template->assign_vars(array(
				'S_WARNING'		=> true,
				'WARNING_MSG'	=> implode('<br />', $error))
			);
		}

		if (sizeof($notify))
		{
			$template->assign_vars(array(
				'S_NOTIFY'		=> true,
				'NOTIFY_MSG'	=> implode('<br />', $notify))
			);
		}
	}

	/**
	* Get attachment file count and size of upload directory
	*
	* @param $limit string	Additional limit for WHERE clause to filter stats by.
	* @return array Returns array with stats: num_files and upload_dir_size
	*/
	public function get_attachment_stats($limit = '')
	{
		$sql = 'SELECT COUNT(a.attach_id) AS num_files, SUM(a.filesize) AS upload_dir_size
			FROM ' . ATTACHMENTS_TABLE . " a
			WHERE a.is_orphan = 0
				$limit";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return array(
			'num_files'			=> (int) $row['num_files'],
			'upload_dir_size'	=> (float) $row['upload_dir_size'],
		);
	}

	/**
	* Set config attachment stat values
	*
	* @param $stats array	Array of config key => value pairs to set.
	* @return null
	*/
	public function set_attachment_stats($stats)
	{
		foreach ($stats as $key => $value)
		{
			$this->config->set($key, $value, true);
		}
	}

	/**
	* Check accuracy of attachment statistics.
	*
	* @return bool|string	Returns false if stats are correct or error message
	*	otherwise.
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
			$u_resync = $this->u_action . '&amp;action=stats';

			return $this->user->lang(
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
	* @return null
	*/
	public function handle_stats_resync()
	{
		if (!confirm_box(true))
		{
			confirm_box(false, $this->user->lang['RESYNC_FILES_STATS_CONFIRM'], build_hidden_fields(array(
				'i'			=> $this->id,
				'mode'		=> 'manage',
				'action'	=> 'stats',
			)));
		}
		else
		{
			$this->set_attachment_stats($this->get_attachment_stats());
			$log = $this->phpbb_container->get('log');
			$log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESYNC_FILES_STATS');
		}

	}

	/**
	* Build Select for category items
	*/
	function category_select($select_name, $group_id = false, $key = '')
	{
		global $db, $user;

		$types = array(
			ATTACHMENT_CATEGORY_NONE		=> $user->lang['NO_FILE_CAT'],
			ATTACHMENT_CATEGORY_IMAGE		=> $user->lang['CAT_IMAGES'],
			ATTACHMENT_CATEGORY_WM			=> $user->lang['CAT_WM_FILES'],
			ATTACHMENT_CATEGORY_RM			=> $user->lang['CAT_RM_FILES'],
			ATTACHMENT_CATEGORY_FLASH		=> $user->lang['CAT_FLASH_FILES'],
			ATTACHMENT_CATEGORY_QUICKTIME	=> $user->lang['CAT_QUICKTIME_FILES'],
		);

		if ($group_id)
		{
			$sql = 'SELECT cat_id
				FROM ' . EXTENSION_GROUPS_TABLE . '
				WHERE group_id = ' . (int) $group_id;
			$result = $db->sql_query($sql);

			$cat_type = (!($row = $db->sql_fetchrow($result))) ? ATTACHMENT_CATEGORY_NONE : $row['cat_id'];

			$db->sql_freeresult($result);
		}
		else
		{
			$cat_type = ATTACHMENT_CATEGORY_NONE;
		}

		$group_select = '<select name="' . $select_name . '"' . (($key) ? ' id="' . $key . '"' : '') . '>';

		foreach ($types as $type => $mode)
		{
			$selected = ($type == $cat_type) ? ' selected="selected"' : '';
			$group_select .= '<option value="' . $type . '"' . $selected . '>' . $mode . '</option>';
		}

		$group_select .= '</select>';

		return $group_select;
	}

	/**
	* Extension group select
	*/
	function group_select($select_name, $default_group = false, $key = '')
	{
		global $db, $user;

		$group_select = '<select name="' . $select_name . '"' . (($key) ? ' id="' . $key . '"' : '') . '>';

		$sql = 'SELECT group_id, group_name
			FROM ' . EXTENSION_GROUPS_TABLE . '
			ORDER BY group_name';
		$result = $db->sql_query($sql);

		$group_name = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$row['group_name'] = (isset($user->lang['EXT_GROUP_' . $row['group_name']])) ? $user->lang['EXT_GROUP_' . $row['group_name']] : $row['group_name'];
			$group_name[] = $row;
		}
		$db->sql_freeresult($result);

		$row['group_id'] = 0;
		$row['group_name'] = $user->lang['NOT_ASSIGNED'];
		$group_name[] = $row;

		for ($i = 0; $i < sizeof($group_name); $i++)
		{
			if ($default_group === false)
			{
				$selected = ($i == 0) ? ' selected="selected"' : '';
			}
			else
			{
				$selected = ($group_name[$i]['group_id'] == $default_group) ? ' selected="selected"' : '';
			}

			$group_select .= '<option value="' . $group_name[$i]['group_id'] . '"' . $selected . '>' . $group_name[$i]['group_name'] . '</option>';
		}

		$group_select .= '</select>';

		return $group_select;
	}

	/**
	* Search Imagick
	*/
	function search_imagemagick()
	{
		$imagick = '';

		$exe = ((defined('PHP_OS')) && (preg_match('#^win#i', PHP_OS))) ? '.exe' : '';

		$magic_home = getenv('MAGICK_HOME');

		if (empty($magic_home))
		{
			$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');
			$path_locations = str_replace('\\', '/', (explode(($exe) ? ';' : ':', getenv('PATH'))));

			$locations = array_merge($path_locations, $locations);

			foreach ($locations as $location)
			{
				// The path might not end properly, fudge it
				if (substr($location, -1) !== '/')
				{
					$location .= '/';
				}

				if (@file_exists($location) && @is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
				{
					$imagick = str_replace('\\', '/', $location);
					continue;
				}
			}
		}
		else
		{
			$imagick = str_replace('\\', '/', $magic_home);
		}

		return $imagick;
	}

	/**
	* Test Settings
	*/
	function test_upload(&$error, $upload_dir, $create_directory = false)
	{
		global $user, $phpbb_root_path;

		// Does the target directory exist, is it a directory and writable.
		if ($create_directory)
		{
			if (!file_exists($phpbb_root_path . $upload_dir))
			{
				@mkdir($phpbb_root_path . $upload_dir, 0777);
				phpbb_chmod($phpbb_root_path . $upload_dir, CHMOD_READ | CHMOD_WRITE);
			}
		}

		if (!file_exists($phpbb_root_path . $upload_dir))
		{
			$error[] = sprintf($user->lang['NO_UPLOAD_DIR'], $upload_dir);
			return;
		}

		if (!is_dir($phpbb_root_path . $upload_dir))
		{
			$error[] = sprintf($user->lang['UPLOAD_NOT_DIR'], $upload_dir);
			return;
		}

		if (!phpbb_is_writable($phpbb_root_path . $upload_dir))
		{
			$error[] = sprintf($user->lang['NO_WRITE_UPLOAD'], $upload_dir);
			return;
		}
	}

	/**
	* Perform operations on sites for external linking
	*/
	function perform_site_list()
	{
		global $db, $user;
		global $request;

		if (isset($_REQUEST['securesubmit']))
		{
			// Grab the list of entries
			$ips = request_var('ips', '');
			$ip_list = array_unique(explode("\n", $ips));
			$ip_list_log = implode(', ', $ip_list);

			$ip_exclude = (int) $request->variable('ipexclude', false, false, \phpbb\request\request_interface::POST);

			$iplist = array();
			$hostlist = array();

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
							$ip_2_fragment = 256;

							$iplist[] = "'$ip_1_counter.*'";
						}

						while ($ip_2_counter <= $ip_2_end)
						{
							$ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
							$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

							if ($ip_3_counter == 0 && $ip_3_end == 254)
							{
								$ip_3_counter = 256;
								$ip_3_fragment = 256;

								$iplist[] = "'$ip_1_counter.$ip_2_counter.*'";
							}

							while ($ip_3_counter <= $ip_3_end)
							{
								$ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
								$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

								if ($ip_4_counter == 0 && $ip_4_end == 254)
								{
									$ip_4_counter = 256;
									$ip_4_fragment = 256;

									$iplist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.*'";
								}

								while ($ip_4_counter <= $ip_4_end)
								{
									$iplist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter'";
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
					$iplist[] = "'" . trim($item) . "'";
				}
				else if (preg_match('#^([\w\-_]\.?){2,}$#is', trim($item)))
				{
					$hostlist[] = "'" . trim($item) . "'";
				}
				else if (preg_match("#^([a-z0-9\-\*\._/]+?)$#is", trim($item)))
				{
					$hostlist[] = "'" . trim($item) . "'";
				}
			}

			$sql = 'SELECT site_ip, site_hostname
				FROM ' . SITELIST_TABLE . "
				WHERE ip_exclude = $ip_exclude";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$iplist_tmp = array();
				$hostlist_tmp = array();
				do
				{
					if ($row['site_ip'])
					{
						if (strlen($row['site_ip']) > 40)
						{
							continue;
						}

						$iplist_tmp[] = "'" . $row['site_ip'] . "'";
					}
					else if ($row['site_hostname'])
					{
						if (strlen($row['site_hostname']) > 255)
						{
							continue;
						}

						$hostlist_tmp[] = "'" . $row['site_hostname'] . "'";
					}
					// break;
				}
				while ($row = $db->sql_fetchrow($result));

				$iplist = array_unique(array_diff($iplist, $iplist_tmp));
				$hostlist = array_unique(array_diff($hostlist, $hostlist_tmp));
				unset($iplist_tmp);
				unset($hostlist_tmp);
			}
			$db->sql_freeresult($result);

			if (sizeof($iplist))
			{
				foreach ($iplist as $ip_entry)
				{
					$sql = 'INSERT INTO ' . SITELIST_TABLE . " (site_ip, ip_exclude)
						VALUES ($ip_entry, $ip_exclude)";
					$db->sql_query($sql);
				}
			}

			if (sizeof($hostlist))
			{
				foreach ($hostlist as $host_entry)
				{
					$sql = 'INSERT INTO ' . SITELIST_TABLE . " (site_hostname, ip_exclude)
						VALUES ($host_entry, $ip_exclude)";
					$db->sql_query($sql);
				}
			}

			if (!empty($ip_list_log))
			{
				// Update log
				$log_entry = ($ip_exclude) ? 'LOG_DOWNLOAD_EXCLUDE_IP' : 'LOG_DOWNLOAD_IP';
				add_log('admin', $log_entry, $ip_list_log);
			}

			trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCCESS'] . adm_back_link($this->u_action));
		}
		else if (isset($_POST['unsecuresubmit']))
		{
			$unip_sql = request_var('unip', array(0));

			if (sizeof($unip_sql))
			{
				$l_unip_list = '';

				// Grab details of ips for logging information later
				$sql = 'SELECT site_ip, site_hostname
					FROM ' . SITELIST_TABLE . '
					WHERE ' . $db->sql_in_set('site_id', $unip_sql);
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$l_unip_list .= (($l_unip_list != '') ? ', ' : '') . (($row['site_ip']) ? $row['site_ip'] : $row['site_hostname']);
				}
				$db->sql_freeresult($result);

				$sql = 'DELETE FROM ' . SITELIST_TABLE . '
					WHERE ' . $db->sql_in_set('site_id', $unip_sql);
				$db->sql_query($sql);

				add_log('admin', 'LOG_DOWNLOAD_REMOVE_IP', $l_unip_list);
			}

			trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCCESS'] . adm_back_link($this->u_action));
		}
	}

	/**
	* Write display_order config field
	*/
	function display_order($value, $key = '')
	{
		$radio_ary = array(0 => 'DESCENDING', 1 => 'ASCENDING');

		return h_radio('config[display_order]', $radio_ary, $value, $key);
	}

	/**
	* Adjust all three max_filesize config vars for display
	*/
	function max_filesize($value, $key = '')
	{
		// Determine size var and adjust the value accordingly
		$filesize = get_formatted_filesize($value, false, array('mb', 'kb', 'b'));
		$size_var = $filesize['si_identifier'];
		$value = $filesize['value'];

		// size="8" and maxlength="15" attributes as a fallback for browsers that do not support type="number" yet.
		return '<input type="number" id="' . $key . '" size="8" maxlength="15" min="0" name="config[' . $key . ']" value="' . $value . '" /> <select name="' . $key . '">' . size_select_options($size_var) . '</select>';
	}

	/**
	* Write secure_allow_deny config field
	*/
	function select_allow_deny($value, $key = '')
	{
		$radio_ary = array(1 => 'ORDER_ALLOW_DENY', 0 => 'ORDER_DENY_ALLOW');

		return h_radio('config[' . $key . ']', $radio_ary, $value, $key);
	}

}
