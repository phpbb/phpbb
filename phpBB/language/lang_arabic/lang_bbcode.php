<?php
/***************************************************************************
 *                         lang_bbcode.php [english]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_bbcode.php,v 1.3 2001/12/18 01:53:26 psotfx Exp $
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

/* CONTRIBUTORS
	2003-1-25	Waheed Al-Sayer (wfa@paaet.edu.kw)
		Fixed many minor grammatical problems.
*/
 
// 
// To add an entry to your BBCode guide simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your BBCode guide entries, if you absolutely must then escape them ie. \"something\";
//
// The BBCode guide items will appear on the BBCode guide page in the same order they are listed in this file
//
// If just translating this file please do not alter the actual HTML unless absolutely necessary, thanks :)
//
// In addition please do not translate the colours referenced in relation to BBCode any section, if you do
// users browsing in your language may be confused to find they're BBCode doesn't work :D You can change
// references which are 'in-line' within the text though.
//
  
$faq[] = array("--","гёѕг№№№…");
$faq[] = array("г« еж BBCode?", "BBCode еж ћ“Ѕ гд  Ў»нё HTML. ”ж«Ѕ «д яд   ” ЎнЏ √ж б« ” ЎнЏ «” Џг«б BBCode Ён гж«÷нЏ нЏ гѕ Џбм «бг”ƒжб Џд «бгд ѕм. »«б«÷«Ё… б–бя  ” ЎнЏ  ЏЎнб BBCode бяб «—”«б Џбм Ќѕ…. BBCode г‘«»е б HTML, «б √‘н— нЌѕѕ »«ёж«” г—»Џ… [ ж ] жбн” &lt; ж &gt; гг« нЏЎн  Ќяг «Ё÷б бг« нЏ—÷. »д«Ѕ Џбм джЏ «бё«б» «бг” Џгб няжд «” Џг«б BBCode б—”«∆бя «”еб »«” Џг«б «б«“—… «Џбм г—»Џ «б—”«∆б Ён ’ЁЌ… «—”«б «б—”«б…. Ќ м гЏ е–е «б”ежб… ”жЁ  ћѕ «бѕбнб «б «бн гЁнѕ«.");

$faq[] = array("--"," д”нё «бд’");
$faq[] = array("янЁ  д”ё «бќЎжЎ «б”гня… ж«бг”Ў—… ж«бг«∆б…", "BBCode   ÷гд «‘«—«  б д”нё «бд’. н Ќёё –бя »«бЎ—ё «б «бн…: <ul><li>бћЏб ћ“Ѕ гд «бд’ ”гня ÷Џе »нд «бЏб«г… <b>[b][/b]</b>, гЋб« <br /><br /><b>[b]</b>√еб«<b>[/b]</b><br /><br />”жЁ  ’»Ќ <b>√еб«</b></li><li>ббд’ «бг”Ў— <b>[u][/u]</b>, гЋб«:<br /><br /><b>[u]</b>’»«Ќ «бќн—<b>[/u]</b><br /><br /> ’»Ќ <u>’»«Ќ «бќн—</u></li><li>бћЏб «бќЎ г«∆б <b>[i][/i]</b>, гЋб«<br /><br />е–« <b>[i]</b>Џўнг!<b>[/i]</b><br /><br />”жЁ нЏЎня е–« <i>Џўнг!</i></li></ul>");
$faq[] = array("янЁ  џн— бжд жЌћг «бќЎ", "б џн— бжд жЌћг «бќЎ ггяд «” Џг«б «б «бн. б«  д”м √д ‘яб жбжд «бд’ж’ нЏ гѕ Џбм »—д«гћ «бг ’ЁЌ жће«“е: <ul><li>б џнн— «ббжд ÷Џ «бд’ »нд «б«‘«—«  <b>[color=][/color]</b>.  ” ЎнЏ «” Џг«б √н гд «б«бж«д «бг Џ«—Ё Џбне« (eg. red, blue, yellow, etc.) √ж Ў—нё… «б«—ё«г «бЋб«Ћ… «б”  Џ‘—н…, eg. #FFFFFF, #000000. гЋб«, бћЏб бжд «бд’ √Ќг—  ” ЎнЏ «” Џг«б:<br /><br /><b>[color=red]</b>√еб«!<b>[/color]</b><br /><br />√ж<br /><br /><b>[color=#FF0000]</b>√еб«!<b>[/color]</b><br /><br />ћгнЏег ”жЁ нўе—жд <span style=\"color:red\">√еб«!</span></li><li>б џнн— Ќћг «бќЎ д” ЎнЏ »Ў—нё… гг«Ћб… «” Џг«б <b>[size=][/size]</b>. е–е «б«‘«—… ”жЁ  Џ гѕ Џбм «бё«б» «бг” Џгб бяд «бЎ—нё… «бгЁ÷б… ен «” Џг«б дў«г «бдёЎ Ён Ќћг «бд’, «» ѕ«Ѕ гд 1 (’џн— «бм «ё’м ѕ—ћ…) «бм «б—ёг 29 (я»н— ћѕ«). гЋб«:<br /><br /><b>[size=9]</b>’џн—<b>[/size]</b><br /><br />”жЁ  яжд  <span style=\"font-size:9px\">’џн—</span><br /><br />ЌнЋ:<br /><br /><b>[size=24]</b>÷ќг!<b>[/size]</b><br /><br />” яжд <span style=\"font-size:24px\">÷ќг!</span></li></ul>");
$faq[] = array("еб «” ЎнЏ «д «г“ћ «‘«—«  «б д”нё?", "дЏг, »«бЎ»Џ  ” ЎнЏ –бя, гЋб« б бЁ  «д »«е ‘ќ’ г«  ” ЎнЏ «” Џг«б:<br /><br /><b>[size=18][color=red][b]</b>«дў— «бн!<b>[/b][/color][/size]</b><br /><br />е–« ”жЁ нўе— <span style=\"color:red;font-size:18px\"><b>«дў— «бн!</b></span><br /><br />бядд« б« дд’Ќ «ўе«— «бяЋн— гд  бя «бд’ж’!  –я— «б«г— г—ћжЏ «бня, «бд«‘—° бб √яѕ гд «џб«ё «б«‘«—«  »Ў—нё… ’ЌнЌ…. гЋб« «б «бн џн— ’ЌнЌ:<br /><br /><b>[b][u]</b>е–« ќЎ√<b>[/b][/u]</b>");

$faq[] = array("--","«ё »«” «бд’ж’ ж«бя «»… »Ќ—жЁ Ћ«» …");
$faq[] = array("«ё »«” «бд’ж’", "ед«я Ў—нё «д бб«ё »«”, гЏ г—ћЏн… «бм √ж »ѕжд г—ћЏн….<ul><li>Џдѕ «” ќѕ«г «б«ё »«” бб—ѕ Џбм гж÷жЏ Џбня «б –я— √д «бд’ «бгё »” ”жЁ нж÷Џ Ён д’ —”«б я »нд «б«‘«— нд <b>[quote=\"\"][/quote]</b>. е–е «бЎ—нё…  гядя гд «б«‘«—… «бм «б‘ќ’ «бгё »” где √ж √н ‘нЅ ¬ќ—! гЋб« бб«ё »«” ћ“Ѕ гд д’ я »е «б”нѕ »бж»н д” Џгб:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>«бд’ «б–н я »е «б”нѕ »бж»н<b>[/quote]</b><br /><br />«бд нћ… ”жЁ  ÷Џ, Mr. Blobby я »: ё»б «бд’ г»«‘—….  –я— «дя <b>нћ»</b>   ÷гд «б«ёж«” \"\" Ќжб «бд’ «бгё »”, жнћ» «” Џг«бе«.</li><li>«бЎ—нё… «бЋ«дн… ен «б«ё »«” »Ў—нё… Џгн«Ѕ. б ЁЏб –бя ÷Џ «бд’ »нд «б«‘«— нд <b>[quote][/quote]</b>. Џдѕ «” Џ—«÷ «б—”«б… ”жЁ  —м «бд’ «бгё »” , «ё »«”: ё»б «бд’ г»«‘—….</li></ul>");
$faq[] = array("«ўе«— бџ… »—д«гћ √ж д’ »Ќ—жЁ Ћ«» … «бён«”", "≈–« «—ѕ  «ўе«— д’ б»—д«гћ √ж √н д’ »Ќ—жЁ Ћ«» … «бён«”, гЋб«. Courier Џбня ж÷Џ «бд’ »нд «б«‘«— нд <b>[code][/code]</b>, гЋб«.<br /><br /><b>[code]</b>echo \"е–« д’ »—д«гћ\";<b>[/code]</b><br /><br />яб «б д”нё «бг” ќѕг  <b>[code][/code]</b> ”жЁ н»ём бЌнд «” Џ—«÷е г—… «ќ—м.");

$faq[] = array("--"," яжнд ёж«∆г");
$faq[] = array(" яжнд ёж«∆г дёЎн…", "BBCode нѕЏг джЏнд гд «бёж«∆г, дёЎн… жЏѕѕн…. ег« г ‘«»енд бЎ—нё…   HTML. «бёж«∆г «бдёЎн…  ўе— «бдё«Ў яб Џбм ”Ў— жѕ«ќб «б”Ў— гЏ —г“ дёЎ… ё»б «б”Ў—.  б яжнд ё«∆г… дёЎн… д” Џгб <b>[list][/list]</b> ж÷Џ «бЏб«г… <b>[*]</b>ё»б яб ”Ў—. гЋб« «бд’ «б «бн «б«бж«д «бгЁ÷б «” Џг«бе«:<br /><br /><b>[list]</b><br /><b>[*]</b>«Ќг—<br /><b>[*]</b>«“—ё<br /><b>[*]</b>«’Ё—<br /><b>[/list]</b><br /><br />е–« ”жЁ нўе— «б «бн:<ul><li>«Ќг—</li><li>«“—ё</li><li>«’Ё—</li></ul>");
$faq[] = array(" яжнд ёж«∆г Џѕѕн…", "«бджЏ «бЋ«дн гд «бёж«∆г, ёж«∆г Џѕѕн…  ЏЎня  Ќяг Ќжб яб дёЎ…  ўе—. б яжнд ёж«∆г Џѕѕн… д” Џгб <b>[list=1][/list]</b> б яжнд ёж«∆г Ќ—Ён… д” Џгб <b>[list=a][/list]</b>. яг« еж «бЌ«б Ён «бё«∆г… «бдёЎн… д” Џгб <b>[*]</b>. гЋб«:<br /><br /><b>[list=1]</b><br /><b>[*]</b>«–е» «бм «б”жё<br /><b>[*]</b>«‘ — яг»нж —<br /><b>[*]</b>«’—ќ Џбм «бяг»нж — Ќнд нЏЎб<br /><b>[/list]</b><br /><br />”жЁ нўе— «б «бн:<ol type=\"1\"><li>«–е» «бм «б”жё</li><li>«‘ — яг»нж —</li><li>«’—ќ Џбм «бяг»нж — Ќнд нЏЎб</li></ol>жЁн Ќ«б… ё«∆г… Ќ—Ён… д” Џгб:<br /><br /><b>[list=a]</b><br /><b>[*]</b>«бћж«» «б√жб<br /><b>[*]</b>«бћж«» «бЋ«дн<br /><b>[*]</b>«бћж«» «бЋ«бЋ<br /><b>[/list]</b><br /><br />нЏЎн<ol type=\"a\"><li>«бћж«» «б√жб</li><li>«бћж«» «бЋ«дн</li><li>«бћж«» «бЋ«бЋ</li></ol>");

$faq[] = array("--", " яжнд ж’б« ");
$faq[] = array("ж’б… гЏ гжёЏ ¬ќ—", "Ў—нё… BBCode «бг” Џгб… Ён phpBB нѕЏг Џѕ… Ў—ё б«ўе«— «бж’б«  URIs, Uniform Resource Indicators «бгЏ—жЁ… » URLs.<ul><li>√жб е–е «бЎ—ё ен «” Џг«б «‘«—«  <b>[url=][/url]</b>, «н д’ ня » »Џѕ = ”жЁ н”»» «д нЏ«гб «бд’ яж’б… гжёЏ. гЋб« б ’б »«бгжёЏ phpBB.com  ” ЎнЏ «” Џг«б:<br /><br /><b>[url=http://www.phpbb.com/]</b>“ж—ж« «бгжёЏ phpBB!<b>[/url]</b><br /><br />е–« ”жЁ няжд «бж’б… «б «бн… , <a href=\"http://www.phpbb.com/\" target=\"_blank\">“ж—ж« «бгжёЏ phpBB!</a> ”жЁ  б«Ќў «д «б’ЁЌ… ”жЁ  ўе— Ён д«Ё–… ћѕнѕ… б гяд «б“«∆— гд гж«’б… «б ’ЁЌ Ён «бгд ѕм.</li><li>«–« яд   —нѕ «бж’б  ўе— я—»Ў ЁёЎ  ” ЎнЏ «” Џг«б:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />е–« ”жЁ нЏЎн «б—»Ў «б «бн, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>»«б«÷«Ё… б–бя Ё≈д phpBB  ” Џгб г« н”гм <i>ж’б«  ”Ќ—н…</i>, жен  Ќжб √н «”г бгжёЏ Џбм «б«д —д  я » »Ў—нё… ’ЌнЌ… «бм ж’б… ѕжд «” Џг«б √н гд «б«‘«—«  «б”«»ё… «б–я— √ж «” Џг«б «бябг… http://. гЋб« Џдѕг«  я » www.phpbb.com Ён —”«б я Ё«д е–« ”жЁ н Ќжб «бм  <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> Џдѕ «” Џ—«÷ —”«б я. </li><li>дЁ” «б‘нЅ ндЎ»ё Џбм Џд«жнд «б»—нѕ «б«бя —ждн…,  ” ЎнЏ «г« «” Џг«б «б»—нѕ «б«бя —ждн г»«‘—… гЋб:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />жеж ”нўе— <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> √ж  ” ЎнЏ ЁёЎ я «»… no.one@domain.adr Ён —”«б я жен ”  Ќжб «бм Џдѕ «” Џ—«÷е«.</li></ul>яг« еж «бЌ«б гЏ ћгнЏ «‘«—«  BBCode ж÷Џ Џдж«днд «б’ЁЌ«  Ён √н гд «б«‘«—«  «б”«»ё –я—е« гЋб ”гня г«∆б жџн—е« <b>[img][/img]</b> («дў— «бдёЎ… «б «бн…), <b>[b][/b]</b>. яг« еж «бЌ«б гЏ «б«‘«—«  «б”«»ё… Џбня «б √яѕ гд «” Џг«б «‘«—«  «бЁ Ќ ж«б«џб«ё «бг–яж—… ”«»ё« »Ў—нё… ’ЌнЌ…, гЋб«:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /><u>бн”</u> ’ЌнЌ« гг« ёѕ нѕЏж «бм «бџ«Ѕ —”«б я° б–« Џбм «б«д »«е.");

$faq[] = array("--", "«ўе«— «б’ж— Ён —”«б я");
$faq[] = array("«÷«Ё… ’ж—… бб—”«б…", "Ў—нё… phpBB BBCode  ” Џгб «‘«—… б«÷«Ё… «б’ж— Ён —”«∆бя. ед«я «г—«д гег«д Џдѕ «÷«Ё… «б’ж—; яЋн— гд «бг” ќѕгнд б« нЁ÷бжд ўеж— «бяЋн— гд «б’ж— Ён «б—”«∆б «б’ж—… «бгѕ—ћ… нћ» √д  яжд гжћжѕ… Џбм «б«д —д   (нЏдн нћ» «д б« яжд гжћжѕ… Џбм ће«“ «бЌ«”» бѕня ЁёЎ, ≈б« «–« я«д бѕня ќ«ѕг ’ЁЌ«  «б«д —д !).  б«  жћѕ Ў—нё… Ён «бжё  «бЌ«бн б ќ“нд «б’ж— Ён phpBB (ћгнЏ е–е «бЏё»«  ”жЁ  ѕ—” бб«’ѕ«— «бё«ѕг гд phpBB). б«ўе«— ’ж—… гд гжёЏ г« Џбня  ÷гнде Ён «б«‘«—… <b>[img][/img]</b>. гЋб«:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />яг« еж г–яж— Ён  яжнд «бж’б«  «Џб«е  ” ЎнЏ ж÷Џ «б’ж—… Ён «‘«—… <b>[url][/url]</b> «д «—ѕ  –бя, гЋб«<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />”жЁ нЏЎн:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "«гж— «ќ—м");
$faq[] = array("еб «” ЎнЏ ж÷Џ «‘«—«  ќ«’… »н?", "б«, «ќ‘м бн” г»«‘—… Ён phpBB 2.0. дЌд дѕ—” «гя«дн… ж÷Џ —гж“ ё«»б… бб џнн— BBCode Ён «б«’ѕ«— «б—∆н”н «бё«ѕг");

//
// This ends the BBCode guide entries
//

?>