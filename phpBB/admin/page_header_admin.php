<?php
/***************************************************************************
 *                           page_header_admin.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
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

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

define('HEADER_INC', true);

//
// gzip_compression
//
$do_gzip_compress = FALSE;
if ( $board_config['gzip_compress'] )
{
	$phpver = phpversion();

	if ( $phpver >= '4.0.4pl1' && strstr($HTTP_USER_AGENT,'compatible') )
	{
		if ( extension_loaded('zlib') )
		{
			ob_start('ob_gzhandler');
		}
	}
	else if ( $phpver > '4.0' )
	{
		if ( strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') )
		{
			if ( extension_loaded('zlib') )
			{
				$do_gzip_compress = TRUE;
				ob_start();
				ob_implicit_flush(0);

				header("Content-Encoding: gzip");
			}
		}
	}
}

header("Content-type: text/html; charset=" . $lang['ENCODING']);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['ENCODING']; ?>">
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
<title><?php echo $board_config['sitename'] . ' - ' . $page_title; ?></title>
</head>
<body>

<a name="top"></a>

<?php

?>