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
* acp_language [U.S. English]
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
	'ACP_FILES'						=> 'Administratorske jezičke datoteke',
	'ACP_LANGUAGE_PACKS_EXPLAIN'	=> 'Ovdje možete instalirati/ukloniti jezičke pakete. Početni jezik je označen zvjezdicom (*).',

	'EMAIL_FILES'			=> 'Email predlošci',

	'FILE_CONTENTS'				=> 'Sadržaj datoteka',
	'FILE_FROM_STORAGE'			=> 'Datoteka iz foldera za pohranu',

	'HELP_FILES'				=> 'Datoteke za pomoć',

	'INSTALLED_LANGUAGE_PACKS'	=> 'Instalirani jezički paketi',
	'INVALID_LANGUAGE_PACK'		=> 'Izgleda da odabrani jezički paket nije ispravan. Molimo vas da paket provjerite i dodate ga ponovo ukoliko to bude neophodno.',
	'INVALID_UPLOAD_METHOD'		=> 'Odabrana metoda dodavanja nije ispravna, molimo vas da odaberete drugu metodu.',

	'LANGUAGE_DETAILS_UPDATED'			=> 'Detalji o jeziku su uspješno ažurirani.',
	'LANGUAGE_ENTRIES'					=> 'Jezički unosi',
	'LANGUAGE_ENTRIES_EXPLAIN'			=> 'Ovdje možete promijeniti postojeće jezičke unose i unose koji još nisu prevedeni.<br /><strong>Napomena:</strong> Promjene koje napravite će biti sačuvane u posebnom folderu koji možete preuzeti. Promjene neće biti vidljive sve dok ne zamijenite originalne jezičke datoteke na vašem serveru.',
	'LANGUAGE_FILES'					=> 'Jezičke datoteke',
	'LANGUAGE_KEY'						=> 'Jezički ključ',
	'LANGUAGE_PACK_ALREADY_INSTALLED'	=> 'Ovaj jezički paket je već instaliran.',
	'LANGUAGE_PACK_DELETED'				=> '<strong>%s</strong> jezički paket je uspješno uklonjen. Svi korisnici koji su koristili ovaj jezik su prebačeni na početni jezik foruma.',
	'LANGUAGE_PACK_DETAILS'				=> 'Detalji jezičkog paketa',
	'LANGUAGE_PACK_INSTALLED'			=> '<strong>%s</strong> jezički paket je uspješno instaliran.',
	'LANGUAGE_PACK_ISO'					=> 'ISO',
	'LANGUAGE_PACK_LOCALNAME'			=> 'Lokalni naziv',
	'LANGUAGE_PACK_NAME'				=> 'Naziv',
	'LANGUAGE_PACK_NOT_EXIST'			=> 'Odabrani jezički paket ne postoji.',
	'LANGUAGE_PACK_USED_BY'				=> 'Broj korisnika (uključujući i robote)',
	'LANGUAGE_VARIABLE'					=> 'Jezička promjenjiva',
	'LANG_AUTHOR'						=> 'Autor jezičkog paketa',
	'LANG_ENGLISH_NAME'					=> 'Engleski naziv',
	'LANG_ISO_CODE'						=> 'ISO kod',
	'LANG_LOCAL_NAME'					=> 'Lokalni naziv',

	'MISSING_LANGUAGE_FILE'		=> 'Nedostaje jezička datoteka: <strong style="color:red">%s</strong>',
	'MISSING_LANG_VARIABLES'	=> 'Nedostaje jezička varijabla',
	'MODS_FILES'				=> 'MOD-ove jezičke datoteke',

	'NO_FILE_SELECTED'				=> 'Niste odabrali jezičku datoteku.',
	'NO_LANG_ID'					=> 'Niste odabrali jezički paket.',
	'NO_REMOVE_DEFAULT_LANG'		=> 'Ne možete ukloniti početni jezički paket.<br />Ako želite ukloniti ovaj jezički paket, prvo ćete morati promijeniti početni jezik foruma.',
	'NO_UNINSTALLED_LANGUAGE_PACKS'	=> 'Nema uklonjenih jezičkih paketa',

	'REMOVE_FROM_STORAGE_FOLDER'		=> 'Ukloni iz foldera za pohranu',

	'SELECT_DOWNLOAD_FORMAT'	=> 'Odaberite format preuzimanja',
	'SUBMIT_AND_DOWNLOAD'		=> 'Sačuvaj i preuzmi datoteku',
	'SUBMIT_AND_UPLOAD'			=> 'Sačuvaj i dodaj datoteku',

	'THOSE_MISSING_LANG_FILES'			=> 'Sljedeće jezičke datoteke nedostaju u folderu za %s jezik',
	'THOSE_MISSING_LANG_VARIABLES'		=> 'Sljedeće jezičke varijable nedostaju u jezičkom paketu za <strong>%s</strong> jezik',

	'UNINSTALLED_LANGUAGE_PACKS'	=> 'Uklonjeni jezički paketi',

	'UNABLE_TO_WRITE_FILE'		=> 'Nije moguće zapisati datoteku u %s.',
	'UPLOAD_COMPLETED'			=> 'Dodavanje je uspješno završeno.',
	'UPLOAD_FAILED'				=> 'Dodavanje nije uspjelo iz nepoznatog razloga. Morat ćete ručno zamijeniti datoteke.',
	'UPLOAD_METHOD'				=> 'Metode dodavanja',
	'UPLOAD_SETTINGS'			=> 'Postavke dodavanja',

	'WRONG_LANGUAGE_FILE'		=> 'Odabrana jezička datoteka nije ispravna.',
));

?>