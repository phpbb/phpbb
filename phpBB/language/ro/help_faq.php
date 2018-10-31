<?php
/**
*
* help_faq [Română]
*
* @package language
* @version $Id: help_faq.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: help_faq.php 8479 2008-02-23 20:49:11 www.phpbb.ro (Ro Silviu) Exp $
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
        1 => 'Probleme de autentificare şi înregistrare'
    ),
    array(
        0 => 'De ce nu mă pot autentifica?',
        1 => 'Acest lucru se poate întâmpla din mai multe motive. În primul rând verificaţi dacă aţi specificat corect numele de utilizator şi parola. Dacă da, contactaţi un administrator al forumului ca să fiţi sigur că nu aveţi interdicţie pe forum. De asemenea, este posibil ca proprietarul site-ului să aibă o eroare de configurare ce trebuie reparată.'
    ),
    array(
        0 => 'De ce trebuie să mă înregistrez?',
        1 => 'S-ar putea să nu fie nevoie, depinde de administratorul forumului dacă e nevoie să vă înregistraţi sau nu pentru a scrie mesaje. Oricum, înregistrarea vă va oferi acces la opţiuni care nu sunt disponibile vizitatorilor cum ar fi imagini asociate, mesaje private, trimiterea de email-uri altor utilizatori, înscrierea în grupuri  etc. Durează doar câteva momente, aşa că vă recomandăm să vă înregistraţi.'
    ),
    array(
        0 => 'De ce sunt scos afară din forum automat?',
        1 => 'Dacă nu activaţi opţiunea <i>Autentifică-mă automat la fiecare vizită</i> atunci când vă autentificaţi, veţi fi autentificat doar pentru o perioadă de timp prestabilită. Această măsură previne ca altcineva să se folosească de contul dumneavoastră. Pentru a rămâne autentificat tot timpul, bifaţi această opţiune la autentificare. Acest lucru nu este recomandat dacă accesaţi forumul de la un calculator public, cum ar fi de la o bibliotecă, internet cafe, laborator de calculatoare (la liceu/universitate etc.). Dacă nu vedeţi această opţiune, înseamnă că a fost dezactivată de către un adminstrator al forumului.'
    ),
    array(
        0 => 'Cum fac să nu îmi apară numele de utilizator în lista de utilizatori conectaţi?',
        1 => 'În profilul dumneavoastră veţi găsi o opţiune <i>Ascunde indicatorul de conectare</i>. Dacă veţi seta această opţiune pe <i>Da</i> veţi fi vizibil doar pentru administratori, moderatori şi pentru dumneavoastră. Veţi fi numărat ca utilizator ascuns.'
    ),
    array(
        0 => 'Mi-am pierdut parola!',
        1 => 'Nu intraţi în panică! Parola dumneavoastră nu poate fi refăcută, dar poate fi resetată uşor. Pentru a realiza acest lucru, mergeţi la pagina de autentificare şi folosiţi legătura <i>Am uitat parola</i>. Urmaţi instrucţiunile şi în scurt timp ar trebui să vă puteţi autentifica.'
    ),
    array(
        0 => 'Sunt înregistrat dar nu mă pot autentifica!',
        1 => 'Mai întâi verificaţi dacă aţi specificat corect numele de utilizator şi parola. Dacă acestea sunt corecte, atunci autentificarea nu este posibilă din cauză că nu aveți activat contul, fiind posibilă una din următoarele situații: <br /> 1. Este activată opţiunea COPPA şi aţi specificat la înregistrare că aveţi sub 13 ani, fiind necesar să urmaţi instrucţiunile primite prin email pentru activarea contului.   <br /> 2. Forumul pe care v-ati inregistrat solicită ca utilizatorii noi să fie activaţi; contul poate fi activat fie de către dumneavoastră personal, fie de către un administrator înainte de a vă putea autentifica, in funcție de configurările stabilite de administrator. Această informaţie v-a fost prezentată la înregistrare. <br />3. Aţi furnizat o adresă de email invalidă sau mesajul a fost prelucrat de un filtru de spam, acestea fiind unul din motivele pentru care emailul cu codul de activare al contului nu a ajuns la dumneavoastră. Daca sunteţi sigur că adresa de e-mail folosită este corectă atunci încercaţi să contactaţi un administrator.'
    ),
    array(
        0 => 'M-am înregistrat cu ceva timp în urmă dar acum nu mă mai pot autentifica?!',
        1 => 'Este posibil ca un administrator să fi dezactivat sau să fi şters contul dumneavoastră dintr-un motiv sau altul. De asemenea, multe forumuri şterg periodic utilizatorii ce nu au fost activi o perioadă lungă de timp pentru a reduce dimensiunea bazei de date. Dacă s-a întâmplat acest lucru, încercaţi să vă înregistraţi din nou şi să vă implicaţi mai mult în discuţii.'
    ),
    array(
        0 => 'Ce este COPPA?',
        1 => 'COPPA, sau "Child Online Privacy and Protection Act of 1998" (Actul pentru protecţia copiilor pe internet din 1998) este o lege din Statele Unite care solicită site-urilor ce pot colecta informaţii personale de la minorii sub vârsta 13 ani să obţină acordul scris al părinţilor sau altă metodă legală prin care tutorele legal îşi dă acordul pentru colectarea informaţiilor personale de la minorul cu vârsta sub 13 ani. Dacă nu sunteţi sigur dacă acest lucru este aplicabil dumneavoastră - ca un utilizator ce se înregistrează - sau acestui site pe care încercaţi să vă înregistraţi, contactaţi un consilier legal pentru asistenţă. Reţineţi că echipa phpBB nu poate furniza sfaturi juridice şi nu este un punct de contact pentru implicaţii legale de orice fel cu excepţia celor specificate mai jos.',
    ),
    array(
        0 => 'De ce nu mă pot înregistra?',
        1 => 'Este posibil ca proprietarul site-ului să fi interzis adresa IP de pe care intraţi sau să fi dezactivat numele de utilizator pe care încercaţi să-l înregistraţi. De asemenea, înregistrarea de conturi ar putea fi dezactivate de către proprietarul site-ului pentru a preveni noile înregistrări. Contactaţi un administrator al forumului pentru asistenţă.',
    ),
    array(
        0 => 'Ce face opţiunea „Şterge toate cookie-urile forumului”?',
        1 => 'Această opţiune va şterge toate cookie-urile create de către phpBB care vă ţin autentificat pe forum. De asemenea, permite funcţionalităţi ca de exemplu citirea urmei dacă acest lucru a fost activat de către proprietarul forumului. Dacă aveţi probleme cu conectarea sau deconectarea în/din forum, ştergerea cookie-urilor forumului poate ajuta într-o astfel de sitaţie',
    ),
    array(
        0 => '--',
        1 => 'Setările şi preferinţele utilizatorului'
    ),
    array(
        0 => 'Cum îmi schimb setările?',
        1 => 'Toate setările dumneavoastră (dacă sunteţi înregistrat) sunt păstrate în baza de date. Pentru a le modifica, folosiţi Panoul utilizatorului; în general un link poate fi găsit în partea superioară a paginilor forumului. Acest lucru vă va permite să vă schimbaţi toate setările şi preferinţele.'
    ),
    array(
        0 => 'Orele nu sunt corecte!',
        1 => 'S-ar putea ca dumneavoastră să vedeţi orele afişate dintr-o zonă cu fus orar diferit faţă de cea în care sunteţi. Dacă este aşa, folosiţi Panoul utilizatorului pentru a modifica fusul orar în concordanţă cu zona în care vă aflaţi, cum ar fi Bucureşti, Londra, Paris, etc. Reţineţi că schimbarea zonei de fus orar, ca majoritatea setărilor, poate fi făcută doar de către utilizatorii înregistraţi. Dacă nu sunteţi înregistrat, acesta este un moment bun să o faceţi.'
    ),
    array(
        0 => 'Am schimbat zona de fus orar, dar ora tot este greşită!',
        1 => 'Dacă sunteţi sigur că aţi setat zona de fus orar şi ora de vară/DST corect dar ora înca este încă incorectă, atunci tipul setat pe ceasul serverului este incorect. Vă rugăm să contactaţi un administrator pentru a corecta problema.'
    ),
    array(
        0 => 'Limba mea nu este în listă!',
        1 => 'Fie administratorul nu a instalat limba dumneavoastră sau nimeni nu a tradus încă acest forum în limba dumneavoastră. Încercaţi să-l întrebaţi pe administratorul forumului dacă poate instala limba de care aveţi nevoie. Dacă pachetul de limbă nu există, sunteţi liber să creaţi o nouă traducere. Mai multe informaţii pot fi gasite pe site-ul grupului phpBB (folosiţi link-ul din partea inferioară a paginilor forumului)'
    ),
    array(
        0 => 'Cum pot afişa o imagine lângă numele meu de utilizator?',
        1 => 'Sunt două imagini ce pot apărea lângă numele de utilizator când vizualizaţi mesaje. Una dintre ele poate fi o imagine asociată cu rangul dumneavoastră, în general acestea luând forma de stele, blocuri sau puncte indicând câte mesaje aţi scris sau statutul dumneavoastră pe forum. Cealaltă, de obicei o imagine mai mare, este cunoscută sub numele de avatar (imagine asociată) şi este, în general, unică sau personală fiecărui utilizator. Administratorul forumului decide dacă să activeze imaginile asociate şi are posibilitatea de a alege modalitatea prin care imaginile asociate pot fi folosite. Dacă nu puteţi folosi imaginile asociate, atunci contactaţi un administrator al forumului şi întrebaţi-l despre motivele care au dus la această decizie.'
    ),
    array(
        0 => 'Care este rangul meu şi cum il pot schimba?',
        1 => 'Rangurile, care apar sub numele dumneavoastră de utilizator, indică numărul de mesaje pe care le-aţi scris sau identifică anumiţi utilizatori (de exemplu moderatorii şi administratorii). În general, nu puteţi schimba direct denumirea rangurilor forumului faţă de cum au fost specificate de către administratorul forumului. Vă rugăm, să nu abuzaţi de forum scriind mesaje inutile doar pentru a vă creşte rangul. Majoritatea forumurilor nu vor tolera acest lucru şi moderatorii sau administratorii vă vor scădea pur şi simplu numărul de mesaje scrise.'
    ),
    array(
        0 => 'De ce când folosesc legătura de email a unui utilizator îmi cere să mă autentific?',
        1 => 'Doar utilizatorii înregistraţi pot trimite mesaje altor utilizatori prin formularul încorporat de mail şi doar dacă administratorul a activat această facilitate. Acest lucru se întâmplă pentru a preveni folosirea maliţioasă a sistemului de mesagerie de către utilizatorii anonimi.'
    ),
    array(
        0 => '--',
        1 => 'Probleme de scriere/publicare a mesajelor'
    ),
    array(
        0 => 'Cum deschid un subiect în forum?',
        1 => 'Pentru a deschide un subiect nou în forum, apăsaţi pe butonul specific, fie din forum, fie pe ecranul cu subiecte. Este posibil să vi se ceară să vă înregistraţi înainte de a scrie un mesaj. Facilităţile care vă sunt disponibile sunt trecute în partea de jos a ecranului. Exemplu: Puteţi crea subiecte noi în acest forum, Puteţi vota în sondaje, etc.'
    ),
    array(
        0 => 'Cum pot modifica sau şterge un mesaj?',
        1 => 'În afara cazului în care sunteţi administratorul sau moderatorul forumului, puteţi modifica sau şterge doar propriile mesaje. Puteţi modifica un mesaj - uneori doar pentru o perioadă scurtă după publicare - apăsând butonul <i>Modifică</i> asociat mesajulului respectiv. Dacă cineva a răspuns la mesaj, veţi observa o mică secţiune de text sub mesaj când reveniţi la subiect care arată de câte ori aţi modificat acel mesaj împreună cu data şi ora modificării. Aceasta va apărea doar dacă cineva a răspuns la subiect; nu va apărea dacă un moderator sau administrator a modificat mesajul, aceştia ar trebui să lase un mesaj în care să spună ce şi de ce au modificat. Reţineţi că utilizatorii normali nu pot şterge un mesaj odată ce cineva a răspuns.'
    ),
    array(
        0 => 'Cum pot să îmi adaug semnătură la mesaj?',
        1 => 'Pentru a adăuga o semnătură trebuie întâi să vă creaţi una folosind Panoul utilizatorului. Odată ce semnătura este creată, puteţi să bifaţi opţiunea <i>Ataşează o semnătură</i> din formularul de publicare pentru a vă adăuga semnătura. Puteţi, de asemenea, să vă adăugaţi automat semnătura la toate mesajele bifând opţiunea corespunzătoare din profil. Dacă procedaţi astfel, puteţi să preveniţi adăugarea semnăturii unor anumite mesaje debifând caseta respectivă din formularul de publicare.'
    ),
    array(
        0 => 'Cum pot crea un sondaj?',
        1 => 'Când creaţi un subiect nou sau modificaţi primul mesaj al unui subiect, ar trebui să vedeţi o secţiune <i>Creare chestionar</i> sub zona principală de publicare; dacă nu vedeţi acest lucru probabil că nu aveţi privilegiile de acces necesare pentru a crea sondaje. Introduceţi un titlu pentru chestionar şi cel puţin două opţiuni în câmpul text corespunzător având grijă să specificaţi o opţiune pe fiecare rând. Puteţi să specificaţi numărul de opţiuni pe care utilizatorul le poate selecta în timpul votării folosind „Opţiuni per utilizator”, valabilitatea în zile a sondajului (0 înseamnă un sondaj permanent) şi în cele din urmă opţiunea ce permite utilizatorilor să-şi schimbe voturile.'
    ),
    array(
        0 => 'De ce nu pot adăuga mai multe opţiuni la sondaj?',
        1 => 'Limita pentru opţiunile sondajului este specificată de către administratorul forumului. Dacă simţiţi că aveţi nevoie să adăugaţi opţiuni suplimentare sondajului peste limita permisă, atunci contactaţi administratorul forumului.'
    ),
    array(
        0 => 'Cum modific sau şterg un sondaj?',
        1 => 'Ca şi în cazul mesajelor, sondajele pot fi modificate doar de către autorul acestora, un moderator sau de către un administrator. Pentru a modifica un sondaj, efectuaţi un click pe primul mesaj din subiect; acesta este întotdeauna asociat cu sondajul. Dacă nimeni nu a votat, atunci utilizatorii pot şterge sau modifica orice opţiuni ale sondajului. Dacă membrii şi-au exprimat opţiunile deja, doar moderatorii sau administratorii îl pot modifica sau şterge. Acest lucru previne schimbarea opţiunilor sondajului înainte de finalizarea acestuia.'
    ),
    array(
        0 => 'De ce nu pot să accesez un forum?',
        1 => 'Unele forumuri pot avea acces limitat pentru anumiţi utilizatori sau grupuri de utilizatori. Pentru a vedea, citi, publica, etc. este nevoie probabil de permisiuni speciale. Contactaţi un moderator sau administratorul forumului pentru a vă da acces.'
    ),
    array(
        0 => 'De ce nu pot adăuga fişiere ataşate?',
        1 => 'Abilitatea de a adăuga fişiere ataşate se poate face pe bază de forum, grup, sau utilizator. Administratorul forumului poate a dezactivat ataşarea fişierelor în forumul în care vreţi să scrieţi, sau poate doar anumite grupuri pot ataşa fişiere. Contactaţi administratorul forumului dacă nu ştiţi sigur de ce nu puteţi adăuga fişiere ataşate.'
    ),
    array(
        0 => 'De ce am primit un avertisment?',
        1 => 'Fiecare administrator are propriile reguli pentru forumul său. Dacă aţi încălcat o regulă, vi se poate da un avertisment. Reţineţi că aceasta este decizia administratorului şi grupul phpBB nu are nimic de-a face cu avertismentele acordate pe site-ul în cauză. Contactaţi administratorul forumului dacă nu ştiţi sigur de ce aţi primit un avertisment.'
    ),
    array(
        0 => 'Cum pot raporta mesaje unui moderator?',
        1 => 'Dacă administratorul a permis această facilitate, mesajul cu pricina ar trebui să includă un buton pentru raportarea lui. Dând click pe acest buton, veţi fi îndrumat cu paşii necesari pentru raportarea mesajului.'
    ),
    array(
        0 => 'Pentru ce este butonul "Salvare" la deschiderea unui subiect?',
        1 => 'Această opţiune vă dă posibilitatea să salvaţi unele pasaje în vederea finalizării şi publicării lor în viitor. Pentru a reîncărca un pasaj salvat, folosiţi Panoul utilizatorului.'
    ),
    array(
        0 => 'De ce mesajul meu trebuie să fie aprobat?',
        1 => 'Administratorul forumului poate a decis că mesajele din forumul în care scrieţi trebuiesc verificate înaintea publicării. De asemenea, este posibil ca administratorul să vă fi adăugat într-un grup de utilizatori ale căror mesaje recesită o revizuire înainte de a fi publicate. Vă rugăm să contactaţi administratorul pentru mai multe detalii.'
    ),
    array(
        0 => 'Cum îmi promovez subiectul?',
        1 => 'În momentul vizualizării, apăsând pe legătura „Ridică acest subiect” puteţi "promova" subiectul curent în partea superioară a forumului pe prima pagină. Dacă nu vedeţi această opţiune, înseamnă că promovarea subiectelor poate fi dezactivată sau timpul permis între promovări nu a fost atins. De asemenea, puteţi promova subiectul respectiv, mai simplu, prin adăugarea unui răspuns. Totuşi, asiguraţi-vă că respectaţi regulile forumului când procedaţi astfel.'
    ),
    array(
        0 => '--',
        1 => 'Formatări şi tipuri de subiecte'
    ),
    array(
        0 => 'Ce este codul BB?',
        1 => 'Codul BB este o implementare specială a HTML-ului ce oferă un control mărit al formatării pe anumite obiecte din cadrul unui mesaj. Posibilitatea de a folosi codul BB este dată de decizia administratorului dar poate fi dezactivat acest cod de la mesaj la mesaj din formularul de publicare. Codul BB este similar ca stil cu HTML-ul dar balizele (tag-urile) sunt închise în paranteze pătrate [ şi ] mai degrabă decât &lt; şi &gt;. Pentru mai multe informaţii despre codul BB, consultaţi ghidul care poate fi accesat din pagina de publicare.'
    ),
    array(
        0 => 'Pot folosi HTML?',
        1 => 'Nu. Nu se poate folosi cod HTML care să fie trimis la browser ca şi fiind cod HTML. Majoritatea formatărilor întreţinute de HTML sunt posibile folosind cod BB.'
    ),
    array(
        0 => 'Ce sunt Zâmbetele?',
        1 => 'Zâmbetele sau iconiţele emotive sunt imagini mici care pot fi folosite pentru a exprima anumite sentimente folosind un cod scurt. Spre exemplu :) înseamnă vesel , :( înseamnă trist. Lista completă a iconiţelor emotive poate fi consultată în formularul de publicare. Încercaţi totuşi să nu folosiţi prea multe iconiţe emotive pentru că pot face un mesaj greu de citit şi un moderator s-ar putea hotărî să le scoată din mesaj sau să şteargă mesajul cu totul. De asemenea, administratorul forumului poate să specifice o limită a numărului de zâmbete ce se pot folosi în cadrul unui mesaj.'
    ),
    array(
        0 => 'Pot publica imagini?',
        1 => 'Da, imaginile pot fi afişate în mesajele dumneavoastră. Dacă administratorul a permis fişierele ataşate, puteţi să încărcaţi imaginile direct pe forum. Altfel, puteţi folosi o legatură către o imagine stocată pe un server accesibil publicului, cum ar fi http://www.exemplu.com/imaginea-mea.gif. Nu puteţi să creaţi legături cu imagini din calculatorul dumneavoastră (doar dacă este un server public), nici cu imagini stocate în spatele unui mecanism de autentificare, cum ar fi căsuţele de e-mail, site-uri protejate cu parolă, etc. Pentru a afişa imaginea, folosiţi eticheta codului BB [img].'
    ),
    array(
        0 => 'Ce sunt anunţurile globale?',
        1 => 'Anunţurile globale conţin informaţii importante şi este indicat să le citiţi cât de curând posibil. Anunţurile globale vor apărea în partea de sus a fiecărei pagini de forum şi în interiorul panoului de control al utilizatorului. Permisiunea de a publica anunţuri globale este acordată de către administratorul forumului.'
    ),
    array(
        0 => 'Ce sunt anunţurile?',
        1 => 'Anunţurile deseori conţin informaţii importante referitoare la forumul pe care îl parcurgeţi şi ar trebui citite cât de curând posibil. Anunţurile apar în partea de sus a fiecărei pagini în forumul de care aparţin. La fel ca anunţurile globale, permisiunea de a publica anunţuri este acordată de către administratorul forumului.'
    ),
    array(
        0 => 'Ce sunt subiectele importante?',
        1 => 'Subiectele importante apar sub anunţuri în forum şi doar pe prima pagină. Deseori, sunt destul de importante şi ar trebui să le citiţi cât de curând posibil. Ca şi cu anunţurile, permisiunea de a publica subiecte importante este acordată de către administratorul forumului.'
    ),
    array(
        0 => 'Ce sunt subiectele blocate/închise?',
        1 => 'Subiectele blocate sunt subiectele unde utilizatorii nu mai pot răspunde şi orice sondaj conţinut s-a închis automat. Subiectele pot fi închise din mai multe motive şi au fost setate astfel de către moderatorii sau administratorii forumului. De asemenea, aţi putea să vă închideţi propriile subiecte doar dacă administratorul forumului v-a acordat această permisiune.'
    ),
    array(
        0 => 'Ce sunt iconiţele de subiect?',
        1 => 'Aceste iconiţe sunt imagini de mici dimensiuni asociate cu mesaje pentru a indica conţinutul acestora. Posibilitatea de a folosi aceste imagini depinde de permisiunile setate de către administrator.'
    ),
    	// This block will switch the FAQ-Questions to the second template column
	array(
		0 => '--',
		1 => '--'
	),
    array(
        0 => '--',
        1 => 'Niveluri de utilizatori şi grupuri'
    ),
    array(
        0 => 'Cine sunt administratorii?',
        1 => 'Administratorii sunt membrii asociaţi cu cel mai mare nivel de control asupra întregului forum. Aceşti utilizatori pot controla toate operaţiunile forumului incluzând permisiunile de acces, excluderea utilizatorilor, crearea grupurilor sau a moderatorilor, etc. în funcţie de permisiunile primite de la fondatorul forumului. De asemenea, au capacitatea de moderare completă în toate forumurile în funcţie de permisiunile primite de la fondatorul forumului.'
    ),
    array(
        0 => 'Cine sunt moderatorii?',
        1 => 'Moderatorii sunt persoane (sau grupuri de persoane) a căror menire este să aibă grijă de forumuri în mod constant. Aceştia au autoritatea de a modifica sau şterge mesajele şi de a bloca, debloca, muta, şterge şi împărţi subiectele în forumurile pe care le moderează. În mod general, moderatorii există pentru a avea grijă ca utilizatorii să nu scrie pe lângă subiect sau să publice mesaje abuzive sau ofensatoare.'
    ),
    array(
        0 => 'Ce sunt grupurile de utilizatori?',
        1 => 'Grupurile de utilizatori sunt grupări de utilizatori ce împart comunitatea în secţiuni ce pot fi controlate de către administratorii forumului. Fiecare utilizator poate aparţine mai multor grupuri şi fiecare grup are drepturi de acces individuale. Acest lucru uşurează munca administratorilor dacă doresc să schimbe permisiunile pentru mai mulţi utilizatori deodată ca de exemplu: schimbarea permisiunilor de moderare sau acordarea accesului utilizatorilor la un forum privat.'
    ),
    array(
        0 => 'Unde pot fi găsite grupurile de utilizatori şi cum pot să mă asociez unuia?',
        1 => 'Puteţi vedea toate grupurile de utilizatori folosind link-ul „Grupuri de utilizatori” din cadrul Panoului de control al utilizatorului. Pentru a vă asocia unui grup, folosiţi butonul corespunzător. Nu toate grupurile sunt deschise. Unele necesită aprobare pentru a vă alătura grupului, unele sunt închise şi altele pot fi chiar ascunse. Dacă grupul este deschis, puteţi să vă înscrieţi apăsând butonul adecvat. Dacă grupul necesită aprobare pentru înscriere, puteţi cere acest lucru apăsând butonul adecvat. Moderatorul grupului va trebui să aprobe cererea dumneavoastră şi este posibil să fiţi întrebat care sunt motivele pentru care doriţi să vă alăturaţi grupului. Vă rugăm să nu hărţuiţi un moderator de grup dacă vă respinge cererea pentru că are motive întemeiate.'
    ),
    array(
        0 => 'Cum pot deveni moderatorul unui grup de utilizatori?',
        1 => 'De obicei un moderator al unui grup de utilizatori este numit când grupul este creat de către un administrator al forumului. Dacă doriţi să creaţi un grup de utilizatori, atunci primul lucru pe care trebuie să îl faceţi este să luaţi legătura cu un administrator; încercaţi să-i trimiteţi un mesaj privat.'
    ),
    array(
        0 => 'De ce grupurile de utilizatori apar în culori diferite?',
        1 => 'Este posibil ca administratorul să asocieze o culoare membrilor unui grup pentru a face mai uşoară identificarea acestora.'
    ),
    array(
        0 => 'Ce este un „Grup implicit”?',
        1 => 'Dacă sunteţi membrul mai multor grupuri, grupul implicit este folosit pentru a determina culoarea şi rangul grupului ce ar trebui să vi se afişeze. Administratorul forumului vă poate da permisiunea să schimbaţi grupul implicit în panoul dumneavoastră de administrare.'
    ),
    array(
        0 => 'Ce este pagina "Echipa"?',
        1 => 'Această pagină vă furnizează o listă cu conducerea forumului, incluzând administratorii şi moderatorii şi alte detalii ca de exemplu forumurile pe care le moderează.'
    ),
    array(
        0 => '--',
        1 => 'Mesaje private'
    ),
    array(
        0 => 'Nu pot trimite mesaje private!',
        1 => 'Există trei posibile motive: nu sunteţi înregistrat ori autentificat, administratorul forumului a dezactivat mesageria privată pentru toţi utilizatorii sau administratorul forumului v-a restricţionat folosirea mesajelor private. Contactaţi un administrator al forumului pentru mai multe informaţii.'
    ),
    array(
        0 => 'Tot primesc mesaje private nedorite!',
        1 => 'Puteţi bloca un utilizator să vă mai trimită mesaje private folosind regulile de mesagerie din Panoul utilizatorului. Dacă primiţi mesaje private abuzive de la un anumit utilizator, contactaţi un administrator al forumului; el are puterea de a restricţiona folosirea mesajelor private pentru anumiţi utilizatori.'
    ),
    array(
        0 => 'Am primit spam-uri sau mesaje abuzive de la cineva din forum!',
        1 => 'Ne pare rău să auzim acest lucru. Opţiunea de trimitere a unui mesaj electronic include măsuri de siguranţă pentru a observa care utilizatori trimit astfel de mesaje. Ar trebui să trimiteţi administratorului o copie completă a mesajului primit. Este foarte important ca acesta să includă antetul ce conţine detalii despre utilizatorul care a trimis mesajul. Astfel, administratorul forumului poate acţiona în consecinţă.'
    ),
    array(
        0 => '--',
        1 => 'Prieteni şi persoane neagreate'
    ),
    array(
        0 => 'Ce este lista de prieteni şi persoane neagreate?',
        1 => 'Puteţi folosi aceste liste pentru a organiza ceilalţi membri de pe forum. Utilizatorii adăugaţi listei de prieteni vor fi afişaţi în panoul dumneavoastră de control pentru acces rapid la trimiterea mesajelor private şi vizualizarea statutului lor (Conectat/Neconectat). Depinzând de stilul folosit, mesajele lor pot fi scoase în evidenţă. Dacă adăugaţi un utilizator în lista cu persoane neagreate, mesajele acestuia vor fi ascunse.'
    ),
    array(
        0 => 'Cum pot adăuga/şterge utilizatori în listele mele de prieteni sau persoane neagreate?',
        1 => 'Puteţi adăuga utilizatori în două moduri. În cadrul fiecărui profil al utilizatorului, există un link pentru a-l adăuga în lista de prieteni sau persoane neagreate. Alternativ, puteţi adăuga direct prin introducerea numelor de utilizatori din Panoul utilizatorului. Din aceeaşi pagină puteţi să şi ştergeţi nume din liste.'
    ),
    array(
        0 => '--',
        1 => 'Căutând în forumuri'
    ),
    array(
        0 => 'Cum pot să caut într-un forum sau forumuri?',
        1 => 'Această acțiune poate fi efectuată prin introducerea termenului căutat în căsuţa de căutare aflată pe prima pagină, în pagina unui forum sau a unui subiect. Căutarea avansată poate fi accesată folosind link-ul „Căutare avansată” localizat pe toate paginile forumului.'
    ),
    array(
        0 => 'De ce căutarea mea nu returnează niciun rezultat?',
        1 => 'Căutarea efectuată a fost probabil prea vagă şi a inclus mulţi termeni comuni care nu sunt indexaţi de phpBB3. Fiţi mai specific şi folosiţi opţiunile disponibile în formularul de căutare avansată.'
    ),
    array(
        0 => 'De ce căutarea mea returnează o pagină goală!?',
        1 => 'Căutarea efectuată a returnat prea multe rezultate pentru a le prelucra webserver-ul. Folosiţi „Căutare avansată” şi fiţi mai specific în termenii folosiţi şi forumurile în care se efectuează căutarea.'
    ),
    array(
        0 => 'Cum pot căuta utilizatori?',
        1 => 'Duceţi-vă la secţiunea „Membri” şi folosiţi legătura „Caută un membru”.'
    ),
    array(
        0 => 'Cum pot găsi mesajele şi subiectele mele?',
        1 => 'Pentru a afişa mesajele dumneavoastră folosiţi legătura „Căută mesajele utilizatorului” din Panoul utilizatorului sau din pagina de profil. Pentru a vă căuta subiectele proprii, folosiţi pagina de căutare avansată şi introduceţi opţiunile adecvate.'
    ),
    array(
        0 => '--',
        1 => 'Însemnarea şi înscrierea la subiecte'
    ),
    array(
        0 => 'Care este diferenţa dintre a însemna şi a înscrie?',
        1 => 'Însemnarea în phpBB3 este foarte asemănătoare cu însemnarea în navigatorul dumneavoastră web. Nu sunteţi notificat când este publicat un răspuns, dar vă puteţi întoarce mai târziu la subiect. Înscriindu-vă, veţi fi notificat când va fi publicat un răspuns în subiectul respectiv sau în forum în funcţie de metodele şi opţiunile preferate.'
    ),
    array(
        0 => 'Cum mă pot înscrie la anumite subiecte sau forumuri?',
        1 => 'Pentru a vă înscrie la un anumit forum, odată intrat în acel forum, folosiţi link-ul "Abonare forum". Pentru a vă înscrie la un subiect, răspundeţi la acel subiect şi marcaţi căsuţa de abonare sau puteţi folosi legătura „Abonare subiect” din cadrul subiectului.'
    ),
    array(
        0 => 'Cum imi pot şterge înscrierile?',
        1 => 'Pentru a vă şterge o înscriere, mergeţi în  Panoul utilizatorului şi folosiţi legăturile către înscrieri.'
    ),
    array(
        0 => '--',
        1 => 'Fişiere ataşate'
    ),
    array(
        0 => 'Ce fişiere ataşate sunt permise pe acest forum?',
        1 => 'Fiecare administrator de forum poate permite sau restricţiona anumite tipuri de fişiere ataşate. Dacă nu sunteţi sigur ce este permis sâ încărcaţi, contactaţi administratorul forumului pentru asistenţă.'
    ),
    array(
        0 => 'Cum pot găsi toate fişierele ataşate proprii?',
        1 => 'Pentru a găsi lista fişierelor ataşate încărcate de dumneavoastră, mergeţi în Panoul utilizatorului şi folosiţi legăturile către secţiunea de fişiere ataşate.'
    ),
    array(
        0 => '--',
        1 => 'Întrebări despre phpBB 3'
    ),
    array(
        0 => 'Cine a scris acest program?',
        1 => 'Acest program (în forma sa nemodificată) este produs, lansat şi aflat sub copyright-ul <a href="https://www.phpbb.com/" target="_blank">grupului phpBB</a>. Este disponibil sub Licenţa Generală Publică GNU şi poate fi distribuit gratuit. Folosiţi link-ul pentru mai multe detalii.'
    ),
    array(
        0 => 'De ce nu este facilitatea X disponibilă?',
        1 => 'Acest program a fost scris şi licenţiat de către grupul phpBB. În cazul în care consideraţi că o facilitate trebuie să fie adaugată sau vreți să raportați un defect, vă rugăm să vizitaţi <a href="https://www.phpbb.com/ideas/">Centrul de idei phpBB</a> unde veți putea vota ideile existente sau propune facilități noi.'
    ),
    array(
        0 => 'Cu cine iau legătura pentru probleme juridice şi/sau abuzuri legate de acest program?',
        1 => 'Oricare din administratorii afişaţi în secţiunea „Echipa” ar trebui să fie un punct de contact potrivit pentru reclamaţiile dumneavoastră. Dacă nu primiţi răspuns, ar trebui să luaţi legătura cu posesorul domeniului (efectuaţi o <a href="http://www.google.com/search?q=whois">interogare whois</a>) sau, dacă acesta este pe un domeniu gratuit (de exemplu: Yahoo!, free.fr, f2s.com, etc.), cu conducerea sau departamentul pentru abuzuri al serviciului respectiv. Vă rugăm să reţineţi că grupul phpBB <strong>nu are absolut niciun fel de control</strong> şi nu poate fi tras la răspundere pentru cum, unde sau de către cine este folosit acest program. Nu luaţi legătura cu grupul phpBB pentru probleme juridice care <strong>nu sunt legate direct</strong> de site-ul phpbb.com sau de program în sine. Dacă trimiteţi un mesaj către grupul phpBB <strong>despre orice folosire a unei terţe persoane</strong> a acestui program, ar trebui să aşteptaţi un terţ răspuns sau niciun răspuns.'
    )
);

?>