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
 *
 ***************************************************************************/


/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

//
// Show the overall footer.
//
if($userdata['user_level'] == ADMIN)
{
	$admin_link = "<a href=\"admin/index.$phpEx\">Administration Panel</a>";
}
$current_time = time();

$template->set_filenames(array(
	"overall_footer" => "overall_footer.tpl")
);

$template->assign_vars(array(
	"PHPBB_VERSION" => "2.0-alpha",
	"ADMIN_LINK" => $admin_link));

$template->pparse("overall_footer");

//
// Close our DB connection.
//
$db->sql_close();


//
// Output page creation time
//
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

printf("<center><font size=-2>phpBB Created this page in %f seconds.</font></center>", $totaltime);

//
// Compress buffered output if required
// and send to browser
//
if($do_gzip_compress)
{
	//
	// Borrowed from php.net!
	//
	$gzip_contents = ob_get_contents(); 
	ob_end_clean(); 

	$gzip_size = strlen($gzip_contents); 
	$gzip_crc = crc32($gzip_contents); 

	$gzip_contents = gzcompress($gzip_contents, 9); 
	$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4); 

	echo "\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
	echo $gzip_contents; 
	echo pack("V", $gzip_crc);
	echo pack("V", $gzip_size);
}

exit;

?>