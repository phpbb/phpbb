<?php
/**
*
* acp_email [Română]
*
* @package language
* @version $Id: email.php,v 1.16 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: email.php,v 1.16 2007/12/29 17:05:00 www.phpbb.ro (Aliniuz) Exp $
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

// Email settings
$lang = array_merge($lang, array(
    'ACP_MASS_EMAIL_EXPLAIN'        => 'Aici puteţi trimite un mesaj prin e-mail fie tuturor utilizatorilor, fie utilizatorilor asociaţi unui anumit grup <strong>care au specificat că optează să primească mesaje în grup</strong>. Pentru a efectua această operaţiune, un mesaj electronic va fi trimis la adresa de e-mail administrativă specificată, cu o copie oarbă trimisă tuturor recipienţilor. Setarea standard este ca doar 20 destinatari să poată fi incluşi într-un astfel de mesaj, pentru mai mulţi destinatari se vor trimite mai multe mesaje electronice. Dacă trimiteţi mesajul unui grup larg de utilizatori, trebuie să aveţi răbdare şi grijă să nu închideţi pagina. Este normal ca un mesaj în masă să dureze mult, iar după ce procesul se va finaliza, veţi fi anunţat prin e-mail',
    'ALL_USERS'                        => 'Toţi utilizatorii',

    'COMPOSE'                => 'Compune',

    'EMAIL_SEND_ERROR'        => 'Au apărut una sau mai multe erori în timpul trimiterii mesajului electronic. Verificaţi %sJurnalul de erori%s pentru mesaje detaliate de eroare.',
    'EMAIL_SENT'            => 'Mesajul dumneavoastră a fost trimis.',
    'EMAIL_SENT_QUEUE'        => 'Mesajul dumneavoastră a fost păstrat pentru trimitere.',

    'LOG_SESSION'            => 'Înregistraţi sesiunea de trimitere a mesajului electronic în jurnalul critic',

    'SEND_IMMEDIATELY'        => 'Trimite imediat',
    'SEND_TO_GROUP'            => 'Trimite către grupul',
    'SEND_TO_USERS'            => 'Trimite către utilizatorii',
    'SEND_TO_USERS_EXPLAIN'    => 'Specificând numele aici, veţi suprascrie orice grup selectat mai sus. Introduceţi fiecare nume de utilizator pe o linie nouă.',
    
    'MAIL_BANNED'			=> 'Notificare utilizatori interziși',
	'MAIL_BANNED_EXPLAIN'	=> 'Aici puteți specifica dacă utilizatorii interziși vor fi, de asemenea, notificați când trimiteți un mesaj în bloc către un grup.',
	'MAIL_HIGH_PRIORITY'    => 'Mare',
    'MAIL_LOW_PRIORITY'        => 'Mică',
    'MAIL_NORMAL_PRIORITY'    => 'Normală',
    'MAIL_PRIORITY'            => 'Prioritatea mesajului electronic',
    'MASS_MESSAGE'            => 'Mesajul dumneavoastră',
    'MASS_MESSAGE_EXPLAIN'    => 'Reţineţi faptul că puteţi introduce doar text simplu. Toate semnele vor fi şterse înainte de trimitere.',
    
    'NO_EMAIL_MESSAGE'        => 'Trebuie să introduceţi un mesaj.',
    'NO_EMAIL_SUBJECT'        => 'Trebuie să specificaţi un subiect pentru mesajul dumneavoastră.',
));

?>