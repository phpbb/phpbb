<?php
/** 
*
* help_bbcode [Română]
*
* @package language
* @version $Id: help_bbcode.php,v 1.27 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: help_bbcode.php,v 1.27 2008/01/03 15:02:00 www.phpbb.ro (shara21jonny) Exp $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
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

$help = array(
	array(
		0 => '--',
		1 => 'Introducere'
	),
	array(
		0 => 'Ce este codul BB?',
		1 => 'Codul BB este o implementare specială a HTML-ului. Dacă puteţi folosi codul BB sau nu în mesajele dumneavoastră, este alegerea administratorului. În plus, puteţi dezactiva codul BB de la mesaj la mesaj din formularul de publicare. Codul BB este similar cu HTML-ul, balizele (tag-urile) sunt scrise între paranteze pătrate [ şi ] decât între &lt; şi &gt; şi oferă un control mai bun asupra a ce şi cum este afişat. În funcţie de stilul pe care îl folosiţi, puteţi descoperi că adăugarea de cod BB la mesajele dumneavoastră este mai uşoară printr-o interfaţă de butoane deasupra zonei de scris din formularul de publicare. Chiar şi aşa probabil că veţi găsi acest ghid folositor.'
	),
	array(
		0 => '--',
		1 => 'Formatarea textului'
	),
	array(
		0 => 'Cum să creaţi text îngroşat, cursiv (italic) şi subliniat',
		1 => 'Codul BB include balize (taguri) pentru a vă permite să schimbaţi rapid stilul textului dumneavoastră. Acest lucru poate fi obţinut în următoarele moduri: <ul><li>Pentru a face o parte de text ingroşat (bold), includeţi-o între <strong>[b][/b]</strong>, spre exemplu:<br /><br /><strong>[b]</strong>Salut<strong>[/b]</strong><br /><br /> va deveni <strong>Salut</strong></li><li>Pentru subliniere, folosiţi <strong>[u][/u]</strong>, spre exemplu:<br /><br /><strong>[u]</strong>Bună dimineaţa<strong>[/u]</strong><br /><br />devine <span style="text-decoration: underline">Bună dimineaţa</span></li><li>Pentru a scrie cu font cursiv (italic), folosiţi <strong>[i][/i]</strong>, spre exemplu:<br /><br /><strong>[i]</strong>Super!<strong>[/i]</strong><br /><br />va deveni <i>Super!</i></li></ul>'
	),
	array(
		0 => 'Cum să schimbaţi culoarea textului sau mărimea',
		1 => 'Pentru a schimba culoarea sau mărimea textului dumneavoastră, puteţi folosi mai multe balize (taguri). Ţineţi minte că felul cum apare mesajul depinde de browser-ul şi sistemul celui ce vizualizează: <ul><li>Schimbarea culorii textului se face prin trecerea între <strong>[color=][/color]</strong>. Puteţi specifica fie o culoare cunoscută, în limba engleză, (<i>red</i> pentru roşu, <i>blue</i> pentru albastru, <i>yellow</i> pentru galben) sau un triplet hexazecimal, ca #FFFFFF, #000000. Spre exemplu, pentru a scrie cu roşu, veţi putea folosi:<br /><br /><strong>[color=red]</strong>Salut!<strong>[/color]</strong><br /><br />sau<br /><br /><strong>[color=#FF0000]</strong>Salut!<strong>[/color]</strong><br /><br />Ambele vor avea ca rezultat <span style="color:red">Salut!</span></li><li>Schimbarea mărimii textului este făcută în acelaşi fel, folosind <strong>[size=][/size]</strong>. Această baliză depinde de stilul pe care l-a selectat utilizatorul, dar formatul recomandat este o valoare numerică reprezentând mărimea textului în procente, pornind de la 20 (extrem de mic) şi până la 200 (foarte mare), mărime standard. Spre exemplu:<br /><br /><strong>[size=30]</strong>MIC<strong>[/size]</strong><br /><br />în general va avea ca rezultat <span style="font-size:30%;">MIC</span><br /><br />în vreme ce:<br /><br /><strong>[size=200]</strong>ENORM!<strong>[/size]</strong><br /><br />va fi <span style="font-size:200%;">ENORM!</span></li></ul>'
	),
	array(
		0 => 'Pot combina balizele (tag-urile) de formatare?',
		1 => 'Desigur. Spre exemplu, pentru a atrage atenţia cuiva, aţi putea să scrieţi:<br /><br /><strong>[size=200][color=red][b]</strong>PRIVEŞTE-MĂ!<strong>[/b][/color][/size]</strong><br /><br />va afişa <span style="color:red;font-size:200%;"><strong>PRIVEŞTE-MĂ!</strong></span><br /><br />Totuşi, nu vă recomandăm să scrieti prea mult text astfel! Ţineţi minte că depinde de dumneavoastră să vă asiguraţi că balizele sunt închise corect. Spre exemplu, următoarea secvenţă este incorectă:<br /><br /><strong>[b][u]</strong>Aşa este greşit<strong>[/b][/u]</strong>'
	),
	array(
		0 => '--',
		1 => 'Citate si text cu lăţime fixă'
	),
	array(
		0 => 'Citarea textului în răspunsuri',
		1 => 'Există două modalităţi de a cita textul, cu referinţă şi fără.<ul><li>Când utilizaţi funcţia de citare la răspunsul mesajului, ar trebui să observaţi că mesajul respectiv este adăugat în fereastra de publicare, inclus într-un bloc <strong>[quote=&quot;&quot;][/quote]</strong>. Această metodă vă va permite să îl citaţi cu referinţă la o persoană sau orice altceva doriţi să scrieţi! Spre exemplu, pentru a cita o parte de text scrisă de Dl. Ionescu, aţi scrie:<br /><br /><strong>[quote=&quot;Dl. Ionescu&quot;]</strong>Textul scris de Dl. Ionescu<strong>[/quote]</strong><br /><br />La rezultat se va adăuga automat &quot;Dl. Ionescu a scris:&quot; înainte de textul actual. Ţineţi minte că <strong>trebuie</strong> să includeţi ghilimelele &quot;&quot; în jurul numelui pe care îl citaţi. Acestea nu sunt opţionale.</li><li>A doua metodă vă permite să citaţi fără un autor. Pentru a folosi acest lucru, introduceţi textul între balizele <strong>[quote][/quote]</strong>. Când veţi vizaliza mesajul, va arăta pur şi simplu textul în blocul de citat.</li></ul>'
	),
	array(
		0 => 'Generarea de cod sau de text cu mărime fixă',
		1 => 'Dacă doriţi să scrieţi o bucată de cod sau - de fapt - orice altceva care are nevoie de o lăţime fixă, cum ar fi un font de tip Courier, ar trebui să introduceţi textul între balize <strong>[code][/code]</strong>, spre exemplu:<br /><br /><strong>[code]</strong>echo &quot;O bucată de cod&quot;;<strong>[/code]</strong><br /><br />Toate formatările folosite între balizele <strong>[code][/code]</strong> sunt reţinute când citiţi mesajul mai târziu. Sintaxa PHP highlighting poate fi activată folosind <strong>[code=php][/code]</strong> şi este recomandată când scrieţi cod PHP pentru că poate fi citit cu uşurinţă.'
	),
	array(
		0 => '--',
		1 => 'Generarea listelor'
	),
	array(
		0 => 'Crearea unei liste neordonate',
		1 => 'Codul BB include două tipuri de liste, neordonate şi ordonate. În mare, sunt la fel cu echivalentele lor HTML. O listă neordonată afişează fiecare obiect din listă secvenţial, adăugându-le un alineat şi un caracter <i>bullet</i>. Pentru a crea o listă neordonată, folosiţi <strong>[list][/list]</strong> şi definiţi fiecare obiect din listă folosind <strong>[*]</strong>. Spre exemplu, pentru a vă lista culorile preferate, aţi putea folosi:<br /><br /><strong>[list]</strong><br /><strong>[*]</strong>roşu<br /><strong>[*]</strong>albastru<br /><strong>[*]</strong>galben<br /><strong>[/list]</strong><br /><br />Aceasta va genera următoarea listă:<ul><li>roşu</li><li>albastru</li><li>galben</li></ul>'
	),
	array(
		0 => 'Crearea unei liste ordonate',
		1 => 'Al doilea tip de listă, lista ordonată, vă oferă controlul asupra a ceea ce este afişat înaintea fiecărui obiect. Pentru a crea o listă ordonată, folosiţi <strong>[list=1][/list]</strong> pentru o listă numerică sau <strong>[list=a][/list]</strong> pentru o listă alfabetică. Ca şi la listele neordonate, obiectele sunt indicate folosind <strong>[*]</strong>. Spre exemplu:<br /><br /><strong>[list=1]</strong><br /><strong>[*]</strong>Mergi la magazin<br /><strong>[*]</strong>Cumpără un calculator nou<br /><strong>[*]</strong>Ţipă la calculator când se blochează<br /><strong>[/list]</strong><br /><br />va genera următoarele:<ol style="list-style-type: decimal;"><li>Mergi la magazin</li><li>Cumpără un calculator nou</li><li>Ţipă la calculator când se blochează</li></ol>Pe când pentru o listă alfabetică veţi folosi:<br /><br /><strong>[list=a]</strong><br /><strong>[*]</strong>Primul răspuns posibil<br /><strong>[*]</strong>Al doilea răspuns posibil<br /><strong>[*]</strong>Al treilea răspuns posibil<br /><strong>[/list]</strong><br /><br />având ca rezultat:<ol style="list-style-type: lower-alpha"><li>Primul răspuns posibil</li><li>Al doilea răspuns posibil</li><li>Al treilea răspuns posibil</li></ol>'
	),
		// This block will switch the FAQ-Questions to the second template column
	array(
		0 => '--',
		1 => '--'
	),
	array(
		0 => '--',
		1 => 'Crearea legăturilor'
	),
	array(
		0 => 'Legătura către alt site',
		1 => 'Codul BB oferă multe modalităţi de creare a legăturilor URI (Uniform Resource Indicators), cunoscute mai bine ca URL-uri.<ul><li>Prima din acestea foloseşte baliza <strong>[url=][/url]</strong> şi orice veţi scrie după semnul egal va determina conţinutul acelei balize să se comporte ca un URL. Spre exemplu, o legătură către phpBB.ro ar fi:<br /><br /><strong>[url=http://www.phpbb.ro/]</strong>Vizitaţi phpBB!<strong>[/url]</strong><br /><br />Rezultatul ar fi următoarea legătură: <a href="http://www.phpbb.ro/">Vizitaţi phpBB!</a>. Veţi observa că legătura se va deschide în aceeaşi fereastră sau într-o fereastră nouă depinzând de opţiunile din browserele utilizatorilor.</li><li>Dacă doriţi să fie afişat chiar URL-ul, atunci puteţi să scrieţi:<br /><br /><strong>[url]</strong>http://www.phpbb.ro/<strong>[/url]</strong><br /><br />Acesta va genera următoarea legătură: <a href="http://www.phpbb.ro/">http://www.phpbb.ro/</a></li><li>Alte facilităţi phpBB includ şi ceva numit <i>legături magice</i>, care va transforma un URL corect din punct de vedere sintactic într-o legătură fără ca dumneavoastră să specificaţi vreo baliză sau să incepeţi cu http://. Spre exemplu, dacă veţi scrie www.phpbb.ro în mesaj, acesta va deveni automat <a href="http://www.phpbb.ro/">www.phpbb.ro</a> când vizualizaţi mesajul.</li><li>Acelaşi lucru se intâmplă şi cu adresele de e-mail. Puteţi folosi o adresă explicit, spre exemplu:<br /><br /><strong>[email]</strong>cineva@domeniu.adr<strong>[/email]</strong><br /><br />care va afişa <a href="mailto:cineva@domeniu.adr">cineva@domeniu.adr</a> sau puteţi să scrieţi cineva@domeniu.adr în mesajul dumneavostră şi va fi automat convertit când îl veţi vizualiza.</li></ul>La fel ca tag-urile codului BB, puteţi folosi pentru URL-uri orice tip de tag, ca <strong>[img][/img]</strong> (citiţi punctul următor), <strong>[b][/b]</strong> etc. Ca şi în cazul balizelor de formatare, depinde de dumneavoastră să vă asiguraţi de ordinea corectă de deschidere şi închidere. Spre exemplu:<br /><br /><strong>[url=http://www.google.com/][img]</strong>http://www.google.com/intl/en_ALL/images/logo.gif<strong>[/url][/img]</strong><br /><br /><span style="text-decoration: underline;">nu</span> este corect, lucru care ar putea duce la ştergerea mesajului, aşa că aveţi mare grijă.'
	),
	array(
		0 => '--',
		1 => 'Afişarea imaginilor în mesaje'
	),
	array(
		0 => 'Adăugarea unei imagini în mesaj',
		1 => 'Codul BB include o baliză pentru includerea imaginilor în mesajele dumneavoastră. Două lucruri foarte importante trebuie ţinute minte atunci când folosiţi această baliză: mulţi utilizatori nu apreciază afişarea multor imagini într-un mesaj şi imaginea trebuie să fie deja disponibilă pe internet (nu poate exista doar pe calculatorul dumneavoastră, doar dacă nu rulaţi un server de web!). Pentru a afişa o imagine, trebuie să inchideţi URL-ul imaginii în balize <strong>[img][/img]</strong>. Spre exemplu:<br /><br /><strong>[img]</strong>http://www.google.com/intl/en_ALL/images/logo.gif<strong>[/img]</strong><br /><br />Aşa cum s-a văzut în secţiunea anterioară despre URL-uri, puteţi include o imagine într-o baliză <strong>[url][/url]</strong> dacă doriţi, spre exemplu:<br /><br /><strong>[url=http://www.google.com/][img]</strong>http://www.google.com/intl/en_ALL/images/logo.gif<strong>[/img][/url]</strong><br /><br />ar genera:<br /><br /><a href="http://www.google.com/"><img src="http://www.google.com/intl/en_ALL/images/logo.gif" alt="" /></a>'
	),
	array(
		0 => 'Adăugarea fişierelor ataşate într-un mesaj',
		1 => 'Fişierele ataşate pot acuma să fie puse în orice parte a mesajului, folosind noul cod BB <strong>[attachment=][/attachment]</strong>, dacă funcţionalitatea fişierelor ataşate a fost activată de către administratorul forumului şi dacă vi s-au atribuit permisiunile necesare să creeaţi fişiere ataşate. La scrierea mesajului există o căsuţă drop-down (respectiv un buton) pentru plasarea fişierelor ataşate în linie.'
	),
	array(
		0 => '--',
		1 => 'Diverse'
	),
	array(
		0 => 'Pot să îmi adaug propriile balize (tag-uri)?',
		1 => 'Dacă sunteţi un administrator al forumului şi aveţi permisiunile necesare, puteţi să adăugaţi coduri BB din secţiunea de coduri BB personalizabile.'
	)
);

?>