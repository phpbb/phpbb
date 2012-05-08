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
* viewtopic [U.S. English]
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
	'ATTACHMENT'						=> 'Prilog',
	'ATTACHMENT_FUNCTIONALITY_DISABLED'	=> 'Mogućnost dodavanja priloga je onemogućena.',

	'BOOKMARK_ADDED'		=> 'Tema je uspješno zabilježena.',
	'BOOKMARK_ERR'			=> 'Zabilježavanje teme nije uspjelo. Molimo vas da pokušate ponovo.',
	'BOOKMARK_REMOVED'		=> 'Tema je uspješno uklonjena iz zabilježenih.',
	'BOOKMARK_TOPIC'		=> 'Zabilježi temu',
	'BOOKMARK_TOPIC_REMOVE'	=> 'Ukloni iz zabilješki',
	'BUMPED_BY'				=> 'Zadnji bump od %1$s %2$s.',
	'BUMP_TOPIC'			=> 'Bumpuj temu',

	'CODE'					=> 'Kod',
	'COLLAPSE_QR'			=> 'Sakrij brzi odgovor',

	'DELETE_TOPIC'			=> 'Obriši temu',
	'DOWNLOAD_NOTICE'		=> 'Nemate potrebne dozvole za pregled datoteka priloženih uz ovu poruku.',

	'EDITED_TIMES_TOTAL'	=> 'Zadnji put uređivano od %1$s %2$s, ukupno uređivano %3$d puta.',
	'EDITED_TIME_TOTAL'		=> 'Zadnji put uređivano od %1$s %2$s, ukupno uređivano %3$d puta.',
	'EMAIL_TOPIC'			=> 'Pošalji email prijatelju',
	'ERROR_NO_ATTACHMENT'	=> 'Odabrani prilog više ne postoji.',

	'FILE_NOT_FOUND_404'	=> 'Datoteka <strong>%s</strong> ne postoji.',
	'FORK_TOPIC'			=> 'Kopiraj temu',
	'FULL_EDITOR'			=> 'Kompletan uređivač',
	
	'LINKAGE_FORBIDDEN'		=> 'Nije vam dozvoljeno pregledanje, preuzimanje i linkanje na ovoj stranici.',
	'LOGIN_NOTIFY_TOPIC'	=> 'Dobili ste obavijest o ovoj temi, molimo vas da se prijavite da biste je pogledali.',
	'LOGIN_VIEWTOPIC'		=> 'Forum od vas zahtjeva registraciju i prijavu za pregled ove teme.',

	'MAKE_ANNOUNCE'				=> 'Promijeni u “Obavijest”',
	'MAKE_GLOBAL'				=> 'Promijeni u “Opća”',
	'MAKE_NORMAL'				=> 'Promijeni u “Standardna tema”',
	'MAKE_STICKY'				=> 'Promijeni u “Važna”',
	'MAX_OPTIONS_SELECT'		=> 'Možete odabrati najviše <strong>%d</strong> opcija',
	'MAX_OPTION_SELECT'			=> 'Možete odabrati najviše <strong>jednu</strong> opciju',
	'MISSING_INLINE_ATTACHMENT'	=> 'Prilog <strong>%s</strong> više nije dostupan',
	'MOVE_TOPIC'				=> 'Premjesti temu',

	'NO_ATTACHMENT_SELECTED'=> 'Niste odabrali prilog za pregled ili preuzimanje.',
	'NO_NEWER_TOPICS'		=> 'Nema novijih tema u ovom forumu.',
	'NO_OLDER_TOPICS'		=> 'Nema starijih tema u ovom forumu.',
	'NO_UNREAD_POSTS'		=> 'Nema novih nepročitanih poruka za ovu temu.',
	'NO_VOTE_OPTION'		=> 'Morate odabrati opciju prije glasanja.',
	'NO_VOTES'				=> 'Nema glasova',

	'POLL_ENDED_AT'			=> 'Anketa završena %s',
	'POLL_RUN_TILL'			=> 'Anketa traje do %s',
	'POLL_VOTED_OPTION'		=> 'Glasali ste za ovu opciju',
	'PRINT_TOPIC'			=> 'Pregled za štampanje',

	'QUICK_MOD'				=> 'Alati za brzo uređivanje',
	'QUICKREPLY'			=> 'Brzi odgovor',
	'QUOTE'					=> 'Citat',

	'REPLY_TO_TOPIC'		=> 'Nazad na temu',
	'RETURN_POST'			=> '%sNazad na poruku%s',

	'SHOW_QR'				=> 'Brzi odgovor',
	'SUBMIT_VOTE'			=> 'Pošalji glas',

	'TOTAL_VOTES'			=> 'Ukupno glasova',

	'UNLOCK_TOPIC'			=> 'Otključaj temu',

	'VIEW_INFO'				=> 'Detalji poruke',
	'VIEW_NEXT_TOPIC'		=> 'Sljedeća tema',
	'VIEW_PREVIOUS_TOPIC'	=> 'Prethodna tema',
	'VIEW_RESULTS'			=> 'Pregledaj rezultate',
	'VIEW_TOPIC_POST'		=> '1 poruka',
	'VIEW_TOPIC_POSTS'		=> '%d poruka',
	'VIEW_UNREAD_POST'		=> 'Prva nepročitana poruka',
	'VISIT_WEBSITE'			=> 'WWW',
	'VOTE_SUBMITTED'		=> 'Glasali ste.',
	'VOTE_CONVERTED'		=> 'Promjena glasova nije podržana za konvertovane ankete.',

));

?>