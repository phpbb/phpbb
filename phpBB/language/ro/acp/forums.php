<?php
/**
*
* acp_forums [Română]
*
* @package language
* @version $Id: forums.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: forums.php, 8479 2007-12-29 17:05:00 www.phpbb.ro (shara21jonny) Exp $
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

// Forum Admin
$lang = array_merge($lang, array(
    'AUTO_PRUNE_DAYS'            => 'Autoşterge mesajele vechi',
    'AUTO_PRUNE_DAYS_EXPLAIN'    => 'Numărul de zile de la ultimul mesaj după care subiectul este mutat.',
    'AUTO_PRUNE_FREQ'            => 'Frecvenţa auto-ştergerii',
    'AUTO_PRUNE_FREQ_EXPLAIN'    => 'Timpul în zile între evenimentele de ştergere.',
    'AUTO_PRUNE_VIEWED'            => 'Autoşterge mesaje în funcţie de vârsta vizualizării',
    'AUTO_PRUNE_VIEWED_EXPLAIN'    => 'Numărul de zile de când subiectul a fost vizualizat după care subiectul este mutat.',
    'CONTINUE'						=> 'Continuare',

    'COPY_PERMISSIONS'                => 'Copiază permisiuni de la',
    'COPY_PERMISSIONS_EXPLAIN'		=> 'Pentru a facilita setarea permisiunilor pentru noul forum propriu, puteţi copia permisiunile unul forum ce există deja.',
    'COPY_PERMISSIONS_ADD_EXPLAIN'    => 'Odată creat, forumul va avea aceleaşi permisiuni ca şi cele pe care le selectaţi aici. Dacă niciun forum nu este selectat, cel mai nou forum creat nu va fi vizibil până au fost specificate permisiunile.',
    'COPY_PERMISSIONS_EDIT_EXPLAIN'    => 'Dacă alegeţi să copiaţi permisiunile, forumul va avea aceleaşi permisiuni ca şi cel selectat aici. Această operaţie va suprascrie orice permisiune pe care aţi setat-o anterior pentru acest forum cu permisiunile forumului pe care l-aţi selectat aici. Dacă niciun forum nu este selectat, vor fi păstrate permisiunile curente.',
    'COPY_TO_ACL'					=> 'Alternativ, puteţi de asemenea să %sspecificaţi permisiuni noi%s pentru acest forum.',
    'CREATE_FORUM'                    => 'Crează un nou forum',

    'DECIDE_MOVE_DELETE_CONTENT'        => 'Ştergeţi conţinut sau mutaţi în forum',
    'DECIDE_MOVE_DELETE_SUBFORUMS'        => 'Ştergeţi subforumuri sau mutaţi în forum',
    'DEFAULT_STYLE'                        => 'Stil standard',
    'DELETE_ALL_POSTS'                    => 'Şterge mesaje',
    'DELETE_SUBFORUMS'                    => 'Şterge subforumuri şi mesaje',
    'DISPLAY_ACTIVE_TOPICS'                => 'Permite subiecte active',
    'DISPLAY_ACTIVE_TOPICS_EXPLAIN'        => 'Dacă alegeţi Da, subiectele active în subforumurile selectate vor fi afişate sub această categorie.',

    'EDIT_FORUM'                    => 'Modificare forum',
    'ENABLE_INDEXING'                => 'Activaţi indexarea căutării',
    'ENABLE_INDEXING_EXPLAIN'        => 'Dacă alegeţi Da, mesajele scrise în acest forum vor fi indexate pentru căutare.',
    'ENABLE_POST_REVIEW'            => 'Permiteţi revizualizarea mesajului',
    'ENABLE_POST_REVIEW_EXPLAIN'    => 'Dacă alegeţi Da, utilizatorii sunt capabili să-şi revizualizeze mesajul dacă au fost scrise în subiect mesaje noi în timp ce utilizatorii le-au scris pe ale lor. Aceasta opţiune ar trebui dezactivată pentru forumurile de tip chat.',
    'ENABLE_QUICK_REPLY'			=> 'Activare răspuns rapid',
	   'ENABLE_QUICK_REPLY_EXPLAIN'	=> 'Activează opţiunea de răspuns rapid în acest forum. Această setare nu este luată în considerare dacă răspunsul rapid este dezactivat pe tot forumul. Răspunsul rapid va fi afişat doar pentru utilizatorii care au dreptul să scrie în acest forum.',

    'ENABLE_RECENT'                    => 'Afişaţi subiectele active',
    'ENABLE_RECENT_EXPLAIN'            => 'Dacă alegeţi Da, subiectele scrise în acest forum vor fi afişate în lista subiectelor active.',
    'ENABLE_TOPIC_ICONS'            => 'Permite subiecte cu iconiţă',

    'FORUM_ADMIN'                        => 'Administrare forum',
    'FORUM_ADMIN_EXPLAIN'                => 'În phpBB3 totul este bazat pe forum. O categorie este un tip special de forum. Fiecare forum poate avea un număr nelimitat de subforumuri şi puteţi determina dacă fiecare mesaj poate fi scris sau nu (în acest caz se comportă ca o categorie veche). Aici puteţi adăuga, modifica, şterge, închide, deschide individual forumurile la fel ca nişte controale adiţionale specifice. Dacă mesajele şi subiectele nu mai sunt sincronizate, puteţi să resincronizaţi forumul. <strong>Trebuie să copiaţi sau să specificaţi permisiuni corespunzătoare pentru forumurile noi create pentru a le afişa.</strong>',
    'FORUM_AUTO_PRUNE'                    => 'Permite autoştergerea',
    'FORUM_AUTO_PRUNE_EXPLAIN'            => 'Ştergeţi forumul de subiecte, specificaţi parametrii de frecvenţa/vârstă mai jos.',
    'FORUM_CREATED'                        => 'Forum creat cu succes.',
    'FORUM_DATA_NEGATIVE'                => 'Parametrii de ştergere nu pot fi negativi.',
    'FORUM_DESC_TOO_LONG'                => 'Descrierea forumului este prea lungă, trebuie să conţină mai puţin de 400 caractere.',
    'FORUM_DELETE'                        => 'Şterge forum',
    'FORUM_DELETE_EXPLAIN'                => 'Formularul de jos vă permite să ştergeţi un forum. Dacă scrierea este permisă în forum, sunteţi capabil să decideţi unde vreţi să puneţi toate subiectele (sau forumurile) sale.',
    'FORUM_DELETED'                        => 'Forum şters cu succes.',
    'FORUM_DESC'                        => 'Descriere',
    'FORUM_DESC_EXPLAIN'                => 'Fiecare marcaj HTML introdus aici va fi afişat cum este.',
    'FORUM_EDIT_EXPLAIN'                => 'Formularul de jos vă permite să particularizaţi acest forum. Reţineţi că moderarea şi instrumentele de numărat mesajele sunt setate via permisiunile forumului pentru fiecare utilizator sau grup de utilizatori.',
    'FORUM_IMAGE'                        => 'Imagine forum',
    'FORUM_IMAGE_EXPLAIN'                => 'Locaţia, relativă la directorul rădăcină al phpBB, a unei imagini adiţionale asociate cu acest forum.',
    'FORUM_IMAGE_NO_EXIST'				=> 'Imaginea specificată pentru forum nu există',
    'FORUM_LINK_EXPLAIN'                => 'Adresa URL completă (incluzând protocolul, cu alte cuvinte <samp>http://</samp>) către locaţia destinaţie va fi trimis utilizatorul care alege acest forum, de exemplu <samp>http://www.phpbb.com/</samp>.',
    'FORUM_LINK_TRACK'                    => 'Urmarire legături redirecţionate',
    'FORUM_LINK_TRACK_EXPLAIN'            => 'Înregistraţi de câte ori legătura unui forum a fost accesată.',
    'FORUM_NAME'                        => 'Nume forum',
    'FORUM_NAME_EMPTY'                    => 'Trebuie să introduceţi un nume pentru acest forum.',
    'FORUM_PARENT'                        => 'Forum părinte',
    'FORUM_PASSWORD'                    => 'Parolă forum',
    'FORUM_PASSWORD_CONFIRM'            => 'Confirmaţi parola forumului',
    'FORUM_PASSWORD_CONFIRM_EXPLAIN'    => 'Trebuie precizată dacă este specificată parola forumului.',
    'FORUM_PASSWORD_EXPLAIN'            => 'Definiţi o parolă pentru acest forum, folosiţi sistemul de permisiuni din preferinţe.',
    'FORUM_PASSWORD_UNSET'                => 'Elminaţi parola forumului',
    'FORUM_PASSWORD_UNSET_EXPLAIN'        => 'Verificaţi aici daca doriţi să eliminaţi parola forumului.',
    'FORUM_PASSWORD_OLD'                => 'Parola forumului foloseşte o metodă hashing veche şi ar trebui schimbată.',
    'FORUM_PASSWORD_MISMATCH'            => 'Parolele specificate nu s-au potrivit.',
    'FORUM_PRUNE_SETTINGS'                => 'Setările de ştergere a forumului',
    'FORUM_RESYNCED'                    => 'Forumul "%s" resincronizat cu succes',
    'FORUM_RULES_EXPLAIN'                => 'Regulile forumului sunt afişate în fiecare pagină a forumurilor afişate.',
    'FORUM_RULES_LINK'                    => 'Legătură către regulile forumului',
    'FORUM_RULES_LINK_EXPLAIN'            => 'Aici puteţi să specificaţi adresa URL-ul a paginii/mesajului care conţine regulile forumului dumneavoastră. Această setare va suprascrie regulile text ale forumului pe care le-aţi specificat.',
    'FORUM_RULES_PREVIEW'                => 'Previzualizare reguli forum',
    'FORUM_RULES_TOO_LONG'                => 'Regulile forumului trebuie să conţină mai puţin de 400 caractere.',
    'FORUM_SETTINGS'                    => 'Setări forum',
    'FORUM_STATUS'                        => 'Stare forum',
    'FORUM_TOPICS_PAGE'                    => 'Subiecte pe pagină',
    'FORUM_STYLE'                        => 'Stil forum',
    'FORUM_TOPICS_PAGE_EXPLAIN'            => 'Dacă nu este zero, această valoare va suprascrie setarea iniţială specificată pentru mesaje pe pagină.',
    'FORUM_TYPE'                        => 'Tip forum',
    'FORUM_UPDATED'                        => 'Informaţiile forumului au fost actualizate cu succes.',
    
    'FORUM_WITH_SUBFORUMS_NOT_TO_LINK'        => 'Vreţi să schimbaţi un forum în care se poate scrie care are are subforumuri într-un link. Vă rugăm să mutaţi toate subforumurile în afara acestui forum înainte de a continua, deoarece dupa ce schimbaţi într-o legatură nu o să mai puteţi vedea subforumurile curente conectate acestui forum.',

    'GENERAL_FORUM_SETTINGS'    => 'Setări generale ale forumului',

    'LINK'                    => 'Legătură',
    'LIST_INDEX'            => 'Afişează subforumul în legenda forumului părinte',
    'LIST_INDEX_EXPLAIN'    => 'Afişează acest forum în index sau în altă parte ca şi o legătură în cadrul legendei forumului părinte dacă opţiunea „Afişează subforumurile în legendă” este activată în forumul parinte.',
    'LIST_SUBFORUMS'			=> 'Afişează subforumurile în legendă',
	  'LIST_SUBFORUMS_EXPLAIN'	=> 'Afişează subforumurile acestui forum în index sau în altă parte ca şi o legătură în cadrul legendei dacă opţiunea acestora „Afişează subforumul în legenda forumului părinte” este activată.',
    'LOCKED'                => 'Închis',

    'MOVE_POSTS_NO_POSTABLE_FORUM'    => 'Forumul selectat pentru mutarea mesajelor în el nu permite operaţiuni de scriere. Vă rugăm să selectaţi un forum ce permite operaţiuni de scriere.',
    'MOVE_POSTS_TO'        => 'Mută mesaje în',
    'MOVE_SUBFORUMS_TO'    => 'Mută subforumuri în',

    'NO_DESTINATION_FORUM'            => 'Nu aţi specificat forumul pentru a muta conţinutul',
    'NO_FORUM_ACTION'                => 'Nicio acţiune nu a fost definită în priviţa conţinutului forumului',
    'NO_PARENT'                        => 'Niciun părinte',
    'NO_PERMISSIONS'                => 'Nu copia permisiunile',
    'NO_PERMISSION_FORUM_ADD'        => 'Nu aveţi permisiunile necesare pentru a adăuga forumuri.',
    'NO_PERMISSION_FORUM_DELETE'    => 'Nu aveţi permisiunile necesare pentru a şterge forumuri.',

    'PARENT_IS_LINK_FORUM'        => 'Părintele specificat este un forum legătură. Forumurile legătură nu pot ţine alte forumuri, vă rugăm să specificaţi o categorie sau un forum ca şi forum părinte.',
    'PARENT_NOT_EXIST'            => 'Forumul părinte nu există.',
    'PRUNE_ANNOUNCEMENTS'        => 'Şterge anunţurile',
    'PRUNE_STICKY'                => 'Şterge mesajele lipicioase',
    'PRUNE_OLD_POLLS'            => 'Şterge chestionarele vechi',
    'PRUNE_OLD_POLLS_EXPLAIN'    => 'Şterge subiectele cu chestionare în care nu s-a votat după limita de zile.',

    'REDIRECT_ACL'    => 'Acum puteţi să %sspecificaţi permisiunile%s pentru acest forum.',

    'SYNC_IN_PROGRESS'            => 'Sincronizare forum',
    'SYNC_IN_PROGRESS_EXPLAIN'    => 'Momentan se resincronizează subiectele din zona %1$d/%2$d.',

    'TYPE_CAT'            => 'Categorie',
    'TYPE_FORUM'        => 'Forum',
    'TYPE_LINK'            => 'Legatură',

    'UNLOCKED'            => 'Deschis',
));

?>