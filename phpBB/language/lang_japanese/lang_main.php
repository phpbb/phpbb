<?php
/***************************************************************************
 *                            lang_main.php [japanese]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.73 2001/12/30 13:39:42 psotfx Exp $
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
$lang['ENCODING'] = "shift_jis";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "フォーラム";
$lang['Category'] = "カテゴリ";
$lang['Topic'] = "トピック";
$lang['Topics'] = "<nobr>トピック";
$lang['Replies'] = "<nobr>返信";
$lang['Views'] = "<nobr>観閲";
$lang['Post'] = "投稿１";
$lang['Posts'] = "<nobr>投稿記事";
$lang['Posted'] = "<nobr>時間";
$lang['Username'] = "名前";
$lang['Password'] = "パスワード";
$lang['Email'] = "Email";
$lang['Poster'] = "投稿者";
$lang['Author'] = "<nobr>投稿者";
$lang['Time'] = "時間";
$lang['Hours'] = "時間";
$lang['Message'] = "メッセージ";

$lang['1_Day'] = "1日以内";
$lang['7_Days'] = "1週間以内";
$lang['2_Weeks'] = "2週間以内";
$lang['1_Month'] = "1ヶ月以内";
$lang['3_Months'] = "3ヶ月以内";
$lang['6_Months'] = "6ヶ月以内";
$lang['1_Year'] = "1年以内";

$lang['Go'] = "移動";
$lang['Jump_to'] = "移動先";
$lang['Submit'] = "送信";
$lang['Reset'] = "リセット";
$lang['Cancel'] = "キャンセル";
$lang['Preview'] = "プレビュー";
$lang['Confirm'] = "確認";
$lang['Spellcheck'] = "スペルチェック（英語のみ）";
$lang['Yes'] = "はい";
$lang['No'] = "いいえ";
$lang['Enabled'] = "有効";
$lang['Disabled'] = "無効";
$lang['Error'] = "エラー";

$lang['Next'] = "次のページ";
$lang['Previous'] = "前のページ";
$lang['Goto_page'] = "ページ移動";
$lang['Joined'] = "登録日";
$lang['IP_Address'] = "IPアドレス";

$lang['Select_forum'] = "フォーラムを選択";
$lang['View_latest_post'] = "最新の記事を表示１";
$lang['View_newest_post'] = "最新の記事を表示";
$lang['Page_of'] = "Page <b>%d</b> of <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQナンバー";
$lang['AIM'] = "AIMアドレス";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s :: フォーラム一覧";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "トピックの新規投稿";
$lang['Reply_to_topic'] = "返信";
$lang['Reply_with_quote'] = "引用";

$lang['Click_return_topic'] = "トピックに戻る場合は%sこちら%sをクリックしてください"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "もう一度試す場合は%sこちら%sをクリックしてください";
$lang['Click_return_forum'] = "フォーラムに戻る場合は%sこちら%sをクリックしてください";
$lang['Click_view_message'] = "自分のメッセージを見る場合は%sこちら%sをクリックしてください";
$lang['Click_return_modcp'] = "モデレーター画面に戻る場合は%sこちら%sをクリックしてください";
$lang['Click_return_group'] = "グループ情報画面に戻る場合は%sこちら%sをクリックしてください";

$lang['Admin_panel'] = "管理画面へ移動";

$lang['Board_disable'] = "申し訳ありませんが、現在掲示板は閉鎖されています。時間が経ってから再びアクセスしてください。";


//
// Global Header strings
//
$lang['Registered_users'] = "登録ユーザー:";
$lang['Online_users_zero_total'] = "オンライン状態のユーザーは<b>0</b>です :: ";
$lang['Online_users_total'] = "オンライン状態のユーザーは<b>%d</b>人います :: ";
$lang['Online_user_total'] = "オンライン状態のユーザーは<b>%d</b>人います :: ";
$lang['Reg_users_zero_total'] = "登録ユーザー（0人）, ";
$lang['Reg_users_total'] = "登録ユーザー（%d人）, ";
$lang['Reg_user_total'] = "登録ユーザー（%d人）, ";
$lang['Hidden_users_zero_total'] = "隠れユーザー（0人）, ";
$lang['Hidden_user_total'] = "隠れユーザー（%d人）, ";
$lang['Hidden_user_total'] = "隠れユーザー（%d人）, ";
$lang['Guest_users_zero_total'] = "ゲスト（0人）";
$lang['Guest_users_total'] = "ゲストユーザー（%d人）";
$lang['Guest_user_total'] = "ゲストユーザー（%d人）";

$lang['Admin_online_color'] = "%sAdministrator%s";
$lang['Mod_online_color'] = "%sModerator%s";
$lang['You_last_visit'] = "最後に訪れた日付 - %s"; // %s replaced by date/time
$lang['Current_time'] = "現在の時刻 - %s"; // %s replaced by time

$lang['Search_new'] = "前回表示した記事を表示";
$lang['Search_your_posts'] = "自分の記事を表示";
$lang['Search_unanswered'] = "未返信の記事を表示";

$lang['Register'] = "登録";
$lang['Profile'] = "ユーザー設定";
$lang['Edit_profile'] = "ユーザー設定を変更";
$lang['Search'] = "検索";
$lang['Memberlist'] = "メンバーリスト";
$lang['FAQ'] = "よくある質問";
$lang['BBCode_guide'] = "BBCodeガイド";
$lang['Usergroups'] = "グループ";
$lang['Last_Post'] = "<nobr>最新記事";
$lang['Moderator'] = "モデレーター";
$lang['Moderators'] = "モデレーター";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "ユーザーが投稿した記事の総数:  <b>0</b>"; // Number of posts
$lang['Posted_articles_total'] = "ユーザーが投稿した記事の総数: <b>%d</b>"; // Number of posts
$lang['Posted_article_total'] = "ユーザーが投稿した記事の総数: <b>%d</b>"; // Number of posts
$lang['Registered_users_zero_total'] = "登録ユーザーの数: <b>0</b>"; // # registered users
$lang['Registered_users_total'] = "登録ユーザーの数: <b>%d</b>"; // # registered users
$lang['Registered_user_total'] = "登録ユーザーの数: <b>%d</b>"; // # registered users
$lang['Newest_user'] = "一番新しい登録ユーザー: <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "前回の訪問以来、新しい記事はありません";
$lang['No_new_posts'] = "新しい記事無し";
$lang['New_posts'] = "新しい記事有り";
$lang['New_post'] = "新しい記事有り";
$lang['No_new_posts_hot'] = "新しい記事無し（人気）";
$lang['New_posts_hot'] = "新しい記事有り（人気）";
$lang['No_new_posts_locked'] = "新しい記事無し（ロック）";
$lang['New_posts_locked'] = "新しい記事有り（ロック）";
$lang['Forum_is_locked'] = "ロック状態";


//
// Login
//
$lang['Enter_password'] = "ログインするために名前とパスワードを入力してください";
$lang['Login'] = "ログイン";
$lang['Logout'] = "ログアウト";

$lang['Forgotten_password'] = "パスワードを忘れてしまいました";

$lang['Log_me_in'] = "自動ログインを有効にする";

$lang['Error_login'] = "ユーザー名とパスワードが一致しない、又はユーザー名がアクティブでない可能性があります。";


//
// Index page
//
$lang['Index'] = "一覧";
$lang['No_Posts'] = "記事がありません";
$lang['No_forums'] = "この掲示板にはフォーラムがありません";

$lang['Private_Message'] = "プライベートメッセージ";
$lang['Private_Messages'] = "プライベートメッセージ";
$lang['Who_is_Online'] = "オンライン管理";

$lang['Mark_all_forums'] = ">> 全ての記事マークする（全てのトピックが”新しい記事無し”となります）";
$lang['Forums_marked_read'] = "全てのフォーラムの記事はマークされました";


//
// Viewforum
//
$lang['View_forum'] = "フォーラムを表示";

$lang['Forum_not_exist'] = "選択したフォーラムは存在しません";
$lang['Reached_on_error'] = "あなたは誤ってこのページに来てしまったようです";

$lang['Display_topics'] = "特定期間内のトピックを表示";
$lang['All_Topics'] = "全てのトピック";

$lang['Topic_Announcement'] = "<b>重要:</b>";
$lang['Topic_Sticky'] = "<b>告知:</b>";
$lang['Topic_Moved'] = "<b>移動済み:</b>";
$lang['Topic_Poll'] = "<b>[投票]</b>";

$lang['Mark_all_topics'] = "全てのトピックをマーク";
$lang['Topics_marked_read'] = "フォーラム内のトピックは全てマークされました";

$lang['Rules_post_can'] = "新規投稿: <b>可</b>";
$lang['Rules_post_cannot'] = "新規投稿: <b>不可</b>";
$lang['Rules_reply_can'] = "返信: <b>可</b>";
$lang['Rules_reply_cannot'] = "返信: <b>不可t</b>";
$lang['Rules_edit_can'] = "自分の記事の編集: <b>可</b>";
$lang['Rules_edit_cannot'] = "自分の記事の編集: <b>不可</b>";
$lang['Rules_delete_can'] = "自分の記事の削除: <b>可</b>";
$lang['Rules_delete_cannot'] = "自分の記事の削除: <b>不可</b>";
$lang['Rules_vote_can'] = "投票への参加: <b>可</b>";
$lang['Rules_vote_cannot'] = "投票への参加: <b>不可</b>";
$lang['Rules_moderate'] = "%sモデーレター権限の行使%s: <b>可</b>"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "このフォーラムにはトピックが一つもありません<br />トピックを投稿する場合は<b>新規投稿</b>をクリックしてください";


//
// Viewtopic
//
$lang['View_topic'] = "トピックを表示";

$lang['Guest'] = 'ゲスト';
$lang['Post_subject'] = "題名";
$lang['View_next_topic'] = "次のトピックを表示";
$lang['View_previous_topic'] = "前のトピックを表示";
$lang['Submit_vote'] = "投票";
$lang['View_results'] = "現在の結果を表示";

$lang['No_newer_topics'] = "このフォーラムにはこれ以上新しいトピックはありません";
$lang['No_older_topics'] = "このフォーラムにはこれ以上古いトピックはありません";
$lang['Topic_post_not_exist'] = "要求したトピック、又は記事は存在しません";
$lang['No_posts_topic'] = "このトピックには記事が存在しません";

$lang['Display_posts'] = "特定期間内の記事を表示";
$lang['All_Posts'] = "全ての記事";
$lang['Newest_First'] = "新しい記事から表示";
$lang['Oldest_First'] = "古い記事から表示";

$lang['Back_to_top'] = "トップに移動";

$lang['Read_profile'] = "ユーザー情報を表示"; 
$lang['Send_email'] = "メールを送信";
$lang['Visit_website'] = "ウェブサイトに移動";
$lang['ICQ_status'] = "ICQのステータス";
$lang['Edit_delete_post'] = "記事を編集/削除";
$lang['View_IP'] = "投稿者のIPアドレスを表示";
$lang['Delete_post'] = "記事を削除";

$lang['wrote'] = "wrote"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Quote"; // comes before bbcode quote output.
$lang['Code'] = "Code"; // comes before bbcode code output.

$lang['Edited_time_total'] = "%sが%sに記事を編集, 編集回数: %d"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "編集者: %s, 最終編集日: %s, 編集回数: %d"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "トピックをロックする";
$lang['Unlock_topic'] = "トピックのロックを解除";
$lang['Move_topic'] = "トピックを移動する";
$lang['Delete_topic'] = "トピックを削除する";
$lang['Split_topic'] = "トピックを分割する";

$lang['Stop_watching_topic'] = "このトピックの返信のチェックを解除";
$lang['Start_watching_topic'] = "このトピックの返信のチェック";
$lang['No_longer_watching'] = "トピックの返信のチェックを解除しました";
$lang['You_are_watching'] = "トピックの返信のチェックを開始しました";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "メッセージ";
$lang['Topic_review'] = "トピック確認";

$lang['No_post_mode'] = "投稿モードが指定されていません"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "トピックの新規投稿";
$lang['Post_a_reply'] = "返信の投稿";
$lang['Post_topic_as'] = "トピックの種類";
$lang['Edit_Post'] = "記事の編集";
$lang['Options'] = "オプション";

$lang['Post_Announcement'] = "重要トピック";
$lang['Post_Sticky'] = "告知トピック";
$lang['Post_Normal'] = "一般トピック";

$lang['Confirm_delete'] = "この記事を削除しますか?";
$lang['Confirm_delete_poll'] = "この投票を削除しますか?";

$lang['Flood_Error'] = "投稿直後に再び記事を投稿することはできません。少し時間が経ってからもう一度投稿してください。";
$lang['Empty_subject'] = "トピックを新規投稿する場合は、題名を記入する必要があります";
$lang['Empty_message'] = "メッセージを記入してください";
$lang['Forum_locked'] = "このフォーラムはロックされているため、新規投稿、返信、編集を行うことはできません";
$lang['Topic_locked'] = "このトピックはロックされているため、返信、編集を行うことはできません";
$lang['No_post_id'] = "編集する記事を選択してください";
$lang['No_topic_id'] = "返信するトピックを選択してください";
$lang['No_valid_mode'] = "post, reply edit, quote以外は実行することができません。戻ってもう一度試してください。";
$lang['No_such_post'] = "そのような記事は存在しません。戻ってもう一度試してください。";
$lang['Edit_own_posts'] = "自分の記事しか編集はできません";
$lang['Delete_own_posts'] = "自分の記事しか削除はできません";
$lang['Cannot_delete_replied'] = "返信の付いたトピック記事を削除することはできません";
$lang['Cannot_delete_poll'] = "アクティブ状態の投票トピックは削除できません";
$lang['Empty_poll_title'] = "投票のお題を入力してください";
$lang['To_few_poll_options'] = "投票の選択肢は2つ以上作成してください";
$lang['To_many_poll_options'] = "投票の選択肢が多すぎます";
$lang['Post_has_no_poll'] = "この記事には投票がありません";

$lang['Add_poll'] = "投票の追加/編集";
$lang['Add_poll_explain'] = "トピックに投票を追加したくない場合は、空白のままにしてください";
$lang['Poll_question'] = "投票のお題";
$lang['Poll_option'] = "選択肢";
$lang['Add_option'] = "選択肢を追加";
$lang['Update'] = "更新";
$lang['Delete'] = "削除";
$lang['Poll_for'] = "投票可能な日数";
$lang['Days'] = "日"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "（特に期限を設けない場合は0のままにしてください）";
$lang['Delete_poll'] = "投票を削除";

$lang['Disable_HTML_post'] = "HTMLを無効にする";
$lang['Disable_BBCode_post'] = "BBCodeを無効にする";
$lang['Disable_Smilies_post'] = "スマイリーを無効にする";

$lang['HTML_is_ON'] = "HTML: <u>有効</u>";
$lang['HTML_is_OFF'] = "HTML: <u>無効</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s: <u>有効</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s <u>無効</u>";
$lang['Smilies_are_ON'] = "スマイリー: <u>有効</u>";
$lang['Smilies_are_OFF'] = "スマイリー: <u>無効</u>";

$lang['Attach_signature'] = "サインを有効にする (設定画面でサインを追加/編集することができます)";
$lang['Notify'] = "返信があったときは通知してもらう";
$lang['Delete_post'] = "この記事を削除する";

$lang['Stored'] = "メッセージは投稿されました";
$lang['Deleted'] = "メッセージは削除されました";
$lang['Poll_delete'] = "投票は削除されました";
$lang['Vote_cast'] = "投票は完了しました";

$lang['Topic_reply_notification'] = "返信の通知";

$lang['bbcode_b_help'] = "太字: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "斜体: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "下線: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "引用: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "コード表示: [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "リスト: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "オーダーリスト: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "画像: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "URL: [url]http://url[/url] 又は [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Close all open bbCode tags";
$lang['bbcode_s_help'] = "Font color: [color=red]text[/color]  Tip: you can also use color=#FF0000";
$lang['bbcode_f_help'] = "Font size: [size=x-small]small text[/size]";

$lang['Emoticons'] = "スマイリーアイコン";
$lang['More_emoticons'] = "全てのアイコンを表示";

$lang['Font_color'] = "フォント色";
$lang['color_default'] = "自動";
$lang['color_dark_red'] = "赤(暗)";
$lang['color_red'] = "赤";
$lang['color_orange'] = "橙";
$lang['color_brown'] = "茶";
$lang['color_yellow'] = "黄";
$lang['color_green'] = "緑";
$lang['color_olive'] = "オリーブ";
$lang['color_cyan'] = "水色";
$lang['color_blue'] = "青";
$lang['color_dark_blue'] = "青(暗)";
$lang['color_indigo'] = "藍";
$lang['color_violet'] = "紫";
$lang['color_white'] = "白";
$lang['color_black'] = "黒";

$lang['Font_size'] = "フォントサイズ";
$lang['font_tiny'] = "最小";
$lang['font_small'] = "小";
$lang['font_normal'] = "中";
$lang['font_large'] = "大";
$lang['font_huge'] = "最大";

$lang['Close_Tags'] = "タグを閉じる";
$lang['Styles_tip'] = "Tip: 選択しているテキストにスタイルを素早く適用することができます";


//
// Private Messaging
//
$lang['Private_Messaging'] = "プライベートメッセージ(PM)";

$lang['Login_check_pm'] = "PM確認のためにログイン";
$lang['New_pms'] = "新着メッセージが%d件あります"; // You have 2 new messages
$lang['New_pm'] = "新着メッセージが%d件あります"; // You have 1 new message
$lang['No_new_pm'] = "新着メッセージはありません";
$lang['Unread_pms'] = "未読メッセージが%d件あります";
$lang['Unread_pm'] = "未読メッセージが%d件あります";
$lang['No_unread_pm'] = "未読メッセージはありません";
$lang['You_new_pm'] = "受信ボックスに新着メッセージがあります";
$lang['You_new_pms'] = "受信ボックスに新着メッセージがあります";
$lang['You_no_new_pm'] = "新着メッセージはありません";

$lang['Inbox'] = "受信ボックス";
$lang['Outbox'] = "送信ボックス";
$lang['Savebox'] = "保管ボックス";
$lang['Sentbox'] = "送信済みボックス";
$lang['Flag'] = "<nobr>フラグ";
$lang['Subject'] = "<nobr>件名";
$lang['From'] = "<nobr>送信者";
$lang['To'] = "<nobr>宛先";
$lang['Date'] = "日時";
$lang['Mark'] = "<nobr>チェック";
$lang['Sent'] = "送信済み";
$lang['Saved'] = "保存済み";
$lang['Delete_marked'] = "チェック項目を削除";
$lang['Delete_all'] = "全て削除";
$lang['Save_marked'] = "チェック項目を保存"; 
$lang['Save_message'] = "メッセージを保存";
$lang['Delete_message'] = "メッセージを削除";

$lang['Display_messages'] = "特定期間内のメッセージを表示"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "全てのメッセージ";

$lang['No_messages_folder'] = "このボックスにメッセージはありません";

$lang['PM_disabled'] = "この掲示板ではプライベートメッセージは機能していません";
$lang['Cannot_send_privmsg'] = "残念ながら、管理人によってプライベートメッセージの使用を禁止されています";
$lang['No_to_user'] = "正しい名前を入力してください";
$lang['No_such_user'] = "そのような名前のユーザーは存在しません";

$lang['Message_sent'] = "メッセージは送信されました";

$lang['Click_return_inbox'] = "受信ボックスに戻る場合は%sこちら%sをクリックしてください";
$lang['Click_return_index'] = "「フォーラム一覧」画面に戻る場合は%sこちら%sをクリックしてください";

$lang['Send_a_new_message'] = "新しいメッセージを送信";
$lang['Send_a_reply'] = "メッセージに返信";
$lang['Edit_message'] = "メッセージを編集";

$lang['Notification_subject'] = "新しいメッセージが届きました";

$lang['Find_username'] = "ユーザー検索";
$lang['Find'] = "検索";
$lang['No_match'] = "見つかりませんでした";

$lang['No_post_id'] = "記事のIDが特定されませんでした";
$lang['No_such_folder'] = "そのようなフォルダーは存在しません";
$lang['No_folder'] = "フォルダーは特定されませんでした";

$lang['Mark_all'] = "全て選択";
$lang['Unmark_all'] = "全ての選択を解除";

$lang['Confirm_delete_pm'] = "このメッセージを削除しますか？";
$lang['Confirm_delete_pms'] = "これらのメッセージを削除しますか？";

$lang['Inbox_size'] = "受信ボックスの容量: %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "送信済みボックスの容量: %d%%"; 
$lang['Savebox_size'] = "保存ボックスの容量: %d%%"; 

$lang['Click_view_privmsg'] = "受信ボックスに移動する場合は%sこちら%sをクリックしてください";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "ユーザー情報 :: %s"; // %s is username 
$lang['About_user'] = "%sの詳細"; // %s is username

$lang['Preferences'] = "オプション";
$lang['Items_required'] = "*がついている項目は必ず入力する必要があります";
$lang['Registration_info'] = "登録情報";
$lang['Profile_info'] = "ユーザー情報";
$lang['Profile_info_warn'] = "他のユーザーが見ることができる情報です";
$lang['Avatar_panel'] = "ユーザー画像設定";
$lang['Avatar_gallery'] = "ユーザー画像ギャラリー";

$lang['Website'] = "<nobr>ホームページ";
$lang['Location'] = "<nobr>所在地";
$lang['Contact'] = "連絡先: ";
$lang['Email_address'] = "メールアドレス";
$lang['Email'] = "メール";
$lang['Send_private_message'] = "メッセージを送信";
$lang['Hidden_email'] = "メール非表示";
$lang['Search_user_posts'] = "このユーザーの投稿記事を検索";
$lang['Interests'] = "<nobr>趣味";
$lang['Occupation'] = "<nobr>職業"; 
$lang['Poster_rank'] = "<nobr>投稿者ランク";

$lang['Total_posts'] = "投稿数";
$lang['User_post_pct_stats'] = "全体の割合: %.2f%%"; // 1.25% of total
$lang['User_post_day_stats'] = "1日の投稿数: %.2f"; // 1.5 posts per day
$lang['Search_user_posts'] = "%sの投稿記事を全て検索"; // Find all posts by username

$lang['No_user_id_specified'] = "そのユーザーは存在しません";
$lang['Wrong_Profile'] = "自分以外の設定、ユーザー情報を変更することはできません";
$lang['Sorry_banned_or_taken_email'] = "そのメールアドレスは禁止されているか、既に登録されている可能性があります。別のメールアドレスで再び試してください。それでも駄目な場合は、掲示板の管理者に問い合わせてください。";
$lang['Only_one_avatar'] = "ユーザー画像は1種類しか指定できません";
$lang['File_no_data'] = "指定したURLのファイルにはデータが含まれていません";
$lang['No_connection_URL'] = "指定したURLに接続できません";
$lang['Incomplete_URL'] = "指定したURLは不完全なものです";
$lang['Wrong_remote_avatar_format'] = "指定したURLは有効ではありません";
$lang['No_send_account_inactive'] = "アカウントが非アクティブになっているため、パスワードの再発行を行うことはできません。詳細は掲示板の管理者に問い合わせてください。";

$lang['Always_smile'] = "スマイリーを常に有効にする";
$lang['Always_html'] = "HTMLを常に有効にする";
$lang['Always_bbcode'] = "BBCodeを常に有効にする";
$lang['Always_add_sig'] = "サインを常に有効にする";
$lang['Always_notify'] = "常に返信を通知してもらう";
$lang['Always_notify_explain'] = "あなたが投稿したトピックに返信があった場合に、自動的に通知メールが送信されます。";

$lang['Board_style'] = "掲示板のスタイル";
$lang['Board_lang'] = "掲示板の言語";
$lang['No_themes'] = "データベースにテーマがありません";
$lang['Timezone'] = "タイムゾーン";
$lang['Date_format'] = "日付のフォーマット";
$lang['Date_format_explain'] = "使用されているシンタックスは、PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> のものと全く同じです";
$lang['Signature'] = "サイン";
$lang['Signature_explain'] = "投稿する記事の最後に追加されるメッセージです。最大文字数は%d字となっています";
$lang['Public_view_email'] = "メールアドレスを常に表示";

$lang['Current_password'] = "現在のパスワード";
$lang['New_password'] = "新しいパスワード";
$lang['Confirm_password'] = "新しいパスワード（確認）";
$lang['password_if_changed'] = "パスワードを変更したい場合のみ入力してください";
$lang['password_confirm_if_changed'] = "パスワードを変更する場合のみ入力してください";

$lang['Avatar'] = "ユーザー画像";
$lang['Avatar_explain'] = "記事の投稿者欄に小さな画像を表示します。画像は一度に一つだけしか表示できません。画像の大きさは%d × %d ピクセル、画像のサイズは%dkBまでとなっています。"; $lang['Upload_Avatar_file'] = "自分のPCからユーザー画像をアップロードする";
$lang['Upload_Avatar_URL'] = "URLからユーザー画像をアップロードする";
$lang['Upload_Avatar_URL_explain'] = "使用したいユーザー画像が置いてあるURLを入力してください。こちらの掲示板に保存されます。";
$lang['Pick_local_Avatar'] = "ギャラリーからユーザー画像を選択する";
$lang['Link_remote_Avatar'] = "他のサイトに置かれているユーザー画像にリンクする";
$lang['Link_remote_Avatar_explain'] = "リンクしたいユーザー画像が置いてあるURLを入力してください";
$lang['Avatar_URL'] = "ユーザー画像のURL";
$lang['Select_from_gallery'] = "ギャラリーからユーザー画像を選択する";
$lang['View_avatar_gallery'] = "ギャラリーを表示";

$lang['Select_avatar'] = "ユーザー画像を選択";
$lang['Return_profile'] = "ユーザー画像をキャンセル";
$lang['Select_category'] = "カテゴリを選択";

$lang['Delete_Image'] = "画像を削除";
$lang['Current_Image'] = "現在の画像";

$lang['Notify_on_privmsg'] = "新しいプライベートメッセージが来たら通知してもらう";
$lang['Popup_on_privmsg'] = "新しいプライベートメッセージが来たらポップアップウィンドウで通知してらう"; 
$lang['Popup_on_privmsg_explain'] = "いくつかのテンプレートでは、新しいプライベートメッセージが来た場合に新しいウィンドウを開く場合があります。"; 
$lang['Hide_user'] = "オンラインステータスを隠す";

$lang['Profile_updated'] = "設定は更新されました";
$lang['Profile_updated_inactive'] = "設定は更新されましたが、重要な詳細も変更したためにアカウントは非アクティブ状態となっています。メールをチェックして、どのようにアカウントを再アクティブするかを確かめてください。管理者による再アクティブが必要な場合は、管理者がアカウントを再アクティブするまで待ってください。";

$lang['Password_mismatch'] = "入力したパスワードは一致しませんでした";
$lang['Current_password_mismatch'] = "入力した現在のパスワードは、データベースに保管されているものと一致しませんでした";
$lang['Invalid_username'] = "その名前は既に登録されているか、使用禁止の名前として登録されています。あるいは \" のような記号が含まれています。";
$lang['Signature_too_long'] = "サインが長すぎます";
$lang['Fields_empty'] = "必要事項の欄を入力してください";
$lang['Avatar_filetype'] = "ユーザー画像は.jpg, .gif, .pngである必要があります";
$lang['Avatar_filesize'] = "ユーザー画像のサイズは%dkB以下である必要があります"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "ユーザー画像の大きさは%d × %d ピクセル以内である必要があります。"; 

$lang['Welcome_subject'] = "ようこそ%sの掲示板へ"; // Welcome to my.com forums
$lang['New_account_subject'] = "新しいユーザーアカウント";
$lang['Account_activated_subject'] = "アカウントはアクティブになりました";

$lang['Account_added'] = "登録していただき、まことにありがとうございます。アカウントは作成されました。名前とパスワードを入力して、掲示板にログインすることができます。";
$lang['Account_inactive'] = "アカウントは作成されました。しかし、この掲示板ではアカウントのアクティベーションが必要となっています。あなたのメールアドレスに送信されたメールにアクティベーションのキーが載っています。詳細に関してはメールを見てください。";
$lang['Account_inactive_admin'] = "アカウントは作成されました。しかし、この掲示板では管理者によるアカウントのアクティベーションが必要となっています。管理者がアカウントをアクティブにした後、あなたのメールアドレスにアクティベーション完了のメールが送信されます。";
$lang['Account_active'] = "アカウントはアクティブとなりました。登録していただき、まことにありがとうございます";
$lang['Account_active_admin'] = "アカウントはアクティブとなりました";
$lang['Reactivate'] = "Reactivate your account!";
$lang['COPPA'] = "アカウントは作成されましたが、承諾される必要があります。詳細に関してはメールを見てください。";

$lang['Registration'] = "登録規約";
$lang['Reg_agreement'] = "掲示板の管理者とモデレーターは、不適切な記事を発見次第削除/編集するように心がけますが、全ての記事に目を通すことはできません。そのため、この掲示板に投稿される全ての記事はその投稿者の視点と意見を表現するものであり、掲示板管理者、モデレーター、サイト管理者の視点と意見を表現するものではなく、管理人は記事に対する責任を一切負いません（掲示板管理者、モデレーター、サイト管理者自身の記事は除く）。<br /><br />口汚い記事、猥褻な言葉、品性を欠く記事、他人を中傷する記事、嫌悪感を与える記事、脅迫的な記事、性的差別につながる記事、法律を違反する記事の投稿は禁止します。この規約を破った場合は、対象ユーザーのアカウント停止が即座に行われます（場合によっては対象ユーザーのプロバイダーに報告されます）。このような処置を実行するために、全ての記事のIPアドレスが記録されています。掲示板管理者、モデレーター、サイト管理者は、自らの判断で掲示板の如何なる記事を削除、編集、移動、ロックする権限があります。あなたが掲示板上で入力した情報をデータベースに保管されます。あなたの同意がない限り、この情報は第三者に公開されることはありませんが、ハッキング等によるデータの損傷や盗難があった場合は掲示板管理者、モデレーター、サイト管理者はその責任を一切負いません。<br /><br />この掲示板では、あなたのローカル・コンピューターに情報を保管するためにCookieを使用しています。このCookieは、掲示板の使い勝手の向上させるための情報しか含まれません。メールアドレスは、登録したアカウントの詳細とパスワードを確認するためにだけ使われます（現在のパスワードを忘れた場合、パスワードを再発行する場合にも使われます）。<br /><br />登録を続ける場合は、これらの規約に同意したものと見なされます。";

$lang['Agree_under_13'] = "<b>私は13歳未満です。</b>この規約に同意します。";
$lang['Agree_over_13'] = "<b>私は13歳以上です。</b>この規約に同意します。";
$lang['Agree_not'] = "この規約に同意しません。";

$lang['Wrong_activation'] = "そのアクティベーションキーはデータベースのものとは一致しません。";
$lang['Send_password'] = "新しいパスワードの発行"; 
$lang['Password_updated'] = "新しいパスワードが発行されました。どのようにアクティブにするのかは、メールを確認して下さい。";
$lang['No_email_match'] = "そのメールアドレスは、その名前のものと一致しません。";
$lang['New_password_activation'] = "新しいパスワードのアクティベーション";
$lang['Password_activated'] = "あなたのアカウントは再アクティブされました。ログインする場合は、受信したメールに載っているパスワードを使用してください。";

$lang['Send_email_msg'] = "メールを送信";
$lang['No_user_specified'] = "ユーザーは特定されませんでした";
$lang['User_prevent_email'] = "このユーザーはメールアドレスによる送信を許可していません。プライベートメッセージ（PM）を使用してください。";
$lang['User_not_exist'] = "そのユーザーは存在しません";
$lang['CC_email'] = "自分自身にこのメールのコピーを送信";
$lang['Email_message_desc'] = "このメッセージはテキストで送信され、BBコードとHTMLは含まれません。このメッセージの返信アドレスは、あなたのメールアドレスに設定されます。";
$lang['Flood_email_limit'] = "この時点ではメールを再び送信することはできません。時間が経ってからもう一度試してください。";
$lang['Recipient'] = "宛先";
$lang['Email_sent'] = "メールは送信されました";
$lang['Send_email'] = "メールを送信";
$lang['Empty_subject_email'] = "件名を入力してください";
$lang['Empty_message_email'] = "メッセージを入力してください";


//
// Memberslist
//
$lang['Select_sort_method'] = "表示方法";
$lang['Sort'] = "並べ替える";
$lang['Sort_Top_Ten'] = "トップ10の投稿者";
$lang['Sort_Joined'] = "登録日";
$lang['Sort_Username'] = "名前";
$lang['Sort_Location'] = "所在地";
$lang['Sort_Posts'] = "投稿数";
$lang['Sort_Email'] = "メール";
$lang['Sort_Website'] = "ホームページ";
$lang['Sort_Ascending'] = "昇順";
$lang['Sort_Descending'] = "降順";
$lang['Order'] = "並べ方";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "グループ設定";
$lang['Group_member_details'] = "グループメンバー詳細";
$lang['Group_member_join'] = "グループに参加";

$lang['Group_Information'] = "グループ情報";
$lang['Group_name'] = "グループ名";
$lang['Group_description'] = "グループの記述";
$lang['Group_membership'] = "グループのメンバーシップ";
$lang['Group_Members'] = "グループのメンバー";
$lang['Group_Moderator'] = "グループのモデレーター";
$lang['Pending_members'] = "未決定のメンバー";

$lang['Group_type'] = "グループのタイプ";
$lang['Group_open'] = "オープングループ";
$lang['Group_closed'] = "クローズドグループ";
$lang['Group_hidden'] = "隠れグループ";

$lang['Current_memberships'] = "グループ（メンバー有り）";
$lang['Non_member_groups'] = "グループ（メンバー無し）";
$lang['Memberships_pending'] = "グループ（メンバー未決定）";

$lang['No_groups_exist'] = "グループはありません";
$lang['Group_not_exist'] = "そのグループは存在しません";

$lang['Join_group'] = "グループに参加";
$lang['No_group_members'] = "このグループにメンバーはいません";
$lang['Group_hidden_members'] = "このグループは隠れグループです。メンバー情報を表示することはできません";
$lang['No_pending_group_members'] = "このグループに未決定メンバーはいません";
$lang["Group_joined"] = "グループ参加の申し込みは完了しました。<br />グループのモデレーターが参加の申し込みを承諾したら、あなたに通知されます。";
$lang['Group_request'] = "グループ参加の申し込みがあります";
$lang['Group_approved'] = "グループ参加の申し込みは承諾されました";
$lang['Group_added'] = "このグループに追加されました"; 
$lang['Already_member_group'] = "あなたは既にこのグループのメンバーです";
$lang['User_is_member_group'] = "ユーザーは既にこのグループのメンバーです";
$lang['Group_type_updated'] = "グループのタイプを更新しました";

$lang['Could_not_add_user'] = "選択したユーザーは存在しません";
$lang['Could_not_anon_user'] = "匿名ユーザーをグループのメンバーにすることはできません";

$lang['Confirm_unsub'] = "参加を解除しますか？";
$lang['Confirm_unsub_pending'] = "あなたの参加はまだグループによって承諾されていません。参加を解除しますか？";

$lang['Unsub_success'] = "あなたはグループから脱退しました";

$lang['Approve_selected'] = "選択したものを承諾";
$lang['Deny_selected'] = "選択したものを拒否";
$lang['Not_logged_in'] = "グループに参加するにはログインする必要があります";
$lang['Remove_selected'] = "選択したものを削除";
$lang['Add_member'] = "メンバーを追加";
$lang['Not_group_moderator'] = "そのアクションはこのグループのモデレーターしか実行できません";

$lang['Login_to_join'] = "グループに参加したり、グループを運営する場合はログインする必要があります";
$lang['This_open_group'] = "このグループはオープングループです。このグループに参加した場合は、「グループに参加」をクリックしてください";
$lang['This_closed_group'] = "このグループはクローズドグループです。このグループに参加することはできません";
$lang['This_hidden_group'] = "このグループは隠れグループです。自動ユーザー追加は許可されていません";
$lang['Member_this_group'] = "あなたはこのグループのメンバーです";
$lang['Pending_this_group'] = "あなたの参加はまだ承諾されていません";
$lang['Are_group_moderator'] = "あなたはグループのモデレーターです";
$lang['None'] = "無し";

$lang['Subscribe'] = "参加する";
$lang['Unsubscribe'] = "参加を解除";
$lang['View_Information'] = "情報を表示";


//
// Search
//
$lang['Search_query'] = "検索クエリ";
$lang['Search_options'] = "検索オプション";

$lang['Search_keywords'] = "キーワードを検索";
$lang['Search_keywords_explain'] = "検索では <u>AND</u>, <u>OR</u>, <u>NOT</u> を使用することができます。ワイルドカードを使用する場合は*を使用してください";
$lang['Search_author'] = "投稿者を検索";
$lang['Search_author_explain'] = "ワイルドカードを使用する場合は*を使用してください";

$lang['Search_for_any'] = "全ての単語を検索、又はクエリ検索";
$lang['Search_for_all'] = "全ての単語を検索";

$lang['Search_title_msg'] = "トピックの題名と本文を検索";
$lang['Search_msg_only'] = "本文のみを検索";
$lang['Return_first'] = "検索結果の各記事の表示文字数: "; // followed by xxx characters in a select box
$lang['characters_posts'] = "文字";

$lang['Search_previous'] = "特定期間内を検索"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "ソート方法";
$lang['Sort_Time'] = "投稿時間";
$lang['Sort_Post_Subject'] = "記事の題名";
$lang['Sort_Topic_Title'] = "トピックの題名";
$lang['Sort_Author'] = "投稿者";
$lang['Sort_Forum'] = "フォーラム";

$lang['Display_results'] = "検索結果の表示形式";
$lang['All_available'] = "全て";
$lang['No_searchable_forums'] = "あなたには検索する権限がありません";

$lang['No_search_match'] = "検索結果：0件";
$lang['Found_search_match'] = "検索結果：%d件"; // eg. Search found 1 match
$lang['Found_search_matches'] = "検索結果：%d件"; // eg. Search found 24 matches

$lang['Close_window'] = "ウィンドウを閉じる";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "このフォーラムで重要トピックを投稿できるのは%sだけです";
$lang['Sorry_auth_sticky'] = "このフォーラムで告知トピックを投稿できるのは%sだけです"; 
$lang['Sorry_auth_read'] = "このフォーラムでトピックを読むことができるのは%sだけです"; 
$lang['Sorry_auth_post'] = "このフォーラムでトピックを投稿できるのは%sだけです"; 
$lang['Sorry_auth_reply'] = "このフォーラムで返信を行えるのは%sだけです"; 
$lang['Sorry_auth_edit'] = "このフォーラムで記事を編集できるのは%sだけです"; 
$lang['Sorry_auth_delete'] = "このフォーラムで記事を削除できるのは%sだけです"; 
$lang['Sorry_auth_vote'] = "このフォーラムで投票に参加できるのは%sだけです"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>匿名ユーザー</b>";
$lang['Auth_Registered_Users'] = "<b>登録ユーザー</b>";
$lang['Auth_Users_granted_access'] = "<b>特別ユーザー（特別な権限が与えられています）</b>";
$lang['Auth_Moderators'] = "<b>モデレーター</b>";
$lang['Auth_Administrators'] = "<b>管理者</b>";

$lang['Not_Moderator'] = "あなたはこのフォーラムのモデレーターではありません";
$lang['Not_Authorised'] = "許可されていません";

$lang['You_been_banned'] = "あなたはこのフォーラムの使用を禁止されています<br />詳細については、ウェブマスターか掲示板の管理者に問い合わせてください。";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "登録ユーザー（0人）, "; // There ae 5 Registered and
$lang['Reg_users_online'] = "登録ユーザー（%d人）, "; // There ae 5 Registered and
$lang['Reg_user_online'] = "登録ユーザー（%d人）, "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "隠れユーザー（0人）がオンライン状態です"; // 6 Hidden users online
$lang['Hidden_users_online'] = "隠れユーザー（%d人）がオンライン状態です"; // 6 Hidden users online
$lang['Hidden_user_online'] = "隠れユーザー（%d人）がオンライン状態です"; // 6 Hidden users online
$lang['Guest_users_online'] = "ゲスト（%d人）がオンライン状態です"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "ゲスト（0人）がオンライン状態です"; // There are 10 Guest users online
$lang['Guest_user_online'] = "ゲスト（%d人）がオンライン状態です"; // There is 1 Guest user online
$lang['No_users_browsing'] = "現在、このフォーラムには1人もオンライン状態のユーザーがいません";

$lang['Online_explain'] = "このデータは、過去5分間の間にアクションを取ったユーザーに基づいたものです";

$lang['Forum_Location'] = "フォーラムの場所";
$lang['Last_updated'] = "最終更新";

$lang['Forum_index'] = "フォーラム一覧";
$lang['Logging_on'] = "ログイン";
$lang['Posting_message'] = "記事の投稿";
$lang['Searching_forums'] = "フォーラムの検索";
$lang['Viewing_profile'] = "ユーザー設定の観閲";
$lang['Viewing_online'] = "オンラインユーザーの観閲";
$lang['Viewing_member_list'] = "メンバーリストの観閲";
$lang['Viewing_priv_msgs'] = "プライベートメッセージの観閲";
$lang['Viewing_FAQ'] = "よくある質問の観閲";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "モデレーター用コントロールパネル";
$lang['Mod_CP_explain'] = "モデレーターの権限を使って、トピックや記事ののロック/ロック解除/移動/削除等を行うことができます。";

$lang['Select'] = "<nobr>選択";
$lang['Delete'] = "削除";
$lang['Move'] = "移動";
$lang['Lock'] = "ロック";
$lang['Unlock'] = "ロック解除";

$lang['Topics_Removed'] = "選択したトピックは削除されました";
$lang['Topics_Locked'] = "選択したトピックはロックされました";
$lang['Topics_Moved'] = "選択したトピックは移動されました";
$lang['Topics_Unlocked'] = "選択したトピックのロックは解除されました";
$lang['No_Topics_Moved'] = "トピックは一切移動されませんでした";

$lang['Confirm_delete_topic'] = "本当に選択したトピックを削除しますか？";
$lang['Confirm_lock_topic'] = "本当に選択したトピックをロックしますか？";
$lang['Confirm_unlock_topic'] = "本当に選択したトピックのロックを解除しますか？";
$lang['Confirm_move_topic'] = "本当に選択したトピックを移動しますか？";

$lang['Move_to_forum'] = "移動先のフォーラム";
$lang['Leave_shadow_topic'] = "現在のフォーラムにシャドウトピックを残す";

$lang['Split_Topic'] = "トピック分割設定";
$lang['Split_Topic_explain'] = "トピック分割設定で、トピックを2つに分けることができます。選択した記事を新しいトピックに";
$lang['Split_title'] = "新しいトピックの題名";
$lang['Split_forum'] = "新しいトピックを設置するフォーラム";
$lang['Split_posts'] = "選択した記事を分割する";
$lang['Split_after'] = "選択した記事以下の記事を分割する";
$lang['Topic_split'] = "選択したトピックは分割されました";

$lang['Too_many_error'] = "選択した記事が多すぎます。トピック分割では、記事を一つしか選択することができません。";

$lang['None_selected'] = "記事が一つも選択されていません。最低一つは選択する必要があります";
$lang['New_forum'] = "新しいフォーラム";

$lang['This_posts_IP'] = "この投稿者のIP";
$lang['Other_IP_this_user'] = "この投稿者が使う他のIP";
$lang['Users_this_IP'] = "このIPを使う投稿者";
$lang['IP_info'] = "IP情報";
$lang['Lookup_IP'] = "IPを検索";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "All times are %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT -12:00";
$lang['-11'] = "GMT -11:00";
$lang['-10'] = "ハワイ標準時 (ハワイ)";
$lang['-9'] = "GMT -9:00";
$lang['-8'] = "太平洋標準時 (米国およびカナダ)";
$lang['-7'] = "山地標準時 (米国およびカナダ)";
$lang['-6'] = "中部標準時 (米国およびカナダ)";
$lang['-5'] = "東部標準時 (米国およびカナダ)";
$lang['-4'] = "GMT -4:00";
$lang['-3.5'] = "GMT -3:30";
$lang['-3'] = "グリニッジ標準時 -3:00";
$lang['-2'] = "中央大西洋";
$lang['-1'] = "GMT -1:00";
$lang['0'] = "GMT";
$lang['1'] = "中央ヨーロッパ標準時 (欧州)";
$lang['2'] = "東ヨーロッパ標準時 (欧州)";
$lang['3'] = "GMT +3:00";
$lang['3.5'] = "GMT +3:30";
$lang['4'] = "GMT +4:00";
$lang['4.5'] = "GMT +4:30";
$lang['5'] = "GMT +5:00";
$lang['5.5'] = "GMT +5:30";
$lang['6'] = "GMT +6:00";
$lang['7'] = "GMT +7:00";
$lang['8'] = "西側標準時 (豪州)";
$lang['9'] = "GMT +9:00";
$lang['9.5'] = "中部標準時 (欧州)";
$lang['10'] = "東部標準時 (欧州)";
$lang['11'] = "GMT +11:00";
$lang['12'] = "GMT +12:00";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00) エニウェトク、クエジェリン";
$lang['tz']['-11'] = "(GMT -11:00) ミッドウェー島、サモア";
$lang['tz']['-10'] = "(GMT -10:00) ハワイ";
$lang['tz']['-9'] = "(GMT -9:00) アラスカ";
$lang['tz']['-8'] = "(GMT -8:00) 太平洋標準時 (米国およびカナダ), ティファナ";
$lang['tz']['-7'] = "(GMT -7:00) 山地標準時 (米国およびカナダ), アリゾナ";
$lang['tz']['-6'] = "(GMT -6:00) 中部標準時 (米国およびカナダ), メキシコシティ";
$lang['tz']['-5'] = "(GMT -5:00) 東部標準時 (米国およびカナダ), ボゴタ, リマ, キト";
$lang['tz']['-4'] = "(GMT -4:00) 大西洋標準時 (カナダ), カラカス, ラパス";
$lang['tz']['-3.5'] = "(GMT -3:30) ニューファンドランド";
$lang['tz']['-3'] = "(GMT -3:00) ブラジリア, ブエノスアイレス, ジョージタウン, フォークランド諸島";
$lang['tz']['-2'] = "(GMT -2:00) 中央大西洋, アセンション島, セントヘレナ島";
$lang['tz']['-1'] = "(GMT -1:00) アゾレス諸島, カーボベルデ諸島";
$lang['tz']['0'] = "(GMT) カサブランカ, ダブリン, エジンバラ, ロンドン, リスボン, モンロビア";
$lang['tz']['1'] = "(GMT +1:00) アムステルダム, ベルリン, ブリュッセル, マドリード, パリ, ローマ";
$lang['tz']['2'] = "(GMT +2:00) カイロ, ヘルシンキ, カリーニングラード, 南アフリカ, ワルシャワ ";
$lang['tz']['3'] = "(GMT +3:00) バグダッド, リヤド, モスクワ, ナイロビ";
$lang['tz']['3.5'] = "(GMT +3:30) テヘラン";
$lang['tz']['4'] = "(GMT +4:00) アブダビ, バク, マスカット, トビリシ";
$lang['tz']['4.5'] = "(GMT +4:30) カブール";
$lang['tz']['5'] = "(GMT +5:00) エカテリンバーグ, イスラマバード, カラチ, タシケント";
$lang['tz']['5.5'] = "(GMT +5:30) ボンベイ, カルカッタ, マドラス, ニューデリー";
$lang['tz']['6'] = "(GMT +6:00) アルマティ, コロンボ, ダッカ, ノボシビルスク";
$lang['tz']['6.5'] = "(GMT +6:30) ラングーン";
$lang['tz']['7'] = "(GMT +7:00) バンコク, ハノイ, ジャカルタ";
$lang['tz']['8'] = "(GMT +8:00) 北京, 香港, パース, シンガポール, 台北";
$lang['tz']['9'] = "(GMT +9:00) 大阪, 札幌, ソウル, 東京, ヤクーツク";
$lang['tz']['9.5'] = "(GMT +9:30) アデレード, ダーウィン";
$lang['tz']['10'] = "(GMT +10:00) キャンベラ, グアム, メルボルン, シドニー, ウラジオストク";
$lang['tz']['11'] = "(GMT +11:00) マガダン, ニューカレドニア, ソロモン諸島";
$lang['tz']['12'] = "(GMT +12:00) オークランド, ウェリントン, フィジー, マーシャル諸島";

$lang['days_long'][0] = "日曜日";
$lang['days_long'][1] = "月曜日";
$lang['days_long'][2] = "火曜日";
$lang['days_long'][3] = "水曜日";
$lang['days_long'][4] = "木曜日";
$lang['days_long'][5] = "金曜日";
$lang['days_long'][6] = "土曜日";

$lang['days_short'][0] = "日";
$lang['days_short'][1] = "月";
$lang['days_short'][2] = "火";
$lang['days_short'][3] = "水";
$lang['days_short'][4] = "木";
$lang['days_short'][5] = "金";
$lang['days_short'][6] = "土";

$lang['months_long'][0] = "1月";
$lang['months_long'][1] = "2月";
$lang['months_long'][2] = "3月";
$lang['months_long'][3] = "4月";
$lang['months_long'][4] = "5月";
$lang['months_long'][5] = "6月";
$lang['months_long'][6] = "7月";
$lang['months_long'][7] = "8月";
$lang['months_long'][8] = "9月";
$lang['months_long'][9] = "10月";
$lang['months_long'][10] = "11月";
$lang['months_long'][11] = "12月";

$lang['months_short'][0] = "1月";
$lang['months_short'][1] = "2月";
$lang['months_short'][2] = "3月";
$lang['months_short'][3] = "4月";
$lang['months_short'][4] = "5月";
$lang['months_short'][5] = "6月";
$lang['months_short'][6] = "7月";
$lang['months_short'][7] = "8月";
$lang['months_short'][8] = "9月";
$lang['months_short'][9] = "10月";
$lang['months_short'][10] = "11月";
$lang['months_short'][11] = "12月";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "情報";
$lang['Critical_Information'] = "重要な情報";

$lang['General_Error'] = "一般エラー";
$lang['Critical_Error'] = "重大エラー";
$lang['An_error_occured'] = "エラーが発生しました";
$lang['A_critical_error'] = "重大エラーが発生しました";

//
// That's all Folks!
// -------------------------------------------------

?>