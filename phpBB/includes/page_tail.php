<?php
/***************************************************************************
 *                              page_tail.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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

// Close our DB connection.
$db->sql_close();

// Output page creation time
if (defined('DEBUG'))
{
	$mtime = explode(' ', microtime());
	$totaltime = $mtime[0] + $mtime[1] - $starttime;

	if (!empty($_REQUEST['explain']) && $auth->acl_get('a_'))
	{
		echo $db->sql_report;
		echo "<pre><b>Page generated in $totaltime seconds with " . $db->num_queries . " queries,\nspending " . $db->sql_time . ' doing MySQL queries and ' . ($totaltime - $db->sql_time) . ' doing PHP things.</b></pre>';

		exit;
	}

	$debug_output = sprintf('<br /><br />[ Time : %.3fs | ' . $db->sql_num_queries() . ' Queries | GZIP : ' .  ( ( $config['gzip_compress'] ) ? 'On' : 'Off' ) . ' | Load : '  . (($user->load) ? $user->load : 'N/A'), $totaltime);

	if ($auth->acl_get('a_'))
	{
		$debug_output .= ' | <a href="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '&amp;explain=1">Explain</a>';
	}
	$debug_output .= ' ]';
}

$template->assign_vars(array(
	'PHPBB_VERSION'	=> $config['version'],
	'ADMIN_LINK' 	=> ($auth->acl_get('a_')) ? sprintf($user->lang['ACP'], '<a href="' . "admin/index.$phpEx?sid=" . $user->data['session_id'] . '">', '</a>') . '<br /><br />' : '',
	'DEBUG_OUTPUT'	=> (defined('DEBUG')) ? $debug_output : ''
));


if (!empty($cache))
{
	$cache->save_cache();
}
$template->display('body');

exit;
?>