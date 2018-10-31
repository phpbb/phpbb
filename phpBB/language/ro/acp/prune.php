<?php
/**
*
* acp_prune [Română]
*
* @package language
* @version $Id: prune.php,v 1.14 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: prune.php,v 1.14 2007/12/29 17:05:00 www.phpbb.ro (shara21jonny) Exp $
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

// User pruning
$lang = array_merge($lang, array(
    'ACP_PRUNE_USERS_EXPLAIN'    => 'Aici puteţi şterge (sau dezactiva) utilizatorii din forumul propriu. Conturile pot fi filtrate printr-o varietate de moduri: după numărul de mesaje, ultima activitate, etc. Fiecare dintre aceste criterii poate fi combinat pentru a limita conturile ce vor fi afectate, de exemplu puteţi şterge utilizatorii cu mai puţin de 10 mesaje care erau inactivi după 2002-01-01 . Alternativ, puteţi specifica direct în căsuţa de text o listă a utilizatorilor, orice criteriu introdus fiind ignorat. Aveţi grijă cu această facilitate! Odată ce un utilizator este şters, nu-l mai puteţi recupera.',

    'DEACTIVATE_DELETE'            => 'Dezactivaţi sau ştergeţi',
    'DEACTIVATE_DELETE_EXPLAIN'    => 'Alegeţi între a dezactiva utilizatorii sau a-i şterge în totalitate, reţineţi că utilizatorii şterşi nu mai pot fi restauraţi!',
    'DELETE_USERS'                => 'Ştergeţi',
    'DELETE_USER_POSTS'            => 'Ştergeţi mesajele utilizatorilor eliminaţi',
    'DELETE_USER_POSTS_EXPLAIN' => 'Şterge mesajele scrise de utlizatorii eliminaţi, nu are niciun efect daca utilizatorii sunt dezactivaţi.',

    'JOINED_EXPLAIN'            => 'Introduceţi o dată în formatul <kbd>YYYY-MM-DD</kbd>.',

    'LAST_ACTIVE_EXPLAIN'        => 'Introduceţi o dată în formatul <kbd>YYYY-MM-DD</kbd>. Introduceţi <kbd>0000-00-00</kbd> pentru a şterge utilizatorii care nu s-au autentificat, condiţiile <em>Înainte</em> şi <em>După</em> vor fi ignorate.',

    'PRUNE_USERS_LIST'                => 'Utilizatorii care vor fi şterşi',
    'PRUNE_USERS_LIST_DELETE'        => 'Următoarele conturi vor fi eliminate conform cu criteriul selectat pentru ştergerea utilizatorilor.',
    'PRUNE_USERS_LIST_DEACTIVATE'    => 'Următoarele conturi vor fi dezactivate conform cu criteriul selectat pentru ştergerea utilizatorilor.',

    'SELECT_USERS_EXPLAIN'        => 'Precizaţi aici numele utilizatorilor, vor fi folosite de preferinţă pentru criteriile de mai sus. Fondatorii nu pot fi şterşi.',

    'USER_DEACTIVATE_SUCCESS'    => 'Utilizatorii selectaţi au fost dezactivaţi cu succes.',
    'USER_DELETE_SUCCESS'        => 'Utilizatorii selectaţi au fost şterşi cu succes.',
    'USER_PRUNE_FAILURE'        => 'Niciun utilizator nu se potriveşte pentru criteriul selectat.',
    
    'WRONG_ACTIVE_JOINED_DATE'    => 'Data introdusă este greşită, este recunoscută în formatul <kbd>YYYY-MM-DD</kbd>.',
));

// Forum Pruning
$lang = array_merge($lang, array(
    'ACP_PRUNE_FORUMS_EXPLAIN'    => 'Acestă operaţie va şterge orice subiect la care nu s-a răspuns sau nu s-a vizualizat în numărul specificat de zile. Dacă nu introduceţi un număr, atunci toate subiectele vor fi şterse. Iniţial, nu se vor elimina subiectele în care chestionarele sunt în continuare active, nici cele cu anunţuri sau mesaje importante.',

    'FORUM_PRUNE'        => 'Ştergeţi forum',

    'NO_PRUNE'            => 'Niciun forum şters',

    'SELECTED_FORUM'    => 'Forum selectat',
    'SELECTED_FORUMS'    => 'Forumuri selectate',

    'POSTS_PRUNED'                    => 'Mesaje şterse',
    'PRUNE_ANNOUNCEMENTS'            => 'Anunţuri şterse',
    'PRUNE_FINISHED_POLLS'            => 'Şterge chestionarele închise',
    'PRUNE_FINISHED_POLLS_EXPLAIN'    => 'Şterge subiectele ce conţin chestionare finalizate.',
    'PRUNE_FORUM_CONFIRM'            => 'Sunteţi sigur că vreţi să curăţaţi forumurile selectate conform cu setările specificate? Odată eliminate, nu există cale de a recupera mesajele şi subiectele şterse.',
    'PRUNE_NOT_POSTED'                => 'Zile de la ultimul mesaj',
    'PRUNE_NOT_VIEWED'                => 'Zile de la ultima vizualizuare',
    'PRUNE_OLD_POLLS'                => 'Şterge chestionarele vechi',
    'PRUNE_OLD_POLLS_EXPLAIN'        => 'Şterge subiectele ce conţin chestionare în care nu s-a votat în numărul de zile specificat.',
    'PRUNE_STICKY'                    => 'Şterge mesajele lipicioase',
    'PRUNE_SUCCESS'                    => 'Curăţarea forumurilor a fost efectuată cu succes',

    'TOPICS_PRUNED'        => 'Subiecte şterse',
));

?>