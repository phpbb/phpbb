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
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$mode = request_var('mode', '');

// Load the appropriate faq file
switch ($mode)
{
	case 'bbcode':
		$l_title = $user->lang['BBCODE_GUIDE'];
		$user->add_lang('bbcode', false, true);
	break;

	default:
		$page_title = $user->lang['FAQ_EXPLAIN'];
		$ext_name = $lang_file = '';

		/**
		 * You can use this event display a custom help page
		 *
		 * @event core.faq_mode_validation
		 * @var	string	page_title		Title of the page
		 * @var	string	mode			FAQ that is going to be displayed
		 * @var	string	lang_file		Language file containing the help data
		 * @var	string	ext_name		Vendor and extension name where the help
		 *								language file can be loaded from
		 * @since 3.1.4-RC1
		 */
		$vars = array(
			'page_title',
			'mode',
			'lang_file',
			'ext_name',
		);
		extract($phpbb_dispatcher->trigger_event('core.faq_mode_validation', compact($vars)));

		$l_title = $page_title;
		$user->add_lang(($lang_file) ? $lang_file : 'faq', false, true, $ext_name);
	break;
}

// Pull the array data from the lang pack
$switch_column = $found_switch = false;
$help_blocks = array();
foreach ($user->help as $help_ary)
{
	if ($help_ary[0] == '--')
	{
		if ($help_ary[1] == '--')
		{
			$switch_column = true;
			$found_switch = true;
			continue;
		}

		$template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE'		=> $help_ary[1],
			'SWITCH_COLUMN'		=> $switch_column,
		));

		if ($switch_column)
		{
			$switch_column = false;
		}
		continue;
	}

	$template->assign_block_vars('faq_block.faq_row', array(
		'FAQ_QUESTION'		=> $help_ary[0],
		'FAQ_ANSWER'		=> $help_ary[1])
	);
}

// Lets build a page ...
$template->assign_vars(array(
	'L_FAQ_TITLE'				=> $l_title,
	'L_BACK_TO_TOP'				=> $user->lang['BACK_TO_TOP'],

	'SWITCH_COLUMN_MANUALLY'	=> (!$found_switch) ? true : false,
	'S_IN_FAQ'					=> true,
));

page_header($l_title);

$template->set_filenames(array(
	'body' => 'faq_body.html')
);
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

page_footer();
