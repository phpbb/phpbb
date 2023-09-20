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

namespace foo\bar\acp;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $language, $template;

		$this->tpl_name = 'foobar';
		$this->page_title = 'Bertie';

		$display_vars = [
			'title'	=> 'ACP_FOOBAR_SETTINGS',
			'vars'	=> [
				'legend'	=> 'GENERAL_SETTINGS',
				// For the 'dimension' type the order is important: main setting goes last.
				'setting_0_width'	=> ['lang' => 'SETTING_0',	'validate' => 'int:0',	'type' => false, 'method' => false, 'explain' => false],
				'setting_0_height'	=> ['lang' => 'SETTING_0',	'validate' => 'int:0',	'type' => false, 'method' => false, 'explain' => false],
				'setting_0'	=> ['lang' => 'SETTING_0',	'validate' => 'int:0:16',	'type' => 'dimension:0:999', 'explain' => true, 'append' => ' ' . $language->lang('PIXEL')],
				'setting_1'	=> ['lang' => 'SETTING_1',	'validate' => 'bool',		'type' => 'custom', 'method' => 'submit_button', 'lang_explain' => 'CUSTOM_LANG_EXPLAIN', 'explain' => true],
				'setting_2'	=> ['lang' => 'SETTING_2',	'validate' => 'bool',		'type' => 'radio:yes_no'],
				'setting_3'	=> ['lang' => 'SETTING_3',	'validate' => 'int:0:99999','type' => 'number:0:99999', 'explain' => true],
				'setting_4'	=> ['lang' => 'SETTING_4',	'validate' => 'string',		'type' => 'select', 'method' => 'create_select', 'explain' => true],
				'setting_5'	=> ['lang' => 'SETTING_5',	'validate' => 'string',		'type' => 'text:25:255', 'explain' => true],
				'setting_6'	=> ['lang' => 'SETTING_6',	'validate' => 'string',		'type' => 'password:25:255', 'explain' => true],
				'setting_7'	=> ['lang' => 'SETTING_7',	'validate' => 'email',		'type' => 'email:0:100', 'explain' => true],
				'setting_8'	=> ['lang' => 'SETTING_8',	'validate' => 'string',		'type' => 'textarea:5:30', 'explain' => true],
				'setting_9'	=> ['lang' => 'SETTING_9',	'validate' => 'bool',		'type' => 'radio:enabled_disabled', 'explain' => true],
				'setting_10'=> ['lang' => 'SETTING_10',	'validate' => 'bool',		'type' => 'radio:{"LABEL_1":1, "LABEL_3":3, "LABEL_2":2}', 'explain' => true],
			]
		];

		$this->new_config = $cfg_array = $error = [];

		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->new_config[$config_name] = $cfg_array[$config_name];
		}

		$template->assign_vars([
			'L_TITLE'			=> $language->lang($display_vars['title']),
			'L_TITLE_EXPLAIN'	=> $language->lang($display_vars['title'] . '_EXPLAIN'),

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action
		]);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', [
					'S_LEGEND'		=> true,
					'LEGEND'		=> $language->lang($vars),
				]);

				continue;
			}
			$type = explode(':', $vars['type']);

			$l_explain = '';
			$vars['explain'] = $vars['explain'] ?? false;
			$vars['lang_explain'] = $vars['lang_explain'] ?? false;

			if ($vars['explain'])
			{
				$l_explain = $language->lang($vars['lang_explain'] ?: $vars['lang'] . '_EXPLAIN');
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', [
				'KEY'			=> $config_key,
				'TITLE'			=> $language->lang($vars['lang']),
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
			]);

			unset($display_vars['vars'][$config_key]);
		}
	}

	function create_select()
	{
		return [
			'options' => [
				[
					'value'		=> 1,
					'selected'	=> true,
					'label'		=> 'Option 1',
				],
				[
					'value'		=> 2,
					'selected'	=> false,
					'label'		=> 'Option 2',
				],
				[
					'value'		=> 3,
					'selected'	=> false,
					'label'		=> 'Option 3',
				],
			]
		];
	}

	function submit_button()
	{
		return '<input class="button2" type="submit" id="test_button" value="Test submit button" />';
	}

}
