<?php
/***************************************************************************
 *                         lang_bbcode.php [japanese]
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
// Translation by:
//
// Yoichi Iwaki  :: yoichi01@rr.iij4u.or.jp
//
// For questions and comments use: yoichi01@rr.iij4u.or.jp
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
  
$faq[] = array("--","BBCodeの紹介");
$faq[] = array("BBCodeとは？", "BBCodeとは特殊なHTMLの実行コードです。BBCodeの使用は管理者によって設定されていますので、設定でBBCodeが無効となっている場合は使うことができません。BBCodeが有効となっている場合でも、ユーザーは投稿画面でBBCodeを無効にすることができます。BBCodeはHTMLと似たスタイルをしていて、タグは&lt; &gt;ではなく[ ]が用いられます。使用するテンプレートによってBBCodeは記事内容投稿欄の上にBBCode用のボタンがあり、これ使うことで直接BBCodeタグを入力することなく挿入することができます。");

$faq[] = array("--","テキストフォーマット");
$faq[] = array("文字を太字、斜体、下線を当てはめる方法", "BBCodeにはテキストの基本的なスタイルを変更するタグが含まれています。スタイルの変更は次のような方法で行うことができます：<ul><li>文字を太字にする場合はその文字を<b>[b][/b]</b>で囲みます <br /><br /><b>[b]</b>Hello!<b>[/b]</b><br /><br />上記のようにすれば <b>Hello!</b> となります</li><li>文字に下線を付ける場合はその文字を<b>[u][/u]</b>で囲みます<br /><br /><b>[u]</b>Good Morning!<b>[/u]</b><br /><br />上記のようにすれば <u>Good Morning</u> となります</li><li>文字を斜体にする場合はその文字を<b>[i][/i]</b>で囲みます<br /><br /><b>[i]</b>Great!<b>[/i]</b><br /><br />上記のようにすれば <i>Great!</i> となります</li></ul>");
$faq[] = array("文字の色や大きさを変える方法", "文字の色や大きさを変える場合は下記のタグを使用します。どのように表示されるかは観閲者のブラウザとシステムによって異なる場合があるので注意してください：<ul><li>文字の色を変える場合はその文字を<b>[color=][/color]</b>で囲みます。色の特定は(red, blue, yellow)といった単語、又は十六進数(例：#F5CA09)で指定することができます：<br /><br /><b>[color=red]</b>Hello!<b>[/color]</b><br /><br />又は<br /><br /><b>[color=#FF0000]</b>Hello!<b>[/color]</b><br /><br />上記のようにすれば <span style=\"color:red\">Hello!</span> となります</li><li>文字の大きさを変える場合はその文字を<b>[size=][/size]</b>で囲みます。文字の大きさは数字（ピクセル単位）で指定し、最小1から最大29まで設定することができます：<br /><br /><b>[size=9]</b>SMALL<b>[/size]</b><br /><br />上記のようにすれば <span style=\"font-size:9px\">SMALL</span> となります<br /><br />同様に<br /><br /><b>[size=24]</b>HUGE!<b>[/size]</b><br /><br />上記のようにすれば <span style=\"font-size:24px\">HUGE!</span> となります</li></ul>");
$faq[] = array("タグを組み合わせることはできますか？", "もちろんです。例としてタグをいくつか組み合わせたサンプルを書いてみます：<br /><br /><b>[size=18][color=red][b]</b>LOOK AT ME!<b>[/b][/color][/size]</b><br /><br />上記のようにすれば <span style=\"color:red;font-size:18px\"><b>LOOK AT ME!</b></span> となります<br /><br />ただし、あまりタグを組み合わせることはお勧めしません。またタグの配置は正しく行ってください。次の例は間違ったタグの配置です：<br /><br /><b>[b][u]</b>This is wrong<b>[/b][/u]</b>");

$faq[] = array("--","引用と固定幅テキストの出力");
$faq[] = array("返信で文章を引用する方法", "文章を引用する方法は2種類あります<ul><li>返信する時に文章の引用機能を使いたい場合は、引用したい文章を <b>[quote=\"\"][/quote]</b> で囲みます。\" \"の中には引用に関する情報（引用文章を書いた人物、引用した書籍など）を入力します。例えば、Mr. Bobという人物の文章を引用する場合は次のように入力することができます：<br /><br /><b>[quote=\"Mr. Bob\"]</b> Mr. Bobの文章 <b>[/quote]</b><br /><br />上記のようにすることで引用した文章がMr. Bobのものであることが分かります。[quote=\"Mr.Bob\"]の\"\"は<b>必ず</b>つけるようにしてください</li><li>2つ目の方法は引用情報を入力せずに文章を<b>[quote][/quote]</b>で囲みます。この場合、引用した文章は誰によるものなのかは分かりません</li></ul>");
$faq[] = array("コードや固定幅データを出力する方法", "  コードや固定幅が必要なものを出力したい場合は、そのテキストを<b>[code][/code]</b>で囲みます：<br /><br /><b>[code]</b>echo \"これはコードの一部です\";<b>[/code]</b><br /><br /><b>[code][/code]</b>で囲まれている部分で使用されているフォーマットは全て無効となり、この中のフォントはCourierで表示されます");

$faq[] = array("--","リストの作成");
$faq[] = array("番号のないリストを作成する方法", "BBCodeでは番号のあるリストとないリストをサポートしています。BBCodeのリストはHTMLのものと同じように機能します。番号のないリストは各項目の先頭に丸い点が置かれています。番号のないリストを作り場合は<b>[list][/list]</b>を用いて、各項目を<b>[*]</b>を用いて作ります。例えば自分の好きな色をリスト化する場合は次のようになります：<br /><br /><b>[list]</b><br /><b>[*]</b>Red<br /><b>[*]</b>Blue<br /><b>[*]</b>Yellow<br /><b>[/list]</b><br /><br />上記のようにすると次のようなリストができます：<ul><li>Red</li><li>Blue</li><li>Yellow</li></ul>");
$faq[] = array("番号のあるリストを作成する方法", "番号のあるリストを作成するためには<b>[list=1][/list]</b>を用います。番号順の代わりにアルファベット順にしたい場合は<b>[list=a][/list]</b>を用います。各項目は<b>[*]</b>を用いて作ります。簡単な例を紹介します：<br /><br /><b>[list=1]</b><br /><b>[*]</b>Go to the shops<br /><b>[*]</b>Buy a new computer<br /><b>[*]</b>Swear at computer when it crashes<br /><b>[/list]</b><br /><br />上記のようにすると次のようなリストができます：<ol type=\"1\"><li>Go to the shops</li><li>Buy a new computer</li><li>Swear at computer when it crashes</li></ol>次にアルファベット順リストの例を紹介します：<br /><br /><b>[list=a]</b><br /><b>[*]</b>The first possible answer<br /><b>[*]</b>The second possible answer<br /><b>[*]</b>The third possible answer<br /><b>[/list]</b><br /><br />上記のようにすると次のようなリストができます：<ol type=\"a\"><li>The first possible answer</li><li>The second possible answer</li><li>The third possible answer</li></ol>");

$faq[] = array("--", "リンクの作成");
$faq[] = array("他のサイトのリンクを作る方法", "BBCodeでは特定のURLへのリンクを作る方法をいくつかサポートしています。<ul><li>リンクしたい文字を<b>[url=][/url]</b>で囲みます。\"=\"の後にリンク先のURLを入力します。例えばphpBB.comへのリンクを作る場合は次のようにします：<br /><br /><b>[url=http://www.phpbb.com/]</b>Visit phpBB!<b>[/url]</b><br /><br />上記のようにすると <a href=\"http://www.phpbb.com/\" target=\"_blank\">Visit phpBB!</a> と表示されます。デフォルトではリンクをクリックすると新しいウィンドウにリンク先のページが表示されるようになっています。</li><li>URLそのものをリンクにしたい場合は、単純に次のようにします：<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />上記のようにすれば <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a> と表示されます。</li><li>phpBBには<i>自動リンク機能</i>が含まれています。自動リンクは構文的に正しいURLをタグを使用していなくてもリンクにする機能です。例えば www.phpbb.com と入力すると、これは自動的に <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> と表示されます</li><li>メールアドレスに関しても自動リンク機能が有効になっています：<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />上記のようにすれば <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> となります。しかし、タグをつけずに no.one@domain.adr と入力するだけでも自動的にリンクが作られます</li></ul>リンクのタグは <b>[img][/img]</b> （このタグについては次の項目を見てください）, <b>[b][/b]</b> など他の全てのBBCodeタグを囲むことができます。一つの部分に複数のタグを使用する場合は開始タグと終了タグを正しく並べてください。次のタグの使い方は間違ったものです：<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />上記の例は<u>誤った並べ方</u>なので注意してください");

$faq[] = array("--", "画像の表示");
$faq[] = array("画像を載せる方法", "BBCodeを用いて画像を投稿記事に載せることができます。画像を使用する場合は2つほど注意することがあります。1つ目は多くのユーザーは大量の画像が表示される記事を好ましく思っていませんので、載せる画像の数やサイズに注意する必要があります。2つ目は使用する画像は既にインターネット上で利用できるものに限られるということです。現在のバージョンでは一時的にphpBBが画像を保管する機能はありません。画像を載せるには画像のURLを<b>[img][/img]</b>で囲みます：<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />リンクに関する項目でも述べていますが、画像にリンクを張る場合は上記の構文を<b>[url][/url]</b>で囲みます：<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />上記のようにすれば次のようになります：<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "その他");
$faq[] = array("独自のタグを使うことはできますか？", "現在のバージョン（phpBB ver2.0）ではできません。次の大型バージョンでカスタマイズ可能なBBCodeを提供できるようにしようと考えています。");

//
// This ends the BBCode guide entries
//

?>