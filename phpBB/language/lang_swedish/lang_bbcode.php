<?php
/***************************************************************************
 *                         lang_bbcode.php [english]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
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
//  Swedish Translation by:
//	
//	Marcus Svensson
//  xsvennemanx@hotmail.com
//	
// 	Janåke Rönnblom
//	jan-ake.ronnblom@skeria.skelleftea.se
//	
//	Bruce
//	bruce@webway.se
// 
 
// 
// To add an entry to your BBCode guide simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your BBCode guide entries, if you absolutely must then escape them ie. \"something\"
//
// The BBCode guide items will appear on the BBCode guide page in the same order they are listed in this file
//
// If just translating this file please do not alter the actual HTML unless absolutely necessary, thanks :)
//
// In addition please do not translate the colours referenced in relation to BBCode any section, if you do
// users browsing in your language may be confused to find they're BBCode doesn't work :D You can change
// references which are 'in-line' within the text though.
//

$faq[] = array("--","Introduktion");
$faq[] = array("Vad är BBCode?", "BBCode är en speciell implementation av HTML. Om du kan använda BBCode bestäms av administratören (du kan också hindra användningen av BBCode i det aktuella meddelandet när du skapar det). BBCode i sig är snarlikt HTML, taggar är omgärdade/inneslutna i hakparanteser [ och ] i stället för &lt; och &gt; och BBCode ger större kontroll över hur vad och hur någonting visas. Beroende på mallen du använder så kanske du upptäcker att lägga till BBCode till dina inlägg har gjorts mycket enklare genom en klickningsbart gränssnitt ovanför meddelande delen i inläggs formuläret. Även om det nya BBcode interfacet är mycket användbart kommer du nog att finna följande guide praktisk.");

$faq[] = array("--","Text Formatering");
$faq[] = array("Hur man skapar fetstilad, kursiv och understruken text","BBCode inlkluderar taggar för att snabbt låta dig ändra den grundläggande stilen på texten. Det utförs på följande sätt: <ul><li>För att göra en bit text fetstilad omgärda den med <b>[b][/b]</b>, ex. <br /><br /><b>[b]</b>Hej<b>[/b]</b><br /><br />blir <b>Hej</b></li><li>För understrykning använd <b>[u][/u]</b>, till exempel:<br /><br /><b>[u]</b>God Morgon<b>[/u]</b><br /><br />blir <u>God Morgon</u></li><li>För att kursivera text använd <b>[i][/i]</b>, ex.<br /><br />Det här är <b>[i]</b>Suveränt!<b>[/i]</b><br /><br />skulle bli Det här är <i>Suveränt!</i></li></ul>");
$faq[] = array("Hur man ändrar färgen eller storleken på texten", "För att ändra färgen eller storleken på din text kan du använda följande taggar. Kom ihåg att utskriften beror på besökarens browser och system: <ul><li>För att ändra färgen på en text omgärdar du den med <b>[color=][/color]</b>. Du kan ange antingen ett namn på en färg (ex. red, blue, yellow, etc.) eller alternativt hexadecimal värdet för en färg, ex. #FFFFFF, #000000. T.ex. för att skapa röd text kan du använda:<br /><br /><b>[color=red]</b>Hej!<b>[/color]</b><br /><br />eller<br /><br /><b>[color=#FF0000]</b>Hej!<b>[/color]</b><br /><br />kommer båda att skriva ut <span style=\"color:red\">Hej!</span></li><li>För att ändra text storleken gör man på ett liknande sätt, <b>[size=][/size]</b>. Den här taggen är beroende på mallen som du använder men det rekommenderade formatet är ett numeriskt värde som representerar text storleken i pixlar, från 1 (så liten så att du knappt ser den) upp till 29 (väldigt stor). T.ex:<br /><br /><b>[size=9]</b>LITEN<b>[/size]</b><br /><br />blir vanligtvis <span style=\"font-size:9px\">LITEN</span><br /><br />medan:<br /><br /><b>[size=24]</b>ENORM!<b>[/size]</b><br /><br />blir <span style=\"font-size:24px\">ENORM!</span></li></ul>");
$faq[] = array("Kan jag kombinera formatterings taggar?", "Jovisst kan du det, för att t.ex. dra uppmärksamheten åt dig kan du skriva:<br /><br /><b>[size=18][color=red][b]</b>TITTA PÅ MIG!<b>[/b][/color][/size]</b><br /><br />det skulle skriva ut <span style=\"color:red;font-size:18px\"><b>TITTA PÅ MIG!</b></span><br /><br />Vi rekommenderar dock inte att du skriver ut mycket text på det här viset! Kom ihåg att det är upp till dig, författaren av ett meddelande att se till att taggarna är stängda på rätt vis. T.ex. att göra följande är fel:<br /><br /><b>[b][u]</b>Det här är fel<b>[/b][/u]</b>");

$faq[] = array("--","Citering och utskrift av förbestämd formatering");
$faq[] = array("Citering av text i svar", "Det finns två sätt du kan citera text på, med en referens eller utan en.<ul><li>När du använder Citera funktionen för att svara på ett inlägg på forumet så borde du lägga märke till att inläggets text har lagts till i meddelande rutan omgärdat av ett <b>[quote=\"\"][/quote]</b> block. Den här metoden ger dig möjligheten att citera via en referens till en person eller vad du nu väljer! För att t.ex. citer en bit av vad Mr. Blobby skrev skall du skriva:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>Texten som Mr. Blobby skrev skulle vara här<b>[/quote]</b><br /><br />Utskriften skulle automatiskt lägga till Mr. Blobby skrev: före texten. Kom ihåg att du <b>måste</b> ha med citationstecken \"\" runt namnet du citerar, det är inte valbart.</li><li>Den andra metoden tillåter dig att citera i princip vad som helst. För att göra det här omgärda texten med <b>[quote][/quote]</b> taggar. När du visar meddelandet skrivs det helt enkelt ut, Citat: framför texten.</li></ul>");
$faq[] = array("Utskrift av förbestämd bredd på text", "Om du vill skriva ut en bit kod eller vad som helst som kräver att texten bibehåller sitt ursprungliga format, t.ex. Courier text font så borde du omgärda texten med <b>[code][/code]</b> taggar, t.ex.<br /><br /><b>[code]</b>echo \"Det här är en bit kod\";<b>[/code]</b><br /><br />All formatering inom <b>[code][/code]</b> taggarna är bibehållen när du senare visar det.");

$faq[] = array("--","Skapa listor");
$faq[] = array("Skapa en icke ordnad lista", "BBCode stödjer 2 typer av listor, icke ordnad och ordnand lista. De är i princip samma som HTML motsvarigheterna. En icke ordnand lista skriver ut varje föremål i listan efter varandra indraget med en kula. För att skapa en icke ordnad lista använder du <b>[list][/list]</b> och definerar varje föremål av listan genom att använda <b>[*]</b>. Till expempel för att lista dina favorit färger kan du skriva:<br /><br /><b>[list]</b><br /><b>[*]</b>Röd<br /><b>[*]</b>Blå<br /><b>[*]</b>Gul<br /><b>[/list]</b><br /><br />Det skulle skriva ut följande lista:<ul><li>Röd</li><li>Blå</li><li>Gul</li></ul>");
$faq[] = array("Skapa en ordnand lista", "Den andra typen av lista, en ordnad lista ger dig kontroll över vad som skall skrivas ut innan varje föremål. För att skapa en ordnad lista kan du skriva <b>[list=1][/list]</b> för att skapa en numrerad lista eller alternativt <b>[list=a][/list]</b> för att skapa en alfabetisk lista. Precis som med den icke ordnade listan specificerar man föremålen i listan med <b>[*]</b>. Till exempel:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Gå till butiken<br /><b>[*]</b>Köp en ny dator<br /><b>[*]</b>Svär åt datorn när den krashar<br /><b>[/list]</b><br /><br />kommer att skriva ut följande:<ol type=\"1\"><li>Gå till butiken</li><li>Köp en ny dator</li><li>Svär åt datorn när den krashar</li></ol>Och för en alfabetisk lista skall du använda:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Det första möjliga svaret<br /><b>[*]</b>Det andra möjliga svaret<br /><b>[*]</b>Det tredje möjliga svaret<br /><b>[/list]</b><br /><br />blir<ol type=\"a\"><li>Det första möjliga svaret</li><li>Det andra möjliga svaret</li><li>Det tredje möjliga svaret</li></ol>");

$faq[] = array("--", "Skapa Länkar");
$faq[] = array("Länka till en annan sida", "phpBB BBCode stödjer ett antal olika sätt att skapa URIs, Uniform Resource Indicators mer kända som URLs.<ul><li>De första sättet använder <b>[url=][/url]</b> taggen, allt du än skriver in efter = tecknet kommer att tolkas som URL och göra så att du kan skriva in t.ex. en text mellan taggarna. För att t.ex. länka till phpBB.com kan du använda:<br /><br /><b>[url=http://www.phpbb.com/]</b>Besök phpBB!<b>[/url]</b><br /><br />Det här skriver ut följande länk, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Besök phpBB!</a> Du kommer att märka att länken öppnas i ett nytt fönster så att användaren kan fortsätta använda forumet om de så önskar.</li><li>Om du vill att URLen själv skall visas som länk kan du skriva::<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Det skulle skriva ut följnade länk, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Det finns även en funktion som kallas <i>Magiska Länkar</i>, det kommer att automatiskt att göra om en URL till en länk utan att du varken behöver specificera några taggar eller http://. Om du till exempel skriver www.phpbb.com i ditt meddelande så kommer det automatiskt bli <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> när inlägget visas.</li><li>Samma sak gäller email adresser, du kan antingen skriva in:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />vilket skulle skriva ut <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> eller så kan du bara skriva no.one@domain.adr i ditt meddelande och så kommer det att automatiskt bli konverterat när inlägget visas.</li></ul>Precis som med alla andra BBCode taggar kan du omgärda URLs runt andra taggar t.ex. <b>[img][/img]</b> (se nästa del), <b>[b][/b]</b>, etc. Precis som med formaterings taggarna så är det upp till dig att se till att taggarna öppnas och stängs i rätt ordning, till exempel:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />är <u>inte</u> korrekt och kan leda till att ditt inlägg blir borttaget så var försiktig.");

$faq[] = array("--", "Visa bilder i inlägg");
$faq[] = array("Lägg till en bild i ett inlägg", "phpBB BBCode har även en tag för att visa bilder i dina inlägg. Två väldigt viktiga saker bör kommas ihåg när man använder den här taggen; många användare uppskattar inte stora mängder av bilder i inlägg och i andra hand att bilden du vill visa måste finnas tillgänglig på internet (den kan alltså inte bara finnas på din dator, om du inte kör en webserver!). Det finns för tillfället inte något sätt att spara bilder lokalt med phpBB (alla dessa frågor förväntas bli åtgärdade i nästa utgåva av phpBB). För att visa en bild måste du omgärda URLen som pekar mot bilden med <b>[img][/img]</b> taggar. Till exempel:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Du kan även omgärda en bild med <b>[url][/url]</b> taggarna om du så önskar, t.ex<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />skulle skriva ut:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Andra frågor");
$faq[] = array("Kan jag lägga till mina egna taggar?", "Nej, tyvärr inte i phpBB 2.0. Vi undersöker möjligheterna att erbjuda skräddarsydda BBCode taggar i nästa stora version");

//
// This ends the BBCode guide entries
//

?>