<?php
/***************************************************************************
 *                          lang_faq.php [english]
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

//  *************************************
//  Original swedish Translation by:
//
//  Marcus Svensson
//  admin@world-of-war.com
//  http://www.world-of-war.com
//      -------------------------------------
//      Janåke Rönnblom
//      jan-ake.ronnblom@skeria.skelleftea.se
//      -------------------------------------
//      Bruce
//      bruce@webway.se
//  *************************************

//  *************************************
//  Maintained and kept up-to-date by:
//
//  Fredrik Poller
//  fredrik.poller@bonetmail.com
//  http://fredrik.bamze.net/
//  *************************************

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
 
  
$faq[] = array("--","Inloggnings- och registreringsproblem");
$faq[] = array("Varför kan jag inte logga in?", "Har du registrerat dig? Du måste registrera dig för att kunna logga in. Har du blivit avstängd från forumet (i så fall visas ett meddelande)? Om så är fallet bör du kontakta webbmastern eller forumadministratören för att få reda på varför. Om du har registrerat dig och inte är avstängd men ändå inte kan logga in kontrollera då ditt användarnamn och lösenord. Oftast är det problemet, om inte så kontakta forumadministratören.");
$faq[] = array("Varför behöver jag registera mig?", "Det är inte säkert att du behöver, det är upp till forumadministratören om du behöver registrera dig för att skriva ett meddelande. Dock så ger registreringen dig ytterligare funktioner som inte är tillgängliga för gäster, till exempel avatar bilder, privata meddelanden, e-post till andra användare, prenumeration av användargrupper, etc. Det tar endast några sekunder att registrera sig så det är att rekommendera.");
$faq[] = array("Varför blir jag automatiskt utloggad?", "Om du inte kryssar i <i>Logga in mig automatiskt</i> när jag besöker forumet så kommer du endast att vara inloggad under en begränsad tid. Det för att förhindra att någon missbrukar ditt konto. För att förbli inloggad kryssa i rutan vid inloggningen, det är dock inte att rekommendera om du läser forumet från en offentlig dator, såsom bibliotek, internetcafé, skola, etc.");
$faq[] = array("Hur hindrar jag att mitt användarnamn syns på online-listan?", "I din profil finns en inställning, <i>Dölj din online status</i>, om du ändrar inställningen till <i>på</i>, så syns du bara för forumadministratörer och dig själv. Du kommer att räknas som en dold användare.");
$faq[] = array("Jag har glömt mitt lösenord!", "Ingen panik. Även om ditt lösenord inte kan läsas så kan det ändras. För att göra detta gå tillbaka tilllogin sidan och klicka på <i>Jag har glömt mitt lösenord</i>, följ instruktioner och du kommer att vara tillbaka online på nolltid.");
$faq[] = array("Jag har registrerat mig men jag kan inte logga in!", "Först kontrollera att du använder rätt användarnamn och lösenord. Om de är okej så kan två saker inträffat. Om COPPA stöd är aktiverat och om du klickat på <i>Jag är under 13 år</i> när du registrerat dig så måste du följa instruktionerna du fått. Om så inte är fallet så kanske ditt konto behöver aktiveras? Vissa forum kräver att alla ny registeringar måste aktiveras, antingen av dig själv eller av administratören innan du kan logga in. När du registrerade dig så står det angivet om du måste aktivera kontot. Om du fått ett email så följ instruktionerna i det, om inte så är du säker på angett en korrekt email addresS? En anledning till att man använder aktivering är att minska möjligheten för <i>okunniga</i> användare att missbruka forumet anonymt. Om du är säker på att email adressen är giltig så kontakta administratören för forumet.");
$faq[] = array("Jag har registrerat mig tidigare men nu kan jag inte logga in längre?!", "De troligaste anledningarna till detta är att du har angett ett felaktig användarnamn eller lösenord (läs emailet du fick när du först registrerade dig) eller så har administratören raderat ditt konto av någon anledning. Om det är det andra fallet så kanske du inte har skrivit något inlägg? Det är normalt för forum att ibland radera användare som inte har skrivit något för att minska storleken på databasen. Försök registrera dig igen och skriv ett inlägg i en diskussion.");


$faq[] = array("--","Användarpreferenser och inställningar");
$faq[] = array("Hur ändrar jag min inställningar?", "Alla dina inställningar (om du har registrerat dig) är lagrade i databasen. För att ändra dom klicka på <i>profillänken</i> (visas oftast högst upp på sidan). Detta låter dig ändra alla dina inställningar.");
$faq[] = array("Tiden är inte rätt!", "Tiden är med all säkerhet korrekt, dock vad du kanske ser är tid som visas i en annan tidszon är den du befinner dig i. Om så är fallet så bör du ändra dina inställningar för att tidszonen ska stämma överens med din plats, tex. Stockholm, Helsingfors, New York, Sydney, etc. Notera att för att ändra tidszonen, och de flesta inställningarna, så måste du vara registrerad. Så om du inte har registrerat dig så är det god tid att göra det nu, om du ursäktar ordvitsen!");
$faq[] = array("Jag har ändrat tidszonen och tiden är fortfarande fel!", "Om du är säker på att du har satt tidszonen korrekt och tiden fortfarande är felaktig så är \"Daylight Savings Time\" (eller sommartid som det kallas i Sverige och i andra länder) det mest troliga svaret. Forumet är inte designat för att hantera förändringarna mellan vinter och sommartid så under sommar månaderna kan tiden vara en timme fel.");
$faq[] = array("Mitt språk finns inte i listan!", "Det mest troliga skälet för detta är att antingen så har administratören inte installerat ditt språk eller så har inte någon översatt forumet till ditt språk. Fråga administratörerna om de inte kan installera det språk du behöver och om det inte finns så tag gärna chansen och skapa en ny översättning. Mer information finns på phpBBs hemsida (se länken längst ner på sidorna)");
$faq[] = array("Hur visar jag en bild under mitt användarnamn?", "Det kan finnas två bilder under användarnamnet när man tittar i en tråd. Den först är en bild som är associerat med din rang, oftast är bilderna i form av stjärnor eller block som visar hur många inlägg du har skrivit eller din status i forumet. Under den kan det finns en bild som är känt som en avatar, denna är i allmänhet unik eller personlig för varje användare. Det är upp till forumadministratören att tillåta avatarer och de kan även välja på vilket sätt avatarer görs tillgängliga för användaren. Om du inte kan använda avatarer i forumet så är det ett beslut av administratörerna, och du kan fråga dom om deras skäl till detta (vi är säkra på att de är bra!)");
$faq[] = array("Hur ändrar jag min rang?", "I normala fall kan du inte ändra din rang direkt (rang står under ditt användarnamn i ämnen och i din profil beroende på vilken stil som valts). De flesta forumen använder rang för att indikera antalet inlägg du har skrivet och för att identifiera vissa användare, e.g. moderatorer och administratörer kan ha en speciell rang. Försök att inte missbruka forumet genom att skriva onödiga inlägg bara för att öka din rang, administratörerna kommer i såfall att helt enkelt sänka ditt antal inlägg.");
$faq[] = array("När jag klickar på emaillänken till en användare så vill forumet att jag loggar in?", "Tyvärr kan bara registrerade användaren skicka email till andra användare vid det inbyggda epost formuläret (om administratörerna har aktivera denna finess). Detta är för att förhindra missbruk av email systemet av anonyma användare.");


$faq[] = array("--","Problem med inlägg");
$faq[] = array("Hur skriver jag ett meddelande i ett forum?", "Enkelt, klicka på den relevanta knappen antingen på forum- eller ämnessidan. Det är möjligt att du behöver registrera dig innan du kan skriva ett meddelande, de optioner du kan göra visas längst ner på forum- och ämnessidan (<i>Du kan skapa ett nytt meddelande, Du kan rösta i omröstningar, etc.</i>)");
$faq[] = array("Hur ändrar jag eller raderar ett inlägg?", "Såvida du inte är administratör eller moderator kan du bara redigera och radera din egna inlägg. Du kan redigera ett meddelande (ibland bara inom en begränsad tid efter det att det skrevs) genom att klicka på <i>redigeraknappen</i> för det relevanta meddelandet. Om någon redan har besvarat meddelandet kommer det att finnas en förklarande text under meddelandet som talar om det har redigerats, detta visar hur många gånger meddelandet har blivit redigerat. Detta syns enbart om ingen har svarat, det syns inte heller om en moderator eller administratör redigerar meddelandet (dock bör de lämna ett meddelande som talar om vad de har ändrat och varför). Notera att normala användare inte kan radera meddelanden när någon svarat på det.");
$faq[] = array("Hur lägger jag till en signatur till mitt meddelande?", "För att lägga till en signatur till ett meddelande måste du först skapa en, detta gör du via din profil. När du väl har skapat din signatur kan du kryssa i <i>Lägg till signatur</i> när du skapar meddelandet för att lägga til din signatur. Du kan också lägga till en signatur automatiskt till alla din meddelanden genom att ställa in det i din profil (du kan fortfarande hindra din signatur från att läggas till genom att kryss av lägg till signatur rutan när du skapar meddelandet)");
$faq[] = array("Hur skapar jag en omröstning?", "Att skapa en omröstning är enkelt, när du skapar ett nytt ämne (eller redigerar det första meddelandet i ett ämne) så ska det finnas ett <i>Lägg till omröstning-forumlär</i> under meddelandeboxen (om du inte kan se detta så har du förmodligen inte rättigheter att skapa nya omröstningar). Du bör ange en titel för omröstningen och minst två val (för att skapa en valmöjlighet ange frågan och klicka på <i>Lägg till nytt val-knappen</i>. Du kan också ange en tidsbegräsning för omröstningen, 0 är oändligt. Det finns en begräsning på hur många olika valmöjligheter du kan ha, detta bestäms av forumadministratören.");
$faq[] = array("Hur ändrar jag en omröstning?", "Meddelanden och omröstningar kan enbart redigeras av den som skapat dom, eller av moderatorer eller administratörer. För att redigera en omröstning klicka på första meddelandet i ämnet (denna är alltid associerad med omröstningen). Om ingen har röstat så kan användare radera omröstningen eller redigera valmöjligheterna. Dock om någon har röstat så kan bara moderatorer eller administratörer redigera eller radera omröstningen. Detta för att förhindra folk att rigga omröstningar genom att ändra valmöjligheter när folk redan har röstat.");
$faq[] = array("Varför kan jag inte komma in i ett forum?", "Vissa forum kan vara begränsad till vissa användare eller grupper. För att lista, läsa, skriva, etc så måste du ha en speciell auktorisation, enbart gruppmoderatorn eller forumadministratörer kan ge denna rättigheter, så du måste kontakta dom.");
$faq[] = array("Varför kan jag inte rösta i omröstningar?", "Enbart registrerad användare kan rösta i omröstningar (för att hindra fejkade resultat). Om du är registrerad och du fortfarande inte kan rösta så har du förmodligen inte rättigheter till att rösta.");


$faq[] = array("--","Formatteringar och Rubrik/Ämnes typer");
$faq[] = array("Vad är BBCode?", "BBCode är en speciell implementation av HTML, om du kan använda BBCode bestäms av administratören (du kan också hindra användningen av BBCode i det aktuella meddelandet när du skapar det). BBCode i sig är snarlikt HTML, taggar är omgärdade/inneslutna i hakparanteser [ och ] istället för &lt; och &gt; och BBCode ger större kontroll över hur vad och hur någonting visas. För mera information om BBCode titta i guiden som kan nås från <i>nytt meddelande-sidan</i>.");
$faq[] = array("Kan jag använda HTML?", "Det beror på om administratören tillåter dig, de har total kontroll över det. Om det är tillåtet kommer du att finna att det bara är vissa taggar som fungerar. Detta är en säkerhetsåtgärd för att hindra folk från personer från att missbruka forumet genom att använda taggar som kan förstöra designen eller skapa andra problem. Om HTLM är tillåt så kan du hindra det i det aktuella meddelandet när du skapar det.");
$faq[] = array("Vad är Smileys?", "Smileys, eller emoticons är små grafiska bilder som kan användas för att uttrycka någon typ av känsla via en förkortning, e.g. :) betyder lycklig, :( betyder ledsen. Hela listan av emoticons kan du hitta via nytt meddelande formuläret. Försök att inte överanvända smileys, de kan fort få ett meddelande att bli oläsbart och en moderator kan bestämma sig för att redigera bort dem från meddelandet eller radera hela meddelandet.");
$faq[] = array("Kan jag posta bilder?", "Bilder kan visas i ditt meddelande. Dock finns det för tillfället ingen funktion för att ladda upp bilder till forumet. Därför måste du länka till en bild som lagras på en publik webserver, e.g. http://www.some-unknown-place.net/my-picture.gif. Du kan inte länka till bilder som lagras på din lokala PC (såvida den inte är en publikt tillgänglig server) eller till bilder som lagras bakom auktoriserings mekanismer, e.g. hotmail eller yahoo mailkonto, lösenordsskyddade siter, etc. För att visa en bild så använd antingen BBCode [img]-taggen eller motsvarande HTML (om det tillåts).");
$faq[] = array("Vad är Viktiga ämnen/meddelande?", "Viktiga meddelande innehåller oftast viktig information och du bör läsa dom så fort som möjligt. Viktiga meddelande syns högst upp på varje sida i det forum som de skrivs. Om du kan eller inte kan skriva viktiga meddelande beror på vilka rättigheter som behövs, vilka styrs av administratörerna.");
$faq[] = array("Vad är Klistrade ämnen/meddelande?", "Klistrade ämnen syns under viktiga meddelande och enbart på första sidan. De innehåller ofta viktig information så du bör läsa dom när det är möjligt. Såsom med viktiga meddelande så är det administratörerna som bestämmer vilka rättigheter som behövs för att kunna skriva Klistrade ämnen.");
$faq[] = array("Vad är låsta ämnen/meddelande?", "Låsta ämnen skapas av antingen forummoderatorerna eller av forumadministratörerna. Du kan inte besvara ett låst ämne och om den innehåller en omröstning så stoppas denna automatiskt. Ämnen kan låsas av många skäl.");


$faq[] = array("--","Användarnivåer och grupper");
$faq[] = array("Vad är Administratörer", "Administratörer är personer som har den absolut högst nivån av kontroll över hela forumet. Dessa personer kan kontrollera alla aspekter av forumets drift vilket inkluderar att sätta rättigheter, bannlysa användare, skapa användargrupper eller moderatorer, etc. De har också fulla moderatorrättigheter i alla forum.");
$faq[] = array("Vad är Moderatorer?", "Moderatorer är individer (eller grupper av individer) vilkas jobb det är att sköta om de dagliga aktiviterna i forumet. De har rättigheter att redigera eller radera meddelanden och låsa, låsa upp, flytta, radera och dela rubriker i forumet som de modererar. Generellt så är moderaratorer där för att hindra personer från att komma ifrån ämnet eller att skriva missbrukande eller anstötligt material.");
$faq[] = array("Vad är Användargrupper?", "Användargrupp är ett sätt som forumadministratörerna kan gruppera användare. Varje användare kan tillhöra flera grupper (detta skiljer från de flesta andra forumen) och varje grupp kan tilldelas individuella rättigheter. Detta gör det enkelt för administratörer att ange flera användare som moderatorer för ett forum eller ge dom åtkomst till ett privat forum, etc.");
$faq[] = array("Hur går jag med i en användargrupp?", "För att ansluta dig till en användargrupp så klicka på användargrupp länken i sidhuvudet (beroende på designmallen som används), du kan då visa alla användargrupper. Inte alla grupper är <i>öppen åtkomst</i>, vissa är stängda och vissa kan även vara dolda. Om forumet är öppet kan du ansöka om att få bli medlem genom att klicka på lämplig knapp. Användargruppens moderator måste då i sin tur bevilja din ansökan och kan även fråga varför du vill bli medlem. Och tjata inte på moderatorerna om de nekar dig de har säkerligen sina skäl.");
$faq[] = array("Hur blir jag moderator för en användargrupp?", "Användargrupper skapas av initialt av administratörerna och de utser också de en moderator. Om du är intresserad av att skapa en användargrupp så är din första kontakt någon av administratörerna. Skicka ett privat meddelande till dom.");


$faq[] = array("--","Privata meddelanden");
$faq[] = array("Jag kan inte skicka privata meddelanden!", "Det finns tre skäl till detta; du är inte registrerad och/eller du har inte logga in, administratören har avaktiverat privata meddelanden för hela forumet eller administratörern har stoppat dig från att skicka meddelanden. Om det är det senare fallet så fråga administratören varför.");
$faq[] = array("Jag får oönskade privata meddelanden!", "I framtiden kommer vi att lägga till en ignoreralista till det privata meddelandesystemet. Om du fortsätter att få oönskade meddelanden så prata med en administratör, de har möjligheter att stoppa en användare från att skicka privata meddelanden överhuvudtaget.");
$faq[] = array("Jag har fått spam eller anstötliga email från någon på detta forum!", "Vi är ledsna att höra detta. Email-forumläret i forumet innehåller skydd för att försöka att spåra användare som skickar sådana email. Du bör emaila administratörerna på forumet och bifoga en full kopia av email du fick och det är mycket viktigt att du bifogar email huvudet (detta innehåller detaljerna om vilken användare som skickat emailet). Administratörerna kan därefter vidta åtgärder.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","phpBB 2 Issues");
$faq[] = array("Who wrote this bulletin board?", "This software (in its unmodified form) is produced, released and is copyright  <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. It is made available under the GNU General Public Licence and may be freely distributed, see link for more details");
$faq[] = array("Why isn't X feature available?", "This software was written by and licensed through phpBB Group. If you believe a feature needs to be added then please visit the phpbb.com website and see what phpBB Group have to say. Please do not post feature requests to the board at phpbb.com, the Group uses sourceforge to handle tasking of new features. Please read through the forums and see what, if any, our position may already be for a feature and then follow the procedure given there.");
$faq[] = array("Who do I contact about abusive and/or legal matters related to this board?", "You should contact the administrator of this board. If you cannot find who this you should first contact one of the forum moderators and ask them who you should in turn contact. If still get no response you should contact the owner of the domain (do a whois lookup) or, if this is running on a free service (e.g. yahoo, free.fr, f2s.com, etc.), the management or abuse department of that service. Please note that phpBB Group has absolutely no control and cannot in any way be held liable over how, where or by whom this board is used. It is absolutely pointless contacting phpBB Group in relation to any legal (cease and desist, liable, defamatory comment, etc.) matter not directly related to the phpbb.com website or the discrete software of phpBB itself. If you do email phpBB Group about any third party use of this software then you should expect a terse response or no response at all.");

//
// This ends the FAQ entries
//

?>
