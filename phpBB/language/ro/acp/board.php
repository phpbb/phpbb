<?php
/**
*
* acp_board [Română]
*
* @package language
* @version $Id: board.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: board.phpv 8479 2008-05-19 23:00:00 www.phpbb.ro (shara21jonny) Exp $
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

// Board Settings
$lang = array_merge($lang, array(
	'ACP_BOARD_SETTINGS_EXPLAIN'	=> 'Aici puteţi efectua operaţiunile de bază pentru forumul propriu, ca de exemplu alocarea unui nume şi unei descrieri, specificarea valorilor standard pentru fusul orar sau limbă, etc.',
	'CUSTOM_DATEFORMAT'				=> 'Personalizat…',
	'DEFAULT_DATE_FORMAT'			=> 'Format dată',
	'DEFAULT_DATE_FORMAT_EXPLAIN'	=> 'Formatul datei este acelaşi ca şi funcţia PHP <code>date</code>.',
	'DEFAULT_LANGUAGE'				=> 'Limba standard',
	'DEFAULT_STYLE'					=> 'Stil standard',
	'DISABLE_BOARD'					=> 'Dezactivează forum',
	'DISABLE_BOARD_EXPLAIN'			=> 'Aceasta operaţie va face forumul indisponibil pentru utilizatorii ce nu sunt administratori sau moderatori. De asemenea, dacă doriţi puteţi specifica un mesaj scurt (255 caractere) ce va fi afişat.',
	'OVERRIDE_STYLE'				=> 'Suprascrie stilul utilizatorului',
	'OVERRIDE_STYLE_EXPLAIN'		=> 'Înlocuieşte stilul utilizatorului cu cel standard.',
	'SITE_DESC'						=> 'Descriere site',
	'SITE_NAME'						=> 'Nume site',
	'SYSTEM_DST'					=> 'Activează timpul de vară/<abbr title="Daylight Saving Time">DST</abbr>',
	'SYSTEM_TIMEZONE'				=> 'Fusul orar al vizitatorului',
	'SYSTEM_TIMEZONE_EXPLAIN'         => 'Fusul orar folosit pentru afişarea timpului utilizatorilor ce nu sunt autentificaţi (vizitatori, roboţi). Utilizatorii autentificaţi specifică fusul orar în timpul înregistrării şi-l pot modifica prin panoul de control al utilizatorului.',
	'WARNINGS_EXPIRE'				=> 'Durată avertisment',
	'WARNINGS_EXPIRE_EXPLAIN'		=> 'Numărul zilelor ce trebuie să treacă înainte ca un avertisment să expire automat din înregistrarea unui utilizator. Setați 0 pentru a face avertismentele permanente',
));

// Board Features
$lang = array_merge($lang, array(
	'ACP_BOARD_FEATURES_EXPLAIN'	=> 'Aici puteţi activa/dezactiva mai multe funcţionalităţi ale forumului',

	'ALLOW_ATTACHMENTS'			=> 'Permite fişiere ataşate',
	'ALLOW_BIRTHDAYS'			=> 'Permite zile de naştere',
	'ALLOW_BIRTHDAYS_EXPLAIN'	=> 'Permite ca zilele de naştere să fie specificate, iar vârsta să fie afişată în profilurile utilizatorilor. Reţineti că lista zilelor de naştere din pagina de start a forumului este controlată de o altă setare.',
	'ALLOW_BOOKMARKS'			=> 'Permite marcarea subiectelor',
	'ALLOW_BOOKMARKS_EXPLAIN'	=> 'Utilizatorul poate să păstreze legăturile către subiectele preferate. ',
	'ALLOW_BBCODE'				=> 'Permite cod BB',
	'ALLOW_FORUM_NOTIFY'		=> 'Permite abonarea la forumuri',
	'ALLOW_NAME_CHANGE'			=> 'Permite schimbări nume de utilizator',
	'ALLOW_NO_CENSORS'			=> 'Permite dezactivarea cuvintelor cenzurate',
	'ALLOW_NO_CENSORS_EXPLAIN'	=> 'Utilizatorii pot alege dacă permit cenzurarea automată a cuvintelor în mesajele din forum, dar şi a celor din mesajele private.',
	'ALLOW_PM_ATTACHMENTS'		=> 'Permite fişiere ataşate în mesajele private',
	'ALLOW_PM_REPORT'			=> 'Permite utilizatorilor să raporteze mesaje private',
	'ALLOW_PM_REPORT_EXPLAIN'	=> 'Dacă este activată această opţiune, utilizatorii au posibilitatea de a raporta un mesaj privat pe care l-au primit şi care va fi trimis moderatorilor forumului. Aceste mesaje private vor fi apoi vizibile în Panoul moderatorului.',
	'ALLOW_QUICK_REPLY'			=> 'Permite răspunsuri rapide',
	'ALLOW_QUICK_REPLY_EXPLAIN'	=> 'Această setare permite ca opţiunea de răspuns rapid să fie dezactivată pe tot forumul. Când este activată, setările specifice fiecărui forum vor fi folosite pentru a determina dacă răspunsul rapid este afişat în forumurile individuale.',
	'ALLOW_QUICK_REPLY_BUTTON'	=> 'Trimite şi activează răspunsul rapid în toate forumurile',

	'ALLOW_SIG'					=> 'Permite semnături',
	'ALLOW_SIG_BBCODE'			=> 'Permite cod BB în semnăturile utilizatorilor',
	'ALLOW_SIG_FLASH'			=> 'Permite folosirea etichetei de cod BB <code>[FLASH]</code> în semnăturile utilizatorilor',
	'ALLOW_SIG_IMG'				=> 'Permite folosirea etichetei de cod BB <code>[IMG]</code> în semnăturile utilizatorilor',
	'ALLOW_SIG_LINKS'			=> 'Permite folosirea legăturilor în semnăturile utilizatorilor',
	'ALLOW_SIG_LINKS_EXPLAIN'	=> 'Dacă dezactivaţi eticheta de cod BB <code>[URL]</code> atunci şi legăturile automate vor fi dezactivate.',
	'ALLOW_SIG_SMILIES'			=> 'Permite folosirea zâmbetelor în semnăturile utilizatorilor',
	'ALLOW_SMILIES'				=> 'Permite zâmbete',
	'ALLOW_TOPIC_NOTIFY'		=> 'Permite abonarea la subiecte',
	'BOARD_PM'					=> 'Mesagerie privată',
	'BOARD_PM_EXPLAIN'			=> 'Activează mesageria privată pentru toţi utilizatorii.',
));

// Avatar Settings
$lang = array_merge($lang, array(
	'ACP_AVATAR_SETTINGS_EXPLAIN'	=> 'Avatar-urile sunt în general imagini mici şi unice pe care utilizatorul le poate asocia cu el însuşi. Depinzând de stil, acestea sunt în general afişate sub numele utilizatorului când se vizualizează subiectele. Aici puteţi specifica modul în care utilizatorii pot să-şi defineasca avatarurile (imaginile asociate). Ca să puteţi încărca avataruri trebuie să aveţi creat deja directorul specificat mai jos şi să vă asiguraţi că poate fi scris prin serverul web. De asemenea, reţineţi că dimensiunile limitate ale fişierelor sunt impuse doar la încărcarea avatarelor, ele nu se aplică pentru imaginile aflate la distanţă.',
	'ALLOW_AVATARS'					=> 'Activare avataruri',
	'ALLOW_AVATARS_EXPLAIN'			=> 'Permite folosirea globală a avatarurilor;<br />Dacă dezactivaţi avatarurile în general sau avatarurile unui anumit mod, avatarurile dezactivate nu vor mai fi afişate în forum dar utilizatorii vor avea în continuare posibilitatea de a-şi descărca propriul avatar în Panoul utilizatorului.',

	'ALLOW_LOCAL'					=> 'Permite galerie avataruri',
	'ALLOW_REMOTE'					=> 'Permite avataruri la distanţă',
	'ALLOW_REMOTE_EXPLAIN'			=> 'Avatarul este localizat pe alt site web',
	'ALLOW_REMOTE_UPLOAD'			=> 'Permite încărcarea avatarurilor la distanţă',
	'ALLOW_REMOTE_UPLOAD_EXPLAIN'	=> 'Permite încărcarea avatarurilor aflate pe alt site web.',
	'ALLOW_UPLOAD'					=> 'Permite încărcarea avatarurilor',
	'AVATAR_GALLERY_PATH'			=> 'Cale galerie avatar',
	'AVATAR_GALLERY_PATH_EXPLAIN'	=> 'Calea din directorul rădăcină phpBB pentru imagini preîncărcate, de exemplu <samp>images/avatars/gallery</samp>',
	'AVATAR_STORAGE_PATH'			=> 'Cale stocare avatar',
	'AVATAR_STORAGE_PATH_EXPLAIN'	=> 'Calea din directorul rădăcină phpBB, de exemplu. <samp>images/avatars/upload</samp>.<br />Funționalitatea de încărcare a imaginii asociate (avatar) <strong>nu va fi disponibilă</strong> dacă nu se poate scrie în această locație.',
	'MAX_AVATAR_SIZE'				=> 'Dimensiunile maxime ale avatarului',
	'MAX_AVATAR_SIZE_EXPLAIN'		=> 'Lăţime x Înălţime în pixeli',
	'MAX_FILESIZE'					=> 'Dimensiunile maxime ale fişierului avatar',
	'MAX_FILESIZE_EXPLAIN'			=> 'Pentru fişierele avatar încărcate',
	'MIN_AVATAR_SIZE'				=> 'Dimensiunile minime ale avatarului',
	'MIN_AVATAR_SIZE_EXPLAIN'		=> 'Lăţime x Înălţime în pixeli',
));

// Message Settings
$lang = array_merge($lang, array(
	'ACP_MESSAGE_SETTINGS_EXPLAIN'		=> 'Aici puteţi defini toate setările standard pentru mesageria privată.',

	'ALLOW_BBCODE_PM'			=> 'Permite cod BB în mesajele private',
	'ALLOW_FLASH_PM'			=> 'Permite folosirea etichetei codului BB de tipul <code>[FLASH]</code>',
	'ALLOW_FLASH_PM_EXPLAIN'	=> 'Reţineţi că facilitatea de a folosi fişiere flash în mesajele private, dacă este activată aici, depinde şi de permisiuni.',
	'ALLOW_FORWARD_PM'			=> 'Permite trimiterea mai departe a mesajelor private',
	'ALLOW_IMG_PM'				=> 'Permite folosirea etichetelor codului BB de tipul <code>[IMG]</code>',
	'ALLOW_MASS_PM'				=> 'Permite trimiterea mesajelor private către mai mulţi utilizatori şi grupuri',
	'ALLOW_MASS_PM_EXPLAIN'      => 'Trimiterea la grupuri poate fi modificată pentru fiecare grup în cadrul secţiunii de setări a grupului.',
	'ALLOW_PRINT_PM'			=> 'Permite previzualizarea tipăririlor în mesageria privată',
	'ALLOW_QUOTE_PM'			=> 'Permite citate în mesageria privată',
	'ALLOW_SIG_PM'				=> 'Permite semnătura în mesajele private',
	'ALLOW_SMILIES_PM'			=> 'Permite zâmbete în mesajele private',
	'BOXES_LIMIT'				=> 'Numărul maxim de mesaje private pe căsuţă',
	'BOXES_LIMIT_EXPLAIN'		=> 'Utilizatorii pot primi nu mai mult decât acest număr de mesaje în fiecare din căsuţele lor de mesaje. Specificaţi valoarea 0 pentru a permite un număr nelimitat de mesaje.',
	'BOXES_MAX'					=> 'Numărul maxim de directoare pentru mesaje private',
	'BOXES_MAX_EXPLAIN'			=> 'Standard, utilizatorii pot crea atât de multe directoare personale pentru mesajele private.',
	'ENABLE_PM_ICONS'			=> 'Permite folosirea iconiţelor pentru subiect în mesajele private',
	'FULL_FOLDER_ACTION'		=> 'Acţiunea standard pentru director plin',
	'FULL_FOLDER_ACTION_EXPLAIN'=> 'Acţiunea standard ce va fi luată dacă directorul utilizatorului este plin, presupunând că opțiunea utilizatorului setată pentru toate directoarele, nu este aplicabilă. Singura excepţie este pentru directorul „Mesaje trimise” unde acţiunea iniţială este mereu aceea de a şterge mesajele vechi.',
	'HOLD_NEW_MESSAGES'			=> 'Păstrează mesajele noi',
	'PM_EDIT_TIME'				=> 'Limita timpului de modificare',
	'PM_EDIT_TIME_EXPLAIN'		=> 'Limitează timpul disponibil pentru a modifica mesajele private care încă nu au fost trimise. Specificând valoarea 0, această operaţie va fi dezactivată.',
	'PM_MAX_RECIPIENTS'         => 'Numărul maxim al destinatarilor permişi',
  'PM_MAX_RECIPIENTS_EXPLAIN'   => 'Numărul maxim al destinatarilor permişi într-un mesaj privat. Dacă este specificat 0 atunci este permis un număr nelimitat. Această setare poate fi modificată pentru fiecare grup în cadrul secţiunii de setări a grupului.',
));

// Post Settings
$lang = array_merge($lang, array(
	'ACP_POST_SETTINGS_EXPLAIN'			=> 'Aici puteţi configura toate setările standard pentru scriere.',
	'ALLOW_POST_LINKS'					=> 'Permite legături în mesaje/mesaje private',
	'ALLOW_POST_LINKS_EXPLAIN'			=> 'Dacă dezactivaţi etichetele de cod BB <code>[URL]</code>, atunci sunt dezactivate şi URL-urile automate/magice.',
	'ALLOW_POST_FLASH'					=> 'Permite folosirea etichetei de cod BB <code>[FLASH]</code> în mesaje.',
	'ALLOW_POST_FLASH_EXPLAIN'			=> 'Dacă eticheta de cod BB <code>[FLASH]</code> este dezactivată, atunci este dezactivat şi în mesaje. Pe de altă parte, sistemul de permisiuni controlează care utilizator poate folosi eticheta de cod BB <code>[FLASH]</code>.',
	
	'BUMP_INTERVAL'					=> 'Interval popularitate',
	'BUMP_INTERVAL_EXPLAIN'			=> 'Numărul de minute, ore sau zile între ultimul mesaj din subiect şi posibilitatea de a populariza acest subiect. Specificând 0 veţi dezactiva complet această acţiune',
	'CHAR_LIMIT'					=> 'Numărul maxim de caractere pe mesaj',
	'CHAR_LIMIT_EXPLAIN'			=> 'Numărul caracterelor permise într-un mesaj. Specificaţi 0 pentru un număr nelimitat de caractere.',
	'DELETE_TIME'					=> 'Limitare timp de ştergere',
	'DELETE_TIME_EXPLAIN'			=> 'Limitează intervalul de timp disponibil pentru a şterge un mesaj nou. Specificând valoarea 0, dezactivaţi această acţiune.',
	'DISPLAY_LAST_EDITED'			=> 'Afişează timpul când s-a efectuat ultima modificare',
	'DISPLAY_LAST_EDITED_EXPLAIN'	=> 'Selectaţi dacă informaţiile despre ultima modificare să fie afişate în mesaje.',
	'EDIT_TIME'						=> 'Limitează timpul de modificare',
	'EDIT_TIME_EXPLAIN'				=> 'Limitează timpul disponibil pentru modificarea unui mesaj nou. Specificând valoarea 0, dezactivaţi această acţiune.',
	'FLOOD_INTERVAL'				=> 'Interval flood',
	'FLOOD_INTERVAL_EXPLAIN'		=> 'Numărul de secunde pe care un utilizator trebuie să-l aştepte între publicări. Pentru a le permite utilizatorilor să ignore această restricţie, modificaţi-le permisiunile.',
	'HOT_THRESHOLD'					=> 'Limită subiect popular',
	'HOT_THRESHOLD_EXPLAIN'			=> 'Numărul de mesaje pe subiect necesare pentru marcarea acestuia ca subiect popular. Specificaţi valoarea 0 pentru a dezactiva subiectele populare.',
	'MAX_POLL_OPTIONS'				=> 'Numărul maxim de opţiuni pentru chestionare',
	'MAX_POST_FONT_SIZE'			=> 'Mărimea maximă a fontului în mesaj',
	'MAX_POST_FONT_SIZE_EXPLAIN'	=> 'Mărimea maximă a fontului permisă într-un mesaj. Specificaţi valoarea 0 pentru mărime nelimitată a fontului.',
	'MAX_POST_IMG_HEIGHT'			=> 'Înălţimea maximă a imaginii în mesaj',
	'MAX_POST_IMG_HEIGHT_EXPLAIN'	=> 'Înălţimea maximă a unei imagini/fişier flash în posturi. Specificaţi valoarea 0 pentru înalţime nelimitată.',
	'MAX_POST_IMG_WIDTH'			=> 'Lăţimea maximă a unei imagini într-un mesaj',
	'MAX_POST_IMG_WIDTH_EXPLAIN'	=> 'Lăţimea maximă a unei imagini/fişier flash în mesaje. Specificaţi valoarea 0 pentru lăţime nelimitată.',
	'MAX_POST_URLS'					=> 'Număr maxim de legături pe mesaj',
	'MAX_POST_URLS_EXPLAIN'			=> 'Numărul maxim de URL-uri într-un mesaj. Specificaţi valoarea 0 pentru legături nelimitate.',
	'MIN_CHAR_LIMIT'				=> 'Număr minim de caractere pe mesaj',
	'MIN_CHAR_LIMIT_EXPLAIN'		=> 'Numărul minim de caractere pe care utilizatorul trebuie să-l adauge într-un mesaj. Valoarea minimă pentru această setare este 1.',

	'POSTING'						=> 'Publicare',
	'POSTS_PER_PAGE'				=> 'Mesaje pe pagină',
	'QUOTE_DEPTH_LIMIT'				=> 'Numărul maxim de citate în cascadă',
	'QUOTE_DEPTH_LIMIT_EXPLAIN'		=> 'Numărul maxim de citate în cascadă dintr-un mesaj. Specificaţi valoarea 0 pentru adâncime nelimitată.',
	'SMILIES_LIMIT'					=> 'Numărul maxim de zâmbete pe mesaj',
	'SMILIES_LIMIT_EXPLAIN'			=> 'Numărul maxim de zâmbete dintr-un mesaj. Specificaţi valoarea 0 pentru zâmbete nelimitate.',
	'SMILIES_PER_PAGE'				=> 'Zâmbete pe pagină',
	'TOPICS_PER_PAGE'				=> 'Subiecte pe pagină',
));

// Signature Settings
$lang = array_merge($lang, array(
	'ACP_SIGNATURE_SETTINGS_EXPLAIN'	=> 'Aici puteţi alege toate setările standard pentru semnături.',

	'MAX_SIG_FONT_SIZE'				=> 'Mărimea maximă a fontului în semnătură',
	'MAX_SIG_FONT_SIZE_EXPLAIN'		=> 'Mărimea maximă a fontului permisă în semnăturile utilizatorilor. Specificaţi valoarea 0 pentru mărime nelimitată.',
	'MAX_SIG_IMG_HEIGHT'			=> 'Înălţimea maximă a imaginii în semnătură',
	'MAX_SIG_IMG_HEIGHT_EXPLAIN'	=> 'Înălţimea maximă a unei imagini/fişier flash în semnăturile utilizatorilor. Specificaţi valoarea 0 pentru înalţime nelimitată.',
	'MAX_SIG_IMG_WIDTH'				=> 'Lăţimea maximă a imaginii din semnătură',
	'MAX_SIG_IMG_WIDTH_EXPLAIN'		=> 'Lăţimea maximă a unei imagini/fişier flash în semnăturile utilizatorilor. Specificaţi valoarea 0 pentru lăţime nelimitată.',
	'MAX_SIG_LENGTH'				=> 'Lungimea maximă a semnăturii',
	'MAX_SIG_LENGTH_EXPLAIN'		=> 'Numărul maxim de caractere din semnăturile utilizatorilor.',
	'MAX_SIG_SMILIES'				=> 'Numărul maxim de zâmbetele pe semnătură',
	'MAX_SIG_SMILIES_EXPLAIN'		=> 'Numărul maxim de zămbete permis în semnăturile utilizatorilor. Specificaţi valoarea 0 pentru zâmbete nelimitate.',
	'MAX_SIG_URLS'					=> 'Numărul maxim de legături pe semnătură',
	'MAX_SIG_URLS_EXPLAIN'			=> 'Numărul maxim de legături în semnăturile utilizatorilor. Specificaţi valoarea 0 pentru legături nelimitate.',
));

// Registration Settings
$lang = array_merge($lang, array(
	'ACP_REGISTER_SETTINGS_EXPLAIN'		=> 'Aici puteţi defini setările asocitate înregistrarii şi profilului.',

	'ACC_ACTIVATION'			=> 'Activare cont',
	'ACC_ACTIVATION_EXPLAIN'	=> 'Aceasta setare determină dacă utilizatorii au acces imediat la forum sau dacă este necesară o confirmare. De asemenea, puteţi dezactiva noile înregistrări. Folosirea funcției de email pe forum trebuie să fie permisă pentru a utiliza activarea de către utilizator sau administrator',
	'NEW_MEMBER_POST_LIMIT'			=>'Limită mesaj pentru noii membrii',
	'NEW_MEMBER_POST_LIMIT_EXPLAIN'	=> 'Membrii noi apar în grupul <em>Utilizatori înregistraţi recent</em> până când ating acest număr de mesaje. Puteţi folosi acest grup pentru a restricţiona accesul la sistemul de mesagerie privată sau pentru a le revizui mesajele. <strong>Valoarea 0 dezactivează această facilitate.</strong>',
	'NEW_MEMBER_GROUP_DEFAULT'		=> 'Setează ca standard grupul Utilizatori înregistraţi recent',
	'NEW_MEMBER_GROUP_DEFAULT_EXPLAIN'	=> 'Dacă această opţiune este Da şi limita de mesaje pentru un membru nou este specificată, utilizatorii înregistraţi recent nu doar vor fi adăugaţi la grupul <em>Utilizatori înregistraţi recent</em> dar acest grup va fi de asemenea considerat din oficiu. Această opţiune ar putea fi utilă dacă doriţi să asociaţi unui grup în mod automat un rang şi/sau un avatar pe care utilizatorul îl moşteneşte ulterior.',
	'ACC_ADMIN'					=> 'De către administrator',
	'ACC_DISABLE'				=> 'Dezactivare înregistrare',
	'ACC_NONE'					=> 'Fără activare (acces imediat)',
	'ACC_USER'					=> 'De către utilizator (verificare prin email)',
//	'ACC_USER_ADMIN'			=> 'Utilizator + administrator',
	'ALLOW_EMAIL_REUSE'			=> 'Permite refolosirea adresei de e-mail',
	'ALLOW_EMAIL_REUSE_EXPLAIN'	=> 'Utilizatorii diferiţi se pot înregistra cu aceeaşi adresă de e-mail.',
	'COPPA'						=> 'COPPA',
	'COPPA_FAX'					=> 'Număr de fax COPPA',
	'COPPA_MAIL'				=> 'Adresă mail COPPA',
	'COPPA_MAIL_EXPLAIN'		=> 'Aceasta este adresa de mail unde vor fi trimise formularele de înregistrare COPPA.',
	'ENABLE_COPPA'				=> 'Permite COPPA',
	'ENABLE_COPPA_EXPLAIN'		=> 'Această opţiune le cere utilizatorilor să declare dacă au 13 ani sau mai mult în conformitate cu declaraţia U.S. COPPA Act. Dacă această opţiune este dezactivată, atunci grupurile specifice COPPA nu vor mai fi afişate.',
	'MAX_CHARS'					=> 'Max',
	'MIN_CHARS'					=> 'Min',
	'NO_AUTH_PLUGIN'			=> 'Nu a fost găsit niciun plugin de autentificare potrivit.',
	'PASSWORD_LENGTH'			=> 'Lungime parolă',
	'PASSWORD_LENGTH_EXPLAIN'	=> 'Numărul minim şi maxim de caractere în parolă.',
	'REG_LIMIT'					=> 'Încercări înregistrare',
	'REG_LIMIT_EXPLAIN'			=> 'Numărul de încercări pe care utilizatorul le poate face la rezolvarea sarcinii anti-spam înainte ca sesiunea să fie închisă.',
	'USERNAME_ALPHA_ONLY'		=> 'Numai caractere alfanumerice',
	'USERNAME_ALPHA_SPACERS'	=> 'Alfanumerice şi spaţii',
	'USERNAME_ASCII'			=> 'ASCII (niciun unicod internaţional)',
	'USERNAME_LETTER_NUM'		=> 'Orice literă şi număr',
	'USERNAME_LETTER_NUM_SPACERS'	=> 'Orice literă, număr şi spaţiu',
	'USERNAME_CHARS'			=> 'Limitează caracterele utilizatorului',
	'USERNAME_CHARS_ANY'		=> 'Orice caracter',
	'USERNAME_CHARS_EXPLAIN'	=> 'Restricţionează tipul de caractere ce ar putea fi folosit în numele utilizatorilor, spaţierile sunt; spaţiu, -, +, _, [ şi ]',
	'USERNAME_LENGTH'			=> 'Lungime nume utilizator',
	'USERNAME_LENGTH_EXPLAIN'	=> 'Numărul minim şi maxim de caractere în numele utilizatorului.',
));

// Feeds
$lang = array_merge($lang, array(
	'ACP_FEED_MANAGEMENT'				=> 'Setările generale pentru fluxurile distribuite',
	'ACP_FEED_MANAGEMENT_EXPLAIN'		=> 'Acest modul permite diverse fluxuri ATOM, parsarea oricărui cod BB în mesaje pentru a le face citibile în fluxurile externe.',
  
  'ACP_FEED_GENERAL'					=> 'Setările generale ale fluxului',
  'ACP_FEED_POST_BASED'				=> 'Setări pentru mesaje',
	'ACP_FEED_TOPIC_BASED'				=> 'Setări pentru subiecte',
	'ACP_FEED_SETTINGS_OTHER'			=> 'Alte fluxuri şi setări',
	
	'ACP_FEED_ENABLE'					=> 'Activare Fluxuri',
	'ACP_FEED_ENABLE_EXPLAIN'			=> 'Activează sau dezactivează fluxurile ATOM pentru întregul forum.<br />Dezactivând această opţiune veţi dezactiva toate fluxurile fără a mai conta opţiunile setate mai jos.',
	'ACP_FEED_LIMIT'					=> 'Numărul de elemente',
	'ACP_FEED_LIMIT_EXPLAIN'			=> 'Numărul maxim de elemente ale fluxului ce vor fi afişate.',

	'ACP_FEED_OVERALL'			=> 'Activare flux din toate subiectele',
	'ACP_FEED_OVERALL_EXPLAIN'	=> 'Activează fluxul „Toate Subiectele”',
	'ACP_FEED_FORUM'					=> 'Activare fluxuri pe fiecare forum',
	'ACP_FEED_FORUM_EXPLAIN'			=> 'Mesajele noi din forum.',
	'ACP_FEED_TOPIC'					=> 'Activare fluxuri pe fiecare subiect',
	'ACP_FEED_TOPIC_EXPLAIN'			=> 'Mesajele noi din subiecte.',
	
	'ACP_FEED_TOPICS_NEW'				=> 'Activează fluxuri pentru subiectele noi',
	'ACP_FEED_TOPICS_NEW_EXPLAIN'		=> 'Activează fluxul „Subiecte noi” ce afişează ultimele subiecte create incluzând primul mesaj.',
	'ACP_FEED_TOPICS_ACTIVE'			=> 'Activează fluxul subiectelor active',
	'ACP_FEED_TOPICS_ACTIVE_EXPLAIN'	=> 'Activează fluxul „Subiecte active” ce afişează ultimele subiecte active incluzând ultimul mesaj.',
	'ACP_FEED_NEWS'						=> 'Fluxuri de ştiri',
	'ACP_FEED_NEWS_EXPLAIN'				=> 'Extrage primul mesaj din aceste forumuri. Nu selectaţi un forum pentru a dezactiva fluxul de ştiri.<br />Selectaţi mai multe forumuri ţinând apăsată tasta <samp>CTRL</samp> şi făcând click.',

  'ACP_FEED_OVERALL_FORUMS'			=> 'Activare fluxuri din toate forumurile',
	'ACP_FEED_OVERALL_FORUMS_EXPLAIN'	=> 'Activează fluxul „Toate forumurile” ce afişează o listă cu forumurile.',
	
	'ACP_FEED_HTTP_AUTH'				=> 'Permite autentificare HTTP',
	'ACP_FEED_HTTP_AUTH_EXPLAIN'		=> 'Activează autentificarea HTTP ce permite utilizatorilor să primească informaţii ce sunt ascunse vizitatorilor prin adăugarea parametrului <samp>auth=http</samp> în URL-ul fluxului. Reţineţi că anumite setări PHP necesită mai multe modificări în fişierul .htaccess. Instrucţiunile pot fi găsite în acel fişier.',
	'ACP_FEED_ITEM_STATISTICS'			=> 'Statistici elemente',
	'ACP_FEED_ITEM_STATISTICS_EXPLAIN'	=> 'Afişează statistici individuale sub elementele fluxului<br />(Scris de, data şi ora, Răspunsuri, Citiri)',
	
	'ACP_FEED_EXCLUDE_ID'				=> 'Exclude aceste forumuri',
	'ACP_FEED_EXCLUDE_ID_EXPLAIN'		=> 'Conţinutul acestor forumuri <strong>nu va fi inclus în fluxuri</strong>. Se vor extrage informaţii din toate forumurile dacă nu selectaţi niciun forum.<br />Selectaţi/Deselectaţi mai multe forumuri ţinând apăsată tasta <samp>CTRL</samp> şi făcând click.',
));


// Visual Confirmation Settings
$lang = array_merge($lang, array(
  'ACP_VC_SETTINGS_EXPLAIN'				=> 'Aici puteţi selecta şi configura componente ce implementează diverse metode pentru a respinge încercările de înregistrare ale aşa numiţilor roboţi de spam. Aceste plugin-uri provoaca utilizatorul cu un <em>CAPTCHA</em>, un test care este conceput a fi dificil de rezolvat de către calculatoare.',
	'AVAILABLE_CAPTCHAS'					=> 'Componente disponibile',
	'CAPTCHA_UNAVAILABLE'					=> 'Componenta nu poate fi selectată pentru că nu sunt îndeplinite cerinţele pentru a putea fi folosită.',
	'CAPTCHA_GD'							=> 'Imagine GD',
	'CAPTCHA_GD_3D'							=> 'Imagine GD 3D',

	'CAPTCHA_GD_FOREGROUND_NOISE'	    	=> 'Zgomot în prim plan',
	'CAPTCHA_GD_EXPLAIN'		        	=> 'Foloseşte GD pentru a face o imagine antispam mai avansată.',
	'CAPTCHA_GD_FOREGROUND_NOISE_EXPLAIN'	=> 'Foloseşte zgomotul în prim plan pentru a face imaginea mai greu de citit.',
	'CAPTCHA_GD_X_GRID'						=> 'Zgomotul de fundal al axei x',
	'CAPTCHA_GD_X_GRID_EXPLAIN'				=> 'Foloseşte valorile mai mici pentru imaginea mai greu de citit. 0 va dezactiva zgomotul de fundal al axei x.',
	'CAPTCHA_GD_Y_GRID'						=> 'Zgomotul de fundal al axei y',
	'CAPTCHA_GD_Y_GRID_EXPLAIN'				=> 'Foloseşte valorile mai mici pentru imaginea mai greu de citit. 0 va dezactiva zgomotul de fundal al axei y.',
	'CAPTCHA_GD_WAVE'						=> 'Distorsiune gen val',
	'CAPTCHA_GD_WAVE_EXPLAIN'				=> 'Aplică o distorsiune gen val la imagine.',
	'CAPTCHA_GD_3D_NOISE'					=> 'Adaugă obiecte cu zgomot 3D',
	'CAPTCHA_GD_3D_NOISE_EXPLAIN'			=> 'Adaugă obiecte suplimentare peste litere la imaginea.',
	'CAPTCHA_GD_FONTS'						=> 'Foloseşte fonturi diferite',
	'CAPTCHA_GD_FONTS_EXPLAIN'				=> 'Această setare controlează câte forme diferite sunt folosite pentru litere. Puteţi doar să folosiţi formele standard sau să introduceţi litere prelucrate. Adăugarea literelor mici este de asemenea posibilă.',
	'CAPTCHA_FONT_DEFAULT'					=> 'Standard',
	'CAPTCHA_FONT_NEW'						=> 'Forme noi',
	'CAPTCHA_FONT_LOWER'					=> 'Utilizează şi litere mici',
	
	'CAPTCHA_NO_GD'							=> 'Imagine simplă',
	'CAPTCHA_PREVIEW_MSG'					=> 'Schimbările proprii nu au fost salvate. Aceasta este doar o previzualizare.',
	'CAPTCHA_PREVIEW_EXPLAIN'				=> 'Cum va arăta componenta folosind selecţia curentă.',

	'CAPTCHA_SELECT'						=> 'Componente instalate',
	'CAPTCHA_SELECT_EXPLAIN'				=> 'Lista include componentele recunoscute de forum. Elementele gri nu sunt disponibile imediat şi ar putea să necesite configurare înainte de folosire.',
	'CAPTCHA_CONFIGURE'						=> 'Configurare componente',
	'CAPTCHA_CONFIGURE_EXPLAIN'				=> 'Schimbă setările pentru componenta selectată.',
	'CONFIGURE'								=> 'Configurare',
	'CAPTCHA_NO_OPTIONS'					=> 'Această componentă nu are opţiuni de configurare.',

	'VISUAL_CONFIRM_POST'					=> 'Activează acţiuni împotriva boţilor de spam pentru mesajele vizitatorilor',
	'VISUAL_CONFIRM_POST_EXPLAIN'			=> 'Cere utilizatorilor vizitatori să treacă pasul împotriva boţilor de spam pentru a preveni mesajele automate.',
	'VISUAL_CONFIRM_REG'					=> 'Activează acţiuni împotriva boţilor de spam pentru înregistrări',
	'VISUAL_CONFIRM_REG_EXPLAIN'			=> 'Cere utilizatorilor noi să treacă pasul împotriva boţilor de spam pentru a preveni înregistrările automate.',
	'VISUAL_CONFIRM_REFRESH'				=> 'Permite utilizatorilor să reîncarce pasul împotriva boţilor de spam',
	'VISUAL_CONFIRM_REFRESH_EXPLAIN'		=> 'Permite utilizatorilor să solicite un pas nou împotriva boţilor de spam dacă nu pot rezolva pasul curent în timpul înregistrării. Anumite componente s-ar putea să nu suporte această opţiune.',


));

// Cookie Settings
$lang = array_merge($lang, array(
	'ACP_COOKIE_SETTINGS_EXPLAIN'		=> 'Aceste detalii definesc datele folosite pentru a trimite cookie-urile către browserele utilizatorilor. În majoritatea cazurilor, valorile standard pentru setările cookie ar trebui să fie suficiente. Dacă trebuie să schimbaţi vreuna, aveţi grijă, setările incorecte pot face imposibilă autentificarea utilizatorilor.',

	'COOKIE_DOMAIN'				=> 'Domeniu cookie',
	'COOKIE_NAME'				=> 'Nume cookie',
	'COOKIE_PATH'				=> 'Cale cookie',
	'COOKIE_SECURE'				=> 'Securizare cookie',
	'COOKIE_SECURE_EXPLAIN'		=> 'Dacă serverul propriu rulează via SSL atunci setaţi această opţiune pentru a o activa, altfel rămâne ca dezactivată. Având această opţiune activată şi serverul propriu nu rulează via SSL, atunci vor apărea erori de server în timpul redirecţionărilor.',
	'ONLINE_LENGTH'				=> 'Limită timp utilizatori online',
	'ONLINE_LENGTH_EXPLAIN'		=> 'Numărul de minute după care utilizatorii inactivi nu vor mai apărea în secţiunea „Cine este conectat”. Cu cât este mai mare valoarea, cu atât este lungă procesarea necesară pentru a genera această listă.',
	'SESSION_LENGTH'			=> 'Durată sesiune',
	'SESSION_LENGTH_EXPLAIN'	=> 'Sesiunea va expira după acest timp, în secunde.',
));

// Load Settings
$lang = array_merge($lang, array(
	'ACP_LOAD_SETTINGS_EXPLAIN'	=> 'Aici puteţi activa şi dezactiva anumite funcţii ale forumului pentru a reduce volumul de procesare cerut. Pe majoritatea serverelor nu este nevoie să dezactivaţi vreo funcţie. Oricum, pe anumite sisteme sau în mediile de gazduire partajate s-ar putea să fie benefică dezactivarea acestor capabilităţi de care nu aveţi neapărat nevoie. De asemenea, puteţi specifica înainte limitele pentru încărcarea sistemului şi sesiunile active ce vor determina ca forumul să devină indisponibil.',

	'CUSTOM_PROFILE_FIELDS'			=> 'Câmpuri profil personalizate',
	'LIMIT_LOAD'					=> 'Limitează încărcarea sistemului',
	'LIMIT_LOAD_EXPLAIN'			=> 'Dacă media primului minut de încărcare a sistemului depăşeşte această valoare atunci forumul va trece automat offline. O valoarea de 1.0 este egală cu ~100% din utilizarea unui procesor. Acestea funcţionează doar pe servere UNIX şi unde această informaţie este accesibilă. Valoarea de aici se resetează la 0 dacă phpBB nu a reuşit să ajungă la limita de încărcare.',
	'LIMIT_SESSIONS'				=> 'Limita sesiunilor',
	'LIMIT_SESSIONS_EXPLAIN'		=> 'Dacă numărul de sesiuni depăşeşte această valoare într-un minut, forumul va trece offline. Specificaţi 0 pentru sesiuni nelimitate.',
	'LOAD_CPF_MEMBERLIST'			=> 'Permite stilurilor să afişeze câmpuri de profil personalizate în lista membrilor',
	'LOAD_CPF_VIEWPROFILE'			=> 'Afişează câmpuri de profil personalizate în profilul utilizatorului',
	'LOAD_CPF_VIEWTOPIC'			=> 'Afişează câmpuri de profil personalizate în paginile subiectului',
	'LOAD_USER_ACTIVITY'			=> 'Arată activitatea utilizatorului',
	'LOAD_USER_ACTIVITY_EXPLAIN'	=> 'Afişează subiectul/forumul activ în profilurile utilizatorilor dar şi în panoul utilizatorului. Este recomandat să dezactivaţi această opţiune pe un forum cu mai mult de un milion de mesaje.',
	'RECOMPILE_STYLES'			=> 'Recompilează componentele vechi ale stilului',
	'RECOMPILE_STYLES_EXPLAIN'	=> 'Caută componentele de stil actualizate în sistemul de fişiere şi le recompilează.',
	'YES_ANON_READ_MARKING'			=> 'Permite marcarea subiectelor pentru vizitatori',
	'YES_ANON_READ_MARKING_EXPLAIN'	=> 'Păstrează informaţiile ca fiind citite/necitite pentru vizitatori. Dacă este dezactivată, mesajele vor fi întotdeauna marcate ca fiind citite pentru vizitatori.',
	'YES_BIRTHDAYS'					=> 'Permite afişarea zilei de naştere',
	'YES_BIRTHDAYS_EXPLAIN'			=> 'Dacă este dezactivată, lista zilelor de naştere nu va mai fi afişată. Pentru a permite aceste setări să aibă efect, funcţionalitatea zilei de naştere trebuie de asemenea să fie activată.',
	'YES_JUMPBOX'					=> 'Permite afişarea jumpbox-ului',
	'YES_MODERATORS'				=> 'Permite afişarea moderatorilor',
	'YES_ONLINE'					=> 'Permite listarea utilizatorilor conectaţi',
	'YES_ONLINE_EXPLAIN'			=> 'Afişează informaţiile utilizatorilor conectaţi în index, forum şi paginile subiectelor.',
	'YES_ONLINE_GUESTS'				=> 'Activează afişarea vizitatorilor conectaţi în secţiunea „Cine este conectat”',
	'YES_ONLINE_GUESTS_EXPLAIN'		=> 'Permite afişarea informaţiilor vizitatorilor conectaţi în „Cine este conectat”.',
	'YES_ONLINE_TRACK'				=> 'Activează afişarea informaţiilor utilizatorilor conectaţi/neconectaţi',
	'YES_ONLINE_TRACK_EXPLAIN'		=> 'Afişează informaţiile online pentru utilizatori în profiluri şi în paginile subiectului.',
	'YES_POST_MARKING'				=> 'Activează subiectele punctate',
	'YES_POST_MARKING_EXPLAIN'		=> 'Indică dacă utilizatorul a răspuns într-un subiect.',
	'YES_READ_MARKING'				=> 'Activează marcarea subiectului server-side',
	'YES_READ_MARKING_EXPLAIN'		=> 'Păstrează informaţiile de stare citite/necitite în baza de date mai degrabă decât într-un cookie.',
	'YES_UNREAD_SEARCH'            => 'Activează căutarea pentru mesajele necitite',
));

// Auth settings
$lang = array_merge($lang, array(
	'ACP_AUTH_SETTINGS_EXPLAIN'	=> 'phpBB suportă plugin-uri de autentificare sau module. Acestea vă permit să determinaţi câţi utilizatori sunt autentificaţi când aceştia intra pe forum. În mod standard sunt furnizate trei plugin-uri; DB, LDAP şi Apache. Nu toate metodele necesită informaţii adiţionale, aşa că specificaţi valorile pentru câmpuri dacă ele sunt relevante pentru metoda selectată.',

	'AUTH_METHOD'				=> 'Selectaţi o metodă de autentificare',

	'APACHE_SETUP_BEFORE_USE'	=> 'Trebuie să configuraţi autentificarea Apache înainte să folosiţi phpBB cu această metodă. Reţineţi că numele de utilizator pe care-l folosiţi pentru autentificarea Apache trebuie să fie acelaşi cu numele propriu de utilizator phpBB. Autentificarea Apache poate fi folosită doar cu mod_php (nu cu o versiune CGI) şi safe_mode dezactivat.',

	'LDAP_DN'						=> 'Baza LDAP <var>dn</var>',
	'LDAP_DN_EXPLAIN'				=> 'Acesta este Distinguished Name, localizează informaţiile utilizatorului, ex. <samp>o=Compania mea,c=US</samp>',
	'LDAP_EMAIL'					=> 'Atributul e-mail-ului LDAP',
	'LDAP_EMAIL_EXPLAIN'			=> 'Specificaţi această valoare atributului de e-mail al numelui de utilizator propriu (dacă există unul) ca să asociaţi automat adresa de e-mail utilizatorilor noi creaţi. Dacă lăsaţi acest câmp necompletat, atunci câmpul de adresa e-mail pentru utilizatorii care s-au autentificat pentru prima oară va fi gol.',
	'LDAP_INCORRECT_USER_PASSWORD'	=> 'Legătura la serverul LDAP folosind utilizatorul/parola specificată a eşuat.',
	'LDAP_NO_EMAIL'					=> 'Atributul e-mail specificat nu există.',
	'LDAP_NO_IDENTITY'				=> 'Nu a putut găsi o identitate de autentificare pentru %s.',
	'LDAP_PASSWORD'					=> 'Parola LDAP',
	'LDAP_PASSWORD_EXPLAIN'			=> 'Lăsaţi necompletat pentru a folosi legătura anonimă, altfel specificaţi parola pentru utilizatorul de mai sus. Necesar pentru serverele Active Directory. <br /><em><strong>AVERTISMENT:</strong> Această parola va fi păstrată ca text plin în baza de date, fiind vizibilă tuturor celor care pot accesa baza de date sau care pot vizualiza această pagina de configurare.</em>',
	'LDAP_PORT'						=> 'Portul serverului LDAP',
	'LDAP_PORT_EXPLAIN'				=> 'Opţional puteţi specifica un port care ar trebui folosit pentru conectarea la serverul LDAP în locul portului standard 389.',
	'LDAP_SERVER'					=> 'Numele serverului LDAP',
	'LDAP_SERVER_EXPLAIN'			=> 'Dacă folosiţi LDAP, acesta este numele gazdei sau adresa IP a serverului LDAP. Alternativ, puteţi specifica o adresă de genul ldap://hostname:port/',
	'LDAP_UID'						=> 'LDAP <var>uid</var>',
	'LDAP_UID_EXPLAIN'				=> 'Aceasta este cheia sub care caută o identitate de autentificare dată, de exemplu <var>uid</var>, <var>sn</var>, etc.',
	'LDAP_USER'						=> 'Utilizatorul LDAP <var>dn</var>',
	'LDAP_USER_EXPLAIN'				=> 'Lăsaţi necompletat pentru a folosi legătura anonimă. Dacă valoarea este specificată, phpBB foloseşte numele completat în încercările de autentificare pentru a găsi utilizatorul corect, de exemplu <samp>uid=NumeUtilizator,ou=UnitateaMeaMyUnit,o=CompaniaMea,c=US</samp>. Necesar pentru serverele Active Directory.',
	'LDAP_USER_FILTER'				=> 'Filtru utilizator LDAP',
	'LDAP_USER_FILTER_EXPLAIN'		=> 'Opţional, mai departe puteţi limita cu filtre suplimentare obiectele căutate. De exemplu <samp>objectClass=posixGroup</samp> ar rezulta în a folosi <samp>(&amp;(uid=$username)(objectClass=posixGroup))</samp>',
));

// Server Settings
$lang = array_merge($lang, array(
	'ACP_SERVER_SETTINGS_EXPLAIN'	=> 'Aici puteţi defini setările legate de server şi domeniu. Asiguraţi-vă că datele introduse sunt corecte, erorile vor duce la e-mail-uri conţinând informaţii incorecte. Când specificaţi numele domeniului nu uitaţi să includeţi http:// sau alt termen de protocol. Modificaţi doar numărul portului dacă ştiţi că serverul foloseşte o altă valoare, portul 80 este corect în majoritatea cazurilor.',

	'ENABLE_GZIP'				=> 'Activează compresia GZip',
	'ENABLE_GZIP_EXPLAIN'		=> 'Conţinutul generat va fi compresat înainte de a fi trimis utilizatorului. Această operaţie poate reduce traficul reţelei, dar de asemenea va mări gradul de folosire al procesorului pe server şi pe partea clientului. Pentru a funcţiona, trebuie ca extensia PHP zlib să fie încărcată.',
	'FORCE_SERVER_VARS'			=> 'Forţează setările URL pentru server',
	'FORCE_SERVER_VARS_EXPLAIN' => 'Dacă alegeţi „Da”,  în locul valorii determinate automat, serverul va fi setat așa cum l-ați definit.',
	'ICONS_PATH'				=> 'Cale stocare iconiţe mesaj',
	'ICONS_PATH_EXPLAIN'		=> 'Calea din directorul rădăcină al phpBB, de exemplu <samp>images/icons</samp>',
	'PATH_SETTINGS'				=> 'Setări cale',
	'RANKS_PATH'				=> 'Cale stocare imagini rang',
	'RANKS_PATH_EXPLAIN'		=> 'Calea din directorul rădăcină al phpBB, de exemplu <samp>images/ranks</samp>',
	'SCRIPT_PATH'				=> 'Cale script',
	'SCRIPT_PATH_EXPLAIN'		=> 'Calea unde phpBB este localizat relativ faţă de numele domeniului, de exemplu <samp>/phpBB3</samp>',
	'SERVER_NAME'				=> 'Nume domeniu',
	'SERVER_NAME_EXPLAIN'		=> 'Numele domeniului de unde rulează acest forum (de exemplu: <samp>www.foo.bar</samp>)',
	'SERVER_PORT'				=> 'Port server',
	'SERVER_PORT_EXPLAIN'		=> 'Portul pe care serverul rulează, este de obicei 80, schimbaţi-l numai dacă este diferit.',
	'SERVER_PROTOCOL'			=> 'Protocol server',
	'SERVER_PROTOCOL_EXPLAIN'	=> 'Acesta este folosit ca server protocol dacă setările sunt forţate. Dacă nu este completat sau neforţat, protocolul este determinat de către setările cookie-ului securizat (<samp>http://</samp> sau <samp>https://</samp>)',
	'SERVER_URL_SETTINGS'		=> 'Setările URL ale serverului',
	'SMILIES_PATH'				=> 'Cale stocare zâmbete',
	'SMILIES_PATH_EXPLAIN'		=> 'Calea din directorul rădăcină al phpBB, de exemplu <samp>images/smilies</samp>',
	'UPLOAD_ICONS_PATH'			=> 'Cale stocare extensii iconiţe grup',
	'UPLOAD_ICONS_PATH_EXPLAIN'	=> 'Calea din directorul rădăcină al phpBB, de exemplu <samp>images/upload_icons</samp>',
));

// Security Settings
$lang = array_merge($lang, array(
	'ACP_SECURITY_SETTINGS_EXPLAIN'		=> 'Aici puteţi defini setările legate de sesiune şi autentificare.',

	'ALL'							=> 'Toate',
	'ALLOW_AUTOLOGIN'				=> 'Permite autentificări persistente', 
	'ALLOW_AUTOLOGIN_EXPLAIN'		=> 'Determină dacă utilizatorii se pot autentifica automat când vizitează forumul.', 
	'AUTOLOGIN_LENGTH'				=> 'Durata de expirare a legăturilor de autentificare persistente (în zile)', 
	'AUTOLOGIN_LENGTH_EXPLAIN'		=> 'Numărul de zile după care legăturile persistente de autentificare sunt eliminate sau 0 pentru a dezactiva.', 
	'BROWSER_VALID'					=> 'Validare browser',
	'BROWSER_VALID_EXPLAIN'			=> 'Permite validarea browser-ului pentru fiecare sesiune, îmbunătăţind securitatea.',
	'CHECK_DNSBL'					=> 'Caută IP împotriva listei negre DNS',
	'CHECK_DNSBL_EXPLAIN'			=> 'Dacă este activată, adresa IP a utilizatorului este verificată în lista serviciilor de înregistrare şi publicare DNSBL: <a href="http://spamcop.net">spamcop.net</a> şi <a href="http://spamhaus.org">spamhaus.org</a>. Această căutare poate dura ceva timp, în funcţie de configurarea serverului. Dacă apar încetiniri sau sunt raportate false potriviri, este recomandat să dezactivaţi această opţiune.',
	'CLASS_B'						=> 'A.B',
	'CLASS_C'						=> 'A.B.C',
	'EMAIL_CHECK_MX'				=> 'Verifică domeniul e-mail pentru validarea înregistrării MX',
	'EMAIL_CHECK_MX_EXPLAIN'		=> 'Dacă este activată, domeniul e-mail furnizat la înregistrare şi schimbările profilului sunt verificate pentru validarea înregistrării MX.',
	'FORCE_PASS_CHANGE'				=> 'Forţează schimbarea parolei',
	'FORCE_PASS_CHANGE_EXPLAIN'		=> 'Obligă utilizatorul să-şi schimbe parola după un număr precizat de zile. Specificaţi valorea 0 pentru a dezactiva această opţiune.',
	'FORM_TIME_MAX'					=> 'Intervalul maxim de timp pentru a trimite formularele',
	'FORM_TIME_MAX_EXPLAIN'			=> 'Timpul pe care utilizatorul îl are la dispoziţie pentru a trimite un formular. Folosiţi -1 pentru dezactivare. Reţineţi că un formular poate deveni invalid dacă sesiunea expiră indiferent de acestă setare.',
	'FORM_SID_GUESTS'				=> 'Leagă formularele de sesiunile vizitatorilor',
	'FORM_SID_GUESTS_EXPLAIN'		=> 'Dacă este activată, formularul obţinut de către vizitatori pe baza token-ului va fi asociat exclusiv sesiunii curente. Această operaţie poate cauza probleme cu anumiţi ISP.',
	'FORWARDED_FOR_VALID'			=> 'Header validat <var>X_FORWARDED_FOR</var>',
	'FORWARDED_FOR_VALID_EXPLAIN'	=> 'Sesiunea va continua doar dacă sunt trimise <var>X_FORWARDED_FOR</var> header-ului egale cu cele trimise prin cererea anterioară. Banurile vor fi de asemenea verificate împotriva IP-urilor în <var>X_FORWARDED_FOR</var>.',
	'IP_VALID'						=> 'Validarea sesiunii IP',
	'IP_VALID_EXPLAIN'				=> 'Determină cât de multă informaţie din adresa IP a utilizatorului este folosită pentru validarea sesiunii: <samp>Toate</samp> compară adresa completă, <samp>A.B.C</samp> primele x.x.x, <samp>A.B</samp> primele x.x, <samp>Niciunul</samp> dezactivează căutarea.',
	'IP_LOGIN_LIMIT_MAX'			=> 'Numărul maxim de încercări de autentificare pe fiecare adresă IP',
	'IP_LOGIN_LIMIT_MAX_EXPLAIN'	=> 'Limita încercărilor de autentificare permisă de la o singură adresă IP înainte ca utilitarul antispam să fie apelat. Introduceți 0 pentru a împiedica utilitarul antispam să fie apelat de către adrese IP.',
	'IP_LOGIN_LIMIT_TIME'			=> 'Timp expirare pentru încercare autentificare adresă IP',
	'IP_LOGIN_LIMIT_TIME_EXPLAIN'	=> 'Încercarea de autentificare expiră după acest interval.',
	'IP_LOGIN_LIMIT_USE_FORWARDED'	=> 'Limită încercări autentificare după header <var>X_FORWARDED_FOR</var>',
	'IP_LOGIN_LIMIT_USE_FORWARDED_EXPLAIN'	=> 'În loc de a limita încercările de autentificare după adresa IP, acestea sunt limitate în funcție de valorile <var>X_FORWARDED_FOR</var>. <br /><em><strong>Atenție:</strong> Activați această setare doar dacă folosiți un server proxy care asociază <var>X_FORWARDED_FOR</var> pentru valori demne de încredere.</em>',
	'MAX_LOGIN_ATTEMPTS'			=> 'Numărul maxim de încercări de autentificare pe fiecare utiliztor',
	'MAX_LOGIN_ATTEMPTS_EXPLAIN'	=> 'Numărul de încercări de autentificare permis pentru un singur cont înainte ca utilitarul antispam să fie apelat. Introduceți 0 pentru a împiedica utilitarul antispam să fie apelat pentru conturi diferite de utilizator.',
	'NO_IP_VALIDATION'				=> 'Niciunul',
	'NO_REF_VALIDATION'				=> 'Niciunul',
	'PASSWORD_TYPE'					=> 'Complexitate parolă',
	'PASSWORD_TYPE_EXPLAIN'			=> 'Determină cât de complexă tebuie să fie o parolă pentru a fi setată sau modificată, opţiunile ulterioare includ şi pe cele anterioare.',
	'PASS_TYPE_ALPHA'				=> 'Trebuie să conţină litere şi numere',
	'PASS_TYPE_ANY'					=> 'Nicio cerinţă',
	'PASS_TYPE_CASE'				=> 'Trebuie să conţină litere mari şi mici',
	'PASS_TYPE_SYMBOL'				=> 'Trebuie să conţină simboluri',
	'REF_HOST'						=> 'Validează doar serverul',
	'REF_PATH'						=> 'Validează şi calea',
	'REFERER_VALID'					=> 'Validează Referer-ul',
	'REFERER_VALID_EXPLAIN'			=> 'Dacă este activată, referer-ul cererilor POST vor fi verificate cu setările căii pentru server/script. Aceasta poate cauza probleme cu forumuri ce folosesc mai multe domenii şi sau autentificări externe.',
	'TPL_ALLOW_PHP'					=> 'Permite php în şabloane',
	'TPL_ALLOW_PHP_EXPLAIN'			=> 'Dacă această opţiune este activată, <code>PHP</code> şi declaraţiile <code>INCLUDEPHP</code>  vor fi recunoscute şi analizate în şabloane.',
));

// Email Settings
$lang = array_merge($lang, array(
	'ACP_EMAIL_SETTINGS_EXPLAIN'	=> 'Această informaţie este folosită când forumul trimite e-mail-uri utilizatorilor proprii. Vă rugăm să vă asiguraţi că adresa de e-mail specificată este validă, orice mesaje ignorate sau nelivrabile vor fi trimise la această adresă. Dacă serverul propriu nu vă asigură un serviciu de e-mail nativ (bazat pe PHP) puteţi trimite mesajele direct folosind SMTP. Această operaţie necesită adresa unui server corespunzător (întrebaţi providerul dacă este necesar). Dacă serverul necesită autentificare (şi numai dacă o face) specificaţi numele de utilizator, parola şi metoda de autentificare.',

	'ADMIN_EMAIL'					=> 'Adresa de e-mail pentru mesaje returnate',
	'ADMIN_EMAIL_EXPLAIN'			=> 'Aceasta va fi folosită ca şi adresa de returnare pentru toate mesajele electronice, adresa e-mail a contactului tehnic. Va fi folosită întotdeauna ca fiind <samp>Calea de returnare</samp> şi <samp>Trimitere</samp> adreselor în e-mail-uri.',
	'BOARD_EMAIL_FORM'				=> 'Utilizatorii trimit e-mail-uri via forum',
	'BOARD_EMAIL_FORM_EXPLAIN'		=> 'În loc să se afişeze adresele de e-mail, utilizatorii pot trimite mesaje electronice via forum.',
	'BOARD_HIDE_EMAILS'				=> 'Ascunde adresele de e-mail',
	'BOARD_HIDE_EMAILS_EXPLAIN'		=> 'Această funcţie păstrează adresele de e-mail complet private.',
	'CONTACT_EMAIL'					=> 'Adresa de e-mail de contact',
	'CONTACT_EMAIL_EXPLAIN'			=> 'Această adresă va fi folosită doar când este necesar un punct de contact, de exemplu spam, erori, etc. Va fi folosită întotdeauna în secţiunile <samp>De la</samp> şi <samp>Răspunde la</samp> din mesajele electronice.',
	'EMAIL_FUNCTION_NAME'			=> 'Nume funcţie e-mail',
	'EMAIL_FUNCTION_NAME_EXPLAIN'	=> 'Funcţia e-mail folosită pentru a trimite mesaje electronice direct prin PHP.',
	'EMAIL_PACKAGE_SIZE'			=> 'Dimensiunea pachetului de e-mail',
	'EMAIL_PACKAGE_SIZE_EXPLAIN'	=> 'Acesta este numărul de e-mail-uri trimise într-un singur pachet. Această setare este aplicată listei interne de mesaje ce trebuie trimise; specificaţi valoarea 0 dacă aveţi probleme cu notificări legate de mesajele electronice nelivrate.',
	'EMAIL_SIG'						=> 'Semnătură e-mail',
	'EMAIL_SIG_EXPLAIN'				=> 'Acest text va fi ataşat tuturor e-mail-urilor trimise de către forum.',
	'ENABLE_EMAIL'					=> 'Activează posibilitatea forumulului să poată trimite e-mailuri',
	'ENABLE_EMAIL_EXPLAIN'			=> 'Dacă această opţiune este setată ca fiind dezactivată, niciun e-mail nu va fi trimis de către forum. <em>Reţineţi că setările de activare ale contului de utilizator şi administrator impun ca această setare să fie activată. Dacă în secţiunea setărilor activării se foloseşte activarea de către „Utilizator” sau „Administrator”, dezactivând această setare nu va mai necesita activarea conturilor noi.</em>',
	'SMTP_AUTH_METHOD'				=> 'Metoda de autentificare pentru SMTP',
	'SMTP_AUTH_METHOD_EXPLAIN'		=> 'Folosită doar dacă un nume de utilizator/parolă este setat, întrebaţi provider-ul dacă nu sunteţi sigur ce metodă să folosiţi.',
	'SMTP_CRAM_MD5'					=> 'CRAM-MD5',
	'SMTP_DIGEST_MD5'				=> 'DIGEST-MD5',
	'SMTP_LOGIN'					=> 'LOGIN',
	'SMTP_PASSWORD'					=> 'Parola SMTP',
	'SMTP_PASSWORD_EXPLAIN'			=> 'Specificaţi o parolă doar dacă serverul SMTP o cere. <br /><em><strong>Atenţie:</strong> această parolă va fi păstrată ca text clar în baza de date, fiind vizibilă tuturor celor care pot accesa baza de date sau care pot vizualiza această pagina de configurare.</em>',
	'SMTP_PLAIN'					=> 'PLAIN',
	'SMTP_POP_BEFORE_SMTP'			=> 'POP-BEFORE-SMTP',
	'SMTP_PORT'						=> 'Portul serverului SMTP',
	'SMTP_PORT_EXPLAIN'				=> 'Schimbaţi portul doar dacă ştiţi că serverul SMTP este pe un port diferit.',
	'SMTP_SERVER'					=> 'Adresa serverului SMTP',
	'SMTP_SETTINGS'					=> 'Setările SMTP',
	'SMTP_USERNAME'					=> 'Nume de utilizator SMTP',
	'SMTP_USERNAME_EXPLAIN'			=> 'Specificaţi un nume de utilizator doar dacă serverul SMTP îl cere.',
	'USE_SMTP'						=> 'Foloseşte serverul SMTP pentru e-mail-uri',
	'USE_SMTP_EXPLAIN'				=> 'Selectaţi „Da” dacă doriţi să trimiteţi e-mail-uri prin intermediul unui server anume în locul funcţiei locale de mesagerie.',
));

// Jabber settings
$lang = array_merge($lang, array(
	'ACP_JABBER_SETTINGS_EXPLAIN'	=> 'Aici puteţi activa şi controla folosirea Jabber pentru mesageria instantă şi notificările forumului. Jabber este un protocol opensource şi disponibil doar a fi folosit de către oricine. Unele servere Jabber includ căi sau transporturi care vă permit contactarea utilizatorilor din alte reţele de net. Nu toate serverele oferă toate transporturile şi modificările în protocoale pot afecta transportul să funcţioneze. Asiguraţi-vă că aţi specificat detaliile unui cont înregistrat deja - phpBB va folosi detaliile pe care le-aţi specificat aici.',

	'JAB_ENABLE'				=> 'Activează Jabber',
	'JAB_ENABLE_EXPLAIN'		=> 'Permite folosirea mesageriei şi notificărilor Jabber.',
	'JAB_GTALK_NOTE'			=> 'Reţineţi că GTalk nu va funcţiona pentru că funcţia <samp>dns_get_record</samp> nu a putut fi găsită. Această funcţie nu este disponibilă în PHP4 şi nu este implementată pe platformele Windows. În mod curent nu funcţionează pe sistemele bazate pe BSD incluzând Mac OS.',
	'JAB_PACKAGE_SIZE'			=> 'Dimensiunea pachetului Jabber',
	'JAB_PACKAGE_SIZE_EXPLAIN'	=> 'Acesta este numărul de mesaje trimise într-un pachet. Dacă este setat la 0, mesajul este trimis imediat şi nu va fi salvat pentru trimiterea ulterioară.',
	'JAB_PASSWORD'				=> 'Parola Jabber',
	'JAB_PASSWORD_EXPLAIN'		=> '<em><strong>Atenţie:</strong> această parolă va fi păstrată ca text clar în baza de date, fiind vizibilă tuturor celor care pot accesa baza de date sau care pot vizualiza această pagina de configurare.</em>',
	'JAB_PORT'					=> 'Portul Jabber',
	'JAB_PORT_EXPLAIN'			=> 'Lăsaţi gol doar dacă ştiţi că nu este portul 5222',
	'JAB_SERVER'				=> 'Serverul Jabber',
	'JAB_SERVER_EXPLAIN'		=> 'Consultaţi %sjabber.org%s pentru o listă a serverelor.',
	'JAB_SETTINGS_CHANGED'		=> 'Setările Jabber au fost schimbate cu succes.',
	'JAB_USE_SSL'				=> 'Foloseşte SSL pentru conectare',
	'JAB_USE_SSL_EXPLAIN'		=> 'Dacă este activată această opţiune, o conexiune sigură încearcă să se stabilească. Portul Jabber va fi modificat cu 5223 dacă portul 5222 este specificat.',
	'JAB_USERNAME'				=> 'Nume de utilizator Jabber sau JID',
	'JAB_USERNAME_EXPLAIN'		=> 'Specificaţi un nume de utilizator înregistrat sau un JID valid. Numele de utilizator nu va fi verificat dacă este valid. Dacă specificaţi doar un nume de utilizator, atunci JID-ul dumneavoastră va fi numele de utilizator şi serverul pe care l-aţi specificat mai sus. Altfel, specificaţi un JID valid, de exemplu utilizator@jabber.org.',
));

?>