<?php
/***************************************************************************
 *                          lang_faq.php [slovenian]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *    Popravek in dodatni prevod prvega slovenskega prevoda: Tomaž Koštial (m5@cyberdude.com) 13/10/2002
 *    Dodal prvo poglavje: Pozdravljeni v slovenski razlièici (naslov in 4 alineje): Lado Golouh, www.razmerje.com
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
 
// 
// To add an entry to your FAQ simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your FAQ entries, if you absolutely must then escape them ie. \"something\";
//
// The FAQ items will appear on the FAQ page in the same order they are listed in this file
//


$faq[] = array("--","Pozdravljeni v slovenski razlièici foruma phpBB"); 
$faq[] = array("Kaj je uporabniško ime?", "To je ime, pod katerim boste prepoznavni. Lahko je vaše pravo ali izmišljeno ime, sestavljeno iz poljubnih èrk in številk. Priporoèamo, da si izberete ime, ki vam je všeè.");
$faq[] = array("Zakaj potrebujem geslo?", "Geslo si izbereš, potrebuješ pa ga kot kljuè za varen vstop pod tvojim uporabniškim imenom. Ko si prijavljen/-a lahko ne samo objavljaš pod svojim imenom, temveè pišeš in bereš zasebna sporoèila, spreminjaš podatke o sebi v profilu, ipd. zato je prav, da je tvoja identiteta varovana.");
$faq[] = array("Kako se znajdem na forumu", "Najlažje tako, da s klikanjem preizkušaš in se pri tem uèiš. Pomoè poišèeš na dveh mestih: <b>a.</b> <i>Pogosta vpašanja in odgovori</i> v menijski vrstici zgoraj na osnovni strani in <b>b.</b> levo ob okenèku, ki se odpre za vpis sporoèila, so navodila, kako oblikovati izgled sporoèila: <i>(Navodila) BBkoda</i>. Ne pozabi: že uteèeni èlani poveèini zelo radi pomagajo zaèetnikom.");
$faq[] = array("Ali lahko ostanem anonimen?", "Imaš dve možnosti: <b>a.</b> prikrita ideniteta, ker se prijaviš z izmišljenim uporabniškim imenom. Èe administrator dovoli, lahko sodeluješ le <b>b.</b>kot gost, namesto imena se izpiše <i>gost</i>. Kljub temu odgovarjaš za svoje objave, da so v okviru splošnih norm, ob zlorabi lahko administrator onemogoèi dostop na stran. Objava pod imenom omogoèa veèjo preglednost sporoèil.");
  
$faq[] = array("--","Vprašanja v zvezi s prijavo in registracijo");
$faq[] = array("Zakaj se ne morem prijaviti?", "Ali si se registriral? Ne? To je povsem resno vprašanje, saj se moraš za prijavo predhodno registrirati. Ti je bil mogoèe vstop onemogoèen (v tem primeru se ti bo prikazalo ustrezno obvestilo)? Èe je bilo res tako, potem kontaktiraj administratorja, ki ti bo povedal vzrok prepovedi vstopa. Èe pa ni niè od navedenega,  natanèno preveri uporabniško ime in geslo, ki si ga vnesel pri prijavi. To so namreè najpogostejši vzroki za nastali problem. Èa pa še kljub temu, da vstopa nimaš onemogoèenega s strani administratorja, ne boš uspel odpraviti problema, kontaktiraj administratorja foruma, saj je možno, da nastavitve le-tega niso ustrezne.");
$faq[] = array("Zakaj se moram registrirati?", "Registriranje ni 100% potrebno. Vse je odvisno od administratorja oz. od tega, kako je nastavil forum. Brez registracije boš verjetno lahko objavljal in odgovarjal na objave v doloèenih forumih, na doloèene (obièajno najveèkrat) pa verjetno ne! Torej, z registracijo boš prišel do dodatnih opcij (izbira avatarja - podobe, pošiljanje zasebnih sporoèil, ...), katere ti kot gostu (neregistriranemu uporabniku) ne bodo na voljo! Ker ti bo registracija vzela le slabo minutko, ti priporoèamo, da le-to izvedeš.");
$faq[] = array("Èemu avtomatska odjava?", "Èe ob prijavi ne odkljukaš polja <i>Samodejna prijava</i>, boš ostal prijavljen le v èasu obiska strani. Ta (ne)nastavitev prepreèuje zlorabo tvojega uporabniškega raèuna. Èe želiš ostati prijavljen, odkljukaj nastavitev <i>Samodejno prijava</i>, kar pa ni priporoèljivo, èe <b>ne dostopaš</b> do strani preko svojega raèunalnika ampak od drugod (knjižnice, cyber cafe, ...). Za vse uporabnike na domaèem, svojem raèunalniku priporoèamo avtomatsko prijavljanje.");
$faq[] = array("Kako odstranim vidnost uporabniškega imena z liste na zvezi (online) uporabnikov?", "V svojem profilu boš našel opcijo <i>Skrij prisotnost</i>. Èe jo postaviš na <i>Da</i>, se bo prisotnost kazala le administratorju in tebi. Obravnavan boš kot <i>skriti uporabnik</i>.");
$faq[] = array("Pozabil sem geslo!", "Ne zganjaj panike! Ker tvojega gesla ni moè vpogledati, boš dobil novega, in sicer tako, da na strani, kjer se prijaviš, klikneš na <u>Pozabil sem geslo</u>, ter slediš navodilom, tako da boš v trenutku zopet na zvezi, online!");
$faq[] = array("Registriral sem se, vendar se ne morem prijaviti!", "Preveri, èe vnašaš pravilno uporabniško ime in geslo. Èe je temu tako, potem sta se lahko zgodili dve stvari, in sicer sledeèi:<br>
- èe je vkljuèena opcija 'COPPA support' in si pri registraciji kliknil <u>Sem mlajši od 13 let</u>, potem boš moral slediti navodilom, ki si jih tedaj prejel. Èe pa temu ni tako, ...<br>
- potem je verjetno potrebno vaš raèun aktivirati. Nekateri forumi namreè pred prijavo zahtevajo aktivacijo raèunov vseh novo registriranih uporabnikov, bodisi s strani administratorja ali samega uporabnika. To zahtevo si mogel opaziti pri sami prijavi. Èe si prejel email-elektronsko sporoèilo, potem sledi navodilom, èe pa sporoèila nisi prejel, si verjetno vnesel napaèen e-mail naslov. Aktivacija raèuna je v veèini primerov uporabljena za zašèito forumov pred anonimno zlorabo le-teh. Èe si popolnoma preprièan, da si vnesel pravi e-mail naslov, skušaj kontaktirati administratorja.");
$faq[] = array("Naenkrat se ne morem veè prijaviti?!", "Najbolj pogosti razlogi za to so:<br>
- vnesel si napaèno uporabniško ime ali geslo (preveri elektronsko sporoèilo, ki si ga prijel ob registraciji)<br>
- administrator je z doloèenim razlogom odstranili tvoj raèun. Pogosto so nastavitve forumov take, da se po doloèenih dneh neobjavljanja sledeèi raèun odstrani in s tem zmanjša velikost baze podatkov. Zatorej se ponovno registriraj in se vkljuèi v debato!");


$faq[] = array("--","Uporabniške nastavitve");
$faq[] = array("Kako spremenim svoje nastavitve?", "Vse tvoje nastavitve (èe si registriran) so shranjene v bazi podatkov. V primeru, da si želiš spremeniti le-te, klikni na povezavo Profil (ponavadi v zgornjem predelu strani, vendar ni nujno tako). V profilu lahko spreminjaš vse svoje nastavitve.");
$faq[] = array("Èas ni pravilen!", "Èas ki ga vidiš je skoraj zagotovo pravilen. Tisto, kar ti vidiš je lahko èas iz drugega èasovnega pasu. Èe je temu tako, potem bi bilo dobro, èe bi spremenil nastavitve èasovnega pasu v svojem profilu in sicer za Slovenijo je ustrezna nastavitev: (GMT + 1:00 ura) Ljubljana, Amsterdam, ... Te in ostale nastavitve pa lahko spreminjajo samo registrirani uporabniki. Èe torej še nisi registriran, je sedaj zadnji èas, da se registriraš!");
$faq[] = array("Spremenil sem èasovni pas, èas pa je še vedno ni pravilen!", "Èe si preprièan, da si pravilno nastavil èasovni pas, èas pa še vedno ni ustrezen, je najverjetneje vzrok v razliki letnih èasov (pomlad, poletje, jesen, zima). Ker forum nima vgrajene opcije za pretvorbo èasa zaradi letnih èasov, je možno, da bo le ta odstopal za 1 uro.");
$faq[] = array("Mojega materinega jezika ni na spisku!", "Razlog tièi v tem, da je za privzeti jezik nastavljen slovenski, saj se v tem forumu uporablja slovenšèina.");
$faq[] = array("Kako dodam sliko poleg uporabniškega imena?", "Poleg uporabniškega imena imaš lahko dve slikici in sicer:<br>
- prva slikica je lahko povezana s tvojim rangom, ki se s pridnostjo in pogostostjo objavljanja postopoma zvišuje,
- pod to pa je lahko veèja slikica, avatar, je podoba, ki je ponavadi edinstvena, pripada samo enemu uporabniku, razen èe je izbrana iz galerije. Èe je izbira avatarja (podobe) onemogoèena, je to paè odloèitev administratorja foruma, preprièani smo, da ima dober razlog za svojo odloèitev. Sliko lahko prispevaš iz svojega arhiva ali narišeš, ali se povežeš z neko slièico na internetu.");
$faq[] = array("Kako spremenim rang?", "V splošnem ni moè spreminjati ranga (pojavi se nad uporabniškim imenom). Veèina forumov uporablja range za identifikacijo množiènosti objav posameznega uporabnika, ter posebne range za obeležitev posebnih uporabnikov. Prosim, ne zlorabi množiènega objavljanja (brez vsebine) z namenom, da bi si pridobil višji rang, saj bo moderator ali administrator v tem primeru znižal število tvojih objav.");
$faq[] = array("Ob kliku na povezavo e-mail uporabnika sem pozvan k prijavi?", "Ja, tako je! Samo registrirani uporabniki lahko pošiljajo elektronska sporoèila uporabnikom foruma. S tem je prepreèena uporaba sistema pošiljanja elektronskih sporoèil anonimnim uporabnikom!");


$faq[] = array("--","Poglavje o objavljanju");
$faq[] = array("Kako objavim novo temo?", "Povsem preprosto! Klikni na ustrezen gumb (Nova tema) v forumu. Pred objavo pa se je v veèini primerov potrebno registrirati!");
$faq[] = array("Kako uredim ali izbrišem sporoèilo?", "V primeru, da nisi administrator ali moderator foruma, lahko urejaš in brišeš le svoja sporoèila. Svoje sporoèilo lahko urediš (vèasih samo nekaj èasa po objavi) tako, da klikneš na gumb uredi, v desnem zgornjem kotu posameznega sporoèila. V primeru, da je na tvoje sporoèilo nekdo že odgovoril, obièajni uporabnik ne more veè izbrisati objave!");
$faq[] = array("Kako k objavi dodam podpis?", "Pred dodajanjem podpisa, je le-tega potrebno predhodno vpisati, to pa storiš v svojem Profilu. Ko ga enkrat ustvariš, ga enostavno vkljuèiš v sporoèilo s tem, da na obrazcu za oddajo sporoèila, dodaš kljukico pred <b>Dodaj podpis</b>. Dodajanje podpisa pa lahko nastaviš tudi kot privzeto (se pravi da se ta prilepi k vsaki objavi) in sicer tako, da v nastavitvah svojega Profila odkljukaš ustrezeno opcijo. Podpis lahko kasneje (pri vsaki objavi posebej) tudi izkljuèiš.");
$faq[] = array("Kako ustvarim anketo?", "Povsem preprosto, èe ti seveda forum to dovoljuje. Ob objavi nove teme ali ob prvi objavi za neko temo se ti mora v obrazcu prikazati tudi del za izpolnitev podatkov za anketo. Èe tega ne vidiš, je opcija za vstavitev ankete onemogoèena! Podatke za anketo izpolniš tako, da najprej vneseš <i>Naslov ankete</i>, nato pa vsaj dve opciji za glasovanje. Prav tako lahko omejiš èas zbiranja glasov (0 pomeni neskonèno). Seveda pa obstaja tudi zgornja meja števila možnosti, ki pa jo nastavi administrator (npr. 10).");
$faq[] = array("Kako uredim ali izbrišem anketo?", "S tem je tako kot pri objavah. Se pravi da jih lahko ureja le lastnik - postavitelj, moderator ali administrator. Za urejanje ankete kliknite na gumb uredi pri prvi objavi v tej temi. Ko je bil enkrat sprejet glas, lahko uredi ali izbriše anketo le moderator ali administrator.");
$faq[] = array("Zakaj ne morem dostopati do doloèenega foruma?", "Dostop do nekaterih forumov (razdelkov) je dovoljen samo doloèenim uporabnikom ali skupini uporabnikov. Za dostop (pregled, branje, objavo, ...) do teh potrebuješ posebno avtorizacijo, ki ti jo lahko omogoèi le moderator ali administrator tistega dela foruma.");
$faq[] = array("Zakaj ne morem glasovati v anketi?", "Glasovanje je omogoèeno le registriranim uporabnikom (s tem je prepreèeno masivno glasovanje enega uporabnika). Èe si registriran in še vedno ne moreš glasovati, potem najbrž nimaè ustreznih dostopnih pravic.");

$faq[] = array("--","Oblikovanje foruma in vrste tem");
$faq[] = array("Kaj je koda BBCode?", "BBCode je posebna izboljšava HTML-jezika, katerega uporabo omogoèi (ali ne) administrator (lahko jo tudi onemogoèiš v obrazcu za vnos sporoèila, s tem da pred objavo odkljukaš ustrezno polje). BBCode je sama po sebi zelo podobna HTML-ju (oznaèbe so v oglatih oklepajih [xyz] kar omogoèa boljši pregled nad tem kaj in kakšen bo izpis.Za veè informacij o tej temi si oglejte dodatno razlago, do katere lahko dostopate tako, da v obrazcu za objavo kliknete na povezavo BBCode (ponavadi se nahaja v drugi vrstici naštetih <b>Možnosti</b>) levo obokencu za vpis sporoèila.");
$faq[] = array("Ali lahko uporabim HTML?", "Odvisno od tega ali to administrator dopušèa. Èe je njegova uporaba omogoèena, boš kmalu spoznal da delujejo le doloèene oznaèbe (tags). Prav tako lahko HTML tudi onemogoèiš in sicer tako, da pred objavo sporoèila odkljukaš polje <i>Onemogoèi HTML</i>.");
$faq[] = array("Kaj so Smeški?", "Smeški (Angl.: Smileys, Emoticons) so majhne grafiène slike, ki omogoèajo, da izraziš doloèena èustva (Primer>> :) pomeni da si sreèen, :( pomeni da si žalosten, ...). Celoten spisek Smeškov lahko vidiš v obrazcu za pošiljanje sporoèila.");
$faq[] = array("Ali lahko sporoèilu dodam sliko?", "Slike so lahko dodane sporoèilom, pri èemer pa ni omogoèen prenos slik direktno na strežnik, ampak v objavo lahko vkljuèiš le slike, ki so shranjene na javnosti dostopnih strežnikih (Primer>> http://www.moj-naslov.net/moja-slikca.gif). Povezave do slik, shranjenih na tvojem raèunalniku, razen èe ni javnosti dostopen strežnik, niso mogoèe. Prav tako ni mogoèe vkljuèevanje slik, ki so shranjene za avtorizacijskimi mehanizmi (Primer>> Hotmail-ov in Yahoo-jev poštni predal, strani zašèitene z geslom, ...). Za vkljuèitev slike uporabi bodisi BBCode-ovo oznaèbo [img] ali ustrezno HTML oznaèbo, èe je uporaba le-tega omogoèena.");
$faq[] = array("Kaj so Obvestila?", "Obvestila so sporoèila, ki vsebujejo pomembne informacije, tako da bi jih moral prebrati èimprej. Pojavijo se na vrhu vsake strani foruma, kjer so bila objavljena. Od pravic, ki so zahtevane za objavljanje obvestil, je odvisno ali jih lahko objaviš tudi ti! Vse je zopet v rokah administratorja.");
$faq[] = array("Kaj predstavljajo NE PREZRI! (Vedno na vrhu, Sticky) teme?", "Pojavijo se na vrhu prve strani razdelka foruma, v katerem so bile objavljene, takoj pod zgoraj omenjenimi obvestili. To so ponavadi dokaj pomembne teme, tako da jih je priporoèljivo prebrati. Prav tako tudi tu administrator doloèi pravice, ki so potrebne za objavljanje teh tem v posameznih forumih!");
$faq[] = array("Kaj so Zaklenjene teme?", "Zaklenjene teme so teme, na katere ni mogoèe veè odgovarjati. Ankete teh tem se avtomatsko konèajo. Zaklenjene so bodisi s strani administratorja bodisi s strani moderatorja, ponavadi zaradi razliènih razlogov, zagotovo pa so vsi tehtni.");


$faq[] = array("--","Uporabniški nivoji in skupine");
$faq[] = array("Kaj so Administratorji?", "Administrator je uporabnik, ki ima najvišjo stopnjo kontrole nad celotnim forumom (nastavitev pravic, nadzor nad uporabniki, kreiranje uporabniških skupin, doloèanje moderatorjev, ...). Prav tako vkljuèuje vse pravice moderatorjev posameznih forumov.");
$faq[] = array("Kaj so Moderatorji?", "Moderatorji so uporabniki, katerih naloga je, da dan za dnem bdijo nad dogajanjem v forumu, ki jim je dodeljen. Poleg tega imajo pravico urejanja in brisanja objav, zaklepanja, odklepanja, premikanja, brisanja in deljenja tem forumov, ki jih moderirajo. Poskrbeti morajo, da uporabniki z objavami ne zaidejo izven teme, zaradi katere je bil forum ustvarjen ter za to, da se objave z žaljivo in neustrezno vsebino èimprej odstranijo.");
$faq[] = array("Kaj so Uporabniške skupine?", "To so skupine, po katerih lahko administrator razvrsti uporabnike. Vsak uporabnik lahko pripada veè skupinam (tu je razlika v primerjavi z ostalimi forumi), vsaki skupini pa se lahko priredijo dostopne pravice. S tem je olajšano delo administratorju, saj tako lahko hitreje doloèi moderatorje foruma ali jim dodeli pravice za dostop do zasebnega foruma, ....");
$faq[] = array("Kako se pridružim doloèeni Uporabniški skupini?", "Za pristop k doloèeni skupini klikni na povezavo Uporabniške skupine, ki se nahaja v glavi strani (ponavadi zgornji del strani), kjer lahko pregledaš tebi vidne skupine. Vpogled v vse obstojeèe skupine ponavadi ni mogoè, saj so nekatere zaprte , nekatere pa imajo celo zakrito èlanstvo. Èe je skupina odprtega tipa, lahko zaprosiš za èlanstvo s klikom na ustrezen gumb. Za zavrnjene prošnje se ne znašajte nad moderatorjem skupine, saj imajo gotovo dobre razloge za zavrnitev.");
$faq[] = array("Kako postanem moderator Uporabniške skupine?", "Uporabniške skupine ustvari administrator in so jim prav tako dodeljeni moderatorji. Èe si zainteresiran za kreiranje Uporabniške skupine, o tem interesu obvesti admninistratorja, ponavadi je dovolj že zasebno sporoèilo.");


$faq[] = array("--","Pošiljanje zasebnih sporoèil");
$faq[] = array("Ne morem pošiljati zasebnih sporoèil!", "Za to obstajajo trije vzroki in sicer:<br> 
- nisi registriran/a in/ali prijavljen/a;<br>
- administrator je onemogoèil pošiljanje zasebnih sporoèil;<br>
- administrator ti je onemogoèil pošiljati zasebna sporoèila.<br>
V zadnjem primeru kontaktiraj administratorja in ga prosi za pojasnilo!");
$faq[] = array("Nenehno dobivam nezaželena zasebna sporoèila!", "V prihodnosti bo v sistem pošiljanja sporoèil vkljuèena tudi t.i. Ignore lista (seznam naslovov od katerih ne želimo prejemati sporoèil), do takrat pa o problemu, s katerim se sreèuješ, obvesti administratorja foruma, ki lahko posamezniku onemogoèi pošiljanje zasebnih sporoèil.");
$faq[] = array("Od nekoga iz foruma sem prejel vsiljeno (spam) ali žaljivo elektronsko sporoèilo!", "Za nastalo situacijo nam je zelo žal! Obrazec za pošiljanje elektronske pošte vsebuje varnostne toèke, preko katerih poskušamo zaslediti uporabnike, ki izrabljajo to storitev. V tem primeru je zelo pomembno, da administratorju pošlješ celotno kopijo sporoèila, še posebej t.i. header (vsebuje podatke o uporabniku, ki ga je poslal). Šele tedaj bo moè ukrepati proti storilcu.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","O programu phpBB 2");
$faq[] = array("Kdo je ustvaril ta forum?", "Ta program je (v nespremenjeni obliki) avtorsko delo <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. Javnosti je na voljo pod pogoji GNU General Public Licence in se lahko prosto posreduje drugim uporabnikom. Za veè informacij obišèite povezavo.");
$faq[] = array("Zakaj v forumu ni na voljo funkcija X?", "Ta software je delo skupine phpBB Group, ki ima zanj tudi licenco. Èe mislite, da bi bilo treba dodati kakšno dodatno funkcijo, potem obišèite stran phpbb.com in si oglejte, kaj o tem pravi phpBB Group. Prosimo vas, da na forumu na strani phpbb.com ne objavljate prošenj za nove funkcije. phpBB Group za te namene uporablja forume za izmenjavo mnenj (sourceforge). Prosimo, da preberete forume in si ogledate, kakšno je naše mnenje glede posameznih funkcij in potem sledite navodilom, ki jih boste dobili tam.");
$faq[] = array("Koga lahko kontaktiram glede zlorabe in pravnih zadev povezanih s tem forumom?", "Obrnite se na administratorja tega foruma. Èe ne najdete njegovega kontaktnega naslova, se obrnite na enega od moderatorjev in vprašajte koga morate kontaktirati. Èe še vedno ne dobite odziva, se obrnite na lastnika domene (do podatkov pridete preko <i>whois lookup</i>). Èe forum gostuje na brezplaènem serverju (npr. yahoo, free.fr, f2s.com, etc.), se obrnite na njihov oddelek za zlorabo storitev. Zavedati se morate, da phpBB Group nima popolnoma nobenega nadzora in zato ne more biti odgovorna za to, kdo uporablja njihov forum. Popolnoma nesmiselno je kontaktirati phpBB Group v zvezi s pravnimi zadevami, ki niso direktno povezane s stranjo phpbb.com ali z njihovim programom. Èe boste vseeno poslali sporoèilo phpBB Group o uporabi njihovega foruma, se zavedajte, da boste v najboljšem primeru dobili le kratek odgovor, v veèini primerov pa sploh ne boste dobili odgovora.");

//
// This ends the FAQ entries
//

?>