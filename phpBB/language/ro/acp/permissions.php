<?php
/** 
*
* acp_permissions [Română]
*
* @package language
* @version $Id: permissions.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: permissions.php, 8479 2008-05-19 20:49:11 www.phpbb.ro (shara21jonny) Exp $
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
	'ACP_PERMISSIONS_EXPLAIN'	=> '
		<p>Permisiunile sunt împărţite şi grupate in patru mari secţiuni majore, dupa cum urmează:</p>

		<h2>Permisiuni Globale</h2>
		<p>Sunt folosite pentru a controla accesul la un nivel global şi se aplică la întreagul forum. Ele sunt ulterior împărţite în Permisiuni Utilizatori, Permisiuni Grupuri, Administratori şi Moderatori Globali.</p>

		<h2>Permisiuni Forum</h2>
		<p>Sunt folosite pentru a controla accesul pe fiecare forum. Ele sunt ulterior împărţite în Permisiuni Forum, Moderatori Forum, Permisiuni Utilizatori Forum şi Permisiuni Grupuri.</p>

		<h2>Permisiuni Roluri</h2>
		<p>Sunt folosite pentru a crea seturi diferite de permisiuni pentru diferite tipuri de permisiuni, mai târziu fiind capabile sa fie alocate pe fundamentul rolurilor. Rolurile standard trebuie să acopere administrarea forumurilor mari şi mici, totuşi în interiorul fiecăruia dintre cele patru divizii puteţi adăuga/modifica/şterge rolurile aşa cum credeţi că e mai bine.</p>

		<h2>Permisiuni Măşti</h2>
		<p>Sunt folosite pentru a vedea permisiunile efective alocate pentru Utilizatori, Moderatori (Locali şi Globali), Administratori sau Forumuri.</p>
	
		<br />

		<p>Pentru informaţii suplimentare legate de configurarea şi administrarea permisiunilor forumului phpBB3 propriu, vă rugăm să vizitaţi <a href="https://www.phpbb.com/support/documentation/3.0/quickstart/quick_permissions.html">Capitolul 1.5 al Ghidului nostru de Start Rapid</a>.</p>
	',

	'ACL_NEVER'				=> 'Niciodată',
	'ACL_SET'				=> 'Configurare permisiuni',
	'ACL_SET_EXPLAIN'		=> 'Permisiunile sunt bazate pe un simplu sistem <samp>DA</samp>/<samp>NU</samp>. Opţiunea <samp>NICODATĂ</samp> specificată pentru un utilizator sau grup utilizatori înlocuieşte orice altă valoare atribuită. Dacă nu doriţi să atribuiţi o valoare pentru o opţiune acestui utilizator sau grup selectaţi <samp>NU</samp>. Dacă valorile sunt atribuite pentru această opţiune, ele vor fi folosite în preferinţe, altfel <samp>NICIODATĂ</samp> este asumat. Toate obiectele marcate (cu o căsuţă de marcaj în faţa lor) vor copia permisiunile pe care le-aţi definit.',
	'ACL_SETTING'			=> 'Configurări',

	'ACL_TYPE_A_'			=> 'Permisiuni administrative',
	'ACL_TYPE_F_'			=> 'Permisiuni forum',
	'ACL_TYPE_M_'			=> 'Permisiuni de moderare',
	'ACL_TYPE_U_'			=> 'Permisiuni utilizator',

	'ACL_TYPE_GLOBAL_A_'	=> 'Permisiuni administrative',
	'ACL_TYPE_GLOBAL_U_'	=> 'Permisiuni utilizator',
	'ACL_TYPE_GLOBAL_M_'	=> 'Permisiuni moderator global',
	'ACL_TYPE_LOCAL_M_'		=> 'Permisiuni moderator forum',
	'ACL_TYPE_LOCAL_F_'		=> 'Permisiuni forum',
	
	'ACL_NO'				=> 'Nu',
	'ACL_VIEW'				=> 'Vizualizare permisiuni',
	'ACL_VIEW_EXPLAIN'		=> 'Aici puteţi vedea permisiunile efective pe care le are un utilizator sau grup. Un pătrat roşu arată ca utilizatorul sau grupul nu are permisiune, un pătrat verde arată că utilizatorul sau grupul are permisiune.',
	'ACL_YES'				=> 'Da',

	'ACP_ADMINISTRATORS_EXPLAIN'				=> 'Aici puteţi atribui drepturi de administrator pentru utilizatori sau grupuri. Toţi utilizatorii cu drepturi de administrator pot vedea panoul de administrare.',
	'ACP_FORUM_MODERATORS_EXPLAIN'				=> 'Aici puteţi aloca utilizatori şi grupuri de utilizatori ca şi moderatori de forum. Pentru a atribui accesul utilizatorilor la forumuri, pentru a defini drepturile globale de moderare sau administrare, vă rugăm să folosiţi pagina potrivită.',
	'ACP_FORUM_PERMISSIONS_EXPLAIN'				=> 'Aici puteţi modifica ce utilizatori sau grupuri pot accesa anumite forumuri. Pentru a aloca moderatori sau a defini administratori vă rugăm folosiţi pagina potrivită.',
	'ACP_FORUM_PERMISSIONS_COPY_EXPLAIN'		=> 'Aici puteţi copia permisiunile de la un forum la alt form sau mai multe.',
	'ACP_GLOBAL_MODERATORS_EXPLAIN'				=> 'Aici puteţi aloca drepturi de moderatori globali pentru utilizatori sau grupuri. Aceşti moderatori sunt ca şi cei obişnuiţi numai că au acces de moderare pe toate forumurile.',
	'ACP_GROUPS_FORUM_PERMISSIONS_EXPLAIN'		=> 'Aici puteţi atribui permisiuni pe forum pentru grupuri.',
	'ACP_GROUPS_PERMISSIONS_EXPLAIN'			=> 'Aici puteţi atribui permisiuni globale pentru grupuri - permisiuni utilizatori, permisiuni globale de moderare şi permisiuni administrative. Permisiunile utilizatorilor includ capabilitatea de a folosi imagini asociate (avatar), de a trimite mesaje private, etc. Permisiunile moderatorului global sunt de tipul aprobare mesaje, administrare subiecte, administrare restricţii iar cele administrative sunt de tipul modificare permisiuni, definire coduri BB personalizate, administrare forumuri etc. Permisiunile individuale ale utilizatorilor ar trebui schimbate numai in situaţii rare, metoda preferată fiind aceea de a pune utilizatorul în grupuri şi a atribui permisiunile grupului respectiv.',
	'ACP_ADMIN_ROLES_EXPLAIN'					=> 'Aici puteţi administra rolurile pentru permisiuni administrative. Rolurile sunt permisiuni efective, dacă schimbaţi un rol, atunci elementele ce au acest rol îşi vor schimba de asemenea permisiunile.',
	'ACP_FORUM_ROLES_EXPLAIN'					=> 'Aici puteţi administra rolurile pentru permisiuni forum. Rolurile sunt permisiuni efective, dacă schimbaţi un rol, atunci elementele ce au acest rol îşi vor schimba de asemenea permisiunile.',
	'ACP_MOD_ROLES_EXPLAIN'						=> 'Aici puteţi administra rolurile pentru permisiuni de moderare. Rolurile sunt permisiuni efective, dacă schimbaţi un rol, atunci elementele ce au acest rol îşi vor schimba de asemenea permisiunile.',
	'ACP_USER_ROLES_EXPLAIN'					=> 'Aici puteţi administra rolurile pentru permisiuni de utilizator. Rolurile sunt permisiuni efective, dacă schimbaţi un rol, atunci elementele ce au acest rol îşi vor schimba de asemenea permisiunile.',
	'ACP_USERS_FORUM_PERMISSIONS_EXPLAIN'		=> 'Aici puteţi atribui permisiuni pe forum pentru utilizatori.',
	'ACP_USERS_PERMISSIONS_EXPLAIN'				=> 'Aici puteţi atribui permisiuni globale pentru utilizatori - permisiuni utilizatori, permisiuni moderatori globali şi permisiuni administratori. Permisiunile utilizatorilor includ posibilitatea de a folosi imagini asociate (avatar), de a trimite mesaje private, etc; permisiunile moderatorului global includ posibilitatea de a aproba mesaje, de a administra subiecte şi interdicţii şi, în cele din urmă, permisiunile administrative includ posibilitatea de a modifica permisiunile, de a defini coduri BB specifice, de a administra forumuri, etc. Pentru a schimba aceste configurări pentru un număr mai mare de utilizatori, sistemul de permisiuni pentru grupuri este metoda preferată. Permisiunile utilizatorilor ar trebui schimbate numai in ocazii rare, metoda preferată fiind aceea de a asocia utilizatorii in grupuri şi pentru a atribui permisiunile pentru întregul grup.',
	'ACP_VIEW_ADMIN_PERMISSIONS_EXPLAIN'		=> 'Aici puteţi vedea permisiunile administrative efective atribuite utilizatorilor/grupurilor selectate.',
	'ACP_VIEW_GLOBAL_MOD_PERMISSIONS_EXPLAIN'	=> 'Aici puteţi vedea permisiunile globale de moderare atribuite utilizatorilor/grupurilor selectate.',
	'ACP_VIEW_FORUM_PERMISSIONS_EXPLAIN'		=> 'Aici puteţi vedea permisiunile forumului atribuite utilizatorilor/grupurilor selectate.',
	'ACP_VIEW_FORUM_MOD_PERMISSIONS_EXPLAIN'	=> 'Aici puteţi vedea permisiunile moderatorului de forum atribuite utilizatorilor/grupurilor selectat.',
	'ACP_VIEW_USER_PERMISSIONS_EXPLAIN'			=> 'Aici puteţi vedea permisiunile efective ale utilizatorului atribuite pentru utilizatorii/grupurile selectate.',

	'ADD_GROUPS'				=> 'Adaugă grupuri',
	'ADD_PERMISSIONS'			=> 'Adaugă permisiuni',
	'ADD_USERS'					=> 'Adaugă utilizatori',
	'ADVANCED_PERMISSIONS'		=> 'Permisiuni avansate',
	'ALL_GROUPS'				=> 'Selectează toate grupurile',
	'ALL_NEVER'					=> 'Toate <samp>NICIODATĂ</samp>',
	'ALL_NO'					=> 'Toate <samp>NU</samp>',
	'ALL_USERS'					=> 'Selectează toţi utilizatorii',
	'ALL_YES'					=> 'Toate <samp>DA</samp>',
	'APPLY_ALL_PERMISSIONS'		=> 'Aplică toate permisiunile',
	'APPLY_PERMISSIONS'			=> 'Aplică permisiuni',
	'APPLY_PERMISSIONS_EXPLAIN'	=> 'Rolul şi permisiunile definite pentru acest element vor fi aplicate pentru acesta dar şi pentru toate celelalte selectate.',
	'AUTH_UPDATED'				=> 'Permisiunile au fost actualizate',
	'COPY_PERMISSIONS_CONFIRM'				=> 'Sunteţi sigur că doriţi să continuaţi cu această operaţiune? Reţineţi că aceasta va suprascrie orice permisiuni existente pe destinaţiile selectate.',
	'COPY_PERMISSIONS_FORUM_FROM_EXPLAIN'	=> 'Forumul sursă ale cărui permisiuni vreţi să le copiaţi.',
	'COPY_PERMISSIONS_FORUM_TO_EXPLAIN'		=> 'Forumul destinaţie pe care vreţi să aplicaţi permisiunile.',
	'COPY_PERMISSIONS_FROM'					=> 'Copiază permisiunile de la',
	'COPY_PERMISSIONS_TO'					=> 'Aplică permisiunile la',

	'CREATE_ROLE'				=> 'Crează rol',
	'CREATE_ROLE_FROM'			=> 'Foloseşte configurările de la…',
	'CUSTOM'					=> 'Personalizat…',

	'DEFAULT'					=> 'Iniţial',
	'DELETE_ROLE'				=> 'Şterge rol',
	'DELETE_ROLE_CONFIRM'		=> 'Sunteţi sigur ca doriţi să ştergeţi aceast rol? Elementele ce au asociat aceast rol <strong>NU-ŞI</strong> vor pierde configurările permisiunilor.',
	'DISPLAY_ROLE_ITEMS'		=> 'Vizualizează elementele ce folosesc aceast rol',

	'EDIT_PERMISSIONS'			=> 'Modifică permisiuni',
	'EDIT_ROLE'					=> 'Modifică rol',

	'GROUPS_NOT_ASSIGNED'		=> 'Niciun grup nu are aceast rol',

	'LOOK_UP_GROUP'				=> 'Vizualizare grup utilizatori',
	'LOOK_UP_USER'				=> 'Vizualizare utilizator',

	'MANAGE_GROUPS'		=> 'Administrează grupuri',
	'MANAGE_USERS'		=> 'Administrează utilizatori',

	'NO_AUTH_SETTING_FOUND'		=> 'Configurările permisiunilor nu sunt definite.',
	'NO_ROLE_ASSIGNED'			=> 'Niciun rol atribuit…',
	'NO_ROLE_ASSIGNED_EXPLAIN'	=> 'Alocând acest rol nu va schimba permisiunile din dreapta. Dacă vreţi să eliminaţi toate permisiunile ar trebui să foloseiţi legătura „Toate <samp>NU</samp>” .',
	'NO_ROLE_AVAILABLE'			=> 'Niciun rol nu este disponibil',
	'NO_ROLE_NAME_SPECIFIED'	=> 'Daţi un nume rolului.',
	'NO_ROLE_SELECTED'			=> 'Rolul nu poate fi găsit.',
	'NO_USER_GROUP_SELECTED'	=> 'Nu aţi selectat un utilizator sau grup.',

	'ONLY_FORUM_DEFINED'	=> 'Aţi selectat doar forumuri în selecţia proprie. Trebuie să selectaţi şi cel puţin un utilizator sau un grup.',

	'PERMISSION_APPLIED_TO_ALL'		=> 'Rolul şi permisiunile vor fi de asemenea aplicate tuturor obiectelor selectate',
	'PLUS_SUBFORUMS'				=> '+Subforumuri',

	'REMOVE_PERMISSIONS'			=> 'Şterge permisiuni',
	'REMOVE_ROLE'					=> 'Şterge rol',
	'RESULTING_PERMISSION'			=> 'Rezultat permisiuni',
	'ROLE'							=> 'Rol',
	'ROLE_ADD_SUCCESS'				=> 'Rol adăugat cu succes.',
	'ROLE_ASSIGNED_TO'				=> 'Utilizatori/Grupuri asociate pentru %s',
	'ROLE_DELETED'					=> 'Rol şters cu succes.',
	'ROLE_DESCRIPTION'				=> 'Descriere rol',

	'ROLE_ADMIN_FORUM'			=> 'Administrator forum',
	'ROLE_ADMIN_FULL'			=> 'Administrator total',
	'ROLE_ADMIN_STANDARD'		=> 'Administrator standard',
	'ROLE_ADMIN_USERGROUP'		=> 'Administrator de utilizator şi grupuri',
	'ROLE_FORUM_BOT'			=> 'Acces robot',
	'ROLE_FORUM_FULL'			=> 'Acces total',
	'ROLE_FORUM_LIMITED'		=> 'Acces limitat',
	'ROLE_FORUM_LIMITED_POLLS'	=> 'Acces limitat + Chestionare',
	'ROLE_FORUM_NOACCESS'		=> 'Fără acces',
	'ROLE_FORUM_ONQUEUE'		=> 'În lista de aşteptare pentru moderare',
	'ROLE_FORUM_POLLS'			=> 'Acces standard + Chestionare',
	'ROLE_FORUM_READONLY'		=> 'Acces doar pentru vizualizare',
	'ROLE_FORUM_STANDARD'		=> 'Acces standard',
	'ROLE_FORUM_NEW_MEMBER'		=> 'Acces utilizator nou înregistrat',
	'ROLE_MOD_FULL'				=> 'Moderator total',
	'ROLE_MOD_QUEUE'			=> 'Moderator al listei de aşteptare',
	'ROLE_MOD_SIMPLE'			=> 'Moderator simplu',
	'ROLE_MOD_STANDARD'			=> 'Moderator standard',
	'ROLE_USER_FULL'			=> 'Toate facilităţile',
	'ROLE_USER_LIMITED'			=> 'Permisiuni limitate',
	'ROLE_USER_NOAVATAR'		=> 'Fără avatar',
	'ROLE_USER_NOPM'			=> 'Fără mesaje private',
	'ROLE_USER_STANDARD'		=> 'Facilităţi standard',
	'ROLE_USER_NEW_MEMBER'		=> 'Facilităţi utilizator nou înregistrat',
	
	'ROLE_DESCRIPTION_ADMIN_FORUM'			=> 'Poate accesa panoul de administrare al forumului şi setările de permisiuni.',
	'ROLE_DESCRIPTION_ADMIN_FULL'			=> 'Are acces la toate funcţiile de administrare ale acestui forum.<br />Nu este recomandat.',
	'ROLE_DESCRIPTION_ADMIN_STANDARD'		=> 'Are acces la majoritatea funcţionalităţilor de administrare, dar îi este permis accesul la opţiunile serverului şi sistemului asociat.',
	'ROLE_DESCRIPTION_ADMIN_USERGROUP'		=> 'Poate administra grupurile şi utilizatorii: poate schimba permisiuni, setări, administra restricţii şi ranguri.',
	'ROLE_DESCRIPTION_FORUM_BOT'			=> 'Acest rol este recomandat pentru roboţi şi păianjeni de căutare.',
	'ROLE_DESCRIPTION_FORUM_FULL'			=> 'Poate folosi toate opţiunile forumului, inclusiv publicarea anunţurilor şi a mesajelor importante. De asemenea, poate ignora limita de flood.<br />Nu este recomandat pentru utilizatorii normali.',
	'ROLE_DESCRIPTION_FORUM_LIMITED'		=> 'Poate folosi câteva dintre opţiunile forumului, dar nu poate ataşa fişiere sau folosi iconiţe pentru mesaje.',
	'ROLE_DESCRIPTION_FORUM_LIMITED_POLLS'	=> 'La fel ca şi la accesul limitat, dar poate crea chestionare.',
	'ROLE_DESCRIPTION_FORUM_NOACCESS'		=> 'Nu poate accesa sau vizualiza forumul.',
	'ROLE_DESCRIPTION_FORUM_ONQUEUE'		=> 'Poate folosi majoritatea opţiunilor, inclusiv fişierele ataşate, dar mesajele şi subiectele trebuie aprobate de către un moderator.',
	'ROLE_DESCRIPTION_FORUM_POLLS'			=> 'La fel ca şi la accesul standard, dar poate crea chestionare.',
	'ROLE_DESCRIPTION_FORUM_READONLY'		=> 'Poate citi forumul, dar nu poate crea subiecte noi sau răspunde mesajelor.',
	'ROLE_DESCRIPTION_FORUM_STANDARD'		=> 'Poate folosi majoritatea opţiunilor forumului, inclusiv fişierele ataşate şi ştergerea propriilor subiecte, dar nu-şi poate închide propriile subiecte sau crea chestionare.',
	'ROLE_DESCRIPTION_FORUM_NEW_MEMBER'		=> 'Un rol pentru membrii grupului special ce conţine utilizatorii recent înregistraţi; conţine <samp>NICIODATĂ</samp> permisiuni pentru a limita funcţionalităţile accesibile utilizatorilor noi.',
	'ROLE_DESCRIPTION_MOD_FULL'				=> 'Poate folosi toate opţiunile de moderare, inclusiv banarea.',
	'ROLE_DESCRIPTION_MOD_QUEUE'			=> 'Poate folosi lista de moderare pentru validarea şi modificarea mesajelor, dar nimic altceva.',
	'ROLE_DESCRIPTION_MOD_SIMPLE'			=> 'Poate folosi pentru subiecte doar acţiunile de bază. Nu poate trimite avertismente sau folosi lista de moderare.',
	'ROLE_DESCRIPTION_MOD_STANDARD'			=> 'Poate folosi majoritatea opţiunilor de moderare, dar nu poate interzice utilizatorii sau schimba autorul mesajului.',
	'ROLE_DESCRIPTION_USER_FULL'			=> 'Poate folosi toate opţiunile valabile pentru utilizatori, incluzând schimbarea numelui sau ignorarea limitei de flood.<br />Nu este recomandat.',
	'ROLE_DESCRIPTION_USER_LIMITED'			=> 'Poate accesa unele opţiuni ale utilizatorului. Fişierele ataşate, e-mailurile sau mesajele instante nu sunt permise.',
	'ROLE_DESCRIPTION_USER_NOAVATAR'		=> 'Are opţiuni limitate şi nu poate folosi opţiunea de imagine asociată (avatar).',
	'ROLE_DESCRIPTION_USER_NOPM'			=> 'Are opţiuni limitate şi nu poate folosi mesaje private.',
	'ROLE_DESCRIPTION_USER_STANDARD'		=> 'Poate accesa majoritatea, dar nu toate opţiunile utilizatorului. De exemplu, nu poate schimba numele sau ignora limita de flood.',
	'ROLE_DESCRIPTION_USER_NEW_MEMBER'		=> 'Un rol pentru membrii grupului special ce conţine utilizatorii recent înregistraţi; conţine <samp>NICIODATĂ</samp> permisiuni pentru a limita funcţionalităţile accesibile utilizatorilor noi.',
	
	'ROLE_DESCRIPTION_EXPLAIN'		=> 'Puteţi specifica o scurtă explicaţie pentru ce foloseşte acest rol sau pentru ce înseamnă. Textul pe care îl introduceţi aici va fi afişat de asemenea în cadrul ferestrelor cu permisiuni.',
	'ROLE_DESCRIPTION_LONG'			=> 'Descrierea rolului este prea lungă, te rugăm să te limitezi la 4000 caractere.',
	'ROLE_DETAILS'					=> 'Detalii rol',
	'ROLE_EDIT_SUCCESS'				=> 'Rol modificat cu succes.',
	'ROLE_NAME'						=> 'Nume rol',
	'ROLE_NAME_ALREADY_EXIST'		=> 'Acest nume de rol <strong>%s</strong> există deja pentru tipul de permisiune specificat.',
	'ROLE_NOT_ASSIGNED'				=> 'Rolul nu a fost incă alocat.',

	'SELECTED_FORUM_NOT_EXIST'		=> 'Forumul/Forumurile selectate nu există.',
	'SELECTED_GROUP_NOT_EXIST'		=> 'Grupul/Grupurile selectate nu există.',
	'SELECTED_USER_NOT_EXIST'		=> 'Utilizatorul/Utilizatorii selectaţi nu există.',
	'SELECT_FORUM_SUBFORUM_EXPLAIN'	=> 'Forumul pe care îl alegeţi aici va include toate subforumurile în această selecţie.',
	'SELECT_ROLE'					=> 'Selectează rol',
	'SELECT_TYPE'					=> 'Selectează tip',
	'SET_PERMISSIONS'				=> 'Selectează permisiuni',
	'SET_ROLE_PERMISSIONS'			=> 'Specifică permisiuni rol',
	'SET_USERS_PERMISSIONS'			=> 'Specifică permisiuni utilizator',
	'SET_USERS_FORUM_PERMISSIONS'	=> 'Specifică permisiuni utilizator forum',

	'TRACE_DEFAULT'					=> 'Iniţial, fiecare permisiune este <samp>NU</samp> (nu este specificată). Aşa că permisiunile pot fi suprascrise de către alte configurări.',
	'TRACE_FOR'						=> 'Urmă pentru',
	'TRACE_GLOBAL_SETTING'			=> '%s (global)',
	'TRACE_GROUP_NEVER_TOTAL_NEVER'	=> 'Permisiunea grupului pentru acest forum este <samp>NICIODATĂ</samp> ca şi rezultatul total, aşa că vechiul rezultat este păstrat.',
	'TRACE_GROUP_NEVER_TOTAL_NEVER_LOCAL'	=> 'Permisiunea grupului pentru acest forum este <samp>NICIODATĂ</samp> ca şi rezultatul total aşa că vechiul rezultat este păstrat.',
	'TRACE_GROUP_NEVER_TOTAL_NO'	=> 'Permisiunea grupului este setată pentru <samp>NICIODATĂ</samp> care devine noua valoare totală pentru că nu a fost specificată incă (specifică <samp>NU</samp>).',
	'TRACE_GROUP_NEVER_TOTAL_NO_LOCAL'	=> 'Permisiunea grupului pentru acest forum este <samp>NICIODATĂ</samp> care devine noua valoare totală pentru că nu a fost specificată incă (specifică <samp>NU</samp>).',
	'TRACE_GROUP_NEVER_TOTAL_YES'	=> 'Permisiunea grupului este <samp>NICIODATĂ</samp> care suprascrie toate opţiunile <samp>DA</samp> cu <samp>NICIODATĂ</samp> pentru acest utilizator.',
	'TRACE_GROUP_NEVER_TOTAL_YES_LOCAL'	=> 'Permisiunea grupului pentru acest forum este <samp>NICIODATĂ</samp> care suprascrie toate opţiunile <samp>DA</samp> cu <samp>NICIODATĂ</samp> pentru acest utilizator.',
	'TRACE_GROUP_NO'				=> 'Permisiunea este <samp>NU</samp> pentru acest grup, aşa că toată valoare veche va fi păstrată.',
	'TRACE_GROUP_NO_LOCAL'			=> 'Permisiunea este <samp>NU</samp> pentru acest grup în forum astfel încât toată valoare veche va fi păstrată.',
	'TRACE_GROUP_YES_TOTAL_NEVER'	=> 'Această permisiune de grup este <samp>DA</samp> dar totalul <samp>NICIODATĂ</samp> nu poate fi suprascris.',
	'TRACE_GROUP_YES_TOTAL_NEVER_LOCAL'	=> 'Această permisiune de grup pentru forum este <samp>DA</samp> dar totalul <samp>NICODATĂ</samp> nu poate fi suprascris.',
	'TRACE_GROUP_YES_TOTAL_NO'		=> 'Această permisiune de grup este <samp>DA</samp> care devine noua valoare totală deoarece incă nu a fost setată (specifică <samp>NU</samp>).',
	'TRACE_GROUP_YES_TOTAL_NO_LOCAL'	=> 'Această permisiune de grup pentru forum este <samp>DA</samp> care devine devine noua valoare totală pentru că încă nu a fost setată (specifică <samp>NU</samp>).',
	'TRACE_GROUP_YES_TOTAL_YES'		=> 'Această permisiune de grup este <samp>DA</samp> şi totalul permisiunii este deja specificat pentru <samp>DA</samp>, aşa că rezultatul total este păstrat.',
	'TRACE_GROUP_YES_TOTAL_YES_LOCAL'	=> 'Această permisiune de grup pentru forum este <samp>DA</samp> şi permisiunea totală este deja specificată pentru <samp>DA</samp>, aşa că rezultatul total este păstrat.',
	'TRACE_PERMISSION'				=> 'Vezi permisiune - %s',
	'TRACE_RESULT'					=> 'Vezi rezultat',
	'TRACE_SETTING'					=> 'Vezi configurare',

	'TRACE_USER_GLOBAL_YES_TOTAL_YES'		=> 'Permisiunile utilizatorulul independente de forum sunt evaluate la <samp>DA</samp> dar totalul este deja setat la <samp>DA</samp>, aşa că rezultatul total va fi păstrat. %sTrace global permission%s',
	'TRACE_USER_GLOBAL_YES_TOTAL_NEVER'		=> 'Permisiunile utilizatorulul independente de forum sunt evaluate la <samp>DA</samp> care suprascrie rezultatul local curent <samp>NICIODATĂ</samp>. %sTrace global permission%s',
	'TRACE_USER_GLOBAL_NEVER_TOTAL_KEPT'	=> 'Permisiunile utilizatorulul independente de forum sunt evaluate la  <samp>NICIODATĂ</samp> care nu influenţează permisiunea locală. %sTrace global permission%s',
	
	'TRACE_USER_FOUNDER'					=> 'Utilizatorul este un fondator, de aceea permisiunile de administrare sunt specificate iniţial la valoarea <samp>DA</samp>.',
	'TRACE_USER_KEPT'						=> 'Permisiunea utilizatorului este <samp>NU</samp> aşa că vechea valoare totală este păstrată.',
	'TRACE_USER_KEPT_LOCAL'					=> 'Permisiunea utilizatorului pentru acest forum este <samp>NU</samp> aşa că valoarea totală veche este păstrată.',
	'TRACE_USER_NEVER_TOTAL_NEVER'			=> 'Permisiunea utilizatorului este <samp>NICIODATĂ</samp> şi valoarea totală este setată la <samp>NICIODATĂ</samp>, aşa că nimic nu este schimbat.',
	'TRACE_USER_NEVER_TOTAL_NEVER_LOCAL'	=> 'Permisiunea utilizatorului pentru acest forum este <samp>NICIODATĂ</samp> iar valoarea totală este setată la  <samp>NICODATĂ</samp>, aşa că nimic nu este schimbat.',
	'TRACE_USER_NEVER_TOTAL_NO'				=> 'Permisiunea utilizatorului este <samp>NICIODATĂ</samp> care devin valorile totale deoarece au fost setate la NU.',
	'TRACE_USER_NEVER_TOTAL_NO_LOCAL'		=> 'Permisiunea utilizatorului pentru acest forum este <samp>NICIODATĂ</samp> care devin valorile totale deoarece au fost setate la NU.',
	'TRACE_USER_NEVER_TOTAL_YES'			=> 'Permisiunea utilizatorului este <samp>NICIODATĂ</samp> şi le suprascrie pe cele anterioare <samp>DA</samp>.',
	'TRACE_USER_NEVER_TOTAL_YES_LOCAL'		=> 'Permisiunea utilizatorului pentru acest forum este <samp>NICIODATĂ</samp> şi suprascrie permisiunile precedente <samp>DA</samp>.',
	'TRACE_USER_NO_TOTAL_NO'				=> 'Permisiunea utilizatorului este <samp>NU</samp> iar valoarea totală a fost setată la NU aşa că iniţial este la <samp>NICIODATĂ</samp>.',
	'TRACE_USER_NO_TOTAL_NO_LOCAL'			=> 'Permisiunea utilizatorului pentru acest forum este <samp>NU</samp> iar valoarea totală a fost setată la NU aşa că iniţial este <samp>NICIODATĂ</samp>.',
	'TRACE_USER_YES_TOTAL_NEVER'			=> 'Permisiunea utilizatorului este <samp>DA</samp> dar cele totale <samp>NICIODATĂ</samp> nu pot fi suprascrise.',
	'TRACE_USER_YES_TOTAL_NEVER_LOCAL'		=> 'Permisiunea utilizatorului pentru acest forum este <samp>DA</samp> dar totalul <samp>NICODATĂ</samp> nu poate fi suprascris.',
	'TRACE_USER_YES_TOTAL_NO'				=> 'Permisiunea utilizatorului este <samp>DA</samp> care devine valoarea totală deoarece a fost setat la <samp>NU</samp>.',
	'TRACE_USER_YES_TOTAL_NO_LOCAL'			=> 'Permisiunea utilizatorului pentru acest forum este <samp>DA</samp> care devin valoarea totală pentru că a fost setat la <samp>NU</samp>.',
	'TRACE_USER_YES_TOTAL_YES'				=> 'Permisiunea utilizatorului este <samp>DA</samp> iar valoarea totală este setată la <samp>DA</samp>, aşa că nu s-a schimbat nimic.',
	'TRACE_USER_YES_TOTAL_YES_LOCAL'		=> 'Permisiunea utilizatorului pentru acest forum este <samp>DA</samp> iar valoarea totală este setată la <samp>YES</samp>, aşa că nimic nu va fi schimbat.',
	'TRACE_WHO'								=> 'Cine',
	'TRACE_TOTAL'							=> 'Total',

	'USERS_NOT_ASSIGNED'			=> 'Niciun utilizator asociat acestui rol',
	'USER_IS_MEMBER_OF_DEFAULT'		=> 'este un membru al urmatoarelor grupuri predefinite',
	'USER_IS_MEMBER_OF_CUSTOM'		=> 'este un membru al urmatoarelor grupuri de utilizatori definite ',

	'VIEW_ASSIGNED_ITEMS'	=> 'Vizualizare elementele asociate',
	'VIEW_LOCAL_PERMS'		=> 'Permisiuni locale',
	'VIEW_GLOBAL_PERMS'		=> 'Permisiuni globale',
	'VIEW_PERMISSIONS'		=> 'Vizualizare permisiuni',

	'WRONG_PERMISSION_TYPE'	=> 'Tipul de permisiuni selectat este greşit.',
	'WRONG_PERMISSION_SETTING_FORMAT'	=> 'Specificaţiile de permisiune sunt într-un format greşit, phpBB nu poate să le proceseze corect.',
));

?>