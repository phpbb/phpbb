<?php
/***************************************************************************
 *                             admin_search.php
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

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_search'))
	{
		return;
	}

	$module['DB']['SEARCH_INDEX'] = basename(__FILE__) . $SID;

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

// Check permissions
if (!$auth->acl_get('a_search'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Start indexing
if (isset($_POST['start']) || isset($_GET['batchstart']))
{
	$batchsize = 5000; // Process this many posts per batch
	$batchcount = request_var('batchcount', 1);
	$batchstart = request_var('batchstart', 0);
	$loopcount = 0;

	$fulltext = new fulltext_search();

	// Search re-indexing is tough on the server ... so we'll check the load
	// each loop and if we're on a 1min load of 3 or more we'll re-load the page
	// and try again. No idea how well this will work in practice so we'll see ...
	if (file_exists('/proc/loadavg'))
	{
		if ($load = @file('/proc/loadavg'))
		{
			list($load) = explode(' ', $load[0]);

			if ($load > 3)
			{
				redirect("adm/admin_search.$phpEx$SID&batchstart=$batchstart&batchcount=$batchcount", 3);
			}
		}
	}

	if (!$batchstart)
	{
		// Take board offline
		set_config('board_disable', 1);

		// Empty existing tables
		$db->sql_query("TRUNCATE " . SEARCH_TABLE);
		$db->sql_query("TRUNCATE " . SEARCH_WORD_TABLE);
		$db->sql_query("TRUNCATE " . SEARCH_MATCH_TABLE);
	}

	// Fetch a batch of posts_text entries
	$sql = "SELECT COUNT(*) AS total, MAX(post_id) AS max_post_id, MIN(post_id) AS min_post_id
		FROM " . POSTS_TABLE;
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$totalposts = $row['total'];
	$max_post_id = $row['max_post_id'];

	$batchstart = (!$batchstart) ? $row['min_post_id'] : $batchstart;
	$batchend = $batchstart + $batchsize;

	$db->sql_freeresult($result);

	$sql = "SELECT *
		FROM " . POSTS_TABLE . "
		WHERE post_id
			BETWEEN $batchstart
				AND $batchend";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$fulltext->add('admin', $row['post_id'], $row['post_text'], $row['post_subject']);
		}
		while ($row = $db->sql_fetchrow($result));
	}

	$db->sql_freeresult($result);

	$batchcount++;

	if (($batchstart + $batchsize) < $max_post_id)
	{
		redirect("adm/admin_search.$phpEx$SID&batchstart=" . ($batchstart + $batchsize) . "&batchcount=$batchcount", 3);
	}
	else
	{
		set_config('board_disable', 0);

		// search tidy
		$fulltext->search_tidy();

		adm_page_header($user->lang['SEARCH_INDEX']);

?>

<h1><?php echo $user->lang['SEARCH_INDEX']; ?></h1>

<p><?php echo $user->lang['SEARCH_INDEX_COMPLETE']; ?></p>

<?php

		adm_page_footer();

	}

	exit;

}
else if (isset($_POST['cancel']))
{
	set_config('board_disable', 0);
	adm_page_header($user->lang['SEARCH_INDEX']);

?>

<h1><?php echo $user->lang['SEARCH_INDEX']; ?></h1>

<p><?php echo $user->lang['SEARCH_INDEX_CANCEL']; ?></p>

<?php

	adm_page_footer();

}
else
{
	adm_page_header($user->lang['SEARCH_INDEX']);

?>

<h1><?php echo $user->lang['SEARCH_INDEX']; ?></h1>

<p><?php echo $user->lang['SEARCH_INDEX_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_search.$phpEx$SID"; ?>"><table cellspacing="1" cellpadding="4" border="0" align="center" bgcolor="#98AAB1">
	<tr>
		<td class="cat" height="28" align="center">&nbsp;<input type="submit" name="start" value="<?php echo $user->lang['START']; ?>" class="btnmain" /> &nbsp; <input type="submit" name="cancel" value="<?php echo $user->lang['CANCEL']; ?>" class="btnmain" />&nbsp;</td>
	</tr>
</table></form>

<?php

	adm_page_footer();

}

?>