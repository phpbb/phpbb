<?php
/***************************************************************************
 *                               pagestart.php
 *                            -------------------
 *   begin                : Thursday, Aug 2, 2001
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

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

define('IN_ADMIN', true);
define('NEED_SID', true);
require($phpbb_root_path . 'common.'.$phpEx);
require_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

// Start session management
$user->start($update);
$user->setup();
$auth->acl($user->data);
// End session management

// -----------------------------
// Functions
function page_header($sub_title, $meta = '', $table_html = true)
{
	global $config, $db, $user, $phpEx;

	define('HEADER_INC', true);

	// gzip_compression
	if ($config['gzip_compress'])
	{
		if (extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	header("Content-type: text/html; charset=" . $user->lang['ENCODING']);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $user->lang['ENCODING']; ?>">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="subSilver.css" type="text/css">
<?php

	echo $meta;

?>
<style type="text/css">
<!--
th		{ background-image: url('images/cellpic3.gif') }
td.cat	{ background-image: url('images/cellpic1.gif') }
//-->
</style>
<title><?php echo $config['sitename'] . ' - ' . $page_title; ?></title>
</head>
<body>

<?php

	if ($table_html)
	{

?>
<a name="top"></a>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td colspan="2" height="25" align="right" nowrap="nowrap"><span class="subtitle">&#0187; <i><?php echo $sub_title; ?></i></span> &nbsp;&nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" />

<?php

	}

}

function page_footer($copyright_html = true)
{
	global $cache, $config, $db, $phpEx;

	// Close our DB connection.
	$db->sql_close();

?>

		</td>
	</tr>
</table>
<?php

	if ($copyright_html)
	{

?>

<div align="center"><span class="copyright">Powered by phpBB <?php echo $config['version']; ?> &copy; 2002 <a href="http://www.phpbb.com/" target="_phpbb" class="copyright">phpBB Group</a></span></div>

<br clear="all" />

</body>
</html>
<?php

	}

	if (!empty($cache))
	{
		$cache->save_cache();
	}

	exit;
}

function page_message($title, $message, $show_header = false)
{
	global $phpEx, $SID, $user;

	if ($show_header)
	{

?>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><a href="../index.<?php echo $phpEx . $SID; ?>"><img src="images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></a></td>
		<td width="100%" background="images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle"><?php echo $user->lang['ADMIN_TITLE']; ?></span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<?php

	}

?>

<br /><br />

<table class="bg" width="80%" cellpadding="4" cellspacing="1" border="0" align="center">
	<tr>
		<th><?php echo $title; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><?php echo $message; ?></td>
	</tr>
</table>

<br />

<?php

}
// End Functions
// -----------------------------

?>