<?php
/***************************************************************************
 *                            lang_main.php [Korean]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.85.2.4 2002/06/23 02:47:56 dougk_ff7 Exp $
 *
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
// Add your details here if wanted, e.g. Name, username, email address, website
// 2002/08/28 Translated by TankTonk
// 2002/12/17 updated by Soon-Son Kwon(kss@kldp.org)
//

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'euc-kr';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] =  'Y년 n월 j일'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
// $lang['TRANSLATION'] = 'Translated by TankTonk';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = '게시판';
$lang['Category'] = '분류';
$lang['Topic'] = '주제';
$lang['Topics'] = '주제';
$lang['Replies'] = '답변';
$lang['Views'] = '조회';
$lang['Post'] = '올리기';
$lang['Posts'] = '올린 글';
$lang['Posted'] = '올려짐';
$lang['Username'] = '사용자 이름(id)';
$lang['Password'] = '비밀번호';
$lang['Email'] = '이메일';
$lang['Poster'] = '올린사람';
$lang['Author'] = '글쓴이';
$lang['Time'] = '시간';
$lang['Hours'] = '시간';
$lang['Message'] = '메시지';

$lang['1_Day'] = '1일';
$lang['7_Days'] = '7일';
$lang['2_Weeks'] = '2주';
$lang['1_Month'] = '1달';
$lang['3_Months'] = '3달';
$lang['6_Months'] = '6달';
$lang['1_Year'] = '1년';

$lang['Go'] = '가기';
$lang['Jump_to'] = '건너뛰기';
$lang['Submit'] = '제출';
$lang['Reset'] = '리셋';
$lang['Cancel'] = '취소';
$lang['Preview'] = '미리보기';
$lang['Confirm'] = '확인';
$lang['Spellcheck'] = '스펠체크';
$lang['Yes'] = '예';
$lang['No'] = '아니오';
$lang['Enabled'] = '사용가능';
$lang['Disabled'] = '사용정지';
$lang['Error'] = '에러';

$lang['Next'] = '다음';
$lang['Previous'] = '이전';
$lang['Goto_page'] = '페이지로';
$lang['Joined'] = '가입';
$lang['IP_Address'] = 'IP 주소';

$lang['Select_forum'] = '게시판 선택';
$lang['View_latest_post'] = '최근 글 보기';
$lang['View_newest_post'] = '새 글 보기';
$lang['Page_of'] = '페이지 <b>%d</b> 중 <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ 번호';
$lang['AIM'] = 'AIM 주소';
$lang['MSNM'] = 'MSN 메신저';
$lang['YIM'] = 'Yahoo 메신저';

$lang['Forum_Index'] = '%s 게시판 인덱스';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = '글 쓰기';
$lang['Reply_to_topic'] = '답변 달기';
$lang['Reply_with_quote'] = '인용과 함께 답변';

$lang['Click_return_topic'] = '주제로 돌아가려면 %s여기%s를 클릭하십시오'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = '다시 시도하시려면 %s여기%s를 클릭하십시오';
$lang['Click_return_forum'] = '게시판으로 돌아가려면 %s여기%s를 클릭하십시오';
$lang['Click_view_message'] = '귀하의 메시지를 보려면 %s여기%s를 클릭하십시오';
$lang['Click_return_modcp'] = '관리자 제어판으로 돌아가려면 %s여기%s를 클릭하십시오';
$lang['Click_return_group'] = '그룹 정보로 돌아가려면 %s여기%s를 클릭하십시오';

$lang['Admin_panel'] = '운영자 제어판으로 감';

$lang['Board_disable'] = '죄송합니다만 지금은 게시판을 사용하실 수 없습니다. 나중에 다시 시도해 보십시오.';


//
// Global Header strings
//
 $lang['Registered_users'] = '등록 사용자:';
$lang['Browsing_forum'] = '이 게시판을 사용하고 있는 사용자:';
$lang['Online_users_zero_total'] = '총 <b>0</b>명의 사용자가 접속중 :: ';
$lang['Online_users_total'] = '총 <b>%d</b>명이 접속중 :: ';
$lang['Online_user_total'] = '총 <b>%d</b>명이 접속중 :: ';
$lang['Reg_users_zero_total'] = '등록 사용자 0명, ';
$lang['Reg_users_total'] = '등록 사용자 %d명, ';
$lang['Reg_user_total'] = '등록 사용자 %d명, ';
$lang['Hidden_users_zero_total'] = '잠수 0명 및';
$lang['Hidden_user_total'] = '잠수 %d명 및 ';
$lang['Hidden_users_total'] = '잠수 %d명 및 ';
$lang['Guest_users_zero_total'] = '손님 0명';
$lang['Guest_users_total'] = '손님 %d명';
$lang['Guest_user_total'] = '손님 %d명';
$lang['Record_online_users'] = '동시 사용자 최다기록: <b>%s</b>명(%s)'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%s운영자%s';
$lang['Mod_online_color'] = '%s관리자%s';

$lang['You_last_visit'] = '마지막 방문은 %s'; // %s replaced by date/time
$lang['Current_time'] = '현재 시간은 %s'; // %s replaced by time

$lang['Search_new'] = '새로 올라온 글 보기';
$lang['Search_your_posts'] = '내가 올린 글 찾기';
$lang['Search_unanswered'] = '답변이 없는 글 찾기';

$lang['Register'] = '사용자 등록하기';
$lang['Profile'] = '개인 정보';
$lang['Edit_profile'] = '개인 정보 수정';
$lang['Search'] = '검색';
$lang['Memberlist'] = '멤버리스트';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'BBCode 설명서';
$lang['Usergroups'] = '사용자 그룹';
$lang['Last_Post'] = '마지막 글';
$lang['Moderator'] = '관리자';
$lang['Moderators'] = '관리자';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = '사용자가 올린 글은 <b>0</b>개 입니다'; // Number of posts
$lang['Posted_articles_total'] = '사용자가 올린 글은 총 <b>%d</b>개 입니다'; // Number of posts
$lang['Posted_article_total'] = '사용자가 올린 글은 총 <b>%d</b>개 입니다'; // Number of posts
$lang['Registered_users_zero_total'] = '등록된 사용자는 <b>0</b>명 입니다'; // # registered users
$lang['Registered_users_total'] = '등록된 사용자는 <b>%d</b>명 입니다'; // # registered users
$lang['Registered_user_total'] = '등록된 사용자는 <b>%d</b>명 입니다'; // # registered users
$lang['Newest_user'] = '최근에 등록한 사용자는 <b>%s%s%s</b> 입니다'; // a href, username, /a

$lang['No_new_posts_last_visit'] = '마지막 방문 이후로 새로 올라온 글이 없습니다';
$lang['No_new_posts'] = '새로운 글 없음';
$lang['New_posts'] = '새로운 글';
$lang['New_post'] = '새로운 글';
$lang['No_new_posts_hot'] = '새로운 글 없음 [ 인기 ]';
$lang['New_posts_hot'] = '새로운 글 [ 인기 ]';
$lang['No_new_posts_locked'] = '새로운 글 없음 [ 잠김 ]';
$lang['New_posts_locked'] = '새로운 글 [ 잠김 ]';
$lang['Forum_is_locked'] = '잠겨 있는 게시판입니다.';


//
// Login
//
$lang['Enter_password'] = '로그인하려면 사용자 이름과 비밀번호를 입력하세요';
$lang['Login'] = '로그인';
$lang['Logout'] = '로그아웃';

$lang['Forgotten_password'] = '비밀번호를 잊었어요';

$lang['Log_me_in'] = '자동 로그인';

$lang['Error_login'] = '사용자 이름이 틀렸거나 비밀번호가 틀렸습니다';


//
// Index page
//
$lang['Index'] = '인덱스';
$lang['No_Posts'] = '글 없음';
$lang['No_forums'] = '이 게시판에는 게시판이 없습니다';

$lang['Private_Message'] = '비공개 메시지';
$lang['Private_Messages'] = '비공개 메시지';
$lang['Who_is_Online'] = '온라인 사용자';

$lang['Mark_all_forums'] = '모든 게시판을 읽은 상태로 표시';
$lang['Forums_marked_read'] = '모든 게시판을 읽은 상태로 표시했습니다';


//
// Viewforum
//
$lang['View_forum'] = '게시판 보기';

$lang['Forum_not_exist'] = '선택한 게시판은 존재하지 않습니다';
$lang['Reached_on_error'] = '에러로 이 페이지에 오셨습니다';

$lang['Display_topics'] = '이전 주제 표시';
$lang['All_Topics'] = '모든 주제';

$lang['Topic_Announcement'] = '<b>공지사항:</b>';
$lang['Topic_Sticky'] = '<b>끈적이:</b>';
$lang['Topic_Moved'] = '<b>이동되었음:</b>';
$lang['Topic_Poll'] = '<b>[ 투표 ]</b>';

$lang['Mark_all_topics'] = '모든 글을 읽은 상태로 표시';
$lang['Topics_marked_read'] = '이 게시판의 주제가 읽은 상태로 표시되었습니다';

$lang['Rules_post_can'] = '새로운 주제를 올릴 수 <b>있습니다</b>';
$lang['Rules_post_cannot'] = '새로운 주제를 올릴 수 <b>없습니다</b>';
$lang['Rules_reply_can'] = '답글을 올릴 수 <b>있습니다</b>';
$lang['Rules_reply_cannot'] = '답글을 올릴 수 <b>없습니다</b>';
$lang['Rules_edit_can'] = '주제를 수정할 수 <b>있습니다</b>';
$lang['Rules_edit_cannot'] = '주제를 수정할 수 <b>없습니다</b>';
$lang['Rules_delete_can'] = '올린 글을 삭제할 수 <b>있습니다</b>';
$lang['Rules_delete_cannot'] = '올린 글을 삭제할 수 <b>없습니다</b>';
$lang['Rules_vote_can'] = '투표를 할 수 <b>있습니다</b>';
$lang['Rules_vote_cannot'] = '투표를 할 수 <b>없습니다</b>';
$lang['Rules_moderate'] = '%s이 게시판을 관리할 수%s <b>있습니다</b>'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = '이 게시판에는 올려진 글이 없습니다<br /><b>글 쓰기</b> 링크를 클릭하여 글을 올리십시오';


//
// Viewtopic
//
$lang['View_topic'] = '주제 보기';

$lang['Guest'] = '손님';
$lang['Post_subject'] = '주제';
$lang['View_next_topic'] = '다음 주제 보기';
$lang['View_previous_topic'] = '이전 주제 보기';
$lang['Submit_vote'] = '투표 하기';
$lang['View_results'] = '결과 보기';

$lang['No_newer_topics'] = '이 게시판에는 더 새로운 주제가 없습니다';
$lang['No_older_topics'] = '이 게시판에는 더 오래된 주제가 없습니다';
$lang['Topic_post_not_exist'] = '요청한 주제나 글이 존재하지 않습니다';
$lang['No_posts_topic'] = '이 토픽에 대한 글이 존재하지 않습니다';

$lang['Display_posts'] = '이전 글 표시';
$lang['All_Posts'] = '모든 글';
$lang['Newest_First'] = '새로운 글 먼저';
$lang['Oldest_First'] = '오래된 글 먼저';

$lang['Back_to_top'] = '위로';

$lang['Read_profile'] = '사용자 정보 보기';
$lang['Send_email'] = '사용자에게 이메일 보내기';
$lang['Visit_website'] = '글 올린이의 웹사이트 방문';
$lang['ICQ_status'] = 'ICQ 상태';
$lang['Edit_delete_post'] = '글 편집/삭제';
$lang['View_IP'] = '글 올린이의 IP 주소 보기';
$lang['Delete_post'] = '글 삭제';

$lang['wrote'] = '씀'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = '인용'; // comes before bbcode quote output.
$lang['Code'] = '코드'; // comes before bbcode code output.

$lang['Edited_time_total'] = '%s 가 %s에 수정함, 총 %d 번 수정됨'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = '%s 가 %s에 수정함, 총 %d 번 수정됨'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = '이 주제를 잠금';
$lang['Unlock_topic'] = '이 주제의 잠금을 해제';
$lang['Move_topic'] = '이 주제를 이동함';
$lang['Delete_topic'] = '이 주제를 삭제함';
$lang['Split_topic'] = '이 주제를 분리함';

$lang['Stop_watching_topic'] = '이 주제의 감시를 해제함';
$lang['Start_watching_topic'] = '이 주제에 대한 답글을 감시함';
$lang['No_longer_watching'] = '이 주제를 더 이상 감시하지 않습니다';
$lang['You_are_watching'] = '이 주제를 감시하고 있습니다';

$lang['Total_votes'] = '총 투표수';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = '메시지 본문';
$lang['Topic_review'] = '주제 검토';

$lang['No_post_mode'] = '모드가 지정되지 않았습니다'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = '새로운 주제 올리기';
$lang['Post_a_reply'] = '답글 올리기';
$lang['Post_topic_as'] = '주제 올리기';
$lang['Edit_Post'] = '글 편집';
$lang['Options'] = '옵션';

$lang['Post_Announcement'] = '공지 사항';
$lang['Post_Sticky'] = '끈적이';
$lang['Post_Normal'] = '일반';

$lang['Confirm_delete'] = '이 글을 삭제하시겠습니까?';
$lang['Confirm_delete_poll'] = '이 투표를 삭제하시겠습니까?';

$lang['Flood_Error'] = '또 다른 글을 금방 올릴 수는 없으므로 잠시후에 재시도 하십시오';
$lang['Empty_subject'] = '새로운 주제를 올리려면 주제를 지정해야 합니다';
$lang['Empty_message'] = '글을 올리려면 본문을 입력하십시오';
$lang['Forum_locked'] = '이 게시판은 잠겼으므로 글을 올리거나, 답변을 하거나 수정을 할 수 없습니다';
$lang['Topic_locked'] = '이 주제는 잠겼으므로 답변을 하거나 수정을 할 수 없습니다';
$lang['No_post_id'] = '편집하려는 글을 먼저 선택해야 합니다';
$lang['No_topic_id'] = '답변하려는 주제를 먼저 선택해야 합니다';
$lang['No_valid_mode'] = '글 올리기, 편집 혹은 메시지 인용만 할 수 있읍니다, 되돌아가서 재시도 하십시오';
$lang['No_such_post'] = '그런 글은 없습니다, 되돌아가서 재시도 하십시오';
$lang['Edit_own_posts'] = '오직 자신의 글만을 수정할 수 있습니다';
$lang['Delete_own_posts'] = '오직 자신의 글만을 삭제할 수 있습니다';
$lang['Cannot_delete_replied'] = '답글이 있는 게시물은 삭제할 수 없습니다';
$lang['Cannot_delete_poll'] = '현재 진행중인 투표는 삭제할 수 없습니다';
$lang['Empty_poll_title'] = '투표에 대한 제목을 입력해야 합니다';
$lang['To_few_poll_options'] = '최소한 두가지 투표 옵션을 입력해야 합니다';
$lang['To_many_poll_options'] = '옵션을 너무 많이 지정하였습니다';
$lang['Post_has_no_poll'] = '이 글에는 투표가 없습니다';
$lang['Already_voted'] = '이미 투표하셨습니다';
$lang['No_vote_option'] = '투표할 때 옵션을 지정해야 합니다';

$lang['Add_poll'] = '투표 넣기';
$lang['Add_poll_explain'] = '투표 넣기를 원하지 않으면 빈칸으로 비워두십시오';
$lang['Poll_question'] = '투표 질문';
$lang['Poll_option'] = '투표 옵션';
$lang['Add_option'] = '옵션 추가';
$lang['Update'] = '갱신';
$lang['Delete'] = '삭제';
$lang['Poll_for'] = '투표 진행';
$lang['Days'] = '일'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ 무기한 투표를 원하면 0 혹은 빈칸으로 하십시오 ]';
$lang['Delete_poll'] = '투표 삭제';

$lang['Disable_HTML_post'] = 'HTML 사용 불가';
$lang['Disable_BBCode_post'] = 'BBCode 사용 불가';
$lang['Disable_Smilies_post'] = '스마일 사용 불가';

$lang['HTML_is_ON'] = 'HTML <u>사용</u>';
$lang['HTML_is_OFF'] = 'HTML <u>사용 안함</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s <u>사용</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s <u>사용 안함</u>';
$lang['Smilies_are_ON'] = '스마일 <u>사용</u>';
$lang['Smilies_are_OFF'] = '스마일 <u>사용 안함</u>';

$lang['Attach_signature'] = '서명 붙이기 (서명은 개인 정보에서 지정)';
$lang['Notify'] = '답글이 올라오면 통보함';
$lang['Delete_post'] = '글 삭제';

$lang['Stored'] = '메시지가 성공적으로 입력되었습니다';
$lang['Deleted'] = '메시지가 성공적으로 삭제되었습니다';
$lang['Poll_delete'] = '투표가 성공적으로 삭제되었습니다';
$lang['Vote_cast'] = '투표를 하셨습니다';

$lang['Topic_reply_notification'] = '답글 통보';

$lang['bbcode_b_help'] = '볼드 문자: [b]문자[/b]  (alt+b)';
$lang['bbcode_i_help'] = '이탤릭 문자: [i]문자[/i]  (alt+i)';
$lang['bbcode_u_help'] = '밑줄 문자: [u]문자[/u]  (alt+u)';
$lang['bbcode_q_help'] = '인용 문구: [quote]문구[/quote]  (alt+q)';
$lang['bbcode_c_help'] = '코드 표시: [code]코드[/code]  (alt+c)';
$lang['bbcode_l_help'] = '리스트: [list]문자[/list] (alt+l)';
$lang['bbcode_o_help'] = '정렬 리스트: [list=]문자[/list]  (alt+o)';
$lang['bbcode_p_help'] = '이미지 넣기: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'URL 넣기: [url]http://url[/url] 또는 [url=http://url]URL 문서[/url]  (alt+w)';
$lang['bbcode_a_help'] = '모든 열린 BBCode 태그 닫기';
$lang['bbcode_s_help'] = '글꼴색: [color=red]문자[/color]  팁: 사용방식 color=#FF0000';
$lang['bbcode_f_help'] = '글꼴크기: [size=x-small]작은 문자[/size]';

$lang['Emoticons'] = '이모티콘';
$lang['More_emoticons'] = '이모티콘 보기';

$lang['Font_color'] = '글꼴색';
$lang['color_default'] = '초기값';
$lang['color_dark_red'] = '어두운 적색';
$lang['color_red'] = '적색';
$lang['color_orange'] = '오렌지색';
$lang['color_brown'] = '갈색';
$lang['color_yellow'] = '노란색';
$lang['color_green'] = '녹색';
$lang['color_olive'] = '올리브색';
$lang['color_cyan'] = '하늘색';
$lang['color_blue'] = '청색';
$lang['color_dark_blue'] = '어두운 청색';
$lang['color_indigo'] = '남색';
$lang['color_violet'] = '자주색';
$lang['color_white'] = '흰색';
$lang['color_black'] = '흑색';

$lang['Font_size'] = '글꼴 크기';
$lang['font_tiny'] = '아주작게';
$lang['font_small'] = '작게';
$lang['font_normal'] = '보통';
$lang['font_large'] = '크게';
$lang['font_huge'] = '아주크게';

$lang['Close_Tags'] = '태그 닫기';
$lang['Styles_tip'] = '팁: 스타일은 선택된 문자에 즉시 적용이 가능합니다';


//
// Private Messaging
//
$lang['Private_Messaging'] = '비공개 메시지';

$lang['Login_check_pm'] = '비공개 메시지를 확인하려면 로그인하십시오';
$lang['New_pms'] = '%d 개의 새로운 메시지가 있습니다'; // You have 2 new messages
$lang['New_pm'] = '%d 개의 새로운 메시지가 있습니다'; // You have 1 new message
$lang['No_new_pm'] = '새로운 메시지가 없습니다';
$lang['Unread_pms'] = '%d 개의 읽지 않은 메시지가 있습니다';
$lang['Unread_pm'] = '%d 개의 읽지 않은 메시지가 있습니다';
$lang['No_unread_pm'] = '읽지 않은 메시지가 없습니다';
$lang['You_new_pm'] = '새로운 비공개 메시지가 있습니다';
$lang['You_new_pms'] = '새로운 비공개 메시지가 있습니다';
$lang['You_no_new_pm'] = '새로운 비공개 메시지가 없습니다';

$lang['Unread_message'] = '읽지 않은 메시지';
$lang['Read_message'] = '읽은 메시지';

$lang['Read_pm'] = '메시지 읽기';
$lang['Post_new_pm'] = '메시지 올리기';
$lang['Post_reply_pm'] = '메시지에 답변하기';
$lang['Post_quote_pm'] = '메시지 인용하기';
$lang['Edit_pm'] = '메시지 편집';

$lang['Inbox'] = '받은 편지함';
$lang['Outbox'] = '보낼 편지함';
$lang['Savebox'] = '저장 편지함';
$lang['Sentbox'] = '보낸 편지함';
$lang['Flag'] = '플래그';
$lang['Subject'] = '주제';
$lang['From'] = 'From';
$lang['To'] = 'To';
$lang['Date'] = '날짜';
$lang['Mark'] = '표시';
$lang['Sent'] = '보내짐';
$lang['Saved'] = '저장됨';
$lang['Delete_marked'] = '표시된 것 삭제';
$lang['Delete_all'] = '전부 삭제';
$lang['Save_marked'] = '표시된 것 저장';
$lang['Save_message'] = '메시지 저장';
$lang['Delete_message'] = '메시지 삭제';

$lang['Display_messages'] = '이전 메시지 표시'; // Followed by number of days/weeks/months
$lang['All_Messages'] = '모든 메시지';

$lang['No_messages_folder'] = '이 폴더에는 메시지가 없습니다';

$lang['PM_disabled'] = '이 게시판에서는 비공개 메시지를 사용할수 없도록 설정되어 있습니다';
$lang['Cannot_send_privmsg'] = '운영자가 귀하의 메시지 보내기 기능을 막아 놓았습니다';
$lang['No_to_user'] = '메시지를 보내려면 사용자 이름을 지정하십시오';
$lang['No_such_user'] = '존재하지 않는 사용자입니다';

$lang['Disable_HTML_pm'] = '이 메시지에 HTML 사용하지 않음';
$lang['Disable_BBCode_pm'] = '이 메시지에 BBCode 사용하지 않음';
$lang['Disable_Smilies_pm'] = '이 메시지에 스마일 사용하지 않음';

$lang['Message_sent'] = '메시지가 보내졌습니다';

$lang['Click_return_inbox'] = '받은 편지함으로 가려면 %s여기%s를 클릭하세요';
$lang['Click_return_index'] = '인덱스로 가려면 %s여기%s를 클릭하세요';

$lang['Send_a_new_message'] = '새로운 비공개 메시지 보내기';
$lang['Send_a_reply'] = '비공개 메시지에 대한 답글';
$lang['Edit_message'] = '비공개 메시지 편집';

$lang['Notification_subject'] = '새로운 비공개 메시지가 도착했습니다';

$lang['Find_username'] = '사용자 이름 찾기';
$lang['Find'] = '찾기';
$lang['No_match'] = '찾기 실패';

$lang['No_post_id'] = '글 ID 가 지정되지 않았슴';
$lang['No_such_folder'] = '존재하지 않는 폴더';
$lang['No_folder'] = '폴더가 지정되지 않았음';

$lang['Mark_all'] = '모두 표시';
$lang['Unmark_all'] = '모두 표시 해제';

$lang['Confirm_delete_pm'] = '이 메시지를 삭제하겠습니까?';
$lang['Confirm_delete_pms'] = '이 메시지를 삭제하겠습니까?';

$lang['Inbox_size'] = '받은 편지함이 %d%% 찼습니다'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = '보낸 편지함이 %d%% 찼습니다';
$lang['Savebox_size'] = '저장 편지함이 %d%% 찼습니다';

$lang['Click_view_privmsg'] = '받은 편지함으로 가려면 %s여기%s를 클릭하십시오';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = '정보 보기:: %s'; // %s is username
$lang['About_user'] = '%s의 모든 정보'; // %s is username

$lang['Preferences'] = '선택';
$lang['Items_required'] = ' * 로 표시된 부분은 필수 항목입니다';
$lang['Registration_info'] = '등록 정보';
$lang['Profile_info'] = '개인 정보';
$lang['Profile_info_warn'] = '공개될 정보';
$lang['Avatar_panel'] = '아바타 제어판';
$lang['Avatar_gallery'] = '아바타 모음';

$lang['Website'] = '홈페이지';
$lang['Location'] = '위치';
$lang['Contact'] = '연락하기:';
$lang['Email_address'] = '이메일 주소';
$lang['Email'] = '이메일';
$lang['Send_private_message'] = '비밀 메시지 보내기';
$lang['Hidden_email'] = '[ 숨김 ]';
$lang['Search_user_posts'] = '이 사용자의 글 찾기';
$lang['Interests'] = '관심사';
$lang['Occupation'] = '직업';
$lang['Poster_rank'] = '등급';

$lang['Total_posts'] = '총 글수';
$lang['User_post_pct_stats'] = '전부의 %.2f%%'; // 1.25% of total
$lang['User_post_day_stats'] = '하루당 %.2f 건'; // 1.5 posts per day
$lang['Search_user_posts'] = '%s님이 올린 글 모두 찾기'; // Find all posts by username

$lang['No_user_id_specified'] = '존재하지 않는 사용자입니다';
$lang['Wrong_Profile'] = '타인의 개인 정보는 수정할 수 없습니다.';

$lang['Only_one_avatar'] = '오직 한 종류의 아바타만을 지정할 수 있습니다';
$lang['File_no_data'] = '지정한 URL에는 아무런 데이터도 없습니다';
$lang['No_connection_URL'] = '지정한 URL에 연결할 수 없습니다';
$lang['Incomplete_URL'] = 'URL이 유효하지 않습니다';
$lang['Wrong_remote_avatar_format'] = '원격 아바타의 URL이 유효하지 않습니다';
$lang['No_send_account_inactive'] = '계정이 현재 정지중이기 때문에 비밀번호를 읽을수 없습니다. 자세한 내용은 운영자에게 연락하십시오';

$lang['Always_smile'] = '항상 스마일 사용';
$lang['Always_html'] = '항상 HTML 허용';
$lang['Always_bbcode'] = '항상 BBCode 허용';
$lang['Always_add_sig'] = '항상 서명 첨부';
$lang['Always_notify'] = '항상 답글시 통보';
$lang['Always_notify_explain'] = '내가 올린 주제에 대한 답글이 있을때 이메일 보내기. 글을 올릴 때 변경 가능함';

$lang['Board_style'] = '게시판 형태';
$lang['Board_lang'] = '게시판 언어';
$lang['No_themes'] = '데이터베이스에 주제 없음';
$lang['Timezone'] = '시간대';
$lang['Date_format'] = '날짜 형식';
$lang['Date_format_explain'] = '사용된 형식은 PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a>와 동일한 기능입니다';
$lang['Signature'] = '서명';
$lang['Signature_explain'] = '이것은 올리는 글에 첨부될 사인 문구입니다. 제한은 %d 글자입니다';
$lang['Public_view_email'] = '항상 내 이메일 주소 보이기';

$lang['Current_password'] = '현재 비밀번호';
$lang['New_password'] = '새 비밀번호';
$lang['Confirm_password'] = '비밀번호 확인';
$lang['Confirm_password_explain'] = '비밀번호나 이메일 주소를 바꾸려면 현재 비밀번호를 확인해야 합니다';
$lang['password_if_changed'] = '새 비밀번호를 입력하세요';
$lang['password_confirm_if_changed'] = '위의 새 비밀번호를 다시 입력하세요';

$lang['Avatar'] = '아바타';
$lang['Avatar_explain'] = '자신을 표시하는 작은 그래픽 이미지를 보여줍니다. 한번에 하나의 이미지만 허용되고 제한폭은 %d 픽셀, 제한높이는 %d 픽셀이며 파일 크기 제한은  %dkB 입니다.';
$lang['Upload_Avatar_file'] = '내 컴퓨터로부터 아바타 업로드';
$lang['Upload_Avatar_URL'] = 'URL로 아바타 업로드';
$lang['Upload_Avatar_URL_explain'] = '아바타 이미지가 있는 URL을 입력하세요, 이 사이트로 복사됩니다.';
$lang['Pick_local_Avatar'] = '아바타 모음에서 선택하기';
$lang['Link_remote_Avatar'] = '다른 사이트의 아바타 링크';
$lang['Link_remote_Avatar_explain'] = '링크하려는 아바타 이미지가 있는 URL을 입력하세요.';
$lang['Avatar_URL'] = '아바타 이미지 URL';
$lang['Select_from_gallery'] = '아바타 모음에서 선택';
$lang['View_avatar_gallery'] = '모음 보이기';

$lang['Select_avatar'] = '아바타 선택';
$lang['Return_profile'] = '아바타 취소';
$lang['Select_category'] = '카테고리 선택';

$lang['Delete_Image'] = '이미지 삭제';
$lang['Current_Image'] = '현재 이미지';

$lang['Notify_on_privmsg'] = '새로운 비밀 메시지가 오면 이메일로 통보하기';
$lang['Popup_on_privmsg'] = '새로운 비밀 메시지가 오면 팝업창 띄우기';
$lang['Popup_on_privmsg_explain'] = '새로운 비밀 메시지가 도착해 있으면 사이트 접속시 팝업창이 뜹니다';
$lang['Hide_user'] = '접속 상태 숨기기';

$lang['Profile_updated'] = '개인 정보가 업데이트되었습니다';
$lang['Profile_updated_inactive'] = '개인 정보가 업데이트되었습니다만 중요한 변경으로 계정이 중지되었습니다. 이메일을 확인하여 계정을 다시 살려야 합니다. 만약 운영자의 조치가 필요한 것이라면 운영자가 필요한 조치를 취할 때까지 기다리십시오';

$lang['Password_mismatch'] = '입력한 비밀번호가 일치하지 않습니다';
$lang['Current_password_mismatch'] = '입력한 현재 비밀번호가 데이터베이스에 저장된 것과 일치하지 않습니다';
$lang['Password_long'] = '비밀번호는 32자 이내여야 합니다';
$lang['Username_taken'] = '이미 사용중인 사용자 이름입니다';
$lang['Username_invalid'] = '사용자 이름에 쓰일 수 없는 문자를 넣으셨습니다';
$lang['Username_disallowed'] = '허용되지 않는 사용자 이름입니다';
$lang['Email_taken'] = '이메일 주소가 이미 등록된 사용자의 것입니다';
$lang['Email_banned'] = '금지된 이메일 주소입니다';
$lang['Email_invalid'] = '유효하지 않은 이메일 주소입니다';
$lang['Signature_too_long'] = '서명이 너무 깁니다';
$lang['Fields_empty'] = '필수사항을 기재하여야 합니다';
$lang['Avatar_filetype'] = '아바타는 .jpg, .gif 혹은 .png 여야 합니다';
$lang['Avatar_filesize'] = '아바타 이미지의 파일 크기는 %d kB 보다 작아야 합니다'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = '아바타의 크기는 가로 %d 픽셀, 세로 %d 픽셀보다 작아야 합니다';

$lang['Welcome_subject'] = '%s 게시판에 오신것을 환영합니다'; // Welcome to my.com forums
$lang['New_account_subject'] = '새로운 사용자 계정';
$lang['Account_activated_subject'] = '계정이 활성화되었슴';

$lang['Account_added'] = '등록해 주셔서 감사합니다, 계정이 새로 만들어졌으므로 사용자 이름과 비밀번호를 이용하여 로그인하십시오';
$lang['Account_inactive'] = '계정이 만들어졌습니다만, 계정 인증을 해야 합니다, 인증키가 지정한 이메일 주소로 보내졌으므로 이메일을 확인하여 인증을 마무리하십시오.';
$lang['Account_inactive_admin'] = '계정이 만들어졌습니다만, 관리자가 계정을 승인해야 사용하실 수 있습니다. 관리자에게 이메일이 보내졌으니 계정이 승인되면 통보가 갈 것입니다';
$lang['Account_active'] = '계정이 인증되었습니다. 등록해 주셔서 감사합니다';
$lang['Account_active_admin'] = '계정이 인증되었습니다';
$lang['Reactivate'] = '계정을 재인증하십시오!';
$lang['Already_activated'] = '이미 계정 인증을 하셨습니다';
$lang['COPPA'] = '계정이 만들어졌으나 승인을 필요로 하므로 자세한 사항은 이메일을 확인해 주십시오.';

$lang['Registration'] = '등록 동의 조건';
$lang['Reg_agreement'] = '이곳은 서로의 지식과 정보를 교환하기 위한 곳입니다. 이곳은 누구나 가입하실 수 있으며 가입시 필요로 하는 필수 정보는 이곳에서 사용하실 ID와 비밀번호, 그리고 이메일 주소이고, 그 이외의 정보들은 본인의 선택에 따라 공개하지 않으셔도 이용에 아무런 문제가 없으며 특히 여러분의 <strong>이메일 주소는 사이트 전 공간에서 직접 노출되지 않습니다</strong>. <p>등록 사용자에게는 대략 다음과 같은 기능이 기본적으로 부여되므로 사이트를 훨씬 효과적이고 편리하게 이용하실 수 있습니다.</p> <li><strong>답글 통보 기능</strong>: 본인이 올린 글에 답글이 올라올 경우 바로 이메일을 통해 통보받을 수 있습니다.</li><li><strong>새로운 글 표시 기능</strong>: 마지막 방문 이후 새로 올라온 글만 골라서 읽을 수 있습니다.</li><li><strong>서명 및 아바타</strong>: 사용자의 개성을 살릴 수 있는 서명 기능과 아바타를 직접 설정할 수 있습니다.</li><li>그 외에도 사용자간 메시지 교환 및 사이트 이용에 관한 여러가지 사항들을 설정할 수 있습니다.</li> <p>상대방을 비방하는 글이나 광고, 욕설 및 기타 사이트 성격에 맞지 않는 글들은 사전 통보없이 운영자 혹은 각 게시판 관리자에 의해 삭제될 수도 있습니다. 이는 이곳을 좀더 좋은 환경으로 만들기 위한 최소한의 노력이므로 서로 예의를 지켜서 불미스러운 일이 생기지 않도록 함께 힘써 주시기를 부탁드립니다.</p>';

$lang['Agree_under_13'] = '본인은 13세 <b>미만</b>이며 상기 조건에 동의합니다';
$lang['Agree_over_13'] = '본인은 13세 <b>이상</b>이며 상기 조건에 동의합니다';
$lang['Agree_not'] = '조건에 동의하지 않습니다';

$lang['Wrong_activation'] = '인증키가 일치하지 않습니다';
$lang['Send_password'] = '새 비밀번호 받기';
$lang['Password_updated'] = '새 비밀번호가 만들어졌습니다 이메일을 확인하여 인증받으십시오';
$lang['No_email_match'] = '제공한 이메일 주소가 일치하지 않습니다';
$lang['New_password_activation'] = '새 비밀번호 인증';
$lang['Password_activated'] = '계정이 재인증되었습니다. 이메일로 받은 비밀번호로 로그인하십시오';

$lang['Send_email_msg'] = '이메일 메시지 보내기';
$lang['No_user_specified'] = '사용자가 지정되지 않았습니다';
$lang['User_prevent_email'] = '이 사용자는 이메일 수신을 거부하고 있습니다. 비공개 메시지 보내기를 시도하십시오';
$lang['User_not_exist'] = '존재하지 않는 사용자 입니다';
$lang['CC_email'] = '이메일을 자신에게도 보내기';
$lang['Email_message_desc'] = '이 메시지는 순수 문자로 보내지며 HTML이나 BBCode를 포함하지 않습니다. 회신 주소는 귀하의 이메일 주소로 됩니다.';
$lang['Flood_email_limit'] = '현재 또 다른 이메일을 보낼 수 없습니다. 나중에 다시 시도해 보세요';
$lang['Recipient'] = '수신자';
$lang['Email_sent'] = '이메일이 보내졌습니다';
$lang['Send_email'] = '이메일 보내기';
$lang['Empty_subject_email'] = '이메일의 제목을 지정하세요';
$lang['Empty_message_email'] = '이메일의 본문을 입력하세요';


//
// Memberslist
//
$lang['Select_sort_method'] = '정렬 방법 선택';
$lang['Sort'] = '정렬';
$lang['Sort_Top_Ten'] = '작성자 10순위';
$lang['Sort_Joined'] = '가입일';
$lang['Sort_Username'] = '사용자 이름';
$lang['Sort_Location'] = '위치';
$lang['Sort_Posts'] = '총글수';
$lang['Sort_Email'] = '이메일';
$lang['Sort_Website'] = '웹사이트';
$lang['Sort_Ascending'] = '올림';
$lang['Sort_Descending'] = '내림';
$lang['Order'] = 'Order';


//
// Group control panel
//
$lang['Group_Control_Panel'] = '그룹 제어판';
$lang['Group_member_details'] = '그룹 회원 정보';
$lang['Group_member_join'] = '그룹 가입';

$lang['Group_Information'] = '그룹 정보';
$lang['Group_name'] = '그룹 이름';
$lang['Group_description'] = '그룹 설명';
$lang['Group_membership'] = '그룹 회원제';
$lang['Group_Members'] = '그룹 회원';
$lang['Group_Moderator'] = '그룹 관리자';
$lang['Pending_members'] = '보류 회원';

$lang['Group_type'] = '그룹 유형';
$lang['Group_open'] = '공개 그룹';
$lang['Group_closed'] = '닫힌 그룹';
$lang['Group_hidden'] = '잠수 그룹';

$lang['Current_memberships'] = '현재 회원';
$lang['Non_member_groups'] = '비회원 그룹';
$lang['Memberships_pending'] = '회원제 보류';

$lang['No_groups_exist'] = '존재하는 그룹이 없음';
$lang['Group_not_exist'] = '존재하지 않는 그룹';

$lang['Join_group'] = '그룹 가입';
$lang['No_group_members'] = '이 그룹에는 회원이 없습니다';
$lang['Group_hidden_members'] = '이 그룹은 잠수했습니다, 회원 자격을 볼 수 없습니다';
$lang['No_pending_group_members'] = '이 그룹은 보류 회원이 없습니다';
$lang['Group_joined'] = '이 그룹에 성공적으로 가입 신청되었습니다.<br />그룹 관리자가 가입을 승인하면 통보가 갈 것입니다';
$lang['Group_request'] = '그룹 가입 신청이 접수되었습니다';
$lang['Group_approved'] = '요청이 승인되었습니다';
$lang['Group_added'] = '이 그룹에 가입되셨습니다';
$lang['Already_member_group'] = '이미 이 그룹 회원입니다';
$lang['User_is_member_group'] = '사용자는 이미 이 그룹의 회원입니다';
$lang['Group_type_updated'] = '그룹 유형 업데이트 성공';

$lang['Could_not_add_user'] = '존재하지 않는 사용자';
$lang['Could_not_anon_user'] = '무명인을 그룹 회원으로 할 수 없음';

$lang['Confirm_unsub'] = '이 그룹에서 탈퇴하겠습니까?';
$lang['Confirm_unsub_pending'] = '가입 신청이 아직 승인되지 않았습니다, 가입을 취소하겠습니까?';

$lang['Unsub_success'] = '이 그룹에서 탈퇴하셨습니다.';

$lang['Approve_selected'] = '선택된 사용자 승인';
$lang['Deny_selected'] = '선택된 사용자 거부';
$lang['Not_logged_in'] = '그룹에 가입하려면 로그인해야 합니다.';
$lang['Remove_selected'] = '선택된 사용자 삭제';
$lang['Add_member'] = '회원 추가';
$lang['Not_group_moderator'] = '이 그룹의 관리자가 아니므로 이 기능을 실행할 수 없습니다.';

$lang['Login_to_join'] = '그룹에 가입하거나 회원을 관리하려면 로그인하세요';
$lang['This_open_group'] = '공개 그룹입니다, 가입하려면 클릭하세요';
$lang['This_closed_group'] = '닫힌 그룹이므로 더이상 회원을 받지 않습니다';
$lang['This_hidden_group'] = '잠수 그룹이므로 자동 회원추가가 되지 않습니다';
$lang['Member_this_group'] = '귀하는 이 그룹의 회원입니다';
$lang['Pending_this_group'] = '귀하의 회원 자격이 보류 중입니다';
$lang['Are_group_moderator'] = '귀하는 그룹 관리자입니다';
$lang['None'] = '없음';

$lang['Subscribe'] = '신청';
$lang['Unsubscribe'] = '취소';
$lang['View_Information'] = '정보 보기';


//
// Search
//
$lang['Search_query'] = '검색';
$lang['Search_options'] = '검색 옵션';

$lang['Search_keywords'] = '키워드로 검색';
$lang['Search_keywords_explain'] = '<u>AND</u>를 사용하여 반드시 들어가야 할 단어를 지정할 수 있고, <u>OR</u>를 사용하여 들어가도 되는 단어를 지정하며 <u>NOT</u>을 사용하여 들어가서는 안되는 단어를 지정합니다. 와일드문자로 * 를 사용할 수 있습니다';
$lang['Search_author'] = '글쓴이로 검색';
$lang['Search_author_explain'] = '부분 일치를 위해 와일드 문자 사용';

$lang['Search_for_any'] = '입력된 형식을 이용해 검색';
$lang['Search_for_all'] = '모든 내용 검색';
$lang['Search_title_msg'] = '주제와 메시지 본문 검색';
$lang['Search_msg_only'] = '본문만 검색';

$lang['Return_first'] = '먼저 보여줄 글자수'; // followed by xxx characters in a select box
$lang['characters_posts'] = '게시물의 글자수';

$lang['Search_previous'] = '검색 한계 날짜'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = '검색 수단';
$lang['Sort_Time'] = '올린 시간';
$lang['Sort_Post_Subject'] = '올린 주제';
$lang['Sort_Topic_Title'] = '주제 제목';
$lang['Sort_Author'] = '글쓴이';
$lang['Sort_Forum'] = '게시판';

$lang['Display_results'] = '결과 보이기';
$lang['All_available'] = '모두 허용';
$lang['No_searchable_forums'] = '이 사이트에 있는 게시판을 검색할 권한이 없습니다';

$lang['No_search_match'] = '검색 조건에 맞는 결과가 없습니다';
$lang['Found_search_match'] = '검색 결과 %d 개 일치합니다'; // eg. Search found 1 match
$lang['Found_search_matches'] = '검색 결과 %d 개 일치합니다'; // eg. Search found 24 matches

$lang['Close_window'] = '창닫기';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = '이 게시판에는 오직 %s 만 공지사항을 올릴 수 있습니다';
$lang['Sorry_auth_sticky'] = '이 게시판에는 오직 %s 만 끈적이 메시지를 올릴 수 있습니다';
$lang['Sorry_auth_read'] = '이 게시판에서는 오직 %s 만 주제를 읽을 수 있습니다';
$lang['Sorry_auth_post'] = '이게시판에서는 오직 %s 만 주제를 올릴 수 있습니다';
$lang['Sorry_auth_reply'] = '이게시판에서는 오직 %s 만 답글을 올릴 수 있습니다';
$lang['Sorry_auth_edit'] = '이게시판에서는 오직 %s 만 올린 글을 수정할 수 있습니다';
$lang['Sorry_auth_delete'] = '이게시판에서는 오직 %s 만 올린 글을 삭제할 수 있습니다';
$lang['Sorry_auth_vote'] = '이게시판에서는 오직 %s 만 투표에 참여할 수 있습니다';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>무명 사용자</b>';
$lang['Auth_Registered_Users'] = '<b>등록 사용자</b>';
$lang['Auth_Users_granted_access'] = '<b>특별 허가를 받은 사용자</b>';
$lang['Auth_Moderators'] = '<b>관리자</b>';
$lang['Auth_Administrators'] = '<b>운영자</b>';

$lang['Not_Moderator'] = '귀하는 이 게시판의 관리자가 아닙니다';
$lang['Not_Authorised'] = '권한 없음';

$lang['You_been_banned'] = '귀하는 이 게시판에서 퇴출당했습니다<br />자세한 사항은 웹마스터나 게시판 운영자에게 연락하세요';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = '등록 사용자가 없으며 '; // There ae 5 Registered and
$lang['Reg_users_online'] = '등록 사용자가 %d 명이며 '; // There ae 5 Registered and
$lang['Reg_user_online'] = '등록 사용자가 %d 명이며'; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '잠수중인 온라인 사용자 없음'; // 6 Hidden users online
$lang['Hidden_users_online'] = '잠수중인 온라인 사용자 %d 명'; // 6 Hidden users online
$lang['Hidden_user_online'] = '잠수중인 온라인 사용자 %d 명'; // 6 Hidden users online
$lang['Guest_users_online'] = '온라인 손님 %d 명'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = '온라인 손님 없음'; // There are 10 Guest users online
$lang['Guest_user_online'] = '온라인 손님 %d 명'; // There is 1 Guest user online
$lang['No_users_browsing'] = '이 게시판에는 현재 사용자가 없습니다';

$lang['Online_explain'] = '이 데이터는 지난 5분 동안 활동했던 사용자들에 대한 것입니다';

$lang['Forum_Location'] = '게시판 위치';
$lang['Last_updated'] = '마지막 업데이트';

$lang['Forum_index'] = '게시판 인덱스';
$lang['Logging_on'] = '로그인';
$lang['Posting_message'] = '메시지 올리는중';
$lang['Searching_forums'] = '게시판 검색중';
$lang['Viewing_profile'] = '개인정보 보는중';
$lang['Viewing_online'] = '온라인 사용자 보는중';
$lang['Viewing_member_list'] = '회원 리스트 보는중';
$lang['Viewing_priv_msgs'] = '비공개 메시지 보는중';
$lang['Viewing_FAQ'] = 'FAQ 보는중';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = '관리자 제어판';
$lang['Mod_CP_explain'] = '아래 양식을 이용하여 이 게시판에 대한 대량 관리 작업을 할 수 있습니다. 임의의 주제들을 잠그고, 해제하고, 이동하고 삭제할 수 있습니다.';

$lang['Select'] = '선택';
$lang['Delete'] = '삭제';
$lang['Move'] = '이동';
$lang['Lock'] = '잠금';
$lang['Unlock'] = '해제';

$lang['Topics_Removed'] = '선택된 주제는 성공적으로 데이터베이스에서 삭제되었습니다.';
$lang['Topics_Locked'] = '선택된 주제가 잠겼습니다';
$lang['Topics_Moved'] = '선택된 주제가 이동되었습니다';
$lang['Topics_Unlocked'] = '선택된 주제가 잠겼습니다';
$lang['No_Topics_Moved'] = '이동된 주제는 없습니다';

$lang['Confirm_delete_topic'] = '선택된 주제를 삭제하시겠습니까?';
$lang['Confirm_lock_topic'] = '선택된 주제를  잠그시겠습니까?';
$lang['Confirm_unlock_topic'] = '선택된 주제를 해제하시겠습니까?';
$lang['Confirm_move_topic'] = '선택된 주제를 이동하시겠습니까?';

$lang['Move_to_forum'] = '게시판으로 이동';
$lang['Leave_shadow_topic'] = '이전 게시판에 그림자 주제 남기기.';

$lang['Split_Topic'] = '주제 분리 제어판';
$lang['Split_Topic_explain'] = '아래 양식을 이용하여, 올린 글을 개별적으로 선택하거나 지정 위치에서 분리시키거나 하여 주제를 둘로 분리할 수 있습니다';
$lang['Split_title'] = '새 주제 제목';
$lang['Split_forum'] = '새 주제에 대한 게시판';
$lang['Split_posts'] = '선택된 글을 분리';
$lang['Split_after'] = '선택된 글로 부터 분리';
$lang['Topic_split'] = '선택된 주제가 성공적으로 분리되었습니다';

$lang['Too_many_error'] = '글을 너무 많이 선택했습니다. 주제를 분리하려면 글을 하나만 선택해야 합니다!';

$lang['None_selected'] = '이 작업을 하기 위한 주제가 선택되지 않았습니다. 되돌아가서 최소한 하나를 선택하십시오.';
$lang['New_forum'] = '새 게시판';

$lang['This_posts_IP'] = '이 글에 대한 IP';
$lang['Other_IP_this_user'] = '이 사용자가 글을 올린 다른 IP 주소';
$lang['Users_this_IP'] = '이 IP 에서 글을 올리는 사용자';
$lang['IP_info'] = 'IP 정보';
$lang['Lookup_IP'] = 'IP 찾기';


//
// Timezones ... for display on each page
//
$lang['All_times'] = '시간대: %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 시간';
$lang['-11'] = 'GMT - 11 시간';
$lang['-10'] = 'GMT - 10 시간';
$lang['-9'] = 'GMT - 9 시간';
$lang['-8'] = 'GMT - 8 시간';
$lang['-7'] = 'GMT - 7 시간';
$lang['-6'] = 'GMT - 6 시간';
$lang['-5'] = 'GMT - 5 시간';
$lang['-4'] = 'GMT - 4 시간';
$lang['-3.5'] = 'GMT - 3.5 시간';
$lang['-3'] = 'GMT - 3 시간';
$lang['-2'] = 'GMT - 2 시간';
$lang['-1'] = 'GMT - 1 시간';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 시간';
$lang['2'] = 'GMT + 2 시간';
$lang['3'] = 'GMT + 3 시간';
$lang['3.5'] = 'GMT + 3.5 시간';
$lang['4'] = 'GMT + 4 시간';
$lang['4.5'] = 'GMT + 4.5 시간';
$lang['5'] = 'GMT + 5 시간';
$lang['5.5'] = 'GMT + 5.5 시간';
$lang['6'] = 'GMT + 6 시간';
$lang['6.5'] = 'GMT + 6.5 시간';
$lang['7'] = 'GMT + 7 시간';
$lang['8'] = 'GMT + 8 시간';
$lang['9'] = 'GMT + 9 시간(한국)';
$lang['9.5'] = 'GMT + 9.5 시간';
$lang['10'] = 'GMT + 10 시간';
$lang['11'] = 'GMT + 11 시간';
$lang['12'] = 'GMT + 12 시간';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 시간';
$lang['tz']['-11'] = 'GMT - 11 시간';
$lang['tz']['-10'] = 'GMT - 10 시간';
$lang['tz']['-9'] = 'GMT - 9 시간';
$lang['tz']['-8'] = 'GMT - 8 시간';
$lang['tz']['-7'] = 'GMT - 7 시간';
$lang['tz']['-6'] = 'GMT - 6 시간';
$lang['tz']['-5'] = 'GMT - 5 시간';
$lang['tz']['-4'] = 'GMT - 4 시간';
$lang['tz']['-3.5'] = 'GMT - 3.5 시간';
$lang['tz']['-3'] = 'GMT - 3 시간';
$lang['tz']['-2'] = 'GMT - 2 시간';
$lang['tz']['-1'] = 'GMT - 1 시간';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 시간';
$lang['tz']['2'] = 'GMT + 2 시간';
$lang['tz']['3'] = 'GMT + 3 시간';
$lang['tz']['3.5'] = 'GMT + 3.5 시간';
$lang['tz']['4'] = 'GMT + 4 시간';
$lang['tz']['4.5'] = 'GMT + 4.5 시간';
$lang['tz']['5'] = 'GMT + 5 시간';
$lang['tz']['5.5'] = 'GMT + 5.5 시간';
$lang['tz']['6'] = 'GMT + 6 시간';
$lang['tz']['6.5'] = 'GMT + 6.5 시간';
$lang['tz']['7'] = 'GMT + 7 시간';
$lang['tz']['8'] = 'GMT + 8 시간';
$lang['tz']['9'] = 'GMT + 9 시간(한국)';
$lang['tz']['9.5'] = 'GMT + 9.5 시간';
$lang['tz']['10'] = 'GMT + 10 시간';
$lang['tz']['11'] = 'GMT + 11 시간';
$lang['tz']['12'] = 'GMT + 12 시간';

$lang['datetime']['Sunday'] = '일요일';
$lang['datetime']['Monday'] = '월요일';
$lang['datetime']['Tuesday'] = '화요일';
$lang['datetime']['Wednesday'] = '수요일';
$lang['datetime']['Thursday'] = '목요일';
$lang['datetime']['Friday'] = '금요일';
$lang['datetime']['Saturday'] = '토요일';
$lang['datetime']['Sun'] = '일';
$lang['datetime']['Mon'] = '월';
$lang['datetime']['Tue'] = '화';
$lang['datetime']['Wed'] = '수';
$lang['datetime']['Thu'] = '목';
$lang['datetime']['Fri'] = '금';
$lang['datetime']['Sat'] = '토';
$lang['datetime']['January'] = '1월';
$lang['datetime']['February'] = '2월';
$lang['datetime']['March'] = '3월';
$lang['datetime']['April'] = '4월';
$lang['datetime']['May'] = '5월';
$lang['datetime']['June'] = '6월';
$lang['datetime']['July'] = '7월';
$lang['datetime']['August'] = '8월';
$lang['datetime']['September'] = '9월';
$lang['datetime']['October'] = '10월';
$lang['datetime']['November'] = '11월';
$lang['datetime']['December'] = '12월';
$lang['datetime']['Jan'] = '1월';
$lang['datetime']['Feb'] = '2월';
$lang['datetime']['Mar'] = '3월';
$lang['datetime']['Apr'] = '4월';
$lang['datetime']['May'] = '5월';
$lang['datetime']['Jun'] = '6월';
$lang['datetime']['Jul'] = '7월';
$lang['datetime']['Aug'] = '8월';
$lang['datetime']['Sep'] = '9월';
$lang['datetime']['Oct'] = '10월';
$lang['datetime']['Nov'] = '11월';
$lang['datetime']['Dec'] = '12월';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = '정보';
$lang['Critical_Information'] = '중요한 정보';

$lang['General_Error'] = '일반 에러';
$lang['Critical_Error'] = '치명적 에러';
$lang['An_error_occured'] = '에러가 발생했습니다';
$lang['A_critical_error'] = '치명적 에러가 발생했습니다';

//
// That's all Folks!
// -------------------------------------------------

?>