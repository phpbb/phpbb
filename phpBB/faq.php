<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : faq.php 
// STARTED   : Mon Jul 8, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$auth->acl($user->data);
$user->setup();

// Load the appropriate faq file
if (isset($_GET['mode']))
{
	switch($_GET['mode'])
	{
		case 'bbcode':
			$lang_file = 'lang_bbcode';
			$l_title = $lang['BBCode_guide'];
			break;
		default:
			$lang_file = 'lang_faq';
			$l_title = $lang['FAQ'];
			break;
	}
}
else
{
	$lang_file = 'lang_faq';
	$l_title = $lang['FAQ'];
}

include($user->lang_path . $lang_file . '.' . $phpEx);

// Pull the array data from the lang pack
$j = 0;
$counter = 0;
$counter_2 = 0;
$faq_block = array();
$faq_block_titles = array();

for($i = 0; $i < count($faq); $i++)
{
	if ($faq[$i][0] != '--')
	{
		$faq_block[$j][$counter]['id'] = $counter_2;
		$faq_block[$j][$counter]['question'] = $faq[$i][0];
		$faq_block[$j][$counter]['answer'] = $faq[$i][1];

		$counter++;
		$counter_2++;
	}
	else
	{
		$j = ($counter != 0) ? $j + 1 : 0;

		$faq_block_titles[$j] = $faq[$i][1];

		$counter = 0;
	}
}

//
// Lets build a page ...
//
$template->assign_vars(array(
	'L_FAQ_TITLE' => $l_title,
	'L_BACK_TO_TOP' => $lang['Back_to_top'])
);

for($i = 0; $i < count($faq_block); $i++)
{
	if (count($faq_block[$i]))
	{
		$template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE' => $faq_block_titles[$i])
		);

		$template->assign_block_vars('faq_block_link', array(
			'BLOCK_TITLE' => $faq_block_titles[$i])
		);

		for($j = 0; $j < count($faq_block[$i]); $j++)
		{
			$template->assign_block_vars('faq_block.faq_row', array(
				'FAQ_QUESTION' => $faq_block[$i][$j]['question'],
				'FAQ_ANSWER' => $faq_block[$i][$j]['answer'],

				'S_ROW_COUNT' => $j,
				'U_FAQ_ID' => $faq_block[$i][$j]['id'])
			);

			$template->assign_block_vars('faq_block_link.faq_row_link', array(
				'FAQ_LINK' => $faq_block[$i][$j]['question'],

				'S_ROW_COUNT' => $j,
				'U_FAQ_LINK' => '#' . $faq_block[$i][$j]['id'])
			);
		}
	}
}

page_header($l_title);

$template->set_filenames(array(
	'body' => 'faq_body.html')
);
make_jumpbox('viewforum.'.$phpEx, $forum_id);

page_footer();

?>