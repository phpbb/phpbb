<?php
/***************************************************************************
 *
 *	language/lang_icelandic/lang_bbcode.php   [icelandic]
 *	------------------------------------------------------------------------
 *
 *	Created     Sun,  8 Sep 2002 01:12:56 +0200
 *
 *	Copyright   (c) 2002 The phpBB Group
 *	Email       support@phpbb.com
 *
 *	Created by  C.O.L.T. v1.4.4 - The Cool Online Language Translation Tool
 *	            Fast like a bullet and available online!
 *	            (c) 2002 Matthias C. Hormann <matthias@hormann-online.net>
 *
 *	Visit       http://www.phpbb.kicks-ass.net/ to find out more!
 *
 ***************************************************************************/

/***************************************************************************
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 ***************************************************************************/

// 
// To add an entry to your BBCode guide simply add a line to this file in this format:
// 

//
// This ends the BBCode guide entries
//

/***************************************************************************
 *
 *	To add an entry to your FAQ simply add a line to this file in this format:
 *
 *		$faq[] = array("question", "answer");
 *
 *	If you want to separate a section enter:
 *
 *		$faq[] = array("--", "Block heading here if wanted");
 *
 *	Links will be created automatically.
 *
 *	DO NOT forget the ; at the end of the line.
 *	DO NOT put double quotes (") in your FAQ entries. If you absolutely
 *	must then escape them, i.e. \"something\";
 *
 *	The FAQ items will appear on the FAQ page in the same order as listed here.
 *
 ***************************************************************************/

$faq[] = array("--", "Kynning");
$faq[] = array("Hvað er BB kóði 'BBCode'?", "BB kóði er svipaður og HTML. Hvort þú getur notað BB kóða á innleggjum þínum er undir umsjónarmanni umræðuborðsins komið. Þú getur líka valið hvort þú gerir BB kóðann óvirkan í hverju innleggi fyrir sig á innleggssíðunni. BB kóðinn sjálfur er svipaður og HTML, tög eru með hornklofa [ og ] frekar en < og > og það gefur það mikla stjórn yfir því hvernig innleggin koma út á skjánum. Þar sem kóðarnir eru fyrir ofan innleggin þá er mjög auðvelt að bæta þeim við með því að klikka á þá. Þetta er þó mismunandi eftir þema borðanna. Þó svo að það er svona auðvelt þá gætu þessar leiðbeiningar reynst góðar.");

$faq[] = array("--", "Útlitbreyting texta");
$faq[] = array("Hvernig er feitletrað, skáletrað og undirstrikað ", "BB kóðinn inniheldur tög sem gerir auðvelt að breyta grunn útliti texta. Þetta er gert á eftirfarandi hátt: <ul><li>Til að gera hluta af texta feitletraðan settu hann innan <b>[b][/b]</b>, t.d. <br /><br /><b>[b]</b>Halló<b>[/b]</b><br /><br />þetta gerir textann svona: <b>Halló</b></li><li>Til að strika undir texta settu hann þá innan <b>[u][/u]</b>, til dæmis:<br /><br /><b>[u]</b>Góðan dag<b>[/u]</b><br /><br /> verður <u>Góðan dag</u></li><li>Til að gera skáletur þá á að nota <b>[i][/i]</b>, t.d.<br /><br />Þetta er <b>[i]</b>flott!<b>[/i]</b><br /><br />Myndi gera: Þetta er <i>flott!</i></li></ul>");

$faq[] = array("Hvernig á að breyta lit og stærð á texta", "Til að breyta lit og texta þá er hægt að nota eftirfarandi tög.  Athugaðu þó að útkoman fer eftir vafra þess sem skoðar hann og kerfi: <ul><li>Til að breyta lit á texta þá á að setja hann innan <b>[color=][/color]</b>. Þú getur annað hvort skilgreint nafn á lit (s.s. red, blue, yellow, o.s.frv.) eða þú getur sett inn þrefalda sextánda tölu, s.s. #FFFFFF, #000000. Til dæmis að gera rauðan texta þá getur þú notað:<br /><br /><b>[color=red]</b>Halló!<b>[/color]</b><br /><br />eða<br /><br /><b>[color=#FF0000]</b>Halló!<b>[/color]</b><br /><br />Báðar aðferðir gefa sömu útkomu: <span style=\"color:red\">Halló!</span></li><li>Til að breyta stærð á texta er gert samskonar með því að nota <b>[size=][/size]</b>. Þessi tög eru háð þema borðsins en það sem við mælum með er tala sem segir til um punkta stærð á stafnum. Hún byrjar á 1 (sem er svo lítill að þá sést ekki neitt) og endar á 29 (mjög stórir). Til dæmis:<br /><br /><b>[size=9]</b>LÍTILL<b>[/size]</b><br /><br />mun búa til <span style=\"font-size:9px\">LITLA</span><br /><br />en hins vegar:<br /><br /><b>[size=24]</b>RISASTÓR!<b>[/size]</b><br /><br />mun verða: <span style=\"font-size:24px\">RISASTÓR!</span></li></ul>");
$faq[] = array("Get ég notað tög saman?", "Já, auðvitað getur þú það, til dæmis til að ná athygli einhvers getur þú gert svona:<br /><br /><b>[size=18][color=red][b]</b>SJÁIÐ MIG!<b>[/b][/color][/size]</b><br /><br />þetta myndi koma svona út:  <span style=\"color:red;font-size:18px\"><b>SJÁIÐ MIG!</b></span><br /><br />Við mælum samt ekki með að nota mikið texta sem lítur svona út!<br />Mundu svo að þú þarft að sjá um að loka tögum rétt. Til dæmis þá er eftirfarandi rangt:<br /><br /><b>[b][u]</b>Þetta er rangt<b>[/b][/u]</b>");


$faq[] = array("--", "Vitna í texta og föst breidd á texta");
$faq[] = array("Vitna í texta í innleggjum", "Það eru tvær leiðir til þess, með tilvísun eða án.<ul><li>Þegar þú notar tilvísun til að svara innleggi þá sérðu að textinn kemur inn í kassann þar sem þú átt að setja þitt innlegg með <b>[quote=\"\"][/quote]</b> framan og aftan við. Þessi aðferð veitir möguleika á að vísa til innleggs eða annars sem að einhver annar hefur sent inn! Til dæmis að vitna í texta sem Jóhann skrifaði þá áttu að setja:<br /><br /><b>[quote=\"Jóhann\"]</b>Hér á textinn að koma sem Jóhann skrifaði<b>[/quote]</b><br /><br />Útkoman er sú að það bætist sjálfvirkt við, Jóhann skrifaði: á undan sjálfum textanum. Mundu að þú <b>verður</b> að hafa gæsalappirnar \"\" sitthvoru megin við það sem þú ert að vitna í, það er ekki val um það.</li><li>Hin aðferðin er sú að blindandi vitna til einhvers. Til að gera það þá þarftu að setja<b>[quote][/quote]</b> tög sitt hvorumegin við textann. Þegar þú skoðar innleggið þá sérðu bara, Tilvísun: á undan textanum sjálfum.</li></ul>");


$faq[] = array("--", "Búa til lista");
$faq[] = array("Búa til ónúmeraðan lista", "BB kóði styður tvær gerðir af listum, ónúmeraðaðan og númeraðan. Þetta er eins gert og í HTML. Ónúmeraður listi setur fram lista í röð en það er sett stjarna eða punktur undan hverju í listanum. Til að búa til ónúmeraðan lista þá átt að nota <b>[list][/list]</b> og skilgreina hvern hlut innan listans með því að nota <b>[*]</b>. Til dæmis að lista uppáhalds litina þína þá gerirðu svona:<br /><br /><b>[list]</b><br /><b>[*]</b>Rauður<br /><b>[*]</b>Blár<br /><b>[*]</b>Gulur<br /><b>[/list]</b><br /><br />Þetta býr til eftirfarandi lista:<ul><li>Rauður</li><li>Blár</li><li>Gulur</li></ul>");
$faq[] = array("Búa til númeraðan lista", "Önnur aðferð við að búa til lista er sú að stjórna hvað kemur fyrir framan hvern hlut í honum. Til að búa til númeraðan lista þá gerir þú svona: <b>[list=1][/list]</b> til að búa til lista með bókstöfum í stað númera: <b>[list=a][/list]</b>. Eins og með ónúmeraðan lista þá er hver hlutur í listanum tilgreindur með því að nota <b>[*]</b>. Til dæmis:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Fara í búðir<br /><b>[*]</b>Kaupa nýja tölvur<br /><b>[*]</b>Skammast yfir tölvunni þegar hún frýs<br /><b>[/list]</b><br /><br />Þetta kemur svona fram::<ol type=\"1\"><li>Fara í búðir</li><li>Kaupa nýja tölvu</li><li>Skammast yfir tölvunni þegar hún frýs</li></ol>Fyrir lista með bókstöfum fyrir framan þá gerirðu svona:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Fyrsta mögulega svar<br /><b>[*]</b>Annað mögulega svar<br /><b>[*]</b>Þriðja mögulega svar<br /><b>[/list]</b><br /><br />gerir:<ol type=\"a\"><li>Fyrsta mögulega svar</li><li>Annað mögulega svar</li><li>Þriðja mögulega svar</li></ol>");


$faq[] = array("--", "Að búa til tengla/links");
$faq[] = array("Tengill á aðra vefsíðu", "phpBB BB kóði styður nokkrar leiðir til að búa tengla á aðrar vefsíður.<ul><li>Fyrsta aðferðin er að nota <b>[url=][/url]</b> tag, hvað sem þú setur á eftir = merkinu mun valda því að það hegðar sér eins og tengill. Til dæmis að setja tengil til phpBB.com þá áttu að nota:<br /><br /><b>[url=http://www.phpbb.com/]</b>Skoðaðu phpBB!<b>[/url]</b><br /><br />Þetta myndi búa til eftirfarandi tengil, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Skoðaðu phpBB!</a> Þú munt taka eftir því að það opnast nýr gluggi svo að notandi getur haldið áfram að skoða umræðu borðið þar sem frá er horfið.</li><li>Ef Þú vilt að veffangið (URLið) sjálft sé sýnt sem tengill þá getur þú einfaldlega gert svona:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Þetta myndi búa til eftirfarandi tengil, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Að auki er phpBB með svolítið sem kallast <i>Töfra tengill</i>, þetta breytir öllu sem er skrifað eins og veffang (URL) í tengil sjálfvirkt jafnvel þó svo þú skrifir ekki http://. Til dæmis með því að skrifa www.phpbb.com inn í innleggi þá leiðir það til þess að það myndast tengill, <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> er búinn til um leið og þú skoðar innleggið.</li><li>Það sama gerist með netföng, þú getur annaðhvort búið til netfangið t.d. nákvæmlega svona:<br /><br /><b>[email]</b>enginn@veffang.is<b>[/email]</b><br /><br />Þetta verður að <a href=\"emailto:no.one@domain.adr\">enginn@veffang.is</a> eða þú getur einfaldlega skrifað enginn@veffang.is inn í innleggið og því er svo breytt sjálfvirkt þegar þú skoðar það.</li></ul>Eins og með öll BB kóða tög þá getur þú sett vefföng utanum önnur tög svo sem <b>[img][/img]</b> (Sjáðu það næsta), <b>[b][/b]</b>, o.s.frv. Eins og og með öll önnur tög þá þarft þú að sjá um að loka þeim í réttri röð, til dæmis:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />er <u>ekki</u> rétt en það gæti valdið því að innlegginu verði eytt þannig að passaðu þig.");

$faq[] = array("--", "Sýna myndir í innleggjum");
$faq[] = array("Bæta við mynd í innleggjum", "phpBB BB kóði inniheldur tag sem er til að setja inn myndir í innleggin þín. Þú þarft að hafa tvennt í huga þegar þú notar þessi tög; mörgum notendum líkar ekki að það séu settar margar myndir inn í innlegg og myndin verður að vera þegar til á vefnum (hún getur ekki bara verið á þinni tölvu til dæmis, nema hún sé vefþjónn!). Það er núna ekki möguleiki á því að vista myndir á vefþjóninum þar sem phpBB er(allt þetta kemur að öllum líkindum í næstu útgáfu af phpBB). Til að sýna myndir þá þarftu að setja veffangið sem inniheldur myndina innan: <b>[img][/img]</b> taga. Til dæmis:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Eins og kemur fram kemur í veffanga útskýringunum að ofan getur þú sett mynd innan <b>[url][/url]</b> taga ef þú vilt, t.d.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />myndi gera svona:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Önnur mál");
$faq[] = array("Get ég bætt við mínum eigin tögum?", "Nei, það er ég hræddur um ekki. Við erum athuga með að bjóða upp á möguleika á því í útgáfu 3.0");

?>