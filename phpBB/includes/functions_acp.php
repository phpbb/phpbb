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

/**
* Header for acp pages
*/
function adm_page_header($page_title)
{
	global $config, $user, $template;
	global $phpbb_root_path, $phpbb_admin_path, $phpEx, $SID, $_SID;
	global $phpbb_dispatcher, $phpbb_container;

	if (defined('HEADER_INC'))
	{
		return;
	}

	define('HEADER_INC', true);

	// A listener can set this variable to `true` when it overrides this function
	$adm_page_header_override = false;

	/**
	* Execute code and/or overwrite adm_page_header()
	*
	* @event core.adm_page_header
	* @var	string	page_title			Page title
	* @var	bool	adm_page_header_override	Shall we return instead of
	*									running the rest of adm_page_header()
	* @since 3.1.0-a1
	*/
	$vars = array('page_title', 'adm_page_header_override');
	extract($phpbb_dispatcher->trigger_event('core.adm_page_header', compact($vars)));

	if ($adm_page_header_override)
	{
		return;
	}

	$user->update_session_infos();

	// gzip_compression
	if ($config['gzip_compress'])
	{
		if (@extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	$phpbb_version_parts = explode('.', PHPBB_VERSION, 3);
	$phpbb_major = $phpbb_version_parts[0] . '.' . $phpbb_version_parts[1];

	$template->assign_vars(array(
		'PAGE_TITLE'			=> $page_title,
		'USERNAME'				=> $user->data['username'],

		'SID'					=> $SID,
		'_SID'					=> $_SID,
		'SESSION_ID'			=> $user->session_id,
		'ROOT_PATH'				=> $phpbb_root_path,
		'ADMIN_ROOT_PATH'		=> $phpbb_admin_path,
		'PHPBB_VERSION'			=> PHPBB_VERSION,
		'PHPBB_MAJOR'			=> $phpbb_major,

		'U_LOGOUT'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=logout'),
		'U_ADM_LOGOUT'			=> append_sid("{$phpbb_admin_path}index.$phpEx", 'action=admlogout&amp;hash=' . generate_link_hash('acp_logout')),
		'U_ADM_INDEX'			=> append_sid("{$phpbb_admin_path}index.$phpEx"),
		'U_INDEX'				=> append_sid("{$phpbb_root_path}index.$phpEx"),

		'T_IMAGES_PATH'			=> "{$phpbb_root_path}images/",
		'T_SMILIES_PATH'		=> "{$phpbb_root_path}{$config['smilies_path']}/",
		'T_AVATAR_GALLERY_PATH'	=> "{$phpbb_root_path}{$config['avatar_gallery_path']}/",
		'T_ICONS_PATH'			=> "{$phpbb_root_path}{$config['icons_path']}/",
		'T_RANKS_PATH'			=> "{$phpbb_root_path}{$config['ranks_path']}/",
		'T_UPLOAD_PATH'			=> "{$phpbb_root_path}{$config['upload_path']}/",

		'T_FONT_AWESOME_LINK'	=> !empty($config['allow_cdn']) && !empty($config['load_font_awesome_url']) ? $config['load_font_awesome_url'] : "{$phpbb_root_path}assets/css/font-awesome.min.css?assets_version=" . $config['assets_version'],

		'T_ASSETS_VERSION'		=> $config['assets_version'],
		'T_ASSETS_PATH'			=> "{$phpbb_root_path}assets",

		'ICON_MOVE_UP'				=> '<i class="acp-icon acp-icon-move-up fa-arrow-circle-up fa-fw fas" title="' . $user->lang('MOVE_UP') . '"></i>',
		'ICON_MOVE_UP_DISABLED'		=> '<i class="acp-icon acp-icon-disabled fa-arrow-circle-up fa-fw fas" title="' . $user->lang('MOVE_UP') . '"></i>',
		'ICON_MOVE_DOWN'			=> '<i class="acp-icon acp-icon-move-down fa-arrow-circle-down fa-fw fas" title="' . $user->lang('MOVE_DOWN') . '"></i>',
		'ICON_MOVE_DOWN_DISABLED'	=> '<i class="acp-icon acp-icon-disabled fa-arrow-circle-down fa-fw fas" title="' . $user->lang('MOVE_DOWN') . '"></i>',
		'ICON_EDIT'					=> '<i class="acp-icon acp-icon-settings fa-gear fa-fw fas" title="' . $user->lang('EDIT') . '"></i>',
		'ICON_EDIT_DISABLED'		=> '<i class="acp-icon acp-icon-disabled fa-gear fa-fw fas" title="' . $user->lang('EDIT') . '"></i>',
		'ICON_DELETE'				=> '<i class="acp-icon acp-icon-delete fa-xmark-circle fa-fw fas" title="' . $user->lang('DELETE') . '"></i>',
		'ICON_DELETE_DISABLED'		=> '<i class="acp-icon acp-icon-disabled fa-xmark-circle fa-fw fas" title="' . $user->lang('DELETE') . '"></i>',
		'ICON_SYNC'					=> '<i class="acp-icon acp-icon-resync fa-arrows-rotate fa-fw fas" title="' . $user->lang('RESYNC') . '"></i>',
		'ICON_SYNC_DISABLED'		=> '<i class="acp-icon acp-icon-disabled fa-arrows-rotate fa-fw fas" title="' . $user->lang('RESYNC') . '"></i>',

		'S_USER_ID'				=> $user->data['user_id'],
		'S_USER_LANG'			=> $user->lang('USER_LANG'),
		'S_CONTENT_DIRECTION'	=> $user->lang('DIRECTION'),
		'S_CONTENT_ENCODING'	=> 'UTF-8',
		'S_CONTENT_FLOW_BEGIN'	=> ($user->lang('DIRECTION')  == 'ltr') ? 'left' : 'right',
		'S_CONTENT_FLOW_END'	=> ($user->lang('DIRECTION')  == 'ltr') ? 'right' : 'left',

		'CONTAINER_EXCEPTION'	=> $phpbb_container->hasParameter('container_exception') ? $phpbb_container->getParameter('container_exception') : false,
	));

	// An array of http headers that phpBB will set. The following event may override these.
	$http_headers = array(
		// application/xhtml+xml not used because of IE
		'Content-type' => 'text/html; charset=UTF-8',
		'Cache-Control' => 'private, no-cache="set-cookie"',
		'Expires' => gmdate('D, d M Y H:i:s', time()) . ' GMT',
		'Referrer-Policy' => 'strict-origin-when-cross-origin',
	);

	/**
	* Execute code and/or overwrite _common_ template variables after they have been assigned.
	*
	* @event core.adm_page_header_after
	* @var	string	page_title			Page title
	* @var	array	http_headers			HTTP headers that should be set by phpbb
	*
	* @since 3.1.0-RC3
	*/
	$vars = array('page_title', 'http_headers');
	extract($phpbb_dispatcher->trigger_event('core.adm_page_header_after', compact($vars)));

	foreach ($http_headers as $hname => $hval)
	{
		header((string) $hname . ': ' . (string) $hval);
	}

	return;
}

/**
* Page footer for acp pages
*/
function adm_page_footer($copyright_html = true)
{
	global $db, $config, $template, $user, $auth;
	global $phpbb_root_path, $phpbb_container;
	global $phpbb_dispatcher;

	/** @var \phpbb\controller\helper $controller_helper */
	$controller_helper = $phpbb_container->get('controller.helper');

	// A listener can set this variable to `true` when it overrides this function
	$adm_page_footer_override = false;

	/**
	* Execute code and/or overwrite adm_page_footer()
	*
	* @event core.adm_page_footer
	* @var	bool	copyright_html			Shall we display the copyright?
	* @var	bool	adm_page_footer_override	Shall we return instead of
	*									running the rest of adm_page_footer()
	* @since 3.1.0-a1
	*/
	$vars = array('copyright_html', 'adm_page_footer_override');
	extract($phpbb_dispatcher->trigger_event('core.adm_page_footer', compact($vars)));

	if ($adm_page_footer_override)
	{
		return;
	}

	$controller_helper->display_sql_report();

	$template->assign_vars(array(
		'DEBUG_OUTPUT'		=> phpbb_generate_debug_output($db, $config, $auth, $user, $phpbb_dispatcher),
		'TRANSLATION_INFO'	=> (!empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['TRANSLATION_INFO'] : '',
		'S_COPYRIGHT_HTML'	=> $copyright_html,
		'CREDIT_LINE'		=> $user->lang('POWERED_BY', '<a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited'),
		'T_JQUERY_LINK'		=> !empty($config['allow_cdn']) && !empty($config['load_jquery_url']) ? $config['load_jquery_url'] : "{$phpbb_root_path}assets/javascript/jquery-3.7.1.min.js",
		'S_ALLOW_CDN'		=> !empty($config['allow_cdn']),
		'VERSION'			=> $config['version'])
	);

	$template->display('body');

	garbage_collection();
	exit_handler();
}

/**
* Generate back link for acp pages
*/
function adm_back_link($u_action)
{
	global $language;
	return '<br /><br /><a href="' . $u_action . '">&laquo; ' . $language->lang('BACK_TO_PREV') . '</a>';
}

/**
 * Build select field options in acp pages
 *
 * @param array				$options_ary Configuration options data
 * @param int|string|bool	$option_default	Configuration option selected value
 *
 * @return array
 */
function build_select(array $options_ary, int|string|bool $option_default = false): array
{
	global $language;

	$options = [];
	foreach ($options_ary as $value => $title)
	{
		$options[] = [
			'value'	=> $value,
			'selected'	=> $option_default !== false && $value == $option_default,
			'label'	=> $language->lang($title),
		];
	}

	return $options;
}

/**
 * Build radio fields in acp pages
 *
 * @param int|string	$value	Configuration option value
 * @param string		$key	Configuration option key name
 * @param array			$options Configuration options data
 * 							representing array of [values => language_keys]
 *
 * @return array
 */
function phpbb_build_radio(int|string $value, string $key, array $options): array
{
	global $language;

	$buttons = [];
	foreach ($options as $val => $title)
	{
		$buttons[] = [
			'type'		=> 'radio',
			'value'		=> $val,
			'name'		=> 'config[' . $key . ']',
			'checked'	=> $val == $value,
			'label'		=> $language->lang($title),
		];
	}

	return [
		'buttons' => $buttons,
	];
}

/**
 * Build configuration data arrays or templates for configuration settings
 *
 * @param array			$tpl_type	Configuration setting type data
 * @param string		$key		Configuration option name
 * @param array|object	$new_ary	Updated configuration data
 * @param string		$config_key	Configuration option name
 * @param array			$vars		Configuration setting data
 *
 * @return array|string
 */
function phpbb_build_cfg_template(array $tpl_type, string $key, array|object &$new_ary, string $config_key, array $vars): array|string
{
	global $language, $module, $phpbb_dispatcher;

	$tpl = [];
	$name = 'config[' . $config_key . ']';

	// Make sure there is no notice printed out for non-existent config options (we simply set them)
	if (!isset($new_ary[$config_key]))
	{
		$new_ary[$config_key] = '';
	}

	switch ($tpl_type[0])
	{
		case 'password':
			if ($new_ary[$config_key] !== '')
			{
				// replace passwords with asterixes
				$new_ary[$config_key] = '********';
			}
			// no break

		case 'text':
		case 'url':
		case 'email':
		case 'tel':
		case 'search':
			// maxlength and size are only valid for these types and will be
			// ignored for other input types.
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = [
				'tag'		=> 'input',
				'id'		=> $key,
				'type'		=> $tpl_type[0],
				'name'		=> $name,
				'size'		=> $size ?: '',
				'maxlength'	=> $maxlength ?: 255,
				'value'		=> $new_ary[$config_key],
			];
		break;

		case 'color':
		case 'datetime':
		case 'datetime-local':
		case 'month':
		case 'week':
			$tpl = [
				'tag'		=> 'input',
				'id'		=> $key,
				'type'		=> $tpl_type[0],
				'name'		=> $name,
				'value'		=> $new_ary[$config_key],
			];
		break;

		case 'date':
		case 'time':
		case 'number':
		case 'range':
			$min = isset($tpl_type[1]) ? (int) $tpl_type[1] : false;
			$max = isset($tpl_type[2]) ? (int) $tpl_type[2] : false;

			$tpl = [
				'tag'		=> 'input',
				'id'		=> $key,
				'type'		=> $tpl_type[0],
				'name'		=> $name,
				'min'		=> $min !== false ? $min : '',
				'max'		=> $max !== false ? $max : '',
				'value'		=> $new_ary[$config_key],
			];
		break;

		case 'dimension':
			$min = isset($tpl_type[1]) ? (int) $tpl_type[1] : false;
			$max = isset($tpl_type[2]) ? (int) $tpl_type[2] : false;

			$tpl = [
				'tag'		=> 'dimension',
				'width' => [
					'id'		=> $key,
					'type'		=> 'number',
					'name'		=> 'config[' . $config_key . '_width]',
					'min'		=> $min !== false ? $min : '',
					'max'		=> $max !== false ? $max : '',
					'value'		=> $new_ary[$config_key . '_width'],
				],
				'height' => [
					'type'		=> 'number',
					'name'		=> 'config[' . $config_key . '_height]',
					'min'		=> $min !== false ? $min : '',
					'max'		=> $max !== false ? $max : '',
					'value'		=> $new_ary[$config_key . '_height'],
				],
			];
		break;

		case 'textarea':
			$tpl = [
				'tag'		=> 'textarea',
				'id'		=> $key,
				'name'		=> $name,
				'rows'		=> (int) $tpl_type[1],
				'cols'		=> (int) $tpl_type[2],
				'content'	=> $new_ary[$config_key],
			];
		break;

		case 'radio':
			if (!isset($vars['method']) && !isset($vars['function']))
			{
				if (in_array($tpl_type[1], ['yes_no', 'enabled_disabled']))
				{
					$options = array_reverse(explode('_', strtoupper($tpl_type[1])));
					krsort($options);
					$tpl_type = array_merge ($tpl_type, phpbb_build_radio($new_ary[$config_key], $config_key, $options));
				}
			}
		case 'button':
		case 'select':
		case 'custom':
			$args = [];
			$call = $vars['function'] ?? (isset($vars['method']) ? [$module->module, $vars['method']] : false);

			if ($call)
			{
				if (isset($vars['params']))
				{
					foreach ($vars['params'] as $value)
					{
						switch ($value)
						{
							case '{CONFIG_VALUE}':
								$value = $new_ary[$config_key];
							break;

							case '{KEY}':
								$value = $config_key;
							break;
						}

						$args[] = $value;
					}
				}
				else
				{
					$args = array($new_ary[$config_key], $config_key);
				}
			}

			$return = $call ? call_user_func_array($call, $args) : [];

			if (in_array($tpl_type[0], ['select', 'radio', 'button']))
			{
				$tpl_type = array_merge($tpl_type, $return);

				if ($tpl_type[0] == 'select')
				{
					$tpl = [
						'tag'			=> 'select',
						'class'			=> $tpl_type['class'] ?? false,
						'id'			=> $key,
						'data'			=> $tpl_type['data'] ?? [],
						'name'			=> $name,
						'toggleable'	=> !empty($tpl_type[2]) || !empty($tpl_type['toggleable']),
						'options'		=> $tpl_type['options'],
						'group_only'	=> $tpl_type['group_only'] ?? false,
						'size'			=> $tpl_type[1] ?? $tpl_type['size'] ?? 1,
						'multiple'		=> $tpl_type['multiple'] ?? false,
					];
				}
				else if ($tpl_type[0] == 'radio')
				{
					// Only assign id to the one (1st) radio button in the list
					$id_assigned = false;
					foreach ($tpl_type['buttons'] as $i => $button)
					{
						if (!$id_assigned)
						{
							$tpl_type['buttons'][$i]['id'] = $key;
							$id_assigned = true;
						}
					}

					$tpl = [
						'tag'		=> 'radio',
						'buttons'	=> $tpl_type['buttons'],
					];
				}
				else
				{
					$tpl = [
						'tag'		=> 'input',
						'class'		=> $tpl_type['options']['class'],
						'id'		=> $key,
						'type'		=> $tpl_type['options']['type'],
						'name'		=> $tpl_type['options']['name'] ?? $name,
						'value'		=> $tpl_type['options']['value'],
					];
				}
			}
			else
			{
				$tpl = $return;
			}
		break;

		default:
		break;
	}

	if (isset($vars['append']))
	{
		if (is_array($tpl))
		{
			$tpl['append'] = $vars['append'];
		}
		else
		{
			$tpl .= $vars['append'];
		}
	}

	$new = $new_ary;
	/**
	* Overwrite the html code we display for the config value
	*
	* @event core.build_config_template
	* @var	array			tpl_type	Config type array:
	*							0 => data type
	*							1 [optional] => string: size, int: minimum
	*							2 [optional] => string: max. length, int: maximum
	* @var	string			key			Should be used for the id attribute in html
	* @var	array			new			Array with the config values we display
	* @var	string			name		Should be used for the name attribute
	* @var	array			vars		Array with the options for the config
	* @var	array|string	tpl			The resulting html code we display
	* @since 3.1.0-a1
	* @changed 4.0.0-a1	The event location's function renamed from build_config_template() to phpbb_build_cfg_template()
	*/
	$vars = array('tpl_type', 'key', 'new', 'name', 'vars', 'tpl');
	extract($phpbb_dispatcher->trigger_event('core.build_config_template', compact($vars)));
	$new_ary = $new;
	unset($new);

	return $tpl;
}

/**
* Going through a config array and validate values, writing errors to $error. The validation method  accepts parameters separated by ':' for string and int.
* The first parameter defines the type to be used, the second the lower bound and the third the upper bound. Only the type is required.
*/
function validate_config_vars($config_vars, &$cfg_array, &$error)
{
	global $phpbb_root_path, $user, $phpbb_dispatcher, $phpbb_filesystem, $language;

	$type	= 0;
	$min	= 1;
	$max	= 2;

	foreach ($config_vars as $config_name => $config_definition)
	{
		if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
		{
			continue;
		}

		if (!isset($config_definition['validate']))
		{
			continue;
		}

		$validator = explode(':', $config_definition['validate']);

		// Validate a bit. ;) (0 = type, 1 = min, 2= max)
		switch ($validator[$type])
		{
			case 'url':
			case 'csv':
				if ($validator[$type] == 'url')
				{
					$cfg_array[$config_name] = trim($cfg_array[$config_name]);

					if (!empty($cfg_array[$config_name]) && !preg_match('#^' . get_preg_expression('url') . '$#iu', $cfg_array[$config_name]))
					{
						$error[] = $language->lang('URL_INVALID', $language->lang($config_definition['lang']));
					}
				}
				else if ($validator[$type] == 'csv')
				{
					// Validate comma separated values
					$unfiltered_array = explode(',', $cfg_array[$config_name]);
					$filtered_array = array_filter($unfiltered_array);
					if (!empty($filtered_array) && count($unfiltered_array) !== count($filtered_array))
					{
						$error[] = $language->lang('CSV_INVALID', $language->lang($config_definition['lang']));
					}

				}
			// no break here

			case 'string':
				$length = utf8_strlen($cfg_array[$config_name]);

				// the column is a VARCHAR
				$validator[$max] = (isset($validator[$max])) ? min(255, $validator[$max]) : 255;

				if (isset($validator[$min]) && $length < $validator[$min])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_SHORT'], $user->lang[$config_definition['lang']], $validator[$min]);
				}
				else if (isset($validator[$max]) && $length > $validator[2])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LONG'], $user->lang[$config_definition['lang']], $validator[$max]);
				}
			break;

			case 'bool':
				$cfg_array[$config_name] = ($cfg_array[$config_name]) ? 1 : 0;
			break;

			case 'int':
				$cfg_array[$config_name] = (int) $cfg_array[$config_name];

				if (isset($validator[$min]) && $cfg_array[$config_name] < $validator[$min])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LOW'], $user->lang[$config_definition['lang']], $validator[$min]);
				}
				else if (isset($validator[$max]) && $cfg_array[$config_name] > $validator[$max])
				{
					$error[] = sprintf($user->lang['SETTING_TOO_BIG'], $user->lang[$config_definition['lang']], $validator[$max]);
				}

				if (strpos($config_name, '_max') !== false)
				{
					// Min/max pairs of settings should ensure that min <= max
					// Replace _max with _min to find the name of the minimum
					// corresponding configuration variable
					$min_name = str_replace('_max', '_min', $config_name);

					if (isset($cfg_array[$min_name]) && is_numeric($cfg_array[$min_name]) && $cfg_array[$config_name] < $cfg_array[$min_name])
					{
						// A minimum value exists and the maximum value is less than it
						$error[] = sprintf($user->lang['SETTING_TOO_LOW'], $user->lang[$config_definition['lang']], (int) $cfg_array[$min_name]);
					}
				}
			break;

			case 'email':
				if (!preg_match('/^' . get_preg_expression('email') . '$/i', $cfg_array[$config_name]))
				{
					$error[] = $user->lang['EMAIL_INVALID_EMAIL'];
				}
			break;

			// Absolute path
			case 'script_path':
				if (!$cfg_array[$config_name])
				{
					break;
				}

				$destination = str_replace('\\', '/', $cfg_array[$config_name]);

				if ($destination !== '/')
				{
					// Adjust destination path (no trailing slash)
					if (substr($destination, -1, 1) == '/')
					{
						$destination = substr($destination, 0, -1);
					}

					$destination = str_replace(array('../', './'), '', $destination);

					if ($destination[0] != '/')
					{
						$destination = '/' . $destination;
					}
				}

				$cfg_array[$config_name] = trim($destination);

			break;

			// Absolute path
			case 'lang':
				if (!$cfg_array[$config_name])
				{
					break;
				}

				$cfg_array[$config_name] = basename($cfg_array[$config_name]);

				if (!file_exists($phpbb_root_path . 'language/' . $cfg_array[$config_name] . '/'))
				{
					$error[] = $user->lang['WRONG_DATA_LANG'];
				}
			break;

			// Relative path (appended $phpbb_root_path)
			case 'rpath':
			case 'rwpath':
				if (!$cfg_array[$config_name])
				{
					break;
				}

				$destination = $cfg_array[$config_name];

				// Adjust destination path (no trailing slash)
				if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
				{
					$destination = substr($destination, 0, -1);
				}

				$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
				if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
				{
					$destination = '';
				}

				$cfg_array[$config_name] = trim($destination);

			// Path being relative (still prefixed by phpbb_root_path), but with the ability to escape the root dir...
			case 'path':
			case 'wpath':

				if (!$cfg_array[$config_name])
				{
					break;
				}

				$cfg_array[$config_name] = trim($cfg_array[$config_name]);

				// Make sure no NUL byte is present...
				if (strpos($cfg_array[$config_name], "\0") !== false || strpos($cfg_array[$config_name], '%00') !== false)
				{
					$cfg_array[$config_name] = '';
					break;
				}

				$path = $phpbb_root_path . $cfg_array[$config_name];

				if (!file_exists($path))
				{
					$error[] = sprintf($user->lang['DIRECTORY_DOES_NOT_EXIST'], $cfg_array[$config_name]);
				}

				if (file_exists($path) && !is_dir($path))
				{
					$error[] = sprintf($user->lang['DIRECTORY_NOT_DIR'], $cfg_array[$config_name]);
				}

				// Check if the path is writable
				if ($config_definition['validate'] == 'wpath' || $config_definition['validate'] == 'rwpath')
				{
					if (file_exists($path) && !$phpbb_filesystem->is_writable($path))
					{
						$error[] = sprintf($user->lang['DIRECTORY_NOT_WRITABLE'], $cfg_array[$config_name]);
					}
				}

			break;

			default:
				/**
				* Validate a config value
				*
				* @event core.validate_config_variable
				* @var	array	cfg_array	Array with config values
				* @var	string	config_name	Name of the config we validate
				* @var	array	config_definition	Array with the options for
				*									this config
				* @var	array	error		Array of errors, the errors should
				*							be strings only, language keys are
				*							not replaced afterwards
				* @since 3.1.0-a1
				*/
				$vars = array('cfg_array', 'config_name', 'config_definition', 'error');
				extract($phpbb_dispatcher->trigger_event('core.validate_config_variable', compact($vars)));
			break;
		}
	}

	return;
}

/**
* Checks whatever or not a variable is OK for use in the Database
* param mixed $value_ary An array of the form array(array('lang' => ..., 'value' => ..., 'column_type' =>))'
* param mixed $error The error array
*/
function validate_range($value_ary, &$error)
{
	global $user;

	$column_types = array(
		'BOOL'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => 1),
		'USINT'	=> array('php_type' => 'int',		'min' => 0, 				'max' => 65535),
		'UINT'	=> array('php_type' => 'int', 		'min' => 0, 				'max' => (int) 0x7fffffff),
		// Do not use (int) 0x80000000 - it evaluates to different
		// values on 32-bit and 64-bit systems.
		// Apparently -2147483648 is a float on 32-bit systems,
		// despite fitting in an int, thus explicit cast is needed.
		'INT'	=> array('php_type' => 'int', 		'min' => (int) -2147483648,	'max' => (int) 0x7fffffff),
		'TINT'	=> array('php_type' => 'int',		'min' => -128,				'max' => 127),

		'VCHAR'	=> array('php_type' => 'string', 	'min' => 0, 				'max' => 255),
	);
	foreach ($value_ary as $value)
	{
		$column = explode(':', $value['column_type']);
		if (!isset($column_types[$column[0]]))
		{
			continue;
		}
		else
		{
			$type = $column_types[$column[0]];
		}

		switch ($type['php_type'])
		{
			case 'string' :
				$max = (isset($column[1])) ? min($column[1],$type['max']) : $type['max'];
				if (utf8_strlen($value['value']) > $max)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LONG'], $user->lang[$value['lang']], $max);
				}
			break;

			case 'int':
				$min = (isset($column[1])) ? max($column[1],$type['min']) : $type['min'];
				$max = (isset($column[2])) ? min($column[2],$type['max']) : $type['max'];
				if ($value['value'] < $min)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_LOW'], $user->lang[$value['lang']], $min);
				}
				else if ($value['value'] > $max)
				{
					$error[] = sprintf($user->lang['SETTING_TOO_BIG'], $user->lang[$value['lang']], $max);
				}
			break;
		}
	}
}

/**
* Inserts new config display_vars into an exisiting display_vars array
* at the given position.
* Used by extensions.
*
* @param array $display_vars An array of existing config display vars
* @param array $add_config_vars An array of new config display vars
* @param array $where Where to place the new config vars,
*              before or after an exisiting config, as an array
*              of the form: array('after' => 'config_name') or
*              array('before' => 'config_name').
* @return array The array of config display vars
*/
function phpbb_insert_config_array($display_vars, $add_config_vars, $where)
{
	if (is_array($where) && array_key_exists(current($where), $display_vars))
	{
		$position = array_search(current($where), array_keys($display_vars)) + ((key($where) == 'before') ? 0 : 1);
		$display_vars = array_merge(
			array_slice($display_vars, 0, $position),
			$add_config_vars,
			array_slice($display_vars, $position)
		);
	}

	return $display_vars;
}
