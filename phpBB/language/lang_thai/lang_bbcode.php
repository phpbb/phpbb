<?php
/***************************************************************************
 *                         lang_bbcode.php [Thai]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   Translation: Mr.LonelyWinter < mr_lonely_winter@yahoo.com > 
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
  
$faq[] = array("--","แนะนำการใช้งาน");
$faq[] = array("BBCode คืออะไร?", "BBCode คือส่วนหนึ่งของภาษา HTML. คุณสามารถใช้ BBCode ในข้อความที่คุณพิมพ์. และคุณสามารถยกเลิกการใช้ BBCode ในแต่ละข้อความได้ในแบบฟอร์มกรอกข้อความ. BBCode มีรูปแบบคล้ายๆกับภาษา HTML, คือจะมีเครื่องหมาย [ และ ] คลุมคำสั่งไว้ แทน &lt; และ &gt; ในภาษา HTML. แต่จะสามารถควบคุมการแสดงผลได้ดีกว่า. คุณสามารถเพิ่มคำสั่ง BBCode ให้กับข้อความของคุณโดยคลิกปุ่มคำสั่งด้านบนช่องกรอกข้อความ.");

$faq[] = array("--","การจัดรูปแบบของตัวอักษร");
$faq[] = array("จะสร้างตัวหนา, ตัวเอียง และขีดเส้นใต้ ได้อย่างไร", "BBCode จะช่วยสร้างรูปแบบให้กับตัวอักษร ดังนี้: <ul><li>ถ้าจะทำตัวหนา ให้พิมพ์ข้อความแทรกลงในในรหัส <b>[b][/b]</b>, เช่น: <br /><br /><b>[b]</b>สวัสดี<b>[/b]</b><br /><br />จะได้ผลเป็น <b>สวัสดี</b></li><li>ถ้าจะขีดเส้นใต้ ก็ใช้ <b>[u][/u]</b>, เช่น:<br /><br /><b>[u]</b>อรุณสวัสดิ์<b>[/u]</b><br /><br />จะได้เป็น <u>อรุณสวัสดิ์</u></li><li>ถ้าจะทำตัวเอียง ให้ใช้ <b>[i][/i]</b>, เช่น:<br /><br /><b>[i]</b>ตัวเอียง<b>[/i]</b><br /><br />จะได้ผลเป็น <i>ตัวเอียง</i></li></ul>");
$faq[] = array("จะเปลี่ยนสีหรือขนาดตัวอักษรอย่างไร", "การเปลี่ยนสีหรือขนาดของตัวอักษร จะขึ้นอยู่กับโปรแกรม browser <ul><li>ถ้าจะเปลี่ยนสีตัวอักษร ให้ใช้คำสั่ง <b>[color=][/color]</b>. ซึ่งคุณสามารถเปลี่ยนสีได้ (เช่น red, blue, yellow, ฯลฯ) หรือใช้เลขฐาน 6 ก็ได้, เช่น #FFFFFF, #000000. ยกตัวอย่างเช่น, จะสร้างตัวอักษรสีแดง:<br /><br /><b>[color=red]</b>สวัสดี!<b>[/color]</b><br /><br />หรือ<br /><br /><b>[color=#FF0000]</b>สวัสดี!<b>[/color]</b><br /><br />ทั้งคู่จะได้ผลเป็น <span style=\"color:red\">สวัสดี!</span></li><li>การเปลี่ยนขนาดตัวอักษรก็ทำได้โดยใช้คำสั่ง <b>[size=][/size]</b>. ขนาดของตัวอักษรจะเป็นหน่วย pixels, เริ่มต้นที่ 1 (ซึ่งเล็กมากจนมองไม่เห็น) จนถึง 29 (ใหญ่ที่สุด). ตัวอย่างเช่น:<br /><br /><b>[size=9]</b>ขนาดเล็ก<b>[/size]</b><br /><br />จะได้ผลเป็น <span style=\"font-size:9px\">ขนาดเล็ก</span><br /><br />และ<br /><br /><b>[size=24]</b>ขนาดใหญ่!<b>[/size]</b><br /><br />จะได้ผลเป็น <span style=\"font-size:24px\">ขนาดใหญ่!</span></li></ul>");
$faq[] = array("จะใช้คำสั่งร่วมกันได้หรือไม่?", "ใช้ได้, เช่นต้องการเน้นความสำคัญ คุณอาจใช้:<br /><br /><b>[size=18][color=red][b]</b>ประกาศ!<b>[/b][/color][/size]</b><br /><br />จะได้ผลเป็น <span style=\"color:red;font-size:18px\"><b>ประกาศ!</b></span><br /><br />แต่ขอแนะนำว่าอย่าใช้ลักษณะพิเศษมากจนเกินไป และจะต้องปิดคำสั่งให้เรียบร้อยด้วย. ตัวอย่างนี้เป็นตัวอย่างที่ผิด (เพราะปิดคำสั่งผิดลำดับ):<br /><br /><b>[b][u]</b>ตัวอย่างที่ผิด<b>[/b][/u]</b><br /><br /> ต้องแก้ไขเป็น:<br /><br /><b>[b][u]</b>ตัวอย่างที่ถูก<b>[/u][/b]</b>");

$faq[] = array("--","ข้อความอ้างอิง และตัวอักษรความกว้างคงที่");
$faq[] = array("ข้อความอ้างอิงในการตอบ", "มี 2 แบบ.<ul><li>แบบมีชื่อเจ้าของ. ข้อความอ้างอิงจะอยู่ภายในคำสั่ง <b>[quote=\"\"][/quote]</b>. เช่น คุณต้องการพิมพ์ข้อความอ้างอิงของ Mr. Blobby:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>ข้อความอ้างอิงของ Mr. Blobby<b>[/quote]</b><br /><br />เมื่อข้อความถูกแสดง จะมีคำว่า Mr. Blobby wrote: นำหน้า. จำไว้ว่าคุณ<b>ต้อง</b>ใส่ฟันหนู \"\" คลุมชื่อทุกครั้ง.</li><li>แบบไม่มีชื่อเจ้าของ. คือใส่แค่ <b>[quote][/quote]</b>. ก็จะมีแค่คำว่า Quote: นำหน้าข้อความอ้างอิง.</li></ul>");
$faq[] = array("ตัวอักษรความกว้างคงที่", "ถ้าคุณต้องการพิมพ์ข้อความที่ต้องการความกว้างที่แน่นอน เช่น แท็ปกีตาร์. คุณสามารถพิมพ์ข้อความนั้นลงในระหว่างคำสั่ง <b>[code][/code]</b> เช่น <br /><br /><b>[code]</b>echo \"This is some code\";<b>[/code]</b><br /><br />คำสั่งอื่นๆที่อยู่ภายใน <b>[code][/code]</b> (ถ้ามี) ก็จะยังใช้ได้ด้วย.");

$faq[] = array("--","วิธีสร้างรายการ");
$faq[] = array("การสร้างรายการที่ไม่มีหมายเลขลำดับ", "BBCode สามารถสร้างรายการได้ 2 แบบ คือ แบบไม่มีหมายเลขลำดับ และแบบมีหมายเลขลำดับ. เหมือนกับในภาษา HTML. แต่ละรายการจะมีเครื่องหมายวงกลมนำหน้า. ใช้คำสั่ง <b>[list][/list]</b> และนำหน้าแต่ละรายการด้วยคำสั่ง <b>[*]</b>. เช่นต้องการแสดงรายการสีที่ชอบ จะพิมพ์ดังนี้:<br /><br /><b>[list]</b><br /><b>[*]</b>สีแดง<br /><b>[*]</b>สีน้ำเงิน<br /><b>[*]</b>สีเหลือง<br /><b>[/list]</b><br /><br />จะได้ผลเป็น:<ul><li>สีแดง</li><li>สีน้ำเงิน</li><li>สีเหลือง</li></ul>");
$faq[] = array("การสร้างรายการที่มีหมายเลขลำดับ", "แบบที่สองคือ มีหมายเลขลำดับนำหน้า. ใช้คำสั่ง <b>[list=1][/list]</b> เพื่อเรียงลำดับตัวเลข, <b>[list=a][/list]</b> เพื่อเรียงตามลำดับตัวอักษร. ซึ่งมีคำสั่งนำหน้าแต่ละรายการคือ <b>[*]</b>. เช่น:<br /><br /><b>[list=1]</b><br /><b>[*]</b>ไปที่ร้าน<br /><b>[*]</b>ซื้อคอมพิวเตอร์<br /><b>[*]</b>ทำใจเมื่อคอมพิวเตอร์พัง<br /><b>[/list]</b><br /><br />จะได้ผลดังนี้:<ol type=\"1\"><li>ไปที่ร้าน</li><li>ซื้อคอมพิวเตอร์</li><li>ทำใจเมื่อคอมพิวเตอร์พัง</li></ol> ถ้าใช้แบบเรียงตามตัวอักษร เช่น:<br /><br /><b>[list=a]</b><br /><b>[*]</b>คำตอบแรก<br /><b>[*]</b>คำตอบที่สอง<br /><b>[*]</b>คำตอบที่สาม<br /><b>[/list]</b><br /><br />จะได้ผลเป็น<ol type=\"a\"><li>คำตอบแรก</li><li>คำตอบที่สอง</li><li>คำตอบที่สาม</li></ol>");

$faq[] = array("--", "การสร้างข้อความเชื่อมโยง (links)");
$faq[] = array("การเชื่อมโยงไปเว็บอื่นๆ", "BBCode สามารถทำข้อความเชื่อมโยงได้หลายวิธี, Uniform Resource Indicators หรือชื่อย่อคือ URLs.<ul><li>วิธีแรกคือใช้คำสั่ง <b>[url=][/url]</b>, ซึ่งข้อความหลังเครื่องหมายเท่ากับ = จะเป็นตำแหน่ง URL. ตัวอย่างเช่น:<br /><br /><b>[url=http://www.phpbb.com/]</b>คลิกที่นี่ เพื่อไปที่ phpBB<b>[/url]</b><br /><br />จะได้ผลดังนี้ <a href=\"http://www.phpbb.com/\" target=\"_blank\">คลิกที่นี่ เพื่อไปที่ phpBB</a> ซึ่งจะเปิดหน้าใหม่ให้โดยอัตโนมัติ.</li><li>แต่ถ้าคุณต้องการให้ข้อความเชื่อมโยงนั้นถูกแสดงออกมาเลย ก็พิมพ์ว่า:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />จะได้ผลเป็น, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>โปรแกรม phpBB มีฟังก์ชันพิเศษคือ <i>Magic Links</i> ที่สามารถแปลงข้อความเชื่อมโยงโดยอัตโนมัติ โดยที่คุณไม่ต้องพิมพ์ http:// เลย. เช่นคุณพิมพ์ว่า www.phpbb.com ลงในข้อความของคุณ จะได้ผลเป็น <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> โดยอัตโนมัติ.</li><li>ซึ่งจะใช้ได้กับชื่อ e-mail เช่น:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />จะได้ผลเป็น <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> หรือคุณแค่พิมพ์ว่า no.one@domain.adr ก็จะถูกแปลงให้โดยอัตโนมัติ.</li></ul>ทุกคำสั่งของ BBCode คุณสามารถใช้ร่วมกับ URLs ได้ เช่น <b>[img][/img]</b> (ดูหัวข้อถัดไป), <b>[b][/b]</b>, ฯลฯ. แต่จะต้องปิดคำสั่งตามลำดับที่ถูกต้อง, เช่น:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />เป็นคำสั่งที่<u>ผิด</u> และจะทำให้ข้อความของคุณถูกลบทิ้ง.");

$faq[] = array("--", "การแสดงรูปภาพในข้อความที่พิมพ์ตอบ");
$faq[] = array("การแทรกรูปภาพลงในข้อความ", "BBCode สามารถแทรกรูปภาพลงในข้อความ. มี 2 อย่างที่สำคัญคือ; บางคนไม่ชอบรูปภาพเยอะๆในข้อความ และรูปภาพนั้นจะต้องอยู่ใน internet (ไม่ใช่อยู่ในเครื่องคอมพิวเตอร์ของคุณ, ยกเว้นว่าคุณจะส่งมันขึ้น server). ดังนั้นคำสั่ง phpBB จึงไม่ใช่คำสั่งเก็บรูปภาพลง internet. คุณจะต้องใส่ตำแหน่ง URL ของรูปภาพไว้ระหว่างคำสั่ง <b>[img][/img]</b> เช่น:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />อย่างที่กล่าวไว้ข้างต้น ว่าคุณสามารถใช้ร่วมกับคำสั่งข้อความเชื่อมโยง <b>[url][/url]</b>, เช่น<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />จะได้ผลเป็น:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "อื่นๆ");
$faq[] = array("จะใส่คำสั่งของตัวเองได้หรือไม่?", "ไม่ได้");

//
// This ends the BBCode guide entries
//

?>