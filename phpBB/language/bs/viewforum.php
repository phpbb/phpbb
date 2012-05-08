<?php
/**
*
* This file is part of U.S. English phpBB translation.
* Copyright (c) 2010 Maël Soucaze.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; version 2 of the License.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
* viewforum [U.S. English]
*
* @package   language
* @author    Maël Soucaze <maelsoucaze@gmail.com> (Maël Soucaze) http://mael.soucaze.com/
* @author    sevenalive (Robert Baker) http://sevenupdate.com/
* @author    Unknown Bliss <sa007@phpbbdevelopers.net> (Michael C.) http://www.unknownbliss.co.uk/
* @copyright (c) 2005 phpBB Group
* @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
* @version   $Id$
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACTIVE_TOPICS'			=> 'Aktivne teme',
	'ANNOUNCEMENTS'			=> 'Najave',

	'FORUM_PERMISSIONS'		=> 'Dozvole na forumu',

	'ICON_ANNOUNCEMENT'		=> 'Najava',
	'ICON_STICKY'			=> 'Važno',

	'LOGIN_NOTIFY_FORUM'	=> 'Dobili ste obavijest o ovom forumu, molimo vas da se prijavite da biste ga pogledali.',

	'MARK_TOPICS_READ'		=> 'Označi temu kao pročitanu',

	'NEW_POSTS_HOT'			=> 'Nove poruke [ popularno ]',	// Not used anymore
	'NEW_POSTS_LOCKED'		=> 'Nove poruke [ zaključano ]',	// Not used anymore
	'NO_NEW_POSTS_HOT'		=> 'Nema novih poruka [ popularno ]',	// Not used anymore
	'NO_NEW_POSTS_LOCKED'	=> 'Nema novih poruka [ zaključano ]',	// Not used anymore
	'NO_READ_ACCESS'		=> 'Nemate potrebne dozvole za čitanje tema u ovom forumu.',
	'NO_UNREAD_POSTS_HOT'		=> 'Nema nepročitanih poruka [ popularno ]',
	'NO_UNREAD_POSTS_LOCKED'	=> 'Nema nepročitanih poruka [ zaključano ]',

	'POST_FORUM_LOCKED'		=> 'Forum je zaključan',

	'TOPICS_MARKED'			=> 'Teme na ovom forumu su sada označene kao pročitane.',

	'UNREAD_POSTS_HOT'		=> 'Nepročitane poruke [ popularne ]',
	'UNREAD_POSTS_LOCKED'	=> 'Nepročitane poruke [ zaključane ]',

	'VIEW_FORUM'			=> 'Pregledaj forum',
	'VIEW_FORUM_TOPIC'		=> '1 tema',
	'VIEW_FORUM_TOPICS'		=> '%d tema',
));

?>