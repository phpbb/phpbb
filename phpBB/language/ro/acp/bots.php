<?php
/** 
*
* acp_bots [Română]
*
* @package language
* @version $Id: bots.php,v 1.12 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: bots.php,v 1.12 2007/12/29 17:05:00 www.phpbb.ro (NemoXP) Exp $
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

// Bot settings
$lang = array_merge($lang, array(
	'BOTS'				=> 'Administrare roboţi',
	'BOTS_EXPLAIN'		=> '&quot;Boţii&quot;, &quot;păienjenii&quot; sau &quot;crawlerii&quot; sunt agenţi automaţi folosiţi în special de către motoarele de căutare pentru a-şi reactualiza bazele de date. Deoarece aceşti agenţi se folosesc de sesiuni HTTP, pot deregla contoarele de vizitatori, pot mări traficul şi uneori pot să nu indexeze site-urile corect. Aici puteţi defini un tip special de utilizator pentru a depăşi aceste situaţii.',
	'BOT_ACTIVATE'		=> 'Activaţi',
	'BOT_ACTIVE'		=> 'Robot activ',
	'BOT_ADD'			=> 'Adăugare robot',
	'BOT_ADDED'			=> 'Robot adăugat cu succes.',
	'BOT_AGENT'			=> 'Potrivire agent',
	'BOT_AGENT_EXPLAIN'	=> 'O secvenţă de text care se potriveşte cu agentul robotului, potrivirile parţiale sunt permise.',
	'BOT_DEACTIVATE'	=> 'Dezactivare',
	'BOT_DELETED'		=> 'Robot şters cu succes.',
	'BOT_EDIT'			=> 'Modificare roboţi',
	'BOT_EDIT_EXPLAIN'	=> 'Aici puteţi adăuga sau modifica roboţii deja existenti. Puteţi defini o secvenţă de text a agentului şi/sau una sau mai multe adrese de IP (sau clase de IPuri) pentru potrivire. Aveţi grijă când definiţi secvenţele de text ale agentului sau adresele de IP. Puteţi desemenea specifica un stil şi limba în care robotul va vedea forumul. Selectând un stil simplu pentru roboţi, vă poate ajuta să reduceţi traficul de pe forum. Reţineţi să specificaţi permisiile necesare pentru grupul Roboţi.',
	'BOT_LANG'			=> 'Limba robotului',
	'BOT_LANG_EXPLAIN'	=> 'Limba în care robotul va vedea forumul.',
	'BOT_LAST_VISIT'	=> 'Ultima vizită',
	'BOT_IP'			=> 'Adresa IP a robotului',
	'BOT_IP_EXPLAIN'	=> 'Rezultate parţiale sunt permise, separaţi adresele prin virgulă.',
	'BOT_NAME'			=> 'Numele robotului',
	'BOT_NAME_EXPLAIN'	=> 'Folosit doar pentru informaţii proprii.',
	'BOT_NAME_TAKEN'	=> 'Numele este deja folosit pe forum şi puteţi/nu puteţi să îl folosiţi pentru Robot.',
	'BOT_NEVER'			=> 'Niciodată',
	'BOT_STYLE'			=> 'Sitlul robotului',
	'BOT_STYLE_EXPLAIN'	=> 'Stilul forumului pe care îl va vedea robotul.',
	'BOT_UPDATED'		=> 'Setările robotului au fost actualizate cu succes.',

	'ERR_BOT_AGENT_MATCHES_UA'	=> 'Agentul introdus coincide cu cel folosit deja. Vă rugăm să modificaţi setările pentru acest robot.',
	'ERR_BOT_NO_IP'				=> 'Adresa IP introdusă este incorectă sau nu s-a putut stabili o legătură.',
	'ERR_BOT_NO_MATCHES'		=> 'Trebuie să introduceţi cel puţin unul dintre agenţi sau adresa IP pentru potrivirea robotului.',

	'NO_BOT'		=> 'Nu a fost găsit niciun robot cu ID-ul specificat.',
	'NO_BOT_GROUP'	=> 'Nu se poate găsi grupul special pentru roboţi.',
));

?>