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

//
// Chinese GB Simplified Translation by inker
// email : inker@byink.com    
// last modify : 2002/2/23                           
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
  
$faq[] = array("--","介绍");
$faq[] = array("什么是 BBCode 代码?", "BBCode 代码是一种 HTML 的特别语法, 您是否使用 BBCode 代码取决于管理员的开放与否, 另外您也可以在每个文章的发表版面中取消这个功能. BBCode代码的型式类似HTML语法, 可以使用 [ and ] 而不可以使用 &lt; 及 &gt;标签, 它提供了更好的操作方便性和控制面板的编排. 您可以在文章发表的表格上方发现 BBCode 代码的便捷按钮 (置放位置会依不同的布景样式而有所不同). 以下还有更详细的介绍.");

$faq[] = array("--","文字格式");
$faq[] = array("如何创建粗体, 斜体及加底线的文字?", "BBCode 代码提供一些文字标签方便您快速的更改文字的基本形式. 如下: <ul><li>粗体 <b>[b][/b]</b>, 如: <br /><br /><b>[b]</b>你好<b>[/b]</b><br /><br />会变成<b>你好</b><br /><br /></li><li>要使用底线时, 可用<b>[u][/u]</b>, 如:<br /><br /><b>[u]</b>你好<b>[/u]</b><br /><br />变成<u>你好</u><br /><br /></li><li>要斜体显示时, 可用 <b>[i][/i]</b>, 如:<br /><br />真是<b>[i]</b>太好了<b>[/i]</b><br /><br />将会变成 这个真是<i>太好了</i></li></ul>");
$faq[] = array("如何修改文字的颜色以及大小?", "在您的文章中修改文字颜色及大小，可以使用以下的标签. 请注意, 显示的效果视您的浏览器和系统而定: <ul><li>更改文字色彩时, 可使用 <b>[color=][/color]</b>. 您可以指定一个可被辨识的颜色名称(例如. red, blue, yellow, 等等.) 或是使用颜色编码, 例如: #FFFFFF, #000000. 举例来说, 要制作一份红色文字您必须使用:<br /><br /><b>[color=red]</b>你好<b>[/color]</b><br /><br />或是<br /><br /><b>[color=#FF0000]</b>你好!<b>[/color]</b><br /><br />都将显示:<span style=\"color:red\">哈罗!</span><br /><br /></li><li>改变文字的大小也是使用类似的设定, 语句为 <b>[size=][/size]</b>. 这个语句的功能除了使用数值形式以像素来显示您的文字大字外, 其它的根据您使用的样式而定, 起始值为 1 (但是可能会小到您无法看见) 到 29 为止 (超大). 举例说明:<br /><br /><b>[size=9]</b>小<b>[/size]</b><br /><br />将会产生 <span style=\"font-size:9px\">小</span><br /><br />当情形:<br /><br /><b>[size=24]</b><b>[/size]</b><br /><br />将会显示 <span style=\"font-size:24px\">大</span></li></ul>");
$faq[] = array("我可以结合使用不同的标签功能吗?", "当然可以, 例如要吸引大家的注意时, 您可以使用:<br /><br /><b>[size=18][color=red][b]</b>看我这儿!<b>[/b][/color][/size]</b><br /><br /> 将会显示出 <span style=\"color:red;font-size:18px\"><b>看我这儿!</b></span><br /><br />我们并不建议您显示太多这类的文字! 但是这些还是由您自行决定. 在使用 BBCode 代码时, 请尽量使用正确的标签, 以下就是错误的使用方式:<br /><br /><b>[b][u]</b>错误示范<b>[/b][/u]</b>");

$faq[] = array("--","引拥, 显示代码或固定宽度的文字");
$faq[] = array("回覆时引用文字", "有两种方式可让您引用文章内容, 显示引用来源及直接引用.<ul><li>当您在讨论版面使用引言回覆时, 您会注意到文章内容已被加入回复的内容视窗内  <b>[quote=\"\"][/quote]</b> 的区域. 许您引用某位发表者的文章内容并显示来源! 例如要引用123的文章内容时, 您必须输入:<br /><br /><b>[quote=\"123\"]</b>123的文章内容将放置在这<b>[/quote]</b><br /><br />这将会在显示时自动加上: <b>某某写到:(内容)</b>请记得您<b>必须</b>在\"\"这里指定引用者的名称前后加上\"和\"<br /><br /></li><li>第二种方法允许您直接引用. 要使用这个标签时, 您必须使用 <b>[quote][/quote]</b> 标签. 而这种使用方式将会只会显示简单的引用功能, 例如: <b>引用回覆: </b>您所指定的文章内容.</li></ul>");
$faq[] = array("显示程式代码或固定宽度的文字", "如果您想要显示一段程式代码或是任何要固定宽度的文字, 您必须使用 <b>[code][/code]</b> 标签来包含这些文字, 例如:<br /><br /><b>[code]</b>echo \"代码内容\";<b>[/code]</b><br /><br />当您浏览时, 所有被 <b>[code][/code]</b> 标签包含的文字格式都将保持不变.");

$faq[] = array("--","制作列表");
$faq[] = array("制作没有排序的列表", "BBCode 代码支持两种列表模式, 有排序的和无排序的. 无排序的列表以符号且有条列的显示每个项目, 您　使用 <b>[list][/list]</b> 并且使用 <b>[*]</b> 来定义每一个项目. 例如要条列出您最喜欢的颜色时, 您可以使用:<br /><br /><b>[list]</b><br /><b>[*]</b>红色<br /><b>[*]</b>蓝色<br /><b>[*]</b>黄色<br /><b>[/list]</b><br /><br />这将产生以下列表:<ul><li>红色</li><li>蓝色</li><li>黄色</li></ul>");
$faq[] = array("制作依序排列的列表", "第二种列表模式, 有排序的列表让您控制每个项目显示的顺序, 您　使用 <b>[list=1][/list]</b> 来制作以数字排序的列表, 或是以 <b>[list=a][/list]</b> 来制入以字母排序的列表. 如同无排序列表的使用方式一般, 我们以 <b>[*]</b>来指定排序的条件. 例如:<br /><br /><b>[list=1]</b><br /><b>[*]</b>到商店去<br /><b>[*]</b>买一台新的电脑<br /><b>[*]</b>当电脑挂掉时大骂一顿<br /><b>[/list]</b><br /><br />将会产生以下列表:<ol type=\"1\"><li>到商店去</li><li>买一台新的电脑</li><li>当电脑挂掉时大骂一顿</li></ol>如果要使用字母排列的话, 您必须使用:<br /><br /><b>[list=a]</b><br /><b>[*]</b>第一个可能的答案<br /><b>[*]</b>第二个可能的答案<br /><b>[*]</b>第三个可能的答案<br /><b>[/list]</b><br /><br />将会产生<ol type=\"a\"><li>第一个可能的答案</li><li>第二个可能的答案</li><li>第三个可能的答案</li></ol>");

$faq[] = array("--", "建立链接");
$faq[] = array("链接到其它网站", "phpBB BBCode 代码支持数方式的网址, 一般来说最常用的就是 URLs 功能.<ul><li>使用这个方法必须先使用 <b>[url=][/url]</b> 标签, 在等号 ( = ) 之后, 无论您输入任何资料, 皆会使得此一标签链接到您指定的 URL. 举例说明, 要链接 phpBB.com 时, 您可以使用:<br /><br /><b>[url=http://www.phpbb.com/]</b>浏览phpBB!<b>[/url]</b><br /><br />这会产生以下链接, <a href=\"http://www.phpbb.com/\" target=\"_blank\">浏览phpBB!</a> 您必须注意的是, 点选链接将开启一个新的视窗, 这是为了方便浏览者能继续浏览版面内容而设的.<br /><br /></li><li>如果您想要 URL 自行显示成链接, 您可以使用简单的设定:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />这将会产生以下链接, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a><br /><br /></li><li>在附加的 phpBB 功能中, 有一个<b>魔术链接</b>的功能, 这个功能将转换所有正确的 URL 句型成为链接, 您无需指定任何标签也不用在句首加上 http://. 例如您在文章中输入 www.phpbb.com, 当您浏览时, 将自动转换成 <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> 显示.<br /><br /></li><li>这个功能同样支持电子邮件位址, 您可以指定一个特定位址, 例如:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />将会显示为 <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> 或是您只要输入 no.one@domain.adr 系统会自动转换为预设的电子邮件位址.<br /><br /></li></ul>当您使用 BBCode URLs 的标签时也可以加入其它标签功能, 如 <b>[img][/img]</b> (可参考下一个说明), <b>[b][/b]</b>...等等, 您可以搭配使用任何的标签, 但请确定是否正确使用了标签, 例如:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />不正确的语法, 不当的使用将导致您的文章被删除, 请谨慎使用.");

$faq[] = array("--", "在文章中插入图片");
$faq[] = array("在文章中插入图片", "phpBB BBCode 代码提供标签在您的文章中显示图像. 使用前, 请记住两件重要的事;  第一, 许多使用者并不喜欢见到文章中有太多的图片, 第二, 您的图片必须是能在网路上显示的 (例如: 不能是您电脑上的文件 (除非您的电脑是台服务器). phpBB 目前没有提供储存图片的功能  (在下一版的 phpBB 或许会加入此项功能). 目前, 若要显示图像, 您必须使用 <b>[img][/img]</b> 标签并指定图像链接网址,  例如:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />如同在先前网址链接的说明一样, 您也可以使用图片网址超链接 <b>[url][/url]</b> 的标签, 例如:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />将产生:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "其它功能");
$faq[] = array("我可以加入自行定义的标签吗?", "目前 phpBB 2.0 中并没有这项功能,  不过我们希望可以在下一个官方版本中加入这项功能.");

//
// This ends the BBCode guide entries
//

?>