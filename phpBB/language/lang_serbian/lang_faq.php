<?php
/***************************************************************************
 *                          lang_faq.php [Serbian]
 *                            -------------------
 *     begin                : Monday Sep 30 2002 
 *     copyright            : (C) 2002 Simic Vladan
 *     email                : vlada@extremecomputers.co.yu
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
$faq[] = array("Zašto ne mogu da se prijavim?", "Da li ste se registrovali? Morate se registrovati ukoliko želite da se prijavite. Da li imate zabranu pristupa boardu (ako jeste biæe prikazana poruka)? Ako je to u pitanju, trebalo bi da kontaktirate webmastera ili administratora i saznate žašto je tako. Ako ste registrovani i nemate zabranu i još uvek ne možete da se prijavite onda ponovo proverite vaše korisnièko ime i šifru. Obièno je ovo problem, a ako nije onda kontaktirajte administratora jer možda nešto nije u redu sa boardom.");
$faq[] = array("Zašto uopšte moram da se registrujem?", "Ne morate takoðe, na administratoru boarda je da odluèi da li možete slati poruke ako niste registrovani. U svakom sluèaju registracija vam omoguæuje pristu dodatnim moguænostima koje nisu dostupne gostima kao što su biranje avatar slika, privatne poruke, slanje emaila drugim korisnicima, prikljuèivanje grupama itd. Potrebno je svega nekoliko minuta za registraciju pa vam preporuèujemo da se registrujete.");
$faq[] = array("Zašto se odjavljujem automatski?", "Ako ne ukljuèite <i>Prijavi me automatski</i> kada se prijavljujete board æe vas pamtiti kao prijavljenog svo prisutno vreme. Ovo spreèava da se neko drugi prijavi kao vi. Da biste ostali prijavljeni štiklirajte kutijicu prilikom prijavljivanja, mada ovo ne preporuèujemo ako boardu pristupate sa deljenog kompjutera, npr. biblioteke, internet cafea, fakulteta itd.");
$faq[] = array("Kako da spreèim da se moje ime ne pojavljuje na listi korisnika koji su trenutno na forumu?", "U vašem profilu æete naæi opciju <i>Sakrij moj onlajn status</i>, ako ovo promenite na <i>Ukljuèeno</i> biæete vidljivi samo administrators ili sami sebi. Biæete brojani kao skriveni korisnik.");
$faq[] = array("Izgubio sam šifru!", "Ne panièite! Iako vaša šifra ne može biti vraæena, ona može biti resetovana. Da biste ovo uradili idite na stranicu za prijavu i kliknite na <u>Zaboravio sam šifru</u>, pratite instrukcije i brzo æete ponovo biti u moguænosti da se prijavite");
$faq[] = array("Registrovao sam se ali ne mogu da se prijavim!", "Prvo proverite da li ste uneli taèno korisnièko ime i šifru. Ako je to u redu onda je moguæa jedna od ove dve greške. Ako je COPPA podrška ukljuèena i kliknuli ste na <u>Mlaði-a sam od 13 godina</u> prilikom registracije onda æete morati da pratite uputstva koja ste dobili. Ukoliko i ovo ne pomogne proverite da li vaš nalog treba reaktivirati? Neki boardi æe zahtevati da ponovo budete registrovani, ili sami ili od strane administratora pre nego [to budete u moguænosti da se prijavite. Kada se registrujete biæe vam reèeno da li trebate da reaktivirate nalog. Ako vam je poslat email onda pratite instrukcije, a ako niste proverite da li je taèan vaš email? Jedan razlog za aktivacijom je da bi se smanjila moguænost da <i>korisnici koji varaju </i> anonimno vreðaju board. Ako ste sigurni da je email adresa koju ste koristili taèna onda kontaktirajte administratora.");
$faq[] = array("Ranije sam se registrovao ali više ne mogu da se prijavim?!", "Najèešæi razlozo za ovo su; uneli ste pogrešno korisnièko ime ili šifru (proverite u mailu koji vam je poslat prilikom registracije) ili je administrator obrisao vaš nalog iz nekog razloga. Ako je ovo drugo razlog onda možda niste pisali ništa? Èesto boardi periodièno brišu korisnike koji ne pišu da bi smanjili velièinu baze. Probajte da se ponovo registrujete i ukljuèite u diskusije.");


$faq[] = array("--","Podešavanja korisnika");
$faq[] = array("Kako da promenim moja podešavanja?", "Sva vaša podešavanja (ako ste registrovani) su saèuvana u bazi. Da biste im pristupili kliknite na <u>Profil</u> link (obièno je pri vrhu stranice ali možda to nije sluèaj). To æe vam omoguæiti da promenite podešavanja");
$faq[] = array("Vreme je pogrešno!", "Vreme je obièno taèno, u svakom sluèaju ono što vidite je vreme prikazano u vremenskoj zoni razlièitoj od vaše. U tom sluèaju trebalo bi da promenite vaša podešavanja za vremensku zonu da bi se poklopila sa megtom gde se vi nalazite, npr. London, Pariz, Njujork, Sidnej, itd. Znajte da promena vremenske zone, kao i mnoga druga podešavanja mogu promeniti samo registrovani korisnici. Ako niste registrovani sada je dobro vreme da to i uèinite!");
$faq[] = array("Promenio sam svoju vremensku zonu i sat je još uvek netaèan!", "Ukoliko ste sigurni da ste taèno podesili vremensku zonu i vreme je još uvek pogrešno najverovatnije je problem u pomeranju vremena (ili letnje vreme kako je to poznato u UK i drugim mestima). Board nije dizajniran da razlikuje promene izmeðu standardnog i pomerenog vremena pa æe u letnjim mesecima vreme biti za sat vremena razlièito od stvarnog lokalnog vremena.");
$faq[] = array("Moj jezik nije na listi!", "Najèešæi razlog ovome su ili administrator nije instalirao vaè jezik ili neko još uvek nije preveo ovaj board na vaš jezik. Pitajte administratora da li može da instalira jezièki paket koji vam je potreban, a ako ne postoji oseæajte se slobodnim da napravite prevod. Više informacija možete pronaæi na sajtu phpBB grupe (pogledajte link pri dnu stranica)");
$faq[] = array("Kako da prikažem sliku pored mog korisnièkog imena?", "Mogu postojati dve slike ispod korisnièkog imena kada pregledate poruke. Prva slika je asocijacija na tvoju poziciju i izgleda kao skup zvezdica ili kvadratiæa koji pokazuju kojiko ste poruka napisali na forumu. Ispod ove može biti veæa slika poznatija kao avatar, ona je generalno unikatna ili lièna za svakog korisnika. Na administratoru je da omoguæi avatare i oni imaju izbor na koji naèin æe avatars biti dostupni. Ukoliko niste u moguænosti da koristite avatare onda je to odluka administratora, trebalo bi da ih upitate za razlog tome (sirurni smo da æe biti dobar!)");
$faq[] = array("Kako menjam svoju poziciju?", "Generalno ne možete direktno menjati naziv bilo koje pozicije (pozicije se pojavljuju ispod korisnièkog imena u temama i u vašem profilu u zavisnosti od stila). Mnogi boardi koriste pozicije da prikažu broj poruka koje ste napisali i da bi identifikovali odreðene korisnike, npr. urednici i administratori mogu imati specijalne pozicije. Molimo vas da ne zloupotrebljavate board pišuæi nepotrepštine samo da bi poveæali svoju poziciju, verovatno æe vam urednik ili administrator jednostavno smanjiti rank.");
$faq[] = array("Kada kliknem na email link za korisnika traži se da se prijavim?", "Žao nam je ali samo registrovani korisnici mogu slati email drugima putem ugraðene email forme (ako administrator omoguæi ovu opciju). Ovo je da bi se spreèila zlonamerna upotreba email-a od strane anonimnih korisnika.");


$faq[] = array("--","Problemi prilikom pisanja");
$faq[] = array("Kako da napišem poruku na forumu?", "Jednostavno, kliknite na relevantno dugme na bilo kojem ekranu foruma ili teme. Možda æete morati da se registrujete pre nego što æete biti u moguænosti da šaljete poruke, moguænosti koje su vam dostupne su izlistane na dnu ekrana foruma i tema (to su <i>Možete pisati nove teme, Možete glasati, itd.</i>)");
$faq[] = array("Kako da izmenim ili izbrišem poruku?", "Ukoliko niste urednik ili moderator možete samo menjati i brisati sopstvene poruke. Možete izmrniti poruku  (ponekad za samo odreðeno vreme posle pisanja) tako što æete kliknuti na <i>izmeni</i> dugme za relevantnu poruku. Ako je neko veæ odgovorio na poruku primetiæete mali tekst ispod poruke kada se vratite na teme, ovo pokazuje broj koliko puta ste menjali poruku. Ovo æe se pojaviti samo ako niko nije odgovorio, i takoðe se neæe pojaviti ako urednici ili administratori izmene poruku (trebalo bi da ostave poruku šta su menjali i zašto). Znajte i to da obièni korisnici ne mogu brisati poruke kada neko na njih odgovori.");
$faq[] = array("Kako da dodam potpis mojoj poruci?", "Da biste dodali potpis prvo ga morate napraviti, ovo æete uratiti putem vašeg profila. Kada ga napravite možete štiklirati <i>Dodaj potpis</i> kutijicu na formi da bi dodali potpis. Možete takoðe standardno dodati potpis svim vašim porukama ukljuèivanjem relevantnog radio dugmeta u vašem profilu (još uvek možete spreèiti dodavanje potpisa pojedinaènim porukama tako èto æete odèekirati kutijicu na formi)");
$faq[] = array("Kako da napravim glasanje?", "Pravljenje glasanja je lako, kada šaljete novu poruku (ili editujete prvu poruku u temi, ako imate dozvolu) videæete <i>Dodaj glasanje</i> formu ispod glavne forme za poruke (ako je ne vidite onda verovatno nemate prava da napravite glasanje). Trebate da unesete naslov glasanja a onda i najmanje dve opcije (da biste dodali opcije upišite ime pitanja i kliknite na <i>Dodaj opciju</i> dugme. Takoðe možete podesiti vremenski limit za glasanje, 0 je beskonaèno glasanje. Postoji limit broja opcija koje odreðuje administrator");
$faq[] = array("Kako da izmenim ili izbrišem glasanje?", "Kao sa porukama, glasanje može biti izmenjeno od onoga ko je napravio glasanje, urednika ili administratora boarda. Da biste izmenili glasanje kliknite na prvu poruku u temi (ona uvek ima pridruženo glasanje). Ako niko nije glasao onda korisnici mogu izbrisati glasanje ili menjati bilo koju opciju glasanja, ali ako su korisnici veæ glasali samo urednici ili administratori mogu mogu izmeniti ili brisati glasanje. Ovo spreèava nameštanje glasanja menjanjem opcija na pola glasanja");
$faq[] = array("Zašto ne mogu pristupiti forumu?", "Neki forumi mogu biti ogranièeni za odreðene korisnike ili grupe. Da biste pregledali, èitali, pisali itd. trebaæe vam posebna dozvola, samo urednik i administrator mogu garantovati ovakav pristup, trebalo bi da ih kontaktirate.");
$faq[] = array("Zašto ne mogu da glasam?", "Samo registrovani korisnici mogu glasati. Ako ste registrovani i još uvek ne možete glasati onda verovatno nemate adekvatna prava pristupa.");


$faq[] = array("--","Formatiranje i tipovi tema");
$faq[] = array("Šta je BBKod?", "BBKod je specijalna implementacija HTML-a, a da li možete da ga koristite zavisi od administratora (možete ga takoðe iskljuèiti na formi za slanje). BBKode je slièan stilovima u HTML-u, tagovi su ubaèeni izmeðu vitièastih zagrada [ i ] pre nego &lt; i &gt; i nude veæu kontrolu koko i šta se prikazuje. Za više informacija o BBKodu pogledajte uputstvo kome možete pristupiti sa strane za pisanje.");
$faq[] = array("Da li mogu da koristim HTML?", "To zavisi od toga da li vam to administrator dozvoljava, imaju potpunu kontrolu nad njim. Ako vam je dozvoljeno da ga koristite videæete da samo neki tagovi rade. Ovo je <i>mera bezbednosti</i> da bi spreèila ljude da uznemiravaju board koristeæi tagove koji mogu poremetiti izgled ili prouzrokovati probleme. Ako je HTML ukljuèen možete ga iskljuèiti na formi za pisanje.");
$faq[] = array("Šta su smajliji?", "Smajliji ili emotivne ikonice su male slièice koje se koriste da bi iskazali oseæanja koristeæi kratak kod, npr. :) znaèi sreæan, :( znaèi tužan. Kompletnu listu smajlija možete videti na formi sa slanje poruke. Pokušajte da ne preterujete sa smajlijima, mogu vrlo lako prouzrokovati da poruka postane neèitljiva i urednik može odluèiti da ih izmeni ili izbriše sve poruke zajedno");
$faq[] = array("Mogu li slati slike?", "Slike možete zaista prikazati u vašim porukama. Ipak trenutno nema moguænosti da pošaljete sliku direktno na board. Morate linkovati sliku koja postoji na javno dostupno web serveru, npr. http://www.some-unknown-place.net/my-picture.gif. Ne možete linkovati slike koje se nalaze na vašem PC-u (osim ako nije javno dostupan server) niti slike koje se nalaze iza authentifikacionih mehanizama, npr. hotmail ili yahoo sanduèiæi, šifrom zaštiæeni sajtovi, itd. Da biste prikazali sliku koridtite ili BBKod [img] tag ili odgovarajuæi HTML (ako je dozvoljeno).");
$faq[] = array("Šta su obaveštenja?", "Obaveštenja èesto sadrže važnu informaciju i trebalo bi da ih što pre proèitate. Obaveštenja se pojavljuju na vrhu svake strane u forumu na kome su postavljene. Da li možete ili ne možete da šaljete obaveštenja zavisi od dozvola koje su podešene od strane administratora.");
$faq[] = array("Šta su lepljive teme?", "Lepljive teme se prikazuju ispod obaveštenja u pregledu foruma i samo na prvoj stranici. Obièno su prilièno važne pa bi trebalo da ih proèitate što pre. Kao i sa obaveštenjima board administrator odluèuje o dozvolama koje su potrebne da biste poslali lepljive teme u svakom forumu.");
$faq[] = array("Šta su zakljuèane teme?", "Zakljuèane teme su postavljene na ovaj naèin bilo od urednika ili administratora. Ne možete odgovarati na zakljuèane teme i bilo koje glasanje koje sadrži je automatski završeno. Teme mogu biti zakljuèane iz mnogo razloga.");


$faq[] = array("--","Korisnièki nivoi i grupe");
$faq[] = array("Šta su administratori?", "Administratori su ljudi kojima su dodeljeni najviši nivoi kontrole za ceo board. Ovi ljudi mogu da kontrolišu saki deliæ i sve operacije boarda koje ukljuèuju postavljanje dozvola, zabranjivanje pristupa korisnicima, pravljenje korisnièkih grupa ili urednika, itd. Takoðe imaju kompletne moguænosti ureðivanja u svim forumima.");
$faq[] = array("Šta su urednici?", "Urednici su pojedinci (ili grupa pojedinaca) èiji je posao da prate rad foruma iz dana u dan. Imaju dozvole da menjaju ili brišu poruke i zakljuèavaju ili oktljuèavaju, pomeraju, brišu i dele teme u forumima koje ureðuju. Uopšte urednici su tu da spreèe ljude da <i>odlutaju sa teme</i> ili šalju uvredljiv ili neprikladan materijal.");
$faq[] = array("Šta su korisnièke grupe?", "Korisnièke grupe su naèim putem koga administratori mogu da grupišu korisnike. Svaki korisnik može pripadati u više grupa (za razliku od veæine drugih boarda) i svakoj grupi mogu biti dodeljena individualna prava pristupa. Ovo olakšava administratorima da podese više korisnika kao urednike foruma, ili da im daju pristup privatnom forumu itd.");
$faq[] = array("Kako da se pridružim korisnièkoj grupi?", "Da biste se pridružili korisnièkoj grupi kliknite na link Korisnièke grupe u zaglavlju stranice (zavisi od dizajna podloge), i tada možete videti sve korisnièke grupe. Nisu sve grupe <i>otvorenog pristupa</i>, neke su zatvorene i neke mogu èak imati skrivene èlanove. Ako je board otvren onda možete zahtevati da se prikljuèite grupi klikom na odgovarajuæe dugme. Urednik grupe æe morati da vam odobri vaš zahtev, mogu vas pitati i zašto želite da se prikljuèite. Molimo vas da ne uznemiravate urednike ukoliko vaš zahtev ne bude odobren, sigurno da za to imaju razloga.");
$faq[] = array("Kako da postanem urednik korisnièke grupe?", "Korisnièke grupe su prvobitno napravljene od administratora boarda, i takoðe imaju dodeljenog urednika. Ako ste zainteresovani za stvaranje korisnièke grupe onda prvo trebata da kontaktirate administratora, probajte da mu pošaljete privatnu poruku.");


$faq[] = array("--","Privatne poruke");
$faq[] = array("Ne mogu da šaljem privatne poruke!", "Za ovo postoji tri razloga; niste registrovani i/ili niste prijavljeni, administrator je iskljuèio privatne poruke za ceo board ili vas je spreèio da šaljete poruke. Ako je ovo poslednje u pitanju, trebali biste da pitate administratora zašto je tako.");
$faq[] = array("Uporno dobijam neželjene privatne poruke!", "U buduæe æemo dodati listu ignorisanja za privatne poruke. Za sada ako i dalje dobijate neželjene privatne poruke od nekog obavestite administratora, oni imaju moguænost da spreèe korisnika da uopšte šalje privatne poruke.");
$faq[] = array("Dobio sam spam ili uvredljiv materijal od nekog sa ovog boarda!", "Žao nam je što to èujemo. email forma ovog boarda ima mere bezbednosti da pokuša i prati korisnike koji šalju takve poruke. Trebalo bi da pošaljete email administratoru sa punom kopijom email-a kojeg ste dobili, vrlo je važno da mail pošaljete sa zaglavljem (ovde se nalaze detalji o korisniku koji je poslao email). Onda oni mogu stupiti u akciju.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","O phpBB 2");
$faq[] = array("Ko je napisao ovaj bilten board?", "Ovaj softver (u svojoj nemodifikovanoj formi) je proizveden, pušten i ima kopirajt <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Grupe</a>. Dostupan je pod GNU General Public Licence i može se slobodno distribuirati, pogledajte link za više detalja");
$faq[] = array("Zašto nije dostupna moguænost X-a?", "Ovaj softver je napisan i licenciran kroz phpBB Grupu. Ako verujete da bi ova moguænost trebala biti dodata onda vas molimo da posetite phpbb.com web sajt i pogledate šta phpBB Grupa ima da kaže. Molimo vas da ne šaljete zahteve za moguænostima na board na phpbb.com, Grupa koristi izvore znanja za prihvatanje novih moguænosti. Molimo vas da progitate kroz forume i pogledate šta, i koliko, naša pozicija je možda veæ za tu moguænost i onda pratite procedutu da biste došli dotle.");
$faq[] = array("Zašto da vas kontaktiram o uvredljivom materijalu i/ili legalnim stvarima pripisanim ovom boardu?", "Trebalo bi da kontaktirate administratora ovog boarda. Ukoliko neznate ko je on, trebalo bi da prvo kontaktirate jednog od urednika foruma i pitate ga kome da se obratite. Ako još uvek nema odgovora trebalo bi da kontaktirate vlasnika domena (uradite pretragu ko je) ili, ako je board na besplatnom serveru (npr. yahoo, free.fr, f2s.com, itd.), menadžera ili odeljenje za uvrede tog servisa. Znajte da phpBB Grupa apsolutno nema kontrolu i ne može na bilo koji naèin da sazna kako, gde ili ko koristi ovaj board. Apsolutno je besmisleno kontaktirati phpBB Grupu i povezati je sa bilo kojim legalnim (stati i prestati, obavezan, klevetnièki komentar, itd.) èinjenicama koje nisu direktno povezane sa phpbb.com web sajtom ili softwerom phpBB-a. Ako pošaljete email phpBB Grupi o bilo kom treæerazrednom korišæenju ovog softvera onda bi trebalo da oèekujete sažet odgovor ili nikakav odgovor uopšte.");

//
// This ends the FAQ entries
//

?>
