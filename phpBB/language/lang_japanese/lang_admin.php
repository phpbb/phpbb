<?php

/***************************************************************************
 *                            lang_admin.php [japanese]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin.php,v 1.27 2001/12/30 13:49:37 psotfx Exp $
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
// Translation by:
//
// Yoichi Iwaki  :: yoichi01@rr.iij4u.or.jp
//
// For questions and comments use: yoichi01@rr.iij4u.or.jp
//

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "一般管理";
$lang['Users'] = "ユーザー管理";
$lang['Groups'] = "グループ管理";
$lang['Forums'] = "フォーラム管理";
$lang['Styles'] = "スタイル管理";

$lang['Configuration'] = "一般設定";
$lang['Permissions'] = "パーミッション";
$lang['Manage'] = "管理";
$lang['Disallow'] = "使用禁止の名前";
$lang['Prune'] = "プルーニング（剪定）";
$lang['Mass_Email'] = "Mass Mail";
$lang['Ranks'] = "ランク";
$lang['Smilies'] = "スマイリー";
$lang['Ban_Management'] = "掲示板の使用禁止";
$lang['Word_Censor'] = "言語フィルター";
$lang['Export'] = "エクスポート";
$lang['Create_new'] = "作成";
$lang['Add_new'] = "追加";
$lang['Backup_DB'] = "データベースのバックアップ";
$lang['Restore_DB'] = "データベースの復旧";


//
// Index
//
$lang['Admin'] = "管理";
$lang['Not_admin'] = "あなたにこのボードの管理する権限はありません";
$lang['Welcome_phpBB'] = "ようこそphpBBへ";
$lang['Admin_intro'] = "この度はphpBBを選択していただき、まことにありがとうございます。この画面では掲示板の様々な統計を確認することができます。左メニューの<u>管理一覧</u>をクリックすることでこの画面に戻ることができます。フォーラム一覧に戻る場合には、左メニューのphpBBのロゴ、または<u>フォーラム一覧</u>をクリックしてください。左メニューにあるその他の項目では、各フォーラムの様々な設定や制御が行えるようになっており、それぞれの画面にはツールの使い方の説明が載っています。";
$lang['Main_index'] = "フォーラム一覧";
$lang['Forum_stats'] = "掲示板統計";
$lang['Admin_Index'] = "管理一覧";
$lang['Preview_forum'] = "プレビュー";

$lang['Click_return_admin_index'] = "管理一覧画面に戻る場合は%sこちら%sをクリックしてください";

$lang['Statistic'] = "統計";
$lang['Value'] = "値";
$lang['Number_posts'] = "投稿数";
$lang['Posts_per_day'] = "1日の投稿数";
$lang['Number_topics'] = "トピック数";
$lang['Topics_per_day'] = "1日のトピック数";
$lang['Number_users'] = "ユーザー数";
$lang['Users_per_day'] = "1日のユーザー数";
$lang['Board_started'] = "掲示板運営開始日";
$lang['Avatar_dir_size'] = "画像ディレクトリのサイズ";
$lang['Database_size'] = "データベースのサイズ";
$lang['Gzip_compression'] ="GZip圧縮の有効/無効";
$lang['Not_available'] = "使用不可";

$lang['ON'] = "有効"; // This is for GZip compression
$lang['OFF'] = "無効"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "データベース・ユーティリティー";

$lang['Restore'] = "復旧";
$lang['Backup'] = "バックアップ";
$lang['Restore_explain'] = "バックアップファイルから、全てのphpBBテーブルの復旧作業を行います。サーバーがGZip圧縮に対応している場合は、GZip圧縮のテキストをアップロードすると自動的にサーバー上で解凍されます。<br><b>警告</b> - この作業は全ての現存データを上書きします。この作業は時間がかかる可能性がありますので、作業が終わるまでこのページから移動しないでください。";
$lang['Backup_explain'] = "phpBB関連のデータのバックアップを取ることができます。phpBBのデータベースにバックアップしたい追加のテーブルがある場合は、テーブルの名前を「追加テーブル」のテキスト欄に入力してください（複数ある場合はカンマで区切ってください）。サーバーがGZip圧縮に対応している場合は、GZIP圧縮を有効にしてダウンロード前にバックアップファイルのサイズを小さくすることができます。";

$lang['Backup_options'] = "オプション";
$lang['Start_backup'] = "バックアップ開始";
$lang['Full_backup'] = "全てをバックアップ";
$lang['Structure_backup'] = "構造のみをバックアップ";
$lang['Data_backup'] = "データのみをバックアップ";
$lang['Additional_tables'] = "追加テーブル";
$lang['Gzip_compress'] = "GZip圧縮ファイル";
$lang['Select_file'] = "ファイルを選択";
$lang['Start_Restore'] = "復旧開始";

$lang['Restore_success'] = "データベースの復旧が完了しました。<br /><br />あなたの掲示板はバックアップ時の状態になっているはずです";
$lang['Backup_download'] = "ダウンロードはすぐ開始されますので、それまで待ちください";
$lang['Backups_not_supported'] = "残念ながらあなたのデータベースシステムは、データベースのバックアップに対応していません";

$lang['Restore_Error_uploading'] = "バックアップファイルのアップロード中にエラーが発生しました";
$lang['Restore_Error_filename'] = "ファイル名に問題があります。別のファイル名で試してみてください";
$lang['Restore_Error_decompress'] = "GZip圧縮されたファイルを解凍することができません。非圧縮のテキストデータをアップロードしてください";
$lang['Restore_Error_no_file'] = "ファイルはアップロードされませんでした";


//
// Auth pages
//
$lang['Select_a_User'] = "ユーザーを選択";
$lang['Select_a_Group'] = "グループを選択";
$lang['Select_a_Forum'] = "フォーラムを選択";
$lang['Auth_Control_User'] = "ユーザーパーミッション設定"; 
$lang['Auth_Control_Group'] = "グループパーミッション設定"; 
$lang['Auth_Control_Forum'] = "フォーラムパーミッション設定"; 
$lang['Look_up_User'] = "ユーザー検索"; 
$lang['Look_up_Group'] = "グループ検索"; 
$lang['Look_up_Forum'] = "フォーラム検索"; 

$lang['Group_auth_explain'] = "各グループのパーミッションとモデレーターのステータスを変更することができます。グループのパーミッション設定を変更しても、グループ内のユーザー個人のパーミッションの方が優先されることがあるので注意してください。";
$lang['User_auth_explain'] = "各ユーザーのパーミッションとモデレーターのステータスを変更することができます。ユーザーのパーミッション設定を変更しても、そのユーザーが属するグループのパーミッションの方が優先されることがあるので注意してください。";
$lang['Forum_auth_explain'] = "各フォーラムのパーミッションレベルを変更することができます。基本モードと発展モードの2種類からレベル設定が可能で、発展モードの方がより高度な設定が行えます。パーミッションレベルを変更すると、利用しているユーザーに影響を及ぼす場合があるので注意してください。";

$lang['Simple_mode'] = "基本モード";
$lang['Advanced_mode'] = "発展モード";
$lang['Moderator_status'] = "モデレーターステータス";

$lang['Allowed_Access'] = "アクセスが許可されてます";
$lang['Disallowed_Access'] = "アクセスが許可されていません";
$lang['Is_Moderator'] = "モデレーターです";
$lang['Not_Moderator'] = "モデレーターではありません";

$lang['Conflict_warning'] = "警告：パーミッションの衝突";
$lang['Conflict_access_userauth'] = "このユーザーは、まだグループ経由の権限を持っています。完全に権限を取り上げる場合は、グループパーミッションを変更、またはユーザーをグループから外す必要があります。グループに与えられている権限（そしてフォーラム関連の権限）は下に書いてあります。";
$lang['Conflict_mod_userauth'] = "このユーザーは、まだグループ経由のモデレーター権限を持っています。完全に権限を取り上げる場合は、グループパーミッションを変更、またはユーザーをグループから外す必要があります。グループに与えられている権限（そしてフォーラム関連の権限）は下に書いてあります。";

$lang['Conflict_access_groupauth'] = "次のユーザーは、まだユーザーパーミッション経由の権限を持っています。完全に権限を取り上げる場合は、ユーザーパーミッションを変更する必要があります。ユーザーに与えられている権限（そしてフォーラム関連の権限）は下に書いてあります。";
$lang['Conflict_mod_groupauth'] = "次ユーザーは、まだユーザーパーミッション経由のモデレーターの権限を持っています。完全に権限を取り上げる場合は、ユーザーパーミッションを変更する必要があります。ユーザーに与えられている権限（そしてフォーラム関連の権限）は下に書いてあります";

$lang['Public'] = "全てのユーザー";
$lang['Private'] = "特定のユーザーのみ";
$lang['Registered'] = "登録ユーザーのみ";
$lang['Administrators'] = "管理者のみ";
$lang['Hidden'] = "不可視";

$lang['View'] = "観閲";
$lang['Read'] = "読む";
$lang['Post'] = "投稿";
$lang['Reply'] = "返信";
$lang['Edit'] = "編集";
$lang['Delete'] = "削除";
$lang['Sticky'] = "告知";
$lang['Announce'] = "発表\"; 
$lang['Vote'] = "投票";
$lang['Pollcreate'] = "投票欄作成";

$lang['Permissions'] = "パーミッション";
$lang['Simple_Permission'] = "基本パーミッション";

$lang['User_Level'] = "ユーザーレベル"; 
$lang['Auth_User'] = "ユーザー";
$lang['Auth_Admin'] = "管理者";
$lang['Group_memberships'] = "ユーザーグループ・メンバーシップ";
$lang['Usergroup_members'] = "このグループには次のメンバーが属しています";

$lang['Forum_auth_updated'] = "フォーラムパーミッションが更新されました";
$lang['User_auth_updated'] = "ユーザーパーミッションが更新されました";
$lang['Group_auth_updated'] = "グループパーミッションが更新されました";

$lang['Auth_updated'] = "パーミッションが更新されました";
$lang['Click_return_userauth'] = "ユーザーパーミッション画面に戻る場合は%sこちら%sをクリックしてください";
$lang['Click_return_groupauth'] = "グループパーミッション画面に戻る場合は%sこちら%sをクリックしてください";
$lang['Click_return_forumauth'] = "フォーラムパーミッション画面に戻る場合は%sこちら%sをクリックしてください";


//
// Banning
//
$lang['Ban_control'] = "掲示板の使用禁止";
$lang['Ban_explain'] = "特定のユーザーの掲示板の使用を禁止することができます。使用禁止の指定対象は、特定のユーザーの名前、IPアドレス、ホスト名です。使用禁止に指定されたユーザーは、フォーラム一覧の画面も表示されなくなります。メールアドレスの使用禁止を行うことで、使用禁止に指定されたユーザーが再び異なる名前で登録を行うことも防ぐことができます。メールアドレスの使用禁止は登録時のみに判定されるものであって、使用禁止に指定したメールアドレスを持つユーザーの観閲や投稿が禁止されることはありませんので注意してください。観閲や投稿を禁止させたい場合は、名前、IPアドレス、ホスト名を指定してください。";
$lang['Ban_explain_warn'] = "IPアドレスによる指定をする場合には注意が必要です。ハイフンを用いて範囲指定が広すぎると、関係のないユーザーが使用禁止に指定される可能性があるため、ワイルドカードを用いてできるだけ範囲を限定するなどの工夫が必要となります。";

$lang['Select_username'] = "名前を選択";
$lang['Select_ip'] = "IPアドレスを選択";
$lang['Select_email'] = "メールアドレスを選択";

$lang['Ban_username'] = "特定のユーザーを使用禁止に指定";
$lang['Ban_username_explain'] = "マウスとキーボードのコマンドを用いると、複数のユーザーを同時に選択することができます";

$lang['Ban_IP'] = "IPアドレス、ホスト名を使用禁止に指定";
$lang['IP_hostname'] = "IPアドレス、又はホスト名";
$lang['Ban_IP_explain'] = "複数のIPアドレス、ホスト名を指定する場合はカンマで区切ってください。IPアドレスの数字の範囲を指定する場合は、指定したい範囲の最初の数字と最後の数字の間にハイフン(-)入れてください。ワイルドカードを使って指定する場合は*を使ってください。";

$lang['Ban_email'] = "メールアドレスを使用禁止に指定";
$lang['Ban_email_explain'] = "複数のメールアドレスを指定する場合はコンマで区切ってください。ワイルドカードを使用する場合は*を使用してください。（例： *@hotmai.com）";

$lang['Unban_username'] = "特定のユーザーの使用禁止を解除";
$lang['Unban_username_explain'] = "マウスとキーボードのコマンドを用いると、複数のユーザーを同時に選択することができます";

$lang['Unban_IP'] = "IPアドレス、ホスト名の使用禁止を解除";
$lang['Unban_IP_explain'] = "マウスとキーボードのコマンドを用いると、複数のIPアドレス、ホスト名を同時に選択することができます";

$lang['Unban_email'] = "メールアドレスの使用禁止を解除";
$lang['Unban_email_explain'] = "マウスとキーボードのコマンドを用いると、複数のメールアドレスを同時に選択することができます";

$lang['No_banned_users'] = "使用禁止のユーザーはいません";
$lang['No_banned_ip'] = "使用禁止のIPアドレスはありません";
$lang['No_banned_email'] = "使用禁止のメールアドレスはありません";

$lang['Ban_update_sucessful'] = "使用禁止リストは更新されました";
$lang['Click_return_banadmin'] = "使用禁止の制御画面に戻る場合は%sこちら%sをクリックしてください";


//
// Configuration
//
$lang['General_Config'] = "一般設定";
$lang['Config_explain'] = "掲示板全体の一般設定を行うことができます。ユーザーとフォーラムの設定を行う場合は左メニューから選択してください。";

$lang['Click_return_config'] = "一般設定画面に戻る場合は%sこちら%sをクリックしてください";

$lang['General_settings'] = "一般的な掲示板設定";
$lang['Site_name'] = "サイト名";
$lang['Site_desc'] = "サイトに関する記述";
$lang['Board_disable'] = "掲示板の停止";
$lang['Board_disable_explain'] = "掲示板を停止すると、一般のユーザーが掲示板を利用できなくなります。掲示板が停止している状態でログアウトすると、管理画面に入れなくなるので注意してください！";
$lang['Acct_activation'] = "アカウントのアクティベーション";
$lang['Acc_None'] = "無効"; // These three entries are the type of activation
$lang['Acc_User'] = "有効（ユーザー）";
$lang['Acc_Admin'] = "有効（管理者）";

$lang['Abilities_settings'] = "ユーザーとフォーラムの基本設定";
$lang['Max_poll_options'] = "投票数の最大数";
$lang['Flood_Interval'] = "投稿間隔";
$lang['Flood_Interval_explain'] = "ユーザーが投稿後に再び投稿できようになる時間（秒単位）"; 
$lang['Board_email_form'] = "掲示板経由のメール";
$lang['Board_email_form_explain'] = "有効の場合は、ユーザー同士がこの掲示板を介してメールを送ることができるようになります";
$lang['Topics_per_page'] = "1ページのトピック数";
$lang['Posts_per_page'] = "1ページの記事数";
$lang['Hot_threshold'] = "人気トピックになるために必要な投稿数";
$lang['Default_style'] = "デフォルトスタイル";
$lang['Override_style'] = "デフォルトスタイル優先";
$lang['Override_style_explain'] = "ユーザーが指定したスタイルをデフォルトスタイルに置き換えます";
$lang['Default_language'] = "デフォルト言語";
$lang['Date_format'] = "日付のフォーマット";
$lang['System_timezone'] = "タイムゾーン";
$lang['Enable_gzip'] = "GZip 圧縮";
$lang['Enable_prune'] = "プルーニング";
$lang['Allow_HTML'] = "HTMLの使用";
$lang['Allow_BBCode'] = "BBCodeの使用";
$lang['Allowed_tags'] = "使用できるHTMLタグ";
$lang['Allowed_tags_explain'] = "タグをカンマで区切ってください";
$lang['Allow_smilies'] = "スマイリーの使用";
$lang['Smilies_path'] = "スマイリーのパス";
$lang['Smilies_path_explain'] = "phpBBのディレクトリ内のパスである必要があります （例： images/smilies）";
$lang['Allow_sig'] = "サインの使用";
$lang['Max_sig_length'] = "サインの最大文字数";
$lang['Max_sig_length_explain'] = "ユーザーのサインで使用できる最大文字数です";
$lang['Allow_name_change'] = "名前変更の許可";

$lang['Avatar_settings'] = "ユーザー画像設定";
$lang['Allow_local'] = "ユーザー画像の使用";
$lang['Allow_remote'] = "他サイトの画像の使用";
$lang['Allow_remote_explain'] = "他のサイトにリンクされているユーザー画像";
$lang['Allow_upload'] = "ユーザー画像のアップロード";
$lang['Max_filesize'] = "ユーザー画像の最大サイズ";
$lang['Max_filesize_explain'] = "ユーザー画像のアップロードを行う時の最大サイズです";
$lang['Max_avatar_size'] = "ユーザー画像の最大面積";
$lang['Max_avatar_size_explain'] = "高さ x 幅（ピクセル単位）";
$lang['Avatar_storage_path'] = "アップロード用のユーザー画像のパス";
$lang['Avatar_storage_path_explain'] = "phpBBのディレクトリ内のパスである必要があります （例： images/avatars）";
$lang['Avatar_gallery_path'] = "ユーザー画像のパス";
$lang['Avatar_gallery_path_explain'] = "phpBBのディレクトリ内のパスである必要があります （例： images/avatars/gallery）";

$lang['COPPA_settings'] = "COPPA（子供のオンライン・プライバシー
保護法）設定";
$lang['COPPA_fax'] = "COPPAファックス番号";
$lang['COPPA_mail'] = "COPPAメールアドレス";
$lang['COPPA_mail_explain'] = "利用者の両親がCOPPA登録フォームを送るためのメールアドレス";

$lang['Email_settings'] = "メールアドレス設定";
$lang['Admin_email'] = "管理者のメールアドレス";
$lang['Email_sig'] = "メールアドレスのサイン";
$lang['Email_sig_explain'] = "掲示板から送信される全てのメールに、ここに入力されたメッセージが付きます";
$lang['Use_SMTP'] = "SMTPサーバーの使用";
$lang['Use_SMTP_explain'] = "指定されたサーバーを介してメールを送りたい、又は送る必要がある場合に有効にしてください";
$lang['SMTP_server'] = "SMTPサーバーのアドレス";

$lang['Disable_privmsg'] = "プライベートメッセージ";
$lang['Inbox_limits'] = "受信ボックスの最大メッセージ数";
$lang['Sentbox_limits'] = "送信ボックスの最大メッセージ数";
$lang['Savebox_limits'] = "保管ボックスの最大メッセージ数";

$lang['Cookie_settings'] = "Cookie設定"; 
$lang['Cookie_settings_explain'] = "Cookie設定は既に行われている状態にあります。ほとんどの場合はこのままで問題ありません。設定を変更する場合は慎重に行ってください。設定を誤ると、ユーザーがログインできなくなる可能性があります。";
$lang['Cookie_name'] = "Cookie名";
$lang['Cookie_domain'] = "Cookieドメイン";
$lang['Cookie_path'] = "Cookieパス";
$lang['Session_length'] = "セッションの長さ （秒単位）";
$lang['Cookie_secure'] = "Cookieセキュア （https）";


//
// Forum Management
//
$lang['Forum_admin'] = "フォーラム管理";
$lang['Forum_admin_explain'] = "カテゴリとフォーラムの作成/削除/編集/再同期を行うことができます。";
$lang['Edit_forum'] = "フォーラムの編集";
$lang['Create_forum'] = "フォーラムの新規作成";
$lang['Create_category'] = "カテゴリの新規作成";
$lang['Remove'] = "削除";
$lang['Action'] = "実行";
$lang['Update_order'] = "アップデート順";
$lang['Config_updated'] = "フォーラム設定を更新しました";
$lang['Edit'] = "編集";
$lang['Delete'] = "削除";
$lang['Move_up'] = "上へ移動";
$lang['Move_down'] = "下へ移動";
$lang['Resync'] = "再同期";
$lang['No_mode'] = "モードは設定されませんでした";
$lang['Forum_edit_delete_explain'] = "各フォーラムの設定を行うことができます。";

$lang['Move_contents'] = "全コンテンツの移動";
$lang['Forum_delete'] = "フォーラムの削除";
$lang['Forum_delete_explain'] = "フォーラム（又はカテゴリ）の削除を行うことができます。削除の際に、そのトピック（又はフォーラム）の移動先を指定することができます。";

$lang['Forum_settings'] = "一般フォーラム設定";
$lang['Forum_name'] = "フォーラム名";
$lang['Forum_desc'] = "記述（フォーラムの説明）";
$lang['Forum_status'] = "ステータス";
$lang['Forum_pruning'] = "自動プルーニング";

$lang['prune_freq'] = '（何日毎に）投稿がないトピックを確認';
$lang['prune_days'] = "（何日間以内に）返信がないトピックを削除";
$lang['Set_prune_data'] = "自動プルーニングを有効にしましたが、オプション内の日数に記入漏れがあります。設定画面に戻って数字を入力してください。";

$lang['Move_and_Delete'] = "移動/削除";

$lang['Delete_all_posts'] = "全て削除";
$lang['Nowhere_to_move'] = "移動先がありません";

$lang['Edit_Category'] = "カテゴリの編集";
$lang['Edit_Category_explain'] = "カテゴリ名を変更することができます";

$lang['Forums_updated'] = "フォーラムとカテゴリの情報は更新されました";

$lang['Must_delete_forums'] = "カテゴリを削除する前に、カテゴリ内のフォーラムを全て削除する必要があります";

$lang['Click_return_forumadmin'] = "フォーラム管理画面に戻る場合は%sこちら%sをクリックしてください";


//
// Smiley Management
//
$lang['smiley_title'] = "スマイリーアイコン編集ユーティリティー";
$lang['smile_desc'] = "ユーザーが投稿の際に使用できるスマイリーの追加、削除、編集を行うことができます。";

$lang['smiley_config'] = "スマイリー設定";
$lang['smiley_code'] = "スマイリーコード";
$lang['smiley_url'] = "スマイリー画像ファイル";
$lang['smiley_emot'] = "スマイリーの記述";
$lang['smile_add'] = "スマイリーの追加";
$lang['Smile'] = "スマイリー";
$lang['Emotion'] = "記述";

$lang['Select_pak'] = "スマイリーパック (.pak)の選択";
$lang['replace_existing'] = "現存のスマイリーを置き換える";
$lang['keep_existing'] = "現存のスマイリーを保管する";
$lang['smiley_import_inst'] = "スマイリーパックを解凍して、全てのファイルを適切なスマイリーディレクトリにアップロードしてください。スマイリーパックをインポートするために、正しい情報を選択してください。";
$lang['smiley_import'] = "スマイリーパックのインポート";
$lang['choose_smile_pak'] = "スマイリーバック (.pak)ファイルを選択をしてください";
$lang['import'] = "スマイリーのインポート";
$lang['smile_conflicts'] = "インポートするスマイリーが、現存のスマイリーと重複した場合はどうしますか？";
$lang['del_existing_smileys'] = "インポートする前に現存のスマイリーを削除する";
$lang['import_smile_pack'] = "スマイリーパックのインポート";
$lang['export_smile_pack'] = "スマイリーパックの新規作成";
$lang['export_smiles'] = "現在導入されているスマイリーパックからスマイリーパックを作成する場合は%sこちら%sからsmiles.pakファイルをダウンロードしてください。保存する時の名前には.pakの拡張子を忘れずにつけてください。そうしたら全てのスマイリー画像と.pakファイルを入れたZIPファイルを作成してください。";

$lang['smiley_add_success'] = "スマイリーは追加されました";
$lang['smiley_edit_success'] = "スマイリーは更新されました";
$lang['smiley_import_success'] = "スマイリーパックはインポートされました";
$lang['smiley_del_success'] = "スマイリーは削除されました";
$lang['Click_return_smileadmin'] = "スマイリーアイコン編集ユーティリティー画面に戻る場合は%sこちら%sをクリックしてください";


//
// User Management
//
$lang['User_admin'] = "ユーザー管理";
$lang['User_admin_explain'] = "ユーザーの情報と設定を変更することができます。ユーザーのパーミッションを変更する場合は、ユーザーとグループの両方のパーミッションを変更する場合があります。";

$lang['Look_up_user'] = "ユーザーを検索";

$lang['Admin_user_fail'] = "ユーザー情報を変更できませんでした";
$lang['Admin_user_updated'] = "ユーザー情報は更新されました";
$lang['Click_return_useradmin'] = "ユーザー管理画面に戻る場合は%sこちら%sをクリックしてください";

$lang['User_delete'] = "このユーザーを削除するか";
$lang['User_delete_explain'] = "削除する場合はチェック。削除すると取り消すことはできません";
$lang['User_deleted'] = "ユーザーは削除されました";

$lang['User_status'] = "アクティブ";
$lang['User_allowpm'] = "プライベートメッセージの使用";
$lang['User_allowavatar'] = "ユーザー画像の表示";

$lang['Admin_avatar_explain'] = "現在のユーザー画像を表示/削除することができます";

$lang['User_special'] = "管理者限定のユーザー設定";
$lang['User_special_explain'] = "ここでの設定は一般ユーザーが行えない（管理人のみが行える）ものです。";


//
// Group Management
//
$lang['Group_administration'] = "グループ管理";
$lang['Group_admin_explain'] = "ユーザーグループの作成/削除/編集などを行うことができます。モデレーターを選択したり、グループの名前、記述、ステータスを変更することができます。";
$lang['Error_updating_groups'] = "グループを更新している時にエラーが発生しました";
$lang['Updated_group'] = "グループは更新されました";
$lang['Added_new_group'] = "グループは作成されました";
$lang['Deleted_group'] = "グループは削除されました";
$lang['New_group'] = "グループの新規作成";
$lang['Edit_group'] = "グループの編集";
$lang['group_name'] = "グループ名";
$lang['group_description'] = "グループの記述";
$lang['group_moderator'] = "グループのモデレーター";
$lang['group_status'] = "グループのステータス";
$lang['group_open'] = "オープングループ";
$lang['group_closed'] = "クローズドグループ";
$lang['group_hidden'] = "隠れグループ";
$lang['group_delete'] = "グループの削除";
$lang['group_delete_check'] = "このグループを削除します";
$lang['submit_group_changes'] = "変更を決定";
$lang['reset_group_changes'] = "変更をリセット";
$lang['No_group_name'] = "グループ名を入力してください";
$lang['No_group_moderator'] = "モデレーターを入力してください";
$lang['No_group_mode'] = "グループのステータスを決めてください";
$lang['delete_group_moderator'] = "前のモデレーターを削除するか?";
$lang['delete_moderator_explain'] = "前のモデレーターをグループから外す場合はチェックを入れてください。チェックを入れない場合は、その前モデレーターは普通のメンバーとなります";
$lang['Click_return_groupsadmin'] = "グループ管理画面に戻る場合は%sこちら%sをクリックしてください";
$lang['Select_group'] = "グループを選択";
$lang['Look_up_group'] = "グループを検索";


//
// Prune Administration
//
$lang['Forum_Prune'] = "フォーラムのプルーニング";
$lang['Forum_Prune_explain'] = "指定された日数内に返信がないトピックを自動的に削除する機能をプルーニングと言います。数字を入力せずにプルーニングを行うと全てのトピックを自動的に削除してしまいますので注意してください。投票欄のあるトピック、アナウンストピックに関してはプルーニングによって削除されませんので、手動で削除する必要があります。";
$lang['Do_Prune'] = "プルーニングの実行";
$lang['All_Forums'] = "全てのフォーラム";
$lang['Prune_topics_not_posted'] = "（何日間）返信がないトピックを削除";
$lang['Topics_pruned'] = "削除されたトピックの数";
$lang['Posts_pruned'] = "削除された投稿記事の数";
$lang['Prune_success'] = "フォーラムのプルーニングは完了しました";


//
// Word censor
//
$lang['Words_title'] = "言語フィルター";
$lang['Words_explain'] = "言語フィルターの追加/削除/編集を行うことができます。言語フィルターは、投稿される文章から言語フィルターに登録されている言葉を見つけ出し、その言葉を指定されたものに置換する機能です。ユーザー登録時には、この言語フィルターに追加されている言葉を名前に含むことができません。ワイルドカードを使用する場合は*を使用してください。（例： *test*という言語フィルターの場合、detestalbe, detestといった言葉も置換します）";
$lang['Word'] = "対象";
$lang['Edit_word_censor'] = "言語フィルターの編集";
$lang['Replacement'] = "置換";
$lang['Add_new_word'] = "新しい言葉の追加";
$lang['Update_word'] = "言語フィルターの更新";

$lang['Must_enter_word'] = "対象と置換を入力してください";
$lang['No_word_selected'] = "編集する言葉が選択されていません";

$lang['Word_updated'] = "選択した言語フィルターは更新されました";
$lang['Word_added'] = "言語フィルターは追加されました";
$lang['Word_removed'] = "選択した言語フィルターは削除されました";

$lang['Click_return_wordadmin'] = "言語フィルターの管理画面に戻るには%sこちら%sをクリックしてください";


//
// Mass Email
//
$lang['Mass_email_explain'] = "全ての登録ユーザーや特定のグループにメールを送信することができます。この作業を実行すると、下のフォームに記入された内容のメールが対象者全員に送信されます。この作業は対象者が多いほど時間がかかります。作業が開始されたら、完了画面が表示されるまではページから移動しないでください。";
$lang['Compose'] = "メール作成"; 

$lang['Recipients'] = "宛先"; 
$lang['All_users'] = "全ての登録ユーザー";

$lang['Email_successfull'] = "メッセージは送信されました";
$lang['Click_return_massemail'] = "メール画面に戻る場合は%sこちら%sをクリックしてください";


//
// Ranks admin
//
$lang['Ranks_title'] = "ランク管理";
$lang['Ranks_explain'] = "ランクの表示/追加/削除/編集を行うことができます。";

$lang['Add_new_rank'] = "新しいランクの追加";

$lang['Rank_title'] = "ランクの称号";
$lang['Rank_special'] = "特別ランクに設定";
$lang['Rank_minimum'] = "最小投稿数";
$lang['Rank_maximum'] = "最大投稿数";
$lang['Rank_image'] = "ランク画像 (phpBB2内の相対パス)";
$lang['Rank_image_explain'] = "そのランクに属するユーザーに表示される画像を指します";

$lang['Must_select_rank'] = "ランクを選択してください";
$lang['No_assigned_rank'] = "特別ランクが指定されていません";

$lang['Rank_updated'] = "ランクは更新されました";
$lang['Rank_added'] = "ランクは追加されました";
$lang['Rank_removed'] = "ランクは削除されました";

$lang['Click_return_rankadmin'] = "ランク管理画面に戻る場合は%sこちら%sをクリックしてください";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "使用禁止の名前";
$lang['Disallow_explain'] = "使用禁止の名前を設定することができます。ワイルドカードを使用する場合は*を使用してください。既に登録されている名前を禁止にしても効果はないので、先にその名前のユーザーを削除する必要があります。";

$lang['Delete_disallow'] = "削除";
$lang['Delete_disallow_title'] = "使用禁止の名前を削除";
$lang['Delete_disallow_explain'] = "使用禁止を解きたい名前を選択して削除を実行してください";

$lang['Add_disallow'] = "追加";
$lang['Add_disallow_title'] = "使用禁止の名前を追加";
$lang['Add_disallow_explain'] = "ワイルドカードを使用する場合は*を使用してください";

$lang['No_disallowed'] = "使用禁止の名前はありません";

$lang['Disallowed_deleted'] = "使用禁止の名前は削除されました";
$lang['Disallow_successful'] = "使用禁止の名前は追加されました";
$lang['Disallowed_already'] = "その名前を使用禁止にすることはできません。掲示板の使用禁止リスト、使用禁止の名前リストに含まれているか、既にユーザー登録されている名前の可能性があります。";

$lang['Click_return_disallowadmin'] = "使用禁止の名前画面に戻る場合は%sこちら%sをクリックしてください";


//
// Styles Admin
//
$lang['Styles_admin'] = "スタイル管理";
$lang['Styles_explain'] = "現在利用できるスタイル（テンプレートとテーマ）の削除、編集を行うことができます。";
$lang['Styles_addnew_explain'] = "下のリストには、テンプレートに使用できる全てのテーマが含まれています。リストに含まれているものはphpBBデータベースには導入されていません。テーマをインストールしたい場合は、インストールをクリックしてください。";

$lang['Select_template'] = "テンプレートの選択";

$lang['Style'] = "スタイル";
$lang['Template'] = "テンプレート";
$lang['Install'] = "インストール";
$lang['Download'] = "ダウンロード";

$lang['Edit_theme'] = "テーマの編集";
$lang['Edit_theme_explain'] = "選択したテーマの編集を行うことができます";

$lang['Create_theme'] = "テーマの作成";
$lang['Create_theme_explain'] = "選択したテンプレートのテーマを作成することができます。色は十六進数（例：CCCCCC）で入力してください。ただし、#CCCCCCのように#は入力しないでください。";

$lang['Export_themes'] = "テーマのエクスポート";
$lang['Export_explain'] = "選択したテンプレートのテーマをエクスポートすることができます。下のリストからテンプレートを選択すると、テーマ設定のファイルが選択したテンプレートのディレクトリに作成されます。ディレクトリに作成することができない場合は、テーマ設定のファイルをダウンロードすることができます。ファイルをディレクトリに作成したい場合は、そのディレクトリにWRITEの権限を与える必要があります。詳細を知りたい場合は、phpBB2のユーザーガイドを見てください。";

$lang['Theme_installed'] = "選択したテーマはインストールされました";
$lang['Style_removed'] = "選択したスタイルは、データベースから削除されました。完全にスタイルを削除する場合は、テンプレートのディレクトリから直接削除する必要があります";
$lang['Theme_info_saved'] = "選択したテンプレートのテーマ設定は保存されました。作業が完了したので、theme_info.cfg（当てはまる場合は選択したテンプレートのディレクトリを含む）のパーミッションをRead-Onlyにしてください";
$lang['Theme_updated'] = "選択したテーマは更新されました。新しいテーマ設定をエクスポートしてください";
$lang['Theme_created'] = "テーマは作成されました。万が一のためや、他の場所で使うために、作成されたテーマをテーマ設定ファイルにしてエクスポートする必要があります";

$lang['Confirm_delete_style'] = "このスタイルを削除しますか？";

$lang['Download_theme_cfg'] = "エクスポーターはテーマ設定を書き込むことができませんでした。ブラウザからこのファイルをダウンロードする場合は、下のボタンをクリックしてください。ダウンロードした設定ファイルは、テンプレートのディレクトリ内に置くことができます。これをパッケージにして、ウェブサイト上で配布したり、他のphpBB2のテンプレートとテーマ設定として利用することもできます";
$lang['No_themes'] = "選択したテンプレートにはテーマ設定が付いていません。新規テーマを作る場合は、左メニューから作成をクリックしてください";
$lang['No_template_dir'] = "テンプレートのディレクトリを開くことができません。ディレクトリを読むことができない、又は存在しない可能性があります";
$lang['Cannot_remove_style'] = "掲示板のデフォルトスタイルは削除することができません。デフォルトスタイルを変更してから削除してください";
$lang['Style_exists'] = "同じスタイル名が既に存在します。戻って違う名前を入力してください";

$lang['Click_return_styleadmin'] = "スタイル管理画面に戻る場合は%sこちら%sをクリックしてください";

$lang['Theme_settings'] = "テーマ設定";
$lang['Theme_element'] = "要素";
$lang['Simple_name'] = "名称（オプション）";
$lang['Value'] = "Value";
$lang['Save_Settings'] = "設定を保存";

$lang['Stylesheet'] = "CSSスタイルシート";
$lang['Background_image'] = "背景画像";
$lang['Background_color'] = "背景色";
$lang['Theme_name'] = "テーマ名";
$lang['Link_color'] = "リンク色";
$lang['Text_color'] = "テキスト色";
$lang['VLink_color'] = "リンク色（Visited）";
$lang['ALink_color'] = "リンク色（Active）";
$lang['HLink_color'] = "リンク色（Hover）";
$lang['Tr_color1'] = "テーブル列の色1";
$lang['Tr_color2'] = "テーブル列の色2";
$lang['Tr_color3'] = "テーブル列の色3";
$lang['Tr_class1'] = "テーブル列のクラス1";
$lang['Tr_class2'] = "テーブル列のクラス2";
$lang['Tr_class3'] = "テーブル列のクラス3";
$lang['Th_color1'] = "テーブルヘッダーの色1";
$lang['Th_color2'] = "テーブルヘッダーの色2";
$lang['Th_color3'] = "テーブルヘッダーの色3";
$lang['Th_class1'] = "テーブルヘッダーのクラス1";
$lang['Th_class2'] = "テーブルヘッダーのクラス2";
$lang['Th_class3'] = "テーブルヘッダーのクラス3";
$lang['Td_color1'] = "テーブルセルの色1";
$lang['Td_color2'] = "テーブルセルの色2";
$lang['Td_color3'] = "テーブルセルの色3";
$lang['Td_class1'] = "テーブルセルのクラス1";
$lang['Td_class2'] = "テーブルセルのクラス2";
$lang['Td_class3'] = "テーブルセルのクラス3";
$lang['fontface1'] = "フォント名1";
$lang['fontface2'] = "フォント名2";
$lang['fontface3'] = "フォント名3";
$lang['fontsize1'] = "フォントサイズ1";
$lang['fontsize2'] = "フォントサイズ2";
$lang['fontsize3'] = "フォントサイズ3";
$lang['fontcolor1'] = "フォント色1";
$lang['fontcolor2'] = "フォント色2";
$lang['fontcolor3'] = "フォント色3";
$lang['span_class1'] = "Span Class 1";
$lang['span_class2'] = "Span Class 2";
$lang['span_class3'] = "Span Class 3";
$lang['img_poll_size'] = "投票画像の大きさ（ピクセル単位）";
$lang['img_pm_size'] = "プライベートメッセージ・ステータスの大きさ（ピクセル単位）";


//
// Install Process
//
$lang['Welcome_install'] = "ようこそphpBB2のセットアップ画面へ";
$lang['Initial_config'] = "基本設定";
$lang['DB_config'] = "データベース設定";
$lang['Admin_config'] = "管理設定";
$lang['continue_upgrade'] = "CONFIGファイルをローカルマシンの方にダウンロードしたら、「アップグレードの続行」のボタンをクリックしてアップグレード作業を進行させてください。アップグレード作業が完了するまでは、CONFIGファイルのアップロードはしないでください。";
$lang['upgrade_submit'] = "アップグレードの続行";

$lang['Installer_Error'] = "インストール中にエラーが発生しました";
$lang['Previous_Install'] = "前回のインストールファイルが見つかりました";
$lang['Install_db_error'] = "データベースの更新中にエラーが発生しました";

$lang['Re_install'] = "まだ前回のインストールがアクティブになっています。<br /><br />phpBB2を再インストールしたい場合は、下のYesボタンをクリックしてください。再インストールする場合は、存続のデータがバックアップされずに全て削除されます。管理者のユーザー名とパスワードは再インストール後に再び作成されます。他の設定に関しては一切残りません。<br /><br />再インストールは慎重に行う必要があります。";

$lang['Inst_Step_0'] = "phpBB2を選択していただき、まことにありがとうございます。インストールを完了させるために、下の欄で要求されている情報を入力してください。次の作業に進む前に、インストール先のデータベースが既に作成されていることを確認してください。MS AccessのようなODBCを用いるデータベースにインストールをする場合は、DNSを作成してから次の作業に進んでください。";

$lang['Start_Install'] = "インストール開始";
$lang['Finish_Install'] = "インストール完了";

$lang['Default_lang'] = "デフォルト言語";
$lang['DB_Host'] = "データベースサーバーのホスト名 / DNS";
$lang['DB_Name'] = "データベースの名前";
$lang['DB_Username'] = "データベースのユーザー名";
$lang['DB_Password'] = "データベースのパスワード";
$lang['Database'] = "あなたのデータベース";
$lang['Install_lang'] = "インストール時の言語を選択してください";
$lang['dbms'] = "データベースのタイプ";
$lang['Table_Prefix'] = "データベース内のテーブルの接頭辞";
$lang['Admin_Username'] = "管理者のユーザー名";
$lang['Admin_Password'] = "管理者のパスワード";
$lang['Admin_Password_confirm'] = "管理者のパスワード（確認）";

$lang['Inst_Step_2'] = "管理者のユーザー名は作成されました。この時点で基本インストールは完了です。今から管理画面に移動することになります。一般設定を確認して、必要な部分の変更を行うようにしてください。この度はphpBB2を選択していただき、まことにありがとうございます。";

$lang['Unwriteable_config'] = "現在のCONFIGファイルのパーミッションでは書き込むことができません。下のボタンをクリックすれば、CONFIGファイルをダウンロードすることができます。このファイルをphpBB2と同じディレクトリにアップロードする必要があります。この作業が完了したら、管理者のアカウンでログインして管理画面（ログイン後に各画面の一番下に入り口が表示されます）に移動し、一般設定を確認してください。この度はphpBB2を選択していただき、まことにありがとうございます。";
$lang['Download_config'] = "CONFIGファイルをダウンロード";

$lang['ftp_choose'] = "ダウンロード方法を選択";
$lang['ftp_option'] = "<br />このバージョンのPHPではFTP Extensionが有効になっているので、FTPを利用してCONFIGファイルを自動転送することができる場合があります。";
$lang['ftp_instructs'] = "あなたはファイルをFTPによる自動転送で送る方法を選択しました。この作業を行うために、下の欄で要求されている情報を入力してください。";
$lang['ftp_info'] = "FTP情報を入力してください";
$lang['Attempt_ftp'] = "CONFIGファイルを適切な場所にFTPで自動的に転送";
$lang['Send_file'] = "CONFIGファイルをダウンロードして、FTPを介して手動で転送";
$lang['ftp_path'] = "phpBB2へのFTPパス";
$lang['ftp_username'] = "FTP - ユーザー名";
$lang['ftp_password'] = "FTP - パスワード";
$lang['Transfer_config'] = "転送開始";
$lang['NoFTP_config'] = "FTPによる自動転送は失敗しました。ファイルをダウンロードして手動で転送してください。";

$lang['Install'] = "新規インストール";
$lang['Upgrade'] = "アップグレード";


$lang['Install_Method'] = "インストール方法を選択してください";

//
// That's all Folks!
// -------------------------------------------------

?>