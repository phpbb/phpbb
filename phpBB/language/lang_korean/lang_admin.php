<?php

/***************************************************************************
 *                            lang_admin.php [English]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin.php,v 1.25 2001/12/24 16:37:48 the_systech Exp $
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "전체포럼설정";
$lang['Users'] = "회원관리";
$lang['Groups'] = "그룹관리";
$lang['Forums'] = "포럼관리";
$lang['Styles'] = "스타일 관리";

$lang['Configuration'] = "포럼환경설정";
$lang['Permissions'] = "권한설정";
$lang['Manage'] = "전체관리";
$lang['Disallow'] = "승인거부 회원";
$lang['Prune'] = "일자 지정";
$lang['Mass_Email'] = "그룹메일(메일링)";
$lang['Ranks'] = "Level(랭킹)설정";
$lang['Smilies'] = "스마일아이콘";
$lang['Ban_Management'] = "접속차단회원";
$lang['Word_Censor'] = "텍스트 필터";
$lang['Export'] = "내 보내기";
$lang['Create_new'] = "만들기";
$lang['Add_new'] = "추가";
$lang['Backup_DB'] = "데이타베이스 백업";
$lang['Restore_DB'] = "데이타베이스 복구";


//
// Index
//
$lang['Admin'] = "관리자페이지";
$lang['Not_admin'] = "당신은 이포럼의 고나리자가 아닙니다.";
$lang['Welcome_phpBB'] = "phpBB에 오신것을 환영합니다.";
$lang['Admin_intro'] = "phpBB 를 선택해주신 당신에게 감사드립니다..이 스크린은 포럼의 모든 다양한기능과 통계에대해서 빠른 결과를 보여줍니다.. <u>Admin Index</u> 를 클릭하면 이전페이지로 되돌아갈수 있으며 phpBB 로고를 클릭하시면 메인페이지로 이동합니다.. 이 스크린의 왼쪽 측면에있는 링크들은 당신이 당신의 모든 포럼경험과,에스팩트를 통제하도록 할것입니다..";
$lang['Main_index'] = "포럼 메인";
$lang['Forum_stats'] = "메인관리 페이지";
$lang['Admin_Index'] = "메인 관리페이지";
$lang['Preview_forum'] = "포럼 미리보기";

$lang['Click_return_admin_index'] = "%s메인 관리페이지로%s ";

$lang['Statistic'] = "통계치";
$lang['Value'] = "가치";
$lang['Number_posts'] = "전체 게시물";
$lang['Posts_per_day'] = "1일 게시물 당 %";
$lang['Number_topics'] = "전체 주제글";
$lang['Topics_per_day'] = "1일 주제글 당 %";
$lang['Number_users'] = "총 회원 수";
$lang['Users_per_day'] = "1일 유저 당 %";
$lang['Board_started'] = "포럼 오픈일자";
$lang['Avatar_dir_size'] = "아바타 디렉토리 싸이즈";
$lang['Database_size'] = "데이타베이스 싸이즈";
$lang['Gzip_compression'] ="Gzip 압축";
$lang['Not_available'] = "사용할수 없음";

$lang['ON'] = "ON"; // This is for GZip compression
$lang['OFF'] = "OFF"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "데이터베이스 유틸리티";

$lang['Restore'] = "복구";
$lang['Backup'] = "백업";
$lang['Restore_explain'] = "구해진 파일로부터 모든 phpBB의 테이블들을 복구하여 저장합니다..서버가 GZIP을 지원하면 압축된 GZIP 텍스트 파일을 업로드 할수 있으며 자동으로 용량이 줄어질것입니다..<b>경고</b> : 이것은 현재의 디비에 덮어씁니다..복구 시간이 길게걸릴지도 모르는것은 복구가 완전해질때까지 페이지가 움직이지 않기때문입니다.";
$lang['Backup_explain'] = "여기서 당신은 phpBB의 의 백업받은 데이터를 모두 처리할수 있습니다..";

$lang['Backup_options'] = "백업옵션";
$lang['Start_backup'] = "백업시작";
$lang['Full_backup'] = "전부 백업";
$lang['Structure_backup'] = "구조만 백업";
$lang['Data_backup'] = "데이타만 백업";
$lang['Additional_tables'] = "테이블 추가";
$lang['Gzip_compress'] = "Gzip 파일로 압축";
$lang['Select_file'] = "파일선택";
$lang['Start_Restore'] = "복구시작";

$lang['Restore_success'] = "데이터베이스가 성공적으로 복구되었습니다..<br /><br />백업받은 시기의 포럼이 원래자리에 있어야 합니다..";
$lang['Backup_download'] = "다운로드가 시작될 때까지 기다리세요.";
$lang['Backups_not_supported'] = "죄송합니다..현재 당신의 데이터베이스 시스템이 데이터베이스 백업을 지원하지 않습니다..";

$lang['Restore_Error_uploading'] = "백업파일 업로딩 Error";
$lang['Restore_Error_filename'] = "파일이름에 문제가 있습니다..검토해보시고 다시 시도해주세요..";
$lang['Restore_Error_decompress'] = "gzip 파일의 압축을 줄일수 없습니다..원문버전을 업로드 하여주세요.";
$lang['Restore_Error_no_file'] = "파일이 업로드되지 않았습니다.";


//
// Auth pages
//
$lang['Select_a_User'] = "회원선택";
$lang['Select_a_Group'] = "그룹선택";
$lang['Select_a_Forum'] = "포럼선택";
$lang['Auth_Control_User'] = "회원권한설정"; 
$lang['Auth_Control_Group'] = "그룹권한설정"; 
$lang['Auth_Control_Forum'] = "포럼권한서렁"; 
$lang['Look_up_User'] = "회원 보기"; 
$lang['Look_up_Group'] = "그룹 보기"; 
$lang['Look_up_Forum'] = "포럼 보기"; 

$lang['Group_auth_explain'] = "사용권한을 바꿀수 있습니다..그리고 현재관리자가 유저그룹에 관리자신분을 양도하였습니다.. 가입을 신청한 유저들의 권한 분류,승인등을 잊어버리지 말아주세요..만약 지금이 그 경우이면 당신은 경고받을 것입니다.";
$lang['User_auth_explain'] = "사용권한을 바꿀수 있습니다..그리고 현재관리자가 유저그룹에 관리자신분을 양도하였습니다.. 가입을 신청한 유저들의 권한 분류,승인등을 잊어버리지 말아주세요..만약 지금이 그 경우이면 당신은 경고받을 것입니다.";
$lang['Forum_auth_explain'] = "여기서 각 포럼의 레벨을 바꿀수 있습니다.. ";

$lang['Simple_mode'] = "간단옵션설정 Mode";
$lang['Advanced_mode'] = "세부옵션설정 Mode";
$lang['Moderator_status'] = "관리자 등급";

$lang['Allowed_Access'] = "접근허용";
$lang['Disallowed_Access'] = "접근차단";
$lang['Is_Moderator'] = "관리자 지정";
$lang['Not_Moderator'] = "관리자 해제";

$lang['Conflict_warning'] = "경고합니다.";
$lang['Conflict_access_userauth'] = "이 사용자는 이포럼에 그룹회원자격과 접근권한을 가지고 있습니다..당신은 이 사용자의 그룹 사용권한을 바꾸거나 제거할수 있습니다.";
$lang['Conflict_mod_userauth'] = "이 사용자는 이포럼에 그룹회원자격과 접근권한을 가지고 있습니다..당신은 이 사용자의 그룹 사용권한을 바꾸거나 제거할수 있습니다.";

$lang['Conflict_access_groupauth'] = "그 다음 사용자는 사용자의 사용권한환경을 경유하는 이 포럼에의 접근권한을 여전히 가지고 있습니다..접근 권한을 가지고있는 사용자들을 보호하기위해 위해 사용자 사용권한을 바꿀수 있습니다..그리고 사용자들은 권한이  변경되는것을 인정하였습니다.";
$lang['Conflict_mod_groupauth'] = "그 다음 사용자는 사용자의 사용권한환경을 경유하는 이 포럼에의 접근권한을 여전히 가지고 있습니다..접근 권한을 가지고있는 사용자들을 보호하기위해 위해 사용자 사용권한을 바꿀수 있습니다..그리고 사용자들은 권한이  변경되는것을 인정하였습니다.";

$lang['Public'] = "공개포럼";
$lang['Private'] = "비밀포럼";
$lang['Registered'] = "회원포럼";
$lang['Administrators'] = "관리자";
$lang['Hidden'] = "숨김";

$lang['View'] = "보임";
$lang['Read'] = "읽기";
$lang['Post'] = "작성";
$lang['Reply'] = "리플";
$lang['Edit'] = "수정";
$lang['Delete'] = "삭제";
$lang['Sticky'] = "읽어보기";
$lang['Announce'] = "공지사항"; 
$lang['Vote'] = "투표";
$lang['Pollcreate'] = "설문조사만들기";

$lang['Permissions'] = "접근 권한설정";
$lang['Simple_Permission'] = "단순 사용권한";

$lang['User_Level'] = "회원레벨"; 
$lang['Auth_User'] = "회원";
$lang['Auth_Admin'] = "관리자";
$lang['Group_memberships'] = "유저그룹 멤버쉽";
$lang['Usergroup_members'] = "현재그룹멤버 회원";

$lang['Forum_auth_updated'] = "포럼의 접근권한 설정을 업데이트 하였습니다.";
$lang['User_auth_updated'] = "회원의 접근권한 설정을 업데이트 하였습니다.";
$lang['Group_auth_updated'] = "그룹권한 업데이트";

$lang['Auth_updated'] = "권한설정 업데이트";
$lang['Click_return_userauth'] = "Click %sHere%s to return to User Permissions";
$lang['Click_return_groupauth'] = "Click %sHere%s to return to Group Permissions";
$lang['Click_return_forumauth'] = "Click %sHere%s to return to Forum Permissions";


//
// Banning
//
$lang['Ban_control'] = "접속차단 관리";
$lang['Ban_explain'] = "여기서 당신은 접속차단 제어를 할수 있습니다.. 정확한 IP 혹은 hostnames을 이용하여 설정할수 있습니다. . 이 방법은 차단된 회원이 포럼의 index 페이지에 도달하지 못하게 합니다. ";
$lang['Ban_explain_warn'] = "차단 리스트에 들어가는 IP 주소의 범위안에 시작과 끝사이의 모든 주소들에 기인하는것을 주의 하십시요";

$lang['Select_username'] = "아이디를 선택하세요";
$lang['Select_ip'] = "IP를 선택하세요";
$lang['Select_email'] = "Email을 선택하세요";

$lang['Ban_username'] = "1명 차단  or 더 많은 특정 사용자";
$lang['Ban_username_explain'] = "1명의 사용자에게  권한을 차단하면 이 포럼에서 마우스와 키보드의 콤비네이션이 이루어지지 않습니다.";

$lang['Ban_IP'] = "Ban one or more IP addresses or hostnames";
$lang['IP_hostname'] = "IP addresses or hostnames";
$lang['Ban_IP_explain'] = "To specify several different IP's or hostnames separate them with commas. To specify a range of IP addresses separate the start and end with a hyphen (-), to specify a wildcard use *";

$lang['Ban_email'] = "Ban one or more email addresses";
$lang['Ban_email_explain'] = "To specify more than one email address separate them with commas. To specify a wildcard username use *, for example *@hotmail.com";

$lang['Unban_username'] = "Un-ban one more specific users";
$lang['Unban_username_explain'] = "You can unban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser";

$lang['Unban_IP'] = "Un-ban one or more IP addresses";
$lang['Unban_IP_explain'] = "You can unban multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser";

$lang['Unban_email'] = "Un-ban one or more email addresses";
$lang['Unban_email_explain'] = "You can unban multiple email addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser";

$lang['No_banned_users'] = "No banned usernames";
$lang['No_banned_ip'] = "No banned IP addresses";
$lang['No_banned_email'] = "No banned email addresses";

$lang['Ban_update_sucessful'] = "The banlist has been updated successfully";
$lang['Click_return_banadmin'] = "Click %sHere%s to return to Ban Control";


//
// Configuration
//
$lang['General_Config'] = "포럼 전체 환경설정";
$lang['Config_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.";

$lang['Click_return_config'] = "%s포럼환경설정 페이지로 되돌아가기%s";

$lang['General_settings'] = "포럼 전체 환경설정";
$lang['Site_name'] = "싸이트 이름";
$lang['Site_desc'] = "싸이트 소개";
$lang['Board_disable'] = "포럼 잠그기";
$lang['Board_disable_explain'] = "현재의 포럼을 일시로 잠그거나 포럼 문을닫을때 이옵션에 체크하시면 모든포럼은 닫히게 됩니다..";
$lang['Acct_activation'] = "Enable account activation";
$lang['Acc_None'] = "None"; // These three entries are the type of activation
$lang['Acc_User'] = "User";
$lang['Acc_Admin'] = "Admin";

$lang['Abilities_settings'] = "포럼/회원 기본정보 셋팅";
$lang['Max_poll_options'] = "설문옵션 최대 수 지정";
$lang['Flood_Interval'] = "글 작성시간 설정";
$lang['Flood_Interval_explain'] = "지정한 시간동안 동작이없음 자동로그아웃 "; 
$lang['Board_email_form'] = "포럼에서 이메일 사용하기";
$lang['Board_email_form_explain'] = "회원아이디로 회원에게 메일을 보내는기능";
$lang['Topics_per_page'] = "다음페이지 이동 주제글 수";
$lang['Posts_per_page'] = "다음페이지 이동 리플 수";
$lang['Hot_threshold'] = "아이콘표시 인기게시물 수";
$lang['Default_style'] = "기본스타일";
$lang['Override_style'] = "회원 스타일선택";
$lang['Override_style_explain'] = "스타일을 회원이 선택할수있도록 지정";
$lang['Default_language'] = "기본언어";
$lang['Date_format'] = "날짜포맷";
$lang['System_timezone'] = "시간선택";
$lang['Enable_gzip'] = "GZip 압축";
$lang['Enable_prune'] = "자동삭제기능";
$lang['Allow_HTML'] = "HTML 허용";
$lang['Allow_BBCode'] = "BBCode 허용";
$lang['Allowed_tags'] = "HTML tags 입력";
$lang['Allowed_tags_explain'] = "태그는 , 로 구분합니다.";
$lang['Allow_smilies'] = "Smilies 허용";
$lang['Smilies_path'] = "Smilies 경로";
$lang['Smilies_path_explain'] = "FullPath 가 아닌 phpbb 루트디렉토리에서부터(images/smilies)";
$lang['Allow_sig'] = "서명사용";
$lang['Max_sig_length'] = "서명 글자 수";
$lang['Max_sig_length_explain'] = "회원서명은 255자를 넘을수 없습니다.";
$lang['Allow_name_change'] = "아이디 바꾸기";

$lang['Avatar_settings'] = "아바타 셋팅";
$lang['Allow_local'] = "아바타겔러리";
$lang['Allow_remote'] = "아바타링크";
$lang['Allow_remote_explain'] = "온라인상에 있는 아바타를 링크합니다.";
$lang['Allow_upload'] = "아바타업로드";
$lang['Max_filesize'] = "아바타 최대싸이즈";
$lang['Max_filesize_explain'] = "업로드하기위한 아바타 파일";
$lang['Max_avatar_size'] = "아바타 크기";
$lang['Max_avatar_size_explain'] = "(세로 x 가로 in pixels)";
$lang['Avatar_storage_path'] = "아바타 경로";
$lang['Avatar_storage_path_explain'] = "FullPath 가 아닌 phpbb 루트디렉토리에서부터(images/avatars)";
$lang['Avatar_gallery_path'] = "아바타 Gallery Path";
$lang['Avatar_gallery_path_explain'] = "FullPath 가 아닌 phpbb 루트디렉토리에서부터(images/avatars/gallery)";

$lang['COPPA_settings'] = "COPPA Settings";
$lang['COPPA_fax'] = "COPPA Fax Number";
$lang['COPPA_mail'] = "COPPA Mailing Address";
$lang['COPPA_mail_explain'] = "This is the mailing address where parents will send COPPA registration forms";

$lang['Email_settings'] = "Email 셋팅";
$lang['Admin_email'] = "관리자 이 메일";
$lang['Email_sig'] = "이 메일 서명";
$lang['Email_sig_explain'] = "포럼에서 메일을 보낼때 들어가는 서명입니다.";
$lang['Use_SMTP'] = "Use SMTP Server for email";
$lang['Use_SMTP_explain'] = "Say yes if you want or have to send email via a named server instead of the local mail function";
$lang['SMTP_server'] = "SMTP Server Address";

$lang['Disable_privmsg'] = "쪽지 박스";
$lang['Inbox_limits'] = "쪽지 보관 수";
$lang['Sentbox_limits'] = "보낸쪽지 보관 수";
$lang['Savebox_limits'] = "저장된 쪽지 보관 수";

$lang['Cookie_settings'] = "쿠키 셋팅"; 
$lang['Cookie_settings_explain'] = "These control how the cookie sent to browsers is defined. In most cases the default should be sufficient. If you need to change these do so with care, incorrect settings can prevent users logging in.";
$lang['Cookie_name'] = "Cookie name";
$lang['Cookie_domain'] = "Cookie domain";
$lang['Cookie_path'] = "Cookie path";
$lang['Session_length'] = "Session length [ seconds ]";
$lang['Cookie_secure'] = "Cookie secure [ https ]";


//
// Forum Management
//
$lang['Forum_admin'] = "포럼관리자영역";
$lang['Forum_admin_explain'] = "관리자는 지금의 포럼을 삭제,생성,수정할수있으며 포럼의 순서,씽크등을 수행할수있습니다.";
$lang['Edit_forum'] = "포럼수정";
$lang['Create_forum'] = "새포럼 추가";
$lang['Create_category'] = "포럼카테고리 타이틀";
$lang['Remove'] = "제거";
$lang['Action'] = "실행";
$lang['Update_order'] = "업데이트순서";
$lang['Config_updated'] = "포럼환경설정을 성공적으로 업데이트 하였습니다.";
$lang['Edit'] = "수정";
$lang['Delete'] = "삭제";
$lang['Move_up'] = "위로 이동";
$lang['Move_down'] = "밑으로이동";
$lang['Resync'] = "데이타정렬(Resync)";
$lang['No_mode'] = "No mode was set";
$lang['Forum_edit_delete_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side";

$lang['Move_contents'] = "콘텐츠 전부이동 ";
$lang['Forum_delete'] = "포럼 삭제";
$lang['Forum_delete_explain'] = "The form below will allow you to delete a forum (or category) and decide where you want to put all topics (or forums) it contained.";

$lang['Forum_settings'] = "전체 포럼설정";
$lang['Forum_name'] = "포럼이름";
$lang['Forum_desc'] = "소개";
$lang['Forum_status'] = "포럼등급";
$lang['Forum_pruning'] = "자동삭제";

$lang['prune_freq'] = '삭제될 주제글 수';
$lang['prune_days'] = "삭제될 주제글수의 게시기간";
$lang['Set_prune_data'] = "You have turned on auto-prune for this forum but did not set a frequency or number of days to prune. Please go back and do so";

$lang['Move_and_Delete'] = "이동 or 삭제";

$lang['Delete_all_posts'] = "전체게시물 삭제";
$lang['Nowhere_to_move'] = "이동표시 남기지않음";

$lang['Edit_Category'] = "카테고리 수정";
$lang['Edit_Category_explain'] = "현재포럼 카테고리 수정";

$lang['Forums_updated'] = "포럼의 카테고리정보를 성공적으로 업데이트 하였습니다.";

$lang['Must_delete_forums'] = "You need to delete all forums before you can delete this category";

$lang['Click_return_forumadmin'] = "%s 이전 페이지로 되돌아가기%s";


//
// Smiley Management
//
$lang['smiley_title'] = "Smiles Editing Utility";
$lang['smile_desc'] = "From this page you can add, remove and edit the emoticons or smileys your users can use in their posts and private messages.";

$lang['smiley_config'] = "Smiley Configuration";
$lang['smiley_code'] = "Smiley Code";
$lang['smiley_url'] = "Smiley Image File";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smile_add'] = "Add a new Smiley";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emotion";

$lang['Select_pak'] = "Select Pack (.pak) File";
$lang['replace_existing'] = "Replace Existing Smiley";
$lang['keep_existing'] = "Keep Existing Smiley";
$lang['smiley_import_inst'] = "You should unzip the smiley package and upload all files to the appropriate Smiley directory for your installation.  Then select the correct information in this form to import the smiley pack.";
$lang['smiley_import'] = "Smiley Pack Import";
$lang['choose_smile_pak'] = "Choose a Smile Pack .pak file";
$lang['import'] = "Import Smileys";
$lang['smile_conflicts'] = "What should be done in case of conflicts";
$lang['del_existing_smileys'] = "Delete existing smileys before import";
$lang['import_smile_pack'] = "Import Smiley Pack";
$lang['export_smile_pack'] = "Create Smiley Pack";
$lang['export_smiles'] = "To create a smiley pack from your currently installed smileys, click %sHere%s to download the smiles.pak file. Name this file appropriately making sure to keep the .pak file extension.  Then create a zip file containing all of your smiley images plus this .pak configuration file.";

$lang['smiley_add_success'] = "The Smiley was successfully added";
$lang['smiley_edit_success'] = "The Smiley was successfully updated";
$lang['smiley_import_success'] = "The Smiley Pack was imported successfully!";
$lang['smiley_del_success'] = "The Smiley was successfully removed";
$lang['Click_return_smileadmin'] = "Click %sHere%s to return to Smiley Administration";


//
// User Management
//
$lang['User_admin'] = "회원관리영역";
$lang['User_admin_explain'] = "Here you can change your user's information and certain specific options. To modify the users permissions please use the user and group permissions system.";

$lang['Look_up_user'] = "아이디 보기";

$lang['Admin_user_fail'] = "Couldn't update the users profile.";
$lang['Admin_user_updated'] = "The user's profile was successfully updated.";
$lang['Click_return_useradmin'] = "Click %sHere%s to return to User Administration";

$lang['User_delete'] = "Delete this user";
$lang['User_delete_explain'] = "Click here to delete this user, this cannot be undone.";
$lang['User_deleted'] = "User was successfully deleted.";

$lang['User_status'] = "User is active";
$lang['User_allowpm'] = "Can send Private Messages";
$lang['User_allowavatar'] = "Can display avatar";

$lang['Admin_avatar_explain'] = "Here you can see and delete the user's current avatar.";

$lang['User_special'] = "Special admin-only fields";
$lang['User_special_explain'] = "These fields are not able to be modified by the users.  Here you can set their status and other options that are not given to users.";


//
// Group Management
//
$lang['Group_administration'] = "Group Administration";
$lang['Group_admin_explain'] = "From this panel you can administer all your usergroups, you can; delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description";
$lang['Error_updating_groups'] = "There was an error while updating the groups";
$lang['Updated_group'] = "The group was successfully updated";
$lang['Added_new_group'] = "The new group was successfully created";
$lang['Deleted_group'] = "The group was successfully deleted";
$lang['New_group'] = "Create new group";
$lang['Edit_group'] = "Edit group";
$lang['group_name'] = "Group name";
$lang['group_description'] = "Group description";
$lang['group_moderator'] = "Group moderator";
$lang['group_status'] = "Group status";
$lang['group_open'] = "Open group";
$lang['group_closed'] = "Closed group";
$lang['group_hidden'] = "Hidden group";
$lang['group_delete'] = "Delete group";
$lang['group_delete_check'] = "Delete this group";
$lang['submit_group_changes'] = "Submit Changes";
$lang['reset_group_changes'] = "Reset Changes";
$lang['No_group_name'] = "You must specify a name for this group";
$lang['No_group_moderator'] = "You must specify a moderator for this group";
$lang['No_group_mode'] = "You must specify a mode for this group, open or closed";
$lang['delete_group_moderator'] = "Delete the old group moderator?";
$lang['delete_moderator_explain'] = "If you're changing the group moderator, check this box to remove the old moderator from the group.  Otherwise, do not check it, and the user will become a regular member of the group.";
$lang['Click_return_groupsadmin'] = "Click %sHere%s to return to Group Administration.";
$lang['Select_group'] = "Select a group";
$lang['Look_up_group'] = "Look up group";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Forum Prune";
$lang['Forum_Prune_explain'] = "This will delete any topic which has not been posted to within the number of days you select. If you do not enter a number then all topics will be deleted. It will not remove topics in which polls are still running nor will it remove announcements. You will need to remove these topics manually.";
$lang['Do_Prune'] = "Do Prune";
$lang['All_Forums'] = "All Forums";
$lang['Prune_topics_not_posted'] = "Prune topics with no replies in this many days";
$lang['Topics_pruned'] = "Topics pruned";
$lang['Posts_pruned'] = "Posts pruned";
$lang['Prune_success'] = "Pruning of forums was successful";


//
// Word censor
//
$lang['Words_title'] = "Word Censoring";
$lang['Words_explain'] = "From this control panel you can add, edit, and remove words that will be automatically censored on your forums. In addition people will not be allowed to register with usernames containing these words. Wildcards (*) are accepted in the word field, eg. *test* will match detestable, test* would match testing, *test would match detest.";
$lang['Word'] = "Word";
$lang['Edit_word_censor'] = "Edit word censor";
$lang['Replacement'] = "Replacement";
$lang['Add_new_word'] = "Add new word";
$lang['Update_word'] = "Update word censor";

$lang['Must_enter_word'] = "You must enter a word and its replacement";
$lang['No_word_selected'] = "No word selected for editing";

$lang['Word_updated'] = "The selected word censor has been successfully updated";
$lang['Word_added'] = "The word censor has been successfully added";
$lang['Word_removed'] = "The selected word censor has been successfully removed";

$lang['Click_return_wordadmin'] = "Click %sHere%s to return to Word Censor Administration";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Here you can email a message to either all of your users, or all users of a specific group.  To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all recipients. If you are emailing a large group of people please be patient after submitting and do not stop the page halfway through. It is normal for amass emailing to take a long time, you will be notified when the script has completed";
$lang['Compose'] = "Compose"; 

$lang['Recipients'] = "Recipients"; 
$lang['All_users'] = "All Users";

$lang['Email_successfull'] = "Your message has been sent";
$lang['Click_return_massemail'] = "Click %sHere%s to return to the Mass Email form";


//
// Ranks admin
//
$lang['Ranks_title'] = "Rank Administration";
$lang['Ranks_explain'] = "Using this form you can add, edit, view and delete ranks. You can also create custom ranks which can be applied to a user via the user management facility";

$lang['Add_new_rank'] = "Add new rank";

$lang['Rank_title'] = "Rank Title";
$lang['Rank_special'] = "Set as Special Rank";
$lang['Rank_minimum'] = "Minimum Posts";
$lang['Rank_maximum'] = "Maximum Posts";
$lang['Rank_image'] = "Rank Image (Relative to phpBB2 root path)";
$lang['Rank_image_explain'] = "Use this to define a small image associated with the rank";

$lang['Must_select_rank'] = "You must select a rank";
$lang['No_assigned_rank'] = "No special rank assigned";

$lang['Rank_updated'] = "The rank was successfully updated";
$lang['Rank_added'] = "The rank was successfully added";
$lang['Rank_removed'] = "The rank was successfully deleted";

$lang['Click_return_rankadmin'] = "Click %sHere%s to return to Rank Administration";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Username Disallow Control";
$lang['Disallow_explain'] = "Here you can control usernames which will not be allowed to be used.  Disallowed usernames are allowed to contain a wildcard character of *.  Please note that you will not be allowed to specify any username that has already been registered, you must first delete that name then disallow it";

$lang['Delete_disallow'] = "Delete";
$lang['Delete_disallow_title'] = "Remove a Disallowed Username";
$lang['Delete_disallow_explain'] = "You can remove a disallowed username by selecting the username from this list and clicking submit";

$lang['Add_disallow'] = "Add";
$lang['Add_disallow_title'] = "Add a disallowed username";
$lang['Add_disallow_explain'] = "You can disallow a username using the wildcard character * to match any character";

$lang['No_disallowed'] = "No Disallowed Usernames";

$lang['Disallowed_deleted'] = "The disallowed username has been successfully removed";
$lang['Disallow_successful'] = "The disallowed username has ben successfully added";
$lang['Disallowed_already'] = "The name you entered could not be disallowed. It either already exists in the list, exists in the word censor list, or a matching username is present";

$lang['Click_return_disallowadmin'] = "Click %sHere%s to return to Disallow Username Administration";


//
// Styles Admin
//
$lang['Styles_admin'] = "Styles Administration";
$lang['Styles_explain'] = "Using this facility you can add, remove and manage styles (templates and themes) available to your users";
$lang['Styles_addnew_explain'] = "The following list contains all the themes that are available for the templates you currently have. The items on this list have not yet been installed into the phpBB database. To install a theme simply click the install link beside an entry";

$lang['Select_template'] = "Select a Template";

$lang['Style'] = "Style";
$lang['Template'] = "Template";
$lang['Install'] = "Install";
$lang['Download'] = "Download";

$lang['Edit_theme'] = "Edit Theme";
$lang['Edit_theme_explain'] = "In the form below you can edit the settings for the selected theme";

$lang['Create_theme'] = "Create Theme";
$lang['Create_theme_explain'] = "Use the form below to create a new theme for a selected template. When entering colours (for which you should use hexadecimal notation) you must not include the initial #, i.e.. CCCCCC is valid, #CCCCCC is not";

$lang['Export_themes'] = "Export Themes";
$lang['Export_explain'] = "In this panel you will be able to export the theme data for a selected template. Select the template from the list below and the script will create the theme configuration file and attempt to save it to the selected template directory. If it cannot save the file itself it will give you the option to download it. In order for the script to save the file you must give write access to the webserver for the selected template dir. For more information on this see the phpBB 2 users guide.";

$lang['Theme_installed'] = "The selected theme has been installed successfully";
$lang['Style_removed'] = "The selected style has been removed from the database. To fully remove this style from your system you must delete the appropriate style from your templates directory.";
$lang['Theme_info_saved'] = "The theme information for the selected template has been saved. You should now return the permissions on the theme_info.cfg (and if applicable the selected template directory) to read-only";
$lang['Theme_updated'] = "The selected theme has been updated. You should now export the new theme settings";
$lang['Theme_created'] = "Theme created. You should now export the theme to the theme configuration file for safe keeping or use elsewhere";

$lang['Confirm_delete_style'] = "Are you sure you want to delete this style";

$lang['Download_theme_cfg'] = "The exporter could not write the theme information file. Click the button below to download this file with your browser. Once you have downloaded it you can transfer it to the directory containing the template files. You can then package the files for distribution or use elsewhere if you desire";
$lang['No_themes'] = "The template you selected has no themes attached to it. To create a new theme click the Create New link on the left hand panel";
$lang['No_template_dir'] = "Could not open the template directory. It may be unreadable by the webserver or may not exist";
$lang['Cannot_remove_style'] = "You cannot remove the style selected since it is currently the forum default. Please change the default style and try again.";
$lang['Style_exists'] = "The style name to selected already exists, please go back and choose a different name.";

$lang['Click_return_styleadmin'] = "Click %sHere%s to return to Style Administration";

$lang['Theme_settings'] = "Theme Settings";
$lang['Theme_element'] = "Theme Element";
$lang['Simple_name'] = "Simple Name";
$lang['Value'] = "Value";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Background Image";
$lang['Background_color'] = "Background Colour";
$lang['Theme_name'] = "Theme Name";
$lang['Link_color'] = "Link Colour";
$lang['Text_color'] = "Text Colour";
$lang['VLink_color'] = "Visited Link Colour";
$lang['ALink_color'] = "Active Link Colour";
$lang['HLink_color'] = "Hover Link Colour";
$lang['Tr_color1'] = "Table Row Colour 1";
$lang['Tr_color2'] = "Table Row Colour 2";
$lang['Tr_color3'] = "Table Row Colour 3";
$lang['Tr_class1'] = "Table Row Class 1";
$lang['Tr_class2'] = "Table Row Class 2";
$lang['Tr_class3'] = "Table Row Class 3";
$lang['Th_color1'] = "Table Header Colour 1";
$lang['Th_color2'] = "Table Header Colour 2";
$lang['Th_color3'] = "Table Header Colour 3";
$lang['Th_class1'] = "Table Header Class 1";
$lang['Th_class2'] = "Table Header Class 2";
$lang['Th_class3'] = "Table Header Class 3";
$lang['Td_color1'] = "Table Cell Colour 1";
$lang['Td_color2'] = "Table Cell Colour 2";
$lang['Td_color3'] = "Table Cell Colour 3";
$lang['Td_class1'] = "Table Cell Class 1";
$lang['Td_class2'] = "Table Cell Class 2";
$lang['Td_class3'] = "Table Cell Class 3";
$lang['fontface1'] = "Font Face 1";
$lang['fontface2'] = "Font Face 2";
$lang['fontface3'] = "Font Face 3";
$lang['fontsize1'] = "Font Size 1";
$lang['fontsize2'] = "Font Size 2";
$lang['fontsize3'] = "Font Size 3";
$lang['fontcolor1'] = "Font Colour 1";
$lang['fontcolor2'] = "Font Colour 2";
$lang['fontcolor3'] = "Font Colour 3";
$lang['span_class1'] = "Span Class 1";
$lang['span_class2'] = "Span Class 2";
$lang['span_class3'] = "Span Class 3";
$lang['img_poll_size'] = "Polling Image Size [px]";
$lang['img_pm_size'] = "Private Message Status size [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Welcome to phpBB 2 Installation";
$lang['Initial_config'] = "Basic Configuration";
$lang['DB_config'] = "Database Configuration";
$lang['Admin_config'] = "Admin Configuration";
$lang['continue_upgrade'] = "Once you have downloaded your config file to your local machine you may\"Continue Upgrade\" button below to move forward with the upgrade process.  Please wait to upload the config file until the upgrade process is complete.";
$lang['upgrade_submit'] = "Continue Upgrade";

$lang['Installer_Error'] = "An error has occurred during installation";
$lang['Previous_Install'] = "A previous installation has been detected";
$lang['Install_db_error'] = "An error occurred trying to update the database";

$lang['Re_install'] = "Your previous installation is still active. <br /><br />If you would like to re-install phpBB 2 you should click the Yes button below. Please be aware that doing so will destroy all existing data, no backups will be made! The administrator username and password you have used to login in to the board will be re-created after the re-installation, no other settings will be retained. <br /><br />Think carefully before pressing Yes!";

$lang['Inst_Step_0'] = "Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist. If you are installing to a database that uses ODBC, e.g. MS Access you should first create a DSN for it before proceeding.";

$lang['Start_Install'] = "Start Install";
$lang['Finish_Install'] = "Finish Installation";

$lang['Default_lang'] = "Default board language";
$lang['DB_Host'] = "Database Server Hostname / DSN";
$lang['DB_Name'] = "Your Database Name";
$lang['DB_Username'] = "Database Username";
$lang['DB_Password'] = "Database Password";
$lang['Database'] = "Your Database";
$lang['Install_lang'] = "Choose Language for Installation";
$lang['dbms'] = "Database Type";
$lang['Table_Prefix'] = "Prefix for tables in database";
$lang['Admin_Username'] = "Administrator Username";
$lang['Admin_Password'] = "Administrator Password";
$lang['Admin_Password_confirm'] = "Administrator Password [ Confirm ]";

$lang['Inst_Step_2'] = "Your admin username has been created.  At this point your basic installation is complete. You will now be taken to a screen which will allow you to administer your new installation. Please be sure to check the General Configuration details and make any required changes. Thank you for choosing phpBB 2.";

$lang['Unwriteable_config'] = "Your config file is un-writeable at present. A copy of the config file will be downloaded to your when you click the button below. You should upload this file to the same directory as phpBB 2. Once this is done you should log in using the administrator name and password you provided on the previous form and visit the admin control centre (a link will appear at the bottom of each screen once logged in) to check the general configuration. Thank you for choosing phpBB 2.";
$lang['Download_config'] = "Download Config";

$lang['ftp_choose'] = "Choose Download Method";
$lang['ftp_option'] = "<br />Since FTP extensions are enabled in this version of PHP you may also be given the option of first trying to automatically ftp the config file into place.";
$lang['ftp_instructs'] = "You have chosen to ftp the file to the account containing phpBB 2 automatically.  Please enter the information below to facilitate this process. Note that the FTP path should be the exact path via ftp to your phpBB2 installation as if you were ftping to it using any normal client.";
$lang['ftp_info'] = "Enter Your FTP Information";
$lang['Attempt_ftp'] = "Attempt to ftp config file into place";
$lang['Send_file'] = "Just send the file to me and I'll ftp it manually";
$lang['ftp_path'] = "FTP path to phpBB 2";
$lang['ftp_username'] = "Your FTP Username";
$lang['ftp_password'] = "Your FTP Password";
$lang['Transfer_config'] = "Start Transfer";
$lang['NoFTP_config'] = "The attempt to ftp the config file into place failed.  Please download the config file and ftp it into place manually.";

$lang['Install'] = "Install";
$lang['Upgrade'] = "Upgrade";


$lang['Install_Method'] = "Choose your installation method";

//
// That's all Folks!
// -------------------------------------------------

?>
