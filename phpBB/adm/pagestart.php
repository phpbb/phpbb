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
require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

// Start session management
$user->start();
$user->setup('admin');

// Did user forget to login? Give 'em a chance to here ...
if ($user->data['user_id'] == ANONYMOUS)
{
	login_box("adm/index.$phpEx$SID", '', $user->lang['LOGIN_ADMIN']);
}

$auth->acl($user->data);
// End session management

// Some oft used variables
$safe_mode	= (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on') ? true : false;
$file_uploads = (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on') ? true : false; 


// -----------------------------
// Functions
function adm_page_header($sub_title, $meta = '', $table_html = true)
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
<title><?php echo $config['sitename'] . ' - ' . $sub_title; ?></title>
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

function adm_page_footer($copyright_html = true)
{
	global $cache, $config, $db, $phpEx;

	if (!empty($cache))
	{
		$cache->unload();
	}

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

<div class="copyright" align="center">Powered by phpBB <?php echo $config['version']; ?> &copy; 2002 <a href="http://www.phpbb.com/" target="_phpbb">phpBB Group</a></div>

<br clear="all" />

</body>
</html>
<?php

	}

	exit;
}

function adm_page_message($title, $message, $show_header = false, $show_prev_info = true)
{
	global $phpEx, $SID, $user, $_SERVER, $_ENV;

	if ($show_header)
	{

?>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><a href="<?php echo "../index.$phpEx$SID"; ?>"><img src="images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></a></td>
		<td width="100%" background="images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle"><?php echo $user->lang['ADMIN_TITLE']; ?></span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<?php

	}

	$page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
?>

<br /><br />

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $title; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><?php echo $message; ?>
<?php
	if ($page && $show_prev_info)
	{
		echo '<br /><br />';
		echo sprintf($user->lang['RETURN_PAGE'], '<a href="' . $page . '">', '</a>');
	}
?>		</td>
	</tr>
</table>

<br />

<?php

}

function adm_page_confirm($title, $message)
{
	global $phpEx, $SID, $user;

	// Grab data from GET and POST arrays ... note this is _not_ 
	// validated! Everything is typed as string to ensure no
	// funny business on displayed hidden field data. Validation
	// will be carried out by whatever processes this form.
	$var_ary = array_merge($_GET, $_POST);

	$s_hidden_fields = '';
	foreach ($var_ary as $key => $var)
	{
		if (empty($var))
		{
			continue;
		}

		if (is_array($var))
		{
			foreach ($var as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $_k => $_v)
					{
						set_var($var[$k][$_k], $_v, 'string');
						$s_hidden_fields .= "<input type=\"hidden\" name=\"${key}[$k][$_k]\" value=\"" . addslashes($_v) . '" />';
					}
				}
				else
				{
					set_var($var[$k], $v, 'string');
					$s_hidden_fields .= "<input type=\"hidden\" name=\"${key}[$k]\" value=\"" . addslashes($v) . '" />';
				}
			}
		}
		else
		{
			set_var($var, $var, 'string');
			$s_hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . addslashes($var) . '" />';
		}
		unset($var_ary[$key]);
	}

?>

<br /><br />

<form name="confirm" method="post" action="<?php echo $_SERVER['SCRIPT_NAME'] . $SID; ?>">
<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $title; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><?php echo $message; ?><br /><br /><input class="btnlite" type="submit" name="confirm" value="<?php echo $user->lang['YES']; ?>" />&nbsp;&nbsp;<input class="btnmain" type="submit" name="cancel" value="<?php echo $user->lang['NO']; ?>" /></td>
	</tr>
</table>

<?php echo $s_hidden_fields; ?>
</form>

<br />

<?php

	adm_page_footer();

}
// End Functions
// -----------------------------

?>