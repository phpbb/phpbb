<?php
/***************************************************************************
 *                                Colours.php
 *                            -------------------
 *   begin                : Friday, September 5, 2003
 *   email                : nlwebhebbies@nlwebhebbies.nl
 *
 *   $Id: colour.php,v 0.0.1 2003/10/05
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

/***************************************************************************
 *
 *   Some code in this file I borrowed from the original index.php, Welcome
 *   Avatar MOD and others...
 *
 ***************************************************************************/

define('IN_PHPBB', true);
$phpbb_root_path = '../';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
$lang['Colour_card'] = !empty($lang['Colour_card']) ? $lang['Colour_card'] : 'Colour Page';
$pagination = $lang['Colour_card'];
//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX);
init_userprefs($userdata);
//
// End session management
//
$page_title = $lang['Colour_card'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx); 
        $template->set_filenames(array(
                'body' => 'colour.tpl')
        );
make_jumpbox($phpbb_root_path.'viewforum.'.$phpEx);

        $template->assign_vars(array(
		'PAGINATION' => !isset($pagination) ? $pagination : '', 
                'L_COLOUR_CARD' => $lang['Colour_card'])
        );

$template->pparse('body');

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>