<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

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
		$l_title = $user->lang['FAQ'];
		$user->add_lang('faq', false, true);
		break;
}

// Pull the array data from the lang pack
$j = 0;
$counter = 0;
$counter_2 = 0;
$help_block = array();
$help_block_titles = array();

foreach ($user->help as $help_ary)
{
	if ($help_ary[0] != '--')
	{
		$help_block[$j][$counter]['id'] = $counter_2;
		$help_block[$j][$counter]['question'] = $help_ary[0];
		$help_block[$j][$counter]['answer'] = $help_ary[1];

		$counter++;
		$counter_2++;
	}
	else
	{
		$j = ($counter != 0) ? $j + 1 : 0;

		$help_block_titles[$j] = $help_ary[1];

		$counter = 0;
	}
}

//
// Lets build a page ...
$template->assign_vars(array(
	'L_FAQ_TITLE'	=> $l_title,
	'L_BACK_TO_TOP'	=> $user->lang['BACK_TO_TOP'])
);

for ($i = 0, $size = sizeof($help_block); $i < $size; $i++)
{
	if (sizeof($help_block[$i]))
	{
		$template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE' => $help_block_titles[$i])
		);

		$template->assign_block_vars('faq_block_link', array(
			'BLOCK_TITLE' => $help_block_titles[$i])
		);

		for ($j = 0, $_size = sizeof($help_block[$i]); $j < $_size; $j++)
		{
			$template->assign_block_vars('faq_block.faq_row', array(
				'FAQ_QUESTION' => $help_block[$i][$j]['question'],
				'FAQ_ANSWER' => $help_block[$i][$j]['answer'],

				'U_FAQ_ID' => $help_block[$i][$j]['id'])
			);

			$template->assign_block_vars('faq_block_link.faq_row_link', array(
				'FAQ_LINK' => $help_block[$i][$j]['question'],

				'U_FAQ_LINK' => '#' . $help_block[$i][$j]['id'])
			);
		}
	}
}

page_header($l_title);

$template->set_filenames(array(
	'body' => 'faq_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

page_footer();

?>