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

$vars = array('lang_main' => 'lang', 'lang_admin' => 'lang', 'lang_faq' => 'faq', 'lang_bbcode' => 'faq');

$dirname = "./../language";
$dir = opendir($dirname);

while ( $file = readdir($dir) )
{
	if ( $file != 'CVS' && !is_file($dirname . "/" . $file) && !is_link($dirname . "/" . $file) )
	{
		foreach($vars as $lang_file => $lang_var)
		{
			$$lang_var = array();

			include($dirname . "/" . $file . "/" . $lang_file . '.php');

			$store = "";
			while( list($key, $value) = each($$lang_var) )
			{
				if ( !is_array($value) )
				{
					$key = ( is_string($key) ) ? "'$key'" : $key;
					$store .=  ( ( $store != "" ) ? ", \n\t" : "" ) . "$key => '" . addslashes($value) . "'";
				}
				else
				{
					$key = ( is_string($key) ) ? "'$key'" : $key;
					$store .= ( ( $store != "" ) ? ", \n\t" : "" ) . "$key => array(\n\t\t";

					$store2 = "";
					while( list($key2, $value2) = each($value) )
					{
						$key2 = ( is_string($key) ) ? "'$key2'" : $key2;
						$store2 .= ( ( $store2 != "" ) ? ", \n\t\t" : "" ) . "$key2 => '" . addslashes($value2) . "'";
					}
					$store .= $store2 . "\n\t)";
				}
			}

			$store = "<?php\n\$$lang_var = array(\n\t$store\n);\n?".">";

			$fp = fopen($dirname . "/" . $file . "/" . $lang_file . '.php', 'w');

			fwrite($fp, $store);

			fclose($fp);

		}
	}
}

?>