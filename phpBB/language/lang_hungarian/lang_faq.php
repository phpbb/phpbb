<?php
/***************************************************************************
 *                          lang_faq.php [Hungarian]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_faq.php,v 1.4.2.2 2002/08/04 17:21:22 dougk_ff7 Exp $
 *
 *     translated by        : Szilard Andai
 *     email                : iranon@send.hu            
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
 
  
$faq[] = array("--","Belépési és Regisztráció tudnivalók");
$faq[] = array("Miért nem tudok belépni?", "Regisztráltad magadat? Elõször regisztrálnod kell magadat ahhoz, hogy beléphess. Ki lettél tiltva a Fórumról (ebben az esetben egy üzenet jelenik meg)? Lépj kapcsolatba a webmesterrel, vagy az Adminisztrátorral, hogy miért. Ha regisztrálva vagy, és nem vagy kitiltva az oldalról, akkor ellenõrizd, hogy nem gépelted-e el a nevet, vagy a jelszót. Ellenõrizd kétszer is, mivel legtöbbször ez a hiba. Ha a hiba még mindig fennáll, akkor lépj kapcsolatba az Adminisztrátorral, megadva a pontos hibát.");
$faq[] = array("Miért kell regisztrálnom magamat", "A regisztráció nem kötelezõ, ez az Adminisztrátoron múlik, hogy szükségessé teszi-e a hozzászólások küldéséhez. A regisztrált felhasználók több szolgáltatáshoz jutnak, melyeket nem érhetõek el a Vendégeknek, mint például Avatar képek, Privát üzenet küldése, email küldés, csoport tagságok, stb. Pusztán néhány perc a regisztráció, megéri a több funkció érdekében.");
$faq[] = array("Miért lép ki automatikusan a rendszerbõl?", "Amennyiben a belépésnél nem jelölte be az <i>Automatikus belépés</i> lehetõséget, akkor csak egy meghatározott ideig lesz a rendszerben belépve. Ez megakadályozza, hogy illetéktelenek férjenek hozzá az azonosítójához, vagy hogy a nevedben írjanak a Fórumba. Nem ajánlott az <i>Automatikus belépés</i> használata ha nyilvános helyrõl használod a Fórumot, mint például könyvtár, internet kávézó, stb.");
$faq[] = array("Hogyan tudom megakadályozni, hogy mások lássák mikor vagyok épp jelen a Fórumon?", "A Profilban beállítható egy opció, mellyel ez letiltható: <i>Jelenlét elrejtése</i>. Ha ezt bekapcsolod, akkor csak az Adminisztrátor, vagy saját magad fogod látni, hogy jelen vagy-e. A bekapcsolásával Rejtett felhasználónak számítasz.");
$faq[] = array("Elfelejtettem a jelszavamat!", "Semmi pánik! A jelszót nem lehet kideríteni, de meg lehet változtatni, újat készíttetni. Ehhez menj vissza a Belépés oldalra, kattints az <u>Elfelejtettem a jelszót</u> linkre, kövesd az utasításokat, és rövid idõn belül újra be tudsz lépni a Fórumba.");
$faq[] = array("Regisztráltam magamat, de mégsem tudok belépni!", "Elõször ellenõrizd, hogy helyes nevet, és jelszót adtál-e meg. Ha ez rendben van, akkor két dolog miatt nem tudsz belépni. Az egyik, hogy néhány Fórumhoz aktiválni kell a regisztrációt a belépéshez. Ezt vagy neked, vagy az Adminisztrátornak kell megtennie, mielõtt megpróbálnál belépni. A regisztráció végén megtudhatod, hogy szükséges-e aktiválnod, a regisztrációt. Az aktiválás lehet email által: ekkor ellenõrizd a regisztrációnál megadott emailcímedet, és kövesd a benne lévõ utasításokat. Az aktivációra azért van szükség, hogy megakadályozzuk a Fórum névtelenségével visszaélõ felhasználókat. Ha biztos vagy benne, hogy érvényes emailt adtál meg, és belátható idõn belül nem kaptál emailt, akkor lépj kapcsolatba az Adminisztrátorral.");
$faq[] = array("Régebben regisztráltam magamat, de egy ideje nem tudok belépni?!", "Ennek az a leggyakoribb oka, hogy hibás nevet, vagy jelszót adtál meg (ellenõrizd az emailt, amit regisztrálásnál kaptál), vagy mert az Adminisztrátor valamiért törölte az azonosítódat. Lehet hogy már regisztrálva vagy, de még semmihez sem szóltál hozzá? A Fórum bizonyos idõközönként törli azokat a felhasználókat, akik nem szóltak hozzá a témákhoz. Regisztráld újra magadat, és kapcsolódj be a beszélgetésekbe.");


$faq[] = array("--","Felhasználói beállítások");
$faq[] = array("Hogyan változtathatom meg a beállításaimat?", "Amennyiben regisztrált felhasználó vagy, a beállításaidat a <u>Profil</u> menüpontra kattintva érheted el. Ez általában az oldal tetején található, de nem minden esetben).");
$faq[] = array("A dátum és/vagy idõbeállítás nem pontos!", "Az idõ helyesen jelenik meg, ha helyes idõzóna van beállítva (Magyarország a \"GMT +1\" zónába tartozik). Ezt a szolgáltatást csak regisztrált felhasználók vehetik igénybe.");
$faq[] = array("Megváltoztattam az idõzónát, de még mindig pontatlan az idõ!", "Ha biztosan a helyes idõzóna van beállítva, akkor feltehetõleg azért nem pontos, mivel probléma lehet a kétfajta idõszámítás. Jelenleg a Fórum nem támogatja a téli-nyári idõszámítás változásának követését, így elõfordulhat +-1 óra eltérés az pontos idõhöz képest.");
$faq[] = array("A használni kívánt nyelv nincs a listában!", "Ennek az az oka, hogy az Adminisztrátor nem telepített más nyelvet a Fórumhoz, vagy mert a Fórumhoz nincsen fordítás a kívánt nyelven. Kérd meg az Adminisztrátort, hogy, hogy telepítse a nyelvet a Fórumhoz, vagy ha ilyen nem létezik, nyugodtan készítse el a fordítást. További információért keresse fel a phpBB Csoport weboldalát (a link az oldal alján található).");
$faq[] = array("Hogyan jeleníthetek meg egy képet a nevem alatt?", "A felhasználói név alatt két kép található. Az egyik a Rangot mutatja (ezek általában csillagok, vagy egyéb sorminta, melyek a hozzászólások számának függvényében változnak). Ez alatt van egy nagyobb kép, melyet Avatarnak nevezünk. Az Avatar egy egyedi vagy személyes kép, mely más és más a legtöbb felhasználónak. A Fórum Adminisztrátorától függ, hogy lehetõség van-e Avatar megjelenítésére. Ha nem tud avatart használni, akkor az Adminisztrátor letiltotta ezt a lehetõséget.");
$faq[] = array("Hogyan tudom megváltoztatni a Rangomat?", "Általában a felhasználók nem változtathatják meg közvetlenül a rangot, ez a hozzászólások számától függ. Minél több hozzászólást írsz, annál nagyobb lesz a rangod. A rang általában a felhasználónév alatt látható a témákban. Vannak speciális rangok, mint például \"Adminsztrátor\", vagy \"Moderátor\". Lehetõleg ne szólj hozzá feleslegesen a témákhoz csak hogy növeld a rangodat: az Adminisztrátornak és a Moderátoroknak bármikor lehetõsége van lefokozni rangot.");
$faq[] = array("Miért kell bejelentkezni az emailküldéshez?", "A Fórumon keresztüli emailküldés csak regisztrált felhasználók számára lehetséges. Ez a névtelen felhasználók nemkívánt levelezésének elkerülésére van.");


$faq[] = array("--","Hozzászólással kapcsolatos kérdések");
$faq[] = array("Hogyan szólhatok hozzá a fórumokhoz?", "Egyszerû, a megfelelõ gombra kattintva, mely vagy a fórumban található, vagy a témában. Ha a fórumban készítesz új hozzászólást, akkor adj egy címet a témának. Ha egy témában írsz egy új hozzászólást, akkor nem kötelezõ témát megadni. Hozzászólás küldéséhez elõbb regisztrálnod kell magadat. A fórum vagy a téma alján megtalálhatóak a hozzájuk tartozó jogosultságok (<i>Új témát nyithat, Szavazhat, stb.</i> list)");
$faq[] = array("hogyan szerkeszthetek vagy törölhetek egy hozzászólást?", "Csak abban az esetben módosíthatsz vagy törölhetsz egy hozzászólást, ha azt te készítetted, vagy ha Moderátor, vagy a Fórum Adminisztrátora vagy. Az <i>Átír</i> gombra kattintva tudsz módosítani egy hozzászólást. Ha valaki már válaszolt a hozzászólásra, abban az esetben egy rövid megjegyzést talál, hogy az hányszor lett már módosítva. Ez nem jelenik meg, ha már válaszoltak a hozzászólásra, vagy ha a Moderátorok vagy az Adminisztrátor szerkesztette át. Emlékeztetõül, a sima felhasználók nem törölhetnek egy hozzászólást, melyre már érkezett válasz.");
$faq[] = array("Hogyan csatolhatom az aláírásomat a hozzászóláshoz?", "A csatoláshoz elõször el kell készítened az aláírást; ezt a Profilodban teheted meg. Utána a hozzászólásban be kell kapcsolni az <i>Aláírás hozzáadását</i>. Az aláírás automatikusan is hozzáadható minden hozzászóláshoz, ez szintén a Profilban kapcsolható be (ha ez be van kapcsolva, ettõl függetlenül hozzászólásonként még kikapcsolható).");
$faq[] = array("Hogyan készíthetek szavazást?", "A szavazás készítése egyszerû, amikor egy új témát nyitsz, akkor ezzel együtt egy szavazást is indíthatsz. A szavazás természetesen opcionális, nem kötelezõ a témanyitáshoz. Szavazást úgy is készíthetsz, ha hozzászólásban az Átírra kattintasz (a módosításhoz terrészetesen megfelelõ jogokkal kell rendelkezned). A <i>Szavazás hozzáadása</i> linkre kattintva készítheted el a szavazást. Megadhatsz egy címet a szavazásnak, és legalább két választási lehetõséget írj be (több lehetõség beírásához kattints a <i>Hozzáadás</i> gombra. Ezenkívül idõlimitet is megadhatsz a szavazáshoz, mellyel megadható, hogy hány napig legyen érvényes a szavazás. A szavazási lehetõségek száma meg van határozva, melyet az Adminisztrátor határoz meg.");
$faq[] = array("Hogyan szerkeszthetek vagy törölhetek egy szavazást?", "A hozzászólásokhoz hasonlóan a szavazásokat is csak a szavazás készítõje, egy moderátor, vagy az Adminisztrátor szerkesztheti. Egy szavazás módosításához menj a téma elsõ hozzászólásához (általában ehhez tartozik a szavazás), és kattints az Átírás gombra. Ha senki sem szavazott az adott témában, akkor a készítõje törölheti a szavazást; ha már szavaztak, akkor csak egy Moderátor vagy az Adminisztrátor módosíthatja vagy törölheti.");
$faq[] = array("Miért nem férek hozzá egyes fórumokhoz?", "Néhány fórum csak kiemelt felhasználók és/vagy csoportok számára hozzáférhetõ. A fórum megtekintéséhez, olvasásához, hozzászólás küldéséhez speciális engedély kell, amit vagy a fórum Moderátorától vagy az Adminisztrátortól kaphatsz meg.");
$faq[] = array("Miért nem tudok szavazni?", "Csak regisztrált felhasználók vehetnek részt a szavazásokban (a többszöri szavazás elkerülése végett). Amennyiben regisztrált felhasználó vagy de mégsem tudsz szavazni, akkor nincsen jogosultságod a szavazáshoz.");


$faq[] = array("--","Formázás és téma típusok");
$faq[] = array("Mi az a BBCodeWhat is BBCode?", "A BBCode egy speciális változata a HTML nyelvnek. A BBCode használatának engedélyezése a fórum Adminisztrátorától függ. Ezenkívül lehetõséged van arra, hogy kikapcsold a BBCode-ot a hozzászólásaidban. A BBCode hasonló felépítésû, mint a HTML, kivéve hogy a tagek szögletes zárójelben \"[\" \"]\" vannak; amellett  lehetõséget nyújt hogy hogyan s miként lehet a szövegeket szebben, jobban, tagoltabban megjeleníteni. A használt témától függõen az üzenet mellett megtalálható egy kezelõfelület, mellyel könnyedén és egyszerûen be lehet illeszteni a tageket a szövegbe. Mindig figyeljünk arra, hogy lezárjuk a tageket.");
$faq[] = array("Használhatok HTML-t kódot?", "Ez az Adminisztrátortól függ, õ határozza meg hogy lehet-e használni vagy nem. Ha engedélyezte a használatát, akkor is csak valószínûleg néhány tag fog mûködni. Ennek <i>biztonsági</i> okai vannak; megakadályozza hogy a felhasználók akár véletlenül akár szándékosan megváltoztassák az oldal(ak) formátumát vagy kinézetét, vagy egyéb problémák okozását. A HTML kódokat bárki letilthatja a hozzászólásában.");
$faq[] = array("Mik az Emotikonok?", "Smiley-k vayg másnéven Emotikonok kis grafikák, melyekkel érzéseket lehet kifejeztetni. Például a :) jelentése mosolygás, boldogság, a :( jelentése szomorúság. A használható emotikonok teljes listája megtalálhatók a hozzászólás készítésénél. Lehetõleg ne használj túl sok emotikont, mert nehezen olvashatóvá tehetik a hozzászólást, melyet egy moderátor vagy át fog szerkeszteni, vagy ki fog törölni.");
$faq[] = array("Csatolhatok képeket?", "Egy hozzászólásban képeket is meg lehet jeleníttetni, de ezt csak külsõ webcím megadásával. Jelenleg nincs lehetõség arra, hogy a Fórumra töltsd fel a képeket. A megjelenítéséhez egy publikus, az interneten elérhetõ képet kell megadni, például: http://akarmi.hu/kep.jpg. A saját gépen található képeket nem lehet megjeleníteni (kivéve ha fut egy webszerver a gépen, sem a védett oldalakon található képeket (például levélfiókokban (Hotmail, Yahoo), vagy jelszóval védett oldalakon), stb. A kép megjelenítéséhez használd a BBCode [img] tagjét, vagy a megfelelõ HTML taget (amennyiben ez utóbbi engedélyezve van).");
$faq[] = array("Mik azok a Közlemények?", "A közlemények gyakran fontos információkat tartalmaznak, érdemes ezeket minél hamarabb elolvasni. A közlemények az adott fórumban mindig az oldal tetején, az összes oldalon található meg. Ahhoz hogy valaki közleményt küldjön, megfelelõ jogokkal kell rendelkeznie, melyet az Adminisztrátor állít be.");
$faq[] = array("Mik azok a Kiemelt témák?", "A kiemelt témák a Közlemények alatt jelennek meg, de csak a fórum elsõ oldalán. Ezek általában elég fontos információkat tartalmaznak, ezért ajánlott az elolvasása. Hasonlóan a Közleményekhez, az Adminisztrátor dönti el, hogy ki írhat Kiemelt témát.");
$faq[] = array("Mik azok a lezárt témák?", "A témákat vagy a Moderátorok, vagy az Adminisztrátor zárhatja le. az ilyen témákba nem lehet több hozzászólást vagy szavazatot küldeni. Egy téma lezárásának több oka lehet.");


$faq[] = array("--","Felhasználói szintek és Csoportok");
$faq[] = array("Ki az Adminisztrátor?", "A Adminisztrátor általában a Fórum legmagasabb rangú felhasználója, õ üzemelteti, és tartja karban. Az egész Fórumhoz hozzáférhet, és módosíthatja, például jogosultságokat adhat, letilthat felhasználókat, csoportokat hozhat létre, Moderátori jogosultságokat adhat, stb. Ezenkívül bármely fórumot és témát moderálhatják. Az Adminisztrátor általában egy személy, de ez Fórumtól függ.");
$faq[] = array("Kik azok a Moderátorok?", "A Moderátorok olyan különleges jogosultságokkal rendelkezõ felhasználók, akiknek az a feladata, hogy napról napra figyelemmel kövessék a fórumok mûködését. Jogukban áll bármely hozzászólás szerkesztése vagy törlése, ezenkívül lezárhatják, kinyithatják, áthelyezhetik, törölhetik vagy szétválaszthatják a témákat, amikben moderálhatnak. Általában az a dolguk, hogy eltávolítsák a témába nem illõ hozzászólásokat, vagy a sértegetõ, támadó anyagokat.");
$faq[] = array("Mik azok a Csoportok?", "A Adminisztrátor a Csoportokba rendezheti az azonos érdeklõdési körû felhasználókat. Egy felhasználó több csoportba is tartozhat, és a csoportokhoz különbözõ hozzáférési jogok rendelhetõek. Ezzel könnyedén lehet például zártkörû fórumokat készíteni, vagy moderátori jogokat adni a felhasználóknak.");
$faq[] = array("Hogyan csatlakozhatok egy Csoporthoz?", "A Csoportok megtekintéséhez kattints a lap tetején található Csoportok menüpontra. A link helye a használt témától függ. Nem minden csoport érhetõ el, lehetnek <i>nyílt</i> csoportok, zárt csoportok, vagy akár rejtett
Ha csoport nyitott, akkor a megfelelõ gombra kattintva el lehet küldeni a csatlakozási kérelmet. A csatlakozáshoz szükséges a csoport Moderátorának jóváhagyása, megkérdezheti, hogy miért takarsz csatlakozni a csoportba. Ha a moderátor elutasítja a kérelmedet, akkor annak biztosan megvan az oka, így ne õt hibáztasd.");
$faq[] = array("Hogyan lehetek Csoport Moderátor?", "A Csoportokat az Adminisztrátor készíti, így a hozzátartozó moderátori jogokat is õ adja ki. Ha saját csoportot akarsz indítani, akkor lépj kapcsolatba az Adminisztrátorral, például egy Privát üzenet küldésével.");


$faq[] = array("--","Privát üzenet");
$faq[] = array("Nem tudok Privát üzenetet küldeni!", "Ennek három oka lehet; nem vagy regisztrálva és/vagy nem vagy belépve a Fórumra, vagy az Adminisztrátor nem engedélyezte a Fórumon a Privát üzenet küldését, vagy nem küldhetsz Privát üzeneteket. Ha a legutolsó eset áll fent, lépj kapcsolatba az Adminisztrátorral.");
$faq[] = array("Folyamatosan kéretlen üzeneteket kapok!", "A jövõben lehetõség lesz letiltani a nemkívánt Privát üzeneteket. Egyelõre erre nincs lehetõség, ha kéretlen üzeneteket kapsz, értesítsd az Adminisztrátort.");
$faq[] = array("Kéretlen vagy sértegetõ leveleket kapok valakitõl a Fórumról!", "Ezt sajnálattal halljuk, ebben az esetben a következõ lépéseket tedd meg. Értesítsd az Adminisztrátort, és küldd el neki a kapott levelet, elsõsorban a levél fejléce szükséges. Az Adminisztrátor meg fogja tenni a szükséges lépéseket.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","phpBB 2 kérdések");
$faq[] = array("Kik készítették ezt a fórumot?", "A szoftvert eredeti tulajdonosa a <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. A kiadással, fejlesztéssel, és a szerzõi jogok õket illeti meg. A szoftver a GNU General Public Licence alá tartozik. További részletek a weboldalon.");
$faq[] = array("Miért nem érhetõ el az X szolgáltatás?", "A szoftvert a phpBB Group készítette és licenszeli. Ha úgy gondolod, hogy újabb szolgáltatások, és opciók szükséges a Fórumba, akkor látogasd meg a phpbb.com weboldalt, ahol elmondhatod az ötleteidet, észrevételeidet. Ne küldj kéréseket a phpbb.com Fórumába, a fejlesztõk a www.sourceforge.net oldalon várják az ötleteket. Azonkívül olvasd át a Fórumot, mert lehet hogy az ötletet más már felvetette, és már folyamatban van a megvalósítása.");
$faq[] = array("Ki az illetékes a Fórumon olvasható tartalommal kapcsolatban?", "Elsõsorban a Fórum Adminisztrátorát kell felkeresni. Ha ez nem lehetséges, akkor a weboldal tulajdonosát (ezt kiderítheti egy ún. \"whois\" kereséssel). Ha a fórum egy ingyenes tárhelyen található, akkor azt a szolgáltatót értesítse. A phpBB Group-nak semmilyen köze, hozzáférése vagy beleszólása nincs a Fórumon olvasható tartalomhoz, és nem is állnak kapcsolatban a szoftver üzemeltetõjével.");

//
// This ends the FAQ entries
//

?>
