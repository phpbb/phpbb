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
  
$faq[] = array("--","Introduction");
$faq[] = array("What is BBCode?", "BBCode is a special implementation of HTML, whether you can use BBCode is determined by the administrator (you can also disable it on a per post basis from the posting form). BBCode itself is similar in style to HTML, tags are enclosed in square braces [ and ] rather than &lt; and &gt; and it offers greater control over what and how something is displayed. Depending on the template you are using you may find adding BBCode to your posts is made much easier through a clickable interface above the message area on the posting form. Even with this you may find the following guide useful.");

$faq[] = array("--","Text Formatting");
$faq[] = array("How to create bold, italic and underlined text", "BBCode includes tags to enable you to change the basic look of your text. This is achieved in the following ways: <ul><li>To make a piece of text bold enclose it in <b>[b][/b]</b>, eg. <br /><br /><b>[b]</b>Hello<b>[/b]</b><br /><br />will become <b>Hello</b></li><li>For underlining use <b>[u][/u]</b>, for example:<br /><br /><b>[u]</b>Good Morning<b>[/u]</b><br /><br />becomes <u>Good Morning</u></li><li>To italicise text use <b>[i][/i]</b>, eg.<br /><br />This is <b>[i]</b>Great!<b>[/i]</b><br /><br />would give This is <i>Great!</i></li></ul>");
$faq[] = array("How to change the text colour or size", "To alter the text colour or size the following tags are used, keep in mind that how the output appears will depend on the persons browser and system: <ul><li>Changing the colour of text is achieved by wrapping it in <b>[color=][/color]</b>, you can specify either a recognised colour name (eg. red, blue, yellow, etc.) or the hexadecimal triplet alternative, eg. #FFFFFF, #000000. For example, to create red text you could use:<br /><br /><b>[color=red]</b>Hello!<b>[/color]</b><br /><br />or<br /><br /><b>[color=#FF0000]</b>Hello!<b>[/color]</b><br /><br />will both output <span style=\"color:red\">Hello!</span></li><li>Changing the text size is achieved in a similar way using <b>[size=][/size]</b>. This tag is dependent on the template you are using but the recommended format is a numerical value representing the text size in pixels, starting at 1 (so tiny you will not see it!) through to 29 (very large!). For example:<br /><br /><b>[size=9]</b>SMALL<b>[/size]</b><br /><br />will generally be <span style=\"font-size:9px\">SMALL</span><br /><br />whereas:<br /><br /><b>[size=24]</b>HUGE!<b>[/size]</b><br /><br />will be <span style=\"font-size:24px\">HUGE!</span></li></ul>");
$faq[] = array("Can I combine formatting tags?", "Yes, of course you can, for example to get someones attention you may write:<br /><br /><b>[size=18][color=red][b]</b>LOOK AT ME!<b>[/b][/color][/size]</b><br /><br />this would output <span style=\"color:red;font-size:18px\"><b>LOOK AT ME!</b></span><br /><br />We don't recommend you output lots of text that looks like this though! Remember it is up to you, the poster to ensure tags are closed correctly. For example the following is incorrect:<br /><br /><b>[b][u]</b>This is wrong<b>[/b][/u]</b>");

$faq[] = array("--","Quoting and outputting fixed-width text");
$faq[] = array("Quoting text in replies", "There are two ways you can quote someone elses text, with a reference or without. When you hit use the quote function of the board you should notice that the post you are responding too is automatically added to the text window enclosed in a <b>[quote=\"\"][/quote]</b> block. This method allows you to quote with a reference to a person, thing, whatever. For example to quote a piece of text Mr. Blobby wrote you would enter:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>The text Mr. Blobby wrote would go here<b>[/quote]</b><br /><br />The resulting output will automatically add, Mr. Blobby wrote: before the actual text. Remember to include the parenthesis \"\" around the name you are quoting! The second method allows you to blindly quote something, in this case just enclose the text in <b>[quote][/quote]</b>. The output generated will simply add, Quote: before the text itself.");
$faq[] = array("Outputting code or fixed width data", "If you want to output a piece of code or in fact anything that requires a  fixed width, eg. Courier type font then you should enclose the text in <b>[code][/code]</b>, eg.<br /><br /><b>[code]</b>echo \"This is some code\";<b>[/code]</b><br /><br /> All the formatting used within those tags will be retained.");

$faq[] = array("--","Generating lists");
$faq[] = array("Creating an Un-ordered list", "BBCode supports two types of lists, unordered and ordered, they are essentially the same as their HTML equivalents. An unordered list just ouputs your text one by one with only a bullet character to separate them. To create an unordered list you use <b>[list][/list]</b> and define each item within the list using <b>[*]</b>. For example to list your favorite colours you could use something like:<br /><br /><b>[list]</b><br /><b>[*]</b>Red<br /><b>[*]</b>Blue<br /><b>[*]</b>Yellow<br /><b>[/list]</b><br /><br />This would generate the following list:<ul><li>Red</li><li>Blue</li><li>Yellow</li></ul>");
$faq[] = array("Creating an Ordered list", "The second type of list, an ordered list gives you control over what is output before each item. To create an ordered list you use <b>[list=1][/list]</b> or alternatively <b>[list=a][/list]</b> items are still specified using <b>[*]</b>. The first list will output a numbered list while the second will use letters of the alphabet. For example:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Go to the shops<br /><b>[*]</b>Buy a new computer<br /><b>[*]</b>Swear at computer when it crashes<br /><b>[/list]</b><br /><br />will generate the following:<ol type=\"1\"><li>Go to the shops</li><li>Buy a new computer</li><li>Swear at computer when it crashes</li></ol>Whereas for an alphabetical list you would use:<br /><br /><b>[list=a]</b><br /><b>[*]</b>The first possible answer<br /><b>[*]</b>The second possible answer<br /><b>[*]</b>The third possible answer<br /><b>[/list]</b><br /><br />giving<ol type=\"a\"><li>The first possible answer</li><li>The second possible answer</li><li>The third possible answer</li></ol>");

$faq[] = array("--", "Creating Links");
$faq[] = array("Linking to another site", "phpBB BBCode supports a number of ways of creating URIs, Uniform Resource Indicators better known as URLs. The first of these uses the <b>[url=][/url]</b> tag, whatever you type after the = sign will cause the contents of that tag to act as a URL. For example to link to phpBB.com you could use:<br /><br /><b>[url=http://www.phpbb.com/]</b>Visit phpBB!<b>[/url]</b><br /><br />This would generate the following link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Visit phpBB!</a> You will notice the link opens in a new window so the user can continue browsing the forums if they wish. If you want the URL itself displayed as the link you can do this simply using:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />This would generate the following link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a>. Alternatively phpBB features something called <i>Magic Links</i>, this will turn any syntatically correct URL into a link without you needing to specify any tags or even the leading http://. For example typing www.phpbb.com into your message will automatically lead to <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> being output when you view the message. The same thing applies equally to email addresses, you can either specify an address explicitly for example:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />which will output <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> or you can just type no.one@domain.adr into your message and it will be automatically converted when you view. In addition you can wrap URLs around any of the other tags such as <b>[img][/img]</b> (see next entry), <b>[b][/b]</b>, etc. As with the formatting tags it is up to you to ensure the correct open and close order is following, for example:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />is <u>not</u> correct which may lead to your post being deleted so take care.");

$faq[] = array("--", "Showing images in posts");
$faq[] = array("Adding an image to a post", "phpBB BBCode includes a tag for including images in your posts. Two very important things to remember when using this tag are, many users do not appreciate lots of images being shown in posts and secondly the image you display must already be available on the internet (it cannot only exist on your computer for example, unless you run a webserver!) there is no present way of storing images locally with phpBB (both of these issues will be addressed in the next release of phpBB). To display an image you must surround the URL pointing to the image with <b>[img][/img]</b> tags. For example:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />As noted in the URL section above you can wrap an image in a <b>[url][/url]</b> tag if you wish, eg.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />would generate:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Other matters");
$faq[] = array("Can I add my own tags?", "No, I am afraid not directly in phpBB 2.0. We are looking at offering customisable BBCode tags for the next major version");

//
// This ends the BBCode guide entries
//

?>