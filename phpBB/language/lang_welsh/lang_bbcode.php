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
  
$faq[] = array("--","Cyflwyniad");
$faq[] = array("Beth yw BBCode?", "Mae BBCode yn fersiwn arbennig o HTML. Mae gweinydd y fforwm yn penderfynnu os ydy BBCode ar gael neu beidio. Os ydy e ar gael, dych chi'n gallu penderfynnu a yw e'n gweithio yn eich negeseuon chi, fesul neges. Mae BBCode yn edrych yn debyg iawn i HTML, ond mae tagiau yn cael eu hamgau mewn cromfachau sgw‰r [ fel hyn ] ac nid cromfachau ongl &lt; fel hyn &gt;. Mae BBCode yn eich helpu newid y ffordd mae eich neges yn ymddangos. Gyda rhai patrymluniau phpBB, mae rhyngwyneb clecadwy ar y ffurflen postio yn eich helpu chi rhoi tagiau BBCode yn eich neges. Ond hyd yn oed gyda'r help llaw hwnna, mae'n bosib y bydd y canllawiau isod yn gymorth i chi.");

$faq[] = array("--","Fformadu Testun");
$faq[] = array("Sut i greu testun du, italeg ac wedi'i tanlinellu", "Mae BBCode yn cynnwys tagiau i'ch helpu newid steil sylfaenol eich testun yn gyflym. Dyma sut mae'n gweithio: <ul><li>I droi darn o destun yn <b>wynebddu</b>, rhoi fe tu mewn tagiau <b>[b][/b]</b>, e.e. <br /><br />Bydd:<br /><br /><b>[b]</b>Helo<b>[/b]</b><br /><br />yn troi yn <b>Helo</b><br /></li><li>I <u>danlinellu</u> testun, defnyddia <b>[u][/u]</b>, er enghraifft:<br /><br /><b>[u]</b>Bore Da<b>[/u]</b><br /><br />yn newid i <u>Bore Da</u></li><li>I newid i <i>destun italeg</i>, defnyddia <b>[i][/i]</b>, e.e.<br /><br />Mae hyn yn <b>[i]</b>wych!<b>[/i]</b><br /><br />yn mynd i<br /><br />Mae hyn yn <i>wych!</i></li></ul>");
$faq[] = array("Sut i newid lliw a maint y testun", "Ceir defnyddio y tagiau canlynol i newid lliw a maint eich testun. Cofiwch bydd golwg y testun yn dibynnu ar borwr a sustem y gwyliwr: <ul><li>Newid lliw y testun gan ei lapio mewn tagiau <b>[color=][/color]</b>. Cewch chi ddefnyddio enwau lliwiau (yn Saesneg: red, blue, yellow, ayyb.) neu'r rhif hecsadegol, e.e. #FFFFFF, #000000. Er enghraifft, i greu testun coch cewch chi ddefnyddio:<br /><br /><b>[color=red]</b>Helo!<b>[/color]</b><br /><br />neu<br /><br /><b>[color=#FF0000]</b>Helo!<b>[/color]</b><br /><br />bydd y canlyniad yn edrych fel hyn: <span style=\"color:red\">Helo!</span></li><li>Cewch chi newid maint y testun mewn ffordd debyg, gan ddefnyddio <b>[size=][/size]</b>. Mae'r tag hwn yn dibynnu ar y patrymlun dych chi'n defnyddio, ond y fformat cymeradwy yw rhif sy'n cynrychioli maint y testun mewn picselau, dechrau gyda 1 (sy'n rhy fach i'w weld) a mynd hyd at 29 (yn fawr iawn). Er enghraifft, bydd hyn:<br /><br /><b>[size=9]</b>BACH<b>[/size]</b><br /><br />yn edrych rhywbeth fel hyn: <span style=\"font-size:9px\">BACH</span><br /><br />a bydd hyn:<br /><br /><b>[size=24]</b>ANFERTH!<b>[/size]</b><br /><br />yn edrych fel: <span style=\"font-size:24px\">ANFERTH!</span></li></ul>");
$faq[] = array("Alla i gyfuno tagiau fformadu?", "Gallwch, wrth gwrs. Er enghraifft, cewch chi dynnu sylw rhywun gan sgwennu:<br /><br /><b>[size=18][color=red][b]</b>EDRYCHWCH ARNA I!<b>[/b][/color][/size]</b><br /><br />a fydd yn dangos <span style=\"color:red;font-size:18px\"><b>EDRYCHWCH ARNA I!</b></span><br /><br />Dydyn ni ddim yn awgrymu eich bod chi'n gor-ddefnyddio testun sy'n edrych fel hyn - mae'n gallu bod yn anodd i'w ddarllen. Cofiwch taw eich cyfrifoldeb chi yw e i wneud yn siwr bod bob tag wedi'i gau, ac yn y drefn iawn. Er enghraifft, dydy hyn ddim yn iawn:<br /><br /><b>[b][u]</b>Ddim yn dda o gwbl<b>[/b][/u]</b>");

$faq[] = array("--","Dyfynnu ac allbynnu testun lled penodol");
$faq[] = array("Dyfynnu testun mewn ymateb", "Mae dwy ffordd i ddyfynnu testun, naill ai gyda chyfeiriad (enw y person dych chi'n dyfynnu) neu heb gyfeiriad.<ul><li>Wrth ddefnyddio'r ffwythiant dyfynnu dylech chi sylwi y bydd y testun dyfynedig yn ymddangos yn ffenestr y neges tu fewn i dagiau <b>[quote=\"\"][/quote]</b>. Ceir dyfynu gyda chyfeiriad at berson, neu beth bynnag dych chi eisiau rhoi yn y dyfynodau. Er enghraifft, i ddyfynnu darn o destun gan Jac y Jwc fyddech chi'n sgwennu:<br /><br /><b>[quote=\"Jac y Jwc\"]</b>Dyma geiriau Jac y Jwc<b>[/quote]</b><br /><br />Bydd enw Jac y Jwc yn ymddangos yn awtomatig uwchben ei eiriau. Cofiwch, mae <b>rhaid</b> i chi roi'r dyfynodau \"\" o gwmpas enw y person dych chi'n ei d/ddyfynnu, dydyn nhw ddim yn ddewisol.</li><li>Yr ail ffordd o ddyfynnu yw i roi geiriau heb gyfeiriad, gan lapio'r testun mewn tagiau <b>[quote][/quote]</b> heb roi enw arbennig. Bydd y neges gorffenedig yn dangos <b>Dyfyniad:</b> cyn y testun ei hun.</li></ul>");
$faq[] = array("Allbynnu cod neu ddata lled penodol", "I ddangos darn o god cyfrifiadurol neu unrhywbeth arall sydd angen ffont lled penodol, dylech lapio'r testun mewn tagiau <b>[code][/code]</b>, e.e.<br /><br /><b>[code]</b>echo \"Dyma linell o god cyfrifiadurol\";<b>[/code]</b><br /><br />Na fydd tagiau tu fewn y tagiau <b>code</b> yn cael eu gweithredu.");

$faq[] = array("--","Creu rhestrau");
$faq[] = array("Creu rhestr di-drefn", "Mae BBCode yn cefnogi dwy fath o restrau, wedi'i drefnu ac heb ei drefnu. Maen nhw'n mwy na lai'r un peth ‰ &lt;ol&gt; a &lt;ul&gt; mewn HTML. Mewn rhestr di-drefn, mae bob eitem yn eich rhestr yn ymddangos un ar ™l y llall, gyda pwynt bwled. Er mwyn creu rhestr di-drefn, defnyddiwch <b>[list][/list]</b> a rhoi <b>[*]</b> cyn bob eitem yn y rhestr. Er enghraifft, i wneud rhestr o'ch hoff liwiau, gallwch chi deipio:<br /><br /><b>[list]</b><br /><b>[*]</b>Coch<br /><b>[*]</b>Glas<br /><b>[*]</b>Piws<br /><b>[/list]</b><br /><br />A chewch chi fel canlyniad:<ul><li>Coch</li><li>Glas</li><li>Piws</li></ul>");
$faq[] = array("Creu rhestr mewn trefn", "Gyda'r ail fath o restr, cewchchi benodi beth sy'n dod o flaen bob eitem yn y rhestr. I wneud hyn, defnyddiwch <b>[list=1][/list]</b> am restr wedi'i rifo neu <b>[list=a][/list]</b> ar gyfer rhestr mewn trefn yr wyddor. Unwaith eto, mae bob eitem yn dechrau gyda <b>[*]</b>. Er enghraifft, bydd hyn:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Mynd i'r siop<br /><b>[*]</b>Prynu cyfrifiadur newydd<br /><b>[*]</b>Rhegu ar ben cyfrifiadur<br /><b>[/list]</b><br /><br />yn troi yn hyn:<ol type=\"1\"><li>Mynd i'r siop</li><li>Prynu cyfrifiadur newydd</li><li>Rhegu ar ben cyfrifiadur</li></ol>Gyda rhestr yn ™l trefn yr wyddor fyddech chi'n defnyddio:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Yr ateb cyntaf<br /><b>[*]</b>Yr ail ateb<br /><b>[*]</b>Y trydydd ateb<br /><b>[/list]</b><br /><br />giving<ol type=\"a\"><li>Yr ateb cyntaf</li><li>Yr ail ateb</li><li>Y trydydd ateb</li></ol>");

$faq[] = array("--", "Creu dolennau");
$faq[] = array("Cysylltu ‰ gwefannau eraill", "Mae BBCode phpBB yn caniatau sawl ffordd o greu <i>URLs</i> (<i>Universal Resource Location</i>, neu gyfeiriadau gwefannau i chi a fi).<ul><li>Yn gyntaf, cewch chi ddefnyddio'r tagiau <b>[url=][/url]</b>. Rho gyfeiriad gwe ar ™l yr arwydd <b>=</b> a bydd beth bynnag dych chi'n teipio rhwng y tagiau yn troi yn linc i'r wefan honno. Er enghraifft, i wneud cysylltiad ‰ gwefan phpBB.com gallwch chi deipio:<br /><br /><b>[url=http://www.phpbb.com/]</b>Ymwelwch ‰ phpBB!<b>[/url]</b><br /><br />Byddai hynny yn rhoi y ddolen ganlynol,  <a href=\"http://www.phpbb.com/\" target=\"_blank\">Ymwelwch ‰ phpBB!</a> Bydd y ddolen yn agor mewn ffenestr newydd a bydd y defnyddiwr yn gallu pori seiadau'r fforwm.</li><li>Os dych chi eisiau gweld y cyfeiriad ei hunan fel linc, gallwch chi wneud hyn:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />a chewch chi <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a> fel canlyniad.</li><li>Hefyd, mae gan phpBB rhywbeth o'r enw <i>Dolennau Hudol</i>, a fydd yn troi unrhyw cyfeiriad gwe sy'n wneud synwyr i fewn i ddolen, heb angen teipio unrhyw tagiau, neu hyd yn oed y http:// blaenorol. Er enghraifft, petasech chi'n teipio www.phpbb.com mewn neges, ceith dolen i <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> ei chreu'n awtomatig.</li><li>Mae'r un peth yn digwydd gyda chyfeiriadau ebyst, gallwch chi naill ai deipio cyfeiriad penodol, fel hyn:<br /><br /><b>[email]</b>neb@unman.adr<b>[/email]</b><br /><br />a fydd yn troi i  <a href=\"mailto:neb@unman.adr\">neb@unman.adr</a> neu allwch chi deipio dim ond y cyfeiriad neb@unman.adr yn eich neges - ceith e ei drosglwyddo i ddolen ebost yn awtomatig.</li></ul>Fel gyda bob tag BBCode arall, cewch chi lapio URLs o gwmpas unrhyw tagiau eraill, fel <b>[img][/img]</b> er enghraifft (gweler cofnod nesaf), <b>[b][/b]</b>, ayyb. Fel gyda'r tagiau eraill, mae eich cyfrifoldeb chi yw e i wneud yn siwr bod y tagiau yn cael eu cael ac hynny yn y drefn iawn. Nad yw hwn yn iawn, er enghraifft:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />ac mae'n bosibl y byddai post fel hyn yn cael ei ddileu.");

$faq[] = array("--", "Dangos lluniau mewn negeseuon");
$faq[] = array("Ychwanegu llun i neges", "Mae gan BBCode phpBB tag ar gyfer cynnwys lluniau mewn negeseuon. Dau beth pwysig i'w cofio wrth ddefnyddio'r tag hwn yw; dydy defnyddwyr ddim yn hoffi llawer o luniau mewn negeseuon ac yn ail bod rhaid i'r llun fodoli rhywle ar y we (nid jyst ar eich cyfrifiadur chi, oni bai bod <i>server</i> gyda chi!). Does dim modd ar hyn o bryd o gadw lluniau yn lleol gyda phpBB (ond dyn ni'n disgwyl y bydd fersiynau newydd yn gallu gwneud hyn). Er mwyn dangos llun, mae rhaid lapio'r URL sy'n pwyntio at y llun gyda tagiau <b>[img][/img]</b> . Er enghraifft:<br /><br /><b>[img]</b>http://www.phpbb.com/images/mainlogo.gif<b>[/img]</b><br /><br />Fel dwedon ni yn adran URLiau, uchod, gallwch chi lapio llun mewn tag <b>[url][/url]</b> os wyt ti eisiau, e.e. byddai<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/mainlogo.gif<b>[/img][/url]</b><br /><br />yn creu:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/mainlogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Pethau eraill");
$faq[] = array("Gaf i ychwanegu fy nhagiau fy hun?", "Na chewch, ddim ar hyn o bryd. Mae'n bosibl y bydd tagiau wedi'u haddasu ar gael yn y fersiwn nesaf o phpBB.");

//
// This ends the BBCode guide entries
//

?>