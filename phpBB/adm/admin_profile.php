<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_profile.php
// STARTED   : Sun Apr 20, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

/*
	Remind if...
	... one or more language entries are missing
	
	Taking into consideration
	... admin is NOT able to change the field type later

	Admin is able to preview/test the input and output of a profile field at every time.

	If the admin adds a field, he have to enter at least the default board language params. Without doing so, he
	is not able to activate the field.
	
	If the default board language is changed, a check has to be made if the profile field language entries are
	still valid.

	* Show at profile view (yes/no)
	* Viewtopic Integration (Load Switch, Able to show fields with additional template vars populated if enabled)
	* Custom Validation (Regex)?

	* Try to build the edit screen with the existing create screen...
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

// lang_admin - temporary placed here...
$user->lang += array(
	'FIELD_INT'		=> 'Numbers',
	'FIELD_STRING'	=> 'Single Textfield',
	'FIELD_TEXT'	=> 'Textarea',
	'FIELD_BOOL'	=> 'Boolean (Yes/No)',
	'FIELD_DROPDOWN'=> 'Dropdown Box',
	'FIELD_DATE'	=> 'Date',

	'CUSTOM_PROFILE_FIELDS'		=> 'Custom Profile Fields',
	'NO_FIELD_TYPE'				=> 'No Field type specified',
	'FIRST_OPTION'				=> 'First Option',
	'SECOND_OPTION'				=> 'Second Option',
	'EMPTY_FIELD_NAME'			=> 'Empty field name',
	'EMPTY_USER_FIELD_NAME'		=> 'Empty Field Name presented to the user',
	'FIELD_IDENT_ALREADY_EXIST'	=> 'Field identifier %s already exist, please choose another Field Name.',
	'NEXT_PAGE'					=> 'Next Page',
	'PREVIOUS_PAGE'				=> 'Previous Page',
	'STEP_1_TITLE'				=> 'Add Profile Field',
	'STEP_2_TITLE'				=> 'Profile type specific options',
	'STEP_3_TITLE'				=> 'Remaining Language Definitions',
	'REQUIRED_FIELD_EXPLAIN'	=> 'Force profile field to be filled out or specified by user',
	'REQUIRED_FIELD'			=> 'Required Field',
	'DISPLAY_AT_REGISTRATION'	=> 'Display at registration screen',
	'ROWS'						=> 'Rows',
	'COLUMNS'					=> 'Columns',
	'STEP_X_FROM_X'				=> 'Step %1$d from %2$d',

	'STRING_DEFAULT_VALUE'		=> 'Default Value',
	'TEXT_DEFAULT_VALUE'		=> 'Default Value',
	'STRING_DEFAULT_VALUE_EXPLAIN'	=> 'Enter a default phrase to be displayed, a default value. Leave empty if you want to show it empty at the first place.',
	'TEXT_DEFAULT_VALUE_EXPLAIN'=> 'Enter a default text to be displayed, a default value. Leave empty if you want to show it empty at the first place.',
	'BOOL_ENTRIES'				=> 'Entries',
	'DROPDOWN_ENTRIES'			=> 'Entries',
	'BOOL_ENTRIES_EXPLAIN'		=> 'Enter your options now',
	'DROPDOWN_ENTRIES_EXPLAIN'	=> 'Enter your options now, every option in one line',

	'HIDE_PROFILE_FIELD'		=> 'Hide Profile Field',
	'HIDE_PROFILE_FIELD_EXPLAIN'=> 'Only Administrators and Moderators are able to see this profile field',
	'ADDED_PROFILE_FIELD'		=> 'Successfully added custom profile field',
	'CREATE_NEW_FIELD'			=> 'Create New Field'
);

if (!$auth->acl_get('a_user'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$mode = (isset($_POST['add'])) ? 'create' : request_var('mode', '');
$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$create = (isset($_POST['create'])) ? TRUE : FALSE;
$error = $notify = array();

adm_page_header('CUSTOM_PROFILE_FIELDS');

$default_values = array(
	'field_length' => array(FIELD_STRING => 10, FIELD_TEXT => '5|80', FIELD_INT => 5, FIELD_DATE => 10, FIELD_BOOL => 1, FIELD_DROPDOWN => 0),
	'field_minlen' => array(FIELD_STRING => 0, FIELD_TEXT => 0, FIELD_INT => 0, FIELD_DATE => 10, FIELD_BOOL => 0, FIELD_DROPDOWN => 0),
	'field_maxlen' => array(FIELD_STRING => 20, FIELD_TEXT => 1000, FIELD_INT => 100, FIELD_DATE => 10, FIELD_BOOL => 0, FIELD_DROPDOWN => 5),
	'field_validation' => array(FIELD_STRING => '.*', FIELD_TEXT => '.*', FIELD_INT => '', FIELD_DATE => '', FIELD_BOOL => '', FIELD_DROPDOWN => ''),
	'field_novalue' => array(FIELD_STRING => '', FIELD_TEXT => '', FIELD_INT => 0, FIELD_DATE => ' 0- 0-   0', FIELD_BOOL => 0, FIELD_DROPDOWN => 1),
	'field_default_value' => array(FIELD_STRING => '', FIELD_TEXT => '', FIELD_INT => 0, FIELD_DATE => ' 0- 0-   0', FIELD_BOOL => 0, FIELD_DROPDOWN => 1),
	'field_required' => array(FIELD_STRING => 0, FIELD_TEXT => 0, FIELD_INT => 0, FIELD_DATE => 0, FIELD_BOOL => 0, FIELD_DROPDOWN => 0),
	'field_hide' => array(FIELD_STRING => 0, FIELD_TEXT => 0, FIELD_INT => 0, FIELD_DATE => 0, FIELD_BOOL => 0, FIELD_DROPDOWN => 0),
	'field_show_on_reg' => array(FIELD_STRING => 0, FIELD_TEXT => 0, FIELD_INT => 0, FIELD_DATE => 0, FIELD_BOOL => 0, FIELD_DROPDOWN => 0),
	'pf_preview' => array(FIELD_STRING => '', FIELD_TEXT => '', FIELD_INT => '', FIELD_DATE => ' 0- 0-   0', FIELD_BOOL => '', FIELD_DROPDOWN => '')
);

$cp = new custom_profile_admin();

// Build Language array
// Based on this, we decide which elements need to be edited later and which language items are missing
$lang_ids = $lang_entry = $lang_diff = array();

$result = $db->sql_query('SELECT lang_id FROM ' . LANG_TABLE);

while ($row = $db->sql_fetchrow($result))
{
	$lang_ids[] = $row['lang_id'];
}
$db->sql_freeresult($result);

$sql = 'SELECT field_id, lang_id
	FROM phpbb_profile_lang
		ORDER BY lang_id';
$result = $db->sql_query($sql);
	
while ($row = $db->sql_fetchrow($result))
{
	$lang_entry[$row['field_id']][] = $row['lang_id'];
}
$db->sql_freeresult($result);

foreach ($lang_entry as $field_id => $field_ary)
{
	$lang_diff[$field_id] = array_diff($lang_ids, $field_ary);
}
unset($lang_ids);

if ($mode == '')
{
	trigger_error('INVALID_MODE');
}

if ($mode == 'create')
{
	$field_type = request_var('field_type', 0);
	$cp->vars['field_name'] = request_var('field_name', '');
	$field_ident = strtolower(str_replace(array(' ', "'"), array('', ''), $cp->vars['field_name']));

	$step = request_var('step', 1);
	$s_hidden_fields = '<input type="hidden" name="field_type" value="' . $field_type . '" />';

	$submit = (isset($_REQUEST['next']) || isset($_REQUEST['prev'])) ? true : false;
	$update = (isset($_REQUEST['update'])) ? true : false;
	$save = (isset($_REQUEST['save'])) ? true : false;

	if (!$field_type)
	{
		trigger_error('NO_FIELD_TYPE');
	}

	// Get all relevant informations about entered values within all steps

	// step 1
	$exclude[1] = array('lang_name', 'lang_explain', 'field_name');

	// Text-based fields require lang_default_value to be excluded
	if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
	{
		$exclude[1][] = 'lang_default_value';
	}

	// option-specific fields require lang_options to be excluded
	if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
	{
		$exclude[1][] = 'lang_options';
	}

	$cp->vars['lang_name'] = request_var('lang_name', '');
	$cp->vars['lang_explain'] = request_var('lang_explain', '');
	$cp->vars['lang_default_value'] = request_var('lang_default_value', '');
	$cp->vars['lang_options'] = request_var('lang_options', '');

	// step 2
	$exclude[2] = array('field_length', 'pf_preview', 'field_maxlen', 'field_minlen', 'field_validation', 'field_novalue', 'field_default_value', 'field_required', 'field_show_on_reg', 'field_hide');
	foreach ($exclude[2] as $key)
	{
		$var = request_var($key, $default_values[$key][$field_type]);
		
		// Manipulate the intended variables a little bit if needed
		if ($field_type == FIELD_DROPDOWN && $key == 'field_maxlen')
		{
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
/*
		if (($field_type == FIELD_TEXT || $field_type == FIELD_STRING) && $key == 'options')
		{
			$allow_html = (isset($_REQUEST['allow_html'])) ? 1 : 0;
			$allow_bbcode = (isset($_REQUEST['allow_bbcode'])) ? 1 : 0;
			$allow_smilies = (isset($_REQUEST['allow_smilies'])) ? 1 : 0;
		}
*/

		$cp->vars[$key] = $var;
	}

	// step 3 - all arrays
	$exclude[3] = array('l_lang_name', 'l_lang_explain', 'l_lang_default_value', 'l_lang_options');
	foreach ($exclude[3] as $key)
	{
		$cp->vars[$key] = request_var($key, '');
	}

	$error = array();
	if ($submit && $step == 1)
	{
		// Check values for step 1
		if ($cp->vars['field_name'] == '')
		{
			$error[] = $user->lang['EMPTY_FIELD_NAME'];
		}
		if ($cp->vars['lang_name'] == '')
		{
			$error[] = $user->lang['EMPTY_USER_FIELD_NAME'];
		}
	
		$sql = "SELECT field_ident 
			FROM phpbb_profile_fields 
				WHERE field_ident = '$field_ident'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$error[] = sprintf($user->lang['FIELD_IDENT_ALREADY_EXIST'], $field_ident);
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

		foreach ($key_ary as $key)
		{
			$var = $_POST[$key];
			if (!$var)
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
							$s_hidden_fields .= '<input type="hidden" name="' . $key . '[' . $num . '][' . $_num . ']" value="' . stripslashes(htmlspecialchars($___var)) . '" />' . "\n";
						}
					}
					else
					{
						$s_hidden_fields .= '<input type="hidden" name="' . $key . '[' . $num . ']" value="' . stripslashes(htmlspecialchars($__var)) . '" />' . "\n";
					}
				}
			}
			else
			{
				$s_hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . stripslashes(htmlspecialchars($var)) . '" />' . "\n";
			}
		}
	}

	if (!sizeof($error) && $save && $step == 3)
	{
		save_profile_field($field_type, $field_ident);
	}

?>
	
	<p><?php echo sprintf($user->lang['STEP_X_FROM_X'], $step, 3); ?></p>

	<form name="add_profile_field" method="post" action="admin_profile.<?php echo "$phpEx$SID&amp;mode=$mode&amp;step=$step"; ?>">
	<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
	<tr>
		<th align="center" colspan="2"><?php echo $user->lang['STEP_' . $step . '_TITLE']; ?></th>
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
				<td class="row1"><b>Field Type: </b><br /><span class="gensmall">You are not able to change this value later.</span></td>
				<td class="row2"><b><?php echo $user->lang['FIELD_' . strtoupper($cp->profile_types[$field_type])]; ?></b></td>
			</tr>
			<tr>
				<td class="row1"><b>Field Name: </b><br /><span class="gensmall">The Field Name is a name for you to identify the profile field, it is not displayed to the user. You are able to use this name as template variable later.</span></td>
				<td class="row2"><input class="post" type="text" name="field_name" size="20" value="<?php echo $cp->vars['field_name']; ?>" /></td>
			</tr>
			<tr>
				<th align="center" colspan="2">Language specific options [<b><?php echo $config['default_lang']; ?></b>]</th>
			</tr>
			<tr>
				<td class="row1"><b>Field Name: </b><br /><span class="gensmall">The Field Name presented to the user</span></td>
				<td class="row2"><input class="post" type="text" name="lang_name" size="20" value="<?php echo $cp->vars['lang_name']; ?>" /></td>
			</tr>
			<tr>
				<td class="row1"><b>Field Description: </b><br /><span class="gensmall">The Explanation for this field presented to the user</span></td>
				<td class="row2"><textarea name="lang_explain" rows="3" cols="80"><?php echo $cp->vars['lang_explain']; ?></textarea></td>
			</tr>
<?php
			// String and Text needs to set default values here...
			if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
			{
?>
				<tr>
					<td class="row1"><b><?php echo $user->lang[strtoupper($cp->profile_types[$field_type]) . '_DEFAULT_VALUE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang[strtoupper($cp->profile_types[$field_type]) . '_DEFAULT_VALUE_EXPLAIN']; ?></span></td>
					<td class="row2"><?php echo ($field_type == FIELD_STRING) ? '<input class="post" type="text" name="lang_default_value" size="20" value="' . $cp->vars['lang_default_value'] . '" />' : '<textarea name="lang_default_value" rows="5" cols="80">' . $cp->vars['lang_default_value'] . '</textarea>'; ?></td>
				</tr>
<?php
			}
			
			if ($field_type == FIELD_BOOL || $field_type == FIELD_DROPDOWN)
			{
				if ($field_type == FIELD_BOOL && !is_array($cp->vars['lang_options']))
				{
					$cp->vars['lang_options'][0] = '';
					$cp->vars['lang_options'][1] = '';
				}
?>
				<tr>
					<td class="row1"><b><?php echo $user->lang[strtoupper($cp->profile_types[$field_type]) . '_ENTRIES']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang[strtoupper($cp->profile_types[$field_type]) . '_ENTRIES_EXPLAIN']; ?></span></td>
					<td class="row2"><?php echo ($field_type == FIELD_DROPDOWN) ? '<textarea name="lang_options" rows="5" cols="80">' . $cp->vars['lang_options'] . '</textarea>' : '<table border=0><tr><td><input name="lang_options[0]" size="20" value="' . $cp->vars['lang_options'][0] . '" class="post" /></td><td>[ ' . $user->lang['FIRST_OPTION'] . ' ]</td></tr><tr><td><input name="lang_options[1]" size="20" value="' . $cp->vars['lang_options'][1] . '" class="post" /></td><td>[ ' . $user->lang['SECOND_OPTION'] . ' ]</td></tr></table>'; ?></td>
				</tr>
<?php
			}
?>
			<tr>
				<td width="100%" colspan="2" class="cat"><input class="btnlite" type="submit" name="next" value="<?php echo $user->lang['NEXT_PAGE']; ?>" /></td>
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
					<td class="row1"><b><?php echo $option_ary['L_NAME']; ?></b><?php echo (isset($option_ary['L_EXPLAIN'])) ? '<br /><span class="gensmall">' . $option_ary['L_EXPLAIN'] . '</span>' : ''; ?></td>
					<td class="row2"><?php echo $option_ary['FIELD']; ?></td>
				</tr>
<?php
			}
/*
			// Options (HTML, BBCode, Smilies) for various fields...
			if ($field_type == FIELD_STRING || $field_type == FIELD_TEXT)
			{
?>
				<tr>
					<td class="row1"><b>Allow HTML: </b></td>
					<td class="row2"><input type="checkbox" name="allow_html" value="1"<?php echo ($cp->vars['options'] & 1 << 1) ? ' checked="checked"' : ''; ?> /></td>
				</tr>
				<tr>
					<td class="row1"><b>Allow BBCode: </b></td>
					<td class="row2"><input type="checkbox" name="allow_bbcode" value="1"<?php echo ($cp->vars['options'] & 1 << 2) ? ' checked="checked"' : ''; ?> /></td>
				</tr>
				<tr>
					<td class="row1"><b>Allow Smilies: </b></td>
					<td class="row2"><input type="checkbox" name="allow_smilies" value="1"<?php echo ($cp->vars['options'] & 1 << 3) ? ' checked="checked"' : ''; ?> /></td>
				</tr>
<?php
			}
*/
?>
			<tr>
				<td width="100%" colspan="2" class="cat"><input class="btnlite" type="submit" name="update" value="<?php echo $user->lang['UPDATE']; ?>" />&nbsp;<input class="btnlite" type="submit" name="prev" value="<?php echo $user->lang['PREVIOUS_PAGE']; ?>" />&nbsp;<input class="btnlite" type="submit" name="next" value="<?php echo $user->lang['NEXT_PAGE']; ?>" /></td>
			</tr>
			<?php echo $s_hidden_fields; ?>
			</table>

			<br /><br />
			<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center" width="99%">
			<tr>
				<th align="center" colspan="2">Preview Profile Field</th>
			</tr>
<?php 
			if (!empty($user_error) || $update) 
			{ 
?>				<tr>
					<td class="row3" colspan="2"><?php echo (!empty($user_error)) ? '<span style="color:red">' . $user_error . '</span>' : '<span style="color:green">Everything OK</span>'; ?></td>
				</tr>
<?php
			}
			
			$field_data = array(
				'lang_name' => $cp->vars['lang_name'],
				'lang_explain' => $cp->vars['lang_explain'],
				'lang_id' => 1,
				'field_id' => 1,
				'lang_default_value' => $cp->vars['lang_default_value'],
				'field_default_value' => $cp->vars['field_default_value'],
				'field_ident' => 'preview',
				'field_type' => $field_type,

				'field_length' => $cp->vars['field_length'],
				'field_maxlen' => $cp->vars['field_maxlen'],
				'lang_options' => $cp->vars['lang_options']
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
			$options = build_language_options($field_type);

			foreach ($options as $lang_id => $lang_ary)
			{
?>
				<tr>
					<td align="center" class="row3" colspan="2"><?php echo ($lang_ary['lang_iso'] == $config['default_lang']) ? 'Default Language [' . $config['default_lang'] . ']' : 'Language [' . $lang_ary['lang_iso'] . ']'; ?></td>
				</tr>
<?php
				foreach ($lang_ary['fields'] as $field_name => $field_ary)
				{
?>
				<tr>
					<td class="row1"><b><?php echo $field_ary['L_NAME']; ?></b><?php echo (isset($field_ary['L_EXPLAIN'])) ? '<br /><span class="gensmall">' . $field_ary['L_EXPLAIN'] . '</span>' : ''; ?></td>
					<td class="row2"><?php echo $field_ary['FIELD']; ?></td>
				</tr>
<?php			
				}
			}
?>
			<tr>
				<td width="100%" colspan="2" class="cat"><input class="btnlite" type="submit" name="prev" value="<?php echo $user->lang['PREVIOUS_PAGE']; ?>" />&nbsp;<input type="submit" name="save" class="btnmain" value="Save" /></td>
			</tr>
			<?php echo $s_hidden_fields; ?>
			</table>
			</form>
<?php
			break;
	}
}

if ($mode == 'delete')
{
	$confirm = (isset($_REQUEST['confirm'])) ? true : false;
	$cancel = (isset($_REQUEST['cancel'])) ? true : false;
	$field_id = request_var('field_id', 0);

	if (!$field_id)
	{
		trigger_error('INVALID_MODE');
	}

	if ($confirm)
	{
		$sql = 'SELECT field_ident FROM phpbb_profile_fields WHERE field_id = ' . $field_id;
		$result = $db->sql_query($sql);
		$field_ident = $db->sql_fetchfield('field_ident', 0, $result);

		$db->sql_query('DELETE FROM phpbb_profile_fields WHERE field_id = ' . $field_id);
		$db->sql_query('DELETE FROM phpbb_profile_fields_lang WHERE field_id = ' . $field_id);
		$db->sql_query('DELETE FROM phpbb_profile_lang WHERE field_id = ' . $field_id);
		$db->sql_query('ALTER TABLE ' . CUSTOM_PROFILE_DATA . ' DROP ' . $field_ident);

		$order = 0;

		$sql = 'SELECT *
			FROM phpbb_profile_fields
			ORDER BY field_order';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			++$order;
			if ($row['field_order'] != $order)
			{
				$db->sql_query("UPDATE phpbb_profile_fields SET field_order = $order WHERE field_id = " . $row['field_id']);
			}
		}

		trigger_error('Successfully deleted profile field.');
	}
	else if (!$confirm && !$cancel)
	{
		$l_message = '<form method="post" action="admin_profile.' . $phpEx . $SID . '&amp;mode=delete&amp;field_id=' . $field_id . '">' . 'CONFIRM_DELETE_PROFILE_FIELD' . '<br /><br /><input class="btnlite" type="submit" name="confirm" value="' . $user->lang['YES'] . '" />&nbsp;&nbsp;<input class="btnlite" type="submit" name="cancel" value="' . $user->lang['NO'] . '" /></form>';

		adm_page_message($user->lang['CONFIRM'], $l_message, false);

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

	if (!in_array($default_lang_id, $lang_entry[$field_id]))
	{
		trigger_error('DEFAULT_LANGUAGE_NOT_FILLED');
	}

	$db->sql_query("UPDATE phpbb_profile_fields SET field_active = 1 WHERE field_id = $field_id");
	trigger_error('PROFILE_FIELD_ACTIVATED');
}

if ($mode == 'deactivate')
{
	$field_id = request_var('field_id', 0);

	if (!$field_id)
	{
		trigger_error('INVALID_MODE');
	}
	
	$db->sql_query("UPDATE phpbb_profile_fields SET field_active = 0 WHERE field_id = $field_id");
	trigger_error('PROFILE_FIELD_DEACTIVATED');
}

if ($mode == 'move_up' || $mode == 'move_down')
{
	$field_order = intval($_GET['order']);
	$order_total = $field_order * 2 + (($mode == 'move_up') ? -1 : 1);

	$sql = "UPDATE phpbb_profile_fields
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
<!--
	<tr>
		<th align="center" colspan="6"><?php echo $user->lang['CUSTOM_PROFILE_FIELDS']; ?></th>
	</tr>
	<tr>
		<td class="spacer" colspan="6" height="1"><img src="../images/spacer.gif" alt="" width="1" height="1" /></td>
	</tr>
//-->
	<tr> 
		<th nowrap="nowrap">Name</th>
		<th nowrap="nowrap">Template Variable</th>
		<th nowrap="nowrap">Type</th>
		<th colspan="3" nowrap="nowrap">Options</th>
		<th nowrap="nowrap">Reorder</th>
	</tr>
<?php
	$sql = 'SELECT *
		FROM phpbb_profile_fields
		ORDER BY field_order';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

		$active_lang = (!$row['field_active']) ? 'ACTIVATE' : 'DEACTIVATE';
		$active_value = (!$row['field_active']) ? 'activate' : 'deactivate';
		$id = $row['field_id'];
?>
	<tr>
		<td class="<?php echo $row_class; ?>"><?php echo $row['field_name']; ?></td>
		<td class="<?php echo $row_class; ?>"><?php echo $row['field_ident']; ?></td>
		<td class="<?php echo $row_class; ?>"><?php echo $user->lang['FIELD_' . strtoupper($cp->profile_types[$row['field_type']])]; ?></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_profile.<?php echo $phpEx . $SID; ?>&amp;mode=<?php echo $active_value; ?>&amp;field_id=<?php echo $id; ?>"><?php echo $user->lang[$active_lang]; ?></a></td>
		<td class="<?php echo $row_class; ?>"><a href="admin_profile.<?php echo $phpEx . $SID; ?>&amp;mode=edit&amp;field_id=<?php echo $id; ?>"><?php echo ((sizeof($lang_diff[$row['field_id']])) ? '<span style="color:red">' . $user->lang['EDIT'] . '</span>' : $user->lang['EDIT']) . '</a>' . ((sizeof($lang_diff[$row['field_id']])) ? '</span>' : ''); ?></td>
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
		<td class="cat" colspan="7"><input class="post" type="text" name="field_name" size="20" /> <select name="field_type"><?php echo $s_select_type; ?></select> <input class="btnlite" type="submit" name="add" value="<?php echo $user->lang['CREATE_NEW_FIELD']; ?>" /></td>
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
function build_language_options($field_type, $mode = 'new')
{
	global $user, $config, $db, $cp;

	$sql = 'SELECT lang_id, lang_iso 
		FROM ' . LANG_TABLE . 
		(($mode == 'new') ? " WHERE lang_iso <> '" . $config['default_lang'] . "'" : '');
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

	if ($mode == 'new')
	{
		foreach ($options as $field => $field_type)
		{
			$lang_options[1]['lang_iso'] = $config['default_lang'];
			$lang_options[1]['fields'][$field] = array(
				'L_NAME'	=> 'CP_' . strtoupper($field) . '_TITLE',
				'L_EXPLAIN'	=> 'CP_' . strtoupper($field) . '_EXPLAIN',
				'FIELD'		=> '<b>' . ((is_array($cp->vars[$field])) ? implode('<br />', $cp->vars[$field]) : str_replace("\n", '<br />', $cp->vars[$field])) . '</b>'
			);
		}
	}

	foreach ($languages as $lang_id => $lang_iso)
	{
		$lang_options[$lang_id]['lang_iso'] = $lang_iso;
		foreach ($options as $field => $field_type)
		{
			$value = ($mode == 'new') ? request_var('l_' . $field, '') : $cp->vars['l_' . $field];
				 
			if ($field == 'lang_options')
			{
				$var = ($mode == 'new') ? $cp->vars['lang_options'] : $cp->vars['lang_options'][$lang_id];
					
				switch ($field_type)
				{
					case 'two_options':

						$lang_options[$lang_id]['fields'][$field] = array(
							'L_NAME'	=> 'CP_' . strtoupper($field) . '_TITLE',
							'L_EXPLAIN' => 'CP_' . strtoupper($field) . '_EXPLAIN',
							'FIELD'		=> '<table border=0><tr><td><span class="genmed">' . $user->lang['FIRST_OPTION'] . ': </span></td><td><input name="l_' . $field . '[' . $lang_id . '][]" size="20" value="' . ((isset($value[$lang_id][0])) ? $value[$lang_id][0] : $var[0]) . '" class="post" /></td></tr><tr><td><span class="genmed">' . $user->lang['SECOND_OPTION'] . ': </span></td><td><input name="l_' . $field . '[' . $lang_id . '][]" size="20" value="' . ((isset($value[$lang_id][1])) ? $value[$lang_id][1] : $var[1]) . '" class="post" /></td></tr></table>'
						);
						break;

					case 'optionfield':

						$lang_options[$lang_id]['fields'][$field] = array(
							'L_NAME'	=> 'CP_' . strtoupper($field) . '_TITLE',
							'L_EXPLAIN'	=> 'CP_' . strtoupper($field) . '_EXPLAIN',
							'FIELD'		=> '<textarea name="l_' . $field . '[' . $lang_id . ']" rows="7" cols="80">' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '</textarea>'
						);
						break;
				}
			}
			else
			{
				$var = ($mode == 'new') ? $cp->vars[$field] : $cp->vars[$field][$lang_id];

				$lang_options[$lang_id]['fields'][$field] = array(
					'L_NAME'	=> 'CP_' . strtoupper($field) . '_TITLE',
					'L_EXPLAIN'	=> 'CP_' . strtoupper($field) . '_EXPLAIN',
					'FIELD'		=> ($field_type == 'string') ? '<input class="post" type="text" name="l_' . $field . '[' . $lang_id . ']" value="' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '" size="20" />' : '<textarea name="l_' . $field . '[' . $lang_id . ']" rows="3" cols="80">' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '</textarea>'
				);
			}
		}
	}

	return $lang_options;
}

function save_profile_field($field_type, $field_ident)
{
	global $cp, $db, $config, $user;

	// Collect all informations, if something is going wrong, abort the operation
	$profile_sql = $profile_lang = $empty_lang = $profile_lang_fields = array();

	$sql = 'SELECT lang_id 
		FROM ' . LANG_TABLE . ' 
		WHERE lang_iso = '" . $config['default_lang'] . "'";
	$result = $db->sql_query($sql);
	$default_lang_id = (int) $db->sql_fetchfield('lang_id', 0, $result);
	$db->sql_freeresult($result);

	$result = $db->sql_query('SELECT MAX(field_order) as max_field_order FROM phpbb_profile_fields');
	$new_field_order = (int) $db->sql_fetchfield('max_field_order', 0, $result);
	$db->sql_freeresult($result);

	// Save the field
	$profile_fields = array(
		'field_name'		=> $cp->vars['field_name'],
		'field_type'		=> $field_type,
		'field_ident'		=> $field_ident,
		'field_length'		=> $cp->vars['field_length'],
		'field_minlen'		=> $cp->vars['field_minlen'],
		'field_maxlen'		=> $cp->vars['field_maxlen'],
		'field_novalue'		=> $cp->vars['field_novalue'],
		'field_default_value'	=> $cp->vars['field_default_value'],
		'field_validation'	=> $cp->vars['field_validation'],
		'field_required'	=> $cp->vars['field_required'],
		'field_show_on_reg'	=> $cp->vars['field_show_on_reg'],
		'field_hide'		=> $cp->vars['field_hide'],
		'field_order'		=> $new_field_order + 1,
		'field_active'		=> 0
	);

	$db->sql_query('INSERT INTO phpbb_profile_fields ' . $db->sql_build_array('INSERT', $profile_fields));

	$field_id = $db->sql_nextid();
		
	$sql = 'ALTER TABLE ' . CUSTOM_PROFILE_DATA . " ADD $field_ident ";
	switch ($field_type)
	{
		case FIELD_STRING:
			$sql .= " VARCHAR(255) DEFAULT NULL NULL";
			break;

		case FIELD_DATE:
			$sql .= "VARCHAR(10) DEFAULT NULL NULL";
			break;

		case FIELD_TEXT:
			$sql .= "TEXT NULL";
			break;

		case FIELD_BOOL:
			$sql .= "TINYINT(2) DEFAULT NULL NULL";
			break;
		
		case FIELD_DROPDOWN:
			$sql .= "MEDIUMINT(8) DEFAULT NULL NULL";
			break;

		case FIELD_INT:
			$sql .= (($cp->vars['field_maxlen'] > 60000) ? 'BIGINT(20)' : 'MEDIUMINT(8)') . (($cp->vars['field_minlen'] >= 0) ? ' UNSIGNED' : ' ') . " DEFAULT NULL NULL";
			break;
	}
	$profile_sql[] = $sql;

	$sql_ary = array(
		'field_id' => $field_id,
		'lang_id' => $default_lang_id,
		'lang_name' => $cp->vars['lang_name'],
		'lang_explain' => $cp->vars['lang_explain'],
		'lang_default_value' => $cp->vars['lang_default_value']
	);
	$profile_sql[] = 'INSERT INTO phpbb_profile_lang ' . $db->sql_build_array('INSERT', $sql_ary);

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
					'field_id' => $field_id,
					'lang_id' => $lang_id,
					'lang_name' => $cp->vars['l_lang_name'][$lang_id],
					'lang_explain' => $cp->vars['l_lang_explain'][$lang_id],
					'lang_default_value' => $cp->vars['l_lang_default_value'][$lang_id]
				);
			}
		}
	}

	$cp->vars['l_lang_name'] = request_var('l_lang_name', '');
	$cp->vars['l_lang_explain'] = request_var('l_lang_explain', '');
	$cp->vars['l_lang_default_value'] = request_var('l_lang_default_value', '');
	$cp->vars['l_lang_options'] = request_var('l_lang_options', '');

	if (!empty($cp->vars['lang_options']))
	{
		if (!is_array($cp->vars['lang_options']))
		{
			$cp->vars['lang_options'] = explode("\n", $cp->vars['lang_options']);
		}

		foreach ($cp->vars['lang_options'] as $option_id => $value)
		{
			$sql_ary = array(
				'field_id' => $field_id,
				'lang_id' => $default_lang_id,
				'option_id' => $option_id,
				'field_type' => $field_type,
				'value' => $value
			);
			$profile_sql[] = 'INSERT INTO phpbb_profile_fields_lang ' . $db->sql_build_array('INSERT', $sql_ary);
		}
	}

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
						'field_id' => $field_id,
						'lang_id' => $lang_id,
						'option_id' => $option_id,
						'field_type' => $field_type,
						'value' => $value
					);
				}
			}
		}
	}

	foreach ($profile_lang as $sql)
	{
		$profile_sql[] = 'INSERT INTO phpbb_profile_lang ' . $db->sql_build_array('INSERT', $sql);
	}

	if (sizeof($profile_lang_fields))
	{
		foreach ($profile_lang_fields as $sql)
		{
			$profile_sql[] = 'INSERT INTO phpbb_profile_fields_lang ' . $db->sql_build_array('INSERT', $sql);
		}
	}

//		$db->sql_transaction();
	foreach ($profile_sql as $sql)
	{
		$db->sql_query($sql);
	}
//	$db->sql_transaction('commit');

	trigger_error($user->lang['ADDED_PROFILE_FIELD']);
}


?>