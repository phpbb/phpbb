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
//   Norwegian translation by lanes, shantra & water
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


$faq[] = array("--","Introduksjon");
$faq[] = array("Hva er BBCode?", "BBCode er en tilpasset utgave av HTML, og det er opp til administrator av forumene om du har anledning til å benytte BBCode i innleggene dine. Du kan også selv deaktivere BBCode i hvert enkelt innlegg dersom du ønsker det. BBCode føler et lignende prinsipp som HTML, men bruker [ og ] isteden for &lt; og &gt;. Du har også mer kontroll med hvilket og hvordan innholdet vises. Avhengig av hvilken stil som benyttes på forumene, eller hvilken stil du selv har valgt, er det veldig lett å bruke BBCode vha. knapper på skjemaet for å legge til innlegg.<br /><br />");

$faq[] = array("--","Tekst formatering");
$faq[] = array("Hvordan kan jeg skrive fet, kursiv og understreket tekst?", "BBCode har tagger som gjør det lett å formatere tekst på følgende måter: <ul><li>For å gjøre tekst fet omgir du den med <b>[b][/b]</b>, f.eks. <br /><br /> <b>[b]</b>Fet tekst<b>[/b]</b><br /><br />blir formatert slik : <b>Fet tekst</b><br /><br /></li><li>Bruk <b>[u][/u]</b> for å understreke tekst, f.eks. <br /><br /><b>[u]</b>Understreket tekst<b>[/u]</b><br /><br />blir formatert slik : <u>Understreket tekst<br /><br /></u></li><li>Bruk <b>[i][/i]</b> for å sette tekst i kursiv , f.eks.<br /><br /><b>[i]</b>Dette er kursiv<b>[/i]</b><br /><br /> blir formatert slik : <i>Dette er kursiv</i><br /><br /></li></ul>");
$faq[] = array("Hvordan kan jeg endre tekststørrelse og farge?", "Du kan bruke følgende tagger for å endre tekstens farge og størrelse, men husk at formateringen vil kunne variere i forskjellige nettlesere og operativsystemer. <ul><li>Du kan endre tekstens farge med <b>[color=][/color]</b>, og du kan benytte godkjente fargenavn (f.eks. red, blue, yellow, osv.) eller heksadesimalkode (f.eks. #FFFFFF, #000000). For å formatere teksten med rød kan du bruke : <br /><br /><b>[color=red]</b>Dette er rød tekst<b>[/color]</b><br /> <br />eller<br /><br /><b>[color=#FF0000]</b>Dette er rød tekst<b>[/color]</b><br /><br /> som begge vil formatere teksten slik : <span style=\"color:red\">Dette er rød tekst</span><br /><br /> </li><li> Du kan endre tekstens størrelse på tilsvarende måte med <b>[size=][/size]</b>. Denne taggen er avhegning av hvilken stil forumet benytter, men det anbefales å bruke nummerisk verdi for å angi størrelse i piksler. Du kan angi størrelser fra 1 (som gir nærmest uleselig tekst) og opp til 29 (som blir veldig stort). <br /><br /><b>[size=9]</b> liten tekst<b>[/size]</b><br /><br />blir formatert slik : <span style=\"font-size:9px\">liten tekst </span><br /><br />og<br /><br /><b>[size=24]</b>STOR TEKST<b>[/size]</b><br /><br /> blir formatert slik :  <span style=\"font-size:24px\">STOR TEKST</span></li></ul>");
$faq[] = array("Kan jeg kombinere formateringstagger?", "Ja, for å virkelig vekke oppmerksomhet kan du f.eks. benytte :<br /><br /><b>[size=18][color=red][b]</b>LES HER!<b>[/b][/color][/size]</b><br /><br />som blir formatert slik : <span style=\"color:red;font-size:18px\"><b>LES HER!</b></span><br /><br />Vi anbefaler at du ikke benytter dette mye, det kan bli vanskelig å lese og det er opp til deg som skriver innlegget å sørge for at du bruker korrekt formatert BBCode. Det er f.eks. lett å \"gå seg bort\" hvis du bruker for mange tagger i hverandre, følgende er et eksempel på dette :<br /><br /><b>[b][u]</b> Dette er feil!<b>[/b][/u]</b><br /><br />");

$faq[] = array("--","Sitere og vise predefinert tekst");
$faq[] = array("Sitere tekst i svar", "Du kan sitere både med og uten referanse til  opprinnelig forfatter.<ul><li>Når du bruker siter funksjonen for å svare på et innlegg vil du se at teksten du siterer er satt inn i <b>[quote=\"\"][/quote]</b> tagger. Denne metoden gir deg full anlednig til å sitere en annen person eller hva som helst annet du ønsker å sitere. Hvis f.eks ønsker å sitere en Mr. Blobby uttalelse skriver du :<br /><br /><b>[quote=\"Mr. Blobby\"]</b>Mr. Blobbys uttalelse<b>[/quote]</b><br /><br /> Mr. Blobby skrev: vil automatisk bli lagt til før teksten du siterer. Husk at du <b>må</b> bruke anførselstegn, \"\", rundt navnet på den du siterer.<br /><br /></li><li>Du kan også sitere uten å angi hvem eller hva du siterer, bruk <b>[quote][/quote]</b> tagger rundt teksten du ønsker å sitere. Når du siterer vha. denne metoden vil Sitat : automatisk bli satt inn før teksten du siterer.<br /><br /></li></ul>");
$faq[] = array("Skrive kode eller predefinert tekst", "Hvis du ønsker å vise kode eller predefinert tekst bruker du taggene <b>[code][/code]</b>, f.eks. <br /><br /><b>[code]</b>echo \"Dette er kode\";<b>[/code]</b><br /><br />All tekst som settes i disse taggene vil beholde formatet du benytter når den vises i innlegget.<br /><br />");

$faq[] = array("--","Generere lister");
$faq[] = array("Lage en ikke sortert liste", "BBCode støtter både sorterte og ikke-sorterte lister, som i praksis er lik tilsvarende lister laget med HTML. En ikke-sorterte liste viser en vanlig punktliste med punkter som markør for hvert nye punkt på lista. Bruk <b>[list][/list]</b>, og angi hvert punkt med <b>[*]</b>, for å lage en ikke-sorterte liste. <br /><br /><b>[list]</b><br /><b>[*]</b>Rød<br /><b>[*]</b>Blå<br /><b>[*]</b>Gul<br /><b>[/list]</b><br /><br /> formateres slik : <ul><li>Rød</li><li>Blå</li><li>Gul</li></ul>");
$faq[] = array("Lage en sortert liste", "Velge du en sortert liste kan erstatte det vanlige punktlista med en nummerert eller alfabetisert liste. Bruk <b>[list=1][/list]</b> for å lage en nummerert liste eller <b>[list=a][/list]</b> for å lage alfabetisert liste, og <b>[*]</b> for å angi hvert punkt på lista.<br /><br /><b>[list=1]</b><br /><b>[*]</b>Gå til butikken<br /><b>[*]</b>Kjøp ny pc<br /><b>[*]</b>Kjeft på pc-en når den kræsjer<br /><b>[/list]</b><br /><br /> formateres slik :<ol type=\"1\"><li>Gå til butikken</li><li>Kjøp ny pc</li><li>Kjeft på pc-en når den kræsjer</li></ol> Alternativt lage du en alfabetisert liste slik :<br /><br /><b>[list=a]</b><br /><b>[*]</b>Det første svaralternativet<br /><b>[*]</b>Det andre svaralternativet<br /><b>[*]</b>Det tredje svaralternativet<br /><b>[/list]</b><br /><br />som vil bli formatert slik :<ol type=\"a\"><li>Det første svaralternativet</li><li>Det andre svaralternativet</li><li>The Det tredje svaralternativet</li></ol>");

$faq[] = array("--", "Lage linker");
$faq[] = array("Eksterne linker", "Du kan velge mellom flere metoder for å lage URL-er eller linker med BBCode.<ul><li><b>Med [url=][/url]</b> taggene vil teksten mellom taggene linke til det du skriver etter the = f.eks. :<br /><br /><b>[url=http://www.phpbb.com/]</b>Besøk phpBB!<b>[/url]</b><br /><br />gir følgende link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Besøk phpBB!</a> Legg merke til at linken åpnes i et nytt vindu slik at forumet beholdes i det originale nettleservinduet.<br /><br /></li><li>Hvis du ønsker at selve URL-en skal være synlig bruker du :<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />som gir følgende link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a><br /><br /></li><li>PhpBB har en funksjon som kalles <i>Magic Links</i>, som automatisk konverterer alle korrekt formaterte URL-er til linker, du trenger ikke engang skrive http:// først. Hvis du f.eks. skriver www.phpbb.com i innlegget ditt vil teksten automatisk bli konvertert til <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a>.<br /><br /></li><li><i>Magic Links</i> fungerer også med e-postadresser, du kan enten skrive :<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />som formateres slik : <a href=\"mailto:no.one@domain.adr\">no.one@domain.adr</a> eller du kan skrive, som også formateres slik :  <a href=\"mailto:no.one@domain.adr\">no.one@domain.adr</a>.<br /><br /></li></ul>Som med alle andre BBCode tagger kan du bruke URL taggene sammen med andre formateringstagger, f.eks. <b>[img][/img]</b> (se neste seksjon), <b>[b][/b]</b>, osv. På lik linje med formateringstaggene må du selv sørge for at alle taggene åpnes og lukkes korrekt.<br /><br />Dette eksemplet :  <b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />er <u>ikke</u> korrekt formatert, og i et slikt tilfellet kan du risikere at innlegget ditt blir slettet.<br /><br />");

$faq[] = array("--", "Vise bilder i posteringer");
$faq[] = array("Legge til bilde(r) i et innlegg", "phpBB BBCode har en tagg som gjør det mulig å vise bilder i innleggene dine. Når du vurderer å bruke bilder i innleggene er det viktig er klar over følgende, mange brukere foretrekker temaer som ikke er overlesset med bilder. I tillegg må også bildet/bildene du skal bruke allerede finnes på www (det holder f.eks. ikke at du har bildet på pc-en din). Det er ikke mulig å lagre eller laste opp bilder til phpBB, men dette vil bli mulig i neste versjon, phpBB 2.2.<br /><br /> For å vise bilder i et innlegg må du bruke BBCode taggene <b>[img][/img]</b> rundt bildenes URLer som i eksemplet under<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Du kan også bruke bilder som linker vha. <b>[url][/url]</b>, f.eks.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />gir :<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br /><br />");

$faq[] = array("--", "Annet");
$faq[] = array("Kan jeg lage mine egne koder?", "Nei, dette er dessverre ikke mulig i phpBB 2, men det er godt mulig det blir funksjonalitet for dette i neste hovedversjon, phpBB 2.2.<br /><br />");


//
// This ends the BBCode guide entries
//

?>