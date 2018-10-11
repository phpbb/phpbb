<?php
/***************************************************************************
 *						lang_extend_post_icons.php [English]
 *						--------------------------
 *	begin				: 28/09/2003
 *	copyright			: Ptirhiik
 *	email				: ptirhiik@clanmckeen.com
 *
 *	version				: 1.0.1 - 28/10/2003
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
	$lang['Lang_extend_post_icons']		= 'Post Icons';

	$lang['Icons_settings_explain']		= 'Here you can add, edit or delete posts icons';
	$lang['Icons_auth']					= 'Auth level';
	$lang['Icons_auth_explain']			= 'The icon will be available only to the users suiting this requirement';
	$lang['Icons_defaults']				= 'Default assignement';
	$lang['Icons_defaults_explain']		= 'Those assignments will be used on the topics lists when no icon is defined for a topic';
	$lang['Icons_delete']				= 'Delete an icon';
	$lang['Icons_delete_explain']		= 'Please choose an icon in order to replace this one :';
	$lang['Icons_confirm_delete']		= 'Are you sure you want to delete this one ?';

	$lang['Icons_lang_key']				= 'Icon title';
	$lang['Icons_lang_key_explain']		= 'The icon title will be displayed when the user set his mouse on the icon (title or alt HTML statement). You can use text, or a key of the language array. <br />(check language/lang_<i>your_language</i>/lang_main.php).';
	$lang['Icons_icon_key']				= 'Icon';
	$lang['Icons_icon_key_explain']		= 'Icon url or key to the images array. <br />(check templates/<i>your_template</i>/<i>your_template</i>.cfg)';

	$lang['Icons_error_title']			= 'The icon title is empty';
	$lang['Icons_error_del_0']			= 'You can\'t remove the default empty icon';

	$lang['Refresh']					= 'Refresh';
	$lang['Usage']						= 'Usage';

	$lang['Image_key_pick_up']			= 'Pick up an image key';
	$lang['Lang_key_pick_up']			= 'Pick up a lang key';
}

$lang['Icons_settings']			= 'Posts icons';
$lang['Icons_per_row']			= 'Icons per row';
$lang['Icons_per_row_explain']	= 'Set here the number of icons displayed per row in the posting display';
$lang['post_icon_title']		= 'Message Icon';
// icons
$lang['icon_none']				= 'No icon';
$lang['icon_note']				= 'Note';
$lang['icon_important']			= 'Important';
$lang['icon_idea']				= 'Idea';
$lang['icon_warning']			= 'Warning !';
$lang['icon_question']			= 'Question';
$lang['icon_cool']				= 'Cool';
$lang['icon_funny']				= 'Funny';
$lang['icon_angry']				= 'Grrrr !';
$lang['icon_sad']				= 'Snif !';
$lang['icon_mocker']			= 'Hehehe !';
$lang['icon_shocked']			= 'Oooh !';
$lang['icon_complicity']		= 'Complicity';
$lang['icon_bad']				= 'Bad !';
$lang['icon_great']				= 'Great !';
$lang['icon_disgusting']		= 'Beark !';
$lang['icon_winner']			= 'Gniark !';
$lang['icon_impressed']			= 'Oh yes !';
$lang['icon_roleplay']			= 'Roleplay';
$lang['icon_fight']				= 'Fight';
$lang['icon_loot']				= 'Loot';
$lang['icon_picture']			= 'Picture';
$lang['icon_calendar']			= 'Calendar event';

?>