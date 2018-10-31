<?php
/**
*
* acp_common [Română]
*
* @package language
* @version $Id: common.php 9464 2009-04-17 15:52:40Z acydburn $
* @translate $Id: common.php 9464 yyyy-mm-dd hh:mm:ss www.phpbb.ro (shara21jonny) Exp $
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

// Common
$lang = array_merge($lang, array(
	'ACP_ADMINISTRATORS'		=> 'Administratori',
	'ACP_ADMIN_LOGS'			=> 'Jurnal administrator',
	'ACP_ADMIN_ROLES'			=> 'Roluri administrator',
	'ACP_ATTACHMENTS'			=> 'Fişiere ataşate',
	'ACP_ATTACHMENT_SETTINGS'	=> 'Configurări fişiere ataşate',
	'ACP_AUTH_SETTINGS'			=> 'Autentificare',
	'ACP_AUTOMATION'			=> 'Automatizare',
	'ACP_AVATAR_SETTINGS'		=> 'Configurări imagini asociate',

	'ACP_BACKUP'				=> 'Copie de rezervă',
	'ACP_BAN'					=> 'Interzicere',
	'ACP_BAN_EMAILS'			=> 'Interzicere conturi e-mail',
	'ACP_BAN_IPS'				=> 'Interzicere IP-uri',
	'ACP_BAN_USERNAMES'			=> 'Interzicere utilizatori',
	'ACP_BBCODES'				=> 'CoduriBB',
	'ACP_BOARD_CONFIGURATION'	=> 'Configurare forum',
	'ACP_BOARD_FEATURES'		=> 'Caracteristici forum',
	'ACP_BOARD_MANAGEMENT'		=> 'Administrare forum',
	'ACP_BOARD_SETTINGS'		=> 'Setări forum',
	'ACP_BOTS'					=> 'Păienjeni/Roboţi',
	
	'ACP_CAPTCHA'				=> 'CAPTCHA',

	'ACP_CAT_DATABASE'			=> 'Baza de date',
	'ACP_CAT_DOT_MODS'			=> 'MODificări',
	'ACP_CAT_FORUMS'			=> 'Forumuri',
	'ACP_CAT_GENERAL'			=> 'General',
	'ACP_CAT_MAINTENANCE'		=> 'Întreţinere',
	'ACP_CAT_PERMISSIONS'		=> 'Permisiuni',
	'ACP_CAT_POSTING'			=> 'Scriere',
	'ACP_CAT_STYLES'			=> 'Stiluri',
	'ACP_CAT_SYSTEM'			=> 'Sistem',
	'ACP_CAT_USERGROUP'			=> 'Utilizatori şi grupuri',
	'ACP_CAT_USERS'				=> 'Utilizatori',
	'ACP_CLIENT_COMMUNICATION'	=> 'Comunicaţii client',
	'ACP_COOKIE_SETTINGS'		=> 'Configurări cookie',
	'ACP_CRITICAL_LOGS'			=> 'Jurnal erori',
	'ACP_CUSTOM_PROFILE_FIELDS'	=> 'Câmpuri profil personalizabil',
	
	'ACP_DATABASE'				=> 'Administrare bază de date',
	'ACP_DISALLOW'				=> 'Dezactivări',
	'ACP_DISALLOW_USERNAMES'	=> 'Nume utilizatori dezactivate',
	
	'ACP_EMAIL_SETTINGS'		=> 'Configurări e-mail',
	'ACP_EXTENSION_GROUPS'		=> 'Administrare extensii grupuri',
	
	'ACP_FORUM_BASED_PERMISSIONS'	=> 'Permisiuni bazate pe forum',
	'ACP_FORUM_LOGS'				=> 'Jurnale forum',
	'ACP_FORUM_MANAGEMENT'			=> 'Administrare forum',
	'ACP_FORUM_MODERATORS'			=> 'Moderatori forum',
	'ACP_FORUM_PERMISSIONS'			=> 'Permisiuni forum',
  'ACP_FORUM_PERMISSIONS_COPY'	=> 'Copiere permisiuni forum',
	'ACP_FORUM_ROLES'				=> 'Roluri forum',

	'ACP_GENERAL_CONFIGURATION'		=> 'Configurare generală',
	'ACP_GENERAL_TASKS'				=> 'Activităţi generale',
	'ACP_GLOBAL_MODERATORS'			=> 'Moderatori globali',
	'ACP_GLOBAL_PERMISSIONS'		=> 'Permisiuni globale',
	'ACP_GROUPS'					=> 'Grupuri',
	'ACP_GROUPS_FORUM_PERMISSIONS'	=> 'Permisiuni forum pentru grupuri',
	'ACP_GROUPS_MANAGE'				=> 'Administrare grupuri',
	'ACP_GROUPS_MANAGEMENT'			=> 'Administrare grup',
	'ACP_GROUPS_PERMISSIONS'		=> 'Permisiuni grupuri',
	
	'ACP_ICONS'					=> 'Iconiţe subiect',
	'ACP_ICONS_SMILIES'			=> 'Iconiţe/zâmbete subiect',
	'ACP_IMAGESETS'				=> 'Seturi imagine',
	'ACP_INACTIVE_USERS'		=> 'Utilizatori inactivi',
	'ACP_INDEX'					=> 'Index Panoul administratorului',
	
	'ACP_JABBER_SETTINGS'		=> 'Setări Jabber',
	
	'ACP_LANGUAGE'				=> 'Administrare limbă',
	'ACP_LANGUAGE_PACKS'		=> 'Pachete limbă',
	'ACP_LOAD_SETTINGS'			=> 'Configurări încărcare',
	'ACP_LOGGING'				=> 'Autentificare',
	
	'ACP_MAIN'					=> 'Panoul administratorului',
	'ACP_MANAGE_EXTENSIONS'		=> 'Administrare extensii',
	'ACP_MANAGE_FORUMS'			=> 'Administrare forumuri',
	'ACP_MANAGE_RANKS'			=> 'Administrare ranguri',
	'ACP_MANAGE_REASONS'		=> 'Administrare motive raportări/contestări',
	'ACP_MANAGE_USERS'			=> 'Administrare utilizatori',
	'ACP_MASS_EMAIL'			=> 'E-mail în masă',
	'ACP_MESSAGES'				=> 'Mesaje',
	'ACP_MESSAGE_SETTINGS'		=> 'Configurări mesaje private',
	'ACP_MODULE_MANAGEMENT'		=> 'Modul administrare',
	'ACP_MOD_LOGS'				=> 'Jurnal moderator',
	'ACP_MOD_ROLES'				=> 'Roluri moderator',
	
	'ACP_NO_ITEMS'				=> 'Nu există încă un element.',
	
	'ACP_ORPHAN_ATTACHMENTS'	=> 'Fişiere ataşate orfane',
	
	'ACP_PERMISSIONS'			=> 'Permisiuni',
	'ACP_PERMISSION_MASKS'		=> 'Permisiuni măşti',
	'ACP_PERMISSION_ROLES'		=> 'Permisiuni roluri',
	'ACP_PERMISSION_TRACE'		=> 'Cale permisiuni',
	'ACP_PHP_INFO'				=> 'Informaţii PHP',
	'ACP_POST_SETTINGS'			=> 'Configurări mesaj',
	'ACP_PRUNE_FORUMS'			=> 'Curăţare forumuri',
	'ACP_PRUNE_USERS'			=> 'Curăţare utilizatori',
	'ACP_PRUNING'				=> 'Curăţare',
	
	'ACP_QUICK_ACCESS'			=> 'Acces rapid',
	
	'ACP_RANKS'					=> 'Ranguri',
	'ACP_REASONS'				=> 'Motive raportări/contestări',
	'ACP_REGISTER_SETTINGS'		=> 'Configurări înregistrare utilizator',

	'ACP_RESTORE'				=> 'Restaurare',
	'ACP_FEED'					=> 'Administrare flux',
	'ACP_FEED_SETTINGS'			=> 'Setări flux',

	'ACP_SEARCH'				=> 'Configurare căutare',
	'ACP_SEARCH_INDEX'			=> 'Index căutare',
	'ACP_SEARCH_SETTINGS'		=> 'Setări căutare',

	'ACP_SECURITY_SETTINGS'		=> 'Setări securitate',
	'ACP_SEND_STATISTICS'		=> 'Trimite informaţii statistice',
	'ACP_SERVER_CONFIGURATION'	=> 'Configurare server',
	'ACP_SERVER_SETTINGS'		=> 'Setări server',
	'ACP_SIGNATURE_SETTINGS'	=> 'Setări semnătură',
	'ACP_SMILIES'				=> 'Zâmbete',
	'ACP_STYLE_COMPONENTS'		=> 'Componente stil',
	'ACP_STYLE_MANAGEMENT'		=> 'Administrare stil',
	'ACP_STYLES'				=> 'Stiluri',
	'ACP_SUBMIT_CHANGES'		=> 'Trimite modificări',
	
	'ACP_TEMPLATES'				=> 'Şabloane',
	'ACP_THEMES'				=> 'Teme',
	
	'ACP_UPDATE'					=> 'Actualizare',
	'ACP_USERS_FORUM_PERMISSIONS'	=> 'Permisiuni forum pentru utilizatori',
	'ACP_USERS_LOGS'				=> 'Jurnal utilizatori',
	'ACP_USERS_PERMISSIONS'			=> 'Permisiuni utilizatori',
	'ACP_USER_ATTACH'				=> 'Fişiere ataşate',
	'ACP_USER_AVATAR'				=> 'Imagine asociată (avatar)',
	'ACP_USER_FEEDBACK'				=> 'Părere',
	'ACP_USER_GROUPS'				=> 'Grupuri',
	'ACP_USER_MANAGEMENT'			=> 'Administrare utilizator',
	'ACP_USER_OVERVIEW'				=> 'Privire generală',
	'ACP_USER_PERM'					=> 'Permisiuni',
	'ACP_USER_PREFS'				=> 'Preferinţe',
	'ACP_USER_PROFILE'				=> 'Profil',
	'ACP_USER_RANK'					=> 'Rang',
	'ACP_USER_ROLES'				=> 'Roluri utilizator',
	'ACP_USER_SECURITY'				=> 'Securitate utilizator',
	'ACP_USER_SIG'					=> 'Semnătură',

  'ACP_USER_WARNINGS'				=> 'Avertismente',

	'ACP_VC_SETTINGS'					=> 'Setări împotriva boţilor de spam',
	'ACP_VC_CAPTCHA_DISPLAY'			=> 'Previzualizare imagine CAPTCHA',
	'ACP_VERSION_CHECK'					=> 'Verifică actualizări',
	'ACP_VIEW_ADMIN_PERMISSIONS'		=> 'Vezi permisiuni administrative',
	'ACP_VIEW_FORUM_MOD_PERMISSIONS'	=> 'Vezi permisiuni de moderare pe forum',
	'ACP_VIEW_FORUM_PERMISSIONS'		=> 'Vezi permisiuni forum',
	'ACP_VIEW_GLOBAL_MOD_PERMISSIONS'	=> 'Vezi permisiuni globale de moderare',
	'ACP_VIEW_USER_PERMISSIONS'			=> 'Vezi permisiuni utilizatori',
	
	'ACP_WORDS'					=> 'Cenzură cuvinte',

	'ACTION'				=> 'Acţiune',
	'ACTIONS'				=> 'Acţiuni',
	'ACTIVATE'				=> 'Activează',
	'ADD'					=> 'Adaugă',
	'ADMIN'					=> 'Administrare',
	'ADMIN_INDEX'			=> 'Index admin',
	'ADMIN_PANEL'			=> 'Panoul administratorului',
	'ADM_LOGOUT'         => 'Ieşire&nbsp;din&nbsp;PA',
  'ADM_LOGGED_OUT'      => 'Ieşire reuşită din Panoul de control al administratorului',

	'BACK'					=> 'Înapoi',

	'COLOUR_SWATCH'			=> 'Mostră culoare web-safe',
	'CONFIG_UPDATED'		=> 'Configurarea a fost actualizată cu succes.',

	'DEACTIVATE'				=> 'Dezactivează',
	'DIRECTORY_DOES_NOT_EXIST'	=> 'Calea introdusă „%s” nu există.',
	'DIRECTORY_NOT_DIR'			=> 'Calea introdusă „%s” nu este un director.',
	'DIRECTORY_NOT_WRITABLE'	=> 'Calea introdusă „%s” nu se poate scrie.',
	'DISABLE'					=> 'Dezactivează',
	'DOWNLOAD'					=> 'Descarcă',
	'DOWNLOAD_AS'				=> 'Descarcă ca',
	'DOWNLOAD_STORE'			=> 'Descarcă sau stochează fişier',
	'DOWNLOAD_STORE_EXPLAIN'	=> 'Puteţi descărca direct fişierul sau să-l salvaţi într-un director <samp>de stocat/</samp>.',

	'EDIT'					=> 'Modifică',
	'ENABLE'				=> 'Activează',
	'EXPORT_DOWNLOAD'		=> 'Descarcă',
	'EXPORT_STORE'			=> 'Stochează',

	'GENERAL_OPTIONS'		=> 'Opţiuni generale',
	'GENERAL_SETTINGS'		=> 'Setări generale',
	'GLOBAL_MASK'			=> 'Mască permisiuni globale',

	'INSTALL'				=> 'Instalează',
	'IP'					=> 'IP utilizator',
	'IP_HOSTNAME'			=> 'Adrese IP sau nume host',

	'LOGGED_IN_AS'			=> 'Sunteţi autentificat ca:',
	'LOGIN_ADMIN'			=> 'Pentru a administra forumul, trebuie să fiţi un utilizator autentificat.',
	'LOGIN_ADMIN_CONFIRM'	=> 'Pentru a administra forumul, trebuie să vă reautentificaţi.',
	'LOGIN_ADMIN_SUCCESS'	=> 'Aţi fost autentificat cu succes şi veţi fi redirecţionat către Panoul administratorului.',
	'LOOK_UP_FORUM'			=> 'Selectează un forum',
	'LOOK_UP_FORUMS_EXPLAIN'=> 'Puteţi selecta mai mult decât un forum.',

	'MANAGE'				=> 'Administrază',
	'MENU_TOGGLE'			=> 'Ascunde sau afişează meniul lateral',
	'MORE'					=> 'Mai mult',			// Not used at the moment
	'MORE_INFORMATION'		=> 'Mai multe informaţii »',
	'MOVE_DOWN'				=> 'Mută mai jos',
	'MOVE_UP'				=> 'Mută mai sus',

	'NOTIFY'				=> 'Notificare',
	'NO_ADMIN'				=> 'Nu sunteţi autorizat să administraţi acest forum.',
	'NO_EMAILS_DEFINED'		=> 'Nu a fost găsită o adresă de e-mail validă.',
	'NO_PASSWORD_SUPPLIED'	=> 'Trebuie să introduceţi parola pentru a acesa Panoul administratorului.',	

	'OFF'					=> 'Dezactivat',
	'ON'					=> 'Activat',

	'PARSE_BBCODE'						=> 'Analizează Codul BB',
	'PARSE_SMILIES'						=> 'Analizează zâmbetele',
	'PARSE_URLS'						=> 'Analizează link-urile',
	'PERMISSIONS_TRANSFERRED'			=> 'Perimisiuni transferate',
	'PERMISSIONS_TRANSFERRED_EXPLAIN'	=> 'Acum aveţi permisiunile de la %1$s. Puteţi să accesaţi forumul cu permisiunile utilizatorului, dar nu puteţi să accesaţi Panoul administratorului, pentru că permisiunile administrative nu au fost transferate. Puteţi să <a href="%2$s"><strong>reveniţi la setul de permisiuni ale dumneavoastră</strong></a> oricând.',
	
	'PROCEED_TO_ACP'					=> '%sContinuă cu Panoul administratorului%s',

	'REMIND'							=> 'Reaminteşte',
	'RESYNC'							=> 'Resincronizează',
	'RETURN_TO'							=> 'Intoarce la…',

	'SELECT_ANONYMOUS'		=> 'Selectează utilizatorul anonim',
	'SELECT_OPTION'			=> 'Selectează opţiune',
	
	'SETTING_TOO_LOW'		=> 'Valoarea specificată pentru variabila „%1$s” este prea mică. Valoarea minimă admisă este %2$d.',
	'SETTING_TOO_BIG'		=> 'Valoarea specificată pentru variabila „%1$s” este prea mare. Valoarea maximă admisă este %2$d.',	
	'SETTING_TOO_LONG'		=> 'Valoarea specificată pentru variabila „%1$s” este prea lungă. Lungimea maximă admisă este %2$d.',
	'SETTING_TOO_SHORT'		=> 'Valoarea specificată pentru variabila „%1$s” prea scurtă. Lungimea minimă admisă este %2$d.',
	'SHOW_ALL_OPERATIONS'	=> 'Arată toate operaţiile',

	'UCP'					=> 'Panoul utilizatorului',
	'USERNAMES_EXPLAIN'		=> 'Introduceţi fiecare nume de utilizator pe linii separate.',
	'USER_CONTROL_PANEL'	=> 'Panoul utilizatorului',

	'WARNING'				=> 'Avertisment',
));

// PHP info
$lang = array_merge($lang, array(
	'ACP_PHP_INFO_EXPLAIN'	=> 'Această pagină listează informaţiile versiunii de PHP instalate pe acest server. Include detaliile modulelor încărcate, variabilelor disponibile şi setările standard. Această informaţie poate fi de ajutor pentru diagnosticarea problemelor. Vă rog, fiţi conştient că unele companii de hosting vor limita ce informaţii să afişeze din motive de securitate. Sunteţi avizat să nu daţi mai departe detaliile acestei pagini, exceptând când sunteţi întrebat de un <a href="https://www.phpbb.com/about/team/">membru oficial al echipei de suport</a>.',

	'NO_PHPINFO_AVAILABLE'	=> 'Informaţiile despre configuraţia PHP nu pot fi determinate. Phpinfo() a fost dezactivat din motive de securitate.',
));

// Logs
$lang = array_merge($lang, array(
	'ACP_ADMIN_LOGS_EXPLAIN'	=> 'Aici vedeţi listate toate acţiunile efectuate de administratorii forumului. Le puteţi sorta după nume de utilizator, după dată, IP sau acţiune. Dacă aveţi permisiunile necesare, puteţi să ştergeţi operaţiunile individuale sau tot jurnalul.',
	'ACP_CRITICAL_LOGS_EXPLAIN'	=> 'Aici vedeţi toate acţiunile efectuate de către forum. Acest jurnal vă înştiinţează cu informaţii pe care le puteţi folosi la rezolvarea problemelor specifice, de exemplu netrimiterea e-mail-urilor. Le puteţi sorta după nume utilizator, dată, IP sau acţiune. Dacă aveţi permisiunile necesare, puteţi să ştergeţi operaţiunile individuale sau tot jurnalul.',
	'ACP_MOD_LOGS_EXPLAIN'		=> 'Aici vedeţi toate acţiunile efectuate de către moderatori în forumuri, subiecte şi mesaje, de asemenea acţiunile efectuate asupra utilizatorilor, inclusiv interzicerea. Le puteți sorta după nume utilizator, dată, IP sau acţiune. Dacă aveţi permisiunile necesare, puteţi să ştergeţi operaţiunile individuale sau tot jurnalul.',
	'ACP_USERS_LOGS_EXPLAIN'	=> 'Aici vedeţi toate acţiunile realizate de către utilizatori sau asupra utilizatorilor (raportări, avertismente şi note utilizator).',
	'ALL_ENTRIES'				=> 'Toate intrările',

	'DISPLAY_LOG'	=> 'Afişează intrările de la anteriorul',

	'NO_ENTRIES'	=> 'Nicio informaţie în jurnal pentru această perioadă.',

	'SORT_IP'		=> 'Adresă IP',
	'SORT_DATE'		=> 'Dată',
	'SORT_ACTION'	=> 'Acţiune jurnal',
));

// Index page
$lang = array_merge($lang, array(
	'ADMIN_INTRO'				=> 'Mulţumim că aţi ales phpBB ca soluţie pentru forumul dumneavoastră. Această pagină vă va da o scurtă privire de ansamblu a tuturor statisticilor forumului dumneavoastră. Legăturile de pe partea stângă a paginii vă permit să controlaţi orice aspect a experienţei dumneavoastră pe forum. Fiecare pagină va avea instrucţiuni despre modul de folosire a opţiunilor.',
	'ADMIN_LOG'					=> 'Jurnal cu acţiunile administratorilor',
	'ADMIN_LOG_INDEX_EXPLAIN'	=> 'Aici vedeţi ultimele cinci acţiuni realizate de către administratori. Pentru a vedea toate acţiunile realizate de către aceştia, accesaţi articolul adecvat din meniu sau urmaţi legătura de mai jos.',
	'AVATAR_DIR_SIZE'			=> 'Dimensiunile directorului de imagini asociate (avatare)',

	'BOARD_STARTED'		=> 'Forum din data de',
	'BOARD_VERSION'		=> 'Versiune forum',

	'DATABASE_SERVER_INFO'	=> 'Server bază de date',
	'DATABASE_SIZE'			=> 'Dimensiunile bazei de date',

	// Enviroment configuration checks, mbstring related
	'ERROR_MBSTRING_FUNC_OVERLOAD'					=> 'Funcția overloading este configurată necorespunzător',
	'ERROR_MBSTRING_FUNC_OVERLOAD_EXPLAIN'			=> '<var>mbstring.func_overload</var> trebuie să fie setată fie 0 sau 4. Puteți verifica valoarea curentă în secțiunea <samp>PHP information</samp>.',
	'ERROR_MBSTRING_ENCODING_TRANSLATION'			=> 'Caracterul de codare transparentă este configurat necorespunzător',
	'ERROR_MBSTRING_ENCODING_TRANSLATION_EXPLAIN'	=> '<var>mbstring.encoding_translation</var> trebuie să fie setată 0. Puteți verifica valoarea curentă în secțiunea <samp>PHP information</samp>.',
	'ERROR_MBSTRING_HTTP_INPUT'						=> 'Caracterul de conversie intrare HTTP este configurat necorespunzător',
	'ERROR_MBSTRING_HTTP_INPUT_EXPLAIN'				=> '<var>mbstring.http_input</var> trebuie să fie setată ca <samp>pass</samp>. Puteți verifica valoarea curentă în secțiunea <samp>PHP information</samp>.',
	'ERROR_MBSTRING_HTTP_OUTPUT'					=> 'Caracterul de conversie ieșire HTTP este configurat necorespunzător',
	'ERROR_MBSTRING_HTTP_OUTPUT_EXPLAIN'			=> '<var>mbstring.http_output</var> trebuie să fie setată ca <samp>pass</samp>. Puteți verifica valoarea curentă în secțiunea <samp>PHP information</samp>.',

	'FILES_PER_DAY'		=> 'Fişiere ataşate pe zi',
	'FORUM_STATS'		=> 'Statistici forum',

	'GZIP_COMPRESSION'	=> 'Compresii GZip',

	'NOT_AVAILABLE'		=> 'Nu este disponibil',
	'NUMBER_FILES'		=> 'Număr de fişiere ataşate',
	'NUMBER_POSTS'		=> 'Număr de mesaje',
	'NUMBER_TOPICS'		=> 'Număr de subiecte',
	'NUMBER_USERS'		=> 'Număr de utilizatori',
	'NUMBER_ORPHAN'		=> 'Fişiere ataşate fără legături',
	'PHP_VERSION_OLD'	=> 'Versiunea PHP aflată pe acest server nu va fi suportată de viitoarele versiuni ale phpBB. %sDetalii%s',

	'POSTS_PER_DAY'		=> 'Mesaje pe zi',
	
	'PURGE_CACHE'			=> 'Şterge cache',
	'PURGE_CACHE_CONFIRM'	=> 'Sunteţi sigur că doriţi să ştergeţi cache-ul?',
	'PURGE_CACHE_EXPLAIN'	=> 'Şterge toate articolele legate de cache, aceasta include orice cache al fişierelor de șabloane sau interogările.',
	'PURGE_SESSIONS'			=> 'Şterge toate sesiunile',
	'PURGE_SESSIONS_CONFIRM'	=> 'Sunteţi sigur că vreţi să şergeţi toate sesiunile? Această operaţiune va scoate afară toţi utilizatorii.',
	'PURGE_SESSIONS_EXPLAIN'	=> 'Şterge toate sesiunile. Această operaţiune va scoate afară toţi utilizatorii prin iniţializarea tabelei pentru sesiuni.',
	
	'RESET_DATE'		          	=> 'Resetare dată de start a forumului',
	'RESET_DATE_CONFIRM'        	=> 'Sunteţi sigur că vreţi să resetaţi data de start a forumului?',
	'RESET_ONLINE'			        => 'Resetare contor care indică cei mai multi utilizatori online',
	'RESET_ONLINE_CONFIRM'			=> 'Sunteţi sigur că vreţi să resetaţi contorul care indică cei mai mulţi utilizatori online?',
	'RESYNC_POSTCOUNTS'				=> 'Resincronizare contor mesaje',
	'RESYNC_POSTCOUNTS_EXPLAIN'		=> 'Numai mesajele existente vor fi luate în considerare. Mesajele şterse nu se vor număra.',
	'RESYNC_POSTCOUNTS_CONFIRM'		=> 'Sunteţi sigur că vreţi să resincronizaţi contorul mesajelor?',
	'RESYNC_POST_MARKING'			=> 'Resincronizare subiecte punctate',
	'RESYNC_POST_MARKING_CONFIRM'	=> 'Sunteţi sigur că doriţi să resincronizaţi subiectele punctate?',
	'RESYNC_POST_MARKING_EXPLAIN'	=> 'Mai întâi deselectaţi toate subiectele şi apoi selectaţi corect subiectele care au avut activitate în ultimele şase luni.',
	'RESYNC_STATS'					=> 'Resincronizare statistici',
	'RESYNC_STATS_CONFIRM'			=> 'Sunteţi sigur că vreţi să resincronizaţi statisticile?',
	'RESYNC_STATS_EXPLAIN'			=> 'Recalculare număr total de mesaje, subiecte, utilizatori şi fişiere.',
	'RUN'							=> 'Porneşte acum',

	'STATISTIC'		         	=> 'Statistică',
	'STATISTIC_RESYNC_OPTIONS'	=> 'Resincronizează sau resetează statisticile',

	'TOPICS_PER_DAY'	=> 'Subiecte pe zi',

	'UPLOAD_DIR_SIZE'	=> 'Dimensiunea fişierelor ataşate trimise',
	'USERS_PER_DAY'		=> 'Utilizatori pe zi',

	'VALUE'					=> 'Valoare',
	'VERSIONCHECK_FAIL'			=> 'Eşuare la încercarea de a obţiune informaţii despre ultima versiune.',
	'VERSIONCHECK_FORCE_UPDATE'	=> 'Reverificare versiune',
	'VIEW_ADMIN_LOG'		=> 'Vezi log-ul administratorului',
	'VIEW_INACTIVE_USERS'	=> 'Vezi utilizatori inactivi',

	'WELCOME_PHPBB'			=> 'Bine aţi venit la phpBB',
	'WRITABLE_CONFIG'      => 'Fişierul de configurare (config.php) permite modificarea lui de către oricine. Vă recomandăm să schimbaţi permisiunile la 640 sau cel puţin 644 (de exemplu: <a href="http://en.wikipedia.org/wiki/Chmod" rel="external">chmod</a> 640 config.php).',
));

// Inactive Users
$lang = array_merge($lang, array(
	'INACTIVE_DATE'					=> 'Dată inactivă',
	'INACTIVE_REASON'				=> 'Motiv',
	'INACTIVE_REASON_MANUAL'		=> 'Cont dezactivat de către administrator',
	'INACTIVE_REASON_PROFILE'		=> 'Detaliile profilului au fost schimbate',
	'INACTIVE_REASON_REGISTER'		=> 'Cel mai recent cont înregistrat',
	'INACTIVE_REASON_REMIND'		=> 'Forţarea reactivării contului utilizatorului',
	'INACTIVE_REASON_UNKNOWN'		=> 'Necunoscut',
	'INACTIVE_USERS'				=> 'Utilizatori inactivi',
	'INACTIVE_USERS_EXPLAIN'		=> 'Aceasta este o listă cu utiliazatorii înregistrati, dar care au conturile inactive. Puteţi activa, şterge ori reaminti (trimiţând un e-mail) acestor utilizatori dacă doriţi.',
	'INACTIVE_USERS_EXPLAIN_INDEX'	=> 'Aceasta este o listă cu ultimii 10 utilizatori înregistraţi ce au conturile inactive. Conturile sunt inactive fie pentru că activarea contului a fost specificată în setările de înregistrare a utilizatorului și aceste conturi ale utilizatorilor nu au fost activate, fie că aceste conturi au fost dezactivate. Lista întreagă poate fi accesată folosind legătura de mai jos de unde puteţi activa, şterge ori aduce aminte (trimiţând un e-mail) acestor utilizatori dacă doriţi.',

	'NO_INACTIVE_USERS'	=> 'Niciun utilizator inactiv',

	'SORT_INACTIVE'		=> 'Data de inactivitate',
	'SORT_LAST_VISIT'	=> 'Ultima vizită',
	'SORT_REASON'		=> 'Motiv',
	'SORT_REG_DATE'		=> 'Data înregistrării',
	'SORT_LAST_REMINDER'=> 'Ultima înştiinţare',
	'SORT_REMINDER'		=> 'Înştiinţare trimisă',

	'USER_IS_INACTIVE'		=> 'Utilizatorul este inactiv',
));

// Send statistics page
$lang = array_merge($lang, array(
	'EXPLAIN_SEND_STATISTICS'	=> 'Vă rugăm să trimiteţi informaţii despre serverul propriu şi configuraţiile forumului la phpBB pentru analize statistice. Toate informaţiile care vă identifică sau conţin date referitoare la site-ul propriu au fost eliminate - datele sunt complet <strong>anonime</strong>. Ne vom baza deciziile referitoare la viitoarele versiuni phpBB pe aceste informaţii. Statisticile sunt disponibile publicului larg. De asemenea, aceste date sunt furnizate şi proiectului PHP, limbajului de programare ce a fost folosit pentru dezvoltarea forumului phpBB.',
	'EXPLAIN_SHOW_STATISTICS'	=> 'Folosind butonul de mai jos puteţi previzualiza toate variabilele care vor fi transmise.',
	'DONT_SEND_STATISTICS'		=> 'Reveniţi în Panoul administratorului dacă nu doriţi să trimiteţi informaţii statistice la phpBB.',
	'GO_ACP_MAIN'				=> 'Revenire la pagina de start a Panoului administratorului',
	'HIDE_STATISTICS'			=> 'Ascunde detaliile',
	'SEND_STATISTICS'			=> 'Trimite informaţii statistice',
	'SHOW_STATISTICS'			=> 'Arată detaliile',
	'THANKS_SEND_STATISTICS'	=> 'Vă mulţumim că aţi trimis informaţiile proprii.',
));


// Log Entries
$lang = array_merge($lang, array(
	'LOG_ACL_ADD_USER_GLOBAL_U_'		=> '<strong>Utilizatorii cu permisiunile utilizatorului adăugate sau modificate</strong><br />» %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_U_'		=> '<strong>Grupurile cu permisiunile utilizatorului adăugate sau modificate</strong><br />» %s',
	'LOG_ACL_ADD_USER_GLOBAL_M_'		=> '<strong>Utilizatorii cu permisiunile de moderator global adăugate sau modificate</strong><br />» %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_M_'		=> '<strong>Grupurile cu permisiunile de moderator global adăugate sau modificate</strong><br />» %s',
	'LOG_ACL_ADD_USER_GLOBAL_A_'		=> '<strong>Utilizatorii cu permisiunile de administrator adăugate sau modificate</strong><br />» %s',
	'LOG_ACL_ADD_GROUP_GLOBAL_A_'		=> '<strong>Grupurile cu permisiunile de administrator adăugate sau modificate</strong><br />» %s',

	'LOG_ACL_ADD_ADMIN_GLOBAL_A_'		=> '<strong>Administratorii adăugaţi sau modificaţi</strong><br />» %s',
	'LOG_ACL_ADD_MOD_GLOBAL_M_'			=> '<strong>Moderatorii globali adăugaţi sau modificaţi</strong><br />» %s',

	'LOG_ACL_ADD_USER_LOCAL_F_'			=> '<strong>Utilizatorii cu accesele de forum adăugate sau modificate</strong> de la %1$s<br />» %2$s',
	'LOG_ACL_ADD_USER_LOCAL_M_'			=> '<strong>Utilizatorii cu accesele de moderator de forum adăugate sau modificate</strong> de la %1$s<br />» %2$s',
	'LOG_ACL_ADD_GROUP_LOCAL_F_'		=> '<strong>Grupurile cu accesele de forum adăugate sau modificate</strong> de la %1$s<br />» %2$s',
	'LOG_ACL_ADD_GROUP_LOCAL_M_'		=> '<strong>Grupurile cu accesele de moderator de forum adăugate sau modificate</strong> de la %1$s<br />» %2$s',

	'LOG_ACL_ADD_MOD_LOCAL_M_'			=> '<strong>Moderatori adăugaţi sau modificaţi</strong> de la %1$s<br />» %2$s',
	'LOG_ACL_ADD_FORUM_LOCAL_F_'		=> '<strong>Permisiunile forumului adăugate sau modificate</strong> de la %1$s<br />» %2$s',

	'LOG_ACL_DEL_ADMIN_GLOBAL_A_'		=> '<strong>Administratori eliminaţi</strong><br />» %s',
	'LOG_ACL_DEL_MOD_GLOBAL_M_'			=> '<strong>Moderatori globali eliminaţi</strong><br />» %s',
	'LOG_ACL_DEL_MOD_LOCAL_M_'			=> '<strong>Moderatorii eliminaţi</strong> de la %1$s<br />» %2$s',
	'LOG_ACL_DEL_FORUM_LOCAL_F_'		=> '<strong>Permisiunile forumului pentru utilizatori/grupuri eliminate</strong> de la %1$s<br />» %2$s',

	'LOG_ACL_TRANSFER_PERMISSIONS'		=> '<strong>Permisiuni transferate de la</strong><br />» %s',
	'LOG_ACL_RESTORE_PERMISSIONS'		=> '<strong>Propriile permisiuni restaurate după ce s-au folosit permisiunile de la</strong><br />» %s',
	
	'LOG_ADMIN_AUTH_FAIL'		=> '<strong>Încercare de autentificare administrativă eşuată</strong>',
	'LOG_ADMIN_AUTH_SUCCESS'	=> '<strong>Autentificare administrativă cu succes</strong>',
	
	'LOG_ATTACHMENTS_DELETED'	=> '<strong>Fişiere ataşate ale utilizatorului eliminate</strong><br />» %s',

	'LOG_ATTACH_EXT_ADD'		=> '<strong>Extensiile fişierelor ataşate adăugate sau modificate</strong><br />» %s',
	'LOG_ATTACH_EXT_DEL'		=> '<strong>Extensiile fişierelor ataşate eliminate</strong><br />» %s',
	'LOG_ATTACH_EXT_UPDATE'		=> '<strong>Extensiile fişierelor ataşate actualizate</strong><br />» %s',
	'LOG_ATTACH_EXTGROUP_ADD'	=> '<strong>Extensiile grupului adăugate</strong><br />» %s',
	'LOG_ATTACH_EXTGROUP_EDIT'	=> '<strong>Extensiile grupului modificate</strong><br />» %s',
	'LOG_ATTACH_EXTGROUP_DEL'	=> '<strong>Extensiile grupului eliminate</strong><br />» %s',
	'LOG_ATTACH_FILEUPLOAD'		=> '<strong>Fişier fără legătură încărcat la mesaj</strong><br />» ID %1$d - %2$s',
	'LOG_ATTACH_ORPHAN_DEL'		=> '<strong>Fişiere fără legătură şterse</strong><br />» %s',

	'LOG_BAN_EXCLUDE_USER'	=> '<strong>Utilizator exclus de la interziceri</strong> pentru motiv „<em>%1$s</em>”<br />» %2$s ',
	'LOG_BAN_EXCLUDE_IP'	=> '<strong>IP exclus de la interziceri</strong> pentru motiv „<em>%1$s</em>”<br />» %2$s ',
	'LOG_BAN_EXCLUDE_EMAIL' => '<strong>E-mail exclus de la interziceri</strong> pentru motiv „<em>%1$s</em>”<br />» %2$s ',
	'LOG_BAN_USER'			=> '<strong>Utilizator interzis</strong> pentru motiv „<em>%1$s</em>”<br />» %2$s ',
	'LOG_BAN_IP'			=> '<strong>IP interzis</strong> pentru motiv „<em>%1$s</em>”<br />» %2$s',
	'LOG_BAN_EMAIL'			=> '<strong>E-mail interzis</strong> pentru motiv „<em>%1$s</em>”<br />» %2$s',
	'LOG_UNBAN_USER'		=> '<strong>Interdicție utilizator eliminată</strong><br />» %s',
	'LOG_UNBAN_IP'			=> '<strong>Interdicție IP eliminată</strong><br />» %s',
	'LOG_UNBAN_EMAIL'		=> '<strong>Interdicție e-mail eliminată</strong><br />» %s',

	'LOG_BBCODE_ADD'		=> '<strong>CodBB nou adăugat</strong><br />» %s',
	'LOG_BBCODE_EDIT'		=> '<strong>CodBB modificat</strong><br />» %s',
	'LOG_BBCODE_DELETE'		=> '<strong>CodBB şters</strong><br />» %s',

	'LOG_BOT_ADDED'		=> '<strong>Bot nou adăugat</strong><br />» %s',
	'LOG_BOT_DELETE'	=> '<strong>Bot şters</strong><br />» %s',
	'LOG_BOT_UPDATED'	=> '<strong>Bot existent actualizat</strong><br />» %s',

	'LOG_CLEAR_ADMIN'		=> '<strong>Jurnal admin şters</strong>',
	'LOG_CLEAR_CRITICAL'	=> '<strong>Jurnal erori şters</strong>',
	'LOG_CLEAR_MOD'			=> '<strong>Jurnal moderator şters</strong>',
	'LOG_CLEAR_USER'		=> '<strong>Jurnal utilizator şters</strong><br />» %s',
	'LOG_CLEAR_USERS'		=> '<strong>Jurnale utilizator şterse</strong>',

	'LOG_CONFIG_ATTACH'			=> '<strong>Schimbările setărilor fişierelor ataşate</strong>',
	'LOG_CONFIG_AUTH'			=> '<strong>Schimbările setărilor de autentificare</strong>',
	'LOG_CONFIG_AVATAR'			=> '<strong>Schimbările setărilor avatarelor</strong>',
	'LOG_CONFIG_COOKIE'			=> '<strong>Schimbările setărilor pentru cookie</strong>',
	'LOG_CONFIG_EMAIL'			=> '<strong>Schimbările setărilor de e-mail</strong>',
	'LOG_CONFIG_FEATURES'		=> '<strong>Schimbările opţiunilor forumului</strong>',
	'LOG_CONFIG_LOAD'			=> '<strong>Schimbările setărilor încărcate</strong>',
	'LOG_CONFIG_MESSAGE'		=> '<strong>Schimbările setărilor mesajelor private</strong>',
	'LOG_CONFIG_POST'			=> '<strong>Schimbările setărilor pentru mesaje</strong>',
	'LOG_CONFIG_REGISTRATION'	=> '<strong>Schimbările setărilor înregistrării utilizatorilor</strong>',
	'LOG_CONFIG_FEED'			=> '<strong>Schimbările fluxurilor distribuite modificate</strong>',
	'LOG_CONFIG_SEARCH'			=> '<strong>Schimbările setărilor de căutare</strong>',
	'LOG_CONFIG_SECURITY'		=> '<strong>Schimbările setărilor de securitate</strong>',
	'LOG_CONFIG_SERVER'			=> '<strong>Schimbările setărilor de server</strong>',
	'LOG_CONFIG_SETTINGS'		=> '<strong>Schimbările setărilor de forum</strong>',
	'LOG_CONFIG_SIGNATURE'		=> '<strong>Schimbările setărilor de semnătură</strong>',
	'LOG_CONFIG_VISUAL'			=> '<strong>Schimbările setărilor împotriva boţilor de spam</strong>',

	'LOG_APPROVE_TOPIC'			=> '<strong>Subiect aprobat</strong><br />» %s',
	'LOG_BUMP_TOPIC'			=> '<strong>Subiectul uitlizatorului adus în atenţie</strong><br />» %s',
	'LOG_DELETE_POST'			=> '<strong>Mesaj șters „%1$s” scris de</strong><br />» %2$s',
	'LOG_DELETE_SHADOW_TOPIC'   => '<strong>Subiect umbră şters</strong><br />» %s',
	'LOG_DELETE_TOPIC'          => '<strong>Subiect șters „%1$s” scris de</strong><br />» %2$s',
	'LOG_FORK'                  => '<strong>Subiect copiat</strong><br />» din %s',
	'LOG_LOCK'                  => '<strong>Subiect închis</strong><br />» %s',
	'LOG_LOCK_POST'             => '<strong>Mesaj închis</strong><br />» %s',
	'LOG_MERGE'                 => '<strong>Mesaje unite</strong> în subiectul<br />» %s',
	'LOG_MOVE'                  => '<strong>Subiect mutat</strong><br />» din %1$s în %2$s',
	'LOG_PM_REPORT_CLOSED'		=> '<strong>Raport mesaj privat închis</strong><br />» %s',
	'LOG_PM_REPORT_DELETED'		=> '<strong>Raport mesaj privat şters</strong><br />» %s',
	'LOG_POST_APPROVED'			=> '<strong>Mesaj aprobat</strong><br />» %s',
	'LOG_POST_DISAPPROVED'		=> '<strong>Mesaj neaprobat „%1$s” din următorul motiv</strong><br />» %2$s',
	'LOG_POST_EDITED'			=> '<strong>Mesaj modificat „%1$s” de către</strong><br />» %2$s',
	'LOG_REPORT_CLOSED'			=> '<strong>Raport închis</strong><br />» %s',
	'LOG_REPORT_DELETED'		=> '<strong>Raport şters</strong><br />» %s',
	'LOG_SPLIT_DESTINATION'		=> '<strong>Mesajele împărţite mutate</strong><br />» la %s',
	'LOG_SPLIT_SOURCE'			=> '<strong>Împarte mesaje</strong><br />» de la %s',

	'LOG_TOPIC_APPROVED'		=> '<strong>Subiect aprobat</strong><br />» %s',
	'LOG_TOPIC_DISAPPROVED'		=> '<strong>Subiect neaprobat „%1$s” din următorul motiv</strong><br />%2$s',
	'LOG_TOPIC_RESYNC'			=> '<strong>Contoare subiect resincronizate</strong><br />» %s',
	'LOG_TOPIC_TYPE_CHANGED'	=> '<strong>Tipul schimbat al subiectului</strong><br />» %s',
	'LOG_UNLOCK'				=> '<strong>Subiect deschis</strong><br />» %s',
	'LOG_UNLOCK_POST'			=> '<strong>Mesaj deschis</strong><br />» %s',

	'LOG_DISALLOW_ADD'		=> '<strong>Numele de utilizatori interzise adăugate</strong><br />» %s',
	'LOG_DISALLOW_DELETE'	=> '<strong>Numele de utlizatori interzise şterse</strong>',

	'LOG_DB_BACKUP'			=> '<strong>Copie siguranță bază de date</strong>',
	'LOG_DB_DELETE'			=> '<strong>Copie siguranță bază de date ştearsă</strong>',
	'LOG_DB_RESTORE'		=> '<strong>Copie siguranță bază de date restaurată</strong>',

	'LOG_DOWNLOAD_EXCLUDE_IP'	=> '<strong>IP-uri/nume de hosturi excluse din lista de descărcare</strong><br />» %s',
	'LOG_DOWNLOAD_IP'			=> '<strong>IP-uri/nume de hosturi adăugate în lista de descărcare</strong><br />» %s',
	'LOG_DOWNLOAD_REMOVE_IP'	=> '<strong>IP-uri/nume de hosturi şterse din lista de descărcare</strong><br />» %s',

	'LOG_ERROR_JABBER'		=> '<strong>Eroare Jabber</strong><br />» %s',
	'LOG_ERROR_EMAIL'		=> '<strong>Eroare e-mail</strong><br />» %s',
	
	'LOG_FORUM_ADD'							=> '<strong>Forum nou creat</strong><br />» %s',
	'LOG_FORUM_COPIED_PERMISSIONS'			=> '<strong>Permisiuni forum copiate</strong> de la %1$s<br />» %2$s',
	'LOG_FORUM_DEL_FORUM'					=> '<strong>Forum şters</strong><br />» %s',
	'LOG_FORUM_DEL_FORUMS'					=> '<strong>Forum şters, inclusiv subforumurile sale</strong><br />» %s',
	'LOG_FORUM_DEL_MOVE_FORUMS'				=> '<strong>Forum şters si subforumurile mutate</strong> la %1$s<br />» %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS'				=> '<strong>Forum şters si mesajele mutate </strong> la %1$s<br />» %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS_FORUMS'		=> '<strong>Forum şters inclusiv subforumurile sale, mesajele mutate</strong> la %1$s<br />» %2$s',
	'LOG_FORUM_DEL_MOVE_POSTS_MOVE_FORUMS'	=> '<strong>Forum şters, mesajele mutate</strong> la %1$s <strong>şi subforumurile</strong> la %2$s<br />» %3$s',
	'LOG_FORUM_DEL_POSTS'					=> '<strong>Forum şters, inclusiv mesajele sale</strong><br />» %s',
	'LOG_FORUM_DEL_POSTS_FORUMS'			=> '<strong>Forum şters, inclusiv mesajele şi subforumurile sale</strong><br />» %s',
	'LOG_FORUM_DEL_POSTS_MOVE_FORUMS'		=> '<strong>Forum şters, inclusiv mesajele sale, subforumurile mutate</strong> la %1$s<br />» %2$s',
	'LOG_FORUM_EDIT'						=> '<strong>Detaliile forumului modificate</strong><br />» %s',
	'LOG_FORUM_MOVE_DOWN'					=> '<strong>Forum mutat</strong> %1$s <strong>sub</strong> %2$s',
	'LOG_FORUM_MOVE_UP'						=> '<strong>Forum mutat</strong> %1$s <strong>deasupra</strong> %2$s',
	'LOG_FORUM_SYNC'						=> '<strong>Forum resincronizat</strong><br />» %s',
	'LOG_GENERAL_ERROR'	=> '<strong>A fost întâlnită o eroare generală</strong>: %1$s <br />» %2$s',

	'LOG_GROUP_CREATED'		=> '<strong>Grup nou de utilizatori creat</strong><br />» %s',
	'LOG_GROUP_DEFAULTS'	=> '<strong>Grup „%1$s” făcut iniţial pentru membri</strong><br />» %s',
	'LOG_GROUP_DELETE'		=> '<strong>Grup de utilizatori şters</strong><br />» %s',
	'LOG_GROUP_DEMOTED'		=> '<strong>Lideri retrogradaţi în grupurile de utilizatori</strong> %1$s<br />» %2$s',
	'LOG_GROUP_PROMOTED'	=> '<strong>Membri promovaţi ca lideri în grupurile de utilizatori</strong> %1$s<br />» %2$s',
	'LOG_GROUP_REMOVE'		=> '<strong>Membri scoşi din grupurile de utilizatori</strong> %1$s<br />» %2$s',
	'LOG_GROUP_UPDATED'		=> '<strong>Detaliile grupului de utilizatori actualizate</strong><br />» %s',
	'LOG_MODS_ADDED'		=> '<strong>Noi lideri adăugaţi în grupurile de utilizatori</strong> %1$s<br />» %2$s',
	
	'LOG_USERS_ADDED'		=> '<strong>Membri noi adăugaţi în grupurile de utilizatori</strong> %1$s<br />» %2$s',
	'LOG_USERS_APPROVED'	=> '<strong>Utilizatorii aprobaţi în grupul</strong> %1$s<br />» %2$s',
	'LOG_USERS_PENDING'		=> '<strong>Utilizatorii ce au cerut să adere la grupul „%1$s” şi care aşteaptă aprobarea</strong><br />» %2$s',

	'LOG_IMAGE_GENERATION_ERROR'	=> '<strong>Eroare la crearea imaginii</strong><br />» Eroare în %1$s la linia %2$s: %3$s',

	'LOG_IMAGESET_ADD_DB'	    	=> '<strong>Set nou de imagini adăugat în baza de date</strong><br />» %s',
	'LOG_IMAGESET_ADD_FS'		    => '<strong>Set nou de imagini adăugat în sistemul de fişiere</strong><br />» %s',
	'LOG_IMAGESET_DELETE'	      	=> '<strong>Set de imagini şters</strong><br />» %s',
	'LOG_IMAGESET_EDIT_DETAILS'  	=> '<strong>Detaliile setului de imagini modificate</strong><br />» %s',
	'LOG_IMAGESET_EDIT'		    	=> '<strong>Set de imagini modificat</strong><br />» %s',
	'LOG_IMAGESET_EXPORT'		    => '<strong>Set de imagini exportat</strong><br />» %s',
	'LOG_IMAGESET_LANG_MISSING'		=> '<strong>Setul de imagini nu poate găsi localizarea „%2$s”</strong><br />» %1$s',
	'LOG_IMAGESET_LANG_REFRESHED'	=> '<strong>Localizarea „%2$s” a setului de imagini reîmprospătată</strong><br />» %1$s',
	'LOG_IMAGESET_REFRESHED'     	=> '<strong>Set de imagini reîmprospătat</strong><br />» %s',

	'LOG_INACTIVE_ACTIVATE'	=> '<strong>Utilizatori inactivi activaţi</strong><br />» %s',
	'LOG_INACTIVE_DELETE'	=> '<strong>Utilizatori inactivi şterşi</strong><br />» %s',
	'LOG_INACTIVE_REMIND'	=> '<strong>Email-uri de reamintire trimise utilizatorilor inactivi</strong><br />» %s',
	'LOG_INSTALL_CONVERTED'	=> '<strong>Covertit de la %1$s la phpBB %2$s</strong>',
	'LOG_INSTALL_INSTALLED'	=> '<strong>phpBB instalat %s</strong>',

	'LOG_IP_BROWSER_FORWARDED_CHECK'	=> '<strong>IP sesiune/browser/verificare eşuată X_FORWARDED_FOR</strong><br />»IP utilizator „<em>%1$s</em>” verificat împotriva sesiunii IP „<em>%2$s</em>”, stringul browser-ului utilizatorului „<em>%3$s</em>” verificat împotriva stringului de sesiune a browser-ului „<em>%4$s</em>” şi stringul X_FORWARDED_FOR „<em>%5$s</em>” al utilizatorului verificat împotriva stringului de sesiune X_FORWARDED_FOR „<em>%6$s</em>”.',

	'LOG_JAB_CHANGED'			=> '<strong>Cont Jabber schimbat</strong>',
	'LOG_JAB_PASSCHG'			=> '<strong>Parola Jabber schimbată</strong>',
	'LOG_JAB_REGISTER'			=> '<strong>Cont Jabber înregistrat</strong>',
	'LOG_JAB_SETTINGS_CHANGED'	=> '<strong>Setări Jabber schimbate</strong>',

	'LOG_LANGUAGE_PACK_DELETED'		=> '<strong>Pachet de limbă şters</strong><br />» %s',
	'LOG_LANGUAGE_PACK_INSTALLED'	=> '<strong>Pachet de limbă instalat</strong><br />» %s',
	'LOG_LANGUAGE_PACK_UPDATED'		=> '<strong>Detalii pachet de limbă actualizat</strong><br />» %s',
	'LOG_LANGUAGE_FILE_REPLACED'	=> '<strong>Fişier de limbă inlocuit</strong><br />» %s',
	'LOG_LANGUAGE_FILE_SUBMITTED'	=> '<strong>Fişier de limbă trimis şi plasat în folderul de stocare</strong><br />» %s',

	'LOG_MASS_EMAIL'		=> '<strong>E-mail în masă trimis</strong><br />» %s',

	'LOG_MCP_CHANGE_POSTER'	=> '<strong>Autor în subiect schimbat „%1$s”</strong><br />» din %2$s în %3$s',

	'LOG_MODULE_DISABLE'	=> '<strong>Modul dezactivat</strong><br />» %s',
	'LOG_MODULE_ENABLE'		=> '<strong>Modul activat</strong><br />» %s',
	'LOG_MODULE_MOVE_DOWN'	=> '<strong>Modul mutat în jos</strong><br />» %1$s sub %2$s',
	'LOG_MODULE_MOVE_UP'	=> '<strong>Modul mutat în sus</strong><br />» %1$s deasupra %2$s',
	'LOG_MODULE_REMOVED'	=> '<strong>Modul scos</strong><br />» %s',
	'LOG_MODULE_ADD'		=> '<strong>Modul adăugat</strong><br />» %s',
	'LOG_MODULE_EDIT'		=> '<strong>Modul modificat</strong><br />» %s',

	'LOG_A_ROLE_ADD'		=> '<strong>Rol admin adăugat</strong><br />» %s',
	'LOG_A_ROLE_EDIT'		=> '<strong>Rol admin modificat</strong><br />» %s',
	'LOG_A_ROLE_REMOVED'	=> '<strong>Rol admin şters</strong><br />» %s',
	'LOG_F_ROLE_ADD'		=> '<strong>Rol forum adăugat</strong><br />» %s',
	'LOG_F_ROLE_EDIT'		=> '<strong>Rol forum modificat</strong><br />» %s',
	'LOG_F_ROLE_REMOVED'	=> '<strong>Rol forum şters</strong><br />» %s',
	'LOG_M_ROLE_ADD'		=> '<strong>Rol moderator adăugat</strong><br />» %s',
	'LOG_M_ROLE_EDIT'		=> '<strong>Rol moderator modificat</strong><br />» %s',
	'LOG_M_ROLE_REMOVED'	=> '<strong>Rol moderator şters</strong><br />» %s',
	'LOG_U_ROLE_ADD'		=> '<strong>Rol utilizator adăugat</strong><br />» %s',
	'LOG_U_ROLE_EDIT'		=> '<strong>Rol utilizator modificat</strong><br />» %s',
	'LOG_U_ROLE_REMOVED'	=> '<strong>Rol utilizator şters</strong><br />» %s',

	'LOG_PROFILE_FIELD_ACTIVATE'	=> '<strong>Câmp profil activat</strong><br />» %s',
	'LOG_PROFILE_FIELD_CREATE'		=> '<strong>Câmp profil adăugat</strong><br />» %s',
	'LOG_PROFILE_FIELD_DEACTIVATE'	=> '<strong>Câmp profil dezactivat</strong><br />» %s',
	'LOG_PROFILE_FIELD_EDIT'		=> '<strong>Câmp profil schimbat</strong><br />» %s',
	'LOG_PROFILE_FIELD_REMOVED'		=> '<strong>Câmp profil şters</strong><br />» %s',

	'LOG_PRUNE'					=> '<strong>Forumuri şterse</strong><br />» %s',
	'LOG_AUTO_PRUNE'			=> '<strong>Forumuri şterse automat</strong><br />» %s',
	'LOG_PRUNE_USER_DEAC'		=> '<strong>Utilizatori dezactivaţi</strong><br />» %s',
	'LOG_PRUNE_USER_DEL_DEL'	=> '<strong>Utilizatori şterşi şi mesajele lor şterse</strong><br />» %s',
	'LOG_PRUNE_USER_DEL_ANON'	=> '<strong>Utilizatori şterşi şi mesaje lor reţinute</strong><br />» %s',

	'LOG_PURGE_CACHE'			=> '<strong>Şterge cache</strong>',
	'LOG_PURGE_SESSIONS'		=> '<strong>Şterge sesiuni</strong>',

	'LOG_RANK_ADDED'		=> '<strong>Rang nou adăugat</strong><br />» %s',
	'LOG_RANK_REMOVED'		=> '<strong>Rang şters</strong><br />» %s',
	'LOG_RANK_UPDATED'		=> '<strong>Rang actualizat</strong><br />» %s',

	'LOG_REASON_ADDED'		=> '<strong>Motiv raport/negare adăugat</strong><br />» %s',
	'LOG_REASON_REMOVED'	=> '<strong>Motiv raport/negare şters</strong><br />» %s',
	'LOG_REASON_UPDATED'	=> '<strong>Motiv report/negare actualizat</strong><br />» %s',
	'LOG_REFERER_INVALID' => '<strong>Validare eşuată a Referer-ului</strong><br />»Referer a fost „<em>%1$s</em>”. Cererea a fost respinsă şi sesiunea închisă.',

	'LOG_RESET_DATE'			=> '<strong>Dată de start pentru forum resetată</strong>',
	'LOG_RESET_ONLINE'			=> '<strong>Contorul cu majoritatea utilizatorilor online resetat</strong>',
	'LOG_RESYNC_POSTCOUNTS'		=> '<strong>Contor mesaje pentru utilizatori resincronizat</strong>',
	'LOG_RESYNC_POST_MARKING'	=> '<strong>Subiecte punctate resincronizate</strong>',
	'LOG_RESYNC_STATS'			=> '<strong>Mesaje, subiecte şi statistici utilizatori resincronizate</strong>',

	'LOG_SEARCH_INDEX_CREATED'	=> '<strong>Index de căutare creat pentru</strong><br />» %s',
	'LOG_SEARCH_INDEX_REMOVED'	=> '<strong>Index de căutare şters pentru</strong><br />» %s',
	'LOG_STYLE_ADD'				=> '<strong>Stil nou adăugat</strong><br />» %s',
	'LOG_STYLE_DELETE'			=> '<strong>Stil şters</strong><br />» %s',
	'LOG_STYLE_EDIT_DETAILS'	=> '<strong>Stil modificat</strong><br />» %s',
	'LOG_STYLE_EXPORT'			=> '<strong>Stil exportat</strong><br />» %s',

	'LOG_TEMPLATE_ADD_DB'			=> '<strong>Set template nou adăugat în baza de date</strong><br />» %s',
	'LOG_TEMPLATE_ADD_FS'			=> '<strong>Set template nou adăugat în sistemul de fişiere</strong><br />» %s',
	'LOG_TEMPLATE_CACHE_CLEARED'	=> '<strong>Versiuni de cache şterse ale fişierelor template din set template <em>%1$s</em></strong><br />» %2$s',
	'LOG_TEMPLATE_DELETE'			=> '<strong>Set template şters</strong><br />» %s',
	'LOG_TEMPLATE_EDIT'				=> '<strong>Set template modificat <em>%1$s</em></strong><br />» %2$s',
	'LOG_TEMPLATE_EDIT_DETAILS'		=> '<strong>Detalii template modificat</strong><br />» %s',
	'LOG_TEMPLATE_EXPORT'			=> '<strong>Set template exportat</strong><br />» %s',
	'LOG_TEMPLATE_REFRESHED'		=> '<strong>Set template reîmprospătat</strong><br />» %s',

	'LOG_THEME_ADD_DB'			=> '<strong>Temă nouă adăugată în baza de date</strong><br />» %s',
	'LOG_THEME_ADD_FS'			=> '<strong>Temă nouă adăugată în sistemul de fişiere</strong><br />» %s',
	'LOG_THEME_DELETE'			=> '<strong>Temă ştearsă</strong><br />» %s',
	'LOG_THEME_EDIT_DETAILS'	=> '<strong>Detalii temă modificată</strong><br />» %s',
	'LOG_THEME_EDIT'			=> '<strong>Temă modificată <em>%1$s</em></strong>',
	'LOG_THEME_EDIT_FILE'		=> '<strong>Temă modificată <em>%1$s</em></strong><br />» Fişier modificat <em>%2$s</em>',
	'LOG_THEME_EXPORT'			=> '<strong>Temă exportată</strong><br />» %s',
	'LOG_THEME_REFRESHED'		=> '<strong>Temă reîmprospătată</strong><br />» %s',

	'LOG_UPDATE_DATABASE'	=> '<strong>Bază de date actualizată de la versiunea %1$s la versiunea %2$s</strong>',
	'LOG_UPDATE_PHPBB'		=> '<strong>phpBB actualizat de la versiunea %1$s la versiunea %2$s</strong>',

	'LOG_USER_ACTIVE'		=> '<strong>Utilizator activat</strong><br />» %s',
	'LOG_USER_BAN_USER'		=> '<strong>Utilizator interzis prin management utilizatori</strong> pentru motivul „<em>%1$s</em>”<br />» %2$s',
	'LOG_USER_BAN_IP'		=> '<strong>IP interzis prin management utilizatori</strong> pentru motivul „<em>%1$s</em>”<br />» %2$s',
	'LOG_USER_BAN_EMAIL'	=> '<strong>E-mail interzis prin management utilizatori</strong> pentru motivul „<em>%1$s</em>”<br />» %2$s',
	'LOG_USER_DELETED'		=> '<strong>Utilizator şters</strong><br />» %s',
	'LOG_USER_DEL_ATTACH'	=> '<strong>Toate fişierele ataşate scoase de către utilizatorul</strong><br />» %s',
	'LOG_USER_DEL_AVATAR'	=> '<strong>Avatar utilizator şters</strong><br />» %s',
	'LOG_USER_DEL_OUTBOX'	=> '<strong>Dosarul cu mesaje expediate a fost curăţat</strong><br />» %s',
	'LOG_USER_DEL_POSTS'	=> '<strong>Toate mesajele şterse de către utilizatorul</strong><br />» %s',
	'LOG_USER_DEL_SIG'		=> '<strong>Semnătură utilizator ştearsă</strong><br />» %s',
	'LOG_USER_INACTIVE'		=> '<strong>Utilizator dezactivat</strong><br />» %s',
	'LOG_USER_MOVE_POSTS'	=> '<strong>Mesajele utilizatorului mutate</strong><br />» mesaje de către „%1$s” în forumul „%2$s”',
	'LOG_USER_NEW_PASSWORD'	=> '<strong>Parolă utilizator schimbată</strong><br />» %s',
	'LOG_USER_REACTIVATE'	=> '<strong>Cont utilizator forţat pentru reactivare</strong><br />» %s',
	'LOG_USER_REMOVED_NR'	=> '<strong>Marcaj de utilizator nou şters pentru utilizatorul</strong><br />» %s',
	'LOG_USER_UPDATE_EMAIL'	=> '<strong>E-mail utilizator „%1$s” schimbat</strong><br />» din „%2$s” în „%3$s”',
	'LOG_USER_UPDATE_NAME'	=> '<strong>Nume utilizator schimbat</strong><br />» din „%1$s” în „%2$s”',
	'LOG_USER_USER_UPDATE'	=> '<strong>Detalii utilizator actualizate</strong><br />» %s',

	'LOG_USER_ACTIVE_USER'		=> '<strong>Cont utilzator activat</strong>',
	'LOG_USER_DEL_AVATAR_USER'	=> '<strong>Avatar utilizator şters</strong>',
	'LOG_USER_DEL_SIG_USER'		=> '<strong>Semnătură utilizator ştearsă</strong>',
	'LOG_USER_FEEDBACK'			=> '<strong>Feedback utilizator adăugat</strong><br />» %s',
	'LOG_USER_GENERAL'			=> '<strong>Intrare adăugată:</strong><br />» %s',
	'LOG_USER_INACTIVE_USER'	=> '<strong>Cont utilizator dezactivat</strong>',
	'LOG_USER_LOCK'				=> '<strong>Subiect utilizator blocat</strong><br />» %s',
	'LOG_USER_MOVE_POSTS_USER'	=> '<strong>Toate mesajele mutate în forumul</strong>» %s',
	'LOG_USER_REACTIVATE_USER'	=> '<strong>Cont utilizator forţat pentru reactivare</strong>',
	'LOG_USER_UNLOCK'			=> '<strong>Utilizatorul şi-a deblocat propriul subiect</strong><br />» %s',
	'LOG_USER_WARNING'			=> '<strong>Avertisment utilizator</strong><br />» %s',
	'LOG_USER_WARNING_BODY'		=> '<strong>Următorul avertisment a fost dat acestui utilizator</strong><br />» %s',

	'LOG_USER_GROUP_CHANGE'			=> '<strong>Utilizatorul a schimbat grupul iniţial</strong><br />» %s',
	'LOG_USER_GROUP_DEMOTE'			=> '<strong>Utilizatorul a renunţat ca lider pentru grupul de utilizatori</strong><br />» %s',
	'LOG_USER_GROUP_JOIN'			=> '<strong>Utilizatorul a intrat în grupul </strong><br />» %s',
	'LOG_USER_GROUP_JOIN_PENDING'	=> '<strong>Utilizatorul a aderat la grup şi trebuie să fie aprobat în grupul</strong><br />» %s',
	'LOG_USER_GROUP_RESIGN'			=> '<strong>Utilizatorul a părăsit grupul</strong><br />» %s',
	'LOG_WARNING_DELETED'		=> '<strong>Avertismente şterse pentru utilizatorul</strong><br />» %s',
	'LOG_WARNINGS_DELETED'		=> '<strong>Au fost şterse %2$s avertismente pentru utilizatorul</strong><br />» %1$s', // Example: '<strong>Deleted 2 user warnings</strong><br />» username'
	'LOG_WARNINGS_DELETED_ALL'	=> '<strong>Au fost şterse toate avertismentele utilizatorului</strong><br />» %s',

	'LOG_WORD_ADD'			=> '<strong>Cuvânt restricţionat adăugat</strong><br />» %s',
	'LOG_WORD_DELETE'		=> '<strong>Cuvânt restricţionat şters</strong><br />» %s',
	'LOG_WORD_EDIT'			=> '<strong>Cuvânt restricţionat modificat</strong><br />» %s',
));

?>