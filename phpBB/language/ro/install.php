<?php
/**
*
* install [Română]
*
* @package language
* @version $Id: install.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: install.php 8479 2008-05-19 22:35:00 www.phpbb.ro (shara21jonny) Exp $
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
	'ADMIN_CONFIG'				=> 'Configurare administrator',
	'ADMIN_PASSWORD'			=> 'Parolă administrator',
	'ADMIN_PASSWORD_CONFIRM'	=> 'Confirmaţi parola administratorului',
	'ADMIN_PASSWORD_EXPLAIN'	=> '(Specificaţi o parolă cu lungimea între 6 şi 30 caractere)',
	'ADMIN_TEST'				=> 'Verificaţi setările administratorului',
	'ADMIN_USERNAME'			=> 'Numele de utilizator al administratorului',
	'ADMIN_USERNAME_EXPLAIN'	=> '(Specificaţi un nume de utilizator cu lungimea între 3 şi 20 caractere)',
	'APP_MAGICK'				=> 'Suport Imagemagick [ Fişiere ataşate ]',
	'AUTHOR_NOTES'				=> 'Notiţe autor<br />» %s',
	'AVAILABLE'					=> 'Disponibil',
	'AVAILABLE_CONVERTORS'		=> 'Convertoare disponibile',

	'BEGIN_CONVERT'				=> 'Începe conversia',
	'BLANK_PREFIX_FOUND'		=> 'O scanare a tabelelor a arătat o instalare validă care nu foloseşte prefix pentru tabele.',
	'BOARD_NOT_INSTALLED'			=> 'Nu a fost găsită nicio instalare',
	'BOARD_NOT_INSTALLED_EXPLAIN'	=> 'Cadrul unitar de conversie al phpBB necesită o instalare standard a phpBB3 pentru a funcţiona, vă rugăm să <a href="%s">începeţi cu instalarea phpBB3</a>.',
	'BACKUP_NOTICE'					=> 'Vă rugăm să salvați o copie a forumului înainte de actualizare în caz că apare vreo problemă în timpul procesului de actualizare.',

	'CATEGORY'					=> 'Categorie',
	'CACHE_STORE'				=> 'Tipul cache',
	'CACHE_STORE_EXPLAIN'		=> 'Locaţia fizică unde datele sunt ţinute în cache, sistemul de fişiere este preferat.',
	'CAT_CONVERT'				=> 'Converteşte',
	'CAT_INSTALL'				=> 'Instalează',
	'CAT_OVERVIEW'				=> 'Privire generală',
	'CAT_UPDATE'				=> 'Actualizează',
	'CHANGE'					=> 'Schimbă',
	'CHECK_TABLE_PREFIX'		=> 'Vă rugăm să verificaţi prefixul tabelei şi să încercaţi din nou.',
	'CLEAN_VERIFY'				=> 'Se şterge şi se verifică structura finală',
	'CLEANING_USERNAMES'		=> 'Se curăţă numele de utilizatori',
	'COLLIDING_CLEAN_USERNAME'	=> '<strong>%s</strong> este numele de utilizator clar pentru:',
	'COLLIDING_USERNAMES_FOUND'	=> 'Pe vechiul forum au fost găsite nume de utilizator. Pentru a finaliza conversia ştergeţi sau redenumiţie aceşti utilizatori ca să existe doar un singur utilizator din vechiul forum pentru fiecare nume de utilizator clar.',
	'COLLIDING_USER'			=> '» id utilizator: <strong>%d</strong> nume utilizator: <strong>%s</strong> (%d posts)',
	'CONFIG_CONVERT'			=> 'Se converteşte configuraţia',
	'CONFIG_FILE_UNABLE_WRITE'	=> 'Nu a fost posibilă scrierea fişierului de configurare. Metodele alternative pentru ca acest fişier să fie creat sunt prezentate mai jos',
	'CONFIG_FILE_WRITTEN'		=> 'Fişierul de configurare a fost scris. Acum puteţi continua cu următorul pas al instalării',
	'CONFIG_PHPBB_EMPTY'		=> 'Variabila phpBB3 de configurare pentru "%s" este goală.',
	'CONFIG_RETRY'				=> 'Încercaţi din nou',
	'CONTACT_EMAIL_CONFIRM'		=> 'Confirmaţi e-mailul de contact',
	'CONTINUE_CONVERT'			=> 'Continuaţi conversia',
	'CONTINUE_CONVERT_BODY'		=> 'A fost găsită o încercare anterioară de conversie. Acum puteţi alege între a începe o conversie nouă sau a continua conversia găsită.',
	'CONTINUE_LAST'				=> 'Continuă ultimele declaraţii',
	'CONTINUE_OLD_CONVERSION'	=> 'Continuă conversia începută anterior',
	'CONVERT'					=> 'Converteşte',
	'CONVERT_COMPLETE'			=> 'Conversie finalizată',
	'CONVERT_COMPLETE_EXPLAIN'	=> 'Acum aţi convertit cu succes forumul propriu la phpBB 3.0. Acum vă puteţi autentifica şi <a href="../">accesa forumul propriu</a>. Asiguraţi-vă că setările au fost transferate corect înainte de activarea forumului ştergând directorul de instalarea. Reţineţi că ajutorul folosirii phpBB este disponibil online via <a href="http://www.phpbb.com/support/documentation/3.0/">Documentaţie</a> şi <a href="https://www.phpbb.com/phpBB/viewforum.php?f=46">Forumurile de suport</a>',
	'CONVERT_INTRO'				=> 'Bine aţi venit în Unified Convertor Framework al phpBB',
	'CONVERT_INTRO_BODY'		=> 'De aici, puteţi importa date de la alte sisteme de forumuri (instalate). Lista de mai jos arată toate modulele de conversie ce sunt disponibile. Dacă niciun convertor pentru softul forumului din care doriţi să convertiţi nu este afişat în această listă, vă rugăm să vizitaţi site-ul nostru unde pot fi găsite pentru descărcare module suplimentare de conversie.',
	'CONVERT_NEW_CONVERSION'	=> 'Conversie nouă',
	'CONVERT_NOT_EXIST'			=> 'Convertorul specificat nu există',
	'CONVERT_OPTIONS'			=> 'Opţiuni',
	'CONVERT_SETTINGS_VERIFIED'	=> 'Informaţiile introduse au fost verificate. Pentru a începe procesul de conversie, vă rugăm să apăsaţi butonul de mai jos.',
	'CONV_ERR_FATAL'					=> 'Eroare fatală de conversie',
	
	'CONV_ERROR_ATTACH_FTP_DIR'			=> 'Încărcarea fişierelor ataşate pe FTP este activată pe vechiul forum. Vă rugăm să dezactivaţi opţiunea de încărcare prin FTP şi asiguraţi-vă că este specificat un directorul valid de încărcare, apoi copiaţi toate fişierele ataşate în acest nou director accesibil. Odată ce aţi terminat această operaţie, restartaţi convertorul.',
	'CONV_ERROR_CONFIG_EMPTY'			=> 'Nu există nicio informaţie de configurare disponibilă pentru această conversie.',
	'CONV_ERROR_FORUM_ACCESS'			=> 'Nu s-au putut prelua informaţiile de acces pe forum.',
	'CONV_ERROR_GET_CATEGORIES'			=> 'Nu s-au putut prelua categoriile.',
	'CONV_ERROR_GET_CONFIG'				=> 'Nu s-a putut recupera configurarea forumului.',
	'CONV_ERROR_COULD_NOT_READ'			=> 'Nu s-a putut accesa/citi „%s”.',
	'CONV_ERROR_GROUP_ACCESS'			=> 'Nu s-au putut lua informaţiile de autentificare pentru grup.',
	'CONV_ERROR_INCONSISTENT_GROUPS'	=> 'Inconsistenţă în tabelul grupurilor detectată în add_bots() - trebuie să adăugaţi toate grupurile speciale dacă o efectuaţi manual.',
	'CONV_ERROR_INSERT_BOT'				=> 'Nu s-a putut insera robot în tabelul utilizatorilor.',
	'CONV_ERROR_INSERT_BOTGROUP'		=> 'Nu s-a putut insera robot în tabelul roboţilor.',
	'CONV_ERROR_INSERT_USER_GROUP'		=> 'Nu s-a putut insera utilizator în tabelul user_group.',
	'CONV_ERROR_MESSAGE_PARSER'			=> 'Eroare de mesaj analizată',
	'CONV_ERROR_NO_AVATAR_PATH'			=> 'Notă către dezvoltator: trebuie să specificaţi $convertor[\'avatar_path\'] pentru a folosi %s.',
	'CONV_ERROR_NO_FORUM_PATH'			=> 'Calea relativă către sursa forumului nu a fost specificată.',
	'CONV_ERROR_NO_GALLERY_PATH'		=> 'Notă către dezvoltator: trebuie să specificaţi $convertor[\'avatar_gallery_path\'] pentru a folosi %s.',
	'CONV_ERROR_NO_GROUP'				=> 'Grupul „%1$s” nu a putut fi găsit în %2$s.',
	'CONV_ERROR_NO_RANKS_PATH'			=> 'Notă către dezvoltator: trebuie să specificaţi $convertor[\'ranks_path\'] pentru a folosi %s.',
	'CONV_ERROR_NO_SMILIES_PATH'		=> 'Notă către dezvoltator: trebuie să specificaţi $convertor[\'smilies_path\'] pentru a folosi %s.',
	'CONV_ERROR_NO_UPLOAD_DIR'			=> 'Notă către dezvoltator: trebuie să specificaţi $convertor[\'upload_dir\'] pentru a folosi %s.',
	'CONV_ERROR_PERM_SETTING'			=> 'Nu s-au putut insera/actualiza setările permisiunilor.',
	'CONV_ERROR_PM_COUNT'				=> 'Nu s-a putut selecta directorul pentru numărarea mesajelor private.',
	'CONV_ERROR_REPLACE_CATEGORY'		=> 'Nu s-a putut insera un forum nou înlocuind o categorie veche.',
	'CONV_ERROR_REPLACE_FORUM'			=> 'Nu s-a putut insera un forum nou înlocuind un forum vechi.',
	'CONV_ERROR_USER_ACCESS'			=> 'Nu s-au putut lua informaţiile de autentificare ale utilizatorului.',
	'CONV_ERROR_WRONG_GROUP'			=> 'Grup greşit „%1$s” definit în %2$s.',
	'CONV_OPTIONS_BODY'					=> 'Această pagină colectează datele necesare pentru a accesa forumul sursă. Specificaţi detaliile bazei de date pentru fostul dumneavoastră forum; convertorul nu va schimba nimic în baza de date specificată mai jos. Sursa forumului ar trebui să fie dezactivată pentru a permite o conversie consistentă.',
	'CONV_SAVED_MESSAGES'				=> 'Mesaje salvate',

	'COULD_NOT_COPY'			=> 'Nu s-a putut copia fişierul <strong>%1$s</strong> în <strong>%2$s</strong><br /><br />Vă rugăm să verificaţi dacă directorul propriu zis există şi dacă serverul web poate scrie în acesta',
	'COULD_NOT_FIND_PATH'		=> 'Nu s-a putut găsi calea către fostul forum. Vă rugăm să verificaţi setările şi să încercaţi din nou.<br />» Calea specificată a fost %s',

	'DBMS'						=> 'Tipul bazei de date',
	'DB_CONFIG'					=> 'Configurare bază de date',
	'DB_CONNECTION'				=> 'Counexiune bază de date',
	'DB_ERR_INSERT'				=> 'Eroare în timpul procesării interogării <code>INSERT</code>',
	'DB_ERR_LAST'				=> 'Eroare în timpul procesării <var>query_last</var>',
	'DB_ERR_QUERY_FIRST'		=> 'Eroare în timpul executării <var>query_first</var>',
	'DB_ERR_QUERY_FIRST_TABLE'	=> 'Eroare în timpul executării <var>query_first</var>, %s („%s”)',
	'DB_ERR_SELECT'				=> 'Eroare în timpul procesării interogării <code>SELECT</code>',
	'DB_HOST'					=> 'Nume gazdă pentru serverul bazei de date sau DSN',
	'DB_HOST_EXPLAIN'			=> 'DSN (Numele sursei de date) este relevant doar pentru instalările ODBC. Pentru PostgreSQL, folosiţi localhost pentru a vă conecta la serverul local prin socket-ul domeniului UNIX şi 127.0.0.1 pentru a vă conecta prin TCP. PEntru SQLite, specificați calea completă către fișierul bazei de date proprii.',
	'DB_NAME'					=> 'Numele bazei de date',
	'DB_PASSWORD'				=> 'Parola bazei de date',
	'DB_PORT'					=> 'Portul serverului bazei de date',
	'DB_PORT_EXPLAIN'			=> 'Lăsaţi acest câmp necompletat doar dacă ştiţi că serverul operează pe un port ce nu e standard.',
	'DB_UPDATE_NOT_SUPPORTED'	=> 'Ne pare rău dar acest script nu suportă actualizarea versiunii phpBB de la o versiune mai veche de „%1$s”. Versiunea pe care o aveţi instalată este „%2$s”. Vă rugăm să faceţi actualizarea la o versiune anterioară înainte de a executa acest script. Asistenţa pentru această procedură este acordată în Forumul de suport la phpBB.com.',
	'DB_USERNAME'				=> 'Numele de utilizator al bazei de date',
	'DB_TEST'					=> 'Testează conexiunea',
	'DEFAULT_LANG'				=> 'Limba standard a forumului',
	'DEFAULT_PREFIX_IS'			=> 'Convertorul nu a putut găsi tabelele cu prefixul specificat. Vă rugăm să vă asiguraţi că aţi specificat detaliile corecte pentru forumul pe care îl convertiţi. Prefixul standard al tabelelor pentru %1$s este <strong>%2$s</strong>.',
	'DEV_NO_TEST_FILE'			=> 'Nicio valoare nu a fost specificată în convertor pentru variabila test_file. Dacă sunteţi un utilizator al acestui convertor, nu ar trebui să vedeţi această eroare, vă rugăm să raportaţi acest mesaj autorului convertorului. Dacă sunteţi un autor de convertor, trebuie să specificaţi numele fişierului ce există în forumul sursă pentru a permite ca să fie verificată calea către acesta.',
	'DIRECTORIES_AND_FILES'		=> 'Pregătirea directorului şi a fişierului',
	'DISABLE_KEYS'				=> 'Dezactivare chei',
	'DLL_FIREBIRD'				=> 'Firebird',
	'DLL_FTP'					=> 'Suport FTP la distanţă [ Instalare ]',
	'DLL_GD'					=> 'Suport grafic GD [ Confirmare vizuală ]',
	'DLL_MBSTRING'				=> 'Suport caracter multi-byte',
	'DLL_MSSQL'					=> 'MSSQL Server 2000+',
	'DLL_MSSQL_ODBC'			=> 'MSSQL Server 2000+ via ODBC',
	'DLL_MSSQLNATIVE'         => 'MSSQL Server 2005+ [ Nativ ]',
	'DLL_MYSQL'					=> 'MySQL',
	'DLL_MYSQLI'				=> 'MySQL cu extensie MySQLi',
	'DLL_ORACLE'				=> 'Oracle',
	'DLL_POSTGRES'				=> 'PostgreSQL',
	'DLL_SQLITE'				=> 'SQLite',
	'DLL_XML'					=> 'Suport XML [ Jabber ]',
	'DLL_ZLIB'					=> 'Suport compresie zlib [ gz, .tar.gz, .zip ]',
	'DL_CONFIG'					=> 'Descarcă fişier de configurare',
	'DL_CONFIG_EXPLAIN'			=> 'Puteţi descărca fişierul complet config.php pe calculatorul personal. Atunci va trebui să actulizaţi fişierul manual, înlocuind orice fişier existent din rădăcina forumului phpBB 3.0 cu cel descărcat. Reţineţi să încărcaţi fişierul în formatul ASCII (consultaţi documentaţia aplicaţiei FTP dacă nu sunteţi sigur cum să efectuaţi această operaţie). Când aţi încărcat config.php vă rugăm apăsaţi „Gata” pentru a trece la următoarea etapă.',
	'DL_DOWNLOAD'				=> 'Descarcă',
	'DONE'						=> 'Gata',

	'ENABLE_KEYS'				=> 'Reactivare chei. Această operaţie poate dura ceva timp',

	'FILES_OPTIONAL'			=> 'Fişiere şi directoare opţionale',
	'FILES_OPTIONAL_EXPLAIN'	=> '<strong>Opţional</strong> - Aceste fişiere, directoare sau permisiuni nu sunt necesare. Rutina de instalare va încerca să folosească o varietate de tehnici pentru a le crea dacă ele nu există sau nu se poate scrie în ele. Oricum, prezenţa acestora va accelera instalarea.',
	'FILES_REQUIRED'			=> 'Fişiere şi directoare',
	'FILES_REQUIRED_EXPLAIN'	=> '<strong>Obligatoriu</strong> - Pentru ca phpBB să funcţioneze corect trebuie să poată accesa sau scrie anumite fişiere sau directoare. Dacă vedeţi mesajul „Nu a fost găsit” trebuie să creezi fişierul sau directorul relevant. Dacă vezi „Nescriibil” trebuie să schimbi permisiunile fişierului sau directorului pentru ca phpBB să le poată scrie.',
	'FILLING_TABLE'				=> 'Completează tabelul <strong>%s</strong>',
	'FILLING_TABLES'			=> 'Completează tabelele',
	'FIREBIRD_DBMS_UPDATE_REQUIRED'		=> 'phpBB nu mai suportă Firebird/Interbase mai vechi de versiunea 2.1. Vă rugăm să actualizaţi versiunea Firebird proprie la cel puţin 2.1.0 înainte de a proceda cu această procedură de actualizare.',
	'FINAL_STEP'				=> 'Continuă cu pasul final',
	'FORUM_ADDRESS'				=> 'Adresă forum',
	'FORUM_ADDRESS_EXPLAIN'		=> 'Acesta este URL-ul către vechiul forum, de exemplu <samp>http://www.example.com/phpBB2/</samp>. Dacă o adresă este introdusă aici şi câmpul nu a fost lăsat necompletat, orice instanţă a acestei adresă va fi înlocuită de către noua adresă a forumului în cadrul mesajelor, mesajelor private şi semnăturilor.',
	'FORUM_PATH'				=> 'Cale forum',
	'FORUM_PATH_EXPLAIN'		=> 'Aceasta este calea <strong>relativă</strong> pe disc a forumulului vechi de la <strong>rădăcina instalării acestui forum phpBB3</strong>',
	'FOUND'						=> 'Găsit',
	'FTP_CONFIG'				=> 'Transferă fişierul de configurare prin FTP',
	'FTP_CONFIG_EXPLAIN'		=> 'phpBB a detectat prezenţa modulului FTP pe acest server. Puteţi încerca să instalaţi fişierul config.php prin acest modul dacă doriţi. Va trebui să furnizaţi informaţiile listate mai jos. Reţineţi că numele de utilizator şi parola sunt cele ale serverului propriu ! (întreabaţi providerul hostului pentru detalii dacă nu sunteţi sigur ce sunt acestea)',
	'FTP_PATH'					=> 'Cale FTP',
	'FTP_PATH_EXPLAIN'			=> 'Aceasta este calea din directorul rădăcină către forumul phpBB, e.g. htdocs/phpBB3/',
	'FTP_UPLOAD'				=> 'Încarcă',

	'GPL'						=> 'Licenţa generală publică',
	
	'INITIAL_CONFIG'			=> 'Configuraţia de bază',
	'INITIAL_CONFIG_EXPLAIN'	=> 'Acum că instalarea a determinat faptul că serverul poate rula phpBB, trebuie să adăugaţi câteva informaţii specifice. Dacă nu ştiţi cum să vă conectaţi la baza de date, vă rugăm să contactaţi provider-ul hostului (în primă instanţă) sau să folosiţi forumurile de suport ale phpBB. Când introduceţi datele, asiguraţi-vă că le-aţi verificat înainte să continuaţi.',
	'INSTALL_CONGRATS'			=> 'Felicitări !',
	'INSTALL_CONGRATS_EXPLAIN'	=> '
		Aţi instalat cu succes phpBB %1$s. Vă rugăm să alegeţi una din următoarele opţiuni:</p>
		<h2>Să convertiţi un forum existent în phpBB3</h2>
		<p>Unified Convertor Framework phpBB suportă conversia phpBB 2.0.x şi a altor sisteme de forumuri la phpBB3. Dacă aveţi un forum existent pe care doriţi să-l convertiţi, vă rugăm să <a href="%2$s">continuaţi cu convertorul</a>.</p>
		<h2>Să porniţi cu phpBB3!</h2>
		<p>Accesând butonul de mai jos, veţi ajunge la un formular folosit pentru a trimite informaţii statistice la phpBB direct din Panoul administratorului (PA). Acordaţi-vă puţin timp pentru a examina opţiunile disponibile. Reţineţi că ajutorul este disponibil online în limba engleză pe site-ul <a href="https://phpbb.com">phpBB.com</a> via <a href="https://www.phpbb.com/support/documentation/3.0/">Documentaţie</a>, <a href="%3$s">fişierul CITEŞTE-MĂ</a> şi <a href="https://www.phpbb.com/community/viewforum.php?f=46">forumurile de suport</a> dar și în limba română pe site-ul <a href="http://phpbb.ro">phpBB.ro</a> via <a href="http://phpbb.ro/knowledge/kb_categorie.php?id=8">Documentaţie</a> şi <a href="http://phpbb.ro/viewforum.php?f=55">forumurile de suport</a>.</p><p><strong>Vă rugăm să ştergeţi, să mutaţi sau să redenumiţi directorul install înainte de a accesa forumul propriu. Dacă acest director este în continuare prezent, doar Panoul administratorului (PA) va fi disponibil.</strong>',
		'INSTALL_INTRO'				=> 'Bine aţi venit la instalare',
// TODO: write some more introductions here
	'INSTALL_INTRO_BODY'		=> 'Cu această opţiune puteţi să instalaţi phpBB pe serverul propriu.</p><p>Pentru a începe aveţi nevoie de setările bazei de date. Dacă nu ştiţi setările bazei de date, contactaţi provider-ul hostului şi cereţi informaţii despre aceastea. Nu veţi putea continua fără aceste setări. Aveţi nevoie de:</p>

	<ul>
		<li>Tipul bazei de date - baza de date pe care o veţi folosi.</li>
		<li>Numele serverului de găzduire a bazei de date sau DSN-ul acesteia - adresa către serverul bazei de date.</li>
		<li>Portul serverului bazei de date - portul serverului bazei de date (în majoritatea cazurilor acesta nu este necesar).</li>
		<li>Numele bazei de date - numele bazei de date de pe server.</li>
		<li>Numele de utilizator şi parola bazei de date - Datele de autentificare pentru a accesa baza de date.</li>
	</ul>

	<p><strong>Notă:</strong> dacă instalaţi forumul folosind SQLite va trebui să specificaţi în câmpul DSN calea completă către fişierul bazei de date şi să lăsaţi necompletate câmpurile nume utilizator şi parolă. Din motive de securitate va trebui să vă asiguraţi că fişierul bazei de date nu este stocat într-o locaţie accesibilă de pe web.</p>

	<p>phpBB3 suportă următoarele baze de date:</p>
	<ul>
		<li>MySQL 3.23 sau mai nou (MySQLi suportat deasemenea)</li>
		<li>PostgreSQL 7.3+</li>
		<li>SQLite 2.8.2+</li>
		<li>Firebird 2.1+</li>
		<li>MS SQL Server 2000 sau mai nou (direct sau via ODBC)</li>
		<li>MS SQL Server 2005 sau mai nou (nativ)</li>
		<li>Oracle</li>
	</ul>
	
	<p>Vor fi afişate numai acele baze de date suportate pe serverul propriu.',
	'INSTALL_INTRO_NEXT'		=> 'Pentru a începe instalarea apăsaţi butonul de mai jos.',
	'INSTALL_LOGIN'				=> 'Autentificare',
	'INSTALL_NEXT'				=> 'Următoarea etapă',
	'INSTALL_NEXT_FAIL'			=> 'Anumite teste au eşuat şi trebuie să corectaţi această problemă înainte de a trece la pasul următor. Eşuarea acestora ar putea rezulta printr-o instalare incompletă.',
	'INSTALL_NEXT_PASS'			=> 'Toate testele de bază au fost efectuate cu succes şi puteţi continua cu următoarea etapă a instalării. Dacă aţi schimbat orice permisiuni, module, etc., dacă doriţi puteţi purcede la retestarea acestora.',
	'INSTALL_PANEL'				=> 'Panoul de instalare',
	'INSTALL_SEND_CONFIG'		=> 'Din păcate phpBB nu a putut scrie informaţiile de configurare direct în config.php. Această situaţie poate rezulta din cauză că fişierul nu este accesibil la scriere. Va fi afişat mai jos un număr de opţiuni ajutându-vă la finalizarea instalării fişierului config.php.',
	'INSTALL_START'				=> 'Porneşte instalarea',
	'INSTALL_TEST'				=> 'Testează din nou',
	'INST_ERR'					=> 'Eroare la instalare',
	'INST_ERR_DB_CONNECT'		=> 'Nu s-a putut efectua conexiunea către baza de date, consultaţi mai jos mesajul de eroare',
	'INST_ERR_DB_FORUM_PATH'	=> 'Fişierul bazei de date specificat este în interiorul arborelui de directoare al forumului. Ar trebui să puneţi acest fişier intr-o locaţie web neaccesibilă',
	'INST_ERR_DB_INVALID_PREFIX'=> 'Prefixul introdus este invalid. Trebuie să înceapă cu o literă și să conțină doar litere, cifre și liniuțe de subliniere.',
	'INST_ERR_DB_NO_ERROR'		=> 'Niciun mesaj de eroare',
	'INST_ERR_DB_NO_MYSQLI'		=> 'Versiunea MySQL instalată pe acest server este incompatibilă cu opţiunea „MySQL cu extensie MySQLi” pe care aţi selectat-o. În loc de aceasta, vă rugăm să încercaţi opţiunea „MySQL”.',
	'INST_ERR_DB_NO_SQLITE'		=> 'Versiunea extensiei SQLite pe care aţi instalat-o este prea veche, trebuie să fie actulizată la cel puţin 2.8.2.',
	'INST_ERR_DB_NO_ORACLE'		=> 'Versiunea Oracle instalată pe acest server necesită setarea valorii <var>UTF8</var> în parametrul <var>NLS_CHARACTERSET</var>. Fie actualizaţi instalarea la 9.2+ sau schimbaţi parametrii.',
	'INST_ERR_DB_NO_FIREBIRD'	=> 'Versiunea Firebird instalată pe acest forum este mai veche decât 2.1, vă rugăm să actualizaţi la o versiune mai nouă.',
	'INST_ERR_DB_NO_FIREBIRD_PS'=> 'Baza de date selectată pentru Firebird are o dimensiune a paginii mai mică de 8192, trebuie să fie de cel puţin 8192.',
	'INST_ERR_DB_NO_POSTGRES'	=> 'Baza de date selectată nu a fost creată în codarea <var>UNICODE</var> sau <var>UTF8</var>. Încercaţi să instalaţi forumul cu baza de date în codarea <var>UNICODE</var> sau <var>UTF8</var>',
	'INST_ERR_DB_NO_NAME'		=> 'Niciun nume specificat pentru baza de date',
	'INST_ERR_EMAIL_INVALID'	=> 'Adresa de email pe care aţi specificat-o este invalidă',
	'INST_ERR_EMAIL_MISMATCH'	=> 'Adresele de email pe care le-aţi specificat nu se potrivesc.',
	'INST_ERR_FATAL'			=> 'Eroare fatală de instalare',
	'INST_ERR_FATAL_DB'			=> 'O eroare fatală şi nerecuperabilă a apărut în baza de date. Aceasta poate fi cauzată datorită faptului că utilizatorul specificat nu are drepturile necesare pentru comenzi de tipul <code>CREATE TABLES</code> sau <code>INSERT</code>, etc. Mai multe informaţii s-ar putea să găsiţi mai jos. Vă rugăm să contactaţi provider-ul hostului în primă instanţă sau forumurile de suport phpBB pentru asistenţă.',
	'INST_ERR_FTP_PATH'			=> 'Nu s-a putut schimba calea către directorul specificat, verificaţi calea.',
	'INST_ERR_FTP_LOGIN'		=> 'Nu s-a putut efectua autentificarea pe serverul FTP, verificaţi numele de utilizator şi parola',
	'INST_ERR_MISSING_DATA'		=> 'Trebuie să completaţi toate câmpurile din acest bloc',
	'INST_ERR_NO_DB'			=> 'Nu s-a putut încărca modulul PHP pentru tipul bazei de date selectat',
	'INST_ERR_PASSWORD_MISMATCH'	=> 'Parolele specificate nu se potrivesc.',
	'INST_ERR_PASSWORD_TOO_LONG'	=> 'Parola specificată este prea lungă. Lungimea maximă este de 30 de caractere.',
	'INST_ERR_PASSWORD_TOO_SHORT'	=> 'Parola specificată este prea scurtă. Lungimea minimă este de 6 caractere.',
	'INST_ERR_PREFIX'			=> 'Tabelele cu prefixul specificat există deja, vă rugăm sa alegeţi un alt prefix.',
	'INST_ERR_PREFIX_INVALID'	=> 'Prefixul tabelei specificat este invalid pentru această bază de date. Vă rugăm să încercaţi alt nume, fără a mai folosi caractere precum cratima',
	'INST_ERR_PREFIX_TOO_LONG'	=> 'Prefixul tabelei specificat este prea lung. Lungimea maximă este de %d caractere.',
	'INST_ERR_USER_TOO_LONG'	=> 'Numele de utilizator specificat este prea lung. Lungimea maximă este de 20 de caractere.',
	'INST_ERR_USER_TOO_SHORT'	=> 'Numele de utilizator specificat este prea scurt. Lungimea minimă este de 3 caractere.',
	'INVALID_PRIMARY_KEY'		=> 'Cheie primară invalidă : %s',

	'LONG_SCRIPT_EXECUTION'		=> 'Reţineţi că această operaţiune poate dura o vreme... Vă rugăm să nu opriţi scriptul.',

	// mbstring
	'MBSTRING_CHECK'						=> 'Verificare extensie <samp>mbstring</samp>',
	'MBSTRING_CHECK_EXPLAIN'				=> '<strong>Necesar</strong> - <samp>mbstring</samp> este o extensie a PHP ce oferă funţii şiruri multibyte. Anumite funcţionalităţi ale mbstring nu sunt compatibile cu phpBB şi trebuiesc dezactivate.',
	'MBSTRING_FUNC_OVERLOAD'				=> 'Funcţia overloading',
	'MBSTRING_FUNC_OVERLOAD_EXPLAIN'		=> '<var>mbstring.func_overload</var> trebuie să fie 0 sau 4.',
	'MBSTRING_ENCODING_TRANSLATION'			=> 'Codarea transparentă a caracterelor',
	'MBSTRING_ENCODING_TRANSLATION_EXPLAIN'	=> '<var>mbstring.encoding_translation</var> trebuie să fie 0.',
	'MBSTRING_HTTP_INPUT'					=> 'Conversia caracterelor HTTP specificate',
	'MBSTRING_HTTP_INPUT_EXPLAIN'			=> '<var>mbstring.http_input</var> trebuie să fie <samp>pass</samp>.',
	'MBSTRING_HTTP_OUTPUT'					=> 'Conversia caracterelor HTTP generate',
	'MBSTRING_HTTP_OUTPUT_EXPLAIN'			=> '<var>mbstring.http_output</var> trebuie să fie <samp>pass</samp>.',
	
	'MAKE_FOLDER_WRITABLE'		=> 'Vă rugăm să vă asiguraţi că acest director există şi poate fi scris de către serverul web; apoi încercaţi din nou:<br />»<strong>%s</strong>',
	'MAKE_FOLDERS_WRITABLE'		=> 'Vă rugăm să vă asiguraţi că aceste directoare există şi pot fi scrise de către serverul web; apoi încercaţi din nou:<br />»<strong>%s</strong>',
	'MYSQL_SCHEMA_UPDATE_REQUIRED'	=> 'Schema bazei de date MySQL utlizată este neactualizată. phpBB a detectat o schema pentru MySQL 3.x/4.x iar serverul rulează pe MySQL %2$s.<br /><strong>Înainte de a trece la actualizare trebuie să efectuaţi actualizarea bazei de date.</strong><br /><br />Folosiţi <a href="https://www.phpbb.com/kb/article/doesnt-have-a-default-value-errors/">acest articol ce detaliază procedura de actualizare a schemei MySQL</a> (<a href="http://phpbb.ro/knowledge/kb_show.php?id=94/">articolul în limba română</a>). Dacă întampinaţi probleme, vă rugâm să folosiţi <a href="https://www.phpbb.com/community/viewforum.php?f=46">forumul de suport phpbb.com</a> sau <a href="http://phpbb.ro/viewforum.php?f=32">forumul de suport phpBB România</a>.',

	'NAMING_CONFLICT'			=> 'Conflict: %s şi %s sunt amândouă aliasuri<br /><br />%s',
	'NEXT_STEP'					=> 'Continuaţi cu pasul următor',
	'NOT_FOUND'					=> 'Nu poate fi găsit',
	'NOT_UNDERSTAND'			=> 'Nu poate înţelege %s #%d, tabelul %s ("%s")',
	'NO_CONVERTORS'				=> 'Niciun convertor nu este disponibil pentru a fi folosit',
	'NO_CONVERT_SPECIFIED'		=> 'Niciun convertor specificat',
	'NO_LOCATION'				=> 'Nu se poate determina locaţia. Dacă ştiţi că Imagemagick este instalat, puteţi specifica mai târziu locaţia în cadrul Panoului de administrare',
	'NO_TABLES_FOUND'			=> 'Tabelele nu au fost găsite.',

	'OVERVIEW_BODY'					=> 'Bine aţi venit la phpBB3!<br /><br />phpBB® este cea mai folosită soluţie pe scară largă de tip forum bazată pe open source. phpBB3 este ultima versiune dintr-o serie care a început în anul 2000. Ca şi predecesorii săi, phpBB3 este îmbunătăţit, uşor de utilizat şi suportat de către echipa phpBB. De asemenea, phpBB3 îmbunătăţeşte ceea ce a facut phpBB2 atât de popular şi adaugă facilităţi cerute ce nu au fost prezente în versiunile anterioare. Noi sperăm că va depăşi aşteptările dumneavoastră.<br /><br />Acest sistem de instalare vă va ghida prin procesul de instalare al phpBB3, actualizându-l la ultima versiune dintr-o versiune anterioară, putând să-l convertiţi dintr-un alt sistem de forumuri (incluzând phpBB2). Pentru mai multe informaţii, vă încurajăm să consultaţi <a href="../docs/INSTALL.html">ghidul de instalare</a>.<br /><br />Pentru a citi licenţa phpBB3 sau pentru a afla cum se obţine suportul, vă rugăm să selectaţi opţiunile potrivite din meniul de pe margine. Pentru a continua, vă rugăm să selectaţi secţiunea TAB de mai sus.',

	'PCRE_UTF_SUPPORT'				=> 'Suport PCRE UTF-8',
	'PCRE_UTF_SUPPORT_EXPLAIN'		=> 'phpBB <strong>nu</strong> va rula dacă instalarea PHP nu este compilată cu suport UTF-8 în extensia PCRE',
	'PHP_GETIMAGESIZE_SUPPORT'			=> 'Funcţia PHP getimagesize() este disponibilă',
	'PHP_GETIMAGESIZE_SUPPORT_EXPLAIN'	=> '<strong>Necesar</strong> - Pentru ca phpBB să funcţioneze corect, funcţia getimagesize trebuie să fie disponibilă.',
	'PHP_OPTIONAL_MODULE'			=> 'Module opţionale',
	'PHP_OPTIONAL_MODULE_EXPLAIN'	=> '<strong>Opţional</strong> - Aceste module sau aplicaţii sunt opţionale. Totuşi, dacă ele sunt disponibile, acestea vor activa funcţionalităţi suplimentare.',
	'PHP_SUPPORTED_DB'				=> 'Baze de date suportate',
	'PHP_SUPPORTED_DB_EXPLAIN'		=> '<strong>Cerinţe</strong> - Trebuie să aveţi suport pentru cel puţin o bază de date compatibilă cu PHP. Dacă niciun modul al bazei de date nu este afişat ca fiind disponibil, ar trebui să contactaţi provider-ul hostului sau să revizuiţi pentru ajutor documentaţia relevantă de instalare a PHP.',
	'PHP_REGISTER_GLOBALS'			=> 'Setarea PHP <var>register_globals</var> este dezactivată',
	'PHP_REGISTER_GLOBALS_EXPLAIN'	=> 'phpBB va rula în continuare dacă această setare este activată, dar dacă este posibil, este recomandat ca register_globals să fie dezactivată din motive de securitate.',
	'PHP_SAFE_MODE'					=> 'Mod protejat',
	'PHP_SETTINGS'					=> 'Versiune PHP şi setări',
	'PHP_SETTINGS_EXPLAIN'			=> '<strong>Necesar</strong> - Trebuie să aveţi cel puţin versiunea 4.3.3 a PHP pentru a putea instala phpBB. Dacă <var>safe mode</var> este afişat mai jos, instalarea PHP va rula în acest mod. Acest fapt va impune limitări administrării la distanţă şi altor funcţionalităţi similare.',
	'PHP_URL_FOPEN_SUPPORT'			=> 'Setarea PHP <var>allow_url_fopen</var> este disponibilă',
	'PHP_URL_FOPEN_SUPPORT_EXPLAIN'	=> '<strong>Opţional</strong> - Această setare este opţională, oricum anumite funcţii phpBB ca avatarele la distanţă nu vor funcţiona corect fără ea.',
	'PHP_VERSION_REQD'				=> 'Versiune PHP >= 4.3.3',
	'POST_ID'						=> 'Identificator mesaj',
	'PREFIX_FOUND'					=> 'O scanare a tabelelor a arătat că instalarea este validă folosind <strong>%s</strong> ca şi prefix pentru tabelă.',
	'PREPROCESS_STEP'				=> 'Execută preprocesarea funcţiilor/interogărilor',
	'PRE_CONVERT_COMPLETE'			=> 'Toate etapele preconversiei au fost finalizate cu succes. Acuma puteţi începe procesul propriu zis de conversie. Reţineţi că va trebui să adjustaţi manual mai multe lucruri. După conversie, în special verificaţi permisiunile atribuite, reconstruiţi index-ul de căutare care nu este convertit şi deasemenea, asiguraţi-vă că fişierele s-au copiat corect, de exemplu imaginile asociate şi zâmbetele.',
	'PROCESS_LAST'					=> 'Procesează ultimile instrucţiuni',

	'REFRESH_PAGE'				=> 'Reîmprospătează pagina pentru a continua conversia',
	'REFRESH_PAGE_EXPLAIN'		=> 'Dacă selectaţi Da, convertorul va reîmprospăta pagina pentru a continua conversia după fiecare pas finalizat. Dacă aceasta este prima conversie, pentru testarea efectelor şi pentru a determina orice eroare în viitor, vă sugerăm să alegeţi Nu.',
	'REQUIREMENTS_TITLE'		=> 'Compatibilitatea instalării',
	'REQUIREMENTS_EXPLAIN'		=> 'Înainte de a continua cu instalarea completă, phpBB va face unele teste cu configuraţia serverului şi a fişierelor pentru a se asigura că sunteţi capabil să instalaţi şi să rulaţi phpBB. Vă rugăm să vă asiguraţi că citiţi complet aceste rezultate şi să nu continuaţi până când toate testele necesare nu sunt trecute. Dacă doriţi să folosiţi orice funţionalitate dependentă de testările opţionale, va trebui să vă asiguraţi că aceste teste sunt de asemenea trecute.',
	'RETRY_WRITE'				=> 'Reîncearcă scrierea fişierului de configurare',
	'RETRY_WRITE_EXPLAIN'		=> 'Dacă doriţi, puteţi schimba permisiunile pe fişierul config.php pentru a permite phpBB-ului să îl scrie. Dacă doriţi acest lucru trebuie să accesaţi butonul Reîncearcă de mai jos. Nu uitaţi să schimbaţi la loc permisiunile fişierului config.php după ce phpBB a terminat instalarea.',

	'SCRIPT_PATH'				=> 'Cale script',
	'SCRIPT_PATH_EXPLAIN'		=> 'Calea unde phpBB este localizat relativ la numele domeniului, de exemplu <samp>/phpBB3</samp>',
	'SELECT_LANG'				=> 'Selectare limbă',
	'SERVER_CONFIG'				=> 'Configuraţie server',
	'SEARCH_INDEX_UNCONVERTED'	=> 'Indexul de căutare nu a fost convertit',
	'SEARCH_INDEX_UNCONVERTED_EXPLAIN'	=> 'Vechiul index de căutare nu a fost convertit. Căutările vor genera întotdeauna rezultate goale. Pentru a crea un nou index de căutare accesaţi Panoul de control al administratorului, selectaţi Întreţinere şi alegeţi Indexul căutării din submeniu.',
	'SOFTWARE'					=> 'Softul forumului',
	'SPECIFY_OPTIONS'			=> 'Specificaţi opţiunile de conversie',
	'STAGE_ADMINISTRATOR'		=> 'Detalii administrator',
	'STAGE_ADVANCED'			=> 'Setări avansate',
	'STAGE_ADVANCED_EXPLAIN'	=> 'Setările din această pagină sunt necesare numai pentru a fi definite dacă ştiţi că este necesar altceva decât valorile iniţiale. Dacă sunteţi nesigur, continuaţi cu pasul următor, acestea pot fi modificate ulterior din Panoul administratorului.',
	'STAGE_CONFIG_FILE'			=> 'Fişierul de configurare',
	'STAGE_CREATE_TABLE'		=> 'Crează tabelele bazei de date',
	'STAGE_CREATE_TABLE_EXPLAIN'	=> 'Baza de date folosită de către phpBB 3.0 a fost creată şi populată cu datele iniţiale. Continuaţi cu pasul următor pentru a finaliza instalarea phpBB.',
	'STAGE_DATABASE'			=> 'Setări bază de date',
	'STAGE_FINAL'				=> 'Ultima etapă',
	'STAGE_INTRO'				=> 'Introducere',
	'STAGE_IN_PROGRESS'			=> 'Conversie în progres',
	'STAGE_REQUIREMENTS'		=> 'Cerinţe',
	'STAGE_SETTINGS'			=> 'Setări',
	'STARTING_CONVERT'			=> 'Începe procesul de conversie',
	'STEP_PERCENT_COMPLETED'	=> 'Pasul <strong>%d</strong> din <strong>%d</strong>',
	'SUB_INTRO'					=> 'Introducere',
	'SUB_LICENSE'				=> 'Licenţă',
	'SUB_SUPPORT'				=> 'Suport',
	'SUCCESSFUL_CONNECT'		=> 'Conexiune efectuată cu succes',
	'SUPPORT_BODY'	 => 'Suportul total va fi oferit gratuit pentru versiunea curenta stabilă a phpBB3. Acesta include:</p><ul><li>instalare</li><li>configurare</li><li>întrebări tehnice</li><li>probleme legate de erori posibile în software</li><li>actualizări de la versiunile de tip candidat (RC) la ultima versiune stabilă</li><li>conversia de la phpBB 2.0.x la phpBB3</li><li>conversia de la alt software pentru forum la phpBB3 (vă rugăm să consultaţi <a href="https://www.phpbb.com/community/viewforum.php?f=65">Forumul de convertoare</a>)</li></ul><p>Noi încurajăm utilizatorii care încă rulează versiunile beta ale phpBB3 să-şi înlocuiască instalările o copie proaspătă a ultimei versiuni.</p><h2>MODificări / Stiluri</h2><p>Pentru problemele legate de MODificări, vă rugăm să scrieţi în <a href="https://www.phpbb.com/community/viewforum.php?f=81">Forumul de MODificări</a>.<br />Pentru problemele legate de stiluri, şabloane şi seturi de imagini, vă rugăm să scrieţi în <a href="http://www.phpbb.com/community/viewforum.php?f=80">Forumul de stiluri</a>.<br /><br />Dacă întrebarea este legată de un pachet anume, vă rugăm să scrieţi în direct în subiectul dedicat pachetului.</p><h2>Cum se obţine suportul</h2><p><a href="http://www.phpbb.com/community/viewtopic.php?f=14&amp;t=571070">Pachetul de bun venit al phpBB</a><br /><a href="http://www.phpbb.com/support/">Secţiune suport</a><br /><a href="http://www.phpbb.com/support/documentation/3.0/quickstart/">Ghid scurt de început</a><br /><br />. Pentru a vă asigura că sunteţi la zi cu ultimele ştiri şi versiuni, <a href="http://www.phpbb.com/support/">înscrieţi-vă la lista noastră de email</a>!<br /><br />',
	'SYNC_FORUMS'				=> 'Începe sincronizarea forumurilor',
	'SYNC_POST_COUNT'			=> 'Sincronizează numărarea mesajelor',
	'SYNC_POST_COUNT_ID'		=> 'Sincronizează numărarea mesajelor de la <var>entry</var> %1$s la %2$s.',
	'SYNC_TOPICS'				=> 'Începe sincronizarea subiectelor',
	'SYNC_TOPIC_ID'				=> 'Sincronizează subiectele de la <var>topic_id</var> $1%s la $2%s',

	'TABLES_MISSING'			=> 'Nu s-au putut găsi aceste tabele<br />» <strong>%s</strong>.',
	'TABLE_PREFIX'				=> 'Prefixul pentru tabele în baza de date',
	'TABLE_PREFIX_EXPLAIN'		=> 'Prefixul trebuie să înceapă cu o literă și să conțină doar litere, cifre și liniuțe de subliniere.',
	'TABLE_PREFIX_SAME'			=> 'Prefixul tabelelor trebuie să fie cel folosit de către softul din care faceţi conversia.<br />» Prefixul tabelelor specificat a fost %s.',
	'TESTS_PASSED'				=> 'Testele au fost trecute',
	'TESTS_FAILED'				=> 'Testele au eşuat',

	'UNABLE_WRITE_LOCK'			=> 'Nu s-a putut scrie fişierul de închidere',
	'UNAVAILABLE'				=> 'Nu este disponibil',
	'UNWRITABLE'				=> 'Nu poate fi scris',
	'UPDATE_TOPICS_POSTED'		=> 'Generează informaţiile subiectelor publicate',
	'UPDATE_TOPICS_POSTED_ERR'	=> 'O eroare a apărut in timp ce se generau informaţiile subiectelor publicate. Poţi reîncerca efectuarea acestui pas din Panoul administratorului după ce procesul de conversie este finalizat.',
	'VERIFY_OPTIONS'			=> 'Verificarea opţiunilor de conversie',
	'VERSION'					=> 'Versiune',

	'WELCOME_INSTALL'			=> 'Bine aţi venit la instalarea phpBB3',
	'WRITABLE'					=> 'Poate fi scris',
));

// Updater
$lang = array_merge($lang, array(
	'ALL_FILES_UP_TO_DATE'		=> 'Toate fişiere sunt actualizate cu ultima versiune a phpBB. Acum trebuie <a href="../ucp.php?mode=login">să vă autentificaţi pe forum</a> şi să verificaţi dacă totul decurge normal. Nu uitaţi să ştergeţi, să redenumiţi sau să mutaţi directorul de instalare! Vă rugăm să ne trimiteţi informaţii actualizate despre serverul propriu şi configuraţiile forumului folosind modulul <a href="../ucp.php?mode=login&amp;redirect=adm/index.php%3Fi=send_statistics%26mode=send_statistics">Trimite statistici</a> din cadrul Panoului administratorului.',
	'ARCHIVE_FILE'				=> 'Sursa fişierelor din arhivă',

	'BACK'		=> 'Înapoi',
	'BINARY_FILE'		=> 'Fişier binar',
	'BOT'				=> 'Păianjen/Robot',

	'CHANGE_CLEAN_NAMES'			=> 'Metoda folosită pentru a fi siguri că un nume de utilizator nu este folosit de mai mulţi utilizatori a fost schimbată. Sunt câţiva utilizatori care au acelaşi nume când se face comparaţia cu noua metodă. Trebuie să ştergeţi sau să redenumiţi aceşti utilizatori pentru a fi sigura că fiecare nume este folosit de către un singur utilizator înainte de a putea continua.',
	'CHECK_FILES'					=> 'Verifică fişiere',
	'CHECK_FILES_AGAIN'				=> 'Verifică din nou fişiere',
	'CHECK_FILES_EXPLAIN'			=> 'La pasul următor toate fişierele vor fi verificate cu fişierele actualizate - această operaţie poate dura ceva timp dacă este prima verificare a fişierelor.',
	'CHECK_FILES_UP_TO_DATE'		=> 'Potrivit bazei de date versiunea proprie este actualizată la zi. Poate doriţi să continuaţi cu verificarea fişierelor pentru a vă asigura că toate fişierele sunt cu adevărat actualizate la zi cu ultima versiune a phpBB.',
	'CHECK_UPDATE_DATABASE'			=> 'Continuă procesul de actualizare',
	'COLLECTED_INFORMATION'			=> 'Informaţie fişier',
	'COLLECTED_INFORMATION_EXPLAIN'	=> 'Lista de mai jos arată informaţii despre fişierele care trebuie actualizate. Vă rugăm să citiţi informaţiile din faţa fiecărui bloc de stare pentru a vedea ce înseamnă acestea şi ce puteţi face pentru a efectua o actualizare reuşită.',
	'COLLECTING_FILE_DIFFS'			=> 'Colectează diferenţele dintre fişiere',
	'COMPLETE_LOGIN_TO_BOARD'		=> 'Acum trebuie să vă <a href="../ucp.php?mode=login">autentificaţi pe forumul propriu</a> şi să verificaţi dacă totul funcţionează normal. Nu uitaţi să ştergeţi, redenumiţi sau să mutaţi directorul de instalare!',
	'CONTINUE_UPDATE_NOW'			=> 'Continuă acum procesul de actualizare',
	'CONTINUE_UPDATE'				=> 'Continuă acum actualizarea',	
	'CURRENT_FILE'					=> 'Începutul conflictului în fişierul original înainte de actualizare',
	'CURRENT_VERSION'				=> 'Versiunea curentă',

	'DATABASE_TYPE'						=> 'Tipul bazei de date',
	'DATABASE_UPDATE_INFO_OLD'			=> 'Fişierul de actualizare a bazei de date din directorul de instalare nu este actualizat. Vă rugăm să vă asiguraţi că aţi încărcat versiunea corectă a fişerului.',
	'DELETE_USER_REMOVE'				=> 'Şterge utilizatorul şi elimină mesajele',
	'DELETE_USER_RETAIN'				=> 'Şterge utilizatorul dar păstrează mesajele',
	'DESTINATION'						=> 'Fişierul destinaţie',
	'DIFF_INLINE'						=> 'În linie',
	'DIFF_RAW'							=> 'Diferenţe neprelucrate unite',
	'DIFF_SEP_EXPLAIN'					=> 'Secvenţa de cod folosit în fişierul nou/actualizat',
	'DIFF_SIDE_BY_SIDE'					=> 'Unul lângă altul',
	'DIFF_UNIFIED'						=> 'Diferenţe unite',
	'DO_NOT_UPDATE'						=> 'Nu actualiza acest fişier',
	'DONE'								=> 'Gata',
	'DOWNLOAD'							=> 'Descarcă',
	'DOWNLOAD_AS'						=> 'Descarcă sub un nume nou',
	'DOWNLOAD_UPDATE_METHOD_BUTTON'      => 'Descarcă arhiva fişierelor modificate (recomandat)',
	'DOWNLOAD_CONFLICTS'				=> 'Descărcaţi acest fişier în care este evidenţiat codul în conflict',
	'DOWNLOAD_CONFLICTS_EXPLAIN'		=> 'Caută &lt;&lt;&lt; pentru a identifica eventuale conflicte',
	'DOWNLOAD_UPDATE_METHOD'			=> 'Descarcă arhiva cu fişierele modificate',
	'DOWNLOAD_UPDATE_METHOD_EXPLAIN'	=> 'Odată descărcată ar trebui să deschideți arhiva. Veţi găsi fişierele modificate pe care trebuie să le încărcaţi în rădăcina directorului phpBB. Vă rugăm să încărcaţi fişierele în locaţia lor respectivă. După ce aţi încărcat toate fişierele, vă rugăm să verificaţi din nou fişierele cu celălălt buton de mai jos.',

	'ERROR'		=> 'Eroare',
	'EDIT_USERNAME'	=> 'Modifică nume de utilizator',

	'FILE_ALREADY_UP_TO_DATE'		=> 'Fişierul este deja actualizat la zi',
	'FILE_DIFF_NOT_ALLOWED'			=> 'Fişierul nu poate fi verificat pentru stabilirea diferenţelor',
	'FILE_USED'						=> 'Informaţii folosite din',			// Single file
	'FILES_CONFLICT'				=> 'Fişiere de conflict',
	'FILES_CONFLICT_EXPLAIN'		=> 'Următoarele fişiere sunt modificate şi nu reprezintă fişierele originale din versiunea veche. phpBB a stabilit că aceste fişiere crează conflicte chiar şi dacă s-a încercat unirea codului nou cu cel vechi. Vă rugăm să investigaţi confictele şi să încercaţi să le rezolvaţi manual sau continuaţi să le actualizaţi folosind metoda preferată de unire. Dacă rezolvaţi conflictele manual, verificaţi din nou fişierele după ce le-aţi modificat. De asemenea, puteţi să alegeţi metoda preferată de unire pentru fiecare fişier. Primul va rezulta într-un fişier unde liniile de conflict din versiunea veche a fişierului vor fi pierdute, cealaltă va rezulta în pierderea schimbărilor din fişierul nou.',
	'FILES_MODIFIED'				=> 'Fişiere modificate',
	'FILES_MODIFIED_EXPLAIN'		=> 'Următoarele fişiere sunt modificate şi nu reprezintă fişierele originale din versiunea veche. Fişierul actualizat va fi rezultatul unirii între modificările proprii şi noul fişier.',
	'FILES_NEW'						=> 'Fişiere noi',
	'FILES_NEW_EXPLAIN'				=> 'Următoarele fişiere nu există în instalare în momentul de faţă. Aceste fişiere vor fi adăugate la instalare',
	'FILES_NEW_CONFLICT'			=> 'Fişiere de conflict noi',
	'FILES_NEW_CONFLICT_EXPLAIN'	=> 'Următoarele fişiere sunt noi în ultima versiune dar s-a determinat că există deja un fişier cu acelaşi nume în aceeaşi poziţie. Acest fişier va fi suprascris cu fişierul  nou.',
	'FILES_NOT_MODIFIED'			=> 'Fişiere nemodificate',
	'FILES_NOT_MODIFIED_EXPLAIN'	=> 'Următoarele fişiere nu sunt modificate şi reprezintă fişierele originale ale phpBB din versiunea pe care vreţi să o actualizaţi.',
	'FILES_UP_TO_DATE'				=> 'Fişiere deja actualizate',
	'FILES_UP_TO_DATE_EXPLAIN'		=> 'Următoarele fişiere sunt deja actualizate la zi şi nu mai necesită actualizarea.',
	'FTP_SETTINGS'					=> 'Setări FTP',
	'FTP_UPDATE_METHOD'				=> 'Încărcare FTP',

	'INCOMPATIBLE_UPDATE_FILES'		=> 'Fişierele actualizate găsite sunt incompatibile cu versiunea instalată. Versiunea instalată este %1$s şi fişierul actualizat este pentru actualizarea phpBB %2$s la %3$s.',
	'INCOMPLETE_UPDATE_FILES'		=> 'Fişierele actualizate sunt incomplete',
	'INLINE_UPDATE_SUCCESSFUL'		=> 'Actualizarea bazei de date a fost efectuată cu succes. Acum trebuie să continuaţi procesul de actualizare.',

	'KEEP_OLD_NAME'		=> 'Păstrează numele de utilizator',

	'LATEST_VERSION'		=> 'Ultima versiune',
	'LINE'					=> 'Linie',
	'LINE_ADDED'			=> 'Adăugat',
	'LINE_MODIFIED'			=> 'Modificat',
	'LINE_REMOVED'			=> 'Eliminat',
	'LINE_UNMODIFIED'		=> 'Nemodificat',
	'LOGIN_UPDATE_EXPLAIN'	=> 'Pentru a actualiza instalarea trebuie mai întâi să vă autentificaţi.',

	'MAPPING_FILE_STRUCTURE'	=> 'Pentru a uşura încărcarea, aici sunt locaţiile fişierelor care mapează instalarea phpBB.',
	
	'MERGE_MODIFICATIONS_OPTION'	=> 'Uneşte modificările',	
	
	'MERGE_NO_MERGE_NEW_OPTION'	=> 'Nu uni - foloseşte fişierul nou',
	'MERGE_NO_MERGE_MOD_OPTION'	=> 'Nu uni - foloseşte fişierul curent instalat ',
	'MERGE_MOD_FILE_OPTION'		=> 'Uneşte diferenţele (Elimină noul cod phpBB din cadrul blocului de conflict)',
	'MERGE_NEW_FILE_OPTION'		=> 'Uneşte diferenţele (Elimină codul modificat din cadrul blocului de conflict)',
	'MERGE_SELECT_ERROR'		=> 'Modurile de unire a fişierelor de conflict nu sunt corect selectate.',
	'MERGING_FILES'				=> 'Unire diferenţe',
	'MERGING_FILES_EXPLAIN'		=> 'Acum se colectează modificările fişierelor finale.<br /><br />Vă rugăm să aşteptaţi până când phpBB a completat toate operaţiunile efectuate pe fişierele schimbate.',

	'NEW_FILE'						=> 'Sfârşitul conflictului',
		
	'NEW_USERNAME'					=> 'Nume de utilizator nou',
	'NO_AUTH_UPDATE'				=> 'Nu sunteţi autorizat să efectuaţi actualizarea',
	'NO_ERRORS'						=> 'Nicio eroare',
	'NO_UPDATE_FILES'				=> 'Următoarele fişiere nu au fost actualizate',
	'NO_UPDATE_FILES_EXPLAIN'		=> 'Următoarele fişiere sunt noi sau modificate dar directorul în care ele sunt localizate nu a putut fi găsit în instalare. Dacă această listă conţine fişierele altor directoare decât language/ sau styles/ atunci s-ar putea ca vă fi modificat structura directorului iar actualizarea să fie incompletă.',
	'NO_UPDATE_FILES_OUTDATED'		=> 'Nu a fost găsit niciun director valid pentru actualizare, vă rugăm să vă asiguraţi că aţi încărcat fişierele relevante.<br /><br />Instalarea pare să <strong>nu</strong> fie actualizată la zi. Actualizările sunt disponibile penntru versiunea phpBB proprie %1$s, vă rugăm să vizitaţi <a href="https://www.phpbb.com/downloads.php" rel="external">https://www.phpbb.com/downloads.php</a> pentru a obţine pachetul corect în vederea actualizării de la versiunea %2$s la versiunea %3$s.',
	'NO_UPDATE_FILES_UP_TO_DATE'	=> 'Versiunea proprie este actualizată la zi. Nu este nevoie să rulaţi actualizarea. Dacă doriţi să faceţi o verificare de integritate a fişierelor proprii, asiguraţi-vă că aţi încărcat corect fişierele de actualizare.',
	'NO_UPDATE_INFO'				=> 'Informaţiile din fişierul de actualizare nu au putut fi găsite.',
	'NO_UPDATES_REQUIRED'			=> 'Nu este necesară nicio actualizare',
	'NO_VISIBLE_CHANGES'			=> 'Nicio schimbare vizibilă',
	'NOTICE'						=> 'Reţineţi',
	'NUM_CONFLICTS'					=> 'Numărul conflictelor',
	'NUMBER_OF_FILES_COLLECTED'		=> 'Am verificat %1$d din cele %2$d fişiere.<br />Vă rugăm să aşteptaţi până când toate fişierele vor fi verificate.',
	
	'OLD_UPDATE_FILES'		=> 'Fişierele de actualizare nu sunt actualizate la zi. Fişierele de actualizare găsite sunt pentru actualizarea phpBB %1$s la phpBB %2$s dar ultima versiune a phpBB este %3$s.',

	'PACKAGE_UPDATES_TO'				=> 'Pachetul curent actualizează la versiunea',
	'PERFORM_DATABASE_UPDATE'			=> 'Efectuează actualizarea bazei de date',
	'PERFORM_DATABASE_UPDATE_EXPLAIN'	=> 'Mai jos veţi găsi un buton către scriptul de actualizare a bazei de date. Actualizarea bazei de date poate să dureze ceva vreme, aşa că vă rugăm să nu opriţi procesul dacă pare să se blocheze. După ce actualizarea bazei de date s-a finalizat, urmaţi instrucţiunile pentru a continua procesul de actualizare.',
	'PREVIOUS_VERSION'					=> 'Versiunea anterioară',
	'PROGRESS'							=> 'Progres',

	'RESULT'					=> 'Rezultat',
	'RUN_DATABASE_SCRIPT'		=> 'Actualizează acum baza de date',

	'SELECT_DIFF_MODE'			=> 'Selectaţi modul pentru diferenţe',
	'SELECT_DOWNLOAD_FORMAT'	=> 'Selectaţi formatul de descărcare al arhivei',
	'SELECT_FTP_SETTINGS'		=> 'Selectaţi setările FTP',
	'SHOW_DIFF_CONFLICT'		=> 'Arată diferenţe/conflicte',
	'SHOW_DIFF_FINAL'			=> 'Arată fişierul rezultat',
	'SHOW_DIFF_MODIFIED'		=> 'Arată diferenţele unite',
	'SHOW_DIFF_NEW'				=> 'Arată conţinutul fişierului',
	'SHOW_DIFF_NEW_CONFLICT'	=> 'Arată diferenţe',
	'SHOW_DIFF_NOT_MODIFIED'	=> 'Arată diferenţe',
	'SOME_QUERIES_FAILED'		=> 'Câteva interogări au eşuat, declaraţiile şi erorile sunt afişate mai jos',
	'SQL'						=> 'SQL',
	'SQL_FAILURE_EXPLAIN'		=> 'Probabil nu este cazul să vă îngrijoraţi, actualizarea va continua. Ar trebui ca această eroare să se termine, puteţi căuta ajutor pe forumurile noastre de suport. Consultaţi secţiunea <a href="../docs/README.html">Citeşte</a> pentru detalii despre cum să obţineţi sfaturi.',
	'STAGE_FILE_CHECK'			=> 'Verifică fişiere',
	'STAGE_UPDATE_DB'			=> 'Actualizează bază de date',
	'STAGE_UPDATE_FILES'		=> 'Actualizează fişiere',
	'STAGE_VERSION_CHECK'		=> 'Verificare versiune',
	'STATUS_CONFLICT'			=> 'Fişierul modificat produce conflicte',
	'STATUS_MODIFIED'			=> 'Fişierul modificat',
	'STATUS_NEW'				=> 'Noul fişier',
	'STATUS_NEW_CONFLICT'		=> 'Conflictele noului fişier',
	'STATUS_NOT_MODIFIED'		=> 'Fişierul nu a fost modificat',
	'STATUS_UP_TO_DATE'			=> 'Fişier deja actualizat',
	
	'TOGGLE_DISPLAY'			=> 'Arată/ascunde lista fişierelor',
	'TRY_DOWNLOAD_METHOD'      => 'Puteţi încerca metoda descărcării fişierelor modificate.<br />Această metodă funcţionează întotdeauna şi este calea recomandată de actualizare.',
  'TRY_DOWNLOAD_METHOD_BUTTON'=> 'Încerc acum această metodă',

	'UPDATE_COMPLETED'				=> 'Actualizare completă',
	'UPDATE_DATABASE'				=> 'Actualizare baza de date',
	'UPDATE_DATABASE_EXPLAIN'		=> 'În următoarea etapă va fi actualizată baza de date.',
	'UPDATE_DATABASE_SCHEMA'		=> 'Schema actualizării bazei de date',
	'UPDATE_FILES'					=> 'Actualizare fişiere',
	'UPDATE_FILES_NOTICE'			=> 'Vă rugăm să vă asiguraţi că aţi actualizat fişierele forumului propriu, acest fişier vă actualizează doar baza de date.',
	'UPDATE_INSTALLATION'			=> 'Actualizează instalarea phpBB',
	'UPDATE_INSTALLATION_EXPLAIN'	=> 'Cu această opţiune, este posibil să actualizaţi instalarea phpBB la ultima versiune.<br />În timpul procesului toate fişierele proprii vor fi verificate pentru integritatea lor. Puteţi revizui tote diferenţele şi fişierele înainte de actualizare.<br /><br />Actualizarea propriu zisă a fişierului se poate efectua în două moduri diferite.</p><h2>Actualizare manuală</h2><p>Cu această actualizare doar descărcaţi setul personal al fişierelor modificate pentru a vă asigura că nu pierdeţi modificările fişierelor pe care le-ai efectuat. După ce aţi descărcat acest pachet trebuie să încărcaţi manual fişierele în locaţia corectă din directorul rădăcina al phpBB. Odată ce este gata, puteţi porni din nou procesul de verificare al fişierelor ca să vedeţi dacă aţi mutat fişierele în locaţia corectă.</p><h2>Actualizare automată cu FTP</h2><p>Această metodă este similară cu prima dar fără a fi necesară descărcarea şi încărcarea proprie a fişierelor modificate. Aceste operaţii vor fi efectuate pentru dumneavoastră. Pentru a folosi această metodă trebuie să ştiţi detalile de autentificare pe FTP dn moment ce veţi fi întrebat de ele. Odată ce aţi terminat, vei fi redirecţionat către etapa de verificare a fişierelor pentru a vă asigura că totul s-a actualizat corect.',
	'UPDATE_INSTRUCTIONS'			=> '

		<h1>Anunţ de lansare</h1>

		<p>Vă rugăm să citiţi <a href="%1$s" title="%1$s">anunţurile relatărilor pentru ultima versiune</a> înainte de a continua procesul de actualizare, s-ar putea să conţină informaţii folositoare. De asemenea conţine link-urile pentru descărcarea completă precum şi jurnalul schimbărilor.</p>

		<br />

		<h1>Cum să actualizaţi instalarea cu Pachetul de actualizate automată (Automatic Update Package)</h1>

		<p>Modul recomandat pentru a actualiza instalarea afişată aici este disponibil numai pentru pachetul de actualizare automată. De asemenea puteţi să actualizaţi instalarea folosind metodele afişate în documentul INSTALL.html. Paşii pentru actualizarea automată a phpBB3 sunt:</p>

		<ul style="margin-left: 20px; font-size: 1.1em;">
			<li>Accesaţi <a href="https://www.phpbb.com/downloads/" title="https://www.phpbb.com/downloads/">pagina de descărcări a phpBB.com</a> şi descărcaţi arhiva „Automatic Update Package”.<br /><br /></li>
			<li>Dezarhivaţi arhiva.<br /><br /></li>
			<li>Încărcaţi directorul complet de instalare dezarhivat în rădăcina directorului phpBB (unde este localizat fişierul config.php).<br /><br /></li>
		</ul>

		<p>Odată încărcat, forumul propriu va fi offline pentru utilizatorii normali cât timp directorul install încărcat va fi prezent.<br /><br />
		<strong><a href="%2$s" title="%2$s">Acum porniţi procesul de actualizare accesând din browser directorul install</a>.</strong><br />
		<br />
		Veţi fi ghidat în timpul procesului de actualizare. Veţi fi notificat odată ce actualizarea va fi efectuată cu succes.
		</p>
	',
	'UPDATE_INSTRUCTIONS_INCOMPLETE'	=> '

		<h1>A fost găsită o actualizare incompletă</h1>

		<p>phpBB a detectat o actualizare automată incompletă. Vă rugăm să vă asiguraţi că aţi urmat fiecare pas din actualizarea automată. Mai jos veţi găsi o legătură cu care veți putea relua actualizarea sau puteți de asemenea accesa directorul install direct.</p>
	',
	'UPDATE_METHOD'					=> 'Metoda de încărcare',
	'UPDATE_METHOD_EXPLAIN'			=> 'Puteţi alege metoda preferată de actualizare. Folosind opţiunea Încărcare prin FTP vi se va pune la dispoziţie un formular unde trebuie să introduceţi detaliile contului FTP. Cu această metodă, fişierele vor fi mutate automat către noua lor locaţie iar copiile de siguranţă (backup) ale fişierelor vechi vor fi create într-un fişier cu extensia .bak. Dacă alegeţi să descărcaţi fişierele modificate, mai târziu le puteţi dezarhiva şi încărca manual în locaţia lor corectă.',
	'UPDATE_REQUIRES_FILE'			=> 'Aplicaţia de actualizare necesită ca următorul fişier să fie prezent: %s',
	'UPDATE_SUCCESS'				=> 'Actualizarea a fost efectuată cu succes',
	'UPDATE_SUCCESS_EXPLAIN'		=> 'Toate fişierele au fost actualizate cu succes. Următorul pas impune verficarea din nou a fişierelor pentru a vă asigura că fişierele au fost actualizate corect.',
	'UPDATE_VERSION_OPTIMIZE'		=> 'Actualizează versiunea şi optimizează tabelele',
	'UPDATING_DATA'					=> 'Actualizează datele',
	'UPDATING_TO_LATEST_STABLE'		=> 'Actualizează baza de date la ultima versiune stabilă',
	'UPDATED_VERSION'				=> 'Versiune actualizată',
	'UPGRADE_INSTRUCTIONS'         => 'O versiune nouă <strong>%1$s</strong> este disponibilă. Citiţi <a href="%2$s" title="%2$s"><strong>anunţul lansării versiunii</strong></a> pentru a afla ce vă oferă şi cum să o actualizaţi.',
	'UPLOAD_METHOD'					=> 'Metoda de încărcare',

	'UPDATE_DB_SUCCESS'				=> 'Actualizarea bazei de date a fost efectuată cu succes.',
	'USER_ACTIVE'					=> 'Utilizator activ',
	'USER_INACTIVE'					=> 'Utilizator inactiv',

	'VERSION_CHECK'				=> 'Verificare versiune',
	'VERSION_CHECK_EXPLAIN'		=> 'Verifică dacă versiunea phpBB instalată este la zi.',
	'VERSION_NOT_UP_TO_DATE'	=> 'Versiunea phpBB nu este la zi. Vă rugăm să continuaţi procesul de actualizare.',
	'VERSION_NOT_UP_TO_DATE_ACP'=> 'Versiunea phpBB nu este la zi.<br />Mai jos veţi găsi o legătură către anunţul de lansare a ultimei versiuni, dar şi instrucţiunile pentru efectuarea actualizării.',
	'VERSION_NOT_UP_TO_DATE_TITLE'	=> 'Versiunea phpBB instalată nu este la zi.',
	'VERSION_UP_TO_DATE'		=> 'Instalarea este la zi. Cu toate că nicio actualizare nu este valabilă pentru versiunea phpBB curentă folosită, puteţi continua pentru a efectua o verificare de validare a fişierelor.',
	'VERSION_UP_TO_DATE_ACP'	=> 'Instalarea este la zi, nicio actualizare nu este valabilă pentru versiunea phpBB curentă folosită.',
	'VIEWING_FILE_CONTENTS'		=> 'Vizualizează conţinutul fişierelor',
	'VIEWING_FILE_DIFF'			=> 'Vizualizează diferenţele fişierelor',

	'WRONG_INFO_FILE_FORMAT'	=> 'Informaţia formatului fişierului este greşită',
));

// Default database schema entries...
$lang = array_merge($lang, array(
	'CONFIG_BOARD_EMAIL_SIG'		=> 'Mulţumim, Conducerea',
	'CONFIG_SITE_DESC'				=> 'Un text scurt pentru a descrie forumul propriu',
	'CONFIG_SITENAME'				=> 'domeniultau.ro',

	'DEFAULT_INSTALL_POST'			=> 'Acesta este un mesaj exemplu din instalarea phpBB3. Totul pare să funcţioneze normal. Puteţi şterge acest mesaj dacă doriţi şi continua configurarea forumului. În timpul procesului de instalare, pe prima categorie şi primul forum este atribuit un set de permisiuni potrivit grupurilor predefinite de administratori, roboţi, moderatori globali, vizitatori, utilizatori înregistraţi şi utilizatori înregistraţi COPPA. De asemenea, dacă alegeţi să ştergeţi prima categorie şi primul forum, nu uitaţi să stabiliţi permisiuni pentru toate aceste grupuri de utilizatori pe toate categoriile şi forumurile pe care le creaţi. Este recomandat să redenumiţi prima categorie şi primul forum şi să copiaţi permisiunile de la acestea când creaţi categorii şi forumuri noi. Nu uitați că suportul în limba română se acordă pe forumul phpBB România, disponibil la adresa http://www.phpbb.ro. Distracţie maximă!',

	'FORUMS_FIRST_CATEGORY'			=> 'Prima mea categorie',
	'FORUMS_TEST_FORUM_DESC'		=> 'Descrierea primului forum.',
	'FORUMS_TEST_FORUM_TITLE'		=> 'Primul forum propriu',

	'RANKS_SITE_ADMIN_TITLE'		=> 'Administratorul site-ului',
	'REPORT_WAREZ'					=> 'Acest mesaj conţine legături către aplicaţii ilegale sau piratate.',
	'REPORT_SPAM'					=> 'Mesajul raportat are drept scop doar reclama pentru un alt site web sau alt produs.',
	'REPORT_OFF_TOPIC'				=> 'Mesajul raportat este în afara subiectului.',
	'REPORT_OTHER'					=> 'Mesajul raportat nu se potriveşte în nicio altă categorie, vă rugăm să folosiţi câmpul de descriere.',

	'SMILIES_ARROW'					=> 'Săgeată',
	'SMILIES_CONFUSED'				=> 'Confuz',
	'SMILIES_COOL'					=> 'Mişto',
	'SMILIES_CRYING'				=> 'Plângând sau Foarte supărat',
	'SMILIES_EMARRASSED'			=> 'Ruşinat',
	'SMILIES_EVIL'					=> 'Rău sau Foarte supărat',
	'SMILIES_EXCLAMATION'			=> 'Exclamare',
	'SMILIES_GEEK'					=> 'Tocilar',
	'SMILIES_IDEA'					=> 'Idee',
	'SMILIES_LAUGHING'				=> 'Râzând',
	'SMILIES_MAD'					=> 'Supărat',
	'SMILIES_MR_GREEN'				=> 'Dl. Green',
	'SMILIES_NEUTRAL'				=> 'Neutru',
	'SMILIES_QUESTION'				=> 'Întrebare',
	'SMILIES_RAZZ'					=> 'Tachinează',
	'SMILIES_ROLLING_EYES'			=> 'Ochi rostogolindu-se',
	'SMILIES_SAD'					=> 'Trist',
	'SMILIES_SHOCKED'				=> 'Şocat',
	'SMILIES_SMILE'					=> 'Zâmbet',
	'SMILIES_SURPRISED'				=> 'Surprins',
	'SMILIES_TWISTED_EVIL'			=> 'Diavol mic',
	'SMILIES_UBER_GEEK'				=> 'Uber Geek',
	'SMILIES_VERY_HAPPY'			=> 'Foarte fericit',
	'SMILIES_WINK'					=> 'Clipire',

	'TOPICS_TOPIC_TITLE'			=> 'Bine aţi venit la phpBB3',
));

?>