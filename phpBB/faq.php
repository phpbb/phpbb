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
* @ignore
*/
define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

// Start session management
phpbb::$user->session_begin();
$auth->acl(phpbb::$user->data);
phpbb::$user->setup();

$mode = request_var('mode', '');

// Load the appropriate faq file
switch ($mode)
{
	case 'bbcode':
		$l_title = phpbb::$user->lang['BBCODE_GUIDE'];
		phpbb::$user->add_lang('bbcode', false, true);
	break;

	default:
		$l_title = phpbb::$user->lang['FAQ_EXPLAIN'];
		phpbb::$user->add_lang('faq', false, true);
	break;
}

// Pull the array data from the lang pack
$help_blocks = array();
foreach (phpbb::$user->help as $help_ary)
{
	if ($help_ary[0] == '--')
	{
		$template->assign_block_vars('faq_block', array(
			'BLOCK_TITLE'		=> $help_ary[1])
		);

		continue;
	}

	$template->assign_block_vars('faq_block.faq_row', array(
		'FAQ_QUESTION'		=> $help_ary[0],
		'FAQ_ANSWER'		=> $help_ary[1])
	);
}

// Lets build a page ...
$template->assign_vars(array(
	'L_FAQ_TITLE'	=> $l_title,
	'L_BACK_TO_TOP'	=> phpbb::$user->lang['BACK_TO_TOP'])
);

page_header($l_title);

$template->set_filenames(array(
	'body' => 'faq_body.html')
);
make_jumpbox(append_sid('viewforum'));

page_footer();

?>