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
*/

/*
	Remind if...
	... one or more language entries are missing
	
	Taking into consideration
	... admin is NOT able to change the field type later
    ... admin can NOT change field name after creation

	Admin is able to preview/test the input and output of a profile field at any time.

	If the admin adds a field, he needs to enter at least the default board language params. Without doing so, he
	is not able to activate the field.
	
	If the default board language is changed a check has to be made if the profile field language entries are
	still valid.

	TODO:
	* Show at profile view (yes/no)
	* Viewtopic Integration (Load Switch, Able to show fields with additional template vars populated if enabled)
	* Custom Validation (Regex) - not in 2.2
    * Fix novalue/default for dropdown boxes. These fields seem to get saved +1 in the database
*/


if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['USER']['CUSTOM_PROFILE_FIELDS'] = ($auth->acl_get('a_user')) ? "$filename$SID&amp;mode=manage" : '';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

if (!$auth->acl_get('a_user'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$user->add_lang('ucp');

$mode = (isset($_POST['add'])) ? 'create' : request_var('mode', '');
$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$create = (isset($_POST['create'])) ? TRUE : FALSE;
$error = $notify = array();

adm_page_header($user->lang['CUSTOM_PROFILE_FIELDS']);

// Define some default values for each field type
$default_values = array(
	FIELD_STRING	=> array('field_length' => 10, 'field_minlen' => 0, 'field_maxlen' => 20, 'field_validation' => '.*', 'field_novalue' => '', 'field_default_value' => ''),
	FIELD_TEXT		=> array('field_length' => '5|80', 'field_minlen' => 0, 'field_maxlen' => 1000, 'field_validation' => '.*', 'field_novalue' => '', 'field_default_value' => ''),
	FIELD_INT		=> array('field_length' => 5, 'field_minlen' => 0, 'field_maxlen' => 100, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0),
	FIELD_DATE		=> array('field_length' => 10, 'field_minlen' => 10, 'field_maxlen' => 10, 'field_validation' => '', 'field_novalue' => ' 0- 0-   0', 'field_default_value' => ' 0- 0-   0'),
	FIELD_BOOL		=> array('field_length' => 1, 'field_minlen' => 0, 'field_maxlen' => 0, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0),
	FIELD_DROPDOWN	=> array('field_length' => 0, 'field_minlen' => 0, 'field_maxlen' => 5, 'field_validation' => '', 'field_novalue' => 0, 'field_default_value' => 0),
);

$cp = new custom_profile_admin();

// Build Language array
// Based on this, we decide which elements need to be edited later and which language items are missing
$lang_defs = array();

$sql = 'SELECT lang_id, lang_iso
	FROM ' . LANG_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	// Make some arrays with all available languages
	$lang_defs['id'][] = $row['lang_id'];
	$lang_defs['iso'][$row['lang_iso']] = $row['lang_id'];
}
$db->sql_freeresult($result);

$sql = 'SELECT field_id, lang_id
	FROM ' . PROFILE_LANG_TABLE . '
		ORDER BY lang_id';
$result = $db->sql_query($sql);
	
while ($row = $db->sql_fetchrow($result))
{
	// Which languages are available for each item
	$lang_defs['entry'][$row['field_id']][] = $row['lang_id'];
}
$db->sql_freeresult($result);

// Have some fields been defined?
if (isset($lang_defs['entry']))
{
	foreach ($lang_defs['entry'] as $field_id => $field_ary)
	{
		// Fill an array with the languages that are missing for each field
		$lang_defs['diff'][$field_id] = array_diff($lang_defs['id'], $field_ary);
	}
}

if ($mode == '')
{
	trigger_error('INVALID_MODE');
}

if ($mode == 'create' || $mode == 'edit')
{
	$field_id = request_var('field_id', 0);
	$step = request_var('step', 1);
	$error = array();
	
	$submit = (isset($_REQUEST['next']) || isset($_REQUEST['prev'])) ? true : false;
	$update = (isset($_REQUEST['update'])) ? true : false;
	$save = (isset($_REQUEST['save'])) ? true : false;

	// We are editing... we need to grab basic things
	if ($mode == 'edit')
	{
		if (!$field_id)
		{
			trigger_error('No field id specified');
		}

		$sql = 'SELECT l.*, f.*
			FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . ' f 
			WHERE l.lang_id = ' . $lang_defs['iso'][$config['default_lang']] . "
				AND f.field_id = $field_id
				AND l.field_id = f.field_id";
		$result = $db->sql_query($sql);
		$field_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$field_row)
		{
			trigger_error('Profile field not found');
		}
		$field_type = $field_row['field_type'];

		// Get language entries
		$sql = 'SELECT * FROM ' . PROFILE_FIELDS_LANG_TABLE . ' 
			WHERE lang_id = ' . $lang_defs['iso'][$config['default_lang']] . "
				AND field_id = $field_id
				ORDER BY option_id ASC";
		$result = $db->sql_query($sql);

		$lang_options = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$lang_options[$row['option_id']] = $row['value'];
		}
		$db->sql_freeresult($result);

		$field_row['pf_preview'] = '';

		$s_hidden_fields = '<input type="hidden" name="field_id" value="' . $field_id . '" />';
	}
	else
	{
		// We are adding a new field, define basic params
		$lang_options = array();
		$field_row = array();
	
		$field_type = request_var('field_type', 0);
		
		if (!$field_type)
		{
			trigger_error('NO_FIELD_TYPE');
		}

		$field_row = array_merge($default_values[$field_type], array(
			'field_ident'		=> request_var('field_ident', ''),
			'field_required'	=> 0,
			'field_hide'		=> 0,
			'field_show_on_reg'	=> 0,
			'lang_name'			=> '',
			'lang_explain'		=> '',
			'lang_default_value'=> '',
			'pf_preview'		=> '')
		);

		$s_hidden_fields = '<input type="hidden" name="field_type" value="' . $field_type . '" />';
	}

    // $exclude contains the data that we gather in each ste
	$exclude = array(
		1	=> array('field_ident', 'lang_name', 'lang_explain'),
		2	=> array('field_length', 'pf_preview', 'field_maxlen', 'field_minlen', 'field_validation', 'field_novalue', 'field_default_value', 'field_required', 'field_show_on_reg', 'field_hide'),
		3	=> array('l_lang_name', 'l_lang_explain', 'l_lang_default_value', 'l_lang_options')
	);

	// Text-based fields require the lang_default_value to be excluded
	if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
	{
		$exclude[1][] = 'lang_default_value';
	}

	// option-specific fields require lang_options to be excluded
	if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
	{
		$exclude[1][] = 'lang_options';
	}

	$cp->vars['field_ident']			= request_var('field_ident', $field_row['field_ident']);
	$cp->vars['lang_name']			= request_var('field_ident', $field_row['lang_name']);
	$cp->vars['lang_explain']		= request_var('lang_explain', $field_row['lang_explain']);
	$cp->vars['lang_default_value']	= request_var('lang_default_value', $field_row['lang_default_value']);

	$options = request_var('lang_options', '');
	// If the user has submitted a form with options (i.e. dropdown field)
	if (!empty($options))
	{
		if (sizeof(explode("\n", $options)) == sizeof($lang_options) || $mode == 'create')
		{
			// The number of options in the field is equal to the number of options already in the database
			// Or we are creating a new dropdown list.
			$cp->vars['lang_options']	= explode("\n", $options);
		}
		else if ($mode == 'edit')
		{
			$cp->vars['lang_options']	= $lang_options;
			$error[] = 'You are not allowed to remove or add options within already existing profile fields';
		}
	}
	else
	{
		$cp->vars['lang_options']	= $lang_options;
	}

	// step 2
	foreach ($exclude[2] as $key)
	{
		if ($key == 'field_required' || $key == 'field_show_on_reg' || $key == 'field_hide')
		{
			// Are we creating or editing a field?
			$var = (!$submit && $step == 1) ? $field_row[$key] : request_var($key, 0);
			
			// Damn checkboxes...
			if (!$submit && $step == 1)
			{
				$_REQUEST[$key] = $var;
			}
		}
		else
		{
			$var = request_var($key, $field_row[$key]);
		}

		// Manipulate the intended variables a little bit if needed
		if ($field_type == FIELD_DROPDOWN && $key == 'field_maxlen')
		{
			// Get the number of options if this key is 'field_maxlen'
			$var = sizeof(explode("\n", request_var('lang_options', '')));
		}

		if ($field_type == FIELD_TEXT && $key == 'field_length')
		{
			if (isset($_REQUEST['rows']))
			{
				$cp->vars['rows'] = request_var('rows', 0);
				$cp->vars['columns'] = request_var('columns', 0);
				$var = $cp->vars['rows'] . '|' . $cp->vars['columns'];
			}
			else
			{
				$row_col = explode('|', $var);
				$cp->vars['rows'] = $row_col[0];
				$cp->vars['columns'] = $row_col[1];
			}
		}

		if ($field_type == FIELD_DATE && $key == 'field_default_value')
		{
			if (isset($_REQUEST['always_now']) || $var == 'now')
			{
				$now = getdate();

				$cp->vars['field_default_value_day'] = $now['mday'];
				$cp->vars['field_default_value_month'] = $now['mon'];
				$cp->vars['field_default_value_year'] = $now['year'];
				$var = $_POST['field_default_value'] = 'now';
			}
			else
			{
				if (isset($_REQUEST['field_default_value_day']))
				{
					$cp->vars['field_default_value_day'] = request_var('field_default_value_day', 0);
					$cp->vars['field_default_value_month'] = request_var('field_default_value_month', 0);
					$cp->vars['field_default_value_year'] = request_var('field_default_value_year', 0);
					$var = $_POST['field_default_value'] = sprintf('%2d-%2d-%4d', $cp->vars['field_default_value_day'], $cp->vars['field_default_value_month'], $cp->vars['field_default_value_year']);
				}
				else
				{
					list($cp->vars['field_default_value_day'], $cp->vars['field_default_value_month'], $cp->vars['field_default_value_year']) = explode('-', $var);
				}
			}	
		}

		$cp->vars[$key] = $var;
	}

	// step 3 - all arrays
	if ($mode == 'edit')
	{
		// Get language entries
		$sql = 'SELECT * FROM ' . PROFILE_FIELDS_LANG_TABLE . ' 
			WHERE lang_id <> ' . $lang_defs['iso'][$config['default_lang']] . "
				AND field_id = $field_id
			ORDER BY option_id ASC";
		$result = $db->sql_query($sql);

		$l_lang_options = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$l_lang_options[$row['lang_id']][$row['option_id']] = $row['value'];
		}
		$db->sql_freeresult($result);

	
		$sql = 'SELECT lang_id, lang_name, lang_explain, lang_default_value FROM ' . PROFILE_LANG_TABLE . ' 
			WHERE lang_id <> ' . $lang_defs['iso'][$config['default_lang']] . "
				AND field_id = $field_id
			ORDER BY lang_id ASC";
		$result = $db->sql_query($sql);

		$l_lang_name = $l_lang_explain = $l_lang_default_value = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$l_lang_name[$row['lang_id']] = $row['lang_name'];
			$l_lang_explain[$row['lang_id']] = $row['lang_explain'];
			$l_lang_default_value[$row['lang_id']] = $row['lang_default_value'];
		}
		$db->sql_freeresult($result);
	}
	
	foreach ($exclude[3] as $key)
	{
		$cp->vars[$key] = request_var($key, '');

		if (!$cp->vars[$key] && $mode == 'edit')
		{
			$cp->vars[$key] = $$key;
		}
		else if ($key == 'l_lang_options' && sizeof($cp->vars[$key]) > 1)
		{
			foreach ($cp->vars[$key] as $lang_id => $options)
			{
				$cp->vars[$key][$lang_id] = explode("\n", $options);
			}
		}
	}

	if ($submit && $step == 1)
	{
		// Check values for step 1
		if ($cp->vars['field_ident'] == '')
		{
            // Rename $user->lang['EMPTY_FIELD_NAME'] to $user->lang['EMPTY_FIELD_IDENT']
			$error[] = $user->lang['EMPTY_FIELD_IDENT'];
		}

		if (!preg_match('/^[a-z_]+$/', $cp->vars['field_ident']))
		{
			$error[] = $user->lang['INVALID_CHARS_FIELD_IDENT'];
		}

		if ($cp->vars['lang_name'] == '')
		{
			$error[] = $user->lang['EMPTY_USER_FIELD_IDENT'];
		}

		if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
		{
			if (!sizeof($cp->vars['lang_options']))
			{
				$error[] = 'No Entries defined';
			}
		}	
	}

	$user_error = false;
	if ($update && $step == 2)
	{
		// Validate Field
		$user_error = $cp->validate_profile_field($field_type, $cp->vars['pf_preview'], $cp->vars);
	}

	$step = (isset($_REQUEST['next'])) ? $step + 1 : ((isset($_REQUEST['prev'])) ? $step - 1 : $step);

	if (sizeof($error))
	{
		$step--;
		$submit = false;
	}

	if (isset($_REQUEST['prev']) || isset($_REQUEST['next']))
	{
		$update = false;
		$pf_preview = '';
		unset($_REQUEST['pf_preview']);
	}

	// Build up the specific hidden fields
	foreach ($exclude as $num => $key_ary)
	{
		if ($num == $step)
		{
			continue;
		}

		$s_hidden_fields .= build_hidden_fields($key_ary);
	}

	if (!sizeof($error))
	{
		if ($step == 3 && (sizeof($lang_defs['iso']) == 1 || $save))
		{
			save_profile_field($field_type, $mode);
		}
	}

?>
	
	<p><?php echo $user->lang['STEP_' . $step . '_EXPLAIN_' . strtoupper($mode)]; ?></p>

	<form name="add_profile_field" method="post" action="admin_profile.<?php echo "$phpEx$SID&amp;mode=$mode&amp;step=$step"; ?>">
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr>
		<th align="center" colspan="2"><?php echo $user->lang['STEP_' . $step . '_TITLE_' . strtoupper($mode)]; ?></th>
	</tr>
<?php

	if (sizeof($error))
	{

?>
	<tr>
		<td class="row3" colspan="2" align="center"><span style="color:red"><?php echo implode('<br />', $error); ?></span></td>
	</tr>
<?php

	}

	// Now go through the steps
	switch ($step)
	{
		// Create basic options - only small differences between field types
		case 1: 
	
			// Build common create options
?>
			<tr>
				<td class="row1"><b><?php echo $user->lang['FIELD_TYPE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FIELD_TYPE_EXPLAIN']; ?></span></td>
				<td class="row2"><b><?php echo $user->lang['FIELD_' . strtoupper($cp->profile_types[$field_type])]; ?></b></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['FIELD_IDENT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FIELD_IDENT_EXPLAIN']; ?></span></td>
				<td class="row2"><input class="post" type="text" name="field_ident" size="20" value="<?php echo $cp->vars['field_ident']; ?>" /></td>
			</tr>
			<tr>
				<th align="center" colspan="2"><?php echo sprintf($user->lang['LANG_SPECIFIC_OPTIONS'], $config['default_lang']); ?></th>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['USER_FIELD_NAME']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="lang_name" size="20" value="<?php echo $cp->vars['lang_name']; ?>" /></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['FIELD_DESCRIPTION']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FIELD_DESCRIPTION_EXPLAIN']; ?></span></td>
				<td class="row2"><textarea name="lang_explain" rows="3" cols="80"><?php echo $cp->vars['lang_explain']; ?></textarea></td>
			</tr>
<?php
			// String and Text needs to set default values here...
			if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
			{
?>
				<tr>
					<td class="row1"><b><?php echo $user->lang['DEFAULT_VALUE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang[strtoupper($cp->profile_types[$field_type]) . '_DEFAULT_VALUE_EXPLAIN']; ?></span></td>
					<td class="row2"><?php echo ($field_type == FIELD_STRING) ? '<input class="post" type="text" name="lang_default_value" size="20" value="' . $cp->vars['lang_default_value'] . '" />' : '<textarea name="lang_default_value" rows="5" cols="80">' . $cp->vars['lang_default_value'] . '</textarea>'; ?></td>
				</tr>
<?php
			}
			
			if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
			{
				// Initialize these array elements if we are creating a new field
				if (!sizeof($cp->vars['lang_options']))
				{
					if ($field_type == FIELD_BOOL)
					{
						// No options have been defined for a boolean field.
						$cp->vars['lang_options'][0] = '';
						$cp->vars['lang_options'][1] = '';
					}
					else
					{
						// No options have been defined for the dropdown menu
						$cp->vars['lang_options'] = array();
					}
				}
?>
				<tr>
					<td class="row1"><b><?php echo $user->lang['ENTRIES']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang[strtoupper($cp->profile_types[$field_type]) . '_ENTRIES_EXPLAIN']; ?></span></td>
					<td class="row2"><?php echo ($field_type == FIELD_DROPDOWN) ? '<textarea name="lang_options" rows="5" cols="80">' . implode("\n", $cp->vars['lang_options']) . '</textarea>' : '<table border=0><tr><td><input name="lang_options[0]" size="20" value="' . $cp->vars['lang_options'][0] . '" class="post" /></td><td>[ ' . $user->lang['FIRST_OPTION'] . ' ]</td></tr><tr><td><input name="lang_options[1]" size="20" value="' . $cp->vars['lang_options'][1] . '" class="post" /></td><td>[ ' . $user->lang['SECOND_OPTION'] . ' ]</td></tr></table>'; ?></td>
				</tr>
<?php
			}
?>
			<tr>
				<td width="100%" colspan="2" class="cat" align="right"><input class="btnlite" type="submit" name="next" value="<?php echo $user->lang['PROFILE_TYPE_OPTIONS']; ?>" /></td>
			</tr>
			<?php echo $s_hidden_fields; ?>
			</table>
			</form>
<?php
			break;

		case 2:
?>
			<tr>
				<td class="row1"><b><?php echo $user->lang['REQUIRED_FIELD']; ?></b><br /><span class="gensmall"><?php echo $user->lang['REQUIRED_FIELD_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="checkbox" name="field_required" value="1"<?php echo (($cp->vars['field_required']) ? ' checked="checked"' : ''); ?> /></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['DISPLAY_AT_REGISTRATION']; ?></b></td>
				<td class="row2"><input type="checkbox" name="field_show_on_reg" value="1"<?php echo (($cp->vars['field_show_on_reg']) ? ' checked="checked"' : ''); ?> /></td>
			</tr>
			<tr>
				<td class="row1"><b><?php echo $user->lang['HIDE_PROFILE_FIELD']; ?></b><br /><span class="gensmall"><?php echo $user->lang['HIDE_PROFILE_FIELD_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="checkbox" name="field_hide" value="1"<?php echo (($cp->vars['field_hide']) ? ' checked="checked"' : ''); ?> /></td>
			</tr>
			
<?php
			// Build options based on profile type
			$function = 'get_' . $cp->profile_types[$field_type] . '_options';
			$options = $cp->$function();
			foreach ($options as $num => $option_ary)
			{
?>
				<tr>
					<td class="row1"><b><?php echo $option_ary['TITLE']; ?>: </b><?php echo (isset($option_ary['EXPLAIN'])) ? '<br /><span class="gensmall">' . $option_ary['EXPLAIN'] . '</span>' : ''; ?></td>
					<td class="row2"><?php echo $option_ary['FIELD']; ?></td>
				</tr>
<?php
			}
?>
			<tr>
				<td width="100%" colspan="2" class="cat"><table border="0" width="100%"><tr><td align="left"><input class="btnlite" type="submit" name="prev" value="<?php echo $user->lang['PROFILE_BASIC_OPTIONS']; ?>" /></td><td align="right"><input class="btnlite" type="submit" name="update" value="<?php echo $user->lang['UPDATE_PREVIEW']; ?>" />&nbsp;<input class="btnmain" type="submit" name="next" value="<?php echo (sizeof($lang_defs['iso']) == 1) ? $user->lang['SAVE'] : $user->lang['PROFILE_LANG_OPTIONS']; ?>" /></td></tr></table></td>
			</tr>
			<?php echo $s_hidden_fields; ?>
			</table>

			<br /><br />
			<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
			<tr>
				<th align="center" colspan="2"><?php echo $user->lang['PREVIEW_PROFILE_FIELD']; ?></th>
			</tr>
<?php 
			if (!empty($user_error) || $update) 
			{
				// If not and only showing common error messages, use this one
				switch ($user_error)
				{
					case 'FIELD_INVALID_DATE':
					case 'FIELD_REQUIRED':
						$user_error = sprintf($user->lang[$user_error], $cp->vars['lang_name']);
						break;
					case 'FIELD_TOO_SHORT':
					case 'FIELD_TOO_SMALL':
						$user_error = sprintf($user->lang[$user_error], $cp->vars['lang_name'], $cp->vars['field_minlen']);
						break;
					case 'FIELD_TOO_LONG':
					case 'FIELD_TOO_LARGE':
						$user_error = sprintf($user->lang[$user_error], $cp->vars['lang_name'], $cp->vars['field_maxlen']);
						break;
					case 'FIELD_INVALID_CHARS':
						switch ($cp->vars['field_validation'])
						{
							case '[0-9]+':
								$user_error = sprintf($user->lang[$user_error . '_NUMBERS_ONLY'], $cp->vars['lang_name']);
								break;
							case '[\w]+':
								$user_error = sprintf($user->lang[$user_error . '_ALPHA_ONLY'], $cp->vars['lang_name']);
								break;
							case '[\w_\+\. \-\[\]]+':
								$user_error = sprintf($user->lang[$user_error . '_SPACERS_ONLY'], $cp->vars['lang_name']);
								break;
						}

					default:
						$user_error = '';
				}

?>				<tr>
					<td class="row3" colspan="2"><?php echo (!empty($user_error)) ? '<span style="color:red">' . $user_error . '</span>' : '<span style="color:green">' . $user->lang['EVERYTHING_OK'] . '</span>'; ?></td>
				</tr>
<?php
			}
			
			$field_data = array(
				'lang_name'		=> $cp->vars['lang_name'],
				'lang_explain'	=> $cp->vars['lang_explain'],
				'lang_id'		=> $lang_defs['iso'][$config['default_lang']],
				'field_id'		=> 1,

				'lang_default_value'	=> $cp->vars['lang_default_value'],
				'field_default_value'	=> $cp->vars['field_default_value'],
				'field_ident'			=> 'preview',
				'field_type'			=> $field_type,

				'field_length'	=> $cp->vars['field_length'],
				'field_maxlen'	=> $cp->vars['field_maxlen'],
				'lang_options'	=> $cp->vars['lang_options']
			);

			preview_field($field_data);
?>			
			<tr>
				<td width="100%" colspan="2" class="cat"><input class="btnlite" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
			</tr>
			</table>
			</form>
<?php
			break;

		// Define remaining language variables
		case 3: 
			$options = build_language_options($field_type, $mode);

			foreach ($options as $lang_id => $lang_ary)
			{
?>
				<tr>
					<td align="center" class="row3" colspan="2"><?php echo ($lang_ary['lang_iso'] == $config['default_lang']) ? sprintf($user->lang['DEFAULT_ISO_LANGUAGE'], $config['default_lang']) : sprintf($user->lang['ISO_LANGUAGE'], $lang_ary['lang_iso']) ?></td>
				</tr>
<?php
				foreach ($lang_ary['fields'] as $field_ident => $field_ary)
				{
?>
				<tr>
					<td class="row1"><b><?php echo $field_ary['TITLE']; ?>: </b><?php echo (isset($field_ary['EXPLAIN'])) ? '<br /><span class="gensmall">' . $field_ary['EXPLAIN'] . '</span>' : ''; ?></td>
					<td class="row2"><?php echo $field_ary['FIELD']; ?></td>
				</tr>
<?php			
				}
			}
?>
			<tr>
				<td width="100%" colspan="2" class="cat"><table border="0" width="100%"><tr><td align="left"><input class="btnlite" type="submit" name="prev" value="<?php echo $user->lang['PROFILE_TYPE_OPTIONS']; ?>" /></td><td align="right"><div style="align:right"><input type="submit" name="save" class="btnmain" value="<?php echo $user->lang['SAVE']; ?>" /></td></tr></table></td>
			</tr>
			<?php echo $s_hidden_fields; ?>
			</table>
			</form>
<?php
			break;
	}
}

// Delete field
if ($mode == 'delete')
{
	$confirm = (isset($_POST['confirm'])) ? true : false;
	$cancel = (isset($_POST['cancel'])) ? true : false;
	$field_id = request_var('field_id', 0);

	if (!$field_id)
	{
		trigger_error('INVALID_MODE');
	}

	if ($confirm)
	{
		$sql = 'SELECT field_ident 
			FROM ' . PROFILE_FIELDS_TABLE . " 
			WHERE field_id = $field_id";
		$result = $db->sql_query($sql);
		$field_ident = $db->sql_fetchfield('field_ident', 0, $result);
		$db->sql_freeresult($result);

		$db->sql_query('DELETE FROM ' . PROFILE_FIELDS_TABLE . " WHERE field_id = $field_id");
		$db->sql_query('DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . " WHERE field_id = $field_id");
		$db->sql_query('DELETE FROM ' . PROFILE_LANG_TABLE . " WHERE field_id = $field_id");
		$db->sql_query('ALTER TABLE ' . PROFILE_DATA_TABLE . " DROP $field_ident");

		$order = 0;

		$sql = 'SELECT *
			FROM ' . PROFILE_FIELDS_TABLE . '
			ORDER BY field_order';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$order++;
			if ($row['field_order'] != $order)
			{
				$sql = 'UPDATE ' . 
					PROFILE_FIELDS_TABLE . " 
					SET field_order = $order 
					WHERE field_id = {$row['field_id']}";
				$db->sql_query($sql);
			}
		}

		// TODO: add_log
		trigger_error($user->lang['DELETED_PROFILE_FIELD']);
	}
	else if (!$cancel)
	{
		$l_message = '<form method="post" action="admin_profile.' . $phpEx . $SID . '&amp;mode=delete&amp;field_id=' . $field_id . '">' . $user->lang['CONFIRM_DELETE_PROFILE_FIELD'] . '<br /><br /><input class="btnlite" type="submit" name="confirm" value="' . $user->lang['YES'] . '" />&nbsp;&nbsp;<input class="btnlite" type="submit" name="cancel" value="' . $user->lang['NO'] . '" /></form>';

		adm_page_message($user->lang['CONFIRM'], $l_message, false, false);

		adm_page_footer();
	}
	
	$mode = 'manage';
}

if ($mode == 'activate')
{
	$field_id = request_var('field_id', 0);

	if (!$field_id)
	{
		trigger_error('INVALID_MODE');
	}
	
	$sql = 'SELECT lang_id 
		FROM ' . LANG_TABLE . " 
		WHERE lang_iso = '{$config['default_lang']}'";
	$result = $db->sql_query($sql);
	$default_lang_id = (int) $db->sql_fetchfield('lang_id', 0, $result);
	$db->sql_freeresult($result);

	if (!in_array($default_lang_id, $lang_defs['entry'][$field_id]))
	{
		trigger_error('DEFAULT_LANGUAGE_NOT_FILLED');
	}

	$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . " 
		SET field_active = 1 
		WHERE field_id = $field_id";
	$db->sql_query($sql);

	// TODO: add_log
	trigger_error($user->lang['PROFILE_FIELD_ACTIVATED']);
}

if ($mode == 'deactivate')
{
	$field_id = request_var('field_id', 0);

	if (!$field_id)
	{
		trigger_error('INVALID_MODE');
	}
	
	$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
		SET field_active = 0 
		WHERE field_id = $field_id";
	$db->sql_query($sql);

	// TODO: add_log
	trigger_error($user->lang['PROFILE_FIELD_DEACTIVATED']);
}

if ($mode == 'move_up' || $mode == 'move_down')
{
	$field_order = request_var('order', 0);
	$order_total = $field_order * 2 + (($mode == 'move_up') ? -1 : 1);

	$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
		SET field_order = $order_total - field_order
		WHERE field_order IN ($field_order, " . (($mode == 'move_up') ? $field_order - 1 : $field_order + 1) . ')';
	$db->sql_query($sql);

	$mode = 'manage';
}

if ($mode == 'manage')
{
?>
	<form name="profile_fields" method="post" action="admin_profile.<?php echo "$phpEx$SID"; ?>">
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr> 
		<th nowrap="nowrap">Name</th>
		<th nowrap="nowrap">Type</th>
		<th colspan="3" nowrap="nowrap">Options</th>
		<th nowrap="nowrap">Reorder</th>
	</tr>
<?php
	$sql = 'SELECT *
		FROM ' . PROFILE_FIELDS_TABLE . '
		ORDER BY field_order';
	$result = $db->sql_query($sql);

	$row_class = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

		$active_lang = (!$row['field_active']) ? 'ACTIVATE' : 'DEACTIVATE';
		$active_value = (!$row['field_active']) ? 'activate' : 'deactivate';
		$id = $row['field_id'];
?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $row['field_ident']; ?></td>
		<td class="<?php echo $row_class; ?>"><?php echo $user->lang['FIELD_' . strtoupper($cp->profile_types[$row['field_type']])]; ?></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_profile.<?php echo $phpEx . $SID; ?>&amp;mode=<?php echo $active_value; ?>&amp;field_id=<?php echo $id; ?>"><?php echo $user->lang[$active_lang]; ?></a></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_profile.<?php echo $phpEx . $SID; ?>&amp;mode=edit&amp;field_id=<?php echo $id; ?>"><?php echo ((sizeof($lang_defs['diff'][$row['field_id']])) ? '<span style="color:red">' . $user->lang['EDIT'] . '</span>' : $user->lang['EDIT']) . '</a>' . ((sizeof($lang_defs['diff'][$row['field_id']])) ? '</span>' : ''); ?></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_profile.<?php echo $phpEx . $SID; ?>&amp;mode=delete&amp;field_id=<?php echo $id; ?>"><?php echo $user->lang['DELETE']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="admin_profile.<?php echo $phpEx . $SID; ?>&amp;mode=move_up&amp;order=<?php echo $row['field_order']; ?>"><?php echo $user->lang['MOVE_UP']; ?></a> | <a href="admin_profile.<?php echo $phpEx . $SID; ?>&amp;mode=move_down&amp;order=<?php echo $row['field_order']; ?>"><?php echo $user->lang['MOVE_DOWN']; ?></a></td>
	</tr>
<?php
	}
	$db->sql_freeresult($result);

	$s_select_type = '';
	foreach ($cp->profile_types as $key => $value)
	{
		$s_select_type .= '<option value="' . $key . '">' . $user->lang['FIELD_' . strtoupper($value)] . '</option>';
	}
?>
	<tr>
		<td class="cat" colspan="7"><input class="post" type="text" name="field_ident" size="20" /> <select name="field_type"><?php echo $s_select_type; ?></select> <input class="btnlite" type="submit" name="add" value="<?php echo $user->lang['CREATE_NEW_FIELD']; ?>" /></td>
	</tr>
	</table>
	</form>
<?php
}

adm_page_footer();


function preview_field($field_data)
{
	global $cp;

	$field = $cp->process_field_row('preview', $field_data);
?>
	<tr> 
		<td class="row1"><b><?php echo $field_data['lang_name']; ?>: </b><?php echo (!empty($field_data['lang_explain'])) ? '<br /><span class="gensmall">' . $field_data['lang_explain'] . '</span>' : ''; ?></td> 
		<td class="row2"><?php echo $field; ?></td> 
	</tr>
<?php
}

// Build all Language specific options
function build_language_options($field_type, $mode = 'create')
{
	global $user, $config, $db, $cp;

	$sql = 'SELECT lang_id, lang_iso 
		FROM ' . LANG_TABLE . "
		WHERE lang_iso <> '" . $config['default_lang'] . "'";
	$result = $db->sql_query($sql);

	$languages = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$languages[$row['lang_id']] = $row['lang_iso'];
	}
	$db->sql_freeresult($result);
		
	$options = array();
	$options['lang_name'] = 'string';
	if (!empty($cp->vars['lang_explain']))
	{
		$options['lang_explain'] = 'text';
	}
	
	switch ($field_type)
	{
		case FIELD_BOOL:
			$options['lang_options'] = 'two_options';
			break;
		case FIELD_DROPDOWN:
			$options['lang_options'] = 'optionfield';
			break;
		case FIELD_TEXT:
		case FIELD_STRING:
			if (!empty($cp->vars['lang_default_value']))
			{
				$options['lang_default_value'] = ($field_type == FIELD_STRING) ? 'string' : 'text';
			}
			break;
	}
	
	$lang_options = array();

	foreach ($options as $field => $field_type)
	{
		$lang_options[1]['lang_iso'] = $config['default_lang'];
		$lang_options[1]['fields'][$field] = array(
			'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
			'FIELD'		=> '<b>' . ((is_array($cp->vars[$field])) ? implode('<br />', $cp->vars[$field]) : str_replace("\n", '<br />', $cp->vars[$field])) . '</b>'
		);

		if (isset($user->lang['CP_' . strtoupper($field) . '_EXPLAIN']))
		{
			$lang_options[1]['fields'][$field]['EXPLAIN'] = $user->lang['CP_' . strtoupper($field) . '_EXPLAIN'];
		}
	}

	foreach ($languages as $lang_id => $lang_iso)
	{
		$lang_options[$lang_id]['lang_iso'] = $lang_iso;
		foreach ($options as $field => $field_type)
		{
			$value = ($mode == 'create') ? request_var('l_' . $field, '') : $cp->vars['l_' . $field];
				 
			if ($field == 'lang_options')
			{
				$var = ($mode == 'create') ? $cp->vars['lang_options'] : $cp->vars['lang_options'][$lang_id];
					
				switch ($field_type)
				{
					case 'two_options':

						$lang_options[$lang_id]['fields'][$field] = array(
							'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
							'FIELD'		=> '<table border=0><tr><td><span class="genmed">' . $user->lang['FIRST_OPTION'] . ': </span></td><td><input name="l_' . $field . '[' . $lang_id . '][]" size="20" value="' . ((isset($value[$lang_id][0])) ? $value[$lang_id][0] : $var[0]) . '" class="post" /></td></tr><tr><td><span class="genmed">' . $user->lang['SECOND_OPTION'] . ': </span></td><td><input name="l_' . $field . '[' . $lang_id . '][]" size="20" value="' . ((isset($value[$lang_id][1])) ? $value[$lang_id][1] : $var[1]) . '" class="post" /></td></tr></table>'
						);
						break;

					case 'optionfield':

						$lang_options[$lang_id]['fields'][$field] = array(
							'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
							'FIELD'		=> '<textarea name="l_' . $field . '[' . $lang_id . ']" rows="7" cols="80">' . ((isset($value[$lang_id])) ? implode("\n", $value[$lang_id]) : implode("\n", $var)) . '</textarea>'
						);
						break;
				}
				
				if (isset($user->lang['CP_' . strtoupper($field) . '_EXPLAIN']))
				{
					$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $user->lang['CP_' . strtoupper($field) . '_EXPLAIN'];
				}
			}
			else
			{
				$var = ($mode == 'create') ? $cp->vars[$field] : $cp->vars[$field][$lang_id];

				$lang_options[$lang_id]['fields'][$field] = array(
					'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
					'FIELD'		=> ($field_type == 'string') ? '<input class="post" type="text" name="l_' . $field . '[' . $lang_id . ']" value="' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '" size="20" />' : '<textarea name="l_' . $field . '[' . $lang_id . ']" rows="3" cols="80">' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '</textarea>'
				);
		
				if (isset($user->lang['CP_' . strtoupper($field) . '_EXPLAIN']))
				{
					$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $user->lang['CP_' . strtoupper($field) . '_EXPLAIN'];
				}
			}
		}
	}

	return $lang_options;
}

function save_profile_field($field_type, $mode = 'create')
{
	global $cp, $db, $config, $user, $lang_defs;

	$field_id = request_var('field_id', 0);

	// Collect all informations, if something is going wrong, abort the operation
	$profile_sql = $profile_lang = $empty_lang = $profile_lang_fields = array();

	$default_lang_id = $lang_defs['iso'][$config['default_lang']];

	if ($mode == 'create')
	{
		$result = $db->sql_query('SELECT MAX(field_order) as max_field_order FROM ' . PROFILE_FIELDS_TABLE);
		$new_field_order = (int) $db->sql_fetchfield('max_field_order', 0, $result);
		$db->sql_freeresult($result);
		
		$field_ident = $cp->vars['field_ident'];
	}

	// Save the field
	$profile_fields = array(
		'field_length'		=> $cp->vars['field_length'],
		'field_minlen'		=> $cp->vars['field_minlen'],
		'field_maxlen'		=> $cp->vars['field_maxlen'],
		'field_novalue'		=> $cp->vars['field_novalue'],
		'field_default_value'	=> $cp->vars['field_default_value'],
		'field_validation'	=> $cp->vars['field_validation'],
		'field_required'	=> $cp->vars['field_required'],
		'field_show_on_reg'	=> $cp->vars['field_show_on_reg'],
		'field_hide'		=> $cp->vars['field_hide']
	);

	if ($mode == 'create')
	{
		$profile_fields += array(
			'field_type'		=> $field_type,
			'field_ident'		=> $field_ident,
			'field_order'		=> $new_field_order + 1,
			'field_active'		=> 1
		);

		$db->sql_query('INSERT INTO ' . PROFILE_FIELDS_TABLE . ' ' . $db->sql_build_array('INSERT', $profile_fields));

		$field_id = $db->sql_nextid();
	}
	else
	{
		$db->sql_query('UPDATE ' . PROFILE_FIELDS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $profile_fields) . "
			WHERE field_id = $field_id");
	}
		
	if ($mode == 'create')
	{
		// We are defining the biggest common value, because of the possibility to edit the min/max values of each field.
		$sql = 'ALTER TABLE ' . PROFILE_DATA_TABLE . " ADD $field_ident ";
		switch ($field_type)
		{
			case FIELD_STRING:
				$sql .= ' VARCHAR(255) DEFAULT NULL NULL';
				break;

			case FIELD_DATE:
				$sql .= 'VARCHAR(10) DEFAULT NULL NULL';
				break;

			case FIELD_TEXT:
				$sql .= "TEXT NULL,
					ADD {$field_ident}_bbcode_uid VARCHAR(5) NOT NULL,
					ADD {$field_ident}_bbcode_bitfield INT(11) UNSIGNED";
				break;

			case FIELD_BOOL:
				$sql .= 'TINYINT(2) DEFAULT NULL NULL';
				break;
		
			case FIELD_DROPDOWN:
				$sql .= 'MEDIUMINT(8) DEFAULT NULL NULL';
				break;

			case FIELD_INT:
				$sql .= 'BIGINT(20) DEFAULT NULL NULL';
				break;
		}
		$profile_sql[] = $sql;
	}

	$sql_ary = array(
		'lang_name'		=> $cp->vars['lang_name'],
		'lang_explain'	=> $cp->vars['lang_explain'],
		'lang_default_value'	=> $cp->vars['lang_default_value']
	);

	if ($mode == 'create')
	{
		$sql_ary['field_id'] = $field_id;
		$sql_ary['lang_id'] = $default_lang_id;
	
		$profile_sql[] = 'INSERT INTO ' . PROFILE_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	}
	else
	{
		update_insert(PROFILE_LANG_TABLE, $sql_ary, array('field_id' => $field_id, 'lang_id' => $default_lang_id));
	}

	if (sizeof($cp->vars['l_lang_name']))
	{
		foreach ($cp->vars['l_lang_name'] as $lang_id => $data)
		{
			if (($cp->vars['lang_name'] != '' && $cp->vars['l_lang_name'][$lang_id] == '')
				|| ($cp->vars['lang_explain'] != '' && $cp->vars['l_lang_explain'][$lang_id] == '')
				|| ($cp->vars['lang_default_value'] != '' && $cp->vars['l_lang_default_value'][$lang_id] == ''))
			{
				$empty_lang[$lang_id] = true;
				break;
			}
				
			if (!isset($empty_lang[$lang_id]))
			{
				$profile_lang[] = array(
					'field_id'		=> $field_id,
					'lang_id'		=> $lang_id,
					'lang_name'		=> $cp->vars['l_lang_name'][$lang_id],
					'lang_explain'	=> $cp->vars['l_lang_explain'][$lang_id],
					'lang_default_value'	=> $cp->vars['l_lang_default_value'][$lang_id]
				);
			}
		}
	}

	$cp->vars['l_lang_name']			= request_var('l_lang_name', '');
	$cp->vars['l_lang_explain']			= request_var('l_lang_explain', '');
	$cp->vars['l_lang_default_value']	= request_var('l_lang_default_value', '');
	$cp->vars['l_lang_options']			= request_var('l_lang_options', '');

	if (!empty($cp->vars['lang_options']))
	{
		if (!is_array($cp->vars['lang_options']))
		{
			$cp->vars['lang_options'] = explode("\n", $cp->vars['lang_options']);
		}

		foreach ($cp->vars['lang_options'] as $option_id => $value)
		{
			$sql_ary = array(
				'field_type'	=> (int) $field_type,
				'value'			=> $value
			);

			if ($mode == 'create')
			{
				$sql_ary['field_id'] = $field_id;
				$sql_ary['lang_id'] = $default_lang_id;
				$sql_ary['option_id'] = (int) $option_id;

				$profile_sql[] = 'INSERT INTO ' . PROFILE_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			}
			else
			{
				update_insert(PROFILE_FIELDS_LANG_TABLE, $sql_ary, array(
					'field_id' => $field_id,
					'lang_id' => (int) $default_lang_id,
					'option_id' => (int) $option_id)
				);
			}
		}
	}

	// TODO: sizeof() returns 1 if it's argument is something else than an array. It also seems to do that on empty array elements :?
	if (sizeof($cp->vars['l_lang_options']))
	{
		foreach ($cp->vars['l_lang_options'] as $lang_id => $lang_ary)
		{
			if (!is_array($lang_ary))
			{
				$lang_ary = explode("\n", $lang_ary);
			}

			if (sizeof($lang_ary) != sizeof($cp->vars['lang_options']))
			{
				$empty_lang[$lang_id] = true;
			}

			if (!isset($empty_lang[$lang_id]))
			{
				foreach ($lang_ary as $option_id => $value)
				{
					$profile_lang_fields[] = array(
						'field_id'		=> (int) $field_id,
						'lang_id'		=> (int) $lang_id,
						'option_id'		=> (int) $option_id,
						'field_type'	=> (int) $field_type,
						'value'			=> $value
					);
				}
			}
		}
	}

	foreach ($profile_lang as $sql)
	{
		if ($mode == 'create')
		{
			$profile_sql[] = 'INSERT INTO ' . PROFILE_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql);
		}
		else
		{
			$lang_id = $sql['lang_id'];
			unset($sql['lang_id'], $sql['field_id']);
			update_insert(PROFILE_LANG_TABLE, $sql, array('lang_id' => (int) $lang_id, 'field_id' => $field_id));
		}
	}

	if (sizeof($profile_lang_fields))
	{
		foreach ($profile_lang_fields as $sql)
		{
			if ($mode == 'create')
			{
				$profile_sql[] = 'INSERT INTO ' . PROFILE_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql);
			}
			else
			{
				$lang_id = $sql['lang_id'];
				$option_id = $sql['option_id'];
				unset($sql['lang_id'], $sql['field_id'], $sql['option_id']);
				update_insert(PROFILE_FIELDS_LANG_TABLE, $sql, array(
					'lang_id'	=> $lang_id, 
					'field_id'	=> $field_id,
					'option_id'	=> $option_id)
				);
			}
		}
	}

//		$db->sql_transaction();
	if ($mode == 'create')
	{
		foreach ($profile_sql as $sql)
		{
			$db->sql_query($sql);
		}
	}
//	$db->sql_transaction('commit');

	// TODO: add_log
	trigger_error($user->lang['ADDED_PROFILE_FIELD']);
}

// Update, then insert if not successfull
function update_insert($table, $sql_ary, $where_fields)
{
	global $db;

	$where_sql = array();
	foreach ($where_fields as $key => $value)
	{
		$where_sql[] = $key . ' = ' . ((is_string($value)) ? "'" . $db->sql_escape($value) . "'" : $value);
	}

	$db->sql_return_on_error(true);
	
	$sql = "UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql_ary) . ' 
		WHERE ' . implode(' AND ', $where_sql);
	$result = $db->sql_query($sql);

	$db->sql_return_on_error(false);
	
	if (!$result)
	{
		$sql_ary = array_merge($where_fields, $sql_ary);
		$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql_ary));
	}

}

function build_hidden_fields($key_ary)
{
	$hidden_fields = '';

	foreach ($key_ary as $key)
	{
		$var = isset($_REQUEST[$key]) ? $_REQUEST[$key] : false;

		if ($var === false)
		{
			continue;
		}

		if (is_array($var))
		{
			foreach ($var as $num => $__var)
			{
				if (is_array($__var))
				{
					foreach ($__var as $_num => $___var)
					{
						$hidden_fields .= '<input type="hidden" name="' . $key . '[' . $num . '][' . $_num . ']" value="' . stripslashes(htmlspecialchars($___var)) . '" />' . "\n";
					}
				}
				else
				{
					$hidden_fields .= '<input type="hidden" name="' . $key . '[' . $num . ']" value="' . stripslashes(htmlspecialchars($__var)) . '" />' . "\n";
				}
			}
		}
		else
		{
			$hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . stripslashes(htmlspecialchars($var)) . '" />' . "\n";
		}
	}
	return $hidden_fields;
}

?>