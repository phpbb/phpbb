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
/***************************************************************************/
 *
 *  Translation to Albanian done by alket ---> alkettttt@yahoo.com
 * http://www26.brinkster.com/alketttt/
 *
 ***************************************************************************/
 
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
  
$faq[] = array("--","Hyrje");
$faq[] = array("C'është BBCode?", "BBCode është një implementim special i HTML. Përdorimi i BBCode tek forumi juaj varet nga konfigurimi që ka bërë administratori. Për më tepër BBCode mund të aktivizohet ose c'aktivizohet në mesazhet individuale. BBCode është shumë i ngjashëm me HTML, tags janë mbyllur brenda kllapave katrore [ and ] në vend të &lt; and &gt; dhe ofron kontroll më të madh në formatimin e teksteve. Në varësi të shabllonit në përdorim, shtimi i BBCode në mesazhet tuaja është shumë i lehtë nëpërmjet kodeve të klikueshme mbi përmbajtjen e mesazhit në formularin e postimit. Megjithatë guida e mëposhtme mund t'ju hyjë në punë.");

$faq[] = array("--","Formatimi i tekstit");
$faq[] = array("Si të krijoni tekst bold, underlined dhe italic.", "BBCode ju lejon ta ndryshoni stilin e tekstit shumë thjeshtë. Psh: <ul><li>Për ta bërë një tekst bold futeni midis <b>[b][/b]</b>, psh. <br /><br /><b>[b]</b>Përshëndetje<b>[/b]</b><br /><br />bëhet <b>Përshëndetje</b></li><li>Për nënvizim përdor <b>[u][/u]</b>, psh:<br /><br /><b>[u]</b>Mirëmëngjes<b>[/u]</b><br /><br />bëhet <u>Mirëmëngjes</u></li><li>Për të bërë tekstin me italics përdor <b>[i][/i]</b>, psh.<br /><br />Si jeni <b>[i]</b>Sonte?<b>[/i]</b><br /><br />del:  Si jeni <i>Sonte?</i></li></ul>");
$faq[] = array("Si të ndryshohet ngjyra ose madhësia e tekstit.", "Përdorni kodet e mëposhtme për këtë, megjithatë kini parasysh që paraqitja në ekran varet nga sistemi dhe browser-i që përdoret: <ul><li>Ndryshimi i ngjyrës bëhet duke e futur tekstin midis <b>[color=][/color]</b>. Ju mund të specifikoni emrin e ngjyrës (psh. red - kuq, blue - blu, yellow - verdhë, etj.) ose kodin hegzadeksimal, psh. #FFFFFF, #000000. Psh, për të shkruar me të kuqe përdorni:<br /><br  ><b>[color=red]</b>Hello!<b>[/color]</b><br /><br />ose<br /><br /><b>[color=#FF0000]</b>Hello!<b>[/color]</b><br /><br />dhe të dyja kodet do nxjerrin <span style=\"color:red\">Hello!</span></li><li>Ndryshimi i madhësisë së tekstit bëhet në mënyrë të ngjashme duke përdorur<b>[size=][/size]</b>. Ky kod varet nga shablloni në përdorim megjithatë formati i rekomanduar është një vlerë numerike që prezanton madhësinë e tekstit në piksel, duke filluar nga 1 (aq i vogël sa nuk e sheh dot) deri tek 29-ta (shumë i madh). Psh:<br /><br /><b>[size=9]</b>I vogël<b>[/size]</b><br /><br />do jetë zakonisht <span style=\"font-size:9px\">I vogël</span><br /><br />kurse:<br /><br /><b>[size=24]</b>I stërmadh!<b>[/size]</b><br /><br />do jetë <span style=\"font-size:24px\">I stërmadh!</span></li></ul>");
$faq[] = array("A mund ti kombinoj kodet e formatimit?", "Po, patjetër, psh. nqs doni të tërhiqni vëmendjen mund të shkruani:<br /><br /><b>[size=18][color=red][b]</b>Kujdes!<b>[/b][/color][/size]</b><br /><br />dhe kjo do nxjerrë <span style=\"color:red;font-size:18px\"><b>Kujdes!</b></span><br /><br />Kini parasysh që është përgjegjësia juaj ti mbyllni kodet e formatimit në mënyrë korrekte. Shembulli i mëposhtëm tregon një rast kur kodet nuk janë mbyllur në mënyrë korrekte:<br /><br /><b>[b][u]</b>Kjo është gabim<b>[/b][/u]</b>");

$faq[] = array("--","Si të citoni dhe tekstet me gjerësi fikse");
$faq[] = array("Si të citoni në përgjigjet tuaja", "Ka dy mënyra për të cituar, me ose pa referencë.<ul><li>Kur përdorni funksionin Cito në përgjigjet tuaja do shikoni që teksti i mesazhit shtohet në dritaren ku do shkruani i përfshirë në një bllok <b>[quote=\"\"][/quote]</b>. Kjo metodë ju lejon të citoni duke iu refereruar një personi. Psh. për të cituar dicka nga z.Arjan shkruani:<br /><br /><b>[quote=\"z.Arjan\"]</b>teksti që po citoni shkon këtu<b>[/quote]</b><br /><br />Kjo do rezultojë në shtimin e  z.Arjan shkroi: përpara tekstit që po citoni. Ju <b>duhet</b> të vini  \"\" rreth emrit; janë të domosdoshme.</li><li>Metoda e dytë ju lejon të citoni dicka pa specifikuar autorin. Për të bërë këtë rrethojeni tekstin mekodet <b>[quote][/quote]</b>. Kur të shikoni mesazhin do vëreni që do tregojë vetëm Kuotë: përpara tekstit. </li></ul>");
$faq[] = array("Shkruarja e kodeve kompjuterike dhe teksteve me gjerësi fikse", "Nqs doni të shkruani një pjesë me kod kompjuteri ose cdo lloj teksti që kërkon gjerësi fikse atëherë duhet ta rrethoni me kodet <b>[code][/code]</b>, psh.<br /><br /><b>[code]</b>echo \"Ky është kod komputeri\";<b>[/code]</b><br /><br />Formatimi i përdorur brënda kodeve <b>[code][/code]</b> ruhet kur përpunohet nga kompjuteri.");

$faq[] = array("--","Prodhimi i listave");
$faq[] = array("Krijimi i një liste të pa-renditur", "BBCode lejon dy lloj listash, të renditura dhe të parenditura. Në thelb ato janë të njëjta me ekuivalentet në HTML. Një listë e parenditur tregon cdo send në listën tuaj në mënyrë të njëpasnjëshme të paraprira nga një karakter pike. Për të krijuar një listë të parenditur përdoret <b>[list][/list]</b> dhe përcakto cdo send të listës duke përdorur <b>[*]</b>. Psh. për të renditur ngjyrat tuaja të preferuara ju mund të përdorni:<br /><br /><b>[list]</b><br /><b>[*]</b>E kuqe<br /><b>[*]</b>Blu<br /><b>[*]</b>E verdhë<br /><b>[/list]</b><br /><br />Kjo do prodhonte listën<ul><li>E kuqe</li><li>Blu</li><li>E verdhë</li></ul>");
$faq[] = array("Krijimi i një liste të renditur", "Lloji i dytë i listës, lista e renditur, ju mundëson të kontrolloni se c'shkruhet para cdo sendi të listës. Për të krijuar një listë të renditur përdoret <b>[list=1][/list]</b> për të krijuar një listë me renditje numerike ose <b>[list=a][/list]</b> për një listë me renditje alfabetike. Njëlloj si tek lista e parenditur cdo send përcaktohet nga <b>[*]</b>. Psh:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Shko në dyqan<br /><b>[*]</b>Bli një kompjuter<br /><b>[*]</b>Shaje kompjuterin kur nuk punon<br /><b>[/list]</b><br /><br />do prodhojë:<ol type=\"1\"><li>Shko në dyqan</li><li>Bli një kompjuter</li><li>Shaje kompjuterin kur nuk punon</li></ol>kurse për një listë me renditje alfabetike:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Përgjigja e parë e mundshme<br /><b>[*]</b>Përgjigja e dytë e mundshme<br /><b>[*]</b>Përgjigja e tretë e mundshme<br /><b>[/list]</b><br /><br />e cila jep<ol type=\"a\"><li>Përgjigja e parë e mundshme</li><li>Përgjigja e dytë e mundshme</li><li>Përgjigja e tretë e mundshme</li></ol>");

$faq[] = array("--", "Krijimi i lidhjeve");
$faq[] = array("Lidhje me një websit tjetër", "BBCode i phpBB lejon disa mënyra për krijimin e URI-ve, Uniform Resource Indicators që njihen si URL.<ul><li>Mënyra e parë përdor kodin <b>[url=][/url]</b>, cdo gjë që shkruani mbas shenjës së barazimit do shkaktojë përmbajtjen e atij kodi të shërbejë si një URL. Psh. për tu lidhur me phpBB.com me këtë metodë shkruani:<br /><br /><b>[url=http://www.phpbb.com/]</b>Vizito phpBB!<b>[/url]</b><br /><br />kjo do prodhonte lidhjen që vijon, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Vizito phpBB!</a> Do shikoni që lidhja hapet në një dritare të re duke i lejuar përdoruesit të vazhdojnë të përdorin forumin.</li><li>Nqs doni që adresa URL vetë të tregohet si lidhje shkruani:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Kjo do tregojë lidhjen që vijon, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Për më tepër phpBB përdor dicka që quhet <i>Lidhjet Magjike</i>, kjo do e kthejë cdo URL me sintaksë korrekte në një lidhje pa patur nevojë te shkruhet ndonjë kod, nuk ka nevojë as për http://. Psh. shkruajtja e www.phpbb.com në mesazhin tuaj do krijojë një lidhje tek <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> në mënyrë automatike kur dikush lexon mesazhin. </li><li>E njëjta gjë aplikohet me adresat e postës elektronike, ju mund ta specifikoni adresën me anë të kodit, psh:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />e cila do tregojë <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> ose mund të shtypni thjesht no.one@domain.adr në mesazhin tuaj dhe adresa do konvertohet në lidhje në mënyrë automatike.</li></ul>Ashtu si me të gjitha kodet e tjera BBCode URL-të mund të mbështjellin cdo lloj kodi tjetër BBCode, psh <b>[img][/img]</b> (lexo më poshtë), <b>[b][/b]</b>, etj. Ashtu si me kodet e formatimit është në dorën tuaj që kodet të jenë hapur dhe mbyllur në rradhën e duhur, psh: <br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /> <u>nuk</u> është korrekte gjë që mund të shkaktojë fshirjen e mesazhit tuaj.");

$faq[] = array("--", "Tregimi i imazheve");
$faq[] = array("Shtimi i një imazhi në mesazhin tuaj", "BBCode i phpBB përfshin një kod për tregimin e imazheve në mesazhet tuaja. Duhet të keni dy gjëra parasysh kur përdorni këtë kod; e para, shumë përdorues nuk e cmojnë vendosjen e shumë imazheve nëpër poste dhe e dyta imazhi duhet të jetë diku në internet para se të tregohet në një mesazh (nuk mund të ekzistojë vetëm në kompjuterin tuaj, përvec rastit kur keni një webserver). phpBB nuk e mundëson mbajtjen e imazheve në një dosje lokale tashti për tashti (këto cështje do adresohen në versionin e mëvonshëm të phpBB). Për të treguar një imazh duhet ta rrethoni URL-në (adresën në internet) e imazhit me kodet <b>[img][/img]</b>. Psh:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Sic u tregua në seksionin e URL (mësipër) ju mund ta mbështillni një imazh me kodin <b>[url][/url]</b> nqs dëshironi që imazhi të shërbejë edhe si lidhje, psh.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />do prodhonte:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Cështje të tjera");
$faq[] = array("A mund të shtoj kodet e mia?", "Jo, nuk besojmë se është e mundur në phpBB 2.0 Po mundohemi të shtojmë kode BBCode të personalizueshme në versionin tjetër të phpBB.");

//
// This ends the BBCode guide entries
//

?>