<?php

/***************************************************************************
 *                            lang_admin.php [Korean]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin.php,v 1.35.2.3 2002/06/27 20:06:44 thefinn Exp $
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
// 2002/08/28 Translated by TankTonk
// 2002/12/17 updated by Soon-Son Kwon(kss@kldp.org)

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = '일반관리';
$lang['Users'] = '사용자 관리';
$lang['Groups'] = '그룹 관리';
$lang['Forums'] = '게시판 관리';
$lang['Styles'] = '스타일 관리';

$lang['Configuration'] = '설정';
$lang['Permissions'] = '허가';
$lang['Manage'] = '관리';
$lang['Disallow'] = '이름 사용 불가';
$lang['Prune'] = '정리';
$lang['Mass_Email'] = '대량 이메일';
$lang['Ranks'] = '등급';
$lang['Smilies'] = '스마일';
$lang['Ban_Management'] = '금지 제어';
$lang['Word_Censor'] = '단어검열';
$lang['Export'] = '내보내기';
$lang['Create_new'] = '만들기';
$lang['Add_new'] = '더하기';
$lang['Backup_DB'] = '데이터베이스 백업';
$lang['Restore_DB'] = '데이터베이스 복원';


//
// Index
//
$lang['Admin'] = '관리';
$lang['Not_admin'] = '이 게시판을 관리할 권한이 없습니다';
$lang['Welcome_phpBB'] = 'phpBB에 오신 것을 환영합니다';
$lang['Admin_intro'] = 'phpBB를 귀하의 게시판으로 선택해 주셔서 감사합니다. 이 화면은 귀하의 게시판의 각종 통계치를 간략하게 보여줍니다. 왼쪽 틀에 있는 <u>관리 인덱스</u>를 클릭하면 이 화면으로 다시 들어올 수 있습니다. 귀하의 게시판 인덱스로 돌아가려면 왼쪽 틀에 있는 phpBB 로고를 클릭하십시오. 본 화면의 왼쪽에 있는 다른 링크들은 게시판의 모든 기능들을 제어할 수 있도록 해주며, 각 화면에는 도구들의 사용방법이 있습니다';
$lang['Main_index'] = '게시판 인덱스';
$lang['Forum_stats'] = '게시판 통계';
$lang['Admin_Index'] = '관리 인덱스';
$lang['Preview_forum'] = '게시판 미리보기';

$lang['Click_return_admin_index'] = '관리 인덱스로 돌아가려면 %s여기%s를 클릭하십시오';

$lang['Statistic'] = '통계';
$lang['Value'] = '값';
$lang['Number_posts'] = '게시물 갯수';
$lang['Posts_per_day'] = '하루당 게시물';
$lang['Number_topics'] = '주제 갯수';
$lang['Topics_per_day'] = '하루당 주제';
$lang['Number_users'] = '사용자수';
$lang['Users_per_day'] = '하루당 사용자';
$lang['Board_started'] = '게시판 시작됨';
$lang['Avatar_dir_size'] = '아바타 디렉토리 크기';
$lang['Database_size'] = '데이터베이스 크기';
$lang['Gzip_compression'] ='Gzip 압축';
$lang['Not_available'] = '사용 불가';

$lang['ON'] = '켬'; // This is for GZip compression
$lang['OFF'] = '끔';


//
// DB Utils
//
$lang['Database_Utilities'] = '데이터베이스 유틸리티';

$lang['Restore'] = '복원';
$lang['Backup'] = '백업';
$lang['Restore_explain'] = '저장된 파일로 부터 모든 phpBB 테이블의 완전 복원을 실행합니다. 만약 서버가 지원한다면, 압축된 gzip 텍스트 파일이 업로드될때 자동으로 압축을 풀어줍니다. <b>경고</b> 기존의 데이터는 모두 덮어씁니다. 복원은 시간이 오래 걸리므로 끝날때까지 이 화면을 떠나지 마십시오.';
$lang['Backup_explain'] = '여기서는 phpBB에 관련된 모든 데이터를 백업할 수 있습니다. 함께 저장하고자 하는 추가의 사용자 정의 테이블이 phpBB와 같은 데이터베이스에 있다면 아래의 추가 테이블 입력 박스에 이름을 입력하십시오(콤마를 사용하여 여러 이름을 넣을 수 있습니다). 서버가 지원한다면, 다운로드하기 전에 gzip 압축으로 파일의 크기를 작게 할 수 있습니다.';

$lang['Backup_options'] = '백업 옵션';
$lang['Start_backup'] = '백업 시작';
$lang['Full_backup'] = '풀 백업';
$lang['Structure_backup'] = '스트럭처만 백업';
$lang['Data_backup'] = '데이터만 백업';
$lang['Additional_tables'] = '추가 테이블';
$lang['Gzip_compress'] = 'Gzip으로 파일 압축';
$lang['Select_file'] = '파일 선택';
$lang['Start_Restore'] = '복원 시작';

$lang['Restore_success'] = '데이터베이스가 성공적으로 복원되었습니다.<br /><br />이제 게시판은 백업이 만들어 졌을 때의 상태가 되었습니다.';
$lang['Backup_download'] = '다운로드가 곧 시작되므로 기다려 주십시오.';
$lang['Backups_not_supported'] = '아쉽게도 데이터베이스 백업이 귀하의 데이터베이스 시스템에서는 지원이 되지 않습니다.';

$lang['Restore_Error_uploading'] = '백업 파일 업로딩에 문제가 있습니다';
$lang['Restore_Error_filename'] = '파일 이름에 문제가 있으므로 다른 파일을 시도해 보십시오';
$lang['Restore_Error_decompress'] = 'gzip 파일의 압축을 풀 수가 없으므로 단순 텍스트 버전을 업로드하십시오';
$lang['Restore_Error_no_file'] = '업로드된 파일이 없습니다';


//
// Auth pages
//
$lang['Select_a_User'] = '사용자 선택';
$lang['Select_a_Group'] = '그룹 선택';
$lang['Select_a_Forum'] = '게시판 선택';
$lang['Auth_Control_User'] = '사용자 권한 조절';
$lang['Auth_Control_Group'] = '그룹 권한 조절';
$lang['Auth_Control_Forum'] = '게시판 권한 조절';
$lang['Look_up_User'] = '사용자 찾기';
$lang['Look_up_Group'] = '그룹 찾기';
$lang['Look_up_Forum'] = '게시판 찾기';

$lang['Group_auth_explain'] = '여기에서는 각 사용자 그룹에 지정된 권한과 관리자 상태를 변경할 수 있습니다. 그룹 권한을 변경할 때에, 각 사용자 권한으로 사용자가 게시판에 아직 들어올 수 있음을 잊지 마십시오. 그러한 경우가 발생하게되면 경고가 나타날 것입니다.';
$lang['User_auth_explain'] = '여기에서는 각 사용자에 지정된 권한과 관리자 상태를 변경할 수 있습니다. 사용자 권한을 변경할 때에, 각 그룹 권한으로 사용자가 게시판에 아직 들어올 수 있음을 잊지 마십시오. 그러한 경우가 발생하게되면 경고가 나타날 것입니다.';
$lang['Forum_auth_explain'] = '여기에서는 각 게시판의 권한 레벨을 변경할 수 있습니다. 여기에는 단순 모드와 고급 모드가 있는데, 고급 모드가 각 게시판에 대하여 더 세밀한 조절을 제공합니다. 게시판의 권한 레벨을 변경하면 해당 게시판내에서 사용자가 행하는 각종 작업에 영향을 미칠 것임을 명심하십시오.';

$lang['Simple_mode'] = '단순 모드';
$lang['Advanced_mode'] = '고급 모드';
$lang['Moderator_status'] = '관리자 상태';

$lang['Allowed_Access'] = '접근 가능';
$lang['Disallowed_Access'] = '접근 불가';
$lang['Is_Moderator'] = '관리자 입니다';
$lang['Not_Moderator'] = '관리자가 아닙니다';

$lang['Conflict_warning'] = '권한 불일치 경고';
$lang['Conflict_access_userauth'] = '이 사용자는 아직 그룹 멤버쉽으로 이 게시판에 접근할 수 있습니다. 접근을 막으려면 그룹 권한을 변경하던가 이 사용자 그룹을 제거하는 방법이 있습니다. 해당 그룹(게시판 포함)은 아래와 같습니다.';
$lang['Conflict_mod_userauth'] = '이 사용자는 아직 그룹 멤버쉽으로 이 게시판에 관리자 권한을 갖고 있습니다. 관리자 권한을 갖지 못하게 하려면 그룹 권한을 변경하던가 이 사용자 그룹을 제거하는 방법이 있습니다. 해당 그룹(게시판 포함)은 아래와 같습니다.';

$lang['Conflict_access_groupauth'] = '다음 사용자(들)는 아직 그룹 멤버쉽으로 이 게시판에 접근할 수 있습니다. 접근을 막으려면 그룹 권한을 변경하던가 이 사용자 그룹을 제거하는 방법이 있습니다. 해당 그룹(게시판 포함)은 아래와 같습니다.';
$lang['Conflict_mod_groupauth'] = '다음 사용자(들)는 아직 그룹 멤버쉽으로 이 게시판에 관리자 권한을 갖고 있습니다. 관리자 권한을 갖지 못하게 하려면 그룹 권한을 변경하던가 이 사용자 그룹을 제거하는 방법이 있습니다. 해당 그룹(게시판 포함)은 아래와 같습니다.';

$lang['Public'] = '공개';
$lang['Private'] = '비공개';
$lang['Registered'] = '등록';
$lang['Administrators'] = '운영자';
$lang['Hidden'] = '숨김';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = '모두';
$lang['Forum_REG'] = '일반';
$lang['Forum_PRIVATE'] = '비공개';
$lang['Forum_MOD'] = '관리';
$lang['Forum_ADMIN'] = '운영';

$lang['View'] = '보기';
$lang['Read'] = '읽기';
$lang['Post'] = '올리기';
$lang['Reply'] = '답변';
$lang['Edit'] = '편집';
$lang['Delete'] = '삭제';
$lang['Sticky'] = '끈적이';
$lang['Announce'] = '발표';
$lang['Vote'] = '투표';
$lang['Pollcreate'] = '투표 만들기';

$lang['Permissions'] = '권한';
$lang['Simple_Permission'] = '단순 권한';

$lang['User_Level'] = '사용자 레벨';
$lang['Auth_User'] = '사용자';
$lang['Auth_Admin'] = '운영자';
$lang['Group_memberships'] = '사용자 그룹 멤버쉽';
$lang['Usergroup_members'] = '이 그룹은 아래 멤버를 갖고 있습니다';

$lang['Forum_auth_updated'] = '게시판 권한 변경됨';
$lang['User_auth_updated'] = '사용자 권한 변경됨';
$lang['Group_auth_updated'] = '그룹 권한 변경됨';

$lang['Auth_updated'] = '권한이 변경되었습니다';
$lang['Click_return_userauth'] = '사용자 권한으로 돌아가려면 %s여기%s를 클릭하십시오';
$lang['Click_return_groupauth'] = '그룹 권한으로 돌아가려면 %s여기%s를 클릭하십시오';
$lang['Click_return_forumauth'] = '게시판 권한으로 돌아가려면 %s여기%s를 클릭하십시오';


//
// Banning
//
$lang['Ban_control'] = '금지 조절';
$lang['Ban_explain'] = '여기에서는 사용자의 금지를 조절할 수 있습니다. 특정 사용자나 일정 범위의 IP 주소 혹은 호스트 이름을 금지시킬수 있습니다. 이로써 사용자가 게시판의 인덱스 페이지에 접근하는것 조차 막을수 있습니다. 사용자 이름을 바꿔서 등록하는 것을 막으려면 금지 이메일 주소를 지정합니다. 단지 이메일 주소로 금지시키면 사용자가 로그인 하거나 글을 올리는것을 막을 수는 없으므로, 앞의 방법을 먼저 사용해야 합니다.';
$lang['Ban_explain_warn'] = '일정 범위의 IP 주소를 입력하면 그 범위내의 모든 주소들이 금지 리스트에 올라감을 유념하십시오. 필요한 경우에는 와일드카드 문자를 사용할 수도 있습니다. 주소 범위를 입력해야만 한다면 가능한 최소로 하던가 특정 주소를 지정하시기 바랍니다.';

$lang['Select_username'] = '사용자 이름 선택';
$lang['Select_ip'] = 'IP 선택';
$lang['Select_email'] = '이메일 주소 선택';

$lang['Ban_username'] = '한 명 이상 사용자 금지';
$lang['Ban_username_explain'] = '적절한 키보드와 마우스 조작으로 한번에 여러명의 사용자를 금지시킬 수 있습니다';

$lang['Ban_IP'] = '하나 이상의 IP 주소나 호스트 이름 금지';
$lang['IP_hostname'] = 'IP 주소들 이나 호스트 이름들';
$lang['Ban_IP_explain'] = '여러 IP나 호스트 이름을 지정하려면 콤마를 사용하십시오. IP 주소 범위를 지정하려면 하이픈(-)을 사용하십시오. 와일드카드 문자는 * 를 사용하십시오';

$lang['Ban_email'] = '하나 이상의 이메일 주소 금지';
$lang['Ban_email_explain'] = '여러 이메일 주소를 지정하려면 콤마를 사용하십시오. 와일드카드 문자는 * 를 사용하십시오. 예제로 *@hotmail.com';
$lang['Unban_username'] = '한 명 이상 사용자 금지 해제';
$lang['Unban_username_explain'] = '적절한 키보드와 마우스 조작으로 한번에 여러명의 사용자에 대한 금지를 해제할 수 있습니다';

$lang['Unban_IP'] = '하나 이상의 IP 주소 금지 해제';
$lang['Unban_IP_explain'] = '적절한 키보드와 마우스 조작으로 한번에 여러 IP 주소에 대한 금지를 해제할 수 있습니다';

$lang['Unban_email'] = '하나 이상의 이메일 주소 금지 해제';
$lang['Unban_email_explain'] = '적절한 키보드와 마우스 조작으로 한번에 여러 이메일 주소에 대한 금지를 해제할 수 있습니다';

$lang['No_banned_users'] = '금지된 사용자 없음';
$lang['No_banned_ip'] = '금지된 IP 주소 없음';
$lang['No_banned_email'] = '금지된 이메일 주소 없음';

$lang['Ban_update_sucessful'] = '금지리스트가 성공적으로 업데이트되었습니다';
$lang['Click_return_banadmin'] = '금지 조절로 돌아가려면 %s여기%를 클릭하십시오';


//
// Configuration
//
$lang['General_Config'] = '일반 설정';
$lang['Config_explain'] = '다음 양식으로 게시판의 일반적인 옵션을 변경할 수 있습니다. 사용자 및 게시판 설정은 왼쪽의 관련 링크를 이용하십시오.';

$lang['Click_return_config'] = '일반 설정으로 돌아가려면 %s여기%s를 클릭하십시오';

$lang['General_settings'] = '일반 게시판 설정';
$lang['Server_name'] = '도메인 이름';
$lang['Server_name_explain'] = '이 게시판이 실행되는 도메인 이름';
$lang['Script_path'] = '스크립트 경로';
$lang['Script_path_explain'] = 'phpBB2가 위치한 도메인 이름에 대한 상대적 경로';
$lang['Server_port'] = '서버 포트';
$lang['Server_port_explain'] = '서버가 동작하고 있는 포트, 일반적으로 80, 다른 경우에만 변경하십시오';
$lang['Site_name'] = '사이트 이름';
$lang['Site_desc'] = '사이트 소개';
$lang['Board_disable'] = '게시판 사용 정지';
$lang['Board_disable_explain'] = '이 기능은 사용자들이 게시판을 사용하지 못하도록 합니다. 게시판 사용을 정지한 경우, 로그아웃 하지 마십시오. 다시는 로그인 할 수 없습니다!';
$lang['Acct_activation'] = '계정 활성화 사용 가능';
$lang['Acc_None'] = '없음'; // These three entries are the type of activation
$lang['Acc_User'] = '사용자';
$lang['Acc_Admin'] = '관리자';

$lang['Abilities_settings'] = '사용자 및 게시판 기본 설정';
$lang['Max_poll_options'] = '투표 옵션의 최대 갯수';
$lang['Flood_Interval'] = '쇄도 간격';
$lang['Flood_Interval_explain'] = '올려지는 글 사이에 사용자가 기다려야 할 시간(초)';
$lang['Board_email_form'] = '게시판을 통한 사용자 이메일';
$lang['Board_email_form_explain'] = '사용자들은 이 게시판을 통해서 서로 이메일을 보냅니다';
$lang['Topics_per_page'] = '페이지당 주제들';
$lang['Posts_per_page'] = '페이지당 글';
$lang['Hot_threshold'] = '인기있는 주제에 대한 글';
$lang['Default_style'] = '기본 스타일';
$lang['Override_style'] = '사용자 스타일 무시하기';
$lang['Override_style_explain'] = '기본값으로 사용자 스타일 교체하기';
$lang['Default_language'] = '기본 언어';
$lang['Date_format'] = '날짜 형식';
$lang['System_timezone'] = '시스템 시간대';
$lang['Enable_gzip'] = 'GZip 압축 사용 가능';
$lang['Enable_prune'] = '게시판 정리 사용 가능';
$lang['Allow_HTML'] = 'HTML 사용';
$lang['Allow_BBCode'] = 'BBCode 사용';
$lang['Allowed_tags'] = '사용 가능한 HTML 태그';
$lang['Allowed_tags_explain'] = '콤마로 태그를 구분하십시오';
$lang['Allow_smilies'] = '스마일 사용';
$lang['Smilies_path'] = '스마일 있는곳 경로';
$lang['Smilies_path_explain'] = 'phpBB 루트 디렉토디 하의 경로, 즉, images/smiles';
$lang['Allow_sig'] = '서명 사용';
$lang['Max_sig_length'] = '최대 서명 길이';
$lang['Max_sig_length_explain'] = '서명의 최대 문자 수';
$lang['Allow_name_change'] = '사용자 이름 변경 허가';

$lang['Avatar_settings'] = '아바타 설정';
$lang['Allow_local'] = '갤러리 아바타 사용 가능';
$lang['Allow_remote'] = '원격 아바타 사용 가능';
$lang['Allow_remote_explain'] = '다른 웹사이트로 링크된 아바타';
$lang['Allow_upload'] = '아바타 업로드 사용 가능';
$lang['Max_filesize'] = '최대 아바타 파일 크기';
$lang['Max_filesize_explain'] = '업로드된 아바타 파일 용';
$lang['Max_avatar_size'] = '최대 아바타 차원';
$lang['Max_avatar_size_explain'] = '(높이 x 폭 픽셀)';
$lang['Avatar_storage_path'] = '아바타 있는곳 경로';
$lang['Avatar_storage_path_explain'] = 'phpBB 루트 디렉토디 하의 경로, 즉, images/avatars';
$lang['Avatar_gallery_path'] = '아바타 갤러리 경로';
$lang['Avatar_gallery_path_explain'] = '이미 로드된 이미지용 phpBB 루트 디렉토디 하의 경로, 즉, images/avatars/gallery';

$lang['COPPA_settings'] = 'COPPA 설정';
$lang['COPPA_fax'] = 'COPPA 팩스 번호';
$lang['COPPA_mail'] = 'COPPA 주소';
$lang['COPPA_mail_explain'] = '이것은 부모들이 COPPA 등록 양식을 보낼 주소입니다';

$lang['Email_settings'] = '이메일 설정';
$lang['Admin_email'] = '관리자 이메일 주소';
$lang['Email_sig'] = '이메일 서명';
$lang['Email_sig_explain'] = '게시판이 보내는 모든 이메일에 이 텍스트가 첨부됩니다';
$lang['Use_SMTP'] = '이메일에 SMTP 서버 사용하기';
$lang['Use_SMTP_explain'] = '로컬 메일 기능 대신에 지정된 서버를 사용하여 이메일을 보내려면 예 하십시오.';
$lang['SMTP_server'] = 'SMTP 서버 주소';
$lang['SMTP_username'] = 'SMTP 사용자 이름';
$lang['SMTP_username_explain'] = 'SMTP 서버가 요구하는 경우에만, 사용자 이름을 입력하십시오';
$lang['SMTP_password'] = 'SMTP 패스워드';
$lang['SMTP_password_explain'] = 'SMTP 서버가 요구하는 경우에만, 패스워드를 입력하십시오';

$lang['Disable_privmsg'] = '비공개 메시지';
$lang['Inbox_limits'] = '받은함내의 최대 글수';
$lang['Sentbox_limits'] = '보낸함내의 최대 글수';
$lang['Savebox_limits'] = '저장함내의 최대 글수';

$lang['Cookie_settings'] = '쿠키 설정';
$lang['Cookie_settings_explain'] = '다음의 자세한 내용들은 쿠키가 어떻게 사용자 브라우저로 보내지는 가를 정의합니다. 대부분의 경우에 있어서 디폴트의 쿠기 설정치들이 적당하지만, 만약 변경을 하고자 한다면 주의를 요하며 잘못된 설정은 사용자로 하여금 로그인하지 못하게 할 수 있습니다';
$lang['Cookie_domain'] = '쿠키 도메일';
$lang['Cookie_name'] = '쿠키 이름';
$lang['Cookie_path'] = '쿠키 경로';
$lang['Cookie_secure'] = '쿠키 보안';
$lang['Cookie_secure_explain'] = '서버가 SSL을 통하여 동작하면 이것을 사용가능으로, 아닌 경우에는 사용불가능으로 설정하십시오';
$lang['Session_length'] = '세션 길이 [ 초 ]';


//
// Forum Management
//
$lang['Forum_admin'] = '게시판 관리';
$lang['Forum_admin_explain'] = '여기에서는 카테고리와 게시판을 추가, 삭제, 편집, 순서 재조정 및 재-동기화을 할 수 있습니다';
$lang['Edit_forum'] = '게시판 편집';
$lang['Create_forum'] = '게시판 새로 만들기';
$lang['Create_category'] = '카테고리 새로 만들기';
$lang['Remove'] = '삭제';
$lang['Action'] = '실시';
$lang['Update_order'] = '순서 업데이트';
$lang['Config_updated'] = '게시판 구성이 성공적으로 업데이트되었습니다';
$lang['Edit'] = '편집';
$lang['Delete'] = '삭제';
$lang['Move_up'] = '위로 이동';
$lang['Move_down'] = '아래로 이동';
$lang['Resync'] = '재-동기화';
$lang['No_mode'] = '설정된 모드가 없습니다';
$lang['Forum_edit_delete_explain'] = '아래 양식으로 모든 일반 게시판 옵션을 구성할 수 있습니다. 사용자 및 게시판 구성은 왼쪽의 관련 링크를 사용하십시오.';

$lang['Move_contents'] = '모든 내용 이동';
$lang['Forum_delete'] = '게시판 삭제';
$lang['Forum_delete_explain'] = '아래 양식으로 게시판(혹은 카테고리)를 삭제할 수 있으며 그 안에 있는 모든 주제(혹은 게시판)을 어디로 옮길 것인지를 결정할 수 있습니다';

$lang['Status_locked'] = '잠김';
$lang['Status_unlocked'] = '해제';
$lang['Forum_settings'] = '일반 게시판 설정';
$lang['Forum_name'] = '게시판 이름';
$lang['Forum_desc'] = '설명';
$lang['Forum_status'] = '게시판 상태';
$lang['Forum_pruning'] = '자동 정리';

$lang['prune_freq'] = '주제의 시효를 매번 확인';
$lang['prune_days'] = '아무런 글도 올라오지 않은 주제를 삭제';
$lang['Set_prune_data'] = '이 게시판에 대해서 자동 정리를 지정하였으나 정리 주기를 지정하지 않았습니다. 되돌아가서 설정하십시오';

$lang['Move_and_Delete'] = '이동 및 삭제';

$lang['Delete_all_posts'] = '모든 글 삭제';
$lang['Nowhere_to_move'] = '이동할 곳이 없음';

$lang['Edit_Category'] = '카테고리 편집';
$lang['Edit_Category_explain'] = '이 양식으로 카테고리 이름을 변경하십시오';

$lang['Forums_updated'] = '게시판과 카테고리 정보가 성공적으로 업데이트되었습니다';

$lang['Must_delete_forums'] = '이 카테고리를 지우기 전에 모든 게시판을 먼저 삭제해야합니다';

$lang['Click_return_forumadmin'] = '게시판 관리로 돌아가려면 %s여기%s를 클릭하십시오';


//
// Smiley Management
//
$lang['smiley_title'] = '스마일 편집 유틸리티';
$lang['smile_desc'] = '여기에서는 사용자가 글을 올리거나 비공개 메시지를 보낼때 사용할 감정 표현 그림이나 스마일을 추가, 삭제 혹은 편집할 수 있습니다.';

$lang['smiley_config'] = '스마일 구성';
$lang['smiley_code'] = '스마일 코드';
$lang['smiley_url'] = '스마일 그림 파일';
$lang['smiley_emot'] = '스마일 감정 표현';
$lang['smile_add'] = '새로운 스마일 추가';
$lang['Smile'] = '스마일';
$lang['Emotion'] = '감정 표현';

$lang['Select_pak'] = '팩 (.pak) 파일을 선택하십시오';
$lang['replace_existing'] = '기존 스마일 대체';
$lang['keep_existing'] = '기존 스마일 보존';
$lang['smiley_import_inst'] = '설치를 하려면 스마일 패키지의 압축을 풀고 모든 파일들을 적당한 스마일 디렉토리로 업로드해야 합니다.  그런 다음, 이 양식에서 올바른 정보를 선택하여 스마일 팩을 가져 옵니다.';
$lang['smiley_import'] = '스마일 팩 가져오기';
$lang['choose_smile_pak'] = '스마일 팩(.pak) 파일을 선택하십시오';
$lang['import'] = '스마일 가져오기';
$lang['smile_conflicts'] = '충돌난 경우 해야할 일';
$lang['del_existing_smileys'] = '가져오기 전에 기존의 스마일을 삭제하십시오';
$lang['import_smile_pack'] = '스마일 팩을 가져오기';
$lang['export_smile_pack'] = '스마일 팩 만들기';
$lang['export_smiles'] = '현재 설치된 스마일로부터 스마일을 만들려면 %s여기%s를 클릭하여 스마일 팩 파일을 다운로드 하십시오. 반드시 .pak 확장자를 사용하고 적당한 파일명을 지정하십시오. 그런 다음 모든 스마일 이미지와 .pak 구성 파일을 포함하여 zip파일을 만드십시오.';

$lang['smiley_add_success'] = '스마일이 성공적으로 추가되었습니다';
$lang['smiley_edit_success'] = '스마일이 성공적으로 업데이트되었습니다';
$lang['smiley_import_success'] = '스마일팩을 성공적으로 가져왔습니다!';
$lang['smiley_del_success'] = '스마일이 성공적으로 지워졌습니다';
$lang['Click_return_smileadmin'] = '스마일 관리로 돌아가려면 %s여기%s를 클릭하십시오';


//
// User Management
//
$lang['User_admin'] = '사용자 관리';
$lang['User_admin_explain'] = '여기에서는 사용자의 정보와 특정 옵션들을 변경할 수 있습니다. 사용자 권한을 변경하려면 사용자 및 그룹 권한 시스템을 이용하십시오.';

$lang['Look_up_user'] = '사용자 찾기';

$lang['Admin_user_fail'] = '사용자 프로파일을 업데이트할 수 없습니다.';
$lang['Admin_user_updated'] = '사용자의 프로파일이 성공적으로 업데이트되었습니다.';
$lang['Click_return_useradmin'] = '사용자 관리로 돌아가려면 %s여기%s를 클릭하십시오';

$lang['User_delete'] = '이 사용자를 삭제합니다';
$lang['User_delete_explain'] = '여기를 클릭하여 이 사용자를 삭제합니다, 본 동작은 되돌려질 수 없습니다.';
$lang['User_deleted'] = '사용자가 성공적으로 삭제되었습니다.';

$lang['User_status'] = '사용자는 활동중입니다';
$lang['User_allowpm'] = '비공개 메시지를 보낼수 있습니다';
$lang['User_allowavatar'] = '아바타를 표시할 수 있습니다';

$lang['Admin_avatar_explain'] = '여기에서는 사용자의 현재 아바타를 보거나 삭제할 수 있습니다.';

$lang['User_special'] = '특수 운영자 전용 필드';
$lang['User_special_explain'] = '이 필드는 사용자가 수정할 수 없습니다. 여기에서는 사용자들의 상태와 기타 사용자가 건드릴수 없는 옵션들을 설정할 수 있습니다';


//
// Group Management
//
$lang['Group_administration'] = '그룹 관리';
$lang['Group_admin_explain'] = '여기는 모든 그룹을 관리하는 곳으로, 기존 그룹의 삭제, 만들기 및 편집을 할 수 있습니다. 관리자를 지정할 수 있으며, 그룹 상태를 열림/닫힘으로 토글할 수 있고 그룹 이름과 설명을 지정할 수 있습니다';
$lang['Error_updating_groups'] = '그룹을 업데이트하는데 에러가 발생했습니다';
$lang['Updated_group'] = '그룹이 성공적으로 업데이트되었습니다';
$lang['Added_new_group'] = '새로운 그룹이 성공적으로 만들어 졌습니다';
$lang['Deleted_group'] = '그룹이 성공적으로 삭제되었습니다';
$lang['New_group'] = '새로운 그룹 만들기';
$lang['Edit_group'] = '그룹 편집하기';
$lang['group_name'] = '그룹 이름';
$lang['group_description'] = '그룹 설명';
$lang['group_moderator'] = '그룹 관리자';
$lang['group_status'] = '그룹 상태';
$lang['group_open'] = '그룹 열기';
$lang['group_closed'] = '그룹 닫기';
$lang['group_hidden'] = '비밀 그룹';
$lang['group_delete'] = '그룹 삭제';
$lang['group_delete_check'] = '이 그룹을 지웁니다';
$lang['submit_group_changes'] = '변경 적용';
$lang['reset_group_changes'] = '변경 취소';
$lang['No_group_name'] = '이 그룹에 대한 이름을 지정해야 합니다';
$lang['No_group_moderator'] = '이 그룹에 대한 관리자를 지정해야 합니다';
$lang['No_group_mode'] = '이 그룹에 대한 모드를 열힘 혹은 닫힘으로 지정해야 합니다.';
$lang['No_group_action'] = '아무런 동작도 지정되지 않았습니다';
$lang['delete_group_moderator'] = '옛 그룹 관리자를 삭제하겠습니까?';
$lang['delete_moderator_explain'] = '그룹 관리자를 바꾸려면, 이 박스에 체크하여 옛 관리자를 그룹에서 제거하십시오. 그렇지 않으면 체크하지 마십시오, 그 사용자는 그룹의 일반 회원이 됩니다.';
$lang['Click_return_groupsadmin'] = '그룹 관리로 돌아가려면 %s여기%s를 클릭하십시오';
$lang['Select_group'] = '그룹 지정하기';
$lang['Look_up_group'] = '그룹 찾기';


//
// Prune Administration
//
$lang['Forum_Prune'] = '게시판 정리';
$lang['Forum_Prune_explain'] = '이것은 지정된 날짜동안 아무런 게시물도 없었던 주제를 삭제합니다. 번호를 입력하지 않으면 모든 주제들이 삭제됩니다. 투표가 진행중인 주제는 삭제하지 않으며, 공지사항 또한 지우지 않습니다. 이러한 주제들은 수동으로 지워야 합니다.';
$lang['Do_Prune'] = '정리하기';
$lang['All_Forums'] = '모든 게시판';
$lang['Prune_topics_not_posted'] = '이 기간 동안 답변이 없었던 주제 정리하기';
$lang['Topics_pruned'] = '정리된 주제들';
$lang['Posts_pruned'] = '정리된 글들';
$lang['Prune_success'] = '게시판의 정리 작업이 성공적으로 수행되었습니다';


//
// Word censor
//
$lang['Words_title'] = '단어 검열';
$lang['Words_explain'] = '여기에서는 게시판에서 자동으로 검열될 단어들을 추가, 편집 및 삭제할 수 있습니다. 또한, 사용자 이름에 해당 단어들을 사용할 수 없습니다. 와일드문자(*)도 단어 필드에 사용할 수 있으며 예를 들어, *test* 는 detestable, test* 는 testing, *test 는 detest 에 해당됩니다.';
$lang['Word'] = '단어';
$lang['Edit_word_censor'] = '검열 단어 편집';
$lang['Replacement'] = '대체';
$lang['Add_new_word'] = '새로운 단어 추가';
$lang['Update_word'] = '검열 단어 업데이트';

$lang['Must_enter_word'] = '단어와 대치할 단어를 입력해야 합니다';
$lang['No_word_selected'] = '편집할 단어가 선택되지 않았습니다';

$lang['Word_updated'] = '선택된 검열 단어가 성공적으로 업데이트되었습니다';
$lang['Word_added'] = '검열 단어가 성공적으로 추가되었습니다';
$lang['Word_removed'] = '선택된 검열 단어가 성공적으로 삭제되었습니다';

$lang['Click_return_wordadmin'] = '검열 단어 관리로 돌아가려면 %s여기%s를 클릭하십시오';


//
// Mass Email
//
$lang['Mass_email_explain'] = '여기에서는 모든 사용자나 특정 그룹내의 모든 사용자들에게 이메일을 보낼수 있습니다. 이 때, 관리자 메일 주소로 메일이 보내지며, 모든 수신자들에게 blind carbon copy가 보내집니다. 만약 많은 사람들에게 메일을 보낸다면 발송후 좀 지연되더라도 페이지를 이동하지 마십시오. 대량 메일 전달에 시간이 오래 걸리는 것은 정상이며, 작업이 완료되면 메시지가 뜹니다';
$lang['Compose'] = '쓰기';

$lang['Recipients'] = '수신자';
$lang['All_users'] = '모든 사용자';

$lang['Email_successfull'] = '메시지가 보내졌습니다';
$lang['Click_return_massemail'] = '대량 메일 양식으로 돌아가려면 %s여기%s를 클릭하십시오';


//
// Ranks admin
//
$lang['Ranks_title'] = '등급 관리';
$lang['Ranks_explain'] = '이 양식으로 등급을 추가, 편집, 보기 및 삭제할 수 있습니다. 사용자 관리 기능을 사용하여 사용자에게 적용될 특별 등급도 만들수 있습니다';

$lang['Add_new_rank'] = '새로운 등급 추가';

$lang['Rank_title'] = '등급 이름';
$lang['Rank_special'] = '특별 등급으로 설정';
$lang['Rank_minimum'] = '최소 글 수 ';
$lang['Rank_maximum'] = '최대 글 수';
$lang['Rank_image'] = '등급 이미지 (phpBB2 루트에 대한 상대적 경로)';
$lang['Rank_image_explain'] = '이것은 등급에 연관된 작은 이미지를 정의합니다';

$lang['Must_select_rank'] = '등급을 선택해야 합니다';
$lang['No_assigned_rank'] = '특별 등급이 지정되지 않았습니다';

$lang['Rank_updated'] = '등급이 성공적으로 업데이트되었습니다';
$lang['Rank_added'] = '등급이 성공적으로 추가되었습니다';
$lang['Rank_removed'] = '등급이 성공적으로 삭제되었습니다';
$lang['No_update_ranks'] = '등급이 성공적으로 삭제되었습니다만, 이 등급을 사용하는 사용자 계정이 업데이트되지 않았습니다. 이 계정들에 대한 등급을 수동으로 초기화 해야 합니다';

$lang['Click_return_rankadmin'] = '등급 관리로 돌아가려면 %s여기%s를 클릭하십시오';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = '사용자 이름 불가 조절';
$lang['Disallow_explain'] = '여기에서는 사용하지 못하게 할 사용자 이름을 조절할 수 있습니다. 사용 불가 사용자 이름 지정시에 와일드 문자를 사용할 수 있습니다.  이미 등록된 사용자 이름은 지정할 수 없으므로, 그 이름을 먼저 삭제하고 사용 불가로 만들어야 합니다';

$lang['Delete_disallow'] = '삭제';
$lang['Delete_disallow_title'] = '사용 불가 사용자 이름 삭제 하기';
$lang['Delete_disallow_explain'] = '다음 리스트에서 사용자 이름을 선택하고 보냄을 클릭함으로써 사용 불가 사용자 이름을 삭제할 수 있습니다';

$lang['Add_disallow'] = '추가';
$lang['Add_disallow_title'] = '사용 불가 사용자 이름 추가';
$lang['Add_disallow_explain'] = '와일드 문자를 사용하여 사용 불가할 사용자 이름을 지정할 수 있습니다';

$lang['No_disallowed'] = '사용 불가의 사용자 이름이 없음';

$lang['Disallowed_deleted'] = '사용 불가 사용자 이름이 성공적으로 삭제되었습니다';
$lang['Disallow_successful'] = '사용 불가 사용자 이름이 성공적으로 추가되었습니다';
$lang['Disallowed_already'] = '입력한 이름은 사용 불가로 될 수 없읍니다. 이미 리스트상에 있거나, 검열 단어 리스트에 있거나, 해당 사용자 이름이 존재 합니다';

$lang['Click_return_disallowadmin'] = '사용 불가 사용자 이름 관리로 돌아가려면 %s여기%s를 클릭하십시오';


//
// Styles Admin
//
$lang['Styles_admin'] = '스타일 관리';
$lang['Styles_explain'] = '여기에서는 사용자가 사용할 수 있도록 스타일(템플릿 및 테마)을 추가, 삭제 및 관리할 수 있습니다';
$lang['Styles_addnew_explain'] = '다음 리스트는 현재 귀하가 갖고 있는 템플릿에 적용될 수 있는  든 테마를 포함하고 있습니다. 이 리스트 상의 아이템들은 아직 phpBB 데이터베이스에 설치되지 않았습니다. 테마를 설치하려면 엔트리 옆의 설치 링크를 클릭하십시오';

$lang['Select_template'] = '템플릿 선택하기';

$lang['Style'] = '스타일';
$lang['Template'] = '템플릿';
$lang['Install'] = '설치';
$lang['Download'] = '다운로드';

$lang['Edit_theme'] = '테마 편집';
$lang['Edit_theme_explain'] = '선택된 테마에 대한 설정은 아래 양식에서 편집할 수 있습니다';

$lang['Create_theme'] = '테마 만들기';
$lang['Create_theme_explain'] = '아래의 양식을 사용하여 선택된 템플릿에 대한 새로운 테마를 만들수 있습니다. 색을 입력시(16진수 사용하여), # 를 사용하지 마십시오, 즉, CCCCCC 는 올바른 표기이고, #CCCCCC 는 잘못된 표기입니다';

$lang['Export_themes'] = '테마 내보내기';
$lang['Export_explain'] = '여기에서는 선택된 템플릿에 대한 테마를 내보내기 할 수 있습니다. 아래의 리스트에서 템플릿를 선택하면 스크립트가 테마 구성 파일을 작성하고 그 파일을 템플릿 디렉토리에 저장합니다. 저장에 실패하면 다운로드 옵션이 표시될 것입니다. 스크립트가 파일을 저장할 수 있도록 하려면 선택된 템플릿 디렉토리에 대한 웹서버에 쓰기 권한을 줘야 합니다. 보다 자세한 내용은 phpBB 2 사용자 설명서를 참조하십시오.';

$lang['Theme_installed'] = '선택된 테마가 성공적으로 설치되었습니다';
$lang['Style_removed'] = '선택된 스타일이 데이터베이스에서 삭제되었습니다. 시스템에서 이 스타일을 완전히 지우려면 템플릿 디렉토리에서 해당 스타일을 지워야 합니다.';
$lang['Theme_info_saved'] = '선택된 템플릿에 대한 테마 정보가 저장되었습니다. 이제 theme_info.cfg (또한 선택된 템플릿 디렉토리) 에 대한 권한을 읽기-전용으로 바꿔 놓아야 합니다';
$lang['Theme_updated'] = '선택된 테마가 업데이트되었습니다. 이제 새로운 테마 설정을 내보내기 해야 합니다';
$lang['Theme_created'] = '테마가 만들어졌습니다.이제 보관을 위해서나 다른 곳에서 사용하려면 테마를 테마 구성 파일로 내보내기 해야 합니다';

$lang['Confirm_delete_style'] = '이 스타일을 지우시겠습니까';

$lang['Download_theme_cfg'] = '내보내기가 테마 정보 파일을 작성할 수 없었습니다. 아래의 버튼을 클릭하여 이 파일을 브라우저로 다운로드 하십시오. 다운로드한 다음에 파일을 템플릿가 있는 디렉토리로 옮길수 있습니다. 그런 다음, 원한다면 파일을 패키지하여 배포용이나 기타 용도로 사용할 수 있습니다';
$lang['No_themes'] = '선택된 템플릿에 첨부된 테마가 없습니다. 새로운 테마를 만들려면 왼쪽의 새로 만들기 링크를 클릭하십시오';
$lang['No_template_dir'] = '템플릿 디렉토리를 열 수 없습니다. 웹서버가 읽을수 없거나 존재하지 않습니다';
$lang['Cannot_remove_style'] = '선택된 스타일은 현재 게시판 기본값이기때문에 삭제할 수 없습니다. 기본 스타일을 바꾼 다음에 다시 시도해 보십시오.';
$lang['Style_exists'] = '선택된 스타일 이름이 이미 존재 하므로 돌아가서 다른 이름을 선택하십시오.';

$lang['Click_return_styleadmin'] = '스타일 관리로 돌아가려면 %s여기%s를 클릭하십시오';

$lang['Theme_settings'] = '테마 설정';
$lang['Theme_element'] = '테마 요소';
$lang['Simple_name'] = '단순 이름';
$lang['Value'] = '값';
$lang['Save_Settings'] = '설정 저장';

$lang['Stylesheet'] = 'CSS 스타일시트';
$lang['Background_image'] = '배경 이미지';
$lang['Background_color'] = '배경 색';
$lang['Theme_name'] = '테마 이름';
$lang['Link_color'] = '링크 색';
$lang['Text_color'] = '문자 색';
$lang['VLink_color'] = '방문한 링크 색';
$lang['ALink_color'] = '활동 링크 색';
$lang['HLink_color'] = 'Hover 링크 색';
$lang['Tr_color1'] = '테이블 열 색 1';
$lang['Tr_color2'] = '테이블 열 색2';
$lang['Tr_color3'] = '테이블 열 색 3';
$lang['Tr_class1'] = '테이블 열 클래스 1';
$lang['Tr_class2'] = '테이블 열 클래스 2';
$lang['Tr_class3'] = '테이블 열 클래스 3';
$lang['Th_color1'] = '테이블 헤더 색 1';
$lang['Th_color2'] = '테이블 헤더 색  2';
$lang['Th_color3'] = '테이블 헤더 색 3';
$lang['Th_class1'] = '테이블 헤더 클래스 1';
$lang['Th_class2'] = '테이블 헤더 클래스 2';
$lang['Th_class3'] = '테이블 헤더 클래스 3';
$lang['Td_color1'] = '테이블 셀 색 1';
$lang['Td_color2'] = '테이블 셀 색 2';
$lang['Td_color3'] = '테이블 셀 색 3';
$lang['Td_class1'] = '테이블 셀 클래스 1';
$lang['Td_class2'] = '테이블 셀 클래스 2';
$lang['Td_class3'] = '테이블 셀 클래스 3';
$lang['fontface1'] = '폰트 모양 1';
$lang['fontface2'] = '폰트 모양 2';
$lang['fontface3'] = '폰트 모양 3';
$lang['fontsize1'] = '폰트 크기 1';
$lang['fontsize2'] = '폰트 크기 2';
$lang['fontsize3'] = '폰트 크기 3';
$lang['fontcolor1'] = '폰트 색 1';
$lang['fontcolor2'] = '폰트 색 2';
$lang['fontcolor3'] = '폰트 색 3';
$lang['span_class1'] = '스팬 클래스 1';
$lang['span_class2'] = '스팬 클래스 2';
$lang['span_class3'] = '스팬 클래스 3';
$lang['img_poll_size'] = '투표 이미지 크기 [px]';
$lang['img_pm_size'] = '비공개 메시지 상태 크기 [px]';


//
// Install Process
//
$lang['Welcome_install'] = 'phpBB 2 설치를 환영합니다';
$lang['Initial_config'] = '기본 구성';
$lang['DB_config'] = '데이터베이스 구성';
$lang['Admin_config'] = '운영 구성';
$lang['continue_upgrade'] = '구성 파일을 일단 컴퓨터로 다운로드한 다음에는 하단의 \'업그레이드 계속\' 버튼으로 업그레이드 과정을 계속할 수 있습니다.  구성 파일의 업로드를 하려면 업그레이드가 완료될때까지 기다리십시오.';
$lang['upgrade_submit'] = '업그레이드 계속';

$lang['Installer_Error'] = '설치하는 동안 에러가 발생했습니다';
$lang['Previous_Install'] = '이전 설치를 발견했습니다';
$lang['Install_db_error'] = '데이터베이스 업데이트에 에러가 발생했습니다';

$lang['Re_install'] = '이전 설치한 것이 아직 작동중입니다. <br /><br />phpBB2 를 재설치 하려면 아래의 Yes 버튼을 클릭하십시오. 기존의 데이터는 모두 없어지며 백업도 만들어 지지 않음을 주의하십시오. 게시판 로그인시에 사용했던 운영자의 사용자이름과 비밀번호는 재설치후에 다시 만들어지지만, 그 외의 다른 설정들은 복구되지 않습니다. <br /><br />Yes를 누르기전에 잘 생각해 보시기 바랍니다!';

$lang['Inst_Step_0'] = 'phpBB 2를 선택해 주셔서 감사합니다. 본 설치를 완료하려면 아래 요구사항을 기재하십시오. 설치하려는 데이터베이스가 이미 존재해야 하는 것을 주지하십시오. MS 액세스와 같이 ODBC를 사용하는 데이터베이스에 설치한다면, 진행전에 해당 DSN을 먼저 만들어야 합니다.';

$lang['Start_Install'] = '설치 시작';
$lang['Finish_Install'] = '설치 완료';

$lang['Default_lang'] = '기본 게시판 언어';
$lang['DB_Host'] = '데이터베이스 서버 호스트이름 / DSN';
$lang['DB_Name'] = '데이터베이스 이름';
$lang['DB_Username'] = '데이터베이스 사용자이름';
$lang['DB_Password'] = '데이터베이스 비밀번호';
$lang['Database'] = '데이터베이스';
$lang['Install_lang'] = '설치용 언어 선택';
$lang['dbms'] = '데이터베이스 형식';
$lang['Table_Prefix'] = '데이터베이스 테이블용 서문(prefix)';
$lang['Admin_Username'] = '운영자 사용자이름';
$lang['Admin_Password'] = '운영자 비밀번호';
$lang['Admin_Password_confirm'] = '운영자 비밀번호  [ 확인 ]';

$lang['Inst_Step_2'] = '운영자 사용자이름이 만들어졌습니다. 이제 기본적인 설치가 완료되었습니다. 다음 화면에서 설치를 조율할 수 있습니다. 일반 설정의 세부사항들을 확인하고 필요한 수정을 하십시오. phpBB 2를 선택해 주셔서 감사합니다.';

$lang['Unwriteable_config'] = '구성 파일이 현재 쓰기가 안됩니다. 아래의 버튼을 클릭하면 구성 파일의 복사본이 시스템으로 다운로드 될 것입니다. 이 파일을 phpBB 2 와 동일한 디렉토리에 업로드해야 합니다. 그런 다음, 앞의 양식에서 입력한 운영자 이름과 비밀번호로 로그인하고 운영자 제어 센터(로그인하면 각 화면의 밑에 링크가 나타날 것입니다)로 가서 일반 구성을 확인해야 합니다. phpBB 2를 선택해 주셔서 감사합니다.';
$lang['Download_config'] = '구성 파일 다운로드';

$lang['ftp_choose'] = '다운로드 방법 선택';
$lang['ftp_option'] = '<br />이 버전의 PHP는 FTP 사용이 가능하기 때문에 우선적으로 자동으로 구성 파일을 ftp 할 것인지를 선택하는 옵션이 주어집니다.';
$lang['ftp_instructs'] = '파일을 자동으로 phpBB 2 가 있는 계정으로 ftp 하도록 선택하였습니다. 그 작업을 돕는 정보를 아래에 입력하십시오. 다른 ftp 프로그램을 사용할 때와 마찬가지로 FTP 경로는 정확한 경로 여야 합니다.';
$lang['ftp_info'] = 'FTP 정보를 입력하십시오';
$lang['Attempt_ftp'] = '구성 파일 전송 시도';
$lang['Send_file'] = '파일을 내게 보내면 내가 수동으로 ftp 하겠슴';
$lang['ftp_path'] = 'phpBB 2 에 대한 FTP 경로';
$lang['ftp_username'] = 'FTP 사용자이름';
$lang['ftp_password'] = 'FTP 비밀번호';
$lang['Transfer_config'] = '전송 시작';
$lang['NoFTP_config'] = '구성 파일의 전송이 실패했습니다. 구성 파일을 다운로드하여 수동으로 ftp 전송하십시오.';

$lang['Install'] = '설치';
$lang['Upgrade'] = '업그레이드';


$lang['Install_Method'] = '설치 방법을 선택하십시오';

$lang['Install_No_Ext'] = '서버상의 php 구성이 선택된 데이터베이스를 지원하지 않습니다';

$lang['Install_No_PCRE'] = 'phpBB2는 php용 Perl-Compatible Regular Expressions Module을 필요로 하는데 귀하의 php 구성은 그것을 지원하지 않고 있습니다!';

//
// That's all Folks!
// -------------------------------------------------

?>