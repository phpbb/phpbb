<?php
/** 
*
* acp_attachments [Română]
*
* @package language
* @version $Id: attachments.php,v 1.31 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: attachments.php,v 1.31 2007/12/29 17:05:00 www.phpbb.ro (Ro Silviu) Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
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
	'ACP_ATTACHMENT_SETTINGS_EXPLAIN'	=> 'Aici puteţi configura setările pentru fişierele ataşate şi categoriile speciale asociate.',
	'ACP_EXTENSION_GROUPS_EXPLAIN'		=> 'Aici puteţi adăuga, şterge, modifica sau scoate din funcţiune grupurile de extensii. Opţiunile includ alocarea categoriilor speciale, schimbarea mecanismului de descărcare şi definirea unei iconiţe pentru încărcare care va fi afişata în faţa fişierelor ataşate ce aparţin acelui grup.',
	'ACP_MANAGE_EXTENSIONS_EXPLAIN'		=> 'Aici puteţi administra extensiile permise. Pentru a activa o extensie, apelaţi la panoul de administrare al grupurilor de extensii. Nu recomandăm permiterea extensiilor pentru scriping de genul <code>php</code>, <code>php3</code>, <code>php4</code>, <code>phtml</code>, <code>pl</code>, <code>cgi</code>, <code>py</code>, <code>rb</code>, <code>asp</code>, <code>aspx</code> şi aşa mai departe.',
	'ACP_ORPHAN_ATTACHMENTS_EXPLAIN'	=> 'Aici puteţi vedea fişierele din directorul cu fişiere ataşate care nu sunt asociate cu vreun mesaj. Acest lucru se poate întâmpla când utilizatorii ataşează fişiere, dar nu trimit mesajul. Puteţi şterge sau ataşa aceste fişiere unor mesaje existente. Ataşarea unui fişier la un mesaj existent necesită ID-ul mesajului, care trebuie determinat manual de către dumneavoastră. Aceasta va asocia un fişier ataşat deja încărcat unui mesaj trimis.',
	'ADD_EXTENSION'						=> 'Adaugă extensie',
	'ADD_EXTENSION_GROUP'				=> 'Adaugă grup de extensii',
	'ADMIN_UPLOAD_ERROR'				=> 'Erori în timpul ataşării fişierului: „%s”',
	'ALLOWED_FORUMS'					=> 'Forumuri permise',
	'ALLOWED_FORUMS_EXPLAIN'			=> 'Capabil să folosească extensiile specificate în forumurile selectate (sau la toate dacă sunt selectate).',
	'ALLOWED_IN_PM_POST'				=> 'Permis',
	'ALLOW_ATTACHMENTS'					=> 'Permite fişiere ataşate',
	'ALLOW_ALL_FORUMS'					=> 'Permite toate forumurile',
	'ALLOW_IN_PM'						=> 'Permis în mesaje private',
	'ALLOW_PM_ATTACHMENTS'				=> 'Permite fişiere ataşate în mesaje private',
	'ALLOW_SELECTED_FORUMS'				=> 'Doar forumuri selectate mai jos',
	'ASSIGNED_EXTENSIONS'				=> 'Extensii alocate',
	'ASSIGNED_GROUP'					=> 'Grupuri de extensii alocate',
	'ATTACH_EXTENSIONS_URL'				=> 'Extensii',
	'ATTACH_EXT_GROUPS_URL'				=> 'Grupuri de extensii',
	'ATTACH_ID'							=> 'ID',
	'ATTACH_MAX_FILESIZE'				=> 'Mărimea maximă a fişierelor',
	'ATTACH_MAX_FILESIZE_EXPLAIN'		=> 'Mărimea maximă a unui fişier. Dacă această valoare este 0 atunci dimensiunea fișierelor ce pot fi încărcate este limitată doar de configurația PHP.',
	'ATTACH_MAX_PM_FILESIZE'			=> 'Mărimea maximă a fişierelor în mesaje private',
	'ATTACH_MAX_PM_FILESIZE_EXPLAIN'	=> 'Mărimea maximă a fiecărui fişier ataşat unui mesaj privat, 0 înseamnă nelimitat.',
	'ATTACH_ORPHAN_URL'					=> 'Fişiere ataşate orfane',
	'ATTACH_POST_ID'					=> 'ID-ul mesajului',
	'ATTACH_QUOTA'						=> 'Limita totală pentru fişiere ataşate',
	'ATTACH_QUOTA_EXPLAIN'				=> 'Spaţiu maxim alocat pentru fişiere ataşate, 0 înseamnă nelimitat.',
	'ATTACH_TO_POST'					=> 'Ataşează fişier la mesaj',

	'CAT_FLASH_FILES'			=> 'Fişiere Flash',
	'CAT_IMAGES'				=> 'Imagini',
	'CAT_QUICKTIME_FILES'		=> 'Media în format Quicktime',
	'CAT_RM_FILES'				=> 'Stream-uri în format Real Media ',
	'CAT_WM_FILES'				=> 'Stream-uri în format Window Media',
  'CHECK_CONTENT'            => 'Verifică fişierele ataşate',
  'CHECK_CONTENT_EXPLAIN'      => 'Unele browsere pot fi păcălite folosind un mimetype incorect pentru fişierele publicate. Această opţiune asigură faptul că astfel de fişiere ce pot cauza probleme sunt respinse.',
	'CREATE_GROUP'				=> 'Crează un nou grup',
	'CREATE_THUMBNAIL'			=> 'Crează imagine micşorată',
	'CREATE_THUMBNAIL_EXPLAIN'	=> 'Crează imagine micşorată în toate situaţiile posibile.',

	'DEFINE_ALLOWED_IPS'			=> 'Defineşte IP-uri/nume de host-uri permise',
	'DEFINE_DISALLOWED_IPS'			=> 'Defineşte IP-uri/nume de host-uri nepermise',
	'DOWNLOAD_ADD_IPS_EXPLAIN'      => 'Pentru a specifica mai multe IP-uri/nume de host-uri, fiecare trebuie scris pe o nouă linie. Pentru a specifica o clasă de IP-uri, separaţi primul şi ultimul cu o cratimă (-). Pentru a specifica un <i>wildcard</i>, folosiţi „*”',
	'DOWNLOAD_REMOVE_IPS_EXPLAIN'	=> 'Poţi elimina (sau include) mai multe adrese IP într-un pas folosind combinaţia potrivită a mouse-ulului şi tastaturii calculatorului şi browser-ului. IP-urile excluse au un fundal albastru.',
	'DISPLAY_INLINED'				=> 'Afişează imaginile în linie',
	'DISPLAY_INLINED_EXPLAIN'		=> 'Dacă este setat pe Nu, imaginile ataşate vor apărea ca link-uri.',
	'DISPLAY_ORDER'					=> 'Ordinea afişării fişierelor ataşate',
	'DISPLAY_ORDER_EXPLAIN'			=> 'Afişează fişierele ataşate în funcţie de timp.',
	
	'EDIT_EXTENSION_GROUP'			=> 'Editează grupul de extensii',
	'EXCLUDE_ENTERED_IP'			=> 'Facilitează excluderea IP-urilor/numelor de host introduse.',
	'EXCLUDE_FROM_ALLOWED_IP'		=> 'Exclude IP din IP-urile/numele de host permise',
	'EXCLUDE_FROM_DISALLOWED_IP'	=> 'Exclude IP din IP-urile/numele de host nepermise',
	'EXTENSIONS_UPDATED'			=> 'Extensii actualizate cu succes',
	'EXTENSION_EXIST'				=> 'Extensia %s există deja',
	'EXTENSION_GROUP'				=> 'Grup de extensii',
	'EXTENSION_GROUPS'				=> 'Grupuri de extensii',
	'EXTENSION_GROUP_DELETED'		=> 'Grup de extensii şters cu succes.',
	'EXTENSION_GROUP_EXIST'			=> 'Grupul de extensii %s există deja',
	'EXT_GROUP_ARCHIVES'         => 'Arhive',
  'EXT_GROUP_DOCUMENTS'         => 'Documente',
  'EXT_GROUP_DOWNLOADABLE_FILES'   => 'Fişiere descărcabile',
  'EXT_GROUP_FLASH_FILES'         => 'Fişiere Flash',
  'EXT_GROUP_IMAGES'            => 'Imagini',
  'EXT_GROUP_PLAIN_TEXT'         => 'Plain Text',
  'EXT_GROUP_QUICKTIME_MEDIA'      => 'Quicktime Media',
  'EXT_GROUP_REAL_MEDIA'         => 'Real Media',
  'EXT_GROUP_WINDOWS_MEDIA'      => 'Windows Media',

	'GO_TO_EXTENSIONS'		=> 'Du-te la ecranul de administrare al extensiilor',
	'GROUP_NAME'			=> 'Numele grupului',

	'IMAGE_LINK_SIZE'			=> 'Dimensiunile link-ului către imagine',
	'IMAGE_LINK_SIZE_EXPLAIN'	=> 'Afişează imaginea ataşată ca link dacă imaginea este mai mare ca aceasta. Pentru a dezactiva acest mecanism, setaţi valori 0px cu 0px.',
	'IMAGICK_PATH'				=> 'Calea către Imagemagick',
	'IMAGICK_PATH_EXPLAIN'		=> 'Calea completă către convertorul imagemagick, de exemplu. <samp>/usr/bin/</samp>',

	'MAX_ATTACHMENTS'				=> 'Numărul maxim de fişiere ataşate pe mesaj',
	'MAX_ATTACHMENTS_PM'			=> 'Numărul maxim de fişiere ataşate pe mesaj privat',
	'MAX_EXTGROUP_FILESIZE'			=> 'Mărimea maximă a fişierelor',
	'MAX_IMAGE_SIZE'				=> 'Dimensiunile maxime ale imaginilor',
	'MAX_IMAGE_SIZE_EXPLAIN'		=> 'Dimensiunile maxime ale imaginilor. Pentru a dezactiva verificarea dimensiunii, setaţi ambele valori 0px cu 0px.',
	'MAX_THUMB_WIDTH'				=> 'Lăţimea maximă a imaginilor micşorate în pixeli',
	'MAX_THUMB_WIDTH_EXPLAIN'		=> 'Imaginile micşorate generate nu vor avea lăţimea mai mare decât valoarea setată aici.',
	'MIN_THUMB_FILESIZE'			=> 'Mărimea minimă a imaginilor micşorate',
	'MIN_THUMB_FILESIZE_EXPLAIN'	=> 'Nu generează o imagine micşorata dacă imaginea este mai mică decât valoarea setată aici.',
	'MODE_INLINE'					=> 'În linie',
	'MODE_PHYSICAL'					=> 'Fizic',

	'NOT_ALLOWED_IN_PM'			=> 'Doar permis în mesaje',
	'NOT_ALLOWED_IN_PM_POST'	=> 'Nepermis',
	'NOT_ASSIGNED'				=> 'Nealocat',
	'NO_EXT_GROUP'				=> 'Niciunul/una',
	'NO_EXT_GROUP_NAME'			=> 'Niciun nume de grup specificat',
	'NO_EXT_GROUP_SPECIFIED'	=> 'Niciun grup de extensii specificat.',
	'NO_FILE_CAT'				=> 'Niciunul/una',
	'NO_IMAGE'					=> 'Nicio imagine',
	'NO_THUMBNAIL_SUPPORT'		=> 'Suportul imaginilor micşorate a fost dezactivat. Pentru o funcţionare corespunzătoare o extensie GD trebuie să fie disponibilă sau Imagemagick trebuie să fie instalat. Niciuna nu a putut fi găsită.',
	'NO_UPLOAD_DIR'				=> 'Directorul pentru încărcări specificat nu există.',
	'NO_WRITE_UPLOAD'			=> 'Nu există permisiuni de scriere în directorul pentru încărcări specificat. Modificaţi permisiunile pentru a permite server-ului să scrie în acest director.',

	'ONLY_ALLOWED_IN_PM'	=> 'Permis doar în mesajele private',
	'ORDER_ALLOW_DENY'		=> 'Permite',
	'ORDER_DENY_ALLOW'		=> 'Nu permite',

	'REMOVE_ALLOWED_IPS'		=> 'Şterge sau exclude IP-uri/nume de host-uri <em>permise</em>',
	'REMOVE_DISALLOWED_IPS'		=> 'Şterge sau exclude IP-uri/nume de host-uri <em>nepermise</em>',

	'SEARCH_IMAGICK'				=> 'Caută Imagemagick',
	'SECURE_ALLOW_DENY'				=> 'Permite/Nu permite lista',
	'SECURE_ALLOW_DENY_EXPLAIN'		=> 'Schimbă mecanismul standard când descărcările securizate sunt activate în lista de Permise/interzise: <strong>whitelist</strong> (permise) sau <strong>blacklist</strong> (interzise).',
	'SECURE_DOWNLOADS'				=> 'Permite descărcarea securizată',
	'SECURE_DOWNLOADS_EXPLAIN'		=> 'Această opţiune limitează descărcările doar IP-urilor/numelor de host definite.',
	'SECURE_DOWNLOAD_NOTICE'		=> 'Descărcările securizate sunt dezactivate. Setarea de mai jos va fi aplicată după activarea descărcărilor securizate.',
	'SECURE_DOWNLOAD_UPDATE_SUCCESS'=> 'Lista de IP-uri a fost actualizată cu succes.',
	'SECURE_EMPTY_REFERRER'			=> 'Permite vizite fără referinţă',
	'SECURE_EMPTY_REFERRER_EXPLAIN'	=> 'Descărcările securizate se bazează pe referinţe. Vreţi să permiteţi descărcările în cazul în care referinţa este omisă?',
	'SETTINGS_CAT_IMAGES'			=> 'Setările categoriei cu imagini',
	'SPECIAL_CATEGORY'				=> 'Categorie specială',
	'SPECIAL_CATEGORY_EXPLAIN'		=> 'La categoriile speciale diferă modul in care sunt prezentate în interiorul mesajelor.',
	'SUCCESSFULLY_UPLOADED'			=> 'Încărcarea a fost terminată cu succes',
	'SUCCESS_EXTENSION_GROUP_ADD'	=> 'Grup de extensii adăugat cu success',
	'SUCCESS_EXTENSION_GROUP_EDIT'	=> 'Grup de extensii actualizat cu succes',

	'UPLOADING_FILES'				=> 'Fişiere in curs de încărcare',
	'UPLOADING_FILE_TO'				=> 'Fişierul „%1$s” este încărcat la mesajul cu numărul %2$d…',
	'UPLOAD_DENIED_FORUM'			=> 'Nu vă este permis să încărcaţi fişiere în forumul „%s”',
	'UPLOAD_DIR'					=> 'Director pentru încărcări',
	'UPLOAD_DIR_EXPLAIN'			=> 'Calea de stocare pentru fişiere ataşate. Reţineţi că dacă schimbaţi directorul în timp ce deja aveţi fişiere ataşate încărcate, va trebui să copiaţi manual aceste fişiere în noua lor locaţie.',
	'UPLOAD_ICON'					=> 'Iconiţă pentru încărcare',
	'UPLOAD_NOT_DIR'				=> 'Locaţia specificată pentru încărcare nu este un director.',
));

?>