<?php
/***************************************************************************
 *                            lang_main.php [Korean]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.72 2001/12/24 20:31:35 psotfx Exp $
 * ----------------------------------------------------------------------------
 *     korean Language Edited by donguook,ryu(류동욱)
 *     E-Mail             : nexus@dreamwiz.com
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
// The format of this file is:
//
// ---> $lang['message'] = "text";
//
// You should also try to set a locale and a character
// encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may
// not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

//setlocale(LC_ALL, "en");
$lang['ENCODING'] = "euc-kr";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "Y년 m월 d일"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "포럼";
$lang['Category'] = "카테고리";
$lang['Topic'] = "Topic";
$lang['Topics'] = "주제글";
$lang['Replies'] = "리플";
$lang['Views'] = "조회";
$lang['Post'] = "작성글";
$lang['Posts'] = "토론글";
$lang['Posted'] = "글 작성시간 ";
$lang['Username'] = "아이디";
$lang['Password'] = "비밀번호";
$lang['Email'] = "Email";
$lang['Poster'] = "Poster";
$lang['Author'] = "글 쓴이";
$lang['Time'] = "때";
$lang['Hours'] = "시간";
$lang['Message'] = "메세지 내용";

$lang['1_Day'] = "1일 전";
$lang['7_Days'] = "7일 전";
$lang['2_Weeks'] = "2주 이내";
$lang['1_Month'] = "1개월 전";
$lang['3_Months'] = "3개월 전";
$lang['6_Months'] = "6개월 전";
$lang['1_Year'] = "1년 전";

$lang['Go'] = "이동";
$lang['Jump_to'] = "이동";
$lang['Submit'] = " 입력완료 ";
$lang['Reset'] = " 다 시 ";
$lang['Cancel'] = " 취 소 ";
$lang['Preview'] = " 미리보기 ";
$lang['Confirm'] = " 확 인 ";
$lang['Spellcheck'] = "스팰체크";
$lang['Yes'] = "예";
$lang['No'] = "아니요";
$lang['Enabled'] = "사용함";
$lang['Disabled'] = "사용않함";
$lang['Error'] = "Error";

$lang['Next'] = "다음";
$lang['Previous'] = "이전";
$lang['Goto_page'] = "페이지이동";
$lang['Joined'] = "가입일";
$lang['IP_Address'] = "IP Address";

$lang['Select_forum'] = "포럼선택";
$lang['View_latest_post'] = "마지막으로 등록된글 보기";
$lang['View_newest_post'] = "새로 올라온글 보기";
$lang['Page_of'] = "Page %d of %d Page"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Number";
$lang['AIM'] = "AIM Address";
$lang['MSNM'] = "MSN 메신져";
$lang['YIM'] = "Yahoo 메신져";

$lang['Forum_Index'] = "%s";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "새 주제글을 작성합니다.";
$lang['Reply_to_topic'] = "답변글을 작성합니다.";
$lang['Reply_with_quote'] = "글을 인용하여 작성합니다.";

$lang['Click_return_topic'] = "%s[게시물 보기]%s"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "%s[다시 시도하기]%s";
$lang['Click_return_forum'] = "%s[이전 포럼으로 되돌아가기]%s ";
$lang['Click_view_message'] = "%s[작성한 글을 봅니다.]%s";
$lang['Click_return_modcp'] = "%s[관리모드로 되돌아가기]%s ";
$lang['Click_return_group'] = "%s[그룹 페이지로 되돌아 가기]%s <p>";

$lang['Admin_panel'] = "관리자 로그인";

$lang['Board_disable'] = "현재 이용할수 없는 포럼입니다..";


//
// Global Header strings
//
$lang['Registered_users'] = "접속중인 가입회원 ";
$lang['Online_users_zero_total'] = "현재  <b>0</b> 명이 접속 중입니다. ";
$lang['Online_users_total'] = "현재 <b>%d</b> 명이 접속 중입니다. ";
$lang['Online_user_total'] = "현재 접속중인 유저  <b>%d</b> 명 &nbsp &nbsp  &nbsp  &nbsp  &nbsp ";
$lang['Reg_users_zero_total'] = "가입회원  <b>0</b> 명 &nbsp &nbsp  &nbsp  &nbsp  &nbsp ";
$lang['Reg_users_total'] = "가입회원  <b>%d</b> 명 &nbsp &nbsp  &nbsp  &nbsp  &nbsp ";
$lang['Reg_user_total'] = "가입회원  <b>%d</b> 명 &nbsp &nbsp  &nbsp  &nbsp  &nbsp ";
$lang['Hidden_users_zero_total'] = "비공개 회원  <b>0</b> 명 &nbsp  &nbsp  &nbsp  &nbsp ";
$lang['Hidden_user_total'] = "비공개 회원  <b>%d</b> 명  &nbsp  &nbsp  &nbsp  &nbsp ";
$lang['Hidden_user_total'] = "총 비공개 회원  <b>%d</b> 명 ";
$lang['Guest_users_zero_total'] = "손님  <b>0</b> 명";
$lang['Guest_users_total'] = "손님  <b>%d</b> 명";
$lang['Guest_user_total'] = "손님  <b>%d</b> 명";

$lang['You_last_visit'] = "전회 접속  %s"; // %s replaced by date/time
$lang['Current_time'] = "현재 시간  %s"; // %s replaced by time

$lang['Search_new'] = "새로 등록된 글 보기";
$lang['Search_your_posts'] = "내가 작성한 게시물";
$lang['Search_unanswered'] = "답변없는 게시물 보기";

$lang['Register'] = "회원가입";
$lang['Profile'] = "개인정보 열람 / 변경";
$lang['Edit_profile'] = "개인정보 수정";
$lang['Search'] = " 검 색 ";
$lang['Memberlist'] = "Memberlist";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "BBCode 안내";
$lang['Usergroups'] = "유저 그룹";
$lang['Last_Post'] = "마지막 등록 글";
$lang['Moderator'] = "포럼관리자 ";
$lang['Moderators'] = "포럼관리자 ";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "현재 총 등록 게시물  <b>0</b> 개"; // Number of posts
$lang['Posted_articles_total'] = "현재 총 등록 게시물  <b>%d</b> 개"; // Number of posts
$lang['Posted_article_total'] = "현재 총 등록 게시물   <b>%d</b> 개의"; // Number of posts
$lang['Registered_users_zero_total'] = "현재 총 가입회원  <b>0</b> 명..."; // # registered users
$lang['Registered_users_total'] = "현재 총 가입회원  <b>%d</b> 명 "; // # registered users
$lang['Registered_user_total'] = "현재 총 가입회원  <b>%d</b> 명 "; // # registered users
$lang['Newest_user'] = "최근 신규등록자  <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "마지막 접속이후 새로 전송된 게시물 없음";
$lang['No_new_posts'] = "마지막접속이후 새 게시물없음";
$lang['New_posts'] = "마지막접속이후 새 게시물있음";
$lang['New_post'] = "새 토론글";
$lang['No_new_posts_hot'] = "마지막접속이후 새 게시물없음 ";
$lang['New_posts_hot'] = "마지막접속이후 새 게시물있음 ";
$lang['No_new_posts_locked'] = "포럼이 잠겨있을경우";
$lang['New_posts_locked'] = "포럼이 잠겨있을경우";
$lang['Forum_is_locked'] = "포럼이 잠겨있음";


//
// Login
//
$lang['Enter_password'] = "아이디와 비밀번호를 입력하여 로그 인 하십시요";
$lang['Login'] = "로그 인";
$lang['Logout'] = "";

$lang['Forgotten_password'] = "비밀번호 분실";

$lang['Log_me_in'] = "비밀번호 기억";

$lang['Error_login'] = "아이디 또는 비밀번호가 맞지 않습니다.";


//
// Index page
//
$lang['Index'] = "Index";
$lang['No_Posts'] = "등록된 글 없음";
$lang['No_forums'] = "이 보드는 포럼을 갖고 있지 않습니다.";

$lang['Private_Message'] = "쪽 지 ";
$lang['Private_Messages'] = "쪽 지 함 ";
$lang['Who_is_Online'] = "현재접속자 위치보기";

$lang['Mark_all_forums'] = "마크된 모든포럼 읽기";
$lang['Forums_marked_read'] = "모든포럼이 읽은 마크로 표시됨";


//
// Viewforum
//
$lang['View_forum'] = "포럼보기";

$lang['Forum_not_exist'] = "당신이 선택한 그 포럼은 존재하지 않습니다";
$lang['Reached_on_error'] = "당신의 에러로 이 페이지에 도달하였습니다";

$lang['Display_topics'] = "지난 게시물 보기";
$lang['All_Topics'] = "전체게시물";

$lang['Topic_Announcement'] = "<b>공지사항 - </b>";
$lang['Topic_Sticky'] = "<b>읽어보기 - </b>";
$lang['Topic_Moved'] = "<b>이동 - </b>";
$lang['Topic_Poll'] = "투표";

$lang['Mark_all_topics'] = "마크된 모든 주제글 읽기";
$lang['Topics_marked_read'] = "이 포럼의 주제글은 지금 마크표시되었습니다. ";

$lang['Rules_post_can'] = "주제 글 작성 가능";
$lang['Rules_post_cannot'] = "주제글 작성 불가";
$lang['Rules_reply_can'] = "답변 글 쓰기 가능";
$lang['Rules_reply_cannot'] = "답변 글 쓰기 불가";
$lang['Rules_edit_can'] = "글 수정 가능 - 본인";
$lang['Rules_edit_cannot'] = "글 수정 불가";
$lang['Rules_delete_can'] = "글 삭제 가능 - 본인";
$lang['Rules_delete_cannot'] = "글 삭제 불가";
$lang['Rules_vote_can'] = "설문조사 만들기 가능";
$lang['Rules_vote_cannot'] = "설문조사 만들기 불가 ";
$lang['Rules_moderate'] = " %s심플관리%s  "; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "현재 이 포럼에는 아직 등록된 글이 없습니다.";


//
// Viewtopic
//
$lang['View_topic'] = "주제 보기";

$lang['Guest'] = 'Guest';
$lang['Post_subject'] = "제목";
$lang['View_next_topic'] = "다음 글";
$lang['View_previous_topic'] = "이전 글";
$lang['Submit_vote'] = "투표하기";
$lang['View_results'] = "투표결과";

$lang['No_newer_topics'] = "더 이상 게시물이 없습니다.";
$lang['No_older_topics'] = "더 이상 게시물이 없습니다.";
$lang['Topic_post_not_exist'] = "요청하신 주제글 혹은 토론글은 존재하지 않습니다. ";
$lang['No_posts_topic'] = "이 주제글에 대한 토론글은 존재하지 않습니다";

$lang['Display_posts'] = "이전 게시물보기";
$lang['All_Posts'] = "전체 메세지";
$lang['Newest_First'] = "읽지않은 메세지부터";
$lang['Oldest_First'] = "읽은메세지 부터";

$lang['Back_to_top'] = "맨 위로 가기";

$lang['Read_profile'] = "회원 프로필 보기"; 
$lang['Send_email'] = "E-Mail 보내기";
$lang['Visit_website'] = "사용자의 홈 페이지로 이동하기";
$lang['ICQ_status'] = "ICQ 상태";
$lang['Edit_delete_post'] = "이 글을 수정/삭제합니다.";
$lang['View_IP'] = "IP 보기";
$lang['Delete_post'] = "이 글을 삭제합니다.";

$lang['wrote'] = "님 작성"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "인용"; // comes before bbcode quote output.
$lang['Code'] = "Code"; // comes before bbcode code output.

$lang['Edited_time_total'] = "마지막 수정 - %s  %s, 수정 %d 합계시간"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "마지막 수정 - %s  %s, 수정 %d 합계시간"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "이글을 잠급니다.";
$lang['Unlock_topic'] = "이글을 잠금해제합니다.";
$lang['Move_topic'] = "이 글을 이동합니다.";
$lang['Delete_topic'] = "이 글을 삭제합니다.";
$lang['Split_topic'] = "이 글을 쪼갭니다.";

$lang['Stop_watching_topic'] = "이 주제글 감시하기 멈춤";
$lang['Start_watching_topic'] = "리플될때까지 이 주제글 감시하기.";
$lang['No_longer_watching'] = "이 주제글을 더 이상 감시하지 않음";
$lang['You_are_watching'] = "당신은 지금 현재 이 주제글을 감시중입니다";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "본문내용";
$lang['Topic_review'] = "Topic review";

$lang['No_post_mode'] = "유효하지 않은 작성방법"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "새 주제글 작성하기";
$lang['Post_a_reply'] = "답변글 작성하기";
$lang['Post_topic_as'] = "글 유형 지정";
$lang['Edit_Post'] = "글 수정하기";
$lang['Options'] = "옵션";
$lang['Attach_File'] = "파일첨부";

$lang['Post_Announcement'] = "공지사항";
$lang['Post_Sticky'] = "읽어보기";
$lang['Post_Normal'] = "보통 글";

$lang['Confirm_delete'] = "진짜루 삭제하시겠습니까..?";
$lang['Confirm_delete_poll'] = "이 투표를 진짜루 삭제하시겠습니까..?";

$lang['Flood_Error'] = "마지막 작성 후 곧 바로 똑같이 반복해서 또 다른 게시물을 만들 수 없습니다 ..";
$lang['Empty_subject'] = "글 제목을 입력하지 않으셨습니다.";
$lang['Empty_message'] = "본문 내용을 입력하지 않으셨습니다.";
$lang['Forum_locked'] = "죄송합니다.. 작성할수 있는권한이 없습니다..";
$lang['Topic_locked'] = "이 주제글은 잠금설정 되어있어 글의 편집이나 리플은 달수가 없습니다.";
$lang['No_post_id'] = "편집하기 위해 글을 선택해야합니다.";
$lang['No_topic_id'] = "답변하기 위한 주제글을 선택해야 합니다.";
$lang['No_valid_mode'] = "당신은 글 인용,답변글 편집만 할수 있습니다.돌아가서 다시 시도해 주십시요.";
$lang['No_such_post'] = "찾는 게시물이 없습니다..돌아가서 다시 시도 해주십시요";
$lang['Edit_own_posts'] = "죄송합니다..본인이 작성한 글만 편집 할수 있습니다";
$lang['Delete_own_posts'] = "죄송합니다..본인이 작성한 글만 삭제할수 있습니다.";
$lang['Cannot_delete_replied'] = "죄송합니다..답변글에 대한 게시물을 삭제할 수 없습니다.";
$lang['Cannot_delete_poll'] = "죄송합니다..활성중인 설문,투표를 삭제할수 없습니다.";
$lang['Empty_poll_title'] = "님께서 만든 투표에 대해 제목을 입력해야 합니다.";
$lang['To_few_poll_options'] = "최소 2개 이상의 설문 옵션을 입력해야 합니다. ";
$lang['To_many_poll_options'] = "포럼에서 지정한 설문옵션 수 이상은 입력하실수 없습니다.";
$lang['Post_has_no_poll'] = "이 게시물은 설문조사가 없습니다.";

$lang['Disallowed_extension'] = "연장 %s 허용되지 않습니다."; // replace %s with extension (e.g. .php) 
$lang['Disallowed_Mime_Type'] = "허용된 Mime Type이 아님: %s<p>허용된 Types:<br>%s"; // mime type, allowed types 
$lang['Attachement_too_big'] = "너무 큰 첨부파일,<br>최대 Size: %d Bytes"; // replace %d with maximum file size 
$lang['General_Upload_Error'] = "업로드 Error: 첨부파일을 업로드하지 않을수 있음 %s"; // replace %s with local path 


$lang['Add_poll'] = "설문조사 만들기";
$lang['Add_poll_explain'] = " 형식에 맞게 아래 필드를입력하시면 설문조사를 만들수 있습니다.";
$lang['Poll_question'] = "설문 질의";
$lang['Poll_option'] = "설문 옵션";
$lang['Add_option'] = "추가";
$lang['Update'] = "업데이트";
$lang['Delete'] = " 삭  제 ";
$lang['Poll_for'] = "설문 마감일";
$lang['Poll_for_explain'] = "[ 예) 10일 후라면 10을 넣으시면 됩니다..무기한은 0을 쓰세요. ]";
$lang['Delete_poll'] = "설문 삭제";

$lang['Disable_HTML_post'] = "이 글에서 HTML 사용하지 않음";
$lang['Disable_BBCode_post'] = "이 글에서BBCode 사용하지 않음";
$lang['Disable_Smilies_post'] = "이 글에서 Smilies Icon 사용하지 않음";

$lang['HTML_is_ON'] = "HTML is <u>ON</u>";
$lang['HTML_is_OFF'] = "HTML is <u>OFF</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s is <u>ON</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s is <u>OFF</u>";
$lang['Smilies_are_ON'] = "Smilies Icon <u>ON</u>";
$lang['Smilies_are_OFF'] = "Smilies Icon <u>OFF</u>";

$lang['Attach_signature'] = "서명 삽입 (개인정보에서 서명을 수정할수 있습니다.)";
$lang['Notify'] = "이 글에대한 답변글을 메일로 받아봅니다.";
$lang['Delete_post'] = " 이 글을 삭제합니다 ";

$lang['Stored'] = "게시물이 데이타베이스에 등록 되었습니다.";
$lang['Deleted'] = "게시물이 성공적으로 삭제되었습니다.";
$lang['Poll_delete'] = "설문 함이 성공적으로 삭제되었습니다.";
$lang['Vote_cast'] = "성공적으로 한표를 행사하셨습니당.. ^^";

$lang['Topic_reply_notification'] = "답변글이 등록되었습니다.";

$lang['bbcode_b_help'] = "굵은글자: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "이탤릭체: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "글에 밑줄보임: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "글 인용: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "코드: [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "리스트: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "순서 목록: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "이미지삽입: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "URL 삽입: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "태그를 닫습니다.";
$lang['bbcode_s_help'] = "폰트칼라: [color=red]text[/color]  팁: 님 또한 사용할수 있음 color=#FF0000";
$lang['bbcode_f_help'] = "폰트싸이즈: [size=x-small]small text[/size]";

$lang['Emoticons'] = "감정아이콘들";
$lang['More_emoticons'] = "감정아이콘 추가";

$lang['Font_color'] = "폰트칼라";
$lang['color_default'] = "기본색";
$lang['color_dark_red'] = "어두운 빨강";
$lang['color_red'] = "빨강";
$lang['color_orange'] = "오렌지";
$lang['color_brown'] = "브라운";
$lang['color_yellow'] = "노랑색";
$lang['color_green'] = "초록색";
$lang['color_olive'] = "올리브";
$lang['color_cyan'] = "청록";
$lang['color_blue'] = "파랑";
$lang['color_dark_blue'] = "어두운 파랑";
$lang['color_indigo'] = "남 색";
$lang['color_violet'] = "바이올랫";
$lang['color_white'] = "흰색";
$lang['color_black'] = "검정";

$lang['Font_size'] = "폰트싸이즈";
$lang['font_tiny'] = "아주작게";
$lang['font_small'] = "작게";
$lang['font_normal'] = "보통";
$lang['font_large'] = "크게";
$lang['font_huge'] = "아주크게";

$lang['Close_Tags'] = "태그 닫기";
$lang['Styles_tip'] = "팁: 스타일은 선택된 원문에 빠르게 응용될 수 있습니다.";


//
// Private Messaging
//
$lang['Private_Messaging'] = "쪽지 메세지";

$lang['Login_check_pm'] = "쪽지 함 확인";
$lang['New_pms'] = "새 쪽지 %d 개 도착 "; // You have 2 new messages
$lang['New_pm'] = "새 쪽지 도착 -[ %d ]"; // You have 1 new message
$lang['No_new_pm'] = "도착한 쪽지 없음";
$lang['Unread_pms'] = "읽지 않은 메시지 %d ";
$lang['Unread_pm'] = "읽지 않은 메시지 %d ";
$lang['No_unread_pm'] = "읽지않은 메시지가 없습니다.";
$lang['You_new_pm'] = "새 쪽지가 도착 했시유....~~~~";
$lang['You_new_pms'] = "새로운 쪽지가 쪽지함에 도착해 있습니다.";
$lang['You_no_new_pm'] = "새로 도착한 쪽지가 없습니다.";

$lang['Inbox'] = "받은 쪽지함";
$lang['Outbox'] = "쪽지함 나가기";
$lang['Savebox'] = "쪽지 보관함";
$lang['Sentbox'] = "보낸 쪽지함";
$lang['Flag'] = "FLAG";
$lang['Subject'] = "제목";
$lang['From'] = "보낸이";
$lang['To'] = "받는이";
$lang['Date'] = "날자";
$lang['Mark'] = "마크";
$lang['Sent'] = "보냄";
$lang['Saved'] = "저장";
$lang['Delete_marked'] = "마크한거 삭제";
$lang['Delete_all'] = "전부삭제";
$lang['Save_marked'] = "마크한거 저장"; 
$lang['Save_message'] = "쪽지저장";
$lang['Delete_message'] = "쪽지삭제";

$lang['Display_messages'] = "이전에 받은 쪽지 전부보기"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "전체 메세지";

$lang['No_messages_folder'] = "쪽지함이 비어 있습니다.";

$lang['PM_disabled'] = "이 포럼에서는 쪽지보내기가 불가능하게 설정되어습니다.";
$lang['Cannot_send_privmsg'] = "죄송합니다..관리자가 쪽지보내기를 막아 놓았습니다.";
$lang['No_to_user'] = "쪽지를 보내기 위해서는 아이디를 입력해야 합니다.";
$lang['No_such_user'] = "죄송합니다..검색하신 아이디는 존재하지 않습니다.";

$lang['Message_sent'] = "쪽지가 성공적으로 보내졌습니다.";

$lang['Click_return_inbox'] = "%s[쪽지 박스로 돌아가기]%s";
$lang['Click_return_index'] = " %s[메인 화면으로 가기]%s";

$lang['Send_a_new_message'] = "새 쪽지 보내기";
$lang['Send_a_reply'] = "답변 쪽지 보내기";
$lang['Edit_message'] = "쪽지 수정하기";

$lang['Notification_subject'] = "새 쪽지가 도착했습니다.";

$lang['Find_username'] = "아이디 찾기";
$lang['Find'] = " 찾 기 ";
$lang['No_match'] = "맞는아이디가 없습니다";

$lang['No_post_id'] = "조건 지정되지 않은 게시물 ID ";
$lang['No_such_folder'] = "존재하지 않는 폴더입니다.";
$lang['No_folder'] = "지정되지 않은 폴더입니다.";

$lang['Mark_all'] = "전부 마크하기";
$lang['Unmark_all'] = "마크 전부해제";

$lang['Confirm_delete_pm'] = "진짜루 삭제 하시겠습니까..?";
$lang['Confirm_delete_pms'] = "진짜루 삭제 하시겠습니까..?";

$lang['Inbox_size'] = "현재 쪽지함 용량 %d%% "; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "보낸 쪽지함 용량 %d%% "; 
$lang['Savebox_size'] = "쪽지보관함 용량 %d%% "; 

$lang['Click_view_privmsg'] = "%s[ 쪽지함 들어가기 ]%s";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "프로필 보기 :: %s"; // %s is username 
$lang['About_user'] = " %s 님의 프로필입니다."; // %s is username

$lang['Preferences'] = "Preferences";
$lang['Items_required'] = "필수 입력 정보는 모두 기재하셔야 합니다... ";
$lang['Registration_info'] = "필수 입력 사항입니다.";
$lang['Profile_info'] = "선택 입력사항입니다.";
$lang['Profile_info_warn'] = "선택 정보에 해당하는 사항이 있으시면 입력 해주시기 바랍니다.";
$lang['Avatar_panel'] = "아바타 모드";
$lang['Avatar_gallery'] = "아바타 겔러리";

$lang['Website'] = "홈 페이지";
$lang['Location'] = "사는곳";
$lang['Contact'] = "연락";
$lang['Email_address'] = "E-mail 주소";
$lang['Email'] = "E-mail";
$lang['Send_private_message'] = "쪽지 보내기";
$lang['Hidden_email'] = "[ Hidden ]";
$lang['Search_user_posts'] = "이 사용자가 작성한 게시물 찾기.";
$lang['Interests'] = "취미/관심";
$lang['Occupation'] = "직 업"; 
$lang['Poster_rank'] = "글 작성순위";

$lang['Total_posts'] = "총 작성글";
$lang['User_post_pct_stats'] = "%.2f%% 중 &nbsp; 총"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f 개"; // 1.5 posts per day
$lang['Search_user_posts'] = "%s님이 작성한 글 모두보기"; // Find all posts by username

$lang['No_user_id_specified'] = "찾는회원이 없습니다.";
$lang['Wrong_Profile'] = "본인의 프로필 외에는 수정할수 없시유..~";
$lang['Sorry_banned_or_taken_email'] = "잘못된 메일이거나 중복되는 메일이 이미 등록 되어있습니다..<br>확인 후 다시 작성하시기 바랍니다.";
$lang['Only_one_avatar'] = "1개의 아바타만 지정해야 합니다.";
$lang['File_no_data'] = "입력한 URL에는 파일이 존재하지 않습니다.";
$lang['No_connection_URL'] = "입력한 URL로 연결할수 없습니다. ";
$lang['Incomplete_URL'] = "입력한 URL은 잘못된 URL이거나 불완전한 URL입니다.";
$lang['Wrong_remote_avatar_format'] = "입력하신 URL은 잘못된 URL입니다.";
$lang['No_send_account_inactive'] = "죄송합니다..활성되지 않은 비밀번호이거나 포럼에서 정의한 잘못된 비밀번호라 반영이 되지않았습니다. .포럼관리자에게 문의해 주세요.";

$lang['Always_smile'] = "Smilies Icon을 사용합니다.";
$lang['Always_html'] = "HTML을 사용합니다.";
$lang['Always_bbcode'] = "BBCode를 사용합니다.";
$lang['Always_add_sig'] = "언제나 나의 서명을 첨부합니다.";
$lang['Always_notify'] = "답변글이 올라오면 메일로 알려줍니다.";
$lang['Always_notify_explain'] = "(본인이 작성한글에 글이 등록되면 메일로 발송되는 기능)";

$lang['Board_style'] = "보드 스타일";
$lang['Board_lang'] = "보드 언어";
$lang['No_themes'] = "NO Themes";
$lang['Timezone'] = "지역시간 선택 - (KOREA 라면 기본권장)";
$lang['Date_format'] = "날짜 포맷";
$lang['Date_format_explain'] = " (<a href=\"http://www.php.net/date\" target=\"_other\">날짜함수 참고</a>  - (KOREA 라면 기본 권장) )";
$lang['Signature'] = "서명입력";
$lang['Signature_explain'] = "(게시물등록시 하단부에 자동삽입되며 영문<br> %d 자 한글 127자 까지 가능합니다.)";
$lang['Public_view_email'] = "나의 E-Mail 주소를 공개합니다.";

$lang['Current_password'] = "현재 비밀번호";
$lang['New_password'] = "새 비밀번호";
$lang['Confirm_password'] = "비밀번호 확인";
$lang['password_if_changed'] = "(변경하실때에만 입력하세요.)";
$lang['password_confirm_if_changed'] = "(비밀번호 확인사살입니다.)";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "아바타이미지는 %d X %d pixels 로 제한되며 싸이즈는 %dKb 이상 넘을수 없습니다"; $lang['Upload_Avatar_file'] = "아바타 이미지를 내컴퓨터에서 직접 업로드합니다.";
$lang['Upload_Avatar_URL'] = "온라인상에 아바타이미지가 있을경우 URL로 링크합니다.";
$lang['Upload_Avatar_URL_explain'] = "";
$lang['Pick_local_Avatar'] = "준비된 이곳 아바타 겔러리에서 선택합니다.";
$lang['Link_remote_Avatar'] = "아바타이미지 링크";
$lang['Link_remote_Avatar_explain'] = "아바타이미지가 있을경우 직접 링크.";
$lang['Avatar_URL'] = "URL of Avatar Image";
$lang['Select_from_gallery'] = "여기에있는 아바타겔러리를 통해 등록합니다";
$lang['View_avatar_gallery'] = "겔러리 들어가기";

$lang['Select_avatar'] = "아바타선택";
$lang['Return_profile'] = "아바타 선택안함";
$lang['Select_category'] = "카테고리 선택";

$lang['Delete_Image'] = "이미지 삭제";
$lang['Current_Image'] = "현재 이미지";

$lang['Notify_on_privmsg'] = "새 쪽지가 오면 메일로 알려 줍니다.";
$lang['Popup_on_privmsg'] = "쪽지가 오면 팝업 윈도창으로 엽니다."; 
$lang['Popup_on_privmsg_explain'] = ""; 
$lang['Hide_user'] = "나의 정보를 공개하지 않습니다.";

$lang['Profile_updated'] = "수정하신 정보가 업데이트 되었습니다.";
$lang['Profile_updated_inactive'] = "환경설정은 업데이트 되었지만 핵심내용을 바꾼것 같습니다..그래서 님의 계정은 지금 활성되지 않습니다..다시 계정을 활성화 하실려면 관리자에게 요청하시기 바랍니다.";

$lang['Password_mismatch'] = "비밀번호가 일치하지 않습니다.";
$lang['Current_password_mismatch'] = "데이터베이스에 저장된 비밀번호와 일치하지 않습니다.";
$lang['Invalid_username'] = "포럼에서 정의한 사용불가 아이디이거나 중복되는 아이디는 사용하실수 없습니다.";
$lang['Signature_too_long'] = "너무 긴 서명입니다.";
$lang['Fields_empty'] = "필수입력 정보중 입력하지않은 필드가 있는것 같습니다.";
$lang['Avatar_filetype'] = "아바타이미지 타입은 .jpg, .gif or .png 입니다.";
$lang['Avatar_filesize'] = "아바타이미지 파일 싸이즈는  %d kB 입니다."; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "아바타이미지 크기는 가로폭 %d pixels 세로폭 %d pixels 입니다."; 

$lang['Welcome_subject'] = "%s "; // Welcome to my.com forums
$lang['New_account_subject'] = "새로운 사용자 아이디";
$lang['Account_activated_subject'] = "아이디 활성";

$lang['Account_added'] = "<b>성공적으로 등록되었습니다..감사합니다.</b><p>등록정보가 데이타베이스에 저장 되었습니다.<p>메인화면으로 가셔서 다시 로그 인 하십시요...!";
$lang['Account_inactive'] = "아이디가 만들어졌습니다.. 그러나 이 포럼은 아이디의 활성을 요청합니다.. 아이디 정보는 전자우편으로 발송되었습니다..자세한 정보는 전자우편을 확인하세요..";
$lang['Account_inactive_admin'] = "계정이 만들어졌습니다.. 그러나 이포럼은 관리자까지 아이디의 활동을 요청합니다..아이디가 활성이 될 때 전자우편으로 알려질것입니다..";
$lang['Account_active'] = "아이디가 만들어졌습니다.. 가입해주셔서 감사합니다.";
$lang['Account_active_admin'] = "이 아이디는 활성화되어 있습니다.";
$lang['Reactivate'] = "당신의 아이디를 활성화 시켜주세요!";
$lang['COPPA'] = "아이디가 생성되었지만, 메일계정에 문제가 있읍니다..체킹하여 문제가있을시 로그인 하실수 없습니다.";

$lang['Registration'] = " 이용 약관 입니다.";
$lang['Reg_agreement'] = "<ul type=\"square\"><li> 회원가입은 본인가입을 원칙으로 합니다.  <br /><br /><li> 만약 허위가입 적발시는 별도의 통지없이 제명 처리됩니다. ^.^ <br /><br /><li> 본 포럼장 내에서는 욕설과 비방글을 올리지 않습니다.<br /><br />";

$lang['Agree_under_13'] = "<li>위의 약관에 동의하시는 14세 <b>이하</b> 입장";
$lang['Agree_over_13'] = "<li>위의 약관에 동의하시는 14세 <b>이상</b> 입장";
$lang['Agree_not'] = "<li>위의 약관에 동의 하지 않습니다.</ul>";

$lang['Wrong_activation'] = "데이타베이스에 있는 E-Mail 과 일치하지않거나 존재하지 않는 E-Mail입니다. ";
$lang['Send_password'] = "새로운 비밀번호 발급받기"; 
$lang['Password_updated'] = "새로운 비밀번호가 이메일로 발송되었습니다..확인 해보시기 바랍니다.";
$lang['No_email_match'] = "입력하신 이메일 주소와 아이디리스트 에 저장되어있는 이메일 주소가 일치하지 않습니다.";
$lang['New_password_activation'] = "요청하신 새 비밀번호 입니다.";
$lang['Password_activated'] = "로그인을 할 때 전자우편으로 재발송받은 비밀번호로 로그인 하세요.";

$lang['Send_email_msg'] = "회원에게 E-Mail 보내기..!";
$lang['No_user_specified'] = "정의되지 않은 유저 입니다.";
$lang['User_prevent_email'] = "이 사용자는 전자우편 받기를 바라지 않습니다..쪽지를 보내 보세요..!";
$lang['User_not_exist'] = "존재하지 않는 사용자 입니다.";
$lang['CC_email'] = "지금발송되는 메일이 본인메일로도 발송되게합니다. ";
$lang['Email_message_desc'] = "<br>지금 발송하시는 E-Mail은 포럼의회원님에게 보내지는메일로서 Html태그나 BBcode는 사용할수없습니다. .";
$lang['Flood_email_limit'] = "현재 시간에는 이메일을 보낼수 없습니다.. 나중에 다시 보내세요..!";
$lang['Recipient'] = "받는사람";
$lang['Email_sent'] = "E-Mail을 성공적으로 발송하였습니다.";
$lang['Send_email'] = "E-Mail 보내기";
$lang['Empty_subject_email'] = "이 메일 제목을 입력하세요.";
$lang['Empty_message_email'] = "이 메일 내용을 입력하세요.";


//
// Memberslist
//
$lang['Select_sort_method'] = "분류방식 선택";
$lang['Sort'] = "분류";
$lang['Sort_Top_Ten'] = "TOP 10 게시물";
$lang['Sort_Joined'] = "가입일자";
$lang['Sort_Username'] = "아이디";
$lang['Sort_Location'] = "사는곳";
$lang['Sort_Posts'] = "총 작성글";
$lang['Sort_Email'] = "E-mail";
$lang['Sort_Website'] = "홈 페이지";
$lang['Sort_Ascending'] = "위 로";
$lang['Sort_Descending'] = "아래로";
$lang['Order'] = "순서";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "그룹 관리";
$lang['Group_member_details'] = "유저 그룹 현황";
$lang['Group_member_join'] = "멤버쉽 가입";

$lang['Group_Information'] = "그룹 정보";
$lang['Group_name'] = "그룹 이름";
$lang['Group_description'] = "그룹 소개";
$lang['Group_membership'] = "그룹 멤버쉽";
$lang['Group_Members'] = "그룹 멤버";
$lang['Group_Moderator'] = "그룹 관리자";
$lang['Pending_members'] = "보류멤버";

$lang['Group_type'] = "그룹 타입";
$lang['Group_open'] = "공개그룹";
$lang['Group_closed'] = "그룹닫기";
$lang['Group_hidden'] = "비공개 그룹";

$lang['Current_memberships'] = "현재 가입 그룹";
$lang['Non_member_groups'] = "현재 가입하지 않은 그룹";
$lang['Memberships_pending'] = "아직 그룹관리자의 승인이 나지않아 보류 중입니다.";

$lang['No_groups_exist'] = "현재 생성된 그룹이 없습니다.";
$lang['Group_not_exist'] = "그 유저그룹은 존재하지 않습니다.";

$lang['Join_group'] = "멤버등록";
$lang['No_group_members'] = "현재 이 그룹에는 멤버회원이 없습니다.";
$lang['Group_hidden_members'] = "비공개그룹으로서 그룹멤버정보를 공개하지 않습니다.";
$lang['No_pending_group_members'] = "이 그룹은 보류멤버가 없습니다.";
$lang["Group_joined"] = "성공적으로 그룹멤버로 등록되었습니다.<br /><br />그룹관리자의 승인을 기다리시면 됩니다.";
$lang['Group_request'] = "새로운 메버님께서 등록신청을 하셨습니다";
$lang['Group_approved'] = "멤버 승인이 되었음을 알려드립니다.";
$lang['Group_added'] = "이 유저그룹에 추가되었습니다."; 
$lang['Already_member_group'] = "당신은 이미 이 그룹에 가입되어있는 멤버입니다.";
$lang['User_is_member_group'] = "님은 이미 이 그룹의 멤버로 되어있습니다.";
$lang['Group_type_updated'] = "그룹의 타입변경이 성공적으로 실행되었습니다.";

$lang['Could_not_add_user'] = "선택한 사용자는 존재하지 않습니다..";
$lang['Could_not_anon_user'] = "비가입 회원은 그룹멤버가 될수 없습니다.";

$lang['Confirm_unsub'] = "진짜루 탈퇴하시겠습니가..?";
$lang['Confirm_unsub_pending'] = "이 그룹에 아직 승인되지 않은 보류상태입니다..이 그룹에서 탈퇴하시겠습니까..?";

$lang['Unsub_success'] = "이 그룹에서 성공적으로 탈퇴되었습니다.";

$lang['Approve_selected'] = "등록승인";
$lang['Deny_selected'] = "승인취소";
$lang['Not_logged_in'] = "그룹에 들어가기 위해서는 로그 인 하셔야 합니다.";
$lang['Remove_selected'] = " 멤 버 삭 제";
$lang['Add_member'] = " 멤버 추가하기 ";
$lang['Not_group_moderator'] = "관리자가 아니기때문에 이 기능을 사용하실수 없습니다.";

$lang['Login_to_join'] = "로그 인 하시면 멤버쉽에 가입하실수 있습니다.";
$lang['This_open_group'] = "현재 오픈되어 있는 이그룹에 등록을 신청합니다.";
$lang['This_closed_group'] = "이그룹은 닫혀있어 지금은 가입을 할수 없습니다. ";
$lang['This_hidden_group'] = "선택하신 그룹은 비공개그룹입니다.... 가입관련사항은 그룹관리자에게 문의하시기 바랍니다. ";
$lang['Member_this_group'] = "지금 이 그룹에서 탈퇴합니다.";
$lang['Pending_this_group'] = "지금 이 멤버쉽에서 탈퇴합니다.";
$lang['Are_group_moderator'] = "이 그룹의 관리자 입니다.";
$lang['None'] = "None";

$lang['Subscribe'] = "등록신청";
$lang['Unsubscribe'] = "탈퇴신청";
$lang['View_Information'] = "그룹보기";


//
// Search
//
$lang['Search_query'] = "검 색 어 입 력";
$lang['Search_options'] = "검 색 옵 션 ";

$lang['Search_keywords'] = "키워드 검색";
$lang['Search_keywords_explain'] = "단어의 결과에 대해 <u>AND</u> 연산을 사용할수 있으며 단어의 정의를 내리기위해 <u>OR</u> 연산을 사용할수 있습니다..그리고 한 문자만 갖구 검색하는 wildcard (*.*) 를 사용할수 있습니다.";
$lang['Search_author'] = "글쓴이 검색";
$lang['Search_author_explain'] = "Use * as a Wildcard<BR>예)<BR> <B>*</B>A 를 입력하시면 A 가 포함된 아이디를 모두찾아주며<BR> A<b>*</b>를 입력하시면 A로 시작하는 아이디를 모두 찾아줍니다.  ";

$lang['Search_for_any'] = "지정한 단어 중 하나만 포함되어도 검색";
$lang['Search_for_all'] = "지정한 단어가 모두 포함되는 게시물을 검색";

$lang['Return_first'] = "처음부터 시작"; // followed by xxx characters in a select box
$lang['characters_posts'] = "불특정 내용검색";

$lang['Search_previous'] = "지난게시물 검색"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "분류방식";
$lang['Sort_Time'] = "글쓴시간";
$lang['Sort_Post_Subject'] = "본문제목";
$lang['Sort_Topic_Title'] = "주제글 제목";
$lang['Sort_Author'] = "글쓴이";
$lang['Sort_Forum'] = "포럼";

$lang['Display_results'] = "출력 방식";
$lang['All_available'] = "전체에서 ";
$lang['No_searchable_forums'] = "검색하신포럼은 접근이 제한된 포럼입니다..";

$lang['No_search_match'] = "찾는 정보가 없거나 회원전용 메뉴입니다.";
$lang['Found_search_match'] = "매치되는 %d 개의 글을 찾았습니다."; // eg. Search found 1 match
$lang['Found_search_matches'] = "매치되는 %d 개의 글을 찾았습니다."; // eg. Search found 24 matches

$lang['Close_window'] = "윈도우 창 닫기";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "죄송합니다. 이 포럼은 %s만 메시지를 전송할수 있습니다.";
$lang['Sorry_auth_sticky'] = "죄송합니다. 이 포럼은 %s 만 sticky를 작성할수 있습니다."; 
$lang['Sorry_auth_read'] = "죄송합니다. 이 포럼은 %s만 읽기가 가능한 포럼입니다.."; 
$lang['Sorry_auth_post'] = "죄송합니다. 이 포럼은 %s만 주제글을 작성할수 있습니다."; 
$lang['Sorry_auth_reply'] = "죄송합니다. 이 포럼은 %s만 토론글에 대해 답변할수 있습니다. "; 
$lang['Sorry_auth_edit'] = "죄송합니다. 이 포럼은 %s만 토론글을 편집할수 있습니다."; 
$lang['Sorry_auth_delete'] = "죄송합니다. 이 포럼은 %s만 토론글을 삭제할수 있습니다. "; 
$lang['Sorry_auth_vote'] = "죄송합니다. 이 포럼은 %s만  투표를 할수 있습니다."; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>등록되지 않은 사용자</b>";
$lang['Auth_Registered_Users'] = "<b>가입회원</b>";
$lang['Auth_Users_granted_access'] = "<b>운영자가 인증한 회원</b>";
$lang['Auth_Moderators'] = "<b>관리자</b>";
$lang['Auth_Administrators'] = "<b>운영자</b>";

$lang['Not_Moderator'] = "당신은 이 포럼의 관리자가 아닙니다.";
$lang['Not_Authorised'] = "작성자가 아닙니다.";

$lang['You_been_banned'] = "당신은 이 포럼으로부터 접근이 금지되었습니다.<br />자세한 사항은 관리자에게 문의해주세요.";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "회원 0 명 / "; // There ae 5 Registered and
$lang['Reg_users_online'] = "회원 %d 명 / "; // There ae 5 Registered and
$lang['Reg_user_online'] = " 회원 %d 명 / "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "비공개 회원 0 명 "; // 6 Hidden users online
$lang['Hidden_users_online'] = "비공개 %d 명"; // 6 Hidden users online
$lang['Hidden_user_online'] = "비공개 회원 %d 명"; // 6 Hidden users online
$lang['Guest_users_online'] = "손님 %d 명"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "손님 0 명..."; // There are 10 Guest users online
$lang['Guest_user_online'] = " 손님 %d 명"; // There is 1 Guest user online
$lang['No_users_browsing'] = "현재 포럼에 접속한 유저가 없습니다.";

$lang['Online_explain'] = "이 자료는 5분이 지나서 회원들에게 공개됩니다.";

$lang['Forum_Location'] = "현재 위치";
$lang['Last_updated'] = "Last Updated";

$lang['Forum_index'] = "포럼메인 화면";
$lang['Logging_on'] = "로그 인 중";
$lang['Posting_message'] = "글 작성 중";
$lang['Searching_forums'] = "포럼 검색 중";
$lang['Viewing_profile'] = "프로필 보기";
$lang['Viewing_online'] = "온라인 접속자 보기";
$lang['Viewing_member_list'] = "멤버리스트";
$lang['Viewing_priv_msgs'] = "쪽지함";
$lang['Viewing_FAQ'] = "FAQ 보기";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "관리자 영역입니다.";
$lang['Mod_CP_explain'] = "게시물을 선택하여 삭제,이동 ,잠금,잠금해제를 하실수 있습니다.";

$lang['Select'] = " 선 택 ";
$lang['Delete'] = " 삭 제 ";
$lang['Move'] = " 이 동 ";
$lang['Lock'] = " 잠 금 ";
$lang['Unlock'] = " 잠금해제";

$lang['Topics_Removed'] = "선택한 게시물이 성공적으로 데이타베이스에서 삭제되었습니다..";
$lang['Topics_Locked'] = "선택한 게시물을 잠궜습니다.";
$lang['Topics_Moved'] = "선택한 게시물을 성공적으로 이동하였습니다.";
$lang['Topics_Unlocked'] = "선택한 게시물을 잠금상태에서 해제하였습니다.";
$lang['No_Topics_Moved'] = "이미 이동한 게시물입니다.";

$lang['Confirm_delete_topic'] = "선택한게시물을 데이타베이스에서 삭제하시겠습니까..??";
$lang['Confirm_lock_topic'] = "선택한 게시물을 리플을달지 못하도록 잠그시겠습니까..??";
$lang['Confirm_unlock_topic'] = "선택한게시물을 잠금상태에서 해제하시겠습니까..??";
$lang['Confirm_move_topic'] = "선택한 포럼으로 게시물을 옮기시겠습니까..??";

$lang['Move_to_forum'] = "이동할 포럼선택";
$lang['Leave_shadow_topic'] = "원래있던 포럼에는 이동표식을 남겨둡니다..";

$lang['Split_Topic'] = "주제글 자르기 관리";
$lang['Split_Topic_explain'] = "아래 폼의 서식을 사용하여 주제글을 2(선택된 토론글에 개별적으로 토론글을 선택하는 것 ) 로 나눌수 있습니다.";
$lang['Split_title'] = "새로운 주제글 제목";
$lang['Split_forum'] = "새 주제글에 대한 포럼";
$lang['Split_posts'] = "자를 토론글 선택";
$lang['Split_after'] = "선택된 토론글 자름";
$lang['Topic_split'] = "선택된 주제글로 성공적으로 잘라졌습니다.";

$lang['Too_many_error'] = "주제글로 자르기위해 1 개의 토론글만 선택할수 있습니다.";

$lang['None_selected'] = "이 기능을 위해 어떤 주제글도 선택하지 않으셨습니다..돌아가서 다시 1 개만 선택하세요.";
$lang['New_forum'] = "새 포럼";

$lang['This_posts_IP'] = "이 글 작성자 IP";
$lang['Other_IP_this_user'] = "이 사용자의 다른 IP를 전송하였습니다.";
$lang['Users_this_IP'] = "이 IP로 작성된 사용자";
$lang['IP_info'] = "IP 정보";
$lang['Lookup_IP'] = "Look up IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "All times are %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Hours";
$lang['-11'] = "GMT - 11 Hours";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Hours";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 Hours";
$lang['-3.5'] = "GMT - 3.5 Hours";
$lang['-3'] = "GMT - 3 Hours";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 Hours";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT + 3 Hours";
$lang['3.5'] = "GMT + 3.5 Hours";
$lang['4'] = "GMT + 4 Hours";
$lang['4.5'] = "GMT + 4.5 Hours";
$lang['5'] = "GMT + 5 Hours";
$lang['5.5'] = "GMT + 5.5 Hours";
$lang['6'] = "GMT + 6 Hours";
$lang['7'] = "GMT + 7 Hours";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 Hours";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 Hours";
$lang['12'] = "GMT + 12 Hours";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 hours) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 hours) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 hours) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 hours) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 hours) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 hours) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 hours) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 hours) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 hours) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 hours) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 hours) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 hours) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 hours) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 hours) Cairo, Helsinki, Kaliningrad, South Africa, Warsaw";
$lang['tz']['3'] = "(GMT +3:00 hours) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 hours) Tehran";
$lang['tz']['4'] = "(GMT +4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 hours) Kabul";
$lang['tz']['5'] = "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 hours) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 hours) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 hours) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 hours) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 hours) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 hours) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 hours) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "일요일";
$lang['days_long'][1] = "월요일";
$lang['days_long'][2] = "화요일";
$lang['days_long'][3] = "수요일";
$lang['days_long'][4] = "목요일";
$lang['days_long'][5] = "금요일";
$lang['days_long'][6] = "토요일";

$lang['days_short'][0] = "일";
$lang['days_short'][1] = "월";
$lang['days_short'][2] = "화";
$lang['days_short'][3] = "수";
$lang['days_short'][4] = "목";
$lang['days_short'][5] = "금";
$lang['days_short'][6] = "토 ";

$lang['months_long'][0] = "1월";
$lang['months_long'][1] = "2월";
$lang['months_long'][2] = "3월";
$lang['months_long'][3] = "4월";
$lang['months_long'][4] = "5월";
$lang['months_long'][5] = "6월";
$lang['months_long'][6] = "7월";
$lang['months_long'][7] = "8월";
$lang['months_long'][8] = "9월";
$lang['months_long'][9] = "10월";
$lang['months_long'][10] = "11월";
$lang['months_long'][11] = "12월";

$lang['months_short'][0] = "1";
$lang['months_short'][1] = "2";
$lang['months_short'][2] = "3";
$lang['months_short'][3] = "4";
$lang['months_short'][4] = "5";
$lang['months_short'][5] = "6";
$lang['months_short'][6] = "7";
$lang['months_short'][7] = "8";
$lang['months_short'][8] = "9";
$lang['months_short'][9] = "10";
$lang['months_short'][10] = "11";
$lang['months_short'][11] = "12";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = " 정 보 ";
$lang['Critical_Information'] = "중요한 정보";

$lang['General_Error'] = "일반적인  Error";
$lang['Critical_Error'] = "심각한 Error";
$lang['An_error_occured'] = "Error가 발생했습니다.";
$lang['A_critical_error'] = "심각한 Error가 발생했습니다.";

//
// That's all Folks!
// -------------------------------------------------

?>