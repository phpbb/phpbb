<?php
/***************************************************************************
 *                          lang_faq.php [Croatian]
 *                            -------------------
 *     begin                : Monday Dec 01 2002 
 *     copyright            : (C) 2002 Hrvoje Stankov
 *     email                : hrvoje@spirit.hr
 *
 *   $Id$
 *
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
// Do NOT put double quotes (") in your FAQ entries, if you absolutely must then escape them ie. \"something\"
//
// The FAQ items will appear on the FAQ page in the same order they are listed in this file
//
 
  
$faq[] = array("--","Problemi prilikom prijave i registracije");
$faq[] = array("Zašto se ne mogu prijaviti?", "Da li ste se registrirali? Morate se registrirati ukoliko se želite prijaviti. Da li imate zabranu pristupa forumu (ako imate bit æe prikazana poruka)? Ako je to u pitanju, trebali bi kontaktirati webmastera ili admina i saznati zašto. Ako ste registrirani i nemate zabranu i još uvijek se ne možete prijaviti onda ponovno provjerite vaše korisnièko ime i lozinku. Obièno je ovo problem, a ako nije onda kontaktirajte adminia jer možda nešto nije u redu sa forumom.");
$faq[] = array("Zašto se uopæe moram registrirati?", "Ne morate takoðer, na adminu foruma je da odluèi da li možete slati poruke ako niste registrirani. U svakom sluèaju registracija vam omoguæava pristup dodatnim moguænostima koje nisu dostupne gostima kao što su biranje avatara, privatne poruke, slanje emaila drugim korisnicima, prikljuèivanje grupama itd. Potrebno je svega nekoliko minuta za registraciju pa vam preporuèavamo da se registrirate.");
$faq[] = array("Zašto se odjavljujem automatski?", "Ako ne ukljuèite <i>Prijavi me automatski</i> kada se prijavljujete forum æe vas pamtiti kao prijavljenog cijelo vrijeme. Ovo sprjeèava da se neko drugi prijavi kao vi. Da biste ostali prijavljeni oznaèite box prilikom prijavljivanja, iako ovo ne preporuèavamo ako forumu pristupate sa dijeljenog raèunala, npr. knižnice, internet caffea, fakulteta itd.");
$faq[] = array("Kako da sprijeèim da se moje ime ne pojavljuje na listi korisnika koji su trenutno na forumu?", "U vašem profilu æete naæi opciju <i>Sakrij moj online status</i>, ako ovo promjenite na <i>Ukljuèeno</i> bit æete vidljivi samo adminima ili sami sebi. Bit æete brojani kao skriveni korisnik.");
$faq[] = array("Izgubio sam šifru!", "Ne panièarite! Iako vaša šifra ne može biti vraæena, ona može biti resetirana. Da biste ovo uèinili otiðite na stranicu za prijavu i kliknite na <u>Zaboravio sam lozinku</u>, pratite instrukcije i brzo æete ponovno biti u moguænosti da se prijavite");
$faq[] = array("Registrirao sam se ali se ne mogu prijaviti!", "Prvo provjerite da li ste unijeli toèno korisnièko ime i lozinku. Ako je to u redu onda je moguæa jedna od ove dvije greške. Ako je COPPA podrška ukljuèena i kliknuli ste na <u>Mlaði-a sam od 13 godina</u> prilikom registracije onda æete morati pratiti uputstva koja ste dobili. Ukoliko i ovo ne pomogne provjerite da li vaš nalog treba reaktivirati? Neki forumi æe zahtjevati da se ponovno registrirate, ili sami ili od strane admina prije nego što budete u moguænosti da se prijavite. Kada se registrirate bit æe vam reèeno da li trebate reaktivirati nalog. Ako vam je poslan email onda pratite instrukcije, a ako niste provjerite da li je toèan vaš email? Jedan razlog za aktivacijom je da bi se smanjila moguænost da <i>korisnici koji varaju </i> anonimno vrijeðaju forume. Ako ste sigurni da je email adresa koju ste koristili toèna onda kontaktirajte admina.");
$faq[] = array("Ranije sam se registrirao, ali se više ne mogu prijaviti?!", "Najèešæi razlozi za ovo su; unijeli ste pogrešno korisnièko ime ili lozinku (provjerite u emailu koji vam je poslan prilikom registracije) ili je admin obrisao vaš nalog iz nekog razloga. Ako je ovo drugo razlog onda možda niste pisali ništa? Forumi èesto periodièki brišu korisnike koji ne pišu da bi smanjili velièinu baze. Probajte se ponovo registrirati i ukljuèiti u diskusije.");


$faq[] = array("--","Podešavanja korisnika");
$faq[] = array("Kako da promijenim moja podešavanja?", "Sva vaša podešavanja (ako ste registrirani) su saèuvana u bazi. Da biste im pristupili kliknite na <u>Profil</u> link (obièno je pri vrhu stranice ali možda to nije sluèaj). To æe vam omoguæiti promjenu podešavanja");
$faq[] = array("Vrijeme je pogrešno!", "Vrijeme je obièno toèno, u svakom sluèaju ono što vidite je vrijeme prikazano u vremenskoj zoni razlièitoj od vaše. U tom sluèaju trebali bi promjeniti vaša podešavanja za vremensku zonu da bi se poklopila sa mjestom gdje se vi nalazite, npr. London, Pariz, New York, Zagreb, itd. Znajte da promjena vremenske zone, kao i mnoga druga podešavanja mogu promjeniti samo registrirani korisnici. Ako niste registrirani sada je pravi èas da to uèinite!");
$faq[] = array("Promijenio sam svoju vremensku zonu i sat je još uvijek netoèan!", "Ukoliko ste sigurni da ste toèno podesili vremensku zonu i vrijeme je još uvijek pogrešno najvjerovatnije je problem u pomicanju vremena. Forum nije dizajniran da razlikuje promjene izmeðu zimskog i ljetnog vremena pa æe u ljetnim mjesecima vrijeme biti za sat vremena razlièito od stvarnog lokalnog vremena.");
$faq[] = array("Moj jezik nije na listi!", "Najèešæi razlog ovome su ili admin nije instalirao vaš jezik ili neko još uvijek nije preveo ovaj forum na vaš jezik. Pitajte admina da li može instalirati jezièni paket koji vam je potreban, a ako ne postoji slobodnonapravite prijevod. Više informacija možete pronaæi na stranicama phpBB grupe (pogledajte link pri dnu stranica)");
$faq[] = array("Kako da prikažem sliku pored mog korisnièkog imena?", "Mogu postojati dvije slike ispod korisnièkog imena kada pregledate poruke. Prva slika je asocijacija na vašu poziciju i izgleda kao skup zvjezdica ili kvadratiæa koji pokazuju koliko ste poruka napisali na forumu. Ispod ove može biti veæa slika poznatija kao avatar, ona je generalno jedinstvena ili osobna za svakog korisnika. Na adminu je da omoguæi avatare i oni imaju izbor na koji naèin æe avatari biti dostupni. Ukoliko niste u moguænosti da koristite avatare onda je to odluka admina, trebali bi ih pitati za razlog tome (sigurni smo da æe biti dobar!)");
$faq[] = array("Kako mijenjam svoju poziciju?", "Generalno ne možete direktno mijenjati naziv bilo koje pozicije (pozicije se pojavljuju ispod korisnièkog imena u temama i u vašem profilu u odnosu prema stilu). Mnogi forumi koriste pozicije da prikažu broj poruka koje ste napisali i da bi identificirali odreðene korisnike, npr. urednici i admini mogu imati specijalne pozicije. Molimo vas da ne zloupotrebljavate forum pišuæi nepotrebne stvari samo da bi poveæali svoju poziciju, vjerojatno æe vam urednik ili admin jednostavno smanjiti rank.");
$faq[] = array("Kada kliknem na email link za korisnika traži se da se prijavim?", "Žao nam je ali samo registrirani korisnici mogu slati email drugima putem ugraðenog email formulara (ako admin omoguæi ovu opciju). Ovo je da bi se sprjeèila zlonamjerna upotreba email-a od strane anonimnih korisnika.");


$faq[] = array("--","Problemi prilikom pisanja");
$faq[] = array("Kako da napišem poruku na forumu?", "Jednostavno, kliknite na potrebni gumb na bilo kojem ekranu foruma ili teme. Možda æete morati da se registrirate prije nego što æete biti u moguænosti da šaljete poruke, moguænosti koje su vam dostupne su izlistane na dnu ekrana foruma i tema (to su <i>Možete pisati nove teme, Možete glasati, itd.</i>)");
$faq[] = array("Kako da promijenim ili izbrišem poruku?", "Ukoliko niste urednik ili moderator možete samo mijenjati i brisati vlastite poruke. Možete promijeniti poruku  (ponekad za samo odreðeno vrijeme poslje pisanja) tako što æete kliknuti na <i>izmijeni</i> gumb za potrebnu poruku. Ako je neko veæ odgovorio na poruku primijetit æete mali tekst ispod poruke kada se vratite na teme, ovo pokazuje broj koliko puta ste mijenjali poruku. Ovo æe se pojaviti samo ako niko nije odgovorio, i takoðer se neæe pojaviti ako urednici ili admini izmijene poruku (trebali bi ostaviti poruku šta su mijenjali i zašto). Znajte i to da obièni korisnici ne mogu brisati poruke kada neko na njih odgovori.");
$faq[] = array("Kako da dodam potpis mojoj poruci?", "Da biste dodali potpis prvo ga morate napraviti, ovo æete napraviti putem vašeg profila. Kada ga napravite možete oznaèiti <i>Dodaj potpis</i> box na formularu da bi dodali potpis. Možete takoðer standardno dodati potpis svim vašim porukama ukljuèivanjem potrebnog radio gumba u vašem profilu (još uvijek možete sprjeèiti dodavanje potpisa pojedinaènim porukama tako što æete maknuti oznaèavanje sa boxa na formularu)");
$faq[] = array("Kako da napravim glasanje?", "Izrada glasanja je lagana, kada šaljete novu poruku (ili ureðujete prvu poruku u temi, ako imate dozvolu) vidjet æete <i>Dodaj glasanje</i> formular ispod glavnog formulara za poruke (ako ga ne vidite onda vjerojatno nemate pravo da napravite glasanje). Trebate unijeti naslov glasanja a onda i najmanje dvije opcije (da biste dodali opcije upišite ime pitanja i kliknite na <i>Dodaj opciju</i> gumb. Takoðer možete odrediti vremensko ogranièenje za glasanje, 0 je beskonaèno glasanje. Postoji ogranièenje broja opcija koje odreðuje admin");
$faq[] = array("Kako da izmijenim ili izbrišem glasanje?", "Kao sa porukama, glasanje može biti izmijenjeno od onoga ko je napravio glasanje, urednika ili admina foruma. Da biste izmijenili glasanje kliknite na prvu poruku u temi (ona uvijek ima pridruženo glasanje). Ako nitko nije glasao onda korisnici mogu izbrisati glasanje ili mijenjati bilo koju opciju glasanja, ali ako su korisnici veæ glasali samo urednici ili admini mogu izmijeniti ili brisati glasanje. Ovo sprjeèava namještanje glasanja mijenjanjem opcija na pola glasanja");
$faq[] = array("Zašto ne mogu pristupiti forumu?", "Neki forumi mogu biti ogranièeni za odreðene korisnike ili grupe. Da biste pregledali, èitali, pisali itd. trebat æe vam posebna dozvola, samo urednik i admin mogu garantirati ovakav pristup, trebali bi ih kontaktirati.");
$faq[] = array("Zašto ne mogu glasati?", "Samo registrirani korisnici mogu glasati. Ako ste registrirani i još uvijek ne možete glasati onda vjerojatno nemate potrebna prava pristupa.");


$faq[] = array("--","Formatiranje i tipovi tema");
$faq[] = array("Šta je BBCode?", "BBCode je specijalna implementacija HTML-a, a da li ga možete koristit zavisi od admina (možete ga takoðer iskljuèiti na formularu za slanje). BBCode je slièan stilovima u HTML-u, tagovi su ubaèeni izmeðu vitièastih zagrada [ i ] prije nego &lt; i &gt; i nude veæu kontrolu kako i što se prikazuje. Za više informacija o BBCode pogledajte uputstvo kojeme možete pristupiti sa strane za pisanje.");
$faq[] = array("Da li mogu koristiti HTML?", "To zavisi od toga da li vam to admin dozvoljava. Ako vam je dozvoljeno da ga koristite vidjet æete da samo neki tagovi rade. Ovo je <i>sigurnosna mjera</i> da bi se sprijeèilo ljuda da uznemiravaju forum koristeæi tagove koji mogu poremetiti izgled ili uzrokovati probleme. Ako je HTML ukljuèen možete ga iskljuèiti na formularu za pisanje.");
$faq[] = array("Šta su smajliji?", "Smajliji ili emotivne ikonice su male slièice koje se koriste da bi iskazali osjeæaje koristeæi kratki kod, npr. :) znaèi sretan, :( znaèi tužan. Kompletnu listu smajlija možete vidjeti na formularu za slanje poruke. Pokušajte ne pretjerivati sa smajlijima, mogu vrlo lako prouzroèiti da poruka postane neèitka i urednik može odluèiti da ih izmijeni ili izbriše sve poruke zajedno");
$faq[] = array("Mogu li slati slike?", "Moguèe je prikazati slike u vašim porukama. Ipak trenutno nema moguænosti da pošaljete sliku direktno na forum. Morate linkati sliku koja postoji na javno dostupnom web serveru, npr. http://www.some-unknown-place.net/my-picture.gif. Ne možete linkati slike koje se nalaze na vašem PC-u (osim ako nije javno dostupan server) niti slike koje se nalaze iza authentifikacijskih mehanizama, npr. hotmail ili yahoo sanduèiæi, lozinkom zaštiæeni site-ovi, itd. Da biste prikazali sliku koristite ili BBCode [img] tag ili odgovarajuæi HTML (ako je dozvoljeno).");
$faq[] = array("Šta su obavijesti?", "Obavijesti èesto sadrže važnu informaciju i trebalo bi ih što prije proèitati. Obavijesti se pojavljuju na vrhu svake strane u forumu na kojemu su postavljene. Da li možete ili ne možete slati obavijesti zavisi od dozvola koje su admini postavili.");
$faq[] = array("Šta su ljepljive teme?", "Ljepljive teme se prikazuju ispod obavijesti u pregledu foruma i samo na prvoj stranici. Obièno su prilièno važne pa bi ih trebalo što prije proèitati. Kao i sa obavijestima forum admin odluèuje o dozvolama koje su potrebne da biste poslali ljepljive teme u svakom forumu.");
$faq[] = array("Šta su zakljuèane teme?", "Zakljuèane teme su postavljene na ovaj naèin ili od urednika ili administratora. Ne možete odgovarati na zakljuèane teme i bilo koje glasanje koje sadrži je automatski završeno. Teme mogu biti zakljuèane iz mnogo razloga.");


$faq[] = array("--","Korisnièki nivoi i grupe");
$faq[] = array("Šta su admini?", "Admini su ljudi kojima su dodjeljeni najviši nivoi kontrole za cijeli forum. Ovi ljudi mogu kontrolirati svaki dio i sve operacije foruma koje ukljuèuju postavljanje dozvola, zabranjivanje pristupa korisnicima, izrada korisnièkih grupa ili urednika, itd. Takoðer imaju kompletne moguænosti ureðivanja u svim forumima.");
$faq[] = array("Šta su urednici?", "Urednici su pojedinci (ili grupa pojedinaca) èiji je posao da prate rad foruma iz dana u dan. Imaju dozvole da mijenjaju ili brišu poruke i zakljuèavaju ili oktljuèavaju, pomièu, brišu i dijele teme u forumima koje ureðuju. Opæenito urednici su tu kako bi sprjeèili ljude da <i>odlutaju sa teme</i> ili šalju uvrjedljiv ili neprikladan materijal.");
$faq[] = array("Šta su korisnièke grupe?", "Korisnièke grupe su naèim putem kojega admini mogu grupirati korisnike. Svaki korisnik može pripadati u više grupa (za razliku od veæine drugih foruma) i svakoj grupi mogu biti dodjeljena individualna prava pristupa. Ovo olakšava adminima da odrede više korisnika kao urednike foruma, ili da im daju pristup privatnom forumu itd.");
$faq[] = array("Kako da se pridružim korisnièkoj grupi?", "Da biste se pridružili korisnièkoj grupi kliknite na link Korisnièke grupe u zaglavlju stranice (zavisi od dizajna podloge), i tada možete vidjeti sve korisnièke grupe. Nisu sve grupe <i>otvorenog pristupa</i>, neke su zatvorene i neke mogu èak imati skrivene èlanove. Ako je forum otvoren onda možete zahtijevati da se prikljuèite grupi klikom na odgovarajuæi gumb. Urednik grupe æe vam morati odobriti zahtjev, mogu vas pitati i zašto želite da se prikljuèite. Molimo vas da ne uznemiravate urednike ukoliko vaš zahtjev ne bude odobren, sigurno da za to imaju razloga.");
$faq[] = array("Kako da postanem urednik korisnièke grupe?", "Korisnièke grupe su prvobitno napravljene od admina foruma, i takoðer imaju dodjeljenog urednika. Ako ste zainteresirani za stvaranje korisnièke grupe onda prvo trebata kontaktirati admina, probajte mu poslati privatnu poruku.");


$faq[] = array("--","Privatne poruke");
$faq[] = array("Ne mogu slati privatne poruke!", "Za ovo postoje tri razloga; niste registrirani i/ili niste prijavljeni, admini je iskljuèio privatne poruke za cijeli forum ili vas je sprijeèio da šaljete poruke. Ako je ovo poslednje, trebali biste pitati admin zašto.");
$faq[] = array("Uporno dobijam neželjene privatne poruke!", "Ubuduæe æemo dodati ignore listu za privatne poruke. Za sada ako i dalje dobijate neželjene privatne poruke od nekog obavijestite admina, oni imaju moguænost da sprijeèe korisnika da šalje privatne poruke.");
$faq[] = array("Dobio sam spam ili uvredljiv materijal od nekog sa ovog foruma!", "Žao nam je što to èujemo. Email formular ovog foruma ima mjere sigurnosti za praæenje korisnika koji šalju takve poruke. Trebalo bi poslati email adminu sa kompletnom kopijom email-a kojeg ste dobili, vrlo je važno da email pošaljete sa zaglavljem (ovdje se nalaze detalji o korisniku koji je poslao email). Onda oni mogu stupiti u akciju.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","O phpBB 2");
$faq[] = array("Tko je napisao ove forume?", "Ovaj software (u svojoj nemodificiranoj formi) je proizveden, pušten i ima copyright <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Grupe</a>. Dostupan je pod GNU General Public Licence i može se slobodno distribuirati, pogledajte link za više detalja");
$faq[] = array("Zašto nije dostupna moguænost X-a?", "Ovaj software je napisan i licenciran kroz phpBB Grupu. Ako vjerujete da bi ova moguænost trebala biti dodana onda vas molimo da posjetite phpbb.com web site i pogledate što phpBB Grupa kaže. Molimo vas da ne šaljete zahtjeve za moguænostima na forum na phpbb.com, Grupa koristi izvore znanja za prihvaæanje novih moguænosti. Molimo vas da proèitate kroz forume i pogledate što, i koliko, naša pozicija je možda veæ za tu moguænost i onda pratite proceduru da biste došli tamo.");
$faq[] = array("Zašto da vas kontaktiram o uvrijedljivom materijalu i/ili legalnim stvarima pripisanim ovom forumu?", "Trebalo bi kontaktirati admina ovog foruma. Ukoliko ne znate tko je on, trebali bi prvo kontaktirati jednog od urednika foruma i pitati ga kome se trebate obratiti. Ako još uvijek nema odgovora trebali bi kontaktirati vlasnika domene (otkrijte tko je) ili, ako je forum na besplatnom serveru (npr. yahoo, free.fr, f2s.com, itd.), urednika ili odjel za uvrede tog servisa. Znajte da phpBB Grupa apsolutno nema kontrolu i ne može na bilo koji naèin znati kako, gdje ili tko koristi ovaj forum. Apsolutno je besmisleno kontaktirati phpBB Grupu i povezati je sa bilo kojim legalnim (stati i prestati, obavezan, klevetnièki komentar, itd.) èinjenicama koje nisu direktno povezane sa phpbb.com web site-om ili softwareom phpBB-a. Ako pošaljete email phpBB Grupi o bilo kom treæerazrednom korištenju ovog softwarea onda bi trebali oèekivati kratak ili nikakav odgovor.");

//
// This ends the FAQ entries
//

?>
