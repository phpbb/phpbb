<?php
/***************************************************************************
 *                              admin_board.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if (!empty($setmodules))
{
	$module['GENERAL']['PHP_INFO'] = ($auth->acl_get('a_server')) ? basename(__FILE__) . $SID : '';
	return;
}

define('IN_PHPBB', 1);
// Load default header
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);


// Check permissions
if (!$auth->acl_get('a_server'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

ob_start(); 
phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES); 
$phpinfo = ob_get_contents(); 
ob_end_clean(); 

// Here we play around a little with the PHP Info HTML to try and stylise
// it along phpBB's lines ... hopefully without breaking anything. The idea
// for this was nabbed from the PHP annotated manual
preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output); 
$output = preg_replace('#<table#', '<table class="bg" align="center"', $output[1][0]);
$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
$output = preg_replace('#border="0" cellpadding="3" width="600"#', 'border="0" cellspacing="1" cellpadding="4" width="95%"', $output);
$output = preg_replace('#<tr class="v"><td>(.*?<a .*?</a>)(.*?)</td></tr>#s', '<tr class="row1"><td><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td>\2</td><td>\1</td></tr></table></td></tr>', $output);
$output = preg_replace('#<td>#', '<td style="{background-color: #9999cc;}">', $output);
$output = preg_replace('#class="e"#', 'class="row1" nowrap="nowrap"', $output);
$output = preg_replace('#class="v"#', 'class="row2"', $output);
$output = preg_replace('# class="h"#', '', $output);
$output = preg_replace('#<hr />#', '', $output);
preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output); 
$output = $output[1][0];

page_header($user->lang['PHP_INFO']);

echo '<h1>' . $user->lang['PHP_INFO'] . '</h1>';
echo '<p>' . $user->lang['PHP_INFO_EXPLAIN'] . '</p>';
echo $output; 

page_footer();

?>