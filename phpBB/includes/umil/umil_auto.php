<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 */

/**
 * Parameters which should be setup before calling this file:
 * @param string $mod_name The name of the mod to be displayed during installation.
 * @param string $language_file The language file which will be included when installing (should contain the $mod_name)
 * @param string $version_config_name The name of the config variable which will hold the currently installed version
 * @param array $versions The array of versions and actions within each.
 */

/**
 * Language entries that should exist in the $language_file that will be included:
 * $mod_name
 * 'INSTALL_' . $mod_name
 * 'INSTALL_' . $mod_name . '_CONFIRM'
 * 'UPDATE_' . $mod_name
 * 'UPDATE_' . $mod_name . '_CONFIRM'
 * 'UNINSTALL_' . $mod_name
 * 'UNINSTALL_' . $mod_name . '_CONFIRM'
 */

// You must run define('UMIL_AUTO', true) before calling this file.
/**
 * @ignore
 */
if (!defined('UMIL_AUTO'))
{
	exit;
}

/*
* Do not include common.php, the MOD author is required to include this.
*/
if (!defined('IN_PHPBB'))
{
	trigger_error('UMIL doesn\'t support the missing IN_PHPBB anymore. Please visit <a href="http://www.phpbb.com/mods/umil/update/">http://www.phpbb.com/mods/umil/update</a> on how to update your UMIF files.', E_USER_ERROR);
	exit;
}

// Add the language file if one was specified
if (isset($language_file))
{
	$user->add_lang($language_file);
}
if (!isset($user->lang[$mod_name]))
{
	// Prevent errors if the language key doesn't exist.
	$user->lang[$mod_name] = $mod_name;
}

// Use the Mod's logo if one was specified
if (isset($logo_img))
{
	$template->assign_var('LOGO_IMG', $phpbb_root_path . $logo_img);
}

// Display a login box if they are not logged in
if (!$user->data['is_registered'])
{
	login_box();
}

if (!class_exists('umil_frontend'))
{
    if (!file_exists($phpbb_root_path . 'umil/umil_frontend.' . $phpEx))
	{
		trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
	}

	include($phpbb_root_path . 'umil/umil_frontend.' . $phpEx);
}

$force_display_results = request_var('display_results', (defined('DEBUG') ? true : false));
$umil = new umil_frontend($mod_name, true, $force_display_results);

// Check after initiating UMIL.
if ($user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('FOUNDERS_ONLY');
}

// We will sort the actions to prevent issues from mod authors incorrectly listing the version numbers
uksort($versions, 'version_compare');

// Find the current version to install
$current_version = '0.0.0';
foreach ($versions as $version => $actions)
{
	$current_version = $version;
}

$template->assign_var('L_TITLE_EXPLAIN', ((isset($user->lang[$mod_name . '_EXPLAIN'])) ? $user->lang[$mod_name . '_EXPLAIN'] . '<br /><br />' : '') . sprintf($user->lang['VERSIONS'], $current_version, ((isset($config[$version_config_name])) ? $config[$version_config_name] : $user->lang['NONE'])));

$submit = (isset($_POST['submit'])) ? true : false;
$action = request_var('action', '');
$version_select = request_var('version_select', '');

$current_page = (strpos($user->page['page'], '?') !== false) ? substr($user->page['page'], 0, strpos($user->page['page'], '?')) : $user->page['page'];

$stages = array(
	'CONFIGURE'	=> array('url' => append_sid($phpbb_root_path . $current_page)),
	'CONFIRM',
	'ACTION',
);

if (!isset($options) || !is_array($options))
{
	$options = array();
}

$options = array(
	'legend1'			=> 'OPTIONS',
	'action'			=> array('lang' => 'ACTION', 'type' => 'custom', 'function' => 'umil_install_update_uninstall_select', 'explain' => false),
	'version_select'	=> array('lang' => 'VERSION_SELECT', 'type' => 'custom', 'function' => 'umil_version_select', 'explain' => true),
	'display_results'	=> array('lang' => 'DISPLAY_RESULTS', 'type' => 'radio:yes_no', 'explain' => true, 'default' => $force_display_results),
) + $options;

if (!$submit && !$umil->confirm_box(true))
{
	$umil->display_stages($stages);

	$umil->display_options($options);
	$umil->done();
}
else if (!$umil->confirm_box(true))
{
	$umil->display_stages($stages, 2);

	$hidden = array();
	foreach ($options as $key => $data)
	{
		$hidden[$key] = request_var($key, '', true);
	}

	switch ($action)
	{
		case 'install' :
			if (!isset($user->lang['INSTALL_' . $mod_name]))
			{
				$user->lang['INSTALL_' . $mod_name] = sprintf($user->lang['INSTALL_MOD'], $user->lang[$mod_name]);
				$user->lang['INSTALL_' . $mod_name . '_CONFIRM'] = sprintf($user->lang['INSTALL_MOD_CONFIRM'], $user->lang[$mod_name]);
			}
			$umil->confirm_box(false, 'INSTALL_' . $mod_name, $hidden);
		break;

		case 'update' :
			if (!isset($user->lang['UPDATE_' . $mod_name]))
			{
				$user->lang['UPDATE_' . $mod_name] = sprintf($user->lang['UPDATE_MOD'], $user->lang[$mod_name]);
				$user->lang['UPDATE_' . $mod_name . '_CONFIRM'] = sprintf($user->lang['UPDATE_MOD_CONFIRM'], $user->lang[$mod_name]);
			}
			$umil->confirm_box(false, 'UPDATE_' . $mod_name, $hidden);
		break;

		case 'uninstall' :
			if (!isset($user->lang['UNINSTALL_' . $mod_name]))
			{
				$user->lang['UNINSTALL_' . $mod_name] = sprintf($user->lang['UNINSTALL_MOD'], $user->lang[$mod_name]);
				$user->lang['UNINSTALL_' . $mod_name . '_CONFIRM'] = sprintf($user->lang['UNINSTALL_MOD_CONFIRM'], $user->lang[$mod_name]);
			}
			$umil->confirm_box(false, 'UNINSTALL_' . $mod_name, $hidden);
		break;
	}
}
else if ($umil->confirm_box(true))
{
	$umil->display_stages($stages, 3);

	$umil->run_actions($action, $versions, $version_config_name, $version_select);
	$umil->done();
}

// Shouldn't get here.
redirect($phpbb_root_path . $current_page);

function umil_install_update_uninstall_select($value, $key)
{
	global $config, $current_version, $user, $version_config_name;

	$db_version = (isset($config[$version_config_name])) ? $config[$version_config_name] : false;

	if ($db_version === false)
	{
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" checked="checked" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" disabled="disabled" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" disabled="disabled" /> ' . $user->lang['UNINSTALL'];
	}
	else if ($current_version == $db_version)
	{
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" disabled="disabled" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" disabled="disabled" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" checked="checked" /> ' . $user->lang['UNINSTALL'];
	}
	else if (version_compare($current_version, $db_version, '>'))
	{
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" disabled="disabled" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" checked="checked" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" /> ' . $user->lang['UNINSTALL'];
	}
	else
	{
		// Shouldn't ever get here...but just in case.
		return '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="install" /> ' . $user->lang['INSTALL'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="update" /> ' . $user->lang['UPDATE'] . '&nbsp;&nbsp;
		<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="uninstall" /> ' . $user->lang['UNINSTALL'];
	}
}

function umil_version_select($value, $key)
{
	global $user, $versions;

	$output = '<input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="" checked="checked" /> ' . $user->lang['IGNORE'] . ' &nbsp; ';
	$output .='<a href="#" onclick="if (document.getElementById(\'version_select_advanced\').style.display == \'none\') {document.getElementById(\'version_select_advanced\').style.display=\'block\'} else {document.getElementById(\'version_select_advanced\').style.display=\'none\'}">' . $user->lang['ADVANCED'] . '</a><br /><br />';

	$cnt = 0;
	$output .= '<table id="version_select_advanced" style="display: none;" cellspacing="0" cellpadding="0"><tr>';
	foreach ($versions as $version => $actions)
	{
		$cnt++;

		$output .= '<td><input id="' . $key . '" class="radio" type="radio" name="' . $key . '" value="' . $version . '" /> ' . $version . '</td>';

		if ($cnt % 4 == 0)
		{
			$output .= '</tr><tr>';
		}
	}
	$output .= '</tr></table>';

	return $output;
}
?>