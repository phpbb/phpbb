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
 *                            Traditional Chinese[繁體中文語系] Translation
 *                              ------------------- 
 *     begin                : Thu Nov 26 2001 
 *     by                   : 小竹子, OOHOO, 皇家騎士, 思 
 *     email                : kyo.yoshika@msa.hinet.net
 *                            mchiang@bigpond.net.au
 *                            sjwu1@ms12.hinet.net
 *                            f8806077@mail.dyu.edu.tw
 *                             
 *     last modify          : Sun Dec 30 2001
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
  
$faq[] = array("--","介紹");
$faq[] = array("什麼是 BBCode 代碼?", "BBCode 代碼是一種整合 HTML 的特別語法, 您可不可以使用 BBCode 代碼取決於系統管理員的開放與否, 另外您也可以在每個版面的發表中取消這個功能. BBCode代碼的型式類似HTML語法, 但是標籤是用 [ 及 ] 含括著而不需要使用 &lt; 及 &gt;, 而且提供了較佳的操作性方便使用者控制版面的編排. 您可以在文章發表的表格上方發現一系列便捷的 BBCode 代碼按鈕 (置放位置會依不同的佈景樣式而有所不同). 以下還有更多更詳細的介紹.");

$faq[] = array("--","文字格式");
$faq[] = array("如何使用粗體, 斜體及加底線的文字?", "BBCode 代碼包含一些標籤方便您快速的更改文字的基本形式. 這些可以分述如下: <ul><li>要製作一份粗體文字可使用 <b>[b][/b]</b>, 例如: <br /><br /><b>[b]</b>哈囉<b>[/b]</b><br /><br />會變成<b>哈囉</b><br /><br /></li><li>要使用底線時, 可使用<b>[u][/u]</b>, 例如:<br /><br /><b>[u]</b>早安<b>[/u]</b><br /><br />會變成<u>早安</u><br /><br /></li><li>要斜體顯示時, 可使用 <b>[i][/i]</b>, 例如:<br /><br />這個真是 <b>[i]</b>棒呆了!<b>[/i]</b><br /><br />將會變成 這個真是 <i>棒呆了!</i></li></ul>");
$faq[] = array("如何修改文字的顏色以及大小?", "要在您的文章中修改文字顏色及大小需要使用以下的標籤. 請注意, 顯示的效果視您的瀏覽器和系統而定: <ul><li>更改文字色彩時, 可使用 <b>[color=][/color]</b>. 您可以指定一個可被辨識的顏色名稱(例如. red, blue, yellow, 等等.) 或是使用顏色編碼, 例如: #FFFFFF, #000000. 舉例來說, 要製作一份紅色文字您必須使用:<br /><br /><b>[color=red]</b>哈囉!<b>[/color]</b><br /><br />或是<br /><br /><b>[color=#FF0000]</b>哈囉!<b>[/color]</b><br /><br />都將顯示:<span style=\"color:red\">哈囉!</span><br /><br /></li><li>改變文字的大小也是使用類似的設定, 標籤為 <b>[size=][/size]</b>. 這個標籤的功能除了推薦使用數值形式以像素來顯示您的文字大字外, 其餘的視您使用的樣式而定, 起始值為 1 (但是可能會小到您無法看見) 到 29 為止 (巨大). 舉例說明:<br /><br /><b>[size=9]</b>小不拉嘰<b>[/size]</b><br /><br />將會產生 <span style=\"font-size:9px\">小不拉嘰</span><br /><br />當情形改變時:<br /><br /><b>[size=24]</b>有夠大顆!<b>[/size]</b><br /><br />將會顯示 <span style=\"font-size:24px\">有夠大顆!</span></li></ul>");
$faq[] = array("我可以結合不同的標籤功能嗎?", "當然可以, 例如要吸引大家的注意時, 您可以使用:<br /><br /><b>[size=18][color=red][b]</b>看我這兒!<b>[/b][/color][/size]</b><br /><br /> 將會顯示出 <span style=\"color:red;font-size:18px\"><b>看我這兒!</b></span><br /><br />我們並不建議您顯示太多這類的文字! 但是這些還是由您自行決定. 在使用 BBCode 代碼時, 請記得要正確的關閉標籤, 以下就是錯誤的使用方式:<br /><br /><b>[b][u]</b>這是錯誤的示範<b>[/b][/u]</b>");

$faq[] = array("--","引言, 顯示程式代碼或固定寬度的文字");
$faq[] = array("回覆時引用文字", "有兩種方式可讓您引用文章內容, 顯示引用來源及直接引用.<ul><li>當您在討論版面使用引言回覆時, 您會注意到文章內容已被加入回覆內容視窗內  <b>[quote=\"\"][/quote]</b> 的區段. 這個方法允許您引用某位發表者的文章內容並顯示來源! 例如要引用小竹子的文章內容時, 您必須輸入:<br /><br /><b>[quote=\"小竹子\"]</b>小竹子的文章內容放置在這裏<b>[/quote]</b><br /><br />這將會在顯示時, 自動加上: <b>小竹子 寫到:</b> 實際的內容. 請記得您<b>必須</b>在\"\" 裡指定引用者的名稱.<br /><br /></li><li>第二種方法允許您直接引用. 要使用這個標籤時, 您必須使用 <b>[quote][/quote]</b> 標籤. 而這種使用方式將會只會顯示簡單的引用功能, 例如: <b>引用回覆: </b>您所指定的文章內容.</li></ul>");
$faq[] = array("顯示程式代碼或固定寬度的文字", "如果您想要顯示一段程式代碼或是任何需要固定寬度的文字, 您必須使用 <b>[code][/code]</b> 標籤來包含這些文字, 例如:<br /><br /><b>[code]</b>echo \"這是代碼\";<b>[/code]</b><br /><br />當您瀏覽時, 所有被 <b>[code][/code]</b> 標籤包含的文字格式都將保持不變.");

$faq[] = array("--","製作列表");
$faq[] = array("製作沒有排序的列表", "BBCode 代碼支援兩種列表模式, 有排序的和無排序的. 無排序的列表以符號且有條列的顯示每個項目, 您需使用 <b>[list][/list]</b> 並且使用 <b>[*]</b> 來定義每一個項目. 例如要條列出您最喜歡的顏色時, 您可以使用:<br /><br /><b>[list]</b><br /><b>[*]</b>紅色<br /><b>[*]</b>藍色<br /><b>[*]</b>黃色<br /><b>[/list]</b><br /><br />這將產生以下列表:<ul><li>紅色</li><li>藍色</li><li>黃色</li></ul>");
$faq[] = array("製作依序排列的列表", "第二種列表模式, 有排序的列表讓您控制每個項目顯示的順序, 您需使用 <b>[list=1][/list]</b> 來製作以數字排序的列表, 或是以 <b>[list=a][/list]</b> 來製入以字母排序的列表. 如同無排序列表的使用方式一般, 我們以 <b>[*]</b>來指定排序的條件. 例如:<br /><br /><b>[list=1]</b><br /><b>[*]</b>到商店去<br /><b>[*]</b>買一台新的電腦<br /><b>[*]</b>當電腦爛掉時大罵一頓<br /><b>[/list]</b><br /><br />將會產生以下列表:<ol type=\"1\"><li>到商店去</li><li>買一台新的電腦</li><li>當電腦爛掉時大罵一頓</li></ol>如果要使用字母排列的話, 您必須使用:<br /><br /><b>[list=a]</b><br /><b>[*]</b>第一個可能的答案<br /><b>[*]</b>第二個可能的答案<br /><b>[*]</b>第三個可能的答案<br /><b>[/list]</b><br /><br />將會產生<ol type=\"a\"><li>第一個可能的答案</li><li>第二個可能的答案</li><li>第三個可能的答案</li></ol>");

$faq[] = array("--", "建立連結");
$faq[] = array("連結到其它網站", "phpBB BBCode 代碼支援數種產生網址的方式, 一般來說, 最常用的就是 URLs 功能.<ul><li>使用這個方法必須先使用 <b>[url=][/url]</b> 標籤, 在等號 ( = ) 之後, 無論您輸入任何資料, 皆會使得此一標籤連結到您指定的 URL. 舉例說明, 要連結 phpBB.com 時, 您可以使用:<br /><br /><b>[url=http://www.phpbb.com/]</b>參觀 phpBB!<b>[/url]</b><br /><br />這會產生以下連結, <a href=\"http://www.phpbb.com/\" target=\"_blank\">參觀 phpBB!</a> 您必須注意的是, 點選連結將開啟一個新的視窗, 這是為了方便瀏覽者能繼續瀏覽版面內容而設的.<br /><br /></li><li>如果您想要 URL 自行顯示成連結, 您可以使用簡單的設定:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />這將會產生以下連結, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a><br /><br /></li><li>在附加的 phpBB 功能中, 有一個<b>神奇連結</b>的功能, 這個功能將轉換所有正確的 URL 句型成為連結, 您無需指定任何標籤也不需要在句首加上 http://. 例如您在文章中輸入 www.phpbb.com, 當您瀏覽時, 將自動轉換成 <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> 顯示.<br /><br /></li><li>這個功能也支援電子郵件位址, 您可以指定一個特定位址, 例如:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />將會顯示為 <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> 或是您只要輸入 no.one@domain.adr 系統會自動轉換為預設的電子郵件位址.<br /><br /></li></ul>當您使用 BBCode  URLs 的標籤時也可以加入其它標籤功能, 如 <b>[img][/img]</b> (可參考下一個說明), <b>[b][/b]</b>...等等, 您可以搭配使用任何的標籤, 但切記需正確的開啟及關閉標籤, 例如:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />就是個不正確的語法, 不當的使用將導致您的文章被刪除, 所以請小心使用.");

$faq[] = array("--", "在文章中插入圖片");
$faq[] = array("在文章中插入圖片", "phpBB BBCode 代碼提供標籤在您的文章中顯示圖像. 使用前, 請記住兩件重要的事;  第一, 許多使用者並不樂於見到文章中有太多的圖片, 第二, 您的圖片必須是能在網路上顯示的 (例如: 不能是您電腦上的檔案 (除非您的電腦是台網路伺服器). phpBB 目前沒有提供儲存圖片的功能  (在下一版的 phpBB 或許會加入此項功能). 目前, 若要顯示圖像, 您必須使用 <b>[img][/img]</b> 標籤並指定圖像連結網址,  例如:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />如同在先前網址連結的說明一樣, 您也可以使用圖片網址超連結 <b>[url][/url]</b> 的標籤, 例如:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />將產生:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "其它功能");
$faq[] = array("我可以加入自行定義的標籤嗎?", "目前 phpBB 2.0 中並沒有這項功能,  不過我們希望可以在下一個官方版本中加入這項功能.");

//
// This ends the BBCode guide entries
//

?>