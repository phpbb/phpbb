<?php
/***************************************************************************
 *                          lang_faq.php [Hungarian]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_faq.php,v 1.4 2001/12/15 16:42:08 psotfx Exp $
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
// Translation by Gergely EGERVARY 
// mauzi@expertlan.hu
//


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
 
  
$faq[] = array("--","Belépési és regisztrációs problémák");
$faq[] = array("Miért nem tudok belépni?", "Regisztrálta magát? Igen, ezt bizony meg kell tennie, mielõtt beléphetne. Nem tiltották le véletlenül? (Ha igen, akkor megjelenik egy üzenet) Ha regisztrált, és nincs letiltva, ellenõrizze, hogy biztosan jó felhasználónevet és jelszót írt-e be. Általában ez szokott lenni a probléma. Ha mégsem, vegye fel a kapcsolatot a fórum adminisztrátorával.");
$faq[] = array("Miért van szükség regisztrációra?", "Nem biztos, hogy feltétlenül szükséges, az adminisztrátorok döntik el, hogy szükség van-e regisztrációra a hozzászólások küldéséhez. Egyébként a regisztrált felhasználók sok olyan szolgáltatást élvezhetnek, amelyek nem állnak rendelkezésre a vendég felhasználóknak: avatar képek választása, privát üzenetek, email küldése, csoport tagságok, stb. Mindössze néhány perc a regisztráció, megéri tehát regisztrálni.");
$faq[] = array("Miért jelentkeztet ki a rendszer automatikusan?", "Ha nem használja az <i>Automatikus belépés minden látogatásnál</i> lehetõséget, a rendszer csak egy elõre meghatározott ideig fogja a belépve tartani. Ez megakadályozza azt, hogy illetéktelenek hozzáférjenek az azonosítójához. Ne használja ezt a szolgáltatást, ha nem a saját számítógépérõl olvassa a fórumot, például könyvtárból, internet kávézóból, iskolai gépterembõl, stb.");
$faq[] = array("Hogyan akadályozhatom meg, hogy mások lássák, mikor vagyok online?", "A profil beállításai között talál egy opciót: <i>Online státusz elrejtése</i>, ha bekapcsolja ezt a lehetõséget, csak a fórum adminisztrátorai és saját maga fogja látni, hogy mikor van belépve. A többiek csak egy rejtett felhasználó jelenlétét érzékelik.");
$faq[] = array("Elfelejtettem a jelszavam!", "Semmi pánik! Bár a jelszót nem lehet megállapítani, lehet helyette kérni egy másikat. Menjen a bejelentkezõ oldalra, kattintson az <u>Elfelejtettem a jelszavam</u> linkre, kövesse az utasításokat, és perceken belül újra bejelentkezhet.");
$faq[] = array("Regisztráltam, de még mindig nem tudok belépni!", "Elõször is ellenõrizze a felhasználónevet és a jelszót. Ha nem ez a probléma, lehetséges, hogy az azonosítóját aktiválni kell. Néhány fórumon minden új azonosítót aktiválni kell használat elõtt vagy saját magának, vagy az adminisztrátornak. Regisztrálásnál figyelmesen olvassa el, és kövesse az utasításokat. Ha nem kapott email-t, gyõzõdjön meg róla, hogy helyes email címet adott meg. Többek között azért van szükség aktiválásra, hogy kiszûrjük azokat a felhasználókat, akik visszaélnek a fórum anonímitásával.");
$faq[] = array("Régebben regisztráltam, de egy ideje nem tudok belépni?!", "Ennek az a leggyakoribb oka, hogy helytelen felhasználónevet vagy jelszót használ. Ellenõrizze az email-t, amit a regisztrálásnál kapott. Esetleg az adminisztrátor törölte az azonosítóját valamiért. Lehet, hogy regisztrált, de még soha nem szólt hozzá? Az egy elterjedt gyakorlat, hogy egy idõ után törlik a rendszerbõl azokat a felhasználókat, akik még soha nem szóltak hozzá, hogy csökkentsék a felhasználó-adatbázis méretét. Regisztráljon újra, és szóljon hozzá!");


$faq[] = array("--","Felhasználói beállítások");
$faq[] = array("Hogyan változtathatom meg a beállításaimat?", "Az összes beállítása (ha már regisztrált felhasználó) egy adatbázisban van tárolva. A megváltoztatásához kattintson a <u>Profil</u> linkre (általában az oldalak tetején).");
$faq[] = array("A dátum vagy idõ beállítás nem pontos!", "Az óra feltehetõleg pontos, de elképzelhetõ, hogy nem a megfelelõ idõzóna van beállítva. Ebben az esetben a profil beállításainál meg tudja változtatni az idõzónát. Vegye figyelembe, hogy az idõzóna beállítása, mint a legtöbb egyéni beállítás csak regisztrált felhasználók számára elérhetõ. Ha még nem regisztrált, itt a remek alkalom!");
$faq[] = array("Beállítottam az idõzónát, de az óra még mindig nem pontos!", "Ha biztos abban, hogy jól állította be az idõzónát, és még mindig nem pontos az idõ, a probléma feltehetõleg a nyári idõszámítás. A fórum jelenleg nem kezeli a téli-nyári idõszámítás változásait, ezért elképzelhetõ, hogy a nyári hónapokban az óra eltér a valódi helyi idõtõl.");
$faq[] = array("A használni kívánt nyelv nincs a listában!", "A leggyakoribb ok, hogy az adminisztrátorok nem telepítették még fel a kívánt nyelvet, de az is elképzelhetõ, hogy a fórum szoftvert még senki sem fordította le a kívánt nyelvre. Kérje meg a fórum adminisztrátorait, hogy telepítsék fel, vagy ha ez nem lehetséges, érezze magát felhatalmazva a fordítás elkészítésére! További információt talál a phpBB Group weboldalán. (kattintson a linkre a lap alján)");
$faq[] = array("Hogyan jeleníthetek meg egy képet a hozzászólásaimnál?", "Kétféle kép szerepelhet a felhasználó neve alatt. Az egyik a rangjelzõ, általában csillagok vagy pontok formájában. A másik (ismertebb nevén avatar) felhasználónként egyedi. A fórum adminisztrátoraitól függ, milyen avatar képek közül lehet választani, esetleg lehet-e tetszõleges képet feltölteni. Ha nem tud avatart választani, akkor azt az adminisztrátor letiltotta.");
$faq[] = array("Hogyan változtathatom meg a rangomat?", "Általában nem tudja közvetlenül megváltoztatni a rangját. A legtöbb fórumon a rangok a felhasználó hozzászólásainak számától függ, valamint a felhasználó jogait tükrözni, például a moderátoroknak és adminisztrátoroknak külön rangja van. Kérjük ne szóljon hozzá feleslegesen csak azért, hogy ezzel növelje a hozzászólásai számát, és a rangját, mert a moderátorok vagy az adminisztrátorok jogában áll bárki rangját lefokozni.");
$faq[] = array("Miért kell bejelentkezni email küldéséhez?", "Pardon, csak regisztrált felhasználók küldhetnek email-t a fórumon keresztül (ha az adminisztrátor engedélyezte ezt a lehetõséget). Ezzel kívánjuk megelõzni a névtelen felhasználók nemkívánatos levelezését.");


$faq[] = array("--","Hozzászólási problémák");
$faq[] = array("Hogyan nyithatok új témát?", "Egyszerûen, kattintson a megfelelõ gombra a fórumon. Feltehetõleg regisztrálnia kell magát, mielõtt hozzá tud szólni, tekintse meg a jogait a lap alján található listában. (a <i>Tud új témát nyitni ebben a fórumban, Tud hozzászólni a témához ebben a fórumban, stb</i> lista)");
$faq[] = array("Hogyan szerkeszthetek vagy törölhetek egy hozzászólást?", "Ha nem fórum adminisztrátor vagy moderátor, csak a saját hozzászólásait tudja szerkeszteni vagy törölni. Szerkesztéshez (amit általában csak egy meghatározott ideig tehet meg) kattintson a <i>szerkeszt</i> gombra a megfelelõ hozzászólásnál. Ha már valaki válaszolt a hozzászólásra, a szerkesztés után talál egy megjegyzést, hogy hányszor szerkesztette a hozzászólást. Ez az üzenet nem jelenik meg, ha a hozzászólásra még nem válaszoltak, vagy a hozzászólást egy moderátor vagy adminisztrátor szerkesztette. Utóbbi esetben általában hagynak egy megjegyzést, hogy mit szerkesztettek, és miért. A legtöbb felhasználó nem tudja törölni a hozzászólását, ha már válaszoltak rá.");
$faq[] = array("Hogyan írhatom alá hozzászólásaimat?", "Elõször adja meg az aláírást a profil beállításainál. Ha ezzel elkészült, válassza az <i>Aláírás csatolása</i> lehetõséget hozzászólásainál. Az összes hozzászólásánál automatikusan csatolhatja az aláírását, ha kiválasztja ezt a lehetõséget a profil beállításainál. (ez esetben még mindig le tudja tiltani egy-egy hozzászólásnál, ha kívánja)");
$faq[] = array("Hogyan nyithatok szavazást?", "Egyszerûen, amikor új témát nyit (vagy egy téma elsõ hozzászólását szerkeszti, ha van rá joga) a lap alján talál egy <i>Új szavazás</i> mezõt. (ha nem talál ilyen mezõt, valószínûleg nincs joga szavazást nyitni) Adjon meg egy címet a szavazásnak, és legalább két választási lehetõséget (kattintson a <i>Hozzáadás</i> gombra). Megadhat idõ limitet a szavazásra, a 0 végtelen szavazást jelent. A választási lehetõségek maximális számát a fórum adminisztrátora határozza meg.");
$faq[] = array("Hogyan szerkeszthetek vagy törölhetek egy szavazást?", "Akár csak a hozzászólásoknál, a szavazásokat is csak az eredeti hozzászóló, vagy a moderátorok illetve adminisztrátorok tudják szerkeszteni. Szavazás szerkesztéséhez kattintson a szerkesztés gombra az elsõ hozzászólásnál. Ha még senki nem szavazott, a felhasználó letörölheti a szavazást, a késõbbiekben ezt már csak a moderátorok illetve az adminisztrátorok tehetik meg.");
$faq[] = array("Miért nem férek hozzá bizonyos fórumokhoz?", "Néhány fórumot csak arra felhatalmazott felhasználók vagy csoportok érhetnek el. A fórum moderátorai vagy adminisztrátorai tudják biztosítani a hozzáférést, vegye fel velük a kapcsolatot.");
$faq[] = array("Miért nem tudok szavazni?", "Általában csak regisztrált felhasználók tudnak szavazni. Ezzel megakadályozható a szavazatok manipulálása. Ha regisztrált felhasználó, és ennek ellenére nem tud szavazni, valószínûleg nincs rá joga.");


$faq[] = array("--","Formázás és hozzászólás típusok");
$faq[] = array("Mi az a BBCode?", "A BBCode egy speciális HTML változat. Az adminisztrátor határozza meg, használhat-e a hozzászólásaiban BBCode tag-eket. (a BBCode hozzászólásonként bárki által letiltható) Szintaktikailag hasonló a HTML kódhoz, a tag-ek szögletes zárójelben vannak: [ és ] a &lt; és &gt; helyett, és nagyobb felügyeletet biztosít a megjelenítés felett. További információért tekintse meg a BBCode kalauzt, amely elérhetõ a hozzászólás írásakor.");
$faq[] = array("Használhatok HTML kódot?", "Az adminisztrátor határozza meg. Ha használhatja, észre fogja venni, hogy csak néhány HTML tag mûködik. Ez egy <i>biztonsági</i> intézkedés, hogy a felhasználók ne tudjanak olyan kódokat használni, ami átállíthatja a lap formátumát vagy egyéb hasonló problémákat okozhat. A HTML hozzászólásonként bárki által letiltható.");
$faq[] = array("Mik azok az emotikonok?", "A Smiley-k, vagy Emotikonok apró grafikák, amelyek érzelmeket fejeznek ki, például. a :) jelentése boldog, :( jelentése szomorú. Az emotikonok teljes listája megtekinthetõ hozzászólásnál. Ne használjon túl sok emotikont, mert zavaró lehet a fórum olvasásakor, és a moderátorok esetleg eltávolíthatják akár az egész hozzászólást.");
$faq[] = array("Csatolhatok képeket?", "Képek megjeleníthetõek a hozzászólásokban, de jelenleg nincs arra mód, hogy a képeket közvetlenül a fórumra töltse fel. Ezért a képnek elérhetõnek kell lennie egy publikus web szerveren, például: http://www.egy-ismert-szerver.hu/kép.gif. Nem tud hivatkozni olyan képekre, amely a saját gépén található (kivéve ha az egy web szerver) és a kép nem lehet jelszóval védett helyen, például hotmail vagy yahoo levelekben, stb. A kép megjelenítéséhez használja a BBCode [img] tag-et, vagy a megfelelõ HTML tag-et. (ha engedélyezve van)");
$faq[] = array("Mik azok a hírdetmények?", "A hírdetmények speciális témák, amelyek fontos információkat tartalmaznak, amelyeket el kell olvasni amilyen gyorsan csak lehet. A hírdetmények az összes oldal tetején megjelennek abban a fórumban, ahová elküldték. Hírdetmények írásához külön engedélyre van szüksége, amelyet az adminisztrátor tud biztosítani.");
$faq[] = array("Mik azok a fontos témák?", "A fontos témák a hírdetmények alatt jelennek meg, de csak a fórum kezdõoldalán. Általában fontos információkat tartalmaznak, ezért ajánlott elolvasni õket. Akár csak a hírdetményeknél, fontos témák írásához külön engedélyre van szüksége, amelyet az adminisztrátor tud biztosítani.");
$faq[] = array("Mik azok a lezárt témák?", "A lezárt témákhoz nem lehet a továbbiakban hozzászólni, valamint nem lehet többet szavazni. Egy témát a fórum moderátorok vagy adminisztrátorok tudják lezárni, a lezárásnak többféle oka is lehet.");


$faq[] = array("--","Felhasználók és csoportok");
$faq[] = array("Kik azok az adminisztrátorok?", "Az adminisztrátorok a legfõbb üzemeltetõi a fórumnak. Ezek a személyek teljes irányítással rendelkeznek az egész fórum felett, beleértve a felhasználók jogainak beállítását, felhasználók letiltását, csoportok és moderátorok létrehozását, stb. Teljeskörû moderátori joggal bírnak az összes fórumon.");
$faq[] = array("Kik azok a moderátorok?", "A moderátorok olyan személyek, (vagy csoport-tagok) akiknek a feladata egy-egy fórum vagy csoport felügyelete. Joguk van szerkeszteni vagy törölni hozzászólásokat, lezárni, feloldani, áthelyezni témákat azokban a fórumokban, amelyekben moderátorok. Általában a moderátorok felügyelik a felhasználókat, hogy ne írjanak témától eltérõ, vagy egyéb módon tartalmilag kifolyásolható hozzászólásokat.");
$faq[] = array("Mik azok a csoportok?", "A felhasználók csoportokba szervezhetõk. Egy felhasználó több csoportban is tag lehet. A csoportokhoz az adminisztrátorok különféle hozzáférési jogokat rendelhetnek. Ezáltal egyszerûen lehet több felhasználó moderátora egy fórumnak, többen hozzáférhetnek egy zártkörû fórumhoz, stb.");
$faq[] = array("Hogyan csatlakozhatok egy csoporthoz?", "Csatlakozáshoz kattintson a csoportok linkre a lap tetején. Nem minden csoporthoz tud azonnal csatlakozni, például vannak zárt csoportok, ahol a csoport moderátorának jóvá kell hagynia a csatlakozását.");
$faq[] = array("Hogyan lehetek csoport moderátor?", "A csoportokat az adminisztrátor hozza létre, és rendeli hozzá a csoport moderátorát. Ha csoportot szeretne létrehozni, vegye fel a kapcsolatot az adminisztrátorral.");


$faq[] = array("--","Privát üzenetek");
$faq[] = array("Nem tudok privát üzeneteket küldeni!", "Ennek háromféle oka lehet. Vagy nincs regisztrálva illetve bejelentkezve, vagy az adminisztrátor letiltotta a privát üzenetküldés lehetõségét, vagy nincs joga ilyen üzeneteket küdeni.");
$faq[] = array("Állandóan nemkívánatos privát üzeneteket kapok!", "A késõbbiekben lesz lehetõsége letiltani felhasználóktól privát üzenet fogadását. Addig is vegye fel a kapcsolatot a fórum adminisztrátorával.");
$faq[] = array("Kaptam egy nemkívánatos email-t valakitõl a fórumról!", "Sajnáljuk, hogy ilyenrõl kell tudomást szereznünk. Vegye fel a kapcsolatot a fórum adminisztrátorával, küldje el a levél másolatát, beleértve a levél fejlécét. Megtesszük a szükséges intézkedéseket.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","phpBB 2 kérdések");
$faq[] = array("Kik írták ezt a fórumot?", "Ezt a szoftvert (az eredeti, módosítatlan formájában) készítette, kiadta, és a szerzõi jogokat fenntartja: a <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. A szoftver szabadon hozzáférhetõ a GNU General Public Licence alatt, kattintson a linkre további részletekért.");
$faq[] = array("Miért nincs X szolgáltatás benne?", "Ezt a szoftvert a phpBB Group írta. Ha úgy gondolja, hogy egy új szolgáltatást szeretne benne látni, látogassa meg a phpbb.com weboldalt, és vegye fel a kapcsolatot a fejlesztõkkel. Kérjük, ne írja meg a kérését a phpbb.com fórumon, a fejlesztõk a sourceforge portálon várják az ilyen jellegû kérések felvetését.");
$faq[] = array("Ki az illetékes a fórumon olvasható tartalommal kapcsolatban?", "Vegye fel a kapcsolatot a fórum adminisztrátorával. Ha nem járt sikerrel, próbálja megkeresni valamelyik fórum moderátorát. Ha a továbbiakban sem kap választ, keresse meg a domain tulajdonosát. (pl. whois kereséssel) Kérjük, ne forduljon a phpBB Group-hoz, a fejlesztõk nem állnak kapcsolatban a szoftver végfelhasználóival.");

//
// This ends the FAQ entries
//

?>