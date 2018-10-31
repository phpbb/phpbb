<?php
/**
*
* acp_database [Română]
*
* @package language
* @version $Id: database.php,v 1.24 2007/08/13 12:14:06 acydburn Exp $
* @translate $Id: database.php,v 1.24 2007/12/29 17:05:00 www.phpbb.ro (skeleton) Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

// Database Backup/Restore
$lang = array_merge($lang, array(
	'ACP_BACKUP_EXPLAIN'	=> 'Aici puteţi face copii de rezervă ale tuturor datelor ce ţin de phpBB. Datele se pot stoca în directorul <samp>store/</samp> sau se pot descărca direct. Dacă serverul dumneavoastră suportă, puteţi comprima fişierul pentru a reduce dimensiunea sa înainte de a efectua operaţiunea de descărcare.',
	'ACP_RESTORE_EXPLAIN'	=> 'Aceasta va efectua o restaurare completă a tuturor tabelelor phpBB dintr-un fişier salvat. Dacă serverul dumneavoastră suportă, puteţi folosi un fişier text comprimat gzip sau bzip2, iar acesta va fi decomprimat automat. <strong>ATENŢIE:</strong> Această procedură va rescrie orice informaţie deja existentă. Procesul de restaurare poate dura un timp îndelungat; vă rugăm nu părăsiţi această pagină până când restaurarea nu se finalizează. Fişierele de siguranţă sunt păstrate în directorul <samp>store/</samp> şi teoretic sunt generate de funcţionalitatea de restaurare a phpBB-ului. Procesul de restaurare a copiilor de siguranţă ce nu au fost create în sistem poate sau nu poate să funcţioneze corect.',

	'BACKUP_DELETE'		=> 'Fişierul de backup a fost şters cu succes.',
	'BACKUP_INVALID'	=> 'Fişierul selectat pentru backup nu este valid.',
	'BACKUP_OPTIONS'	=> 'Opţiuni de salvare (Backup)',
	'BACKUP_SUCCESS'	=> 'Fişierul copie de siguranță a fost creat cu succes.',
	'BACKUP_TYPE'		=> 'Tip de backup',

	'DATABASE'         			=> 'Instrumentele bazei de date',
	'DATA_ONLY'	          		=> 'Doar datele',
	'DELETE_BACKUP'		        => 'Şterge copie de siguranță',
	'DELETE_SELECTED_BACKUP'	=> 'Sunteţi sigur că vreţi să ştergeţi copia de siguranţă selectată?',
	'DESELECT_ALL'      		=> 'Demarchează tot',
	'DOWNLOAD_BACKUP'       	=> 'Descărcare copie de siguranță',

	'FILE_TYPE'			=> 'Tip fişier',
	'FILE_WRITE_FAIL'	=> 'Nu s-a reuşit scrierea în directorul de stocare.',
	'FULL_BACKUP'		=> 'Tot',

	'RESTORE_FAILURE'		=> 'Fişierul copiei de siguranță s-ar putea să fie corupt.',
	'RESTORE_OPTIONS'		=> 'Opţiuni restaurare',
	'RESTORE_SELECTED_BACKUP'	=> 'Sunteți sigur că vreți să restaurați copia de siguranță selectată?',
	'RESTORE_SUCCESS'		=> 'Baza de date a fost restaurată cu succes.<br /><br />Forumul dumneavoastră va trebui să revină la stadiul în care a fost când a fost salvată copia de siguranţă.',

	'SELECT_ALL'			=> 'Marchează tot',
	'SELECT_FILE'			=> 'Marchează fişier',
	'START_BACKUP'			=> 'Porneşte backup',
	'START_RESTORE'			=> 'Porneşte restaurare',
	'STORE_AND_DOWNLOAD'	=> 'Salvează fişier şi apoi descarcă',
	'STORE_LOCAL'			=> 'Salvează fişier local',
	'STRUCTURE_ONLY'		=> 'Doar structura',

	'TABLE_SELECT'		=> 'Marchează tabel',
	'TABLE_SELECT_ERROR'=> 'Trebuie să selectaţi cel puţin un tabel.',
));

?>