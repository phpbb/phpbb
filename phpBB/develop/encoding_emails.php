<?php
/***************************************************************************
 *                            revar_lang_files.php
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

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
//die("Please read the first lines of this script for instructions on how to enable it");

$dirname = "./../language";
$dir = opendir($dirname);

while ( $file = readdir($dir) )
{
	if ( ereg('^lang_', $file) && !is_file(phpbb_realpath($dirname . '/' . $file)) && !is_link(phpbb_realpath($dirname . '/' . $file)) )
	{
		include($dirname . '/' . $file . '/lang_main.php');

		$lang_dir = opendir($dirname . '/' . $file . '/email');

		while ( $email = readdir($lang_dir) )
		{
			if ( ereg('\.tpl$', $email) && is_file(phpbb_realpath($dirname . '/' . $file . '/email/' . $email)) )
			{
				$fp = fopen($dirname . '/' . $file . '/email/' . $email, 'r+');

				$email_file = "";
				while ( $line = fread($fp, 100000) )
				{
					$email_file .= $line;
				}

				if ( !preg_match('/^Charset: .*?$/m', $email_file) )
				{
					$email_file = preg_replace('/^((Subject: .*?\n)(\n))?/i', "\\2Charset: " . $lang['ENCODING'] . "\n\n", $email_file);
				}
		
				echo '<b>' . $dirname . '/' . $file . '/email/' . $email . "</b><br />\n";
				echo nl2br($email_file);
				echo "\n\n<br /><br />\n\n";

				fseek($fp, 0);
				fwrite($fp, $email_file);
				fclose($fp);
			}
		}
		echo "\n\n<hr />\n\n";
	}
}

?>