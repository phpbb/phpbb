<?php
/***************************************************************************
 *						lang_extend_split_topic_type.php [English]
 *						--------------------------------
 *	begin				: 28/09/2003
 *	copyright			: Ptirhiik
 *	email				: ptirhiik@clanmckeen.com
 *
 *	version				: 1.0.0 - 28/09/2003
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
	die("Hacking attempt");
}

// admin part
if ( $lang_extend_admin )
{
	$lang['Lang_extend_split_topic_type'] = 'Split Topic Type';
}

$lang['Split_settings']			= 'Split topics per type';
$lang['split_global_announce']	= 'Split global announcement';
$lang['split_announce']			= 'Split announcement';
$lang['split_sticky']			= 'Split sticky';
$lang['split_topic_split']		= 'Seperate topic types in different boxes';

?>