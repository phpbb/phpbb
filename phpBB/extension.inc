<?php
/***************************************************************************  
 *                               extension.inc
 *                            -------------------                         
 *   begin                : Saturday, Feb 13, 2001 
 *   copyright            : (C) 2001 The phpBB Group        
 *   email                : support@phpbb.com                           
 *                                                          
 *   $Id$
 *                                                            
 * 
 ***************************************************************************/ 

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

//
// Change this if your extension is not .php!
//
$phpEx = 'php';

//
// For debug timing
// 
$mtime = microtime();
$mtime = explode(' ',$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

?>