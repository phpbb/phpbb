<?php
/***************************************************************************
 *                         lang_bbcode.php [Korean]
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
// 2002/08/28 Translated by TankTonk 
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
  
$faq[] = array("--","소개");
$faq[] = array("BBCode란 무엇인가", "BBCode는 HTML의 특별 버전이다. 포럼상에 글을 올릴때 BBCode를 사용할수 있는지는 운영자가 결정한다. 또한 글 양식에서 각 글에 대하여 개별적으로 BBCode를 억제할 수 있다. BBCode는 스타일에 있어 HTML와 흡사하여, 태그는 &lt; 와 &gt; 대신에 대괄호 [ 와 ] 안에 들어가며, 어떤것을 어떻게 표시할 것인지를 강력하게 제어한다. 사용하는 템플레이트에 따라 글 양식의 메세지 영역위에 있는 클릭이 가능한 인터페이스로 BBCode를 글에 추가하는 것이 더 수월해 졌다는 것을 알게 될 것이다. 다음의 유용한 설명서를 보도록 하자.");

$faq[] = array("--","텍스트 포맷팅");
$faq[] = array("볼드, 이탤릭 및 밑줄 문자를 만드는 방법", "BBCode는 문자의 기본 스타일을 빠르게 바꿀수 있도록 태그를 포함하고 있다. 다음의 방법을 이용한다: <ul><li>일부 문자를 볼드로 하려면 <b>[b][/b]</b> 안에 집어 넣는다, 예를 들어, <br /><br /><b>[b]</b>안뇽<b>[/b]</b><br /><br />은 <b>안뇽</b>이 된다</li><li>밑줄을 그으려면 <b>[u][/u]</b>를 사용한다, 예를 들어, <br /><br /><b>[u]</b>좋은 아침<b>[/u]</b><br /><br />은 <u>좋은 아침</u>이 된다</li><li>문자를 이탤릭으로 하려면 <b>[i][/i]</b>를 사용한다, 예를 들어, <br /><br />정말 <b>[i]</b>좋구나!<b>[/i]</b><br /><br />는 정말 <i>좋구나!</i>가 된다</li></ul>");
$faq[] = array("문자의 색이나 크기를 바꾸는 방법", "문자의 색이나 크기를 바꾸려면 다음의 태그를 사용한다. 화면 출력은 사용자의 브라우저와 시스템에 의존됨을 명심하기 바란다: <ul><li>문자의 색의 변경은 문자를 <b>[color=][/color]</b> 안에 넣어서 한다. 지정색(예: red, blue, yellow 등) 을 입력하거나 16진수를 사용한다, 예: #FFFFFF, #000000. 빨간색 문자를 만들려면:<br /><br /><b>[color=red]</b>안뇽!<b>[/color]</b><br /><br /> 혹은 <br /><br /><b>[color=#FF0000]</b>안뇽!<b>[/color]</b><br /><br />으로 하면 <span style=\"color:red\">안뇽!</span>이 된다</li><li>문자의 크기는 <b>[size=][/size]</b> 태그를 이용한다. 이 태그는 현재 사용중인 탬플리트에 의존하지만 권장 형식은 문자 크기를 픽셀로 나타낸 수치로써, 1 부터 시작하여 29 까지이다. 예:<br /><br /><b>[size=9]</b>작은글씨<b>[/size]</b><br /><br />는 일반적으로 <span style=\"font-size:9px\">작은 글씨</span>가 되고<br /><br /><br /><br /><b>[size=24]</b>큰글씨!<b>[/size]</b><br /><br />는 <span style=\"font-size:24px\">큰글씨HUGE!</span>가 된다</li></ul>");
$faq[] = array("변환 태그를 조합할 수 있는가?", "물론 할 수 있다, 예를 들어 사람들의 주위를 끌려면:<br /><br /><b>[size=18][color=red][b]</b>여기 주목!<b>[/b][/color][/size]</b><br /><br /> 은 <span style=\"color:red;font-size:18px\"><b>여기 주목!</b></span>이 된다<br /><br />그러나 그러한 문자를 많이 사용하는 것을 권하지는 않는다! 태그를 올바로 닫도록 공고해야 한다. 예를 들어, 다음은 틀린 형식이다:<br /><br /><b>[b][u]</b>틀렸음<b>[/b][/u]</b>");

$faq[] = array("--","인용 및 고정폭 데이터 출력");
$faq[] = array("답변에 문장 인용하기", "문장 인용에는 두가지 방법이 있는데, 참조 와 비참조이다.<ul><li>게시판의 게시물에 대한 답글을 하기위해 인용 기능을 이용할때 게시글이 <b>[quote=\"\"][/quote]</b> 블럭내에 들어가는 메세지 창에 첨부된다는 것을 알아야한다. 이 방법으로 타인이나 기타 올리고자 하는것들에 대한 참조를 이용한 인용을 할 수 있다! 예를 들어, 홍길동이 보내온 글의 일부를 인용하려면 :<br /><br /><b>[quote=\"홍길동\"]</b>홍길동이 작성한 글<b>[/quote]</b><br /><br />결과적인 출력은 홍길동 작성:  뒤에 나오며 실제 문장 앞에 온다. 인용하는 이름 주변에 \"\" 를 사용하는 것을 잊지 말아야 한다.</li><li>두번째 방법은 맹목적 인용이다. 이 기능을 이용하려면 문자를 <b>[quote][/quote]</b> 태그 안에 넣는다. 메세지를 보면 단순히, 실제 문장 앞에 인용: 이라고 나온다</li></ul>");
$faq[] = array("코드 및 고정폭 데이터 출력", "코드의 일부분 이나 고정폭 글꼴을 이용하는 임의의 것을 출력하고자 한다면 <b>[code][/code]</b> 태그 안에 넣어야 한다. 예를 들어, <br /><br /><b>[code]</b>echo \"This is some code\";<b>[/code]</b><br /><br /><b>[code][/code]</b> 태그내에서 사용된 포맷팅은 전혀 변경이 없음을 알 수 있을 것이다.");

$faq[] = array("--","리스트 만들기");
$faq[] = array("비정렬 리스트 만들기", "BBCode는 두 종류의 리스트를 지원하는데, 비정렬과 정렬 리스트이다. 이것들은 HTML에 리스트들과 근본적으로는 동일하다. 비정렬 리스트는 리스트상의 각 항목들을 점 문자로 들여쓰기 하면서 연속적으로 출력한다. 비정렬 리스트를 만들려면 <b>[list][/list]</b>를 사용하고 리스트내의 각 항목들을 <b>[*]</b>를 이용하여 정의한다. 예를 들어, 좋아하는 색의 리스트를 만들려면 :<br /><br /><b>[list]</b><br /><b>[*]</b>Red<br /><b>[*]</b>Blue<br /><b>[*]</b>Yellow<br /><b>[/list]</b><br /><br />결과적으로 다음의 리스트가 만들어진다:<ul><li>Red</li><li>Blue</li><li>Yellow</li></ul>");
$faq[] = array("정렬 리스트 만들기", "리스트의 두번째 형식인 정렬 리스트는 각 항목 앞에 무엇을 표기할 것인지를 결정할 수 있게 해준다. <b>[list=1][/list]</b> 를 사용하여 번호 매김 리스트를 만들거나 <b>[list=a][/list]</b> 를 사용하여 알파벳 매김 리스트를 만들 수 있다. 비정렬 리스트에서와 같이 각 항목들은 <b>[*]</b>으로 정의한다. 예를 들어:<br /><br /><b>[list=1]</b><br /><b>[*]</b>가게 방문<br /><b>[*]</b>컴퓨터 구입<br /><b>[*]</b>컴퓨터 앞에서 열심히 공부함<br /><b>[/list]</b><br /><br /> 다음과 같은 출력을 얻는다:<ol type=\"1\"><li>가게 방문</li><li>컴퓨터 구입</li><li>컴퓨터 앞에서 열심히 공부함</li></ol> 알파벳 매김의 예로는:<br /><br /><b>[list=a]</b><br /><b>[*]</b>첫번째 답<br /><b>[*]</b>두번째 답<br /><b>[*]</b>세번째 답<br /><b>[/list]</b><br /><br />출력물은<ol type=\"a\"><li>첫번째 답</li><li>두번째 답</li><li>세번째 답</li></ol>");

$faq[] = array("--", "링크 만들기");
$faq[] = array("다른 사이트 링크", "phpBB의 BBCode는 URI(URL로 더 알려진 Uniform Resource Indicators) 만드는 여러 방법을 지원한다.<ul><li>첫번째 방법은 <b>[url=][/url]</b> 태그를 이용하는데, = 기호 다음에 오는 모든 문자들은 URL로 취급된다. 예를 들어 phpBB.com 으로 링크하려면:<br /><br /><b>[url=http://www.phpbb.com/]</b>phpBB로!<b>[/url]</b><br /><br />다음과 같은 링크를 얻는다, <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB로!</a> 링크가 새로운 창에서 열리므로 게시판은 계속 사용할 수 있다.</li><li>URL 자체를 링크로 표시하려면 :<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />다음과 같은 결과를 얻는다, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>또한 phpBB 는 <i>Magic Links</i>라는 기능을 제공하는데, 이것은 태그나 http://를 지정하지 않아도 문법적으로 올바른 URL을 링크로 만들어 준다. 예를 들어 메세지내에 www.phpbb.com 를 입력하면 자동으로 <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> 가 메세지에 만들어진다.</li><li>같은 기능이 이메일 주소에도 적용되는데, 다음과 같이 주소를 지정할 수 있다:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />결과는 다음과 같다 :  <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a>,혹은 그냥 no.one@domain.adr 을 메세지에 입력하면 나중에 보기할때 자동으로 변환이 된다.</li></ul>다른 BBCode 태그들과 마찬가지로 <b>[img][/img]</b> (다음 예제 참조), <b>[b][/b]</b>와 같은 태그도 URL을 포함할 수 있다. 포매팅 태그에서 처럼 태그의 열고 닫음을 정확히 해야 한다, 예를 들어:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />는 잘못된 것이다.");

$faq[] = array("--", "게시물에 이미지 보이기");
$faq[] = array("게시물에 이미지 붙이기", "phpBB의 BBCode는 게시물에 이미지를 넣을수 있도록 태그를 제공한다. 이 태그를 사용할때 기억해야할 두가지 중요한 것이 있는데; 우선 많은 사람들이 이미지가 뜨는 것을 반기지 않는다는 것과 이미지가 이미 인터넷상에 존재하고 있어야 한다(웹서버를 돌리지 않는한, 이미지가 자신의 컴안에 있어서만은 안된다). 현재 phpBB내에 이미지를 저장하는 방법은 없다(미래의 phpBB에서는 고려가 될 것이다). 이미지를 넣으려면 이미지의 URL을 <b>[img][/img]</b> 태그 안에 넣는다. 예를 들어:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />위에서 언급한 바와 같이 이미지를 <b>[url][/url]</b> 태그안에 넣어도 된다, 예를 들어<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />결과는:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "기타");
$faq[] = array("사용자 정의 태그를 추가할 수 있는가?", "phpBB 2.0에서 직접적인 방법으로는 안된다. 미래의 버전에서는 기능을 넣을 수도 있다");

//
// This ends the BBCode guide entries
//

?>