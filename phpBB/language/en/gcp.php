<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : gcp.php [ English ]
// STARTED   : Sat Dec 16, 2000
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// DO NOT CHANGE
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang += array(
	'GROUP_CLOSED'		=> 'Closed',
	'GROUP_DESC'		=> 'Group description',
	'GROUP_HIDDEN'		=> 'Hidden',
	'GROUP_MEMBERS'		=> 'Group members',
	'GROUP_NAME'		=> 'Group name',
	'GROUP_OPEN'		=> 'Open',
	'GROUP_TYPE'		=> 'Group type',

	'No_groups_exist'	=> 'No Groups Exist',
	
	'REMOVE_SELECTED'	=> 'Remove selected'
);

?>